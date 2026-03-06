<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarkBase64Rows extends Command
{
    protected $signature = 'alerts:mark-base64
                            {--table=alerts : Nama tabel}
                            {--reset : Reset semua has_base64 ke 0 dulu sebelum scan ulang}';

    protected $description = 'Tandai baris yang mengandung base64 image dengan has_base64=1 (jalankan sekali saja)';

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $table = $this->option('table');

        // Cek kolom has_base64 ada
        $dbName  = DB::connection()->getDatabaseName();
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
            $this->warn("Reset has_base64 = 0 untuk semua baris...");
            DB::unprepared("UPDATE `{$table}` SET has_base64 = 0");
            $this->line("✔  Reset selesai.");
        }

        $this->line("Menandai baris dengan base64 image...");
        $this->line("⚠  Ini full scan sekali — setelahnya semua query pakai index.");
        $this->line("");

        $start = microtime(true);

        // Satu query UPDATE — MySQL scan sekali, update langsung
        $affected = DB::affectingStatement(
            "UPDATE `{$table}`
             SET has_base64 = 1
             WHERE has_base64 = 0
               AND (auditorReason LIKE '%data:image/%' OR alertNote LIKE '%data:image/%')"
        );

        $elapsed = round(microtime(true) - $start, 1);
        $this->line("[" . Carbon::now()->format('H:i:s') . "] ✔  Selesai: {$affected} baris ditandai has_base64=1 ({$elapsed}s)");
        $this->line("");
        $this->line("Langkah selanjutnya:");
        $this->line("  php artisan alerts:backup --drop --base64-only   ← backup cepat");
        $this->line("  php artisan alerts:migrate-base64-images          ← migrasi cepat");

        return self::SUCCESS;
    }
}
