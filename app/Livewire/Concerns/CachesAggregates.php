<?php

namespace App\Livewire\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

trait CachesAggregates
{
    /**
     * Serve a cached aggregate, computing it once on a miss.
     *
     * Splits the read from the compute so a failing query never runs twice
     * (a plain try/Cache::remember fallback would re-run the closure on a
     * query error), and guards a miss with an atomic lock so concurrent
     * refreshes share one recompute instead of stampeding the aggregate
     * queries. Degrades to unlocked compute+put when the cache store or the
     * lock store is unavailable, so pages keep working instead of erroring.
     *
     * Note: values are assumed non-null (all callers cache Eloquent/DB
     * collections); null is treated as a miss and is therefore not cacheable.
     */
    protected function cached(string $key, int $ttl, Closure $compute): mixed
    {
        // Fast path: serve a hit without touching the lock.
        try {
            $hit = Cache::get($key);
            if ($hit !== null) {
                return $hit;
            }
        } catch (\Throwable) {
            // store unreadable — compute once, best-effort store
            return $this->computeAndStore($key, $ttl, $compute);
        }

        // Miss: acquire an atomic lock so only one process recomputes; the
        // rest wait, then recheck the cache. Falls back to an unlocked
        // compute+put if the lock store isn't available (e.g. the
        // cache_locks table is missing), so caching still works — just
        // without stampede protection.
        try {
            $lock = Cache::lock("{$key}:lock", 30);
            $lock->block(5);
        } catch (\Throwable) {
            return $this->computeAndStore($key, $ttl, $compute);
        }

        try {
            // recheck after acquiring — the lock holder likely just filled it
            $hit = Cache::get($key);
            if ($hit !== null) {
                return $hit;
            }
            return $this->computeAndStore($key, $ttl, $compute);
        } finally {
            try {
                $lock->release();
            } catch (\Throwable) {
                // release failure is harmless; the lock auto-expires
            }
        }
    }

    /**
     * Run the compute closure exactly once and best-effort persist it.
     */
    protected function computeAndStore(string $key, int $ttl, Closure $compute): mixed
    {
        $value = $compute();
        try {
            Cache::put($key, $value, $ttl);
        } catch (\Throwable) {
            // store unwritable — return the value uncached
        }
        return $value;
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