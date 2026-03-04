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
                            {--drop : Drop tabel backup lama jika sudah ada}
                            {--chunk=200 : Jumlah record per batch}
                            {--background : Jalankan di background (aman meski koneksi SSH putus)}
                            {--log= : Path file log (default: storage/logs/backup-YYYYMMDD-HHmmss.log)}';

    protected $description = 'Backup tabel alerts ke tabel baru dengan progress real-time';

    public function handle(): int
    {
        ini_set('memory_limit', '-1');

        // Jika --background, spawn ulang dengan nohup lalu keluar
        if ($this->option('background')) {
            $logFile = $this->option('log')
                ?: storage_path('logs/backup-' . Carbon::now()->format('Ymd-His') . '.log');

            // Bangun ulang command tanpa --background
            $artisan = base_path('artisan');
            $php     = PHP_BINARY;
            $args    = collect([
                '--table='  . $this->option('table'),
                '--chunk='  . $this->option('chunk'),
                $this->option('drop') ? '--drop' : null,
                $this->option('name') ? '--name=' . $this->option('name') : null,
            ])->filter()->implode(' ');

            $cmd = "nohup \"{$php}\" \"{$artisan}\" alerts:backup {$args} >> \"{$logFile}\" 2>&1 &";
            shell_exec($cmd);

            $this->line("✔  Backup berjalan di background.");
            $this->line("   Log : {$logFile}");
            $this->line("   Pantau: tail -f {$logFile}");
            return self::SUCCESS;
        }

        $sourceTable = $this->option('table');
        $backupName  = $this->option('name') ?: $sourceTable . '_backup_' . Carbon::now()->format('Ymd');
        $drop        = $this->option('drop');
        $chunkSize   = (int) $this->option('chunk');

        $this->log("Memulai backup");
        $this->log("Sumber  : {$sourceTable}");
        $this->log("Target  : {$backupName}");
        $this->log("Chunk   : {$chunkSize} records/batch");
        $this->separator();

        // Cek tabel sumber
        $this->log("Menghitung total records...");
        $totalRows = DB::table($sourceTable)->count();
        $this->log("Total   : {$totalRows} records");
        $this->separator();

        // Cek apakah tabel backup sudah ada
        $exists = DB::select("SHOW TABLES LIKE '{$backupName}'");
        if ($exists) {
            if ($drop) {
                $this->log("⚠  Tabel {$backupName} sudah ada — menghapus...");
                DB::statement("DROP TABLE `{$backupName}`");
                $this->log("✔  Tabel lama dihapus");
            } else {
                $this->log("✘  Tabel {$backupName} sudah ada! Gunakan --drop untuk menimpa.");
                return self::FAILURE;
            }
        }

        // Buat struktur tabel
        $this->log("Membuat struktur tabel backup...");
        DB::statement("CREATE TABLE `{$backupName}` LIKE `{$sourceTable}`");
        $this->log("✔  Struktur tabel dibuat");
        $this->separator();

        // Copy data chunk by chunk dengan output setiap batch
        $this->log("Mulai menyalin data...");

        $copied    = 0;
        $batch     = 0;
        $startTime = microtime(true);
        $lastId    = 0;

        while (true) {
            $rows = DB::table($sourceTable)
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit($chunkSize)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $batch++;
            $data   = $rows->map(fn($r) => (array) $r)->toArray();
            $lastId = $rows->last()->id;

            DB::table($backupName)->insert($data);

            $copied  += count($data);
            $elapsed  = round(microtime(true) - $startTime, 1);
            $percent  = $totalRows > 0 ? round(($copied / $totalRows) * 100, 1) : 0;
            $mem      = round(memory_get_usage(true) / 1024 / 1024, 1);
            $bar      = $this->makeBar($percent);

            $this->log(
                "Batch #{$batch} | {$bar} {$percent}% | {$copied}/{$totalRows} records | {$elapsed}s | mem: {$mem}MB"
            );
        }

        $elapsed = round(microtime(true) - $startTime, 1);
        $this->separator();

        // Verifikasi
        $this->log("Verifikasi...");
        $backupCount = DB::table($backupName)->count();

        if ($backupCount === $totalRows) {
            $this->log("✔  Backup selesai: {$backupCount} records → {$backupName} ({$elapsed}s)");
            return self::SUCCESS;
        }

        $this->log("✘  Jumlah tidak cocok! Sumber: {$totalRows}, Backup: {$backupCount}");
        return self::FAILURE;
    }

    private function log(string $message): void
    {
        $ts = Carbon::now()->format('H:i:s');
        $this->line("[{$ts}] {$message}");
    }

    private function separator(): void
    {
        $this->line(str_repeat('─', 60));
    }

    private function makeBar(float $percent, int $width = 20): string
    {
        $filled = (int) round($percent / 100 * $width);
        $empty  = $width - $filled;
        return '[' . str_repeat('█', $filled) . str_repeat('░', $empty) . ']';
    }
}
