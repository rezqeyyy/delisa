<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Models\Bidan;

class SkriningController extends Controller
{
    /**
     * Menampilkan list skrining untuk bidan.
     */
    public function index()
    {

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
     * Menampilkan halaman detail skrining (Placeholder).
     */
    public function show(Skrining $skrining)
    {
        // Pastikan bidan ini boleh melihat skrining ini (opsional tapi bagus)
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            abort(404);
        }

        // Tampilkan view detailnya
        // return view('bidan.skrining-detail', compact('skrining'));
        
        // Untuk sekarang, kita bisa return teks aja dulu
        return "Ini adalah halaman detail untuk Skrining ID: " . $skrining->id;
    }

    /**
     * Update status skrining menjadi "checked" (AJAX).
     */
    public function markAsViewed(Request $request, Skrining $skrining)
    {
        // Pastikan bidan ini boleh update skrining ini
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update statusnya
        $skrining->update(['checked_status' => true]);

        return response()->json([
            'message' => 'Status updated successfully',
            'redirect_url' => route('bidan.skrining.show', $skrining->id)
        ]);
    }
}