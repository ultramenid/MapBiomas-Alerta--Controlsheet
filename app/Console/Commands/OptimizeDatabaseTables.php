<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeDatabaseTables extends Command
{
    protected $signature = 'db:optimize-tables
                            {--tables=alerts : Nama tabel yang akan di-optimize, pisahkan dengan koma (default: alerts)}
                            {--all : Optimize semua tabel di database}';

    protected $description = 'Jalankan OPTIMIZE TABLE untuk membebaskan ruang disk setelah migrasi base64 atau penghapusan data besar';

    public function handle(): int
    {
        $dbName = DB::getDatabaseName();

        if ($this->option('all')) {
            $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_type = "BASE TABLE"', [$dbName]);
            $tableNames = array_map(fn($t) => $t->table_name, $tables);
        } else {
            $tableNames = array_map('trim', explode(',', $this->option('tables')));
        }

        if (empty($tableNames)) {
            $this->error('Tidak ada tabel yang ditemukan.');
            return self::FAILURE;
        }

        $this->info("Database  : <comment>{$dbName}</comment>");
        $this->info("Tabel     : <comment>" . implode(', ', $tableNames) . "</comment>");
        $this->newLine();

        // Tampilkan ukuran sebelum optimize
        $this->info('Ukuran tabel sebelum OPTIMIZE:');
        $sizeBefore = $this->getTableSizes($dbName, $tableNames);
        $totalBefore = 0;
        foreach ($sizeBefore as $row) {
            $sizeMB = round(($row->data_length + $row->index_length) / 1024 / 1024, 2);
            $freeMB = round($row->data_free / 1024 / 1024, 2);
            $totalBefore += ($row->data_length + $row->index_length);
            $this->line("  <comment>{$row->table_name}</comment>: {$sizeMB} MB (free/fragmented: {$freeMB} MB)");
        }
        $this->newLine();

        // Jalankan OPTIMIZE TABLE
        foreach ($tableNames as $table) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table); // sanitize
            $this->line("⚙  OPTIMIZE TABLE <comment>{$table}</comment> ...");

            try {
                $result = DB::select("OPTIMIZE TABLE `{$table}`");
                $status = $result[0]->Msg_text ?? 'selesai';
                $this->line("   ✔  {$status}");
            } catch (\Throwable $e) {
                $this->error("   ✗  Gagal: " . $e->getMessage());
            }
        }

        $this->newLine();

        // Tampilkan ukuran sesudah optimize
        $this->info('Ukuran tabel setelah OPTIMIZE:');
        $sizeAfter = $this->getTableSizes($dbName, $tableNames);
        $totalAfter = 0;
        foreach ($sizeAfter as $row) {
            $sizeMB = round(($row->data_length + $row->index_length) / 1024 / 1024, 2);
            $freeMB = round($row->data_free / 1024 / 1024, 2);
            $totalAfter += ($row->data_length + $row->index_length);
            $this->line("  <comment>{$row->table_name}</comment>: {$sizeMB} MB (free/fragmented: {$freeMB} MB)");
        }

        $this->newLine();
        $savedMB = round(($totalBefore - $totalAfter) / 1024 / 1024, 2);
        if ($savedMB > 0) {
            $this->info("✅  Total ruang dibebaskan: <comment>{$savedMB} MB</comment>");
        } else {
            $this->line("ℹ  Tidak ada perubahan signifikan (mungkin sudah optimal).");
        }

        return self::SUCCESS;
    }

    private function getTableSizes(string $dbName, array $tableNames): array
    {
        $placeholders = implode(',', array_fill(0, count($tableNames), '?'));
        return DB::select(
            "SELECT table_name, data_length, index_length, data_free
             FROM information_schema.tables
             WHERE table_schema = ? AND table_name IN ({$placeholders})",
            array_merge([$dbName], $tableNames)
        );
    }
}
