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

    public function riwayatPenyakitKeluarga(Request $request)
    {
        $skrining = $this->requireSkriningForPasien((int) $request->query('skrining_id'));

        // Mapping kode -> nama pertanyaan
        $map = [
            'hipertensi_kronik'          => 'Hipertensi Kronik',
            'ginjal'                     => 'Ginjal',
            'autoimun_sle'               => 'Autoimun, SLE',
            'anti_phospholipid_syndrome' => 'Anti Phospholipid Syndrome',
            'lainnya'                    => 'Lainnya',
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'penyakit'                  => ['array'],
            'penyakit.*'                => ['in:hipertensi_kronik,ginjal,autoimun_sle,anti_phospholipid_syndrome,lainnya'],
            'penyakit_keluarga_lainnya' => ['nullable', 'string', 'max:255'],
        ]);

        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

        $map = [
            'hipertensi_kronik'          => ['nama' => 'Hipertensi Kronik',          'resiko' => 'tinggi'],
            'ginjal'                     => ['nama' => 'Ginjal',                     'resiko' => 'tinggi'],
            'autoimun_sle'               => ['nama' => 'Autoimun, SLE',              'resiko' => 'tinggi'],
            'anti_phospholipid_syndrome' => ['nama' => 'Anti Phospholipid Syndrome', 'resiko' => 'tinggi'],
            'lainnya'                    => ['nama' => 'Lainnya',                    'resiko' => 'non-risk'],
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