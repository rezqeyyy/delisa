<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Skrining;
use Illuminate\Http\Request;

class SkriningController extends Controller
{
    public function index()
    {
        // Ambil semua data skrining dengan relasi pasien
        $skrinings = Skrining::with('pasien')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rs.skrining.index', compact('skrinings'));
    }

    public function show($id)
    {
        // Detail skrining
        $skrining = Skrining::with(['pasien', 'kondisiKesehatan', 'jawabanKuisioners', 'riwayatKehamilans'])
            ->findOrFail($id);

        return view('rs.skrining.show', compact('skrining'));
    }
}