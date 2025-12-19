<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\PasienNifasRs;
use App\Models\Skrining;
use App\Models\RumahSakit;
use App\Models\RujukanRs;
use App\Models\KfKunjungan; // ✅ Tambahan: model pemantauan KF yang baru
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // RS yang sedang login
        $rsId = $this->getRsId();

        /**
         * ==============================
         * 1. BASIS DATA RUJUKAN RS
         * ==============================
         */
        $allRujukanForRs = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->get();

        $acceptedRujukan = $allRujukanForRs->where('done_status', true);
        $pendingRujukan  = $allRujukanForRs->where('done_status', false);

        /**
         * ==============================
         * 2. DATA PASIEN RUJUKAN / NON RUJUKAN
         * ==============================
         */
        $acceptedPatientIds = $acceptedRujukan
            ->pluck('pasien_id')
            ->filter()
            ->unique();

        $pendingPatientIds = $pendingRujukan
            ->pluck('pasien_id')
            ->filter()
            ->unique();

        // Pasien yang hanya punya rujukan pending dan belum pernah selesai
        $pasienRujukan = $acceptedPatientIds->count();

        // ✅ Rujukan Menunggu Konfirmasi = semua rujukan pending (done_status=false) seperti halaman rujukan RS
        $rujukanMenungguKonfirmasi = $pendingPatientIds->count();

        /**
         * ==============================
         * 3. DATA PASIEN RUJUKAN (RISIKO) - SINKRON DENGAN TABEL
         * ==============================
         * ⚠️ Stat risiko mengikuti dataset query tabel (peBase) yang akan dibuat di BLOK 7.
         * Jadi di sini cukup inisialisasi, nanti di BLOK 7 kita isi angkanya.
         */
        $rujukanSetelahMelahirkan = 0; // nanti dihitung dari KF TERBARU di blok nifas

        $rujukanBeresiko     = 0;
        $resikoNormal        = 0;
        $resikoPreeklampsia  = 0;


        /**
         * ==============================
         * 4. PASIEN HADIR / TIDAK HADIR (PEMANTAUAN NIFAS)
         * ==============================
         * Hadir  = episode yang sudah mengisi KF1
         * Tidak  = episode yang belum KF1 dan sudah melewati batas waktu KF1
         *
         * Nilai dihitung pada BLOK 5 (karena berbasis pasien_nifas_rs + kf_kunjungans)
         */
        $pasienHadir = 0;
        $pasienTidakHadir = 0;


        /**
         * ==============================
         * 5 & 6. DATA PASIEN NIFAS (RS)
         * ==============================
         */
        $totalNifas = PasienNifasRs::where('rs_id', $rsId)->count();

        // Ambil ID nifas RS (primary key pasien_nifas_rs) - khusus RS yang login
        $pasienNifasIds = PasienNifasRs::query()
            ->where('rs_id', $rsId)
            ->pluck('id');

        // Inisialisasi default
        $sudahKF1            = 0;   // ⚠️ dipakai sebagai "Sudah KF" (sesuai definisi terbaru)
        $pemantauanSehat     = 0;
        $pemantauanDirujuk   = 0;
        $pemantauanMeninggal = 0;

        $pasienHadir = 0;           // Hadir Pemantauan Nifas
        $pasienTidakHadir = 0;      // Tidak Hadir Pemantauan Nifas

        if ($pasienNifasIds->isNotEmpty()) {

            Log::debug('RS Dashboard - Pasien Nifas RS (RS scoped)', [
                'rs_id'             => $rsId,
                'pasien_nifas_ids'  => $pasienNifasIds->values()->all(),
            ]);

            // ==============================
            // ✅ PASIEN NIFAS YANG SEDANG DIRUJUK
            // Definisi: ambil KF TERBARU per pasien_nifas_id, lalu hitung yang kesimpulan_pantauan = 'Dirujuk'
            // ==============================

            // Subquery: KF TERBARU per pasien_nifas_id (PostgreSQL: DISTINCT ON)
            $latestKfSub = DB::table('kf_kunjungans as k')
                ->selectRaw('DISTINCT ON (k.pasien_nifas_id) k.pasien_nifas_id, k.kesimpulan_pantauan, k.created_at')
                ->whereIn('k.pasien_nifas_id', $pasienNifasIds)
                ->orderBy('k.pasien_nifas_id')
                ->orderByDesc('k.created_at'); // patokan "terbaru" (kalau ada kolom tanggal_kunjungan, boleh pakai itu)

            // Hitung pasien nifas yang KF terbarunya "Dirujuk"
            $rujukanSetelahMelahirkan = DB::query()
                ->fromSub($latestKfSub, 'lk')
                ->whereRaw("LOWER(TRIM(COALESCE(lk.kesimpulan_pantauan,''))) = 'dirujuk'")
                ->count();



            $jenisKf1Candidates = ['KF1', 'kf1', '1', 'kf_1', 'KF 1'];
            $jenisKf4Candidates = ['KF4', 'kf4', '4', 'kf_4', 'KF 4'];

            /**
             * HADIR = episode yang sudah mengisi KF1
             */
            $pasienHadir = KfKunjungan::query()
                ->whereIn('pasien_nifas_id', $pasienNifasIds)
                ->whereIn('jenis_kf', $jenisKf1Candidates)
                ->distinct('pasien_nifas_id')
                ->count('pasien_nifas_id');

            /**
             * "SUDAH KF" = ada KF4 ATAU wafat/meninggal di salah satu kunjungan KF
             */
            $sudahKF1 = DB::table('pasien_nifas_rs as pnr')
                ->where('pnr.rs_id', $rsId)
                ->whereNotNull('pnr.kf1_tanggal')
                ->whereNotNull('pnr.kf2_tanggal')
                ->whereNotNull('pnr.kf3_tanggal')
                ->whereNotNull('pnr.kf4_tanggal')
                ->count();

            /**
             * TIDAK HADIR = belum KF1 dan sudah melewati batas waktu KF1
             * Indikator aman: +2 hari dari tanggal_melahirkan / tanggal_mulai_nifas / created_at
             */
            $pasienTidakHadir = PasienNifasRs::query()
                ->whereIn('id', $pasienNifasIds)
                ->whereNotIn('id', function ($sub) use ($jenisKf1Candidates) {
                    $sub->select('pasien_nifas_id')
                        ->from('kf_kunjungans')
                        ->whereIn('jenis_kf', $jenisKf1Candidates);
                })
                ->whereRaw("
            (COALESCE(tanggal_melahirkan, tanggal_mulai_nifas, created_at::date) + INTERVAL '2 days') < CURRENT_DATE
        ")
                ->count();

            Log::debug('RS Dashboard - Rekap Nifas (RS scoped)', [
                'rs_id'            => $rsId,
                'sudahKF'          => $sudahKF1,
                'hadir_kf1'        => $pasienHadir,
                'tidak_hadir_kf1'  => $pasienTidakHadir,
            ]);


            /**
             * PEMANTAUAN KF (rekap kunjungan)
             */
            $kfBase = KfKunjungan::query()
                ->whereIn('pasien_nifas_id', $pasienNifasIds);

            $pemantauanSehat = (clone $kfBase)
                ->where('kesimpulan_pantauan', 'Sehat')
                ->count();

            $pemantauanDirujuk = (clone $kfBase)
                ->where('kesimpulan_pantauan', 'Dirujuk')
                ->count();

            $pemantauanMeninggal = (clone $kfBase)
                ->where('kesimpulan_pantauan', 'Meninggal')
                ->count();
        }


        /**
         * ==============================
         * 7. TABEL DATA PASIEN RUJUKAN (UNIQUE PASIEN) + AUTO UPDATE SKRINING TERBARU
         * ==============================
         * - Yang tampil hanya 1x per pasien (anti redundan)
         * - Data yang dipakai selalu skrining TERBARU pasien tsb
         * - Filter (NIK/Nama/Tanggal/Risiko) dikenakan ke skrining terbaru itu
         */

        // 7a) Ambil daftar pasien yang PERNAH rujuk ke RS ini (minimal done_status=true sesuai konsep tabel sebelumnya)
        $rujukPasienIdSub = RujukanRs::query()
            ->select('pasien_id')
            ->where('rs_id', $rsId)
            ->where('done_status', true)
            ->whereNotNull('pasien_id')
            ->distinct();

        // 7b) Subquery: skrining TERBARU per pasien (PostgreSQL: DISTINCT ON)
        $latestSkriningSub = DB::table('skrinings as s')
            ->selectRaw('DISTINCT ON (s.pasien_id) s.*')
            ->whereIn('s.pasien_id', $rujukPasienIdSub)
            ->orderBy('s.pasien_id')
            ->orderByDesc('s.created_at');

        // 7c) Base dataset tabel: join latest skrining + pasien + user
        $peBase = DB::query()
            ->fromSub($latestSkriningSub, 'ls')
            ->join('pasiens as p', 'p.id', '=', 'ls.pasien_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->select([
                'ls.id as skrining_id',
                'ls.pasien_id as pasien_id',
                'ls.created_at as skrining_created_at',
                'ls.kesimpulan as skrining_kesimpulan',
                'ls.status_pre_eklampsia as skrining_status_pre_eklampsia',
                'ls.jumlah_resiko_sedang as jumlah_resiko_sedang',
                'ls.jumlah_resiko_tinggi as jumlah_resiko_tinggi',
                'p.nik as nik',
                'p.PKecamatan as PKecamatan',
                'p.PWilayah as PWilayah',
                'u.name as nama',
                'u.phone as phone',
            ]);

        // 7d) Filter NIK
        if ($request->filled('nik')) {
            $peBase->where('p.nik', 'like', '%' . $request->nik . '%');
        }

        // 7e) Filter Nama
        if ($request->filled('nama')) {
            $peBase->where('u.name', 'like', '%' . $request->nama . '%');
        }

        // 7f) Filter Tanggal (berdasarkan tanggal skrining TERBARU)
        if ($request->filled('tanggal_dari')) {
            $peBase->whereDate('ls.created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $peBase->whereDate('ls.created_at', '<=', $request->tanggal_sampai);
        }

        // 7g) Filter Status Risiko (berdasarkan skrining TERBARU)
        if ($request->filled('risiko')) {
            $risikoFilter = $request->risiko;

            if ($risikoFilter === 'Beresiko') {
                $peBase->where(function ($q) {
                    $q->where('ls.jumlah_resiko_tinggi', '>', 0)
                        ->orWhereRaw("LOWER(TRIM(COALESCE(ls.kesimpulan,''))) IN ('beresiko','berisiko','risiko tinggi','tinggi')")
                        ->orWhereRaw("LOWER(TRIM(COALESCE(ls.status_pre_eklampsia,''))) IN ('beresiko','berisiko','risiko tinggi','tinggi')")
                        ->orWhere(function ($qq) {
                            $qq->where('ls.jumlah_resiko_sedang', '>', 0)
                                ->orWhereRaw("LOWER(TRIM(COALESCE(ls.kesimpulan,''))) IN ('waspada','menengah','sedang','risiko sedang')")
                                ->orWhereRaw("LOWER(TRIM(COALESCE(ls.status_pre_eklampsia,''))) IN ('waspada','menengah','sedang','risiko sedang')");
                        });
                });
            }

            if ($risikoFilter === 'Tidak Berisiko') {
                $peBase->where(function ($q) {
                    $q->where('ls.jumlah_resiko_tinggi', '<=', 0)
                        ->where('ls.jumlah_resiko_sedang', '<=', 0)
                        ->whereRaw("LOWER(TRIM(COALESCE(ls.kesimpulan,''))) NOT IN ('beresiko','berisiko','risiko tinggi','tinggi','waspada','menengah','sedang','risiko sedang')")
                        ->whereRaw("LOWER(TRIM(COALESCE(ls.status_pre_eklampsia,''))) NOT IN ('beresiko','berisiko','risiko tinggi','tinggi','waspada','menengah','sedang','risiko sedang')");
                });
            }
        }

        // ==============================
        // SINKRON STAT RISIKO DENGAN DATASET TABEL (peBase) - TERBARU
        // ==============================
        $totalPeDataset = (clone $peBase)->count();

        $countHigh = (clone $peBase)
            ->where(function ($q) {
                $q->where('ls.jumlah_resiko_tinggi', '>', 0)
                    ->orWhereRaw("LOWER(TRIM(COALESCE(ls.kesimpulan, ls.status_pre_eklampsia, ''))) IN ('beresiko','berisiko','risiko tinggi','tinggi')");
            })
            ->count();

        // sedang tapi bukan high
        $countMedOnly = (clone $peBase)
            ->where(function ($q) {
                $q->where('ls.jumlah_resiko_sedang', '>', 0)
                    ->orWhereRaw("LOWER(TRIM(COALESCE(ls.kesimpulan, ls.status_pre_eklampsia, ''))) IN ('waspada','menengah','sedang','risiko sedang')");
            })
            ->where(function ($q) {
                $q->where('ls.jumlah_resiko_tinggi', '<=', 0)
                    ->whereRaw("LOWER(TRIM(COALESCE(ls.kesimpulan, ls.status_pre_eklampsia, ''))) NOT IN ('beresiko','berisiko','risiko tinggi','tinggi')");
            })
            ->count();

        $countRisk = $countHigh + $countMedOnly;

        $rujukanBeresiko    = $countRisk;
        $resikoPreeklampsia = $countRisk;
        $resikoNormal       = max(0, $totalPeDataset - $countRisk);

        // ==============================
        // DATA TABEL (LIMIT 5) - unik pasien + skrining terbaru
        // ==============================
        $pePatients = $peBase
            ->orderByDesc('ls.created_at')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $raw = strtolower(trim(($row->skrining_kesimpulan ?? $row->skrining_status_pre_eklampsia ?? '')));

                $isHigh = ((int)($row->jumlah_resiko_tinggi ?? 0)) > 0
                    || in_array($raw, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);

                $isMed = ((int)($row->jumlah_resiko_sedang ?? 0)) > 0
                    || in_array($raw, ['waspada', 'menengah', 'sedang', 'risiko sedang']);

                return (object) [
                    'id'          => $row->pasien_id,
                    'rujukan_id'  => null, // tidak relevan, karena tabel ini unik pasien
                    'nik'         => $row->nik ?? '-',
                    'nama'        => $row->nama ?? 'Nama Tidak Tersedia',
                    'tanggal'     => $row->skrining_created_at ? \Carbon\Carbon::parse($row->skrining_created_at)->format('d/m/Y') : '-',
                    'alamat'      => $row->PKecamatan ?? $row->PWilayah ?? '-',
                    'telp'        => $row->phone ?? '-',
                    'kesimpulan'  => $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Tidak Berisiko'),

                    // ✅ Auto update skrining: showPasien($id) selalu ambil latestSkrining
                    // Pakai action() biar gak ngandelin nama route yang belum kamu tempel di sini
                    'detail_url'  => action([self::class, 'showPasien'], ['id' => $row->pasien_id]),

                    'process_url' => $row->pasien_id
                        ? route('rs.dashboard.proses-nifas', ['id' => $row->pasien_id])
                        : null,
                ];
            });

        Log::debug('RS Dashboard - Tabel Rujukan (Unique pasien + latest skrining)', [
            'rs_id'         => $rsId,
            'total_dataset' => $totalPeDataset,
            'count_risk'    => $countRisk,
            'sample_ids'    => collect($pePatients)->pluck('id')->values()->all(),
        ]);


        return view('rs.dashboard', compact(
            'rujukanSetelahMelahirkan',
            'rujukanBeresiko',
            'resikoNormal',
            'resikoPreeklampsia',
            'pasienRujukan',
            'rujukanMenungguKonfirmasi',
            'pasienHadir',
            'pasienTidakHadir',
            'totalNifas',
            'sudahKF1',
            'pemantauanSehat',
            'pemantauanDirujuk',
            'pemantauanMeninggal',
            'pePatients'
        ));
    }

    public function showPasien($id)
    {
        try {
            $pasien = Pasien::with(['user', 'skrinings' => function ($q) {
                $q->latest();
            }])->findOrFail($id);

            $skrining = $pasien->latestSkrining;

            // Jika tidak ada skrining, kembalikan view dengan data minimal
            if (!$skrining) {
                return view('rs.show', compact('pasien', 'skrining'));
            }

            // --- Logika dari Puskesmas\SkriningController@show ---

            $skrining->load(['kondisiKesehatan', 'riwayatKehamilanGpa', 'puskesmas']);

            $kk = optional($skrining->kondisiKesehatan);
            $gpa = optional($skrining->riwayatKehamilanGpa);

            $sebabSedang = [];
            $sebabTinggi = [];

            // 1. Usia Ibu
            $umur = null;
            try {
                $tgl = optional($pasien)->tanggal_lahir;
                if ($tgl) {
                    $umur = \Carbon\Carbon::parse($tgl)->age;
                }
            } catch (\Throwable $e) {
                $umur = null;
            }
            if ($umur !== null && $umur >= 35) {
                $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)";
            }

            // 2. Primigravida
            if ($gpa && intval($gpa->total_kehamilan) === 1) {
                $sebabSedang[] = 'Primigravida (G=1)';
            }

            // 3. IMT
            if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) {
                $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)';
            }

            // 4. Tekanan Darah
            $sistol = $kk->sdp ?? null;
            $diastol = $kk->dbp ?? null;
            if (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90)) {
                $sebabTinggi[] = 'Tekanan darah di atas 130/90 mHg';
            }

            // 5. Kuisioner Risiko Sedang
            $preModerateNames = [
                'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
                'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
                'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
                'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
            ];
            $preModerateLabels = [
                $preModerateNames[0] => 'Kehamilan kedua/lebih bukan dengan suami pertama',
                $preModerateNames[1] => 'Teknologi reproduksi berbantu',
                $preModerateNames[2] => 'Jarak 10 tahun dari kehamilan sebelumnya',
                $preModerateNames[3] => 'Riwayat keluarga preeklampsia',
            ];

            $preKuisModerate = DB::table('kuisioner_pasiens')
                ->where('status_soal', 'pre_eklampsia')
                ->whereIn('nama_pertanyaan', $preModerateNames)
                ->get(['id', 'nama_pertanyaan'])
                ->keyBy('nama_pertanyaan');

            $preJawabModerate = DB::table('jawaban_kuisioners')
                ->where('skrining_id', $skrining->id)
                ->whereIn('kuisioner_id', $preKuisModerate->pluck('id')->all())
                ->get(['kuisioner_id', 'jawaban'])
                ->keyBy('kuisioner_id');

            foreach ($preModerateNames as $nm) {
                $id = optional($preKuisModerate->get($nm))->id;
                if ($id && (bool) optional($preJawabModerate->get($id))->jawaban) {
                    $sebabSedang[] = $preModerateLabels[$nm] ?? $nm;
                }
            }

            // 6. Kuisioner Risiko Tinggi
            $preHighNames = [
                'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
                'Apakah kehamilan anda saat ini adalah kehamilan kembar',
                'Apakah anda memiliki diabetes dalam masa kehamilan',
                'Apakah anda memiliki penyakit ginjal',
                'Apakah anda memiliki penyakit autoimun, SLE',
                'Apakah anda memiliki penyakit Anti Phospholipid Syndrome',
            ];
            $preHighLabels = [
                $preHighNames[0] => 'Riwayat preeklampsia sebelumnya',
                $preHighNames[1] => 'Kehamilan kembar',
                $preHighNames[2] => 'Diabetes dalam kehamilan',
                $preHighNames[3] => 'Penyakit ginjal',
                $preHighNames[4] => 'Penyakit autoimun (SLE)',
                $preHighNames[5] => 'Anti Phospholipid Syndrome',
            ];

            $preKuisHigh = DB::table('kuisioner_pasiens')
                ->where('status_soal', 'pre_eklampsia')
                ->whereIn('nama_pertanyaan', $preHighNames)
                ->get(['id', 'nama_pertanyaan'])
                ->keyBy('nama_pertanyaan');

            $preJawabHigh = DB::table('jawaban_kuisioners')
                ->where('skrining_id', $skrining->id)
                ->whereIn('kuisioner_id', $preKuisHigh->pluck('id')->all())
                ->get(['kuisioner_id', 'jawaban'])
                ->keyBy('kuisioner_id');

            foreach ($preHighNames as $nm) {
                $id = optional($preKuisHigh->get($nm))->id;
                if ($id && (bool) optional($preJawabHigh->get($id))->jawaban) {
                    $sebabTinggi[] = $preHighLabels[$nm] ?? $nm;
                }
            }

            // 7. Riwayat Penyakit Pasien & Keluarga
            $riwayatPenyakitPasien = DB::table('jawaban_kuisioners as j')
                ->join('kuisioner_pasiens as k', 'k.id', '=', 'j.kuisioner_id')
                ->where('j.skrining_id', $skrining->id)
                ->where('k.status_soal', 'individu')
                ->where('j.jawaban', true)
                ->select('k.nama_pertanyaan', 'j.jawaban_lainnya')
                ->get()
                ->map(fn($r) => ($r->nama_pertanyaan === 'Lainnya' && $r->jawaban_lainnya) ? ('Lainnya: ' . $r->jawaban_lainnya) : $r->nama_pertanyaan)
                ->values()->all();

            $riwayatPenyakitKeluarga = DB::table('jawaban_kuisioners as j')
                ->join('kuisioner_pasiens as k', 'k.id', '=', 'j.kuisioner_id')
                ->where('j.skrining_id', $skrining->id)
                ->where('k.status_soal', 'keluarga')
                ->where('j.jawaban', true)
                ->select('k.nama_pertanyaan', 'j.jawaban_lainnya')
                ->get()
                ->map(fn($r) => ($r->nama_pertanyaan === 'Lainnya' && $r->jawaban_lainnya) ? ('Lainnya: ' . $r->jawaban_lainnya) : $r->nama_pertanyaan)
                ->values()->all();

            // Hitung status risiko untuk UI
            $resikoSedangCount = (int)($skrining->jumlah_resiko_sedang ?? 0);
            $resikoTinggiCount = (int)($skrining->jumlah_resiko_tinggi ?? 0);
            $isBerisiko = ($resikoTinggiCount >= 1 || $resikoSedangCount >= 2);

            // Cek apakah sudah ada rujukan
            $hasReferral = RujukanRs::where('skrining_id', $skrining->id)->exists();

            return view('rs.show', compact(
                'pasien',
                'skrining',
                'sebabSedang',
                'sebabTinggi',
                'riwayatPenyakitPasien',
                'riwayatPenyakitKeluarga',
                'isBerisiko',
                'hasReferral'
            ));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                ->route('rs.dashboard')
                ->with('error', 'Data pasien tidak ditemukan');
        }
    }

    public function prosesPasienNifas(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pasien = Pasien::findOrFail($id);

            $rs_id = $this->getRsId();

            $existingNifas = PasienNifasRs::where('pasien_id', $pasien->id)
                ->where('rs_id', $rs_id)
                ->first();

            if ($existingNifas) {
                DB::commit();

                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar dalam data nifas.');
            }

            $pasienNifas = PasienNifasRs::create([
                'rs_id'               => $rs_id,
                'pasien_id'           => $pasien->id,
                'tanggal_mulai_nifas' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('rs.pasien-nifas.show', $pasienNifas->id)
                ->with('success', 'Pasien berhasil diproses ke data nifas! Silakan tambah data anak.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error Proses Pasien Nifas: ' . $e->getMessage());

            return redirect()
                ->route('rs.dashboard')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getRsId()
    {
        $user = Auth::user();

        if (!$user) {
            throw new \RuntimeException('User belum login.');
        }

        if (!empty($user->rumah_sakit_id)) {
            return $user->rumah_sakit_id;
        }

        if (!empty($user->rs_id)) {
            return $user->rs_id;
        }

        if (method_exists($user, 'rumahSakit')) {
            $rs = $user->rumahSakit()->first();
            if ($rs) {
                return $rs->id;
            }
        }

        $rs = RumahSakit::query()->orderBy('id')->first();

        if (!$rs) {
            throw new \RuntimeException('Belum ada data rumah sakit di tabel rumah_sakits.');
        }

        return $rs->id;
    }
}
