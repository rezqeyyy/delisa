<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\PasienNifas;
use App\Models\PasienPreEklampsia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Data Daerah Asal Pasien (berdasarkan PKabupaten)
        $pasienDepok = Pasien::where('PKabupaten', 'LIKE', '%Depok%')->count();
        $pasienNonDepok = Pasien::where('PKabupaten', 'NOT LIKE', '%Depok%')
            ->orWhereNull('PKabupaten')
            ->count();

        // Data Resiko Eklampsia (sesuaikan dengan kolom yang ada)
        // Jika tidak ada kolom resiko, gunakan data dummy atau sesuaikan
        $pasienNormal = 0;
        $pasienBeresikoEklampsia = 0;

        // Data Pasien Hadir (sesuaikan dengan kolom status_perkawinan atau status lain)
        $pasienHadir = 0;
        $pasienTidakHadir = 0;

        // Data Pasien Nifas
        $totalPasienNifas = PasienNifas::count();
        $sudahKF1 = 0; // Sesuaikan dengan kolom yang ada

        // Data Pemantauan (sesuaikan dengan kolom yang ada)
        $pemantauanSehat = 0;
        $pemantauanDirujuk = 0;
        $pemantauanMeninggal = 0;

        // Data Pasien Pre Eklampsia (5 terbaru) - dari tabel pasiens
        $pasienPreEklampsia = Pasien::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($item) {
                return [
                    'id_pasien' => $item->id,
                    'nama' => $item->nik ?? 'Anon Dadang',
                    'tanggal' => Carbon::parse($item->tanggal_lahir)->format('d/m/Y'),
                    'status' => $item->PKabupaten ?? 'N/A',
                    'no_telp' => $item->no_jkn ?? '0000000000',
                    'klasifikasi' => 'Beresiko'
                ];
            });

        return view('rs.dashboard', compact(
            'pasienDepok',
            'pasienNonDepok',
            'pasienNormal',
            'pasienBeresikoEklampsia',
            'pasienHadir',
            'pasienTidakHadir',
            'totalPasienNifas',
            'sudahKF1',
            'pemantauanSehat',
            'pemantauanDirujuk',
            'pemantauanMeninggal',
            'pasienPreEklampsia'
        ));
    }
}