<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    // Fungsi untuk login admin
    public function login(Request $request)
    {
        // Validasi email dan password
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // Cek apakah admin dengan email tersebut ada
        $admin = Admin::where('email', $credentials['email'])->first();

        // Jika tidak ada admin atau password tidak cocok, kembalikan pesan error
        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return response()->json(['message' => 'Login gagal. Periksa email dan password.'], 401);
        }

        // Buat token untuk admin yang berhasil login
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Kembalikan token dan data admin
        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'admin'   => $admin,
        ]);
    }

    // Fungsi untuk logout admin
    public function logout(Request $request)
    {
        // Menghapus token akses saat ini
        $request->user()->currentAccessToken()->delete();

        // Mengembalikan pesan sukses logout
        return response()->json(['message' => 'Logout berhasil']);
    }

    // Fungsi untuk mendapatkan profil admin yang sedang login
    public function profile(Request $request)
    {
        // Mengembalikan data admin yang sedang login
        return response()->json([
            'admin' => $request->user()
        ]);
    }
}
