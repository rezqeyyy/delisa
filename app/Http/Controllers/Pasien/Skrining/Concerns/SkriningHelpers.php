<?php

namespace App\Http\Controllers\Pasien\skrining\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Skrining;
use App\Models\KondisiKesehatan;
use App\Models\RiwayatKehamilanGpa;

trait SkriningHelpers
{
    /* =========================================================
     * AUTH — REQUIRE SKRINING FOR PASIEN
     * =========================================================
     * Menjamin skrining milik pasien yang login
     * - Jika skrining_id tersedia, pastikan milik pasien
     * - Jika tidak, ambil skrining terbaru milik pasien
     */
    private function requireSkriningForPasien(int $skriningId): Skrining
    {
        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 401);

        return $skriningId
            ? Skrining::where('pasien_id', $pasienId)->whereKey($skriningId)->firstOrFail()
            : Skrining::where('pasien_id', $pasienId)->latest()->firstOrFail();
    }

    /* =========================================================
     * VALIDASI — DATA DIRI COMPLETE
     * =========================================================
     * Memastikan profil pasien & user terisi lengkap
     * Wajib: field utama + no_jkn bila pembiayaan 'BPJS Kesehatan'
     */
    private function isDataDiriCompleteForSkrining(Skrining $skrining): bool
    {
        $pasien = optional($skrining->pasien);
        $user   = $pasien ? optional($pasien->user) : null;

        if (!$pasien || !$user) {
            return false;
        }

        $required = [
            $pasien->nik,
            $pasien->tempat_lahir,
            $pasien->tanggal_lahir,
            $pasien->status_perkawinan,
            $pasien->PKecamatan,
            $pasien->PKabupaten,
            $pasien->PProvinsi,
            $pasien->PWilayah,
            $pasien->rt,
            $pasien->rw,
            $pasien->kode_pos,
            $pasien->pekerjaan,
            $pasien->pendidikan,
            $pasien->pembiayaan_kesehatan,
            $pasien->golongan_darah,
            $user->phone,
            $user->address,
        ];

        foreach ($required as $v) {
            if ($v === null || $v === '') {
                return false;
            }
        }

        if (($pasien->pembiayaan_kesehatan === 'BPJS Kesehatan') && (!$pasien->no_jkn)) {
            return false;
        }

        return true;
    }

    /* =========================================================
     * UTIL — HAS ALL JAWABAN
     * =========================================================
     * Memeriksa kelengkapan jawaban untuk daftar pertanyaan berdasarkan status_soal
     */
    private function hasAllJawaban(Skrining $skrining, array $names, string $statusSoal): bool
    {
        $kuis = DB::table('kuisioner_pasiens')
            ->where('status_soal', $statusSoal)
            ->whereIn('nama_pertanyaan', $names)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        if ($kuis->count() < count($names)) {
            return false;
        }

        $ids = $kuis->pluck('id')->all();

        $answeredCount = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $ids)
            ->count();

        return $answeredCount === count($names);
    }

    /* =========================================================
     * VALIDASI — KELENGKAPAN SKRINING (STEP 1..6)
     * =========================================================
     * Memastikan semua langkah sudah terisi: Data Diri, GPA, Kondisi Kesehatan,
     * Riwayat Penyakit Pasien, Riwayat Penyakit Keluarga, Preeklampsia
     */
    private function isSkriningCompleteForSkrining(Skrining $skrining): bool
    {
        // 1) Data Diri
        if (!$this->isDataDiriCompleteForSkrining($skrining)) {
            return false;
        }

        // 2) GPA (Riwayat Kehamilan G, P, A)
        $gpa = $skrining->riwayatKehamilanGpa;
        if (!$gpa
            || ($gpa->total_kehamilan === null || $gpa->total_kehamilan === '')
            || ($gpa->total_persalinan === null || $gpa->total_persalinan === '')
            || ($gpa->total_abortus === null || $gpa->total_abortus === '')
        ) {
            return false;
        }

        // 3) Kondisi Kesehatan
        $kk = $skrining->kondisiKesehatan;
        if (!$kk
            || $kk->tinggi_badan === null
            || $kk->berat_badan_saat_hamil === null
            || $kk->imt === null
            || ($kk->status_imt === null || $kk->status_imt === '')
            || $kk->tanggal_skrining === null
            || $kk->usia_kehamilan === null
            || $kk->tanggal_perkiraan_persalinan === null
            || ($kk->anjuran_kenaikan_bb === null || $kk->anjuran_kenaikan_bb === '')
            || ($kk->pemeriksaan_protein_urine === null || $kk->pemeriksaan_protein_urine === '')
        ) {
            return false;
        }

        // 4) Riwayat Penyakit Pasien (individu)
        $individuNames = [
            'Hipertensi', 'Alergi', 'Tiroid', 'TB', 'Jantung', 'Hepatitis B', 'Jiwa', 'Autoimun', 'Sifilis', 'Diabetes', 'Asma', 'Lainnya',
        ];
        if (!$this->hasAllJawaban($skrining, $individuNames, 'individu')) {
            return false;
        }

        // 5) Riwayat Penyakit Keluarga (keluarga)
        $keluargaNames = [
            'Hipertensi', 'Alergi', 'Tiroid', 'TB', 'Jantung', 'Hepatitis B', 'Jiwa', 'Autoimun', 'Sifilis', 'Diabetes', 'Asma', 'Lainnya',
        ];
        if (!$this->hasAllJawaban($skrining, $keluargaNames, 'keluarga')) {
            return false;
        }

        // 6) Preeklampsia (14 pertanyaan)
        $preeklampsiaNames = [
            'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
            'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
            'Umur ≥ 35 tahun',
            'Apakah ini termasuk ke kehamilan pertama',
            'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
            'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
            'Apakah memiliki riwayat obesitas sebelum hamil (IMT > 30Kg/m2)',
            'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
            'Apakah kehamilan anda saat ini adalah kehamilan kembar',
            'Apakah anda memiliki diabetes dalam masa kehamilan',
            'Apakah anda memiliki tekanan darah (Tensi) di atas 130/90 mHg',
            'Apakah anda memiliki penyakit ginjal',
            'Apakah anda memiliki penyakit autoimun, SLE',
            'Apakah anda memiliki penyakit Anti Phospholipid Syndrome',
        ];
        if (!$this->hasAllJawaban($skrining, $preeklampsiaNames, 'pre_eklampsia')) {
            return false;
        }

        return true;
    }

    /* =========================================================
     * REKALKULASI — RISIKO PREEKLAMPSIA
     * =========================================================
     * Hitung ulang jumlah faktor sedang/tinggi dan set status/kesimpulan/tindak_lanjut
     */
    private function recalcPreEklampsia(Skrining $skrining): void
    {
        $pasien = optional($skrining->pasien);
        $kk  = KondisiKesehatan::where('skrining_id', $skrining->id)->first();
        $gpa = RiwayatKehamilanGpa::where('skrining_id', $skrining->id)->first();

        // Umur (moderate)
        $umur = null;
        try {
            if ($pasien && $pasien->tanggal_lahir) {
                $umur = Carbon::parse($pasien->tanggal_lahir)->age;
            }
        } catch (\Throwable $e) {
            $umur = null;
        }

        // Tensi threshold 130/90
        $sistol  = $kk ? (int) $kk->sdp : null;
        $diastol = $kk ? (int) $kk->dbp : null;

        $isAgeModerate          = ($umur !== null && $umur >= 35);
        $isPrimigravidaModerate = ($gpa && intval($gpa->total_kehamilan) === 1);
        $isImtModerate          = ($kk && floatval($kk->imt) > 30);
        $isBpHigh               = (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90));

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

        $highCount = 0;
        if ($qidHipertensi && (bool) optional($jawaban->get($qidHipertensi))->jawaban) $highCount++;
        if ($qidGinjal     && (bool) optional($jawaban->get($qidGinjal))->jawaban)     $highCount++;
        if ($qidSle        && (bool) optional($jawaban->get($qidSle))->jawaban)        $highCount++;
        if ($qidAps        && (bool) optional($jawaban->get($qidAps))->jawaban)        $highCount++;
        if ($isBpHigh) $highCount++;

        $moderateCount = 0;
        if ($isAgeModerate)          $moderateCount++;
        if ($isPrimigravidaModerate) $moderateCount++;
        if ($isImtModerate)          $moderateCount++;

        $preModerateNames = [
            'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
            'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
            'Umur ≥ 35 tahun',
            'Apakah ini termasuk ke kehamilan pertama',
            'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
            'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
            'Apakah memiliki riwayat obesitas sebelum hamil (IMT > 30Kg/m2)',
        ];
        $preHighNames = [
            'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
            'Apakah kehamilan anda saat ini adalah kehamilan kembar',
            'Apakah anda memiliki diabetes dalam masa kehamilan',
            'Apakah anda memiliki tekanan darah (Tensi) di atas 130/90 mHg',
        ];

        $preKuisModerate = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preModerateNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $preKuisHigh = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preHighNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $preJawab = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', array_merge(
                $preKuisModerate->pluck('id')->all(),
                $preKuisHigh->pluck('id')->all()
            ))
            ->get(['kuisioner_id', 'jawaban'])
            ->keyBy('kuisioner_id');

        foreach ($preKuisModerate as $row) {
            if ((bool) optional($preJawab->get($row->id))->jawaban) {
                $moderateCount++;
            }
        }
        foreach ($preKuisHigh as $row) {
            if ((bool) optional($preJawab->get($row->id))->jawaban) {
                $highCount++;
            }
        }

        if ($highCount >= 1 || $moderateCount >= 2) {
            $status       = 'Risiko Tinggi';
            $kesimpulan   = 'Berisiko';
            $tindakLanjut = true;
        } else {
            $status       = 'Normal';
            $kesimpulan   = 'Tidak berisiko';
            $tindakLanjut = false;
        }
       
        Skrining::query()->whereKey($skrining->id)->update([
            'status_pre_eklampsia' => $status,
            'jumlah_resiko_sedang' => $moderateCount,
            'jumlah_resiko_tinggi' => $highCount,
            'kesimpulan'           => $kesimpulan,
            'tindak_lanjut'        => $tindakLanjut,
        ]);
    }
    
}