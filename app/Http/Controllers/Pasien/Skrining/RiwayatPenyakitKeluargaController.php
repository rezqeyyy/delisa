<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Skrining;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class RiwayatPenyakitKeluargaController extends Controller
{
    use SkriningHelpers;

    /* =========================================================
     * RIWAYAT PENYAKIT KELUARGA — INDEX
     * =========================================================
     * Navigasi utama: menampilkan pilihan penyakit keluarga (status_soal='keluarga')
     * Prefill: membaca jawaban sebelumnya, termasuk isian "lainnya" jika ada
     */
    public function riwayatPenyakitKeluarga(Request $request)
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
        $penyakitKeluargaLainnya = null;

        $kuisioner = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'keluarga')
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
                    $penyakitKeluargaLainnya = optional($jawaban->get($qid))->jawaban_lainnya;
                }
            }
        }

        return view('pasien.skrining.riwayat-penyakit-keluarga', compact('selected', 'penyakitKeluargaLainnya'));
    }

    /* =========================================================
     * RIWAYAT PENYAKIT KELUARGA — STORE
     * =========================================================
     * Validasi & simpan: mapping kode→pertanyaan, create/update kuisioner keluarga
     * Lainnya: simpan jawaban_lainnya jika opsi "Lainnya" dipilih
     * Proses: set step_form=5, hitung ulang risiko, redirect ke Preeklampsia
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'penyakit'                  => ['array'],
            'penyakit.*'                => ['in:hipertensi,alergi,tiroid,tb,jantung,hepatitis_b,jiwa,autoimun,sifilis,diabetes,asma,lainnya'],
            'penyakit_keluarga_lainnya' => ['nullable', 'string', 'max:255'],
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

        $dipilih     = $data['penyakit'] ?? [];
        $lainnyaText = trim((string)($data['penyakit_keluarga_lainnya'] ?? ''));

        DB::transaction(function () use ($skrining, $map, $dipilih, $lainnyaText) {
            foreach ($map as $code => $def) {
                $row = DB::table('kuisioner_pasiens')
                    ->where('nama_pertanyaan', $def['nama'])
                    ->where('status_soal', 'keluarga')
                    ->first();

                $qid = $row?->id ?? DB::table('kuisioner_pasiens')->insertGetId([
                    'nama_pertanyaan' => $def['nama'],
                    'status_soal'     => 'keluarga',
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

            Skrining::query()->whereKey($skrining->id)->update(['step_form' => 5]);
        });

        // Sinkronkan status risiko preeklamsia setelah update
        $this->recalcPreEklampsia($skrining);

        return redirect()->route('pasien.preeklampsia', ['skrining_id' => $skrining->id])
            ->with('ok', 'Riwayat penyakit keluarga berhasil disimpan.');
    }
}