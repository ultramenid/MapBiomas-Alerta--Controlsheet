<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    // write at most once per this many seconds per session
    private const EVERY = 300;

    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('id') && time() - (int) session('seen_at', 0) >= self::EVERY) {
            DB::table('users')->where('id', session('id'))->update(['last_seen_at' => now('Asia/Jakarta')]);
            session(['seen_at' => time()]);
        }

        return $next($request);
    }
}
