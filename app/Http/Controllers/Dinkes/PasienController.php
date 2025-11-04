<?php
namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PasienController extends Controller
{
    public function show($pasienId)
    {
        // Identitas & alamat
        $pasien = DB::table('pasiens as p')
            ->join('users as u','u.id','=','p.user_id')
            ->leftJoin('roles as r','r.id','=','u.role_id')
            ->selectRaw("
                p.*,
                u.name, u.email, u.photo, u.phone, u.address,
                r.nama_role
            ")
            ->where('p.id', $pasienId)
            ->first();

        abort_unless($pasien, 404);

        // Skrining terbaru + status hadir & risiko
        $skrining = DB::table('skrinings')
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')
            ->first();

        // Kondisi kesehatan terbaru (bila ada)
        $kondisi = $skrining
            ? DB::table('kondisi_kesehatans')
                ->where('skrining_id', $skrining->id)
                ->orderByDesc('created_at')
                ->first()
            : null;

        // GPA & riwayat kehamilan
        $gpa = DB::table('riwayat_kehamilan_gpas')
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')->first();

        $riwayatKehamilan = DB::table('riwayat_kehamilans')
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')->limit(10)->get();

        // Ringkasan KF (kunjungan nifas) + pantauan
        $kfSummary = DB::table('kf')
            ->selectRaw('kunjungan_nifas_ke::int as ke, COUNT(*)::int as total')
            ->where('id_nifas', $pasienId)
            ->groupBy('ke')->orderBy('ke')->get();

        $kfPantauan = DB::table('kf')
            ->selectRaw("kesimpulan_pantauan, COUNT(*)::int as total")
            ->where('id_nifas', $pasienId)
            ->groupBy('kesimpulan_pantauan')
            ->pluck('total','kesimpulan_pantauan');

        // Rujukan RS terakhir
        $rujukan = DB::table('rujukan_rs as rr')
            ->leftJoin('rumah_sakits as rs','rs.id','=','rr.rs_id')
            ->selectRaw('rr.*, rs.nama as rs_nama')
            ->where('rr.pasien_id', $pasienId)
            ->orderByDesc('rr.created_at')->limit(5)->get();

        return view('dinkes.dasbor.pasien-show', [
            'pasien'           => $pasien,
            'skrining'         => $skrining,
            'kondisi'          => $kondisi,
            'gpa'              => $gpa,
            'riwayatKehamilan' => $riwayatKehamilan,
            'kfSummary'        => $kfSummary,
            'kfPantauan'       => $kfPantauan,
            'rujukan'          => $rujukan,
        ]);
    }
}
