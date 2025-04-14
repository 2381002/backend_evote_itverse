<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\UserController;

// =========================
// 📱 USER ROUTES
// =========================

Route::prefix('user')->group(function () {
    // ✅ Public Routes (Tanpa Login)
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    // 🔐 Protected Routes (Setelah Login)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);

        // 👥 Ambil semua kandidat
        Route::get('/candidates', [CandidateController::class, 'index']);

        // 🗳️ Voting
        Route::post('/vote', [VoteController::class, 'store']);
        Route::get('/vote/results', [VoteController::class, 'results']);
        Route::get('/vote/check', [VoteController::class, 'check']); // << tambahkan ini

        // ℹ️ Cek status akun
        Route::get('/status', [UserController::class, 'status']);
    });
});


// =========================
// 🛡️ ADMIN ROUTES
// =========================

Route::prefix('admin')->group(function () {
    // ✅ Public (Login)
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);

    // 🔐 Protected (Butuh Auth Admin)
    Route::middleware(['auth:sanctum', 'auth.admin'])->group(function () {
        // 👤 Data admin yang sedang login
        Route::get('/me', [AdminAuthController::class, 'profile']);

        // 👥 Manajemen user
        Route::get('/users', [UserController::class, 'index']);
        Route::patch('/users/{id}/verify', [UserController::class, 'verifyUser']);

        // 🧑‍💼 Manajemen kandidat (CRUD)
        Route::apiResource('/candidates', CandidateController::class);

        // 📊 Hasil voting
        Route::get('/vote/results', [VoteController::class, 'results']);
    });
});
