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