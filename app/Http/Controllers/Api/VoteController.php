<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vote;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda belum diverifikasi untuk melakukan voting.',
            ], 403);
        }

        // Validasi input kandidat
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        // Cek apakah sudah pernah voting
        if ($user->hasVoted()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan suara sebelumnya.',
            ], 403);            
        }

        // Simpan vote
        $user->vote()->create([
            'candidate_id' => $request->candidate_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Voting berhasil disimpan.',
        ], 201);
    }


    public function check()
    {
        $voted = Vote::where('user_id', Auth::id())->exists();
        return response()->json(['voted' => $voted]);
    }

    public function results(Request $request)
    {
        // Ambil hasil voting
        $results = Vote::select('candidate_id', DB::raw('count(*) as total_votes'))
                    ->groupBy('candidate_id')
                    ->get();

        // Menampilkan hasil voting
        return response()->json($results);
    }
}
