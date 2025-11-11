<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Skrining;
use App\Models\Puskesmas;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;



class SkriningController extends Controller
{
    use SkriningHelpers;    

    /**
     * Lihat hasil skrining (setelah ada kesimpulan).
     */
    public function show(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        if (is_null($skrining->kesimpulan) && is_null($skrining->status_pre_eklampsia)) {
            return redirect()
                ->route('pasien.preeklampsia')
                ->with('error', 'Silakan lakukan skrining preeklampsia terlebih dahulu untuk melihat hasil.');
        }

        $data = $this->buildSkriningShowData($skrining);

        return view('pasien.skrining-show', $data);
    }

    private function buildSkriningShowData(Skrining $skrining): array
    {
        // Penentuan kesimpulan tingkat risiko untuk tampilan hasil:
        // - "Skrining belum selesai" jika kelengkapan lintas halaman belum terpenuhi.
        // - "Berisiko Preeklampsia" jika jumlah risiko tinggi ≥ 1 atau risiko sedang ≥ 2.
        // - "Waspada" jika jumlah risiko sedang ≥ 1.
        // - "Tidak berisiko" jika tidak memenuhi kriteria di atas.

        $pasien = optional($skrining->pasien);
        $kk     = optional($skrining->kondisiKesehatan);
        $gpa    = optional($skrining->riwayatKehamilanGpa);

        $nama = $pasien->nama ?? Auth::user()->name ?? '-';
        $nik  = $pasien->nik ?? Auth::user()->nik ?? '-';

        $tanggal  = $kk->tanggal_skrining ?? optional($skrining)->created_at ?? null;
        $berat    = $kk->berat_badan_saat_hamil ?? null;
        $tinggi   = $kk->tinggi_badan ?? null;
        $imt      = $kk->imt ?? (($berat && $tinggi) ? round($berat / pow($tinggi/100, 2), 2) : null);
        $anjuranBb = $kk->anjuran_kenaikan_bb ?? null;

        $sistol  = $kk->sdp ?? null;
        $diastol = $kk->dbp ?? null;
        $map     = $kk->map ?? (($sistol && $diastol) ? round(($diastol + (($sistol - $diastol) / 3)), 2) : null);

        $usiaKehamilan      = $kk->usia_kehamilan ?? '-';
        $taksiranPersalinan = $kk->tanggal_perkiraan_persalinan ?? null;

        $gravida = $gpa->total_kehamilan ?? '-';
        $para    = $gpa->total_persalinan ?? '-';
        $abortus = $gpa->total_abortus ?? '-';

        $resikoSedang = $skrining->jumlah_resiko_sedang ?? 0;
        $resikoTinggi = $skrining->jumlah_resiko_tinggi ?? 0;

        // Gunakan kelengkapan data lintas semua halaman, bukan lagi step_form
        $isComplete = $this->isSkriningCompleteForSkrining($skrining);

        $kesimpulan = (!$isComplete)
            ? 'Skrining belum selesai'
            : (($resikoTinggi >= 1 || $resikoSedang >= 2)
                ? 'Berisiko Preeklampsia'
                : (($resikoSedang >= 1) ? 'Waspada' : 'Tidak berisiko'));

        $rekomendasi  = ($resikoTinggi >= 1 || $resikoSedang >= 2)
            ? 'Silahkan untuk menghubungi petugas Puskesmas untuk mendapatkan rujukan Dokter atau Rumah Sakit untuk pengobatan lanjutan.'
            : 'Lanjutkan ANC sesuai standar, ulang skrining di trimester berikutnya.';
        $catatan      = $skrining->catatan ?? null;

        // ===== Pemicu risiko =====
        $sebabSedang = [];
        $sebabTinggi = [];

        // Usia ibu
        $umur = null;
        try {
            $tglLahir = optional($skrining->pasien)->tanggal_lahir;
            if ($tglLahir) {
                $umur = Carbon::parse($tglLahir)->age;
            }
        } catch (\Throwable $e) {
            $umur = null;
        }
        if ($umur !== null && $umur >= 35) {
            $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)";
        }

        // Primigravida (G = 1)
        if ($gpa && intval($gpa->total_kehamilan) === 1) {
            $sebabSedang[] = 'Primigravida (G=1)';
        }

        // IMT > 30
        if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) {
            $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)';
        }

        // MAP > 90 mmHg
        if ($map !== null && $map > 90) {
            $sebabSedang[] = 'MAP ' . number_format($map, 2) . ' mmHg (>90)';
        }

        // ===== Risiko sedang dari Preeklampsia (Q1–Q4) =====
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
            ->get(['id','nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $preJawabModerate = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $preKuisModerate->pluck('id')->all())
            ->get(['kuisioner_id','jawaban'])
            ->keyBy('kuisioner_id');

        foreach ($preModerateNames as $nm) {
            $id = optional($preKuisModerate->get($nm))->id;
            if ($id && (bool) optional($preJawabModerate->get($id))->jawaban) {
                $sebabSedang[] = $preModerateLabels[$nm] ?? $nm;
            }
        }

        // Risiko tinggi dari kuisioner individu
        $kuisNames = ['Hipertensi Kronik', 'Ginjal', 'Autoimun, SLE', 'Anti Phospholipid Syndrome'];
        $kuisioner = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'individu')
            ->whereIn('nama_pertanyaan', $kuisNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $jawaban = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $kuisioner->pluck('id')->all())
            ->get(['kuisioner_id', 'jawaban'])
            ->keyBy('kuisioner_id');

        $qidHipertensi = optional($kuisioner->get('Hipertensi Kronik'))->id;
        $qidGinjal     = optional($kuisioner->get('Ginjal'))->id;
        $qidSle        = optional($kuisioner->get('Autoimun, SLE'))->id;
        $qidAps        = optional($kuisioner->get('Anti Phospholipid Syndrome'))->id;

        if ($qidHipertensi && (bool) optional($jawaban->get($qidHipertensi))->jawaban) {
            $sebabTinggi[] = 'Hipertensi kronik';
        }
        if ($qidGinjal && (bool) optional($jawaban->get($qidGinjal))->jawaban) {
            $sebabTinggi[] = 'Riwayat penyakit ginjal';
        }
        if ($qidSle && (bool) optional($jawaban->get($qidSle))->jawaban) {
            $sebabTinggi[] = 'Autoimun/SLE';
        }
        if ($qidAps && (bool) optional($jawaban->get($qidAps))->jawaban) {
            $sebabTinggi[] = 'Antiphospholipid Syndrome (APS)';
        }

        // Risiko tinggi dari Preeklampsia (Q5–Q7)
        $preHighNames = [
            'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
            'Apakah kehamilan anda saat ini adalah kehamilan kembar',
            'Apakah anda memiliki diabetes dalam masa kehamilan',
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

        if (($id = optional($preKuisHigh->get($preHighNames[0]))->id) && (bool) optional($preJawabHigh->get($id))->jawaban) {
            $sebabTinggi[] = 'Riwayat preeklampsia sebelumnya';
        }
        if (($id = optional($preKuisHigh->get($preHighNames[1]))->id) && (bool) optional($preJawabHigh->get($id))->jawaban) {
            $sebabTinggi[] = 'Kehamilan kembar';
        }
        if (($id = optional($preKuisHigh->get($preHighNames[2]))->id) && (bool) optional($preJawabHigh->get($id))->jawaban) {
            $sebabTinggi[] = 'Diabetes dalam kehamilan';
        }

        return compact(
            'skrining','nama','nik','tanggal','berat','tinggi','imt','anjuranBb',
            'sistol','diastol','map','usiaKehamilan','taksiranPersalinan',
            'gravida','para','abortus','resikoSedang','resikoTinggi','kesimpulan',
            'rekomendasi','catatan','sebabSedang','sebabTinggi'
        );
    }

    /**
     * Mulai edit skrining: arahkan ke langkah Data Diri (stepper akan ikut skrining_id).
     */
    public function edit(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        return redirect()->route('pasien.data-diri', ['skrining_id' => $skrining->id]);
    }

    /**
     * Contoh endpoint update (tidak digunakan untuk mengubah data langkah).
     */
    public function update(Request $request, Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        return redirect()
            ->route('pasien.skrining.show', $skrining->id)
            ->with('ok', 'Puskesmas tetap sama seperti saat pengajuan.');
    }

    /**
     * Hapus skrining dan child records terkait.
     */
    public function destroy(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        DB::transaction(function () use ($skrining) {
            DB::table('jawaban_kuisioners')->where('skrining_id', $skrining->id)->delete();
            DB::table('riwayat_kehamilans')->where('skrining_id', $skrining->id)->delete();
            DB::table('riwayat_kehamilan_gpas')->where('skrining_id', $skrining->id)->delete();
            DB::table('kondisi_kesehatans')->where('skrining_id', $skrining->id)->delete();

            $skrining->delete();
        });

        return redirect()->route('pasien.dashboard')->with('ok', 'Skrining berhasil dihapus.');
    }

    /**
     * Pencarian puskesmas untuk modal pemilihan pengajuan.
     */
    public function puskesmasSearch(Request $request)
    {
        // Pencarian puskesmas untuk modal pengajuan:
        // - Parameter query "q" (opsional) akan dicocokkan ke kolom nama/kecamatan/lokasi.
        // - Mengembalikan maksimal 20 baris dengan field: id, nama_puskesmas, kecamatan.
        
        $q = trim($request->query('q', ''));

        $rows = Puskesmas::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where('nama_puskesmas', 'like', "%{$q}%")
                   ->orWhere('kecamatan', 'like', "%{$q}%")
                   ->orWhere('lokasi', 'like', "%{$q}%");
            })
            ->orderBy('nama_puskesmas')
            ->limit(20)
            ->get(['id', 'nama_puskesmas', 'kecamatan']);

        return response()->json($rows);
    }

    /**
     * Pastikan skrining milik pasien yang login.
     */
    private function authorizeAccess(Skrining $skrining): void
    {
        $userPasienId = optional(Auth::user()->pasien)->id;
        abort_unless($skrining->pasien_id === $userPasienId, 403);
    }

    /**
     * Helper: ambil skrining berdasarkan query skrining_id atau latest milik pasien.
     * Return: [Skrining|null, int $pasienId]
     */
    private function getSkriningFromQuery($skriningId): array
    {
        $skriningId = (int) $skriningId;
        $user       = Auth::user();
        $pasienId   = optional($user->pasien)->id;

        $skrining = null;
        if ($skriningId) {
            $skrining = Skrining::where('pasien_id', $pasienId)->whereKey($skriningId)->first();
        }
        if (!$skrining && $pasienId) {
            $skrining = Skrining::where('pasien_id', $pasienId)->latest()->first();
        }

        return [$skrining, $pasienId];
    }

}