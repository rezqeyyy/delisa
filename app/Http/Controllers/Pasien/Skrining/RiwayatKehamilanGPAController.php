<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Skrining;
use App\Models\RiwayatKehamilanGpa;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class RiwayatKehamilanGPAController extends Controller
{
    use SkriningHelpers;

    // Halaman GPA (G, P, A):
    // - Prefill data jika sudah tersimpan.
    public function riwayatKehamilanGpa(Request $request)
    {
        $skriningId = (int) $request->query('skrining_id');
        $skrining   = $this->requireSkriningForPasien($skriningId);

        $gpa = $skrining ? $skrining->riwayatKehamilanGpa()->first() : null;

        return view('pasien.skrining.riwayat-kehamilan-gpa', compact('gpa'));
    }

    // Penyimpanan GPA:
    // - Simpan total kehamilan, persalinan, dan abortus (string untuk konsistensi).
    // - Set step_form=2, lalu hitung ulang risiko dan lanjut ke kondisi kesehatan.
    public function store(Request $request)
    {
        $data = $request->validate([
            'total_kehamilan'   => ['nullable', 'integer', 'min:0'],
            'total_persalinan'  => ['nullable', 'integer', 'min:0'],
            'total_abortus'     => ['nullable', 'integer', 'min:0'],
        ]);

        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

        DB::transaction(function () use ($skrining, $data) {
            RiwayatKehamilanGpa::updateOrCreate(
                ['skrining_id' => $skrining->id, 'pasien_id' => $skrining->pasien_id],
                [
                    'total_kehamilan'  => isset($data['total_kehamilan'])  ? (string) $data['total_kehamilan']  : null,
                    'total_persalinan' => isset($data['total_persalinan']) ? (string) $data['total_persalinan'] : null,
                    'total_abortus'    => isset($data['total_abortus'])    ? (string) $data['total_abortus']    : null,
                ]
            );

            Skrining::query()->whereKey($skrining->id)->update(['step_form' => 2]);
        });

        // Hitung ulang hasil (agar tidak harus menunggu halaman Preeklampsia)
        $this->recalcPreEklampsia($skrining);

        return redirect()
            ->route('pasien.kondisi-kesehatan-pasien', ['skrining_id' => $skrining->id])
            ->with('ok', 'Riwayat kehamilan & persalinan (GPA) berhasil disimpan.');
    }

}