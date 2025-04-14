<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;  // Pastikan Anda mengimpor model Admin
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil pengguna yang sedang terautentikasi
        $user = $request->user();

        // Jika tidak ada pengguna yang terautentikasi, kembalikan akses ditolak
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Ambil admin yang terdaftar di tabel admins
        $admin = Admin::first();  // Anda hanya memiliki satu admin di tabel admins

        // Jika tidak ada admin atau admin yang terdaftar tidak cocok dengan pengguna yang sedang terautentikasi
        if (!$admin || $user->id !== $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk admin',
            ], 403);
        }

        // Jika pengguna adalah admin, lanjutkan ke permintaan berikutnya
        return $next($request);
    }
}
