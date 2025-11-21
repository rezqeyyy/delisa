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
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
            ->selectRaw("
                p.*,
                u.name, u.email, u.photo, u.phone, u.address,
                r.nama_role
            ")
            ->where('p.id', $pasienId)
            ->first();

        abort_unless($pasien, 404);

        // ===================== Skrining TERBARU + tanggal terformat =====================
        // Catatan: alias 'tanggal' dan 'tanggal_waktu' agar langsung kebaca di blade
        $skrining = DB::table('skrinings as s')
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 's.puskesmas_id')
            ->where('s.pasien_id', $pasienId)
            ->orderByDesc('s.created_at')
            ->selectRaw("
                s.*,
                pk.nama_puskesmas as puskesmas_nama,
                to_char(COALESCE(s.created_at, s.updated_at), 'DD/MM/YYYY')         as tanggal,
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
            ->leftJoin('rumah_sakits as rs', 'rs.id', '=', 'rr.rs_id')
            ->selectRaw('rr.*, rs.nama as rs_nama')
            ->where('rr.pasien_id', $pasienId)
            ->orderByDesc('rr.created_at')
            ->limit(5)
            ->get();

        // ===================== Riwayat Penyakit (skrining terbaru) =====================
        $riwayatPenyakit = [];
        $penyakitLainnya = null;

        if ($skrining) {
            // Mapping kode -> nama pertanyaan (harus sama dengan controller pasien)
            $map = [
                'hipertensi'  => 'Hipertensi',
                'alergi'      => 'Alergi',
                'tiroid'      => 'Tiroid',
                'tb'          => 'TB',
                'jantung'     => 'Jantung',
                'hepatitis_b' => 'Hepatitis B',
                'jiwa'        => 'Jiwa',
                'autoimun'    => 'Autoimun',
                'sifilis'     => 'Sifilis',
                'diabetes'    => 'Diabetes',
                'asma'        => 'Asma',
                'lainnya'     => 'Lainnya',
            ];

            // Ambil kuisioner yang relevan (status_soal = individu)
            $kuisioner = DB::table('kuisioner_pasiens')
                ->where('status_soal', 'individu')
                ->whereIn('nama_pertanyaan', array_values($map))
                ->get(['id', 'nama_pertanyaan'])
                ->keyBy('nama_pertanyaan');

            // Ambil jawaban untuk skrining tersebut
            $jawaban = DB::table('jawaban_kuisioners')
                ->where('skrining_id', $skrining->id)
                ->whereIn('kuisioner_id', $kuisioner->pluck('id')->all())
                ->get(['kuisioner_id', 'jawaban', 'jawaban_lainnya'])
                ->keyBy('kuisioner_id');

            foreach ($map as $code => $nama) {
                $qid = optional($kuisioner->get($nama))->id;
                $row = $qid ? $jawaban->get($qid) : null;

                if ($row && $row->jawaban) {
                    if ($code === 'lainnya') {
                        $penyakitLainnya = $row->jawaban_lainnya;
                    } else {
                        $riwayatPenyakit[] = $nama;
                    }
                }
            }
        }

        return view('dinkes.pasien.pasien-show', [
            'pasien'            => $pasien,
            'skrining'          => $skrining,      // punya ->tanggal & ->tanggal_waktu
            'kondisi'           => $kondisi,
            'gpa'               => $gpa,
            'riwayatKehamilan'  => $riwayatKehamilan,
            'kfSummary'         => $kfSummary,
            'kfPantauan'        => $kfPantauan,
            'rujukan'           => $rujukan,
            'riwayatPenyakit'   => $riwayatPenyakit,
            'penyakitLainnya'   => $penyakitLainnya,
        ]);
    }
}
