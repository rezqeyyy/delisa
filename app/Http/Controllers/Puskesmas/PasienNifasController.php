<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasienNifasController extends Controller
{
    public function index()
    {
            // Gunakan DB query builder langsung dengan kolom yang benar
            $pasienNifas = DB::table('pasien_nifas_rs')
        ->join('pasiens', 'pasien_nifas_rs.pasien_id', '=', 'pasiens.id')
        ->join('users', 'pasiens.user_id', '=', 'users.id') // Join ke users
        ->join('rumah_sakits', 'pasien_nifas_rs.rs_id', '=', 'rumah_sakits.id')
        ->select(
            'pasien_nifas_rs.id',
            'pasien_nifas_rs.pasien_id',
            'pasien_nifas_rs.tanggal_mulai_nifas as tanggal',
            'pasien_nifas_rs.created_at',
            'pasiens.nik',
            'users.name as nama_pasien', // Ambil nama dari users
            'pasiens.tanggal_lahir',
            'pasiens.PKecamatan as alamat',
            'pasiens.PKabupaten',
            'rumah_sakits.nama as nama_rs'
        )
        ->orderBy('pasien_nifas_rs.created_at', 'desc')
        ->paginate(10);

        $totalPasienNifas = DB::table('pasien_nifas_rs')->count();
        $sudahKFI = 0;
        $belumKFI = $totalPasienNifas;

        return view('puskesmas.pasien-nifas.index', compact(
            'pasienNifas',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
        ));
    }
}