<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Skrining;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class RiwayatPenyakitPasienController extends Controller
{
    use SkriningHelpers;

    /* {{-- ========== RIWAYAT PENYAKIT PASIEN — INDEX ========== --}} */
    
    /* 
     * Navigasi utama: menampilkan pilihan penyakit (status_soal='individu')
     * Prefill: membaca jawaban sebelumnya untuk menandai pilihan
     */
    public function riwayatPenyakitPasien(Request $request)
    {
        $skrining = $this->requireSkriningForPasien((int) $request->query('skrining_id'));

        // Mapping kode -> nama pertanyaan
        $map = [
            'hipertensi'  => 'Hipertensi',
            'alergi'      => 'Alergi',
            'tiroid'      => 'Tiroid',
            'tb'          => 'TB',
            'jantung'     => 'Jantung',
            'hepatitis_b' => 'Hepatitis B',
            'jiwa'        => 'Jiwa',
            'autoimun'    => 'Autoimun',
            'sifilis'     => 'Sifilis',
            'diabetes'    => 'Diabetes',
            'asma'        => 'Asma',
            'lainnya'     => 'Lainnya',
        ];

        $selected = [];
        $penyakitLainnya = null;

        $kuisioner = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'individu')
            ->whereIn('nama_pertanyaan', array_values($map))
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $jawaban = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $kuisioner->pluck('id')->all())
            ->get(['kuisioner_id', 'jawaban', 'jawaban_lainnya'])
            ->keyBy('kuisioner_id');

        foreach ($map as $code => $nama) {
            $qid = optional($kuisioner->get($nama))->id;
            if ($qid && optional($jawaban->get($qid))->jawaban) {
                $selected[] = $code;
                if ($code === 'lainnya') {
                    $penyakitLainnya = optional($jawaban->get($qid))->jawaban_lainnya;
                }
            }
        }

        return view('pasien.skrining.riwayat-penyakit-pasien', compact('selected', 'penyakitLainnya'));
    }

    /* {{-- ========== RIWAYAT PENYAKIT PASIEN — STORE ========== --}} */

    /* 
     * Validasi & simpan: mapping kode→pertanyaan, create/update kuisioner
     * Lainnya: simpan jawaban_lainnya jika opsi "Lainnya" dipilih
     * Proses: set step_form=4, hitung ulang risiko, redirect ke penyakit keluarga
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'penyakit'          => ['array'],
            'penyakit.*'        => ['in:hipertensi,alergi,tiroid,tb,jantung,hepatitis_b,jiwa,autoimun,sifilis,diabetes,asma,lainnya'],
            'penyakit_lainnya'  => ['nullable', 'string', 'max:255'],
        ]);

        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

        $map = [
            'hipertensi'  => ['nama' => 'Hipertensi',  'resiko' => 'tinggi'],
            'alergi'      => ['nama' => 'Alergi',      'resiko' => 'non-risk'],
            'tiroid'      => ['nama' => 'Tiroid',      'resiko' => 'non-risk'],
            'tb'          => ['nama' => 'TB',          'resiko' => 'non-risk'],
            'jantung'     => ['nama' => 'Jantung',     'resiko' => 'tinggi'],
            'hepatitis_b' => ['nama' => 'Hepatitis B', 'resiko' => 'non-risk'],
            'jiwa'        => ['nama' => 'Jiwa',        'resiko' => 'non-risk'],
            'autoimun'    => ['nama' => 'Autoimun',    'resiko' => 'tinggi'],
            'sifilis'     => ['nama' => 'Sifilis',     'resiko' => 'tinggi'],
            'diabetes'    => ['nama' => 'Diabetes',    'resiko' => 'tinggi'],
            'asma'        => ['nama' => 'Asma',        'resiko' => 'non-risk'],
            'lainnya'     => ['nama' => 'Lainnya',     'resiko' => 'non-risk'],
        ];

        $dipilih      = $data['penyakit'] ?? [];
        $lainnyaText  = trim((string)($data['penyakit_lainnya'] ?? ''));

        DB::transaction(function () use ($skrining, $map, $dipilih, $lainnyaText) {
            foreach ($map as $code => $def) {
                $row = DB::table('kuisioner_pasiens')
                    ->where('nama_pertanyaan', $def['nama'])
                    ->where('status_soal', 'individu')
                    ->first();

                $qid = $row?->id ?? DB::table('kuisioner_pasiens')->insertGetId([
                    'nama_pertanyaan' => $def['nama'],
                    'status_soal'     => 'individu',
                    'resiko'          => $def['resiko'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $isSelected = in_array($code, $dipilih, true);

                DB::table('jawaban_kuisioners')->updateOrInsert(
                    ['skrining_id' => $skrining->id, 'kuisioner_id' => $qid],
                    [
                        'jawaban'         => $isSelected,
                        'jawaban_lainnya' => ($code === 'lainnya' && $isSelected) ? $lainnyaText : null,
                    ]
                );
            }

            Skrining::query()->whereKey($skrining->id)->update(['step_form' => 4]);
        });

        // Hitung ulang status risiko agar konsisten di dashboard
        $this->recalcPreEklampsia($skrining);

        return redirect()
            ->route('pasien.riwayat-penyakit-keluarga', ['skrining_id' => $skrining->id])
            ->with('ok', 'Riwayat penyakit pasien berhasil disimpan.');
    }
}