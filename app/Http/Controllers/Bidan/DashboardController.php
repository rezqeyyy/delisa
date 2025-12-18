<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pasien;
use App\Models\Skrining;
use App\Models\Kf;
use App\Models\Bidan;
use App\Models\PasienNifasBidan;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class DashboardController extends Controller
{
    use SkriningHelpers;

    public function index()
    {
        // =========================================================
        // 1) VALIDASI BIDAN LOGIN
        // =========================================================
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        $puskesmasId = $bidan->puskesmas_id;

        // =========================================================
        // 2) SKRINING (MILIK PUSKESMAS BIDAN + SUDAH LENGKAP)
        // =========================================================
        $skriningsQuery = Skrining::query()
            ->where('puskesmas_id', $puskesmasId)
            ->whereHas('puskesmas', function ($q) {
                $q->where('is_mandiri', true);
            })
            ->where('step_form', 6) // ✅ penting: samakan dengan SkriningController
            ->with(['pasien.user', 'riwayatKehamilanGpa', 'kondisiKesehatan'])
            ->orderByDesc('created_at');

        $skriningsRaw = (clone $skriningsQuery)->get();

        // Kalau kamu masih mau pakai helper completeness, boleh.
        // Tapi sekarang harusnya sudah aman karena step_form = 6.
        // Aku tetap taruh biar gak “ngilangin” validasi logika kamu.
        // Dashboard mengikuti "source of truth" status selesai: step_form = 6
        $skrinings = $skriningsRaw->filter(function ($s) {
            // cukup pastikan pasien & user ada, karena untuk tabel dashboard itu yang paling penting
            return $s->pasien && $s->pasien->user;
        })->values();

        // 🔍 DEBUG: biar ketahuan yang kebuang berapa & kenapa
        Log::info('Bidan.Dashboard skrining debug', [
            'user_id' => Auth::id(),
            'bidan_id' => $bidan->id ?? null,
            'puskesmas_id' => $puskesmasId,
            'raw_count_step6' => $skriningsRaw->count(),
            'used_count' => $skrinings->count(),
            'raw_first_ids' => $skriningsRaw->take(5)->pluck('id')->all(),
        ]);


        // =========================================================
        // 3) CARD: ASAL PASIEN (DEPOK vs NON) DARI SKRINING LENGKAP
        // =========================================================
        $pasienIds   = $skrinings->pluck('pasien_id')->unique();
        $pasienList  = Pasien::whereIn('id', $pasienIds)->get(['PKabupaten']);

        $depok = $pasienList->filter(function ($p) {
            $kab = mb_strtolower(trim($p->PKabupaten ?? ''));
            return $kab !== '' && strpos($kab, 'depok') !== false;
        })->count();

        $nonDepok  = $pasienList->count() - $depok;
        $daerahAsal = (object) ['depok' => $depok, 'non_depok' => $nonDepok];

        // =========================================================
        // 4) CARD: RISIKO PRE-EKLAMPSIA DARI SKRINING LENGKAP
        // =========================================================
        $resikoBeresiko = $skrinings->filter(function ($s) {
            $label = strtolower(trim($s->kesimpulan ?? ''));
            return in_array($label, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);
        })->count();

        $resikoNormal = $skrinings->count() - $resikoBeresiko;

        // =========================================================
        // 5) CARD: HADIR/TIDAK HADIR (VERSI SKRINING) - BIARKAN
        // =========================================================
        $pasienHadir      = $skrinings->filter(fn($s) => optional($s->updated_at)->isToday())->count();
        $pasienTidakHadir = $skrinings->count() - $pasienHadir;

        // =========================================================
        // 6) - 9) STATISTIK NIFAS (DISAMAKAN DENGAN DINKES)
        // Basis = EPISODE RS (pasien_nifas_rs), dipersempit ke pasien yang skrining di klinik bidan
        // =========================================================

        // A) Pasien yang skrining di puskesmas bidan ini dan SUDAH SELESAI (step_form = 6)
        $clinicPasienIds = DB::table('skrinings')
            ->where('puskesmas_id', $puskesmasId)
            ->where('step_form', 6)
            ->distinct()
            ->pluck('pasien_id')
            ->filter()
            ->values()
            ->all();

        // Default nilai (biar aman walau kosong)
        $totalNifas = 0;

        // Ini kita samakan naming-nya dengan Dinkes:
        // - hadir/mangkir = status pemantauan nifas berbasis KF1
        $hadir   = 0;
        $mangkir = 0;

        // Tetap kirim juga yang lama kalau view bidan masih pakai nama ini
        $sudahKf1 = 0;
        $belumKf1 = 0;

        $pemantauanSehat     = 0;
        $pemantauanDirujuk   = 0;
        $pemantauanMeninggal = 0;

        if (!empty($clinicPasienIds)) {

            // B) Ambil LATEST episode nifas RS per pasien (tanpa syarat harus ada KF)
            $rsEpisodesAll = DB::table('pasien_nifas_rs as pnr')
                ->select('pnr.id', 'pnr.pasien_id', 'pnr.created_at', 'pnr.tanggal_melahirkan', 'pnr.tanggal_mulai_nifas')
                ->whereIn('pnr.pasien_id', $clinicPasienIds)
                ->orderBy('pnr.pasien_id')
                ->orderByDesc('pnr.created_at')
                ->get();

            // Map latest episode id per pasien_id
            $latestRsEpisodeIdByPasien = [];
            foreach ($rsEpisodesAll as $ep) {
                if (!isset($latestRsEpisodeIdByPasien[$ep->pasien_id])) {
                    $latestRsEpisodeIdByPasien[$ep->pasien_id] = $ep->id;
                }
            }

            $rsEpisodeIds = array_values(array_unique(array_filter(array_values($latestRsEpisodeIdByPasien))));

            // Total nifas = jumlah episode RS (latest per pasien)
            $totalNifas = count($rsEpisodeIds);
            // Tambahan: "Sudah KF" (episode selesai KF) versi baru
            $sudahKf = 0;

            if (!empty($rsEpisodeIds)) {

                // ---------------------------------------------------------
                // SAMAKAN DETEKSI JENIS KF: angka 1..4 atau string KF1..KF4
                // ---------------------------------------------------------
                $kf1MatchSql = "
                    regexp_replace(upper(coalesce(jenis_kf::text,'')), '[^A-Z0-9]', '', 'g')
                    IN ('1','KF1')
                ";

                $kf4MatchSql = "
                    regexp_replace(upper(coalesce(jenis_kf::text,'')), '[^A-Z0-9]', '', 'g')
                    IN ('4','KF4')
                ";

                // =========================================================
                // BAHARU: "SUDAH KF" (EPISODE) versi revisi kamu
                // Sudah KF = episode yang:
                // - punya record KF4, ATAU
                // - punya salah satu kunjungan dengan kesimpulan_pantauan meninggal/wafat
                // =========================================================
                $sudahKf = DB::table('pasien_nifas_rs as pnr')
                    ->whereIn('pnr.id', $rsEpisodeIds)
                    ->where(function ($q) use ($kf4MatchSql) {
                        // A) Ada KF4
                        $q->whereExists(function ($sub) use ($kf4MatchSql) {
                            $sub->select(DB::raw(1))
                                ->from('kf_kunjungans as kk')
                                ->whereColumn('kk.pasien_nifas_id', 'pnr.id')
                                ->whereRaw($kf4MatchSql);
                        })
                            // ATAU
                            ->orWhereExists(function ($sub) {
                                // B) Pernah meninggal / wafat pada salah satu kunjungan KF
                                $sub->select(DB::raw(1))
                                    ->from('kf_kunjungans as kk')
                                    ->whereColumn('kk.pasien_nifas_id', 'pnr.id')
                                    ->whereRaw("LOWER(COALESCE(kk.kesimpulan_pantauan,'')) IN ('meninggal','wafat')");
                            });
                    })
                    ->distinct('pnr.id')
                    ->count('pnr.id');


                // =========================================================
                // C) HADIR/MANGKIR (PEMANTAUAN NIFAS) = sama dengan Dinkes
                // Hadir   = punya KF1
                // Mangkir = belum KF1 dan sudah lewat due (base_date + 2 hari)
                // =========================================================

                // 1) Hadir: episode punya KF1
                $hadir = DB::table('pasien_nifas_rs as pnr')
                    ->whereIn('pnr.id', $rsEpisodeIds)
                    ->whereExists(function ($q) use ($kf1MatchSql) {
                        $q->select(DB::raw(1))
                            ->from('kf_kunjungans as kk')
                            ->whereColumn('kk.pasien_nifas_id', 'pnr.id')
                            ->whereRaw($kf1MatchSql);
                    })
                    ->distinct('pnr.id')
                    ->count('pnr.id');

                // 2) Mangkir: belum KF1 + sudah lewat due KF1
                $mangkir = DB::table('pasien_nifas_rs as pnr')
                    ->whereIn('pnr.id', $rsEpisodeIds)
                    ->whereNotExists(function ($q) use ($kf1MatchSql) {
                        $q->select(DB::raw(1))
                            ->from('kf_kunjungans as kk')
                            ->whereColumn('kk.pasien_nifas_id', 'pnr.id')
                            ->whereRaw($kf1MatchSql);
                    })
                    ->whereRaw("
                        (
                            COALESCE(
                                pnr.tanggal_melahirkan,
                                pnr.tanggal_mulai_nifas,
                                pnr.created_at::date
                            ) + INTERVAL '2 days'
                        ) < NOW()
                    ")
                    ->distinct('pnr.id')
                    ->count('pnr.id');

                // Untuk kompatibilitas view lama bidan:
                // - "sudahKf1" sebelumnya dipakai view sebagai "Sudah Selesai KF" (padahal dulu isinya KF1)
                // - sekarang kita pakai sesuai definisi baru: "Sudah KF" (KF4 ATAU meninggal/wafat)
                $sudahKf1 = $sudahKf;

                // "belumKf1" juga diset ulang sebagai sisa episode yang belum masuk kategori "Sudah KF"
                $belumKf1 = max(0, $totalNifas - $sudahKf1);

                // Tambahan: supaya view bisa pakai nama yang lebih jelas juga
                // (kalau nanti kamu mau rapihin variable di blade)


                // =========================================================
                // D) PEMANTAUAN KF (PER RIWAYAT, MENINGGAL DISTINCT) = sama Dinkes
                // =========================================================
                $basePemantauan = DB::table('kf_kunjungans as kk')
                    ->whereIn('kk.pasien_nifas_id', $rsEpisodeIds);

                // 1) Total raw
                $totalKfRaw = (clone $basePemantauan)->count();

                // 2) Dirujuk per baris
                $pemantauanDirujuk = (clone $basePemantauan)
                    ->whereRaw("LOWER(COALESCE(kk.kesimpulan_pantauan,'')) = 'dirujuk'")
                    ->count();

                // 3a) Meninggal raw rows
                $meninggalRowsRaw = (clone $basePemantauan)
                    ->whereRaw("LOWER(COALESCE(kk.kesimpulan_pantauan,'')) IN ('meninggal','wafat')")
                    ->count();

                // 3b) Meninggal distinct per episode
                $pemantauanMeninggal = (clone $basePemantauan)
                    ->whereRaw("LOWER(COALESCE(kk.kesimpulan_pantauan,'')) IN ('meninggal','wafat')")
                    ->distinct('kk.pasien_nifas_id')
                    ->count('kk.pasien_nifas_id');

                // 4) Buang duplikat meninggal dari total efektif
                $dupMeninggal     = max(0, $meninggalRowsRaw - $pemantauanMeninggal);
                $totalKfEffective = max(0, $totalKfRaw - $dupMeninggal);

                // 5) Sehat = sisa dari total efektif
                $pemantauanSehat = max(0, $totalKfEffective - $pemantauanDirujuk - $pemantauanMeninggal);
            }
        }

        // =========================================================
        // 10) TABEL: 5 DATA SKRINING TERBARU
        // =========================================================
        $pasienTerbaru = $skrinings->sortByDesc('created_at')->take(5)->values();

        // =========================================================
        // 11) RETURN VIEW
        // =========================================================
        return view('bidan.dashboard', compact(
            'daerahAsal',
            'resikoNormal',
            'resikoBeresiko',
            'pasienHadir',
            'pasienTidakHadir',

            // nifas
            'totalNifas',
            'sudahKf',     // ✅ baru
            'sudahKf1',
            'belumKf1',

            // hadir/mangkir (pemantauan nifas KF1)
            'hadir',
            'mangkir',

            // pemantauan KF
            'pemantauanSehat',
            'pemantauanDirujuk',
            'pemantauanMeninggal',

            // tabel
            'pasienTerbaru'
        ));
    }
}
