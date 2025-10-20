<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Models\Puskesmas;

class SkriningController extends Controller
{
    public function create(Request $request)
    {
        $puskesmasId = (int) $request->query('puskesmas_id');
        $puskesmas   = $puskesmasId ? Puskesmas::find($puskesmasId) : null;
        return view('pasien.skrining-create');
    }

    public function riwayatKehamilan(Request $request)
    {
        return view('pasien.skrining-riwayat-kehamilan');
    }

    public function show(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);
        return view('pasien.skrining-show', compact('skrining'));
    }

    public function edit(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);
        return view('pasien.skrining-edit', compact('skrining'));
    }
    
    public function puskesmasSearch(Request $request)
    {
        $q = trim($request->query('q', ''));

        $rows = Puskesmas::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where('nama_puskesmas', 'like', "%{$q}%")
                   ->orWhere('kecamatan', 'like', "%{$q}%")
                   ->orWhere('lokasi', 'like', "%{$q}%");
            })
            ->orderBy('nama_puskesmas')
            ->limit(20)
            ->get(['id', 'nama_puskesmas', 'kecamatan']);

        return response()->json($rows);
    }

    private function authorizeAccess(Skrining $skrining): void
    {
        $userPasienId = optional(Auth::user()->pasien)->id;
        abort_unless($skrining->pasien_id === $userPasienId, 403);
    }
}