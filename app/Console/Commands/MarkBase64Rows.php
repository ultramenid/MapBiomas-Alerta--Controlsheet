<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarkBase64Rows extends Command
{
    protected $signature = 'alerts:mark-base64
                            {--table=alerts : Nama tabel}
                            {--chunk=1000 : Jumlah record per batch scan}
                            {--reset : Reset semua has_base64 ke 0 dulu sebelum scan ulang}
                            {--background : Jalankan di background (aman meski koneksi SSH putus)}
                            {--log= : Path file log}';

    protected $description = 'Tandai baris yang mengandung base64 image dengan has_base64=1 (jalankan sekali saja)';

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        if ($this->option('background')) {
            $logFile = $this->option('log')
                ?: storage_path('logs/mark-base64-' . Carbon::now()->format('Ymd-His') . '.log');

            $php     = PHP_BINARY;
            $artisan = base_path('artisan');
            $args    = collect([
                'alerts:mark-base64',
                '--table=' . $this->option('table'),
                '--chunk=' . $this->option('chunk'),
                $this->option('reset') ? '--reset' : null,
            ])->filter()->values()->toArray();

            $scriptFile = storage_path('logs/mark-base64-runner-' . Carbon::now()->format('Ymd-His') . '.sh');
            $phpEsc     = escapeshellarg($php);
            $artisanEsc = escapeshellarg($artisan);
            $argsEsc    = implode(' ', array_map('escapeshellarg', $args));
            $logEsc     = escapeshellarg($logFile);

            file_put_contents($scriptFile, "#!/bin/sh\n{$phpEsc} {$artisanEsc} {$argsEsc} >> {$logEsc} 2>&1\nrm -f " . escapeshellarg($scriptFile) . "\n");
            chmod($scriptFile, 0755);

            shell_exec("nohup sh " . escapeshellarg($scriptFile) . " > /dev/null 2>&1 &");

            $this->line("✔  Mark-base64 berjalan di background.");
            $this->line("   Log    : {$logFile}");
            $this->line("   Pantau : tail -f {$logFile}");
            return self::SUCCESS;
        }

        $table     = $this->option('table');
        $chunkSize = (int) $this->option('chunk');

        // Cek kolom has_base64 ada
        $dbName    = DB::connection()->getDatabaseName();
        $colExists = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS
             WHERE table_schema = ? AND table_name = ? AND column_name = 'has_base64'",
            [$dbName, $table]
        )->cnt > 0;

        if (! $colExists) {
            $this->error("Kolom has_base64 tidak ditemukan di tabel {$table}.");
            $this->line("Jalankan dulu: php artisan migrate");
            return self::FAILURE;
        }

        if ($this->option('reset')) {
            $this->log("Reset has_base64 = 0 untuk semua baris...");
            DB::unprepared("UPDATE `{$table}` SET has_base64 = 0");
            $this->log("✔  Reset selesai.");
        }

        $maxId = (int) DB::selectOne("SELECT MAX(id) as max_id FROM `{$table}`")->max_id;

        $this->log("Tabel   : {$table}");
        $this->log("Max ID  : {$maxId}");
        $this->log("Chunk   : {$chunkSize} records/batch");
        $this->log("Memulai scan — progress berdasarkan id range...");
        $this->separator();

        $lastId   = 0;
        $batch    = 0;
        $marked   = 0;
        $scanned  = 0;
        $start    = microtime(true);

        while ($lastId < $maxId) {
            // Scan chunk: cari id yang punya base64, lewati yang sudah ditandai
            $ids = DB::select(
                "SELECT id FROM `{$table}`
                 WHERE id > ? AND id <= ? AND has_base64 = 0
                   AND (auditorReason LIKE '%data:image/%' OR alertNote LIKE '%data:image/%')
                 ORDER BY id",
                [$lastId, $lastId + $chunkSize]
            );

            if (! empty($ids)) {
                $idList = implode(',', array_column($ids, 'id'));
                DB::unprepared("UPDATE `{$table}` SET has_base64 = 1 WHERE id IN ({$idList})");
                $marked += count($ids);
            }

            $lastId  += $chunkSize;
            $scanned += $chunkSize;
            $batch++;
            $elapsed = round(microtime(true) - $start, 1);
            $percent = $maxId > 0 ? min(round(($lastId / $maxId) * 100, 1), 100) : 100;

            $this->log("Batch #{$batch} | " . $this->makeBar($percent) . " {$percent}% | ditandai={$marked} | {$elapsed}s");
        }

        $elapsed = round(microtime(true) - $start, 1);
        $this->separator();
        $this->log("✔  Selesai: {$marked} baris ditandai has_base64=1 ({$elapsed}s)");
        $this->log("");
        $this->log("Langkah selanjutnya:");
        $this->log("  php artisan alerts:backup --drop --base64-only   ← backup cepat");
        $this->log("  php artisan alerts:migrate-base64-images          ← migrasi cepat");

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
