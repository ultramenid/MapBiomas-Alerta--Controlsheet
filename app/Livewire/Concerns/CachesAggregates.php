<?php

namespace App\Livewire\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

trait CachesAggregates
{
    /**
     * Cache::remember that degrades to direct computation when the configured
     * cache store is unavailable (e.g. the database cache table does not
     * exist in this environment), so pages keep working instead of erroring.
     */
    protected function cached(string $key, int $ttl, Closure $compute): mixed
    {
        try {
            return Cache::remember($key, $ttl, $compute);
        } catch (\Throwable) {
            return $compute();
        }
    }

    protected function forgetCached(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Throwable) {
            // cache store unavailable — nothing to invalidate
        }
    }
}
