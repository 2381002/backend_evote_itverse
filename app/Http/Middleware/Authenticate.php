<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * Jika pengguna tidak diautentikasi, mereka akan mendapatkan response dengan status kode 401 dan pesan yang lebih informatif.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Cek apakah request mengharapkan JSON
        if ($request->expectsJson()) {
            // Menambahkan log untuk melacak siapa yang gagal otentikasi
            Log::warning('Unauthorized access attempt.', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'route' => $request->route()->getName(),
            ]);

            // Kirim response JSON yang lebih detail
            return response()->json([
                'success' => false,
                'message' => 'You are not authenticated. Please log in to proceed.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Untuk request yang tidak mengharapkan JSON, arahkan mereka ke halaman login biasa
        return route('login');
    }
}
