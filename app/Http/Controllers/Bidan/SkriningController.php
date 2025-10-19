<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Models\Bidan;
use Illuminate\Support\Carbon; // <-- Import Carbon untuk format tanggal

class SkriningController extends Controller
{
    /**
     * Menampilkan list skrining untuk bidan.
     */
    public function index()
    {
        // ... (Kode method index() kamu) ...
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }
        $puskesmasId = $bidan->puskesmas_id;
        $skrinings = Skrining::where('puskesmas_id', $puskesmasId)
                            ->with(['pasien.user'])
                            ->latest()
                            ->paginate(10); 
        return view('bidan.skrining', compact('skrinings'));
    }

    /**
     * Menampilkan halaman detail skrining.
     */
    public function show(Skrining $skrining) // <-- UBAH METHOD INI
    {
        // Pastikan bidan ini boleh melihat skrining ini
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            abort(404);
        }

        // Eager load semua relasi yang dibutuhkan untuk tabel detail
        $skrining->load(['pasien.user', 'kondisiKesehatan', 'riwayatKehamilanGpa']);

        // Tampilkan view detailnya (file baru di langkah 6)
        return view('bidan.skrining-show', compact('skrining'));
    }

    /**
     * Update status skrining menjadi "checked" (AJAX).
     */
    public function markAsViewed(Request $request, Skrining $skrining)
    {
        // ... (Kode method markAsViewed() kamu) ...
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $skrining->update(['checked_status' => true]);
        return response()->json([
            'message' => 'Status updated successfully',
            'redirect_url' => route('bidan.skrining.show', $skrining->id)
        ]);
    }

    // --- TAMBAHKAN METHOD BARU INI ---

    /**
     * Update status "tindak_lanjut" skrining (Tombol "Sudah Diperiksa").
     */
    public function followUp(Request $request, Skrining $skrining)
    {
        // Pastikan bidan ini boleh update
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            abort(403);
        }

        // Update status "tindak_lanjut"
        $skrining->update(['tindak_lanjut' => true]);

        // Redirect kembali ke halaman detail dengan pesan sukses
        return redirect()->route('bidan.skrining.show', $skrining->id)
                         ->with('success', 'Skrining telah ditandai selesai diperiksa.');
    }
}