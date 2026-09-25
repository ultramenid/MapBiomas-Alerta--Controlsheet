<?php

namespace App\Providers;

use App\Http\Middleware\checkLevel;
use App\Http\Middleware\checkRole;
use App\Http\Middleware\checkSession;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use UniSharp\LaravelFilemanager\Events\FileIsMoving;
use UniSharp\LaravelFilemanager\Events\FileIsRenaming;
use UniSharp\LaravelFilemanager\Events\FileIsUploading;

class AppServiceProvider extends ServiceProvider
{
    /** Only extensions the filemanager is meant to hold (see lfm.valid_mime). */
    const LFM_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // /livewire/update skips route middleware; re-apply the page route's guards on every action.
        Livewire::addPersistentMiddleware([
            checkSession::class,
            checkLevel::class,
            checkRole::class,
        ]);

        // LFM only blocks a few executable extensions, and renaming an extensionless
        // upload to *.php slips through. Allowlist the final file name instead.
        Event::listen(
            [FileIsUploading::class, FileIsRenaming::class, FileIsMoving::class],
            function ($event) {
                $path = method_exists($event, 'newPath') ? $event->newPath() : $event->path();
                abort_unless(
                    in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::LFM_EXTENSIONS, true),
                    403,
                    'File type not allowed.'
                );
            }
        );
    }
}
