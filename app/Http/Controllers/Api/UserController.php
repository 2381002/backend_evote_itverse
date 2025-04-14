<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Vote;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Mendaftar pengguna baru.
     */
    public function register(Request $request)
    {
        // Validasi data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Membuat pengguna baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'voter', // Default role 'voter'
            'is_verified' => false, // Pengguna baru belum diverifikasi
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    /**
     * Login pengguna.
     */
    public function login(Request $request)
    {
        // Validasi login
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifikasi kredensial
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Jika pengguna belum diverifikasi
        if (!$user->is_verified) {
            return response()->json(['message' => 'User not verified'], 403);
        }

        // Mengeluarkan token
        $token = $user->createToken('VotingApp')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Verifikasi pengguna (hanya admin yang bisa mengakses).
     */
    public function verifyUser($id)
    {
        // Temukan pengguna dengan ID
        $user = User::find($id);

        // Jika pengguna tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        // Cek apakah pengguna sudah diverifikasi
        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna sudah diverifikasi sebelumnya.',
            ], 400);
        }

        // Verifikasi pengguna
        $user->is_verified = true;
        $user->save();

        // Menambahkan log untuk mencatat aksi verifikasi
        Log::info('User verified successfully.', [
            'user_id' => $user->id,
            'admin' => auth()->user()->id,  // Menyimpan ID admin yang melakukan verifikasi
            'timestamp' => now(),
        ]);

        // Respons sukses
        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diverifikasi.',
            'data' => $user
        ]);
    }


    public function status(Request $request)
    {
        $user = $request->user(); // Mendapatkan pengguna dari token yang ada di header

        if (!$user) {
            return response()->json(['message' => 'Anda perlu login terlebih dahulu.'], 401);
        }

        // Memeriksa status verifikasi pengguna
        if (!$user->is_verified) {
            return response()->json(['message' => 'Akun Anda belum diverifikasi.'], 403);
        }

        // Mengembalikan data pengguna jika sudah diverifikasi
        return response()->json(['user' => $user], 200);
    }

    // UserController.php

    /**
     * Vote oleh pengguna yang terverifikasi.
     */
    public function vote(Request $request)
    {
        $user = $request->user(); // Mendapatkan pengguna dari token yang ada di header

        // Memeriksa apakah pengguna terverifikasi
        if (!$user->is_verified) {
            return response()->json(['message' => 'Akun Anda belum diverifikasi. Anda tidak dapat memberikan suara.'], 403);
        }

        // Proses voting, misalnya simpan suara ke dalam database
        // Misalnya Anda ingin menyimpan ID kandidat yang dipilih oleh pengguna
        $vote = Vote::create([
            'user_id' => $user->id,
            'candidate_id' => $request->candidate_id,
        ]);

        return response()->json(['message' => 'Terima kasih atas suara Anda.'], 200);
    }


    /**
     * Daftar pengguna (hanya admin yang bisa mengakses).
     */
    public function index(Request $request)
    {
        // Dapatkan semua user dengan informasi terbatas
        $users = User::select('id', 'name', 'email', 'is_verified', 'created_at', 'role')->get();

        return response()->json($users);
    }
}

