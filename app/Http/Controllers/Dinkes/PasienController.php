<?php
namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PasienController extends Controller
{
    public function show($pasienId)
    {
        // ===================== Identitas & alamat =====================
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

        // ===================== Skrining TERBARU + tanggal terformat =====================
        // Catatan: alias 'tanggal' agar langsung kebaca di blade (mis. {{ $skrining->tanggal ?? '—' }})
        $skrining = DB::table('skrinings as s')
            ->where('s.pasien_id', $pasienId)
            ->orderByDesc('s.created_at')
            ->selectRaw("
                s.*,
                to_char(COALESCE(s.created_at, s.updated_at), 'DD/MM/YYYY')      as tanggal,
                to_char(COALESCE(s.created_at, s.updated_at), 'DD/MM/YYYY HH24:MI') as tanggal_waktu
            ")
            ->first();

        // ===================== Kondisi kesehatan terbaru (jika ada) =====================
        $kondisi = $skrining
            ? DB::table('kondisi_kesehatans')
                ->where('skrining_id', $skrining->id)
                ->orderByDesc('created_at')
                ->first()
            : null;

        // ===================== GPA & riwayat kehamilan =====================
        $gpa = DB::table('riwayat_kehamilan_gpas')
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')
            ->first();

        $riwayatKehamilan = DB::table('riwayat_kehamilans')
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // ===================== Episode Nifas milik pasien =====================
        $episodeIdsQuery = DB::table('pasien_nifas_bidan')
            ->where('pasien_id', $pasienId)
            ->select('id')
            ->union(
                DB::table('pasien_nifas_rs')
                    ->where('pasien_id', $pasienId)
                    ->select('id')
            );

        // ========= Ringkasan KF (kunjungan nifas) =========
        $kfSummary = DB::table('kf as k')
            ->whereIn('k.id_nifas', $episodeIdsQuery)
            ->selectRaw('k.kunjungan_nifas_ke::int as ke, COUNT(*)::int as total')
            ->groupBy('ke')
            ->orderBy('ke')
            ->get();

        // ========= Ringkasan kesimpulan pantauan =========
        $kfPantauan = DB::table('kf as k')
            ->whereIn('k.id_nifas', $episodeIdsQuery)
            ->selectRaw("k.kesimpulan_pantauan, COUNT(*)::int as total")
            ->groupBy('k.kesimpulan_pantauan')
            ->pluck('total', 'kesimpulan_pantauan');

        // ===================== Rujukan RS terakhir =====================
        $rujukan = DB::table('rujukan_rs as rr')
            ->leftJoin('rumah_sakits as rs','rs.id','=','rr.rs_id')
            ->selectRaw('rr.*, rs.nama as rs_nama')
            ->where('rr.pasien_id', $pasienId)
            ->orderByDesc('rr.created_at')
            ->limit(5)
            ->get();

        return view('dinkes.dasbor.pasien-show', [
            'pasien'           => $pasien,
            'skrining'         => $skrining,      // punya ->tanggal & ->tanggal_waktu
            'kondisi'          => $kondisi,
            'gpa'              => $gpa,
            'riwayatKehamilan' => $riwayatKehamilan,
            'kfSummary'        => $kfSummary,
            'kfPantauan'       => $kfPantauan,
            'rujukan'          => $rujukan,
        ]);
    }
}
