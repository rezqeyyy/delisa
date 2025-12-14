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
        $nonRujukanPatientIds = $pendingPatientIds
            ->diff($acceptedPatientIds);

        $pasienRujukan    = $acceptedPatientIds->count();
        $pasienNonRujukan = $nonRujukanPatientIds->count();

        /**
         * ==============================
         * 3. DATA PASIEN RUJUKAN (RISIKO)
         * ==============================
         */
        $rujukanSetelahMelahirkan = PasienNifasRs::where('rs_id', $rsId)->count();

        $rujukanBeresiko     = 0;
        $resikoNormal        = 0;
        $resikoPreeklampsia  = 0;

        foreach ($acceptedRujukan as $rujukan) {
            $skr = $rujukan->skrining;

            if (!$skr) {
                continue;
            }

            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));

            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0
                || in_array($raw, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);

            $isMed = ($skr->jumlah_resiko_sedang ?? 0) > 0
                || in_array($raw, ['waspada', 'menengah', 'sedang', 'risiko sedang']);

            if ($isHigh || $isMed) {
                $rujukanBeresiko++;
                $resikoPreeklampsia++;
            } else {
                $resikoNormal++;
            }
        }

        /**
         * ==============================
         * 4. PASIEN HADIR / TIDAK HADIR
         * ==============================
         */
        $pasienHadir = $acceptedRujukan->filter(function (RujukanRs $r) {
            return !is_null($r->pasien_datang)
                || !is_null($r->riwayat_tekanan_darah)
                || !is_null($r->hasil_protein_urin)
                || !is_null($r->perlu_pemeriksaan_lanjut)
                || !is_null($r->catatan_rujukan);
        })->count();

        $totalAccepted    = $acceptedRujukan->count();
        $pasienTidakHadir = max(0, $totalAccepted - $pasienHadir);

        /**
         * ==============================
         * 5. DATA PASIEN NIFAS (RS)
         * ==============================
         */
        $totalNifas = PasienNifasRs::where('rs_id', $rsId)->count();

        // Ambil ID nifas RS (primary key pasien_nifas_rs)
        $pasienNifasIds = PasienNifasRs::where('rs_id', $rsId)->pluck('id');

        // Inisialisasi default
        $sudahKF1            = 0;
        $pemantauanSehat     = 0;
        $pemantauanDirujuk   = 0;
        $pemantauanMeninggal = 0;

        if ($pasienNifasIds->isNotEmpty()) {
            // 🔎 Debugging: cek ID nifas yang dipakai
            Log::debug('RS Dashboard - Pasien Nifas RS', [
                'rs_id'           => $rsId,
                'pasien_nifas_ids' => $pasienNifasIds->values()->all(),
            ]);

            /**
             * ==============================
             * 5a. PASIEN YANG SUDAH KF1
             * ==============================
             *
             * Tabel pemantauan: kf_kunjungans
             * - pasien_nifas_id → relasi ke pasien_nifas_rs.id
             * - jenis_kf        → KF1 / KF2 / KF3 / KF4 (varchar)
             */
            $jenisKf1Candidates = ['KF1', 'kf1', '1', 'kf_1', 'KF 1'];

            $sudahKF1 = KfKunjungan::query()
                ->whereIn('pasien_nifas_id', $pasienNifasIds)
                ->whereIn('jenis_kf', $jenisKf1Candidates)
                ->distinct('pasien_nifas_id')
                ->count('pasien_nifas_id');

            Log::debug('RS Dashboard - Hitung KF1 RS', [
                'rs_id'    => $rsId,
                'sudahKF1' => $sudahKF1,
            ]);

            /**
             * ==============================
             * 6. PEMANTAUAN KF (SEHAT / DIRUJUK / MENINGGAL)
             * ==============================
             *
             * Semua diambil dari kf_kunjungans:
             * - kesimpulan_pantauan: 'Sehat', 'Dirujuk', 'Meninggal', dst.
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

            Log::debug('RS Dashboard - Rekap pemantauan KF RS', [
                'rs_id'              => $rsId,
                'pemantauanSehat'    => $pemantauanSehat,
                'pemantauanDirujuk'  => $pemantauanDirujuk,
                'pemantauanMeninggal'=> $pemantauanMeninggal,
            ]);
        }

        /**
         * ==============================
         * 7. TABEL DATA PASIEN RUJUKAN PRE EKLAMPSIA (DENGAN FILTER)
         * ==============================
         */
        $peQuery = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->where('done_status', true);

        // Filter berdasarkan NIK
        if ($request->filled('nik')) {
            $peQuery->whereHas('skrining.pasien', function ($q) use ($request) {
                $q->where('nik', 'like', '%' . $request->nik . '%');
            });
        }

        // Filter berdasarkan Nama Pasien
        if ($request->filled('nama')) {
            $peQuery->whereHas('skrining.pasien.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        // Filter berdasarkan Tanggal Mulai
        if ($request->filled('tanggal_dari')) {
            $peQuery->whereHas('skrining', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->tanggal_dari);
            });
        }

        // Filter berdasarkan Tanggal Sampai
        if ($request->filled('tanggal_sampai')) {
            $peQuery->whereHas('skrining', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->tanggal_sampai);
            });
        }

        // Filter berdasarkan Status Risiko
        if ($request->filled('risiko')) {
            $risikoFilter = $request->risiko;

            $peQuery->whereHas('skrining', function ($q) use ($risikoFilter) {
                if ($risikoFilter === 'Beresiko') {
                    $q->where(function ($subQ) {
                        $subQ->where('jumlah_resiko_tinggi', '>', 0)
                            ->orWhereRaw("LOWER(TRIM(kesimpulan)) IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi')")
                            ->orWhereRaw("LOWER(TRIM(status_pre_eklampsia)) IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi')");
                    });
                } elseif ($risikoFilter === 'Tidak Berisiko') {
                    $q->where(function ($subQ) {
                        $subQ->where('jumlah_resiko_tinggi', '<=', 0)
                            ->where('jumlah_resiko_sedang', '<=', 0)
                            ->whereRaw("LOWER(TRIM(COALESCE(kesimpulan, ''))) NOT IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi', 'waspada', 'menengah', 'sedang', 'risiko sedang')")
                            ->whereRaw("LOWER(TRIM(COALESCE(status_pre_eklampsia, ''))) NOT IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi', 'waspada', 'menengah', 'sedang', 'risiko sedang')");
                    });
                }
            });
        }

        $pePatients = $peQuery->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (RujukanRs $rujukan) {
                $skr = $rujukan->skrining;
                $pas = optional($skr)->pasien;
                $usr = optional($pas)->user;

                if (!$skr) {
                    return (object) [
                        'id'          => null,
                        'rujukan_id'  => $rujukan->id,
                        'nik'         => '-',
                        'nama'        => 'Data Skrining Tidak Tersedia',
                        'tanggal'     => '-',
                        'alamat'      => '-',
                        'telp'        => '-',
                        'kesimpulan'  => '-',
                        'detail_url'  => '#',
                        'process_url' => null,
                    ];
                }

                $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));

                $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0
                    || in_array($raw, ['beresiko','berisiko','risiko tinggi','tinggi']);

                $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0
                    || in_array($raw, ['waspada','menengah','sedang','risiko sedang']);

                return (object) [
                    'id'          => $pas->id ?? null,
                    'rujukan_id'  => $rujukan->id,
                    'nik'         => $pas->nik ?? '-',
                    'nama'        => $usr->name ?? 'Nama Tidak Tersedia',
                    'tanggal'     => optional($skr->created_at)->format('d/m/Y') ?? '-',
                    'alamat'      => $pas->PKecamatan ?? $pas->PWilayah ?? '-',
                    'telp'        => $usr->phone ?? $pas->no_telepon ?? '-',
                    'kesimpulan'  => $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Tidak Berisiko'),
                    'detail_url'  => route('rs.skrining.edit', $skr->id ?? 0),
                    'process_url' => $pas && $pas->id
                        ? route('rs.dashboard.proses-nifas', ['id' => $pas->id])
                        : null,
                ];
            });

        return view('rs.dashboard', compact(
            'rujukanSetelahMelahirkan',
            'rujukanBeresiko',
            'resikoNormal',
            'resikoPreeklampsia',
            'pasienRujukan',
            'pasienNonRujukan',
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
            $pasien = Pasien::with('user')->findOrFail($id);

            return view('rs.show', compact('pasien'));
        } catch (\Exception $e) {
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
