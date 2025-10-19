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
        // 1. Dapatkan bidan yg login
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }
        $puskesmasId = $bidan->puskesmas_id;

        // 2. Ambil data skrining di puskesmas tsb,
        //    sertakan relasi ke pasien & user, urutkan terbaru, dan paginasi
        $skrinings = Skrining::where('puskesmas_id', $puskesmasId)
                            ->with(['pasien.user'])
                            ->latest()
                            ->paginate(10); // Angka 10 ini nentuin jumlah data per halaman

        // 3. Kirim data ke view
        return view('bidan.skrining', compact('skrinings'));
    }
}