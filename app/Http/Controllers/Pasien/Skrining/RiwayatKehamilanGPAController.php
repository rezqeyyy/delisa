<?php

namespace App\Http\Controllers\Pasien\Skrining;

// Mengimpor base Controller Laravel.
use App\Http\Controllers\Controller;
// Mengimpor Request untuk menangkap input dari HTTP.
use Illuminate\Http\Request;
// Mengimpor facade DB untuk operasi query builder/transaksi.
use Illuminate\Support\Facades\DB;
// Mengimpor model Skrining (tabel skrinings).
use App\Models\Skrining;
// Mengimpor model RiwayatKehamilanGpa (tabel riwayat_kehamilan_gpas).
use App\Models\RiwayatKehamilanGpa;
// Mengimpor trait SkriningHelpers (helper validasi & rekalkulasi skrining).
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class RiwayatKehamilanGPAController extends Controller
{
    use SkriningHelpers;

    /* {{-- ========== GPA — FORM ========== --}} */

    // Halaman GPA (G, P, A):
    // - Prefill data jika sudah tersimpan.
    public function riwayatKehamilanGpa(Request $request)
    {
        // Ambil 'skrining_id' dari query string untuk melanjutkan episode skrining yang sama.
        $skriningId = (int) $request->query('skrining_id');
        // Pastikan skrining milik pasien yang login.
        $skrining   = $this->requireSkriningForPasien($skriningId);

        // Prefill GPA jika sudah ada data untuk skrining ini.
        $gpa = $skrining ? $skrining->riwayatKehamilanGpa()->first() : null;

        // Tampilkan form GPA dengan prefill.
        return view('pasien.skrining.riwayat-kehamilan-gpa', compact('gpa'));
    }

    /* {{-- ========== GPA — STORE ========== --}} */

    // Penyimpanan GPA:
    // - Simpan total kehamilan, persalinan, dan abortus (string untuk konsistensi).
    // - Set step_form=2, lalu hitung ulang risiko dan lanjut ke kondisi kesehatan.
    public function store(Request $request)
    {
        // Validasi input GPA (total kehamilan, persalinan, abortus).
        $data = $request->validate([
            'total_kehamilan'   => ['required', 'integer', 'min:0', 'max:25'],
            'total_persalinan'  => ['required', 'integer', 'min:0', 'max:25'],
            'total_abortus'     => ['nullable', 'integer', 'min:0'],
        ]);

        // Ambil 'skrining_id' dari input untuk melanjutkan episode skrining yang sama.
        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

        /**
         * Transaksi penyimpanan GPA:
         * - updateOrCreate pada tabel riwayat_kehamilan_gpas (string untuk konsistensi)
         * - set step_form=2 → lanjut ke langkah Kondisi Kesehatan
         */
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

        // Rekalkulasi hasil risiko setelah GPA diperbarui.
        $this->recalcPreEklampsia($skrining);

        // Redirect ke langkah Kondisi Kesehatan (step 3) dengan skrining_id.
        return redirect()
            ->route('pasien.kondisi-kesehatan-pasien', ['skrining_id' => $skrining->id])
            ->with('ok', 'Riwayat kehamilan & persalinan (GPA) berhasil disimpan.');
    }

}