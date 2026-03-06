<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateBase64Images extends Command
{
    protected $signature = 'alerts:migrate-base64-images
                            {--dry-run : Jalankan tanpa menyimpan perubahan}
                            {--column=all : Kolom yang akan diproses (all, auditorReason, alertNote)}
                            {--chunk=100 : Jumlah record per batch}
                            {--table=alerts : Nama tabel yang akan diproses (default: alerts)}
                            {--background : Jalankan di background (aman meski koneksi SSH putus)}
                            {--log= : Path file log (default: storage/logs/migrate-base64-YYYYMMDD-HHmmss.log)}';

    protected $description = 'Migrasi base64 images dari database ke file system dan update URL di database';

    // Prefix yang dicari di dalam src attribute
    private const BASE64_PREFIX = 'data:image/';

    private int $totalImages = 0;
    private int $totalRecords = 0;
    private int $failedImages = 0;
    private bool $dryRun = false;
    private string $table = 'alerts';
    private bool $hasBase64Flag = false; // true jika kolom has_base64 ada di tabel

    /** @var string[] File path yang sudah disimpan ke storage (untuk rollback) */
    private array $savedFiles = [];

    /** @var array<int, array{table: string, id: mixed, col: string, original: string}> Record yang sudah diupdate (untuk rollback) */
    private array $updatedRecords = [];

    public function handle(): int
    {
        // Gambar base64 bisa sangat besar — hapus batas memory untuk command ini
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        // Jika --background, spawn ulang dengan nohup lalu keluar
        if ($this->option('background')) {
            $logFile = $this->option('log')
                ?: storage_path('logs/migrate-base64-' . \Carbon\Carbon::now()->format('Ymd-His') . '.log');

            $php     = PHP_BINARY;
            $artisan = base_path('artisan');
            $args    = collect([
                'alerts:migrate-base64-images',
                '--column='  . $this->option('column'),
                '--chunk='   . $this->option('chunk'),
                '--table='   . $this->option('table'),
                $this->option('dry-run') ? '--dry-run' : null,
            ])->filter()->values()->toArray();

            $scriptFile = storage_path('logs/migrate-runner-' . \Carbon\Carbon::now()->format('Ymd-His') . '.sh');
            $phpEsc     = escapeshellarg($php);
            $artisanEsc = escapeshellarg($artisan);
            $argsEsc    = implode(' ', array_map('escapeshellarg', $args));
            $logEsc     = escapeshellarg($logFile);

            file_put_contents($scriptFile, "#!/bin/sh\n{$phpEsc} {$artisanEsc} {$argsEsc} >> {$logEsc} 2>&1\nrm -f " . escapeshellarg($scriptFile) . "\n");
            chmod($scriptFile, 0755);

            $scriptEsc = escapeshellarg($scriptFile);
            shell_exec("nohup sh {$scriptEsc} > /dev/null 2>&1 &");

            $this->line("✔  Migrasi berjalan di background.");
            $this->line("   Log    : {$logFile}");
            $this->line("   Pantau : tail -f {$logFile}");
            return self::SUCCESS;
        }

        $this->dryRun = $this->option('dry-run');
        $column     = $this->option('column');
        $chunkSize  = (int) $this->option('chunk');
        $this->table = $this->option('table');

        // Deteksi kolom has_base64 sekali saja
        $dbName = DB::connection()->getDatabaseName();
        $this->hasBase64Flag = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS
             WHERE table_schema = ? AND table_name = ? AND column_name = 'has_base64'",
            [$dbName, $this->table]
        )->cnt > 0;

        $columns = match ($column) {
            'auditorReason' => ['auditorReason'],
            'alertNote'     => ['alertNote'],
            default         => ['auditorReason', 'alertNote'],
        };

        if ($this->dryRun) {
            $this->warn('⚠️  DRY-RUN mode aktif — tidak ada perubahan yang akan disimpan.');
        }

        // Pastikan direktori tujuan tersedia
        if (! $this->dryRun) {
            Storage::disk('public')->makeDirectory('alert-images');
        }

        $this->info("Tabel target    : <comment>{$this->table}</comment>");
        $this->info("Memulai migrasi kolom: " . implode(', ', $columns));
        $this->newLine();

        try {
            foreach ($columns as $col) {
                $this->info("▶  Memproses kolom: <comment>{$col}</comment>");

                // Cek apakah kolom has_base64 ada (lebih cepat dari LIKE)
                $hasFt = ! $this->hasBase64Flag && DB::selectOne(
                    "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
                     WHERE table_schema = DATABASE()
                       AND table_name = ?
                       AND column_name = ?
                       AND index_type = 'FULLTEXT'",
                    [$this->table, $col]
                )->cnt > 0;

                $query = DB::table($this->table)
                    ->whereNotNull($col)
                    ->where($col, '!=', '');

                if ($this->hasBase64Flag) {
                    $query->where('has_base64', 1);
                    $this->line("   <info>✔ has_base64 index terdeteksi — query secepat kilat</info>");
                } elseif ($hasFt) {
                    // FULLTEXT: 'base64' sebagai keyword cepat via index,
                    // LIKE sebagai filter presisi (hanya rows yang lolos MATCH yang di-LIKE)
                    $query->whereRaw("MATCH(`{$col}`) AGAINST(? IN BOOLEAN MODE)", ['base64'])
                          ->where($col, 'LIKE', '%data:image/%');
                    $this->line("   <info>✔ FULLTEXT index terdeteksi — menggunakan MATCH...AGAINST</info>");
                } else {
                    $query->where($col, 'LIKE', '%data:image/%');
                    $this->line("   <comment>⚠ FULLTEXT index tidak ada — fallback ke LIKE (lambat pada tabel besar)</comment>");
                }

                $query->orderBy('id')
                    ->chunk($chunkSize, function ($rows) use ($col) {
                        foreach ($rows as $row) {
                            $this->processRow($row, $col);
                        }
                    });
            }
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("💥  Error fatal: " . $e->getMessage());
            $this->warn("↩️   Menjalankan rollback...");
            $this->rollback();
            return self::FAILURE;
        }

        $this->newLine();
        $this->line("─────────────────────────────────────────────");
        $this->info("✅  Selesai.");
        $this->line("   Records diproses : {$this->totalRecords}");
        $this->line("   Gambar dimigrasi : {$this->totalImages}");
        if ($this->failedImages > 0) {
            $this->warn("   Gagal            : {$this->failedImages}");
        }

        if ($this->dryRun) {
            $this->warn("⚠️  Tidak ada perubahan disimpan (dry-run).");
        }

        return self::SUCCESS;
    }

    private function processRow(object $row, string $col): void
    {
        $content = $row->{$col};

        if (! $this->hasBase64Image($content)) {
            return;
        }

        $this->totalRecords++;
        $imagesFound = 0;

        // Gunakan DOMDocument agar tidak terkena batas backtrack regex pada konten besar
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Bungkus konten agar DOMDocument tidak menambah <html><body>
        $wrapped = '<?xml encoding="UTF-8"><div>' . $content . '</div>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $imgTags = $dom->getElementsByTagName('img');
        $replacements = []; // [oldSrc => newUrl]

        // Kumpulkan semua img src base64 terlebih dahulu (karena DOMNodeList adalah live)
        $srcs = [];
        $rowFiles = []; // file yang disimpan untuk row ini (untuk rollback lokal)
        foreach ($imgTags as $img) {
            $src = $img->getAttribute('src');
            if (str_starts_with($src, self::BASE64_PREFIX)) {
                $srcs[] = $src;
            }
        }

        foreach ($srcs as $src) {
            if (isset($replacements[$src])) {
                // Base64 yang sama sudah diproses sebelumnya
                continue;
            }

            // Parse mime & data
            // Format: data:image/png;base64,<data>
            if (! preg_match('/^data:(image\/[a-z]+);base64,/i', $src, $m)) {
                continue;
            }
            $mimeType   = $m[1];
            $base64Data = substr($src, strlen($m[0]));
            $extension  = $this->mimeToExtension($mimeType);
            $filename   = 'alert-images/' . \Illuminate\Support\Str::uuid() . '.' . $extension;
            $publicUrl  = '/storage/' . $filename;

            if ($this->dryRun) {
                $this->line("   [DRY-RUN] ID={$row->id} → akan disimpan sebagai {$filename}");
                $replacements[$src] = $publicUrl;
                $imagesFound++;
                $this->totalImages++;
                continue;
            }

            try {
                $decoded = base64_decode(preg_replace('/\s+/', '', $base64Data), strict: true);
                if ($decoded === false) {
                    // Coba lagi tanpa strict (toleran terhadap karakter invalid)
                    $decoded = base64_decode(preg_replace('/[^A-Za-z0-9+\/=]/', '', $base64Data));
                }
                if ($decoded === false || $decoded === '') {
                    $this->warn("   ⚠️  Gagal decode base64 pada ID={$row->id}, dilewati.");
                    $this->failedImages++;
                    continue;
                }

                Storage::disk('public')->put($filename, $decoded);
                $replacements[$src] = $publicUrl;
                $rowFiles[]         = $filename; // catat untuk rollback lokal
                $imagesFound++;
                $this->totalImages++;
                $this->line("   ✔  ID={$row->id} → <info>{$filename}</info>");
            } catch (\Throwable $e) {
                $this->warn("   ⚠️  Error pada ID={$row->id}: " . $e->getMessage());
                $this->failedImages++;
            }
        }

        // Lakukan string-replace langsung pada konten asli (lebih aman daripada serialize DOM)
        if ($imagesFound > 0) {
            $newContent = $content;
            foreach ($replacements as $oldSrc => $newUrl) {
                // Ganti semua kemunculan src yang sama
                $newContent = str_replace(
                    ['src="' . $oldSrc . '"', "src='" . $oldSrc . "'"],
                    ['src="' . $newUrl . '"', "src='" . $newUrl . "'"],
                    $newContent
                );
            }

            if (! $this->dryRun) {
                try {
                    DB::table($this->table)
                        ->where('id', $row->id)
                        ->update([$col => $newContent]);

                    // Setelah berhasil, reset has_base64=0 agar baris tidak diproses ulang
                    if ($this->hasBase64Flag) {
                        DB::table($this->table)->where('id', $row->id)->update(['has_base64' => 0]);
                    }

                    // Catat untuk full rollback jika proses berikutnya gagal
                    $this->savedFiles    = array_merge($this->savedFiles, $rowFiles);
                    $this->updatedRecords[] = [
                        'table'    => $this->table,
                        'id'       => $row->id,
                        'col'      => $col,
                        'original' => $content,
                    ];
                } catch (\Throwable $e) {
                    // DB update gagal — hapus file yang sudah disimpan untuk row ini
                    $this->warn("   ✘  DB update gagal ID={$row->id}: " . $e->getMessage());
                    $this->warn("   ↩️  Menghapus " . count($rowFiles) . " file untuk row ini...");
                    foreach ($rowFiles as $f) {
                        Storage::disk('public')->delete($f);
                    }
                    $this->failedImages += count($rowFiles);
                    $this->totalImages  -= count($rowFiles);
                }
            }
        }
    }

    private function rollback(): void
    {
        $fileCount   = count($this->savedFiles);
        $recordCount = count($this->updatedRecords);

        if ($fileCount === 0 && $recordCount === 0) {
            $this->line('   Tidak ada perubahan untuk di-rollback.');
            return;
        }

        // 1. Hapus semua file yang sudah disimpan ke storage
        if ($fileCount > 0) {
            $this->line("   Menghapus {$fileCount} file dari storage...");
            foreach ($this->savedFiles as $file) {
                try {
                    Storage::disk('public')->delete($file);
                } catch (\Throwable) {
                    $this->warn("   Gagal hapus file: {$file}");
                }
            }
            $this->line("   ✔  {$fileCount} file dihapus.");
        }

        // 2. Kembalikan konten DB ke nilai asli
        if ($recordCount > 0) {
            $this->line("   Memulihkan {$recordCount} record di database...");
            $restored = 0;
            foreach ($this->updatedRecords as $entry) {
                try {
                    DB::table($entry['table'])
                        ->where('id', $entry['id'])
                        ->update([$entry['col'] => $entry['original']]);
                    $restored++;
                } catch (\Throwable $e) {
                    $this->warn("   Gagal restore ID={$entry['id']}: " . $e->getMessage());
                }
            }
            $this->line("   ✔  {$restored}/{$recordCount} record dipulihkan.");
        }

        $this->warn("⚠️   Rollback selesai.");
    }

    private function hasBase64Image(string $content): bool
    {
        return str_contains($content, 'data:image/');
    }

    private function mimeToExtension(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/bmp'  => 'bmp',
            default      => 'png',
        };
    }
}
