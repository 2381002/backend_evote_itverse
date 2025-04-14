<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        // Pastikan pengguna terautentikasi dan memiliki hak akses admin
        if (!$user || !$user->is_admin) {
            return response()->json([
                'error' => 'Access forbidden. Admin rights are required.'
            ], 403);
        }

        return $next($request);
    }
}
    