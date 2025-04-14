<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    // GET /candidates - Lihat semua kandidat (yang aktif untuk user, semua untuk admin jika diatur di route)
    public function index(Request $request)
    {
        try {
            // Mengambil kandidat yang aktif
            $candidates = Candidate::where('is_active', true)->get();

            // Mengembalikan respons dengan data kandidat
            return response()->json([
                'success' => true,
                'message' => 'Daftar kandidat berhasil diambil.',
                'data' => $candidates,
            ]);
        } catch (\Exception $e) {
            // Menangani exception jika terjadi kesalahan
            return response()->json([
                'error' => 'Gagal mengambil daftar kandidat',
                'message' => $e->getMessage(),  // Menyertakan pesan error untuk debug
            ], 500);
        }
    }

    // POST /candidates - Tambah kandidat
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $data['photo'] = $path;
        }

        $candidate = Candidate::create($data);

        // Tambahkan URL lengkap ke response
        $candidate->photo_url = $candidate->photo ? asset('storage/' . $candidate->photo) : null;

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil ditambahkan.',
            'data' => $candidate,
        ], 201);
    }

    // GET /candidates/{id} - Lihat detail kandidat
    public function show($id)
    {
        $candidate = Candidate::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail kandidat berhasil diambil.',
            'data' => $candidate,
        ]);
    }

    // PUT /candidates/{id} - Update kandidat
    public function update(Request $request, $id)
    {
        $candidate = Candidate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $validated['photo'] = $path;
        }

        $candidate->update($validated);

        // Tambahkan URL lengkap ke response
        $candidate->photo_url = $candidate->photo ? asset('storage/' . $candidate->photo) : null;

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil diperbarui.',
            'data' => $candidate,
        ]);
    }

    // DELETE /candidates/{id} - Hapus kandidat
    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dihapus.',
        ]);
    }
}
