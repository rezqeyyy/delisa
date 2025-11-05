<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Skrining;
use App\Models\RiwayatKehamilanGpa;
use App\Models\KondisiKesehatan;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class PreeklampsiaController extends Controller
{
    use SkriningHelpers;

    /**
     * Halaman form Preeklampsia (q1..q7) dengan autosave.
     */
    public function preEklampsia(Request $request)
    {
        $skriningId = (int) $request->query('skrining_id');
        $skrining   = $this->requireSkriningForPasien($skriningId);

        // Autosave jika ada parameter jawaban (pertanyaan1..pertanyaan7)
        $keys   = ['pertanyaan1','pertanyaan2','pertanyaan3','pertanyaan4','pertanyaan5','pertanyaan6','pertanyaan7'];
        $hasAny = collect($keys)->some(fn($k) => $request->filled($k));

        if ($hasAny) {
            $preMap = [
                'pertanyaan1' => ['nama' => 'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)', 'resiko' => 'sedang'],
                'pertanyaan2' => ['nama' => 'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)', 'resiko' => 'sedang'],
                'pertanyaan3' => ['nama' => 'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya', 'resiko' => 'sedang'],
                'pertanyaan4' => ['nama' => 'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia', 'resiko' => 'sedang'],
                'pertanyaan5' => ['nama' => 'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya', 'resiko' => 'tinggi'],
                'pertanyaan6' => ['nama' => 'Apakah kehamilan anda saat ini adalah kehamilan kembar', 'resiko' => 'tinggi'],
                'pertanyaan7' => ['nama' => 'Apakah anda memiliki diabetes dalam masa kehamilan', 'resiko' => 'tinggi'],
            ];

            DB::transaction(function () use ($skrining, $request, $preMap) {
                foreach ($preMap as $key => $def) {
                    $row = DB::table('kuisioner_pasiens')
                        ->where('nama_pertanyaan', $def['nama'])
                        ->where('status_soal', 'pre_eklampsia')
                        ->first();

                    $qid = $row?->id ?? DB::table('kuisioner_pasiens')->insertGetId([
                        'nama_pertanyaan' => $def['nama'],
                        'status_soal'     => 'pre_eklampsia',
                        'resiko'          => $def['resiko'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    DB::table('jawaban_kuisioners')->updateOrInsert(
                        ['skrining_id' => $skrining->id, 'kuisioner_id' => $qid],
                        ['jawaban' => ($request->input($key) === 'ya')]
                    );
                }
            });

            // Hitung ulang hasil setelah simpan agar dashboard/hasil selalu sinkron
            $this->recalcPreEklampsia($skrining);
        }

        // Prefill jawaban untuk ditampilkan di form
        $preNames = [
            'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
            'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
            'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
            'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
            'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
            'Apakah kehamilan anda saat ini adalah kehamilan kembar',
            'Apakah anda memiliki diabetes dalam masa kehamilan',
        ];

        $preKuis = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $preJawab = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $preKuis->pluck('id')->all())
            ->get(['kuisioner_id', 'jawaban'])
            ->keyBy('kuisioner_id');

        $answers = [
            'pertanyaan1' => ($id = optional($preKuis->get($preNames[0]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
            'pertanyaan2' => ($id = optional($preKuis->get($preNames[1]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
            'pertanyaan3' => ($id = optional($preKuis->get($preNames[2]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
            'pertanyaan4' => ($id = optional($preKuis->get($preNames[3]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
            'pertanyaan5' => ($id = optional($preKuis->get($preNames[4]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
            'pertanyaan6' => ($id = optional($preKuis->get($preNames[5]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
            'pertanyaan7' => ($id = optional($preKuis->get($preNames[6]))->id) ? (bool) optional($preJawab->get($id))->jawaban : false,
        ];

        return view('pasien.skrining.preeklampsia', compact('answers'));
    }

    /**
     * Simpan jawaban Preeklampsia dan hitung final.
     * Di sini q1..q7 dihitung bersama faktor dari langkah-langkah sebelumnya.
     */
    public function store(Request $request)
    {
        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));
        $pasien   = optional(Auth::user())->pasien;

        // Ambil data terisi sebelumnya
        $kk  = KondisiKesehatan::where('skrining_id', $skrining->id)->first();
        $gpa = RiwayatKehamilanGpa::where('skrining_id', $skrining->id)->first();

        // Faktor sedang
        $umur = null;
        try {
            if ($pasien && $pasien->tanggal_lahir) {
                $umur = Carbon::parse($pasien->tanggal_lahir)->age;
            }
        } catch (\Throwable $e) {
            $umur = null;
        }

        $sistol  = $kk ? (int) $kk->sdp : null;
        $diastol = $kk ? (int) $kk->dbp : null;
        $map     = $kk ? ($kk->map ?? (($sistol !== null && $diastol !== null) ? round(($diastol + (($sistol - $diastol) / 3)), 2) : null)) : null;

        $isAgeModerate          = ($umur !== null && $umur >= 35);
        $isPrimigravidaModerate = ($gpa && intval($gpa->total_kehamilan) === 1);
        $isImtModerate          = ($kk && floatval($kk->imt) > 30);
        $isMapModerate          = ($map !== null && $map > 90);

        // Faktor tinggi dari kuisioner individu (disimpan pada langkah Riwayat Penyakit Pasien)
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

        $isBpHigh     = $qidHipertensi ? (bool) optional($jawaban->get($qidHipertensi))->jawaban : false;
        $isKidneyHigh = $qidGinjal     ? (bool) optional($jawaban->get($qidGinjal))->jawaban     : false;
        $isSleHigh    = $qidSle        ? (bool) optional($jawaban->get($qidSle))->jawaban        : false;
        $isApsHigh    = $qidAps        ? (bool) optional($jawaban->get($qidAps))->jawaban        : false;

        $isPrimigravidaModerateGpa = ($gpa && intval($gpa->total_kehamilan) === 1);

        // Jawaban q1..q7 dari form
        $q1 = $request->input('pertanyaan1') === 'ya';
        $q2 = $request->input('pertanyaan2') === 'ya';
        $q3 = $request->input('pertanyaan3') === 'ya'; // jarak 10 tahun
        $q4 = $request->input('pertanyaan4') === 'ya'; // keluarga preeklampsia
        $q5 = $request->input('pertanyaan5') === 'ya'; // riwayat preeklampsia sebelumnya
        $q6 = $request->input('pertanyaan6') === 'ya'; // kehamilan kembar
        $q7 = $request->input('pertanyaan7') === 'ya'; // diabetes dalam kehamilan

        // Simpan jawaban ke kuisioner_pasiens (status_soal='preeklampsia')
        $preMap = [
            'pertanyaan1' => ['nama' => 'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)', 'resiko' => 'sedang'],
            'pertanyaan2' => ['nama' => 'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)', 'resiko' => 'sedang'],
            'pertanyaan3' => ['nama' => 'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya', 'resiko' => 'sedang'],
            'pertanyaan4' => ['nama' => 'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia', 'resiko' => 'sedang'],
            'pertanyaan5' => ['nama' => 'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya', 'resiko' => 'tinggi'],
            'pertanyaan6' => ['nama' => 'Apakah kehamilan anda saat ini adalah kehamilan kembar', 'resiko' => 'tinggi'],
            'pertanyaan7' => ['nama' => 'Apakah anda memiliki diabetes dalam masa kehamilan', 'resiko' => 'tinggi'],
        ];

        DB::transaction(function () use ($skrining, $request, $preMap) {
            foreach ($preMap as $key => $def) {
                $row = DB::table('kuisioner_pasiens')
                    ->where('nama_pertanyaan', $def['nama'])
                    ->where('status_soal', 'pre_eklampsia')
                    ->first();

                $qid = $row?->id ?? DB::table('kuisioner_pasiens')->insertGetId([
                    'nama_pertanyaan' => $def['nama'],
                    'status_soal'     => 'pre_eklampsia',
                    'resiko'          => $def['resiko'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                DB::table('jawaban_kuisioners')->updateOrInsert(
                    ['skrining_id' => $skrining->id, 'kuisioner_id' => $qid],
                    ['jawaban' => ($request->input($key) === 'ya')]
                );
            }
        });

        // Primigravida hanya dari GPA
        $isPrimigravidaModerate = $isPrimigravidaModerateGpa;

        $jumlahSedang = ($isAgeModerate ? 1 : 0)
                    + ($isPrimigravidaModerate ? 1 : 0)
                    + ($isImtModerate ? 1 : 0)
                    + ($isMapModerate ? 1 : 0)
                    + ($q1 ? 1 : 0) + ($q2 ? 1 : 0) + ($q3 ? 1 : 0) + ($q4 ? 1 : 0);

        $jumlahTinggi = ($isBpHigh ? 1 : 0)
                    + ($isKidneyHigh ? 1 : 0)
                    + ($isSleHigh ? 1 : 0)
                    + ($isApsHigh ? 1 : 0)
                    + ($q5 ? 1 : 0) + ($q6 ? 1 : 0) + ($q7 ? 1 : 0);

        $status       = 'Normal';
        $kesimpulan   = 'Normal';
        $tindakLanjut = false;

        if ($jumlahTinggi >= 1 || $jumlahSedang >= 2) {
            $status       = 'Risiko Tinggi';
            $kesimpulan   = 'Berisiko';
            $tindakLanjut = true;
        } elseif ($jumlahSedang >= 1) {
            $status     = 'Risiko Sedang';
            $kesimpulan = 'Waspada';
        }

        DB::transaction(function () use ($skrining, $status, $kesimpulan, $jumlahSedang, $jumlahTinggi, $tindakLanjut) {
            Skrining::query()->whereKey($skrining->id)->update([
                'status_pre_eklampsia' => $status,
                'jumlah_resiko_sedang' => $jumlahSedang,
                'jumlah_resiko_tinggi' => $jumlahTinggi,
                'kesimpulan'           => $kesimpulan,
                'step_form'            => 6,
                'tindak_lanjut'        => $tindakLanjut,
            ]);
        });

        $msg = 'Hasil Preeklampsia: ' . $status;
        if ($tindakLanjut) {
            $msg .= ' — Rujuk ke Rumah Sakit.';
        }

        return redirect()->route('pasien.dashboard')->with('ok', $msg);
    }
}