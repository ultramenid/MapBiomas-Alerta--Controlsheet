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

            $php     = PHP_BINARY;
            $artisan = base_path('artisan');
            $args    = collect([
                'alerts:backup',
                '--table='  . $this->option('table'),
                '--chunk='  . $this->option('chunk'),
                $this->option('drop') ? '--drop' : null,
                $this->option('name') ? '--name=' . $this->option('name') : null,
            ])->filter()->values()->toArray();

            // Tulis shell script ke file temp — hindari masalah quoting path dengan spasi
            $scriptFile = storage_path('logs/backup-runner-' . Carbon::now()->format('Ymd-His') . '.sh');
            $phpEsc     = escapeshellarg($php);
            $artisanEsc = escapeshellarg($artisan);
            $argsEsc    = implode(' ', array_map('escapeshellarg', $args));
            $logEsc     = escapeshellarg($logFile);

            file_put_contents($scriptFile, "#!/bin/sh\n{$phpEsc} {$artisanEsc} {$argsEsc} >> {$logEsc} 2>&1\nrm -f " . escapeshellarg($scriptFile) . "\n");
            chmod($scriptFile, 0755);

            $scriptEsc = escapeshellarg($scriptFile);
            shell_exec("nohup sh {$scriptEsc} > /dev/null 2>&1 &");

            $this->line("✔  Backup berjalan di background.");
            $this->line("   Log    : {$logFile}");
            $this->line("   Pantau : tail -f {$logFile}");
            return self::SUCCESS;
        }

        set_time_limit(0);

        $sourceTable = $this->option('table');
        $backupName  = $this->option('name') ?: $sourceTable . '_backup_' . Carbon::now()->format('Ymd');
        $drop        = $this->option('drop');
        $chunkSize   = (int) $this->option('chunk');

        $this->log("Memulai backup");
        $this->log("Sumber  : {$sourceTable}");
        $this->log("Target  : {$backupName}");
        $this->log("Chunk   : {$chunkSize} records/batch");
        $this->separator();

        // Cek total rows — query ringan, tidak load data ke PHP
        $this->log("Menghitung total records...");
        $totalRows = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$sourceTable}`")->total;
        $this->log("Total   : {$totalRows} records");
        $this->separator();

        // Cek apakah tabel backup sudah ada
        $dbName = DB::connection()->getDatabaseName();
        $exists = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
            [$dbName, $backupName]
        )->cnt;

        if ($exists) {
            if ($drop) {
                $this->log("⚠  Tabel {$backupName} sudah ada — menghapus...");
                // TRUNCATE dulu (kosongkan .ibd — instan meski 2GB), baru DROP
                DB::unprepared("TRUNCATE TABLE `{$backupName}`");
                DB::unprepared("DROP TABLE `{$backupName}`");
                $this->log("✔  Tabel lama dihapus");
            } else {
                $this->log("✘  Tabel {$backupName} sudah ada! Gunakan --drop untuk menimpa.");
                return self::FAILURE;
            }
        }

        // Buat struktur tabel (LIKE — copy semua kolom, index, constraints)
        $this->log("Membuat struktur tabel backup...");
        DB::unprepared("CREATE TABLE `{$backupName}` LIKE `{$sourceTable}`");
        $this->log("✔  Struktur tabel dibuat");
        $this->separator();

        // Copy data bertahap: INSERT INTO backup SELECT * FROM source WHERE id > lastId LIMIT chunk
        // Data TIDAK masuk ke PHP memory — semua diproses di sisi MySQL
        $this->log("Mulai menyalin data...");

        $batch     = 0;
        $lastId    = 0;
        $startTime = microtime(true);

        while (true) {
            // Satu query INSERT ... SELECT — murni di MySQL, tidak tarik data ke PHP
            DB::unprepared(
                "INSERT INTO `{$backupName}` SELECT * FROM `{$sourceTable}` WHERE id > {$lastId} ORDER BY id LIMIT {$chunkSize}"
            );

            // Cek posisi cursor terbaru
            $newLastId = DB::selectOne("SELECT MAX(id) as max_id FROM `{$backupName}`")->max_id ?? 0;

            if ((int) $newLastId === (int) $lastId) {
                break; // Tidak ada data baru — selesai
            }

            $lastId  = (int) $newLastId;
            $batch++;
            $copied  = DB::selectOne("SELECT COUNT(*) as total FROM `{$backupName}`")->total;
            $elapsed = round(microtime(true) - $startTime, 1);
            $percent = $totalRows > 0 ? round(($copied / $totalRows) * 100, 1) : 0;
            $bar     = $this->makeBar($percent);

            $this->log("Batch #{$batch} | {$bar} {$percent}% | {$copied}/{$totalRows} records | {$elapsed}s");
        }

        $elapsed = round(microtime(true) - $startTime, 1);
        $this->separator();

        // Verifikasi
        $this->log("Verifikasi...");
        $backupCount = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$backupName}`")->total;

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
