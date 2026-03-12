<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan perjam untuk migrate base64 images di tabel alerts
Schedule::command('alerts:migrate-base64-images --table=alerts --column=auditorReason')
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auditorReason.log'));


Schedule::command('alerts:migrate-base64-images --table=alerts --column=alertNote')
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/alertNote.log'));

// Cleanup orphaned images setiap hari jam 2 AM (setelah migration malam)
Schedule::command('alerts:cleanup-orphaned-images')
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/cleanup-orphaned.log'));

// Optimize tabel alerts setiap hari jam 3 pagi untuk reclaim ruang setelah migrasi base64
Schedule::command('db:optimize-tables --tables=alerts')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/optimize-tables.log'));
