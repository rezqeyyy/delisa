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
    
    private function requireSkriningForPasien(int $skriningId): Skrining
    {
        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 401);

        return $skriningId
            ? Skrining::where('pasien_id', $pasienId)->whereKey($skriningId)->firstOrFail()
            : Skrining::where('pasien_id', $pasienId)->latest()->firstOrFail();
    }

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

    // ... existing code ...

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
            'Hipertensi Kronik',
            'Ginjal',
            'Autoimun, SLE',
            'Anti Phospholipid Syndrome',
            'Lainnya',
        ];
        if (!$this->hasAllJawaban($skrining, $individuNames, 'individu')) {
            return false;
        }

        // 5) Riwayat Penyakit Keluarga (keluarga)
        $keluargaNames = [
            'Hipertensi Kronik',
            'Ginjal',
            'Autoimun, SLE',
            'Anti Phospholipid Syndrome',
            'Lainnya',
        ];
        if (!$this->hasAllJawaban($skrining, $keluargaNames, 'keluarga')) {
            return false;
        }

        // 6) Preeklampsia (7 pertanyaan)
        $preeklampsiaNames = [
            // Sedang
            'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
            'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
            'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
            'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
            // Tinggi
            'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
            'Apakah kehamilan anda saat ini adalah kehamilan kembar',
            'Apakah anda memiliki diabetes dalam masa kehamilan',
        ];
        if (!$this->hasAllJawaban($skrining, $preeklampsiaNames, 'pre_eklampsia')) {
            return false;
        }

        return true;
    }
    

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

        // MAP (moderate)
        $sistol  = $kk ? (int) $kk->sdp : null;
        $diastol = $kk ? (int) $kk->dbp : null;
        $map     = $kk ? ($kk->map ?? (($sistol !== null && $diastol !== null) ? round(($diastol + (($sistol - $diastol) / 3)), 2) : null)) : null;

        $isAgeModerate          = ($umur !== null && $umur >= 35);
        $isPrimigravidaModerate = ($gpa && intval($gpa->total_kehamilan) === 1);
        $isImtModerate          = ($kk && floatval($kk->imt) > 30);
        $isMapModerate          = ($map !== null && $map > 90);

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

        $moderateCount = 0;
        if ($isAgeModerate)          $moderateCount++;
        if ($isPrimigravidaModerate) $moderateCount++;
        if ($isImtModerate)          $moderateCount++;
        if ($isMapModerate)          $moderateCount++;

        if ($highCount > 0) {
            $status = 'tinggi';
        } elseif ($moderateCount >= 2) {
            $status = 'sedang';
        } else {
            $status = 'rendah';
        }
        
        $kesimpulan = ($highCount === 0 && $moderateCount <= 1) ? 'Tidak berisiko' : 'Berisiko';
       
        Skrining::query()->whereKey($skrining->id)->update([
            'status_pre_eklampsia' => $status,
            'jumlah_resiko_sedang' => $moderateCount,
            'jumlah_resiko_tinggi' => $highCount,
            'kesimpulan'           => $kesimpulan,
            'tindak_lanjut'        => ($status === 'tinggi'),
        ]);
    }
    
}