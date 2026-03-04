<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupAlertsTable extends Command
{
    protected $signature = 'alerts:backup
                            {--table=alerts : Nama tabel sumber}
                            {--name= : Nama tabel backup (default: alerts_backup_YYYYMMDD)}
                            {--drop : Drop tabel backup lama jika sudah ada}';

    protected $description = 'Backup tabel alerts ke tabel baru dengan progress bar';

    public function handle(): int
    {
        $sourceTable = $this->option('table');
        $backupName  = $this->option('name') ?: $sourceTable . '_backup_' . Carbon::now()->format('Ymd');
        $drop        = $this->option('drop');

        // Cek tabel sumber
        $totalRows = DB::table($sourceTable)->count();
        $this->info("Tabel sumber : <comment>{$sourceTable}</comment> ({$totalRows} records)");
        $this->info("Tabel backup : <comment>{$backupName}</comment>");
        $this->newLine();

        // Cek apakah tabel backup sudah ada
        $exists = DB::select("SHOW TABLES LIKE '{$backupName}'");
        if ($exists) {
            if ($drop) {
                $this->warn("Tabel {$backupName} sudah ada, menghapus...");
                DB::statement("DROP TABLE `{$backupName}`");
            } else {
                $this->error("Tabel {$backupName} sudah ada! Gunakan --drop untuk menimpa.");
                return self::FAILURE;
            }
        }

        // Step 1: Buat struktur tabel
        $this->line('📋 Membuat struktur tabel backup...');
        DB::statement("CREATE TABLE `{$backupName}` LIKE `{$sourceTable}`");
        $this->info('   ✔  Struktur tabel dibuat');

        // Step 2: Copy data dengan progress per chunk
        $this->newLine();
        $this->line("📦 Menyalin {$totalRows} records...");

        $chunkSize  = 500;
        $copied     = 0;
        $bar        = $this->output->createProgressBar($totalRows);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %elapsed:6s% elapsed');
        $bar->start();

        DB::table($sourceTable)
            ->orderBy('id')
            ->chunk($chunkSize, function ($rows) use ($backupName, &$copied, $bar) {
                $data = $rows->map(fn($r) => (array) $r)->toArray();
                DB::table($backupName)->insert($data);
                $copied += count($data);
                $bar->advance(count($data));
            });

        $bar->finish();
        $this->newLine(2);

        // Verifikasi
        $backupCount = DB::table($backupName)->count();
        if ($backupCount === $totalRows) {
            $this->info("✅ Backup selesai: {$backupCount} records tersalin ke <comment>{$backupName}</comment>");
        } else {
            $this->warn("⚠️  Jumlah tidak cocok! Sumber: {$totalRows}, Backup: {$backupCount}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
