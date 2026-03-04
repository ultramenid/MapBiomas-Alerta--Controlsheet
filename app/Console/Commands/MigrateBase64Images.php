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
                            {--table=alerts : Nama tabel yang akan diproses (default: alerts)}';

    protected $description = 'Migrasi base64 images dari database ke file system dan update URL di database';

    // Prefix yang dicari di dalam src attribute
    private const BASE64_PREFIX = 'data:image/';

    private int $totalImages = 0;
    private int $totalRecords = 0;
    private int $failedImages = 0;
    private bool $dryRun = false;
    private string $table = 'alerts';

    public function handle(): int
    {
        // Gambar base64 bisa sangat besar — hapus batas memory untuk command ini
        ini_set('memory_limit', '-1');

        $this->dryRun = $this->option('dry-run');
        $column     = $this->option('column');
        $chunkSize  = (int) $this->option('chunk');
        $this->table = $this->option('table');

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

        foreach ($columns as $col) {
            $this->info("▶  Memproses kolom: <comment>{$col}</comment>");

            DB::table($this->table)
                ->whereNotNull($col)
                ->where($col, '!=', '')
                ->where($col, 'LIKE', '%data:image/%')
                ->orderBy('id')
                ->chunk($chunkSize, function ($rows) use ($col) {
                    foreach ($rows as $row) {
                        $this->processRow($row, $col);
                    }
                });
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
                DB::table($this->table)
                    ->where('id', $row->id)
                    ->update([$col => $newContent]);
            }
        }
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
