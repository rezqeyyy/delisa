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

    /* {{-- ========== SKRINING — SHOW ========== --}} */

    /* 
     * Menampilkan halaman hasil skrining setelah ada kesimpulan
     */
    public function show(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        if (is_null($skrining->kesimpulan) && is_null($skrining->status_pre_eklampsia)) {
            return redirect()
                ->route('pasien.preeklampsia')
                ->with('error', 'Silakan lakukan skrining preeklampsia terlebih dahulu untuk melihat hasil.');
        }

        $this->recalcPreEklampsia($skrining);
        $data = $this->buildSkriningShowData($skrining);

        return view('pasien.skrining-show', $data);
    }

    /* {{-- ========== SKRINING — BUILD SHOW DATA ========== --}} */
    private function buildSkriningShowData(Skrining $skrining): array
    {
        /* 
         * Menyusun data tampilan hasil: identitas, kondisi, GPA,
         * ringkasan risiko, pemicu sedang/tinggi, dan rekomendasi
         * Kesimpulan: Berisiko jika tinggi ≥1 atau sedang ≥2; jika belum lengkap → "Skrining belum selesai"
         */

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
        $proteinUrine = $kk->pemeriksaan_protein_urine ?? null;

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
                : 'Tidak berisiko');

        $rekomendasi  = ($resikoTinggi >= 1 || $resikoSedang >= 2)
            ? 'Beresiko preeklampsia. Silahkan untuk menghubungi petugas Puskesmas untuk mendapatkan rujukan Dokter atau Rumah Sakit untuk pengobatan lanjutan.'
            : 'Kondisi normal. Lanjutkan ANC sesuai standar dan ulang skrining di trimester berikutnya.';
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

        // Tensi ≥ 130/90 mmHg
        if (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90)) {
            $sebabTinggi[] = 'Tekanan darah di atas 130/90 mHg';
        }

        // Risiko sedang dari Preeklampsia (non-overlap, pakai nama tetap)
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

        // Risiko tinggi dari Preeklampsia (pakai nama tetap)
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

        $sebabSedang = array_values(array_unique($sebabSedang));
        $sebabTinggi = array_values(array_unique($sebabTinggi));

        return compact(
            'skrining','nama','nik','tanggal','berat','tinggi','imt','anjuranBb',
            'sistol','diastol','map','proteinUrine','usiaKehamilan','taksiranPersalinan',
            'gravida','para','abortus','resikoSedang','resikoTinggi','kesimpulan',
            'rekomendasi','catatan','sebabSedang','sebabTinggi'
        );
    }

    /* {{-- ========== SKRINING — EDIT ========== --}} */

    /* 
     * Arahkan ke langkah Data Diri dengan membawa skrining_id
     */
    public function edit(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        return redirect()->route('pasien.data-diri', ['skrining_id' => $skrining->id]);
    }

    /* {{-- ========== SKRINING — UPDATE ========== --}} */
    
    /* 
     * Tidak digunakan untuk mengubah data langkah, hanya redirect
     */
    public function update(Request $request, Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        return redirect()
            ->route('pasien.skrining.show', $skrining->id)
            ->with('ok', 'Puskesmas tetap sama seperti saat pengajuan.');
    }

    /* {{-- ========== SKRINING — DESTROY ========== --}} */
    
    /* 
     * Hapus skrining beserta relasi jawaban, riwayat, dan kondisi
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

    /* {{-- ========== PUSKESMAS — SEARCH ========== --}} */
    
    /* 
     * Pencarian untuk modal pengajuan berdasarkan kata kunci
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

    /* {{-- ========== AUTH — AUTHORIZE ACCESS ========== --}} */
    
    /* 
     * Memastikan skrining milik pasien yang sedang login
     */
    private function authorizeAccess(Skrining $skrining): void
    {
        $userPasienId = optional(Auth::user()->pasien)->id;
        abort_unless($skrining->pasien_id === $userPasienId, 403);
    }

    /* {{-- ========== HELPER — GET SKRINING FROM QUERY ========== --}} */
    
    /* 
     * Ambil skrining berdasarkan skrining_id atau latest milik pasien
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