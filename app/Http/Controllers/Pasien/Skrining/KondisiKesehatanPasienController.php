<?php

namespace App\Http\Controllers\Pasien\Skrining;

// Mengimpor base Controller Laravel.
use App\Http\Controllers\Controller;
// Mengimpor Request untuk menangkap input dari HTTP.
use Illuminate\Http\Request;
// Mengimpor facade DB untuk operasi query builder/transaksi.
use Illuminate\Support\Facades\DB;
// Mengimpor Carbon untuk perhitungan tanggal (HPHT, TPP, usia kehamilan).
use Carbon\Carbon;
// Mengimpor model Skrining (tabel skrinings).
use App\Models\Skrining;
// Mengimpor model KondisiKesehatan (tabel kondisi_kesehatans).
use App\Models\KondisiKesehatan;
// Mengimpor trait SkriningHelpers (helper validasi & rekalkulasi skrining).
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class KondisiKesehatanPasienController extends Controller
{
    use SkriningHelpers;  
    
    /* {{-- ========== KONDISI KESEHATAN — FORM ========== --}} */

    // Halaman kondisi kesehatan:
    // - Menarik data kondisi (jika ada) untuk prefill form.
    public function kondisiKesehatanPasien(Request $request)
    {
        // Ambil 'skrining_id' dari query string untuk melanjutkan episode skrining yang sama.
        $skrining = $this->requireSkriningForPasien((int) $request->query('skrining_id'));
        // Prefill: jika sudah ada data kondisi pada skrining ini, ambil baris pertama (terbaru).
        $kk = $skrining ? $skrining->kondisiKesehatan()->first() : null;

        // Tampilkan form Kondisi Kesehatan dengan data prefill.
        return view('pasien.skrining.kondisi-kesehatan-pasien', compact('kk'));
    }
    
    /* {{-- ========== KONDISI KESEHATAN — STORE ========== --}} */

    // Penyimpanan kondisi kesehatan:
    // - Hitung IMT (kg/m^2) dan kategorinya, serta anjuran kenaikan BB.
    // - Hitung MAP dari SDP/DBP, usia kehamilan (minggu), dan TPP (HPHT + 280 hari).
    // - Simpan ke tabel kondisi_kesehatans, set step_form=3.
    // - Rehitung status preeklampsia dan lanjut ke riwayat penyakit pasien.
    public function store(Request $request)
    {
        // Validasi payload Kondisi Kesehatan (antropometri, tensi, HPHT, tanggal skrining).
        $data = $request->validate([
            'tinggi_badan'               => ['required', 'integer', 'min:80', 'max:250'],
            'berat_badan_saat_hamil'     => ['required', 'numeric', 'min:20', 'max:300'],
            'sdp'                        => ['required', 'integer', 'min:40', 'max:300'],
            'dbp'                        => ['required', 'integer', 'min:30', 'max:200'],
            'pemeriksaan_protein_urine'  => ['required', 'in:Negatif,Positif 1,Positif 2,Positif 3,Belum dilakukan Pemeriksaan'],
            'hpht'                       => ['nullable', 'date'],
            'tanggal_skrining'           => ['required', 'date'],
            'usia_kehamilan_minggu'      => ['required', 'integer', 'min:0', 'max:45'],
        ]);

        // Ambil skrining_id dari input untuk melanjutkan episode skrining yang sama.
        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

        /**
         * Perhitungan medis:
         * - IMT = berat(kg) / (tinggi(m))^2 → 2 desimal
         * - Kategori IMT → Kurus Berat/Ringan, Normal, Gemuk Ringan/Berat
         * - Anjuran Kenaikan BB → tergantung kategori IMT
         * - MAP = (SDP + 2*DBP) / 3 → bila SDP & DBP valid (>0)
         * - Usia kehamilan (minggu) → dari selisih HPHT ke tanggal skrining
         * - TPP (HPHT + 280 hari)
         */
        // Hitung IMT
        $tinggiM = $data['tinggi_badan'] / 100;
        $imt     = round($data['berat_badan_saat_hamil'] / ($tinggiM * $tinggiM), 2);

        // Kategori IMT
        $kategoriImt = 'Normal';
        if ($imt < 17) {
            $kategoriImt = 'Kurus Berat';
        } elseif ($imt >= 17 && $imt <= 18.4) {
            $kategoriImt = 'Kurus Ringan';
        } elseif ($imt > 25 && $imt <= 27) {
            $kategoriImt = 'Gemuk Ringan';
        } elseif ($imt > 27) {
            $kategoriImt = 'Gemuk Berat';
        }

        // Anjuran kenaikan BB
        $anjuran = match ($kategoriImt) {
            'Kurus Berat', 'Kurus Ringan' => '12.5 - 18 kg',
            'Normal'                      => '11.5 - 16 kg',
            'Gemuk Ringan'                => '7 - 11.5 kg',
            'Gemuk Berat'                 => '5 - 9 kg',
            default                       => 'Tidak Ditentukan',
        };

        // MAP
        $map = 0.0;
        if (!empty($data['sdp']) && !empty($data['dbp']) && $data['sdp'] > 0 && $data['dbp'] > 0) {
            $map = round((($data['sdp'] + 2 * $data['dbp']) / 3), 2);
        }

        // Usia kehamilan minggu dan TPP
        $hpht        = !empty($data['hpht']) ? Carbon::parse($data['hpht']) : null;
        $tglSkrining = Carbon::parse($data['tanggal_skrining']);
        if ($hpht && $tglSkrining->greaterThanOrEqualTo($hpht)) {
            $diffDays   = $hpht->diffInDays($tglSkrining);
            $usiaMinggu = intdiv($diffDays, 7);
            $tpp        = $hpht->copy()->addDays(280)->toDateString();
        } else {
            $usiaMinggu = isset($data['usia_kehamilan_minggu']) ? (int) $data['usia_kehamilan_minggu'] : 0;
            $sisaHari   = max(0, 280 - ($usiaMinggu * 7));
            $tpp        = $tglSkrining->copy()->addDays($sisaHari)->toDateString();
        }

        /**
         * Transaksi penyimpanan Kondisi Kesehatan:
         * - updateOrCreate pada tabel kondisi_kesehatans untuk skrining_id
         * - set step_form=3 agar lanjut ke langkah berikutnya
         */
        DB::transaction(function () use ($skrining, $data, $imt, $kategoriImt, $anjuran, $map, $usiaMinggu, $tpp) {
            KondisiKesehatan::updateOrCreate(
                ['skrining_id' => $skrining->id],
                [
                    'tinggi_badan'                  => (int) $data['tinggi_badan'],
                    'berat_badan_saat_hamil'        => (float) $data['berat_badan_saat_hamil'],
                    'imt'                           => (float) $imt,
                    'status_imt'                    => $kategoriImt,
                    'hpht'                          => $data['hpht'] ?? null,
                    'tanggal_skrining'              => $data['tanggal_skrining'],
                    'usia_kehamilan'                => (int) $usiaMinggu,
                    'tanggal_perkiraan_persalinan'  => $tpp,
                    'anjuran_kenaikan_bb'           => $anjuran,
                    'sdp'                           => (int) ($data['sdp'] ?? 0),
                    'dbp'                           => (int) ($data['dbp'] ?? 0),
                    'map'                           => (float) $map,
                    'pemeriksaan_protein_urine'     => $data['pemeriksaan_protein_urine'],
                ]
            );

            Skrining::query()->whereKey($skrining->id)->update(['step_form' => 3]);
        });

        // Rekalkulasi risiko preeklampsia berdasarkan data baru.
        $this->recalcPreEklampsia($skrining);

        // Redirect ke langkah Riwayat Penyakit Pasien (step 4) dengan skrining_id.
        return redirect()
            ->route('pasien.riwayat-penyakit-pasien', ['skrining_id' => $skrining->id])
            ->with('ok', 'Kondisi kesehatan pasien berhasil disimpan.');
    }    
}