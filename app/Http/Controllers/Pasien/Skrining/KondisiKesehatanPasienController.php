<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Skrining;
use App\Models\KondisiKesehatan;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class KondisiKesehatanPasienController extends Controller
{
    use SkriningHelpers;  
    
    /* {{-- ========== KONDISI KESEHATAN — FORM ========== --}} */

    // Halaman kondisi kesehatan:
    // - Menarik data kondisi (jika ada) untuk prefill form.
    public function kondisiKesehatanPasien(Request $request)
    {
        $skrining = $this->requireSkriningForPasien((int) $request->query('skrining_id'));
        $kk = $skrining ? $skrining->kondisiKesehatan()->first() : null;

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
        $data = $request->validate([
            'tinggi_badan'               => ['required', 'integer', 'min:1'],
            'berat_badan_saat_hamil'     => ['required', 'numeric', 'min:1'],
            'sdp'                        => ['nullable', 'integer', 'min:0'],
            'dbp'                        => ['nullable', 'integer', 'min:0'],
            'pemeriksaan_protein_urine'  => ['required', 'in:Negatif,Positif 1,Positif 2,Positif 3,Belum dilakukan Pemeriksaan'],
            'hpht'                       => ['required', 'date'],
            'tanggal_skrining'           => ['required', 'date', 'after_or_equal:hpht'],
            'usia_kehamilan_minggu'      => ['nullable', 'integer', 'min:0'],
        ]);

        $skrining = $this->requireSkriningForPasien((int) $request->input('skrining_id'));

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
        $hpht        = Carbon::parse($data['hpht']);
        $tglSkrining = Carbon::parse($data['tanggal_skrining']);
        $diffDays    = $hpht->diffInDays($tglSkrining, false);
        $usiaMinggu  = $diffDays >= 0 ? intdiv($diffDays, 7) : 0;
        if ($diffDays < 0 && isset($data['usia_kehamilan_minggu'])) {
            $usiaMinggu = (int) $data['usia_kehamilan_minggu'];
        }
        $tpp = $hpht->copy()->addDays(280)->toDateString();

        DB::transaction(function () use ($skrining, $data, $imt, $kategoriImt, $anjuran, $map, $usiaMinggu, $tpp) {
            KondisiKesehatan::updateOrCreate(
                ['skrining_id' => $skrining->id],
                [
                    'tinggi_badan'                  => (int) $data['tinggi_badan'],
                    'berat_badan_saat_hamil'        => (float) $data['berat_badan_saat_hamil'],
                    'imt'                           => (float) $imt,
                    'status_imt'                    => $kategoriImt,
                    'hpht'                          => $data['hpht'],
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

        // Sinkronkan hasil risiko preeklamsia berdasarkan data baru
        $this->recalcPreEklampsia($skrining);

        return redirect()
            ->route('pasien.riwayat-penyakit-pasien', ['skrining_id' => $skrining->id])
            ->with('ok', 'Kondisi kesehatan pasien berhasil disimpan.');
    }    
}