<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PasienNifasController extends Controller
{
    public function index()
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        $puskesmasId = $bidan->puskesmas_id;

        $pasienNifas = DB::table('pasien_nifas_bidan')
            ->join('pasiens', 'pasien_nifas_bidan.pasien_id', '=', 'pasiens.id')
            ->join('users', 'pasiens.user_id', '=', 'users.id')
            ->select(
                'pasien_nifas_bidan.id',
                'pasien_nifas_bidan.pasien_id',
                'pasien_nifas_bidan.tanggal_mulai_nifas as tanggal',
                'pasien_nifas_bidan.created_at',
                'pasiens.nik',
                'users.name as nama_pasien',
                'users.phone as telp',
                'pasiens.PKecamatan as alamat',
                'pasiens.PWilayah as kelurahan'
            )
            ->where('pasien_nifas_bidan.bidan_id', $puskesmasId)
            ->orderBy('pasien_nifas_bidan.created_at', 'desc')
            ->paginate(10);

        $totalPasienNifas = DB::table('pasien_nifas_bidan')
            ->where('bidan_id', $puskesmasId)
            ->count();

        $sudahKFI = 0;
        $belumKFI = $totalPasienNifas - $sudahKFI;

        return view('bidan.pasien-nifas.index', compact(
            'pasienNifas',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
        ));
    }
}