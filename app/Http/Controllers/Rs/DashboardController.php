<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\PasienNifasRs;
use App\Models\Skrining;
use App\Models\RumahSakit;
use App\Models\RujukanRs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // RS yang sedang login
        $rsId = $this->getRsId();

        /**
         * ==============================
         * 1. BASIS DATA RUJUKAN RS
         * ==============================
         * Semua rujukan ke RS ini, dipisah:
         * - acceptedRujukan: rujukan yang SUDAH diterima (done_status = true)
         * - pendingRujukan : rujukan yang BELUM diterima (done_status = false)
         */
        $allRujukanForRs = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->get();

        $acceptedRujukan = $allRujukanForRs->where('done_status', true);
        $pendingRujukan  = $allRujukanForRs->where('done_status', false);

        /**
         * ==============================
         * 2. DATA PASIEN RUJUKAN / NON RUJUKAN (CARD "Data Pasien")
         * ==============================
         * - Pasien Rujukan    = pasien yang punya rujukan ke RS ini dan
         *                       minimal satu rujukannya SUDAH diterima.
         * - Pasien Non Rujukan= pasien yang punya rujukan ke RS ini tetapi
         *                       BELUM ada satupun rujukannya yang diterima.
         */

        // Pasien yang punya rujukan diterima
        $acceptedPatientIds = $acceptedRujukan
            ->pluck('pasien_id')
            ->filter()
            ->unique();

        // Pasien yang punya rujukan pending
        $pendingPatientIds = $pendingRujukan
            ->pluck('pasien_id')
            ->filter()
            ->unique();

        // Non rujukan = pasien yang hanya punya rujukan pending, dan BELUM pernah diterima
        $nonRujukanPatientIds = $pendingPatientIds
            ->diff($acceptedPatientIds);

        $pasienRujukan    = $acceptedPatientIds->count();
        $pasienNonRujukan = $nonRujukanPatientIds->count();

        /**
         * ==============================
         * 3. DATA PASIEN RUJUKAN (CARD "Data Pasien Rujukan")
         * ==============================
         * - Setelah Melahirkan  => pasien rujukan yang sudah masuk data nifas RS ini
         * - Berisiko            => rujukan KE RS INI yang status skrining-nya berisiko / waspada
         */
        $rujukanSetelahMelahirkan = PasienNifasRs::where('rs_id', $rsId)->count();

        $rujukanBeresiko     = 0;
        $resikoNormal        = 0;
        $resikoPreeklampsia  = 0; // kartu khusus "Resiko Preeklampsia"

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
                $resikoPreeklampsia++;  // Semua yang tidak normal kita hitung sebagai "beresiko preeklampsia"
            } else {
                $resikoNormal++;
            }
        }

        /**
         * ==============================
         * 4. PASIEN HADIR / TIDAK HADIR (CARD "Pasien Hadir")
         * ==============================
         * Definisi:
         * - Pasien Hadir       = pasien yang rujukannya SUDAH diterima DAN
         *                        SUDAH dilakukan pemeriksaan lanjutan
         *                        (minimal satu field lanjutan di rujukan_rs terisi)
         * - Pasien Tidak Hadir = pasien yang rujukannya SUDAH diterima tetapi
         *                        BELUM ada pemeriksaan lanjutan sama sekali
         *                        (semua field lanjutan masih null)
         *
         * Field lanjutan yang dicek:
         *   - pasien_datang
         *   - riwayat_tekanan_darah
         *   - hasil_protein_urin
         *   - perlu_pemeriksaan_lanjut
         *   - catatan_rujukan
         */
        $pasienHadir = $acceptedRujukan->filter(function (RujukanRs $r) {
            return !is_null($r->pasien_datang)
                || !is_null($r->riwayat_tekanan_darah)
                || !is_null($r->hasil_protein_urin)
                || !is_null($r->perlu_pemeriksaan_lanjut)
                || !is_null($r->catatan_rujukan);
        })->count();

        $totalAccepted   = $acceptedRujukan->count();
        $pasienTidakHadir = max(0, $totalAccepted - $pasienHadir);

        /**
         * ==============================
         * 5. DATA PASIEN NIFAS (CARD "Data Pasien Nifas")
         * ==============================
         */
        $totalNifas = PasienNifasRs::where('rs_id', $rsId)->count();

        $pasienNifasIds = PasienNifasRs::where('rs_id', $rsId)->pluck('id');

        if ($pasienNifasIds->isNotEmpty()) {
            // Nifas yang sudah punya kunjungan KF1
            $sudahKF1 = DB::table('kf')
                ->whereIn('id_nifas', $pasienNifasIds)
                ->where('kunjungan_nifas_ke', 1)
                ->distinct('id_nifas')
                ->count('id_nifas');

            /**
             * ==============================
             * 6. PEMANTAUAN (CARD "Pemantauan")
             * ==============================
             */
            $kfBase = DB::table('kf')->whereIn('id_nifas', $pasienNifasIds);

            $pemantauanSehat = (clone $kfBase)
                ->where('kesimpulan_pantauan', 'Sehat')
                ->count();

            $pemantauanDirujuk = (clone $kfBase)
                ->where('kesimpulan_pantauan', 'Dirujuk')
                ->count();

            $pemantauanMeninggal = (clone $kfBase)
                ->where('kesimpulan_pantauan', 'Meninggal')
                ->count();
        } else {
            $sudahKF1            = 0;
            $pemantauanSehat     = 0;
            $pemantauanDirujuk   = 0;
            $pemantauanMeninggal = 0;
        }

        /**
         * ==============================
         * 7. TABEL DATA PASIEN RUJUKAN PRE EKLAMPSIA
         * ==============================
         * HANYA:
         * - rujukan ke RS ini (rs_id = $rsId)
         * - sudah diterima (done_status = true)
         *
         * Di sini kita TIDAK lagi memfilter field lanjutan,
         * supaya semua pasien rujukan yang sudah diterima
         * tetap muncul di tabel ini, baik sudah diperiksa
         * maupun belum.
         * ==============================
         */
        $pePatients = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->where('done_status', true)
            ->orderByDesc('created_at')
            ->limit(50)
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
                    // Periksa = form lanjutan
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

    /**
     * Show detail pasien
     */
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

    /**
     *  Proses pasien ke data nifas
     */
    public function prosesPasienNifas(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Cari data pasien
            $pasien = Pasien::findOrFail($id);

            // Get RS ID dari user yang login
            $rs_id = $this->getRsId();

            // Cek apakah pasien sudah ada di data nifas
            $existingNifas = PasienNifasRs::where('pasien_id', $pasien->id)
                ->where('rs_id', $rs_id)
                ->first();

            if ($existingNifas) {
                DB::commit();

                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar dalam data nifas.');
            }

            // Buat data pasien nifas baru
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

    /**
     * Get RS ID from authenticated user
     */
    private function getRsId()
    {
        $user = Auth::user();

        if (!$user) {
            throw new \RuntimeException('User belum login.');
        }

        // 1) Kalau user punya kolom rumah_sakit_id → pakai itu
        if (!empty($user->rumah_sakit_id)) {
            return $user->rumah_sakit_id;
        }

        // 2) Atau kolom rs_id
        if (!empty($user->rs_id)) {
            return $user->rs_id;
        }

        // 3) Kalau di model User ada relasi rumahSakit(), coba pakai
        if (method_exists($user, 'rumahSakit')) {
            $rs = $user->rumahSakit()->first();
            if ($rs) {
                return $rs->id;
            }
        }

        // 4) Fallback: ambil RS pertama yang benar-benar ada di tabel rumah_sakits
        $rs = RumahSakit::query()->orderBy('id')->first();

        if (!$rs) {
            // Ini benar-benar darurat: tidak ada data rumah sakit sama sekali
            throw new \RuntimeException('Belum ada data rumah sakit di tabel rumah_sakits.');
        }

        return $rs->id;
    }
}
