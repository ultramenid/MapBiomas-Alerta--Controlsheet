<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupAlertsTable extends Command
{
    protected $signature = 'alerts:backup
                            {--table=alerts : Nama tabel sumber}
                            {--name= : Nama tabel/file backup (default: alerts_backup_YYYYMMDD)}
                            {--drop : Timpa backup lama jika sudah ada}
                            {--chunk=200 : Jumlah record per batch}
                            {--to-table : Backup ke tabel baru (default: ke file .sql.gz)}
                            {--engine=ARCHIVE : Engine tabel backup — ARCHIVE (compressed), MyISAM (ringan), InnoDB (default mysql)}
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
                '--engine=' . $this->option('engine'),
                $this->option('drop')     ? '--drop'     : null,
                $this->option('to-table') ? '--to-table' : null,
                $this->option('name')     ? '--name=' . $this->option('name') : null,
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
        $chunkSize   = (int) $this->option('chunk');
        $toTable     = $this->option('to-table');

        return $toTable
            ? $this->backupToTable($sourceTable, $backupName, $chunkSize)
            : $this->backupToFile($sourceTable, $backupName, $chunkSize);
    }

    private function backupToTable(string $sourceTable, string $backupName, int $chunkSize): int
    {
        $engine = strtoupper($this->option('engine'));
        $drop   = $this->option('drop');

        $this->log("Memulai backup ke tabel");
        $this->log("Sumber  : {$sourceTable}");
        $this->log("Target  : {$backupName}");
        $this->log("Engine  : {$engine} (" . $this->engineNote($engine) . ")");
        $this->log("Chunk   : {$chunkSize} records/batch");
        $this->separator();

        $this->log("Menghitung total records...");
        $totalRows = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$sourceTable}`")->total;
        $this->log("Total   : {$totalRows} records");
        $this->separator();

        // Cek jika tabel backup sudah ada
        $dbName = DB::connection()->getDatabaseName();
        $exists = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
            [$dbName, $backupName]
        )->cnt;

        if ($exists) {
            if ($drop) {
                $this->log("⚠  Tabel {$backupName} sudah ada — menghapus...");
                // ARCHIVE tidak support TRUNCATE — cukup DROP langsung
                if ($engine !== 'ARCHIVE') {
                    DB::unprepared("TRUNCATE TABLE `{$backupName}`");
                }
                DB::unprepared("DROP TABLE `{$backupName}`");
                $this->log("✔  Tabel lama dihapus");
            } else {
                // Resume: lanjut dari ID terakhir yang sudah ada
                $resumeFrom = (int) (DB::selectOne("SELECT MAX(id) as max_id FROM `{$backupName}`")->max_id ?? 0);
                $alreadyCopied = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$backupName}`")->total;
                $this->log("▶  Resume dari ID {$resumeFrom} ({$alreadyCopied} records sudah ada)");

                // Langsung ke loop copy dengan lastId yang sudah diset
                $this->log("Membuat struktur tabel backup (ENGINE={$engine})...");
                $this->log("✔  Tabel sudah ada — skip CREATE");
                $this->separator();
                $this->log("Mulai menyalin data...");

                $batch       = 0;
                $lastId      = $resumeFrom;
                $startTime   = microtime(true);
                $maxSourceId = (int) DB::selectOne("SELECT MAX(id) as max_id FROM `{$sourceTable}`")->max_id;

                while (true) {
                    DB::unprepared(
                        "INSERT INTO `{$backupName}` SELECT * FROM `{$sourceTable}` WHERE id > {$lastId} ORDER BY id LIMIT {$chunkSize}"
                    );

                    $newLastId = DB::selectOne("SELECT MAX(id) as max_id FROM `{$backupName}`")->max_id ?? 0;
                    if ((int) $newLastId === (int) $lastId) break;

                    $lastId  = (int) $newLastId;
                    $batch++;
                    $elapsed = round(microtime(true) - $startTime, 1);
                    $percent = $maxSourceId > 0 ? round(($lastId / $maxSourceId) * 100, 1) : 0;
                    $percent = min($percent, 100);

                    $this->log("Batch #{$batch} | " . $this->makeBar($percent) . " {$percent}% | lastId={$lastId}/{$maxSourceId} | {$elapsed}s");
                }

                $elapsed     = round(microtime(true) - $startTime, 1);
                $backupCount = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$backupName}`")->total;
                $this->separator();

                if ($backupCount === $totalRows) {
                    $this->log("✔  Backup selesai: {$backupCount} records → {$backupName} ({$elapsed}s)");
                    return self::SUCCESS;
                }

                $this->log("✘  Jumlah tidak cocok! Sumber: {$totalRows}, Backup: {$backupCount}");
                return self::FAILURE;
            }
        }

        // Buat tabel dengan engine pilihan — bukan InnoDB = tidak ada .ibd
        $this->log("Membuat struktur tabel backup (ENGINE={$engine})...");
        $createRow = DB::selectOne("SHOW CREATE TABLE `{$sourceTable}`");
        $createSql = $createRow->{'Create Table'};
        // Ganti nama tabel & engine
        $createSql = preg_replace('/CREATE TABLE `' . preg_quote($sourceTable, '/') . '`/', "CREATE TABLE `{$backupName}`", $createSql, 1);
        $createSql = preg_replace('/ENGINE=\w+/i', "ENGINE={$engine}", $createSql);

        // ARCHIVE tidak support index & PRIMARY KEY sama sekali — strip semua
        if ($engine === 'ARCHIVE') {
            $lines = explode("\n", $createSql);
            $lines = array_filter($lines, function ($line) {
                $trimmed = trim($line);
                // Hapus baris KEY, PRIMARY KEY, UNIQUE KEY, FULLTEXT KEY
                return ! preg_match('/^\s*(PRIMARY KEY|UNIQUE KEY|FULLTEXT KEY|KEY)\s/i', $trimmed);
            });
            // Bersihkan trailing koma pada baris terakhir kolom
            $lines = array_values($lines);
            foreach (array_reverse(array_keys($lines)) as $i) {
                $trimmed = trim($lines[$i]);
                if ($trimmed === '' || str_starts_with($trimmed, ')')) continue;
                $lines[$i] = rtrim($lines[$i], ',');
                break;
            }
            $createSql = implode("\n", $lines);
            // Hapus AUTO_INCREMENT dari definisi kolom & dari table options
            // Urutan penting: strip "AUTO_INCREMENT=NNN" (table option) dulu, lalu keyword kolom
            $createSql = preg_replace('/\s*AUTO_INCREMENT=\d+/i', '', $createSql);
            $createSql = preg_replace('/\s*\bAUTO_INCREMENT\b/i', '', $createSql);
        }
        DB::unprepared($createSql);
        $this->log("✔  Struktur tabel dibuat");
        $this->separator();

        $this->log("Mulai menyalin data...");
        $batch     = 0;
        $lastId    = 0;
        $copied    = 0;
        $startTime = microtime(true);
        $maxSourceId = (int) DB::selectOne("SELECT MAX(id) as max_id FROM `{$sourceTable}`")->max_id;

        while (true) {
            DB::unprepared(
                "INSERT INTO `{$backupName}` SELECT * FROM `{$sourceTable}` WHERE id > {$lastId} ORDER BY id LIMIT {$chunkSize}"
            );

            $newLastId = DB::selectOne("SELECT MAX(id) as max_id FROM `{$backupName}`")->max_id ?? 0;
            if ((int) $newLastId === (int) $lastId) break;

            $lastId  = (int) $newLastId;
            $batch++;
            $copied += $chunkSize;
            $elapsed = round(microtime(true) - $startTime, 1);
            // Gunakan lastId/maxSourceId untuk progress akurat — tanpa COUNT(*) per batch
            $percent = $maxSourceId > 0 ? round(($lastId / $maxSourceId) * 100, 1) : 0;
            $percent = min($percent, 100);

            $this->log("Batch #{$batch} | " . $this->makeBar($percent) . " {$percent}% | lastId={$lastId}/{$maxSourceId} | {$elapsed}s");
        }

        $elapsed     = round(microtime(true) - $startTime, 1);
        $backupCount = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$backupName}`")->total;
        $this->separator();

        if ($backupCount === $totalRows) {
            $this->log("✔  Backup selesai: {$backupCount} records → {$backupName} ({$elapsed}s)");
            return self::SUCCESS;
        }

        $this->log("✘  Jumlah tidak cocok! Sumber: {$totalRows}, Backup: {$backupCount}");
        return self::FAILURE;
    }

    private function engineNote(string $engine): string
    {
        return match ($engine) {
            'ARCHIVE' => 'terkompresi otomatis, tidak ada .ibd',
            'MYISAM'  => 'file .MYD/.MYI, lebih ringan dari InnoDB',
            'INNODB'  => 'file .ibd, sama berat dengan tabel asli',
            default   => $engine,
        };
    }

    private function backupToFile(string $sourceTable, string $backupName, int $chunkSize): int
    {
        $outFile = storage_path('backups/' . $backupName . '.sql.gz');

        // Pastikan direktori ada
        if (! is_dir(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0755, true);
        }

        // Jika file sudah ada
        if (file_exists($outFile)) {
            if ($this->option('drop')) {
                unlink($outFile);
                if (file_exists($outFile . '.checkpoint')) unlink($outFile . '.checkpoint');
                $this->log("⚠  File lama dihapus: {$outFile}");
            } else {
                // Resume dari checkpoint jika ada
                $checkpointFile = $outFile . '.checkpoint';
                if (file_exists($checkpointFile)) {
                    $lastId = (int) file_get_contents($checkpointFile);
                    $this->log("▶  Resume dari checkpoint ID={$lastId}");
                } else {
                    $this->log("✘  File sudah ada: {$outFile}");
                    $this->log("   Gunakan --drop untuk menimpa, atau hapus file checkpoint jika ingin mulai ulang.");
                    return self::FAILURE;
                }
            }
        }

        $this->log("Memulai backup");
        $this->log("Sumber  : {$sourceTable}");
        $this->log("Output  : {$outFile}");
        $this->log("Chunk   : {$chunkSize} records/batch");
        $this->separator();

        // Hitung total
        $this->log("Menghitung total records...");
        $totalRows = (int) DB::selectOne("SELECT COUNT(*) as total FROM `{$sourceTable}`")->total;
        $this->log("Total   : {$totalRows} records");
        $this->separator();

        // Buka gzip stream — tulis langsung ke file terkompresi
        $gz = gzopen($outFile, 'wb9'); // level 9 = kompresi maksimal
        if (! $gz) {
            $this->log("✘  Gagal membuka file output: {$outFile}");
            return self::FAILURE;
        }

        // --- Header ---
        gzwrite($gz, "-- Backup: {$sourceTable}\n");
        gzwrite($gz, "-- Date  : " . Carbon::now()->toDateTimeString() . "\n");
        gzwrite($gz, "-- Rows  : {$totalRows}\n\n");

        // --- Header restore optimization ---
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\n");
        gzwrite($gz, "SET UNIQUE_CHECKS=0;\n");
        gzwrite($gz, "SET AUTOCOMMIT=0;\n\n");

        // --- CREATE TABLE ---
        $this->log("Menulis struktur tabel...");
        $createRow = DB::selectOne("SHOW CREATE TABLE `{$sourceTable}`");
        $createSql = $createRow->{'Create Table'};
        $createSql = preg_replace(
            '/CREATE TABLE `' . preg_quote($sourceTable, '/') . '`/',
            "CREATE TABLE `{$backupName}`",
            $createSql,
            1
        );
        gzwrite($gz, "DROP TABLE IF EXISTS `{$backupName}`;\n");
        gzwrite($gz, $createSql . ";\n\n");

        // --- Data ---
        $this->log("Mulai menyalin data...");
        gzwrite($gz, "-- Data\n");

        $batch      = 0;
        $copied     = 0;
        $lastId     = $lastId ?? 0; // bisa dari checkpoint resume
        $startTime  = microtime(true);
        $checkpointFile = $outFile . '.checkpoint';

        // Ambil nama kolom
        $columns = array_map(
            fn($col) => '`' . $col->Field . '`',
            DB::select("SHOW COLUMNS FROM `{$sourceTable}`")
        );
        $colList = implode(', ', $columns);

        while (true) {
            $rows = DB::select(
                "SELECT * FROM `{$sourceTable}` WHERE id > ? ORDER BY id LIMIT ?",
                [$lastId, $chunkSize]
            );

            if (empty($rows)) break;

            // Multi-row INSERT — jauh lebih cepat saat restore vs INSERT per baris
            $valueGroups = [];
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    if ($val === null) return 'NULL';
                    return "'" . addslashes((string) $val) . "'";
                }, (array) $row);
                $valueGroups[] = '(' . implode(', ', $values) . ')';
                $copied++;
            }

            // Buffer per batch — satu gzwrite per INSERT statement
            $buffer  = "INSERT INTO `{$backupName}` ({$colList}) VALUES\n";
            $buffer .= implode(",\n", $valueGroups) . ";\n";
            gzwrite($gz, $buffer);
            unset($buffer, $valueGroups);

            $lastId  = end($rows)->id;
            $batch++;
            $elapsed = round(microtime(true) - $startTime, 1);
            $percent = $totalRows > 0 ? round(($copied / $totalRows) * 100, 1) : 0;

            $this->log("Batch #{$batch} | " . $this->makeBar($percent) . " {$percent}% | {$copied}/{$totalRows} records | {$elapsed}s");

            // Simpan checkpoint — resume jika proses terputus
            file_put_contents($checkpointFile, $lastId);

            unset($rows);
        }

        // Commit dan restore flags
        gzwrite($gz, "\nCOMMIT;\n");
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzwrite($gz, "SET UNIQUE_CHECKS=1;\n");
        gzwrite($gz, "SET AUTOCOMMIT=1;\n");

        gzclose($gz);

        $elapsed  = round(microtime(true) - $startTime, 1);
        $fileSize = round(filesize($outFile) / 1024 / 1024, 2);
        // Hapus checkpoint — backup selesai sempurna
        if (file_exists($checkpointFile)) unlink($checkpointFile);
        $this->separator();
        $this->log("✔  Backup selesai: {$copied} records → {$outFile} ({$fileSize} MB, {$elapsed}s)");
        $this->log("   Restore : gunakan 'zcat {$outFile} | mysql -u[user] -p [database}'");
        return self::SUCCESS;
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
