<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedImages extends Command
{
    protected $signature = 'alerts:cleanup-orphaned-images
                            {--dry-run : Jalankan tanpa menghapus file}
                            {--table=alerts : Nama tabel yang akan dicek}
                            {--background : Jalankan di background}
                            {--log= : Path file log (default: storage/logs/cleanup-orphaned-YYYYMMDD-HHmmss.log)}';

    protected $description = 'Hapus file gambar orphaned (tidak ada referensi di database)';

    private int $totalFiles = 0;
    private int $deletedFiles = 0;
    private int $orphanedFiles = 0;
    private bool $dryRun = false;
    private string $table = 'alerts';
    private string $imagesPath = 'alert-images';

    public function handle(): int
    {
        // Jika --background, spawn ulang dengan nohup lalu keluar
        if ($this->option('background')) {
            $logFile = $this->option('log')
                ?: storage_path('logs/cleanup-orphaned-' . \Carbon\Carbon::now()->format('Ymd-His') . '.log');

            $php     = PHP_BINARY;
            $artisan = base_path('artisan');
            $args    = collect([
                'alerts:cleanup-orphaned-images',
                '--table='  . $this->option('table'),
                $this->option('dry-run') ? '--dry-run' : null,
            ])->filter()->values()->toArray();

            $scriptFile = storage_path('logs/cleanup-runner-' . \Carbon\Carbon::now()->format('Ymd-His') . '.sh');
            $phpEsc     = escapeshellarg($php);
            $artisanEsc = escapeshellarg($artisan);
            $argsEsc    = implode(' ', array_map('escapeshellarg', $args));
            $logEsc     = escapeshellarg($logFile);

            file_put_contents($scriptFile, "#!/bin/sh\n{$phpEsc} {$artisanEsc} {$argsEsc} >> {$logEsc} 2>&1\nrm -f " . escapeshellarg($scriptFile) . "\n");
            chmod($scriptFile, 0755);

            $scriptEsc = escapeshellarg($scriptFile);
            shell_exec("nohup sh {$scriptEsc} > /dev/null 2>&1 &");

            $this->line("✔  Cleanup berjalan di background.");
            $this->line("   Log    : {$logFile}");
            $this->line("   Pantau : tail -f {$logFile}");
            return self::SUCCESS;
        }

        $this->dryRun = $this->option('dry-run');
        $this->table  = $this->option('table');

        if ($this->dryRun) {
            $this->warn('⚠️  DRY-RUN mode aktif — tidak ada file yang akan dihapus.');
        }

        $this->info("Tabel       : <comment>{$this->table}</comment>");
        $this->info("Storage dir : <comment>storage/app/public/{$this->imagesPath}</comment>");
        $this->newLine();

        try {
            $this->cleanupOrphanedFiles();
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("💥  Error: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->line("─────────────────────────────────────────────");
        $this->info("✅  Selesai.");
        $this->line("   Total file       : {$this->totalFiles}");
        $this->line("   Orphaned (tidak ada di DB) : {$this->orphanedFiles}");
        $this->line("   Dihapus          : {$this->deletedFiles}");

        if ($this->dryRun) {
            $this->warn("⚠️  Tidak ada file dihapus (dry-run).");
        }

        return self::SUCCESS;
    }

    private function cleanupOrphanedFiles(): void
    {
        // Ambil semua file dari storage
        $disk  = Storage::disk('public');
        $files = $disk->files($this->imagesPath);

        if (count($files) === 0) {
            $this->line("   Tidak ada file untuk diperiksa.");
            return;
        }

        $this->totalFiles = count($files);
        $this->info("▶  Memeriksa {$this->totalFiles} file...");
        $this->info("▶  Mengambil referensi dari database...");

        // OPTIMIZE: Ambil semua URL yang ada di database dalam satu query
        // Jangan cek per-file (77 queries) — cek semua sekaligus
        $referencedUrls = DB::table($this->table)
            ->select('auditorReason', 'alertNote')
            ->get()
            ->map(function ($row) {
                $urls = [];
                
                // Extract semua /storage/alert-images/* URLs dari auditorReason
                if (preg_match_all('/\/storage\/alert-images\/[^\s"\'<>]+/i', $row->auditorReason, $m)) {
                    $urls = array_merge($urls, $m[0]);
                }
                
                // Extract semua /storage/alert-images/* URLs dari alertNote
                if (preg_match_all('/\/storage\/alert-images\/[^\s"\'<>]+/i', $row->alertNote, $m)) {
                    $urls = array_merge($urls, $m[0]);
                }
                
                return $urls;
            })
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $this->line("   ✔ Ditemukan " . count($referencedUrls) . " URL di database");
        $this->newLine();

        // Konversi ke key-value untuk lookup O(1)
        $referencedSet = array_flip(array_map(fn($url) => '/storage/' . str_replace('/storage/', '', $url), $referencedUrls));

        $bar = $this->output->createProgressBar($this->totalFiles);
        $bar->start();

        foreach ($files as $relativePath) {
            $bar->advance();
            $fullPath = '/storage/' . $relativePath;
            $filename = basename($relativePath);

            // Cek apakah file ini ada di URL list yang direferensi
            if (! isset($referencedSet[$fullPath])) {
                $this->orphanedFiles++;

                if (! $this->dryRun) {
                    try {
                        $disk->delete($relativePath);
                        $this->deletedFiles++;
                        $this->line("   ✔ Dihapus: {$filename}");
                    } catch (\Throwable $e) {
                        $this->warn("   ✘ Gagal hapus {$filename}: " . $e->getMessage());
                    }
                } else {
                    $this->line("   [DRY-RUN] Akan dihapus: {$filename}");
                    $this->deletedFiles++;
                }
            }
        }

        $bar->finish();
        $this->newLine();
    }
}
