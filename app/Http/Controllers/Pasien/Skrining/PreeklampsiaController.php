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

    /* {{-- ========== PREEKLAMPSIA — INDEX ========== --}} */
    
    /*
     * Navigasi utama: menampilkan form Q1..Q14 (risiko sedang/tinggi)
     * Autosave: jika param pertanyaan1..pertanyaan14 ada di query
     * Prefill: dari DB dan data medis (umur, GPA, IMT, tensi)
     */
    public function preEklampsia(Request $request)
    {
        $skriningId = (int) $request->query('skrining_id');
        $skrining   = $this->requireSkriningForPasien($skriningId);

        // Autosave jika ada parameter jawaban (pertanyaan1..pertanyaan14)
        $keys   = ['pertanyaan1','pertanyaan2','pertanyaan3','pertanyaan4','pertanyaan5','pertanyaan6','pertanyaan7','pertanyaan8','pertanyaan9','pertanyaan10','pertanyaan11','pertanyaan12','pertanyaan13','pertanyaan14'];
        $hasAny = collect($keys)->some(fn($k) => $request->filled($k));

        if ($hasAny) {
            $preMap = [
                'pertanyaan1'  => ['nama' => 'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)', 'resiko' => 'sedang'],
                'pertanyaan2'  => ['nama' => 'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)', 'resiko' => 'sedang'],
                'pertanyaan3'  => ['nama' => 'Umur ≥ 35 tahun', 'resiko' => 'sedang'],
                'pertanyaan4'  => ['nama' => 'Apakah ini termasuk ke kehamilan pertama', 'resiko' => 'sedang'],
                'pertanyaan5'  => ['nama' => 'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya', 'resiko' => 'sedang'],
                'pertanyaan6'  => ['nama' => 'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia', 'resiko' => 'sedang'],
                'pertanyaan7'  => ['nama' => 'Apakah memiliki riwayat obesitas sebelum hamil (IMT > 30Kg/m2)', 'resiko' => 'sedang'],
                'pertanyaan8'  => ['nama' => 'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya', 'resiko' => 'tinggi'],
                'pertanyaan9'  => ['nama' => 'Apakah kehamilan anda saat ini adalah kehamilan kembar', 'resiko' => 'tinggi'],
                'pertanyaan10' => ['nama' => 'Apakah anda memiliki diabetes dalam masa kehamilan', 'resiko' => 'tinggi'],
                'pertanyaan11' => ['nama' => 'Apakah anda memiliki tekanan darah (Tensi) di atas 130/90 mHg', 'resiko' => 'tinggi'],
                'pertanyaan12' => ['nama' => 'Apakah anda memiliki penyakit ginjal', 'resiko' => 'tinggi'],
                'pertanyaan13' => ['nama' => 'Apakah anda memiliki penyakit autoimun, SLE', 'resiko' => 'tinggi'],
                'pertanyaan14' => ['nama' => 'Apakah anda memiliki penyakit Anti Phospholipid Syndrome', 'resiko' => 'tinggi'],
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

        $answers = [];
        foreach ($preNames as $idx => $nm) {
            $key = 'pertanyaan' . ($idx + 1);
            $answers[$key] = ($id = optional($preKuis->get($nm))->id) ? (bool) optional($preJawab->get($id))->jawaban : false;
        }

        // Prefill otomatis dari data sebelumnya bila belum ada jawaban
        $pasien = optional(Auth::user())->pasien;
        $kk  = KondisiKesehatan::where('skrining_id', $skrining->id)->first();
        $gpa = RiwayatKehamilanGpa::where('skrining_id', $skrining->id)->first();
        $umur = null;
        try { if ($pasien && $pasien->tanggal_lahir) { $umur = \Carbon\Carbon::parse($pasien->tanggal_lahir)->age; } } catch (\Throwable $e) { $umur = null; }
        $map = $kk ? ($kk->map ?? ((($kk->sdp ?? null) !== null && ($kk->dbp ?? null) !== null) ? round(($kk->dbp + ((($kk->sdp - $kk->dbp) / 3))), 2) : null)) : null;
        $answers['pertanyaan3']  = ($umur !== null && $umur >= 35);
        $answers['pertanyaan4']  = ($gpa && intval($gpa->total_kehamilan) === 1);
        $answers['pertanyaan7']  = ($kk && floatval($kk->imt) > 30);
        $answers['pertanyaan11'] = (($answers['pertanyaan11'] ?? false) || ($kk && (((int)($kk->sdp ?? 0) >= 130) || ((int)($kk->dbp ?? 0) >= 90))));

        return view('pasien.skrining.preeklampsia', compact('answers'));
    }
        

    /* {{-- ========== PREEKLAMPSIA — STORE ========== --}} */
    
    /* 
     * Simpan jawaban Q1..Q14 ke kuisioner (status_soal='pre_eklampsia')
     * Recalc: hitung status_pre_eklampsia dan jumlah risiko (tinggi/sedang)
     * Stepper: set step_form=6 lalu redirect ke hasil/dashboard
     */
    public function store(Request $request)
    {
        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

        // Jawaban q1..q14 dari form
        $q = [];
        for ($i = 1; $i <= 14; $i++) { $q[$i] = ($request->input('pertanyaan'.$i) === 'ya'); }

        // Simpan jawaban ke kuisioner_pasiens (status_soal='pre_eklampsia')
        $preMap = [
            'pertanyaan1'  => ['nama' => 'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)', 'resiko' => 'sedang'],
            'pertanyaan2'  => ['nama' => 'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)', 'resiko' => 'sedang'],
            'pertanyaan3'  => ['nama' => 'Umur ≥ 35 tahun', 'resiko' => 'sedang'],
            'pertanyaan4'  => ['nama' => 'Apakah ini termasuk ke kehamilan pertama', 'resiko' => 'sedang'],
            'pertanyaan5'  => ['nama' => 'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya', 'resiko' => 'sedang'],
            'pertanyaan6'  => ['nama' => 'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia', 'resiko' => 'sedang'],
            'pertanyaan7'  => ['nama' => 'Apakah memiliki riwayat obesitas sebelum hamil (IMT > 30Kg/m2)', 'resiko' => 'sedang'],
            'pertanyaan8'  => ['nama' => 'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya', 'resiko' => 'tinggi'],
            'pertanyaan9'  => ['nama' => 'Apakah kehamilan anda saat ini adalah kehamilan kembar', 'resiko' => 'tinggi'],
            'pertanyaan10' => ['nama' => 'Apakah anda memiliki diabetes dalam masa kehamilan', 'resiko' => 'tinggi'],
            'pertanyaan11' => ['nama' => 'Apakah anda memiliki tekanan darah (Tensi) di atas 130/90 mHg', 'resiko' => 'tinggi'],
            'pertanyaan12' => ['nama' => 'Apakah anda memiliki penyakit ginjal', 'resiko' => 'tinggi'],
            'pertanyaan13' => ['nama' => 'Apakah anda memiliki penyakit autoimun, SLE', 'resiko' => 'tinggi'],
            'pertanyaan14' => ['nama' => 'Apakah anda memiliki penyakit Anti Phospholipid Syndrome', 'resiko' => 'tinggi'],
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

        // Sinkronisasi hasil final memakai helper agar konsisten
        $this->recalcPreEklampsia($skrining);
        Skrining::query()->whereKey($skrining->id)->update(['step_form' => 6]);

        $skrining->refresh();
        $msg = 'Hasil Preeklampsia: ' . ($skrining->status_pre_eklampsia ?? 'Normal');
        if ($skrining->tindak_lanjut) { $msg .= ' — Rujuk ke Rumah Sakit.'; }

        if ($this->isSkriningCompleteForSkrining($skrining)) {
            return redirect()->route('pasien.skrining.show', $skrining->id)->with('ok', $msg);
        }
        return redirect()->route('pasien.dashboard')->with('ok', $msg);
    }
}