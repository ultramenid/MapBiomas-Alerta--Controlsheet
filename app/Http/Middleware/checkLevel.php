<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkLevel
{
    /**
     * Admin only (role_id 0). Strict check: a missing role must not read as 0.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('role_id') || (int) session('role_id') !== 0) {
            return redirect('/dashboard');
        }
        return $next($request);
    }
}
