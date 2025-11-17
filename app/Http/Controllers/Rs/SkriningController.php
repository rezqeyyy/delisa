<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Skrining;
use App\Models\RujukanRs;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SkriningController extends Controller
{
    public function index()
    {
        // Ambil semua data skrining dengan relasi pasien
        $skrinings = Skrining::with('pasien.user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Tambahkan badge class untuk setiap skrining
        $skrinings->getCollection()->transform(function($skrining) {
            $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
            
            $skrining->badge_class = match(strtolower($conclusion)) {
                'berisiko', 'beresiko' => 'badge-berisiko',
                'normal', 'aman' => 'badge-normal',
                'waspada', 'menengah' => 'badge-waspada',
                default => 'badge-secondary'
            };
            
            $skrining->conclusion_display = ucfirst($conclusion);
            
            return $skrining;
        });

        return view('rs.skrining.index', compact('skrinings'));
    }

    public function show($id)
    {
        // Ambil data skrining dengan semua relasi
        $skrining = Skrining::with([
            'pasien.user',
            'kondisiKesehatan',
            'riwayatKehamilanGpa',
            'puskesmas'
        ])->findOrFail($id);

        // Cari atau buat rujukan RS untuk skrining ini
        $rsId = Auth::user()->rumahSakit->id ?? null;
        
        $rujukan = RujukanRs::firstOrCreate(
            [
                'skrining_id' => $skrining->id,
                'pasien_id' => $skrining->pasien_id,
                'rs_id' => $rsId
            ],
            [
                'done_status' => false,
                'is_rujuk' => false,
            ]
        );

        // Ambil resep obat yang sudah ada
        $resepObats = ResepObat::where('riwayat_rujukan_id', $rujukan->id)->get();

        return view('rs.skrining.show', compact('skrining', 'rujukan', 'resepObats'));
    }

    public function update(Request $request, $id)
    {
        $skrining = Skrining::findOrFail($id);
        $rsId = Auth::user()->rumahSakit->id ?? null;

        $validated = $request->validate([
            'pasien_datang' => 'nullable|boolean',
            'riwayat_tekanan_darah' => 'nullable|string',
            'hasil_protein_urin' => 'nullable|string',
            'perlu_pemeriksaan_lanjut' => 'nullable|boolean',
            'catatan_rujukan' => 'nullable|string',
            
            // Resep obat (array)
            'resep_obat' => 'nullable|array',
            'resep_obat.*' => 'nullable|string',
            'dosis' => 'nullable|array',
            'dosis.*' => 'nullable|string',
            'penggunaan' => 'nullable|array',
            'penggunaan.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($skrining, $rsId, $validated) {
            // Update atau create rujukan RS
            $rujukan = RujukanRs::updateOrCreate(
                [
                    'skrining_id' => $skrining->id,
                    'pasien_id' => $skrining->pasien_id,
                    'rs_id' => $rsId
                ],
                [
                    'pasien_datang' => $validated['pasien_datang'] ?? null,
                    'riwayat_tekanan_darah' => $validated['riwayat_tekanan_darah'] ?? null,
                    'hasil_protein_urin' => $validated['hasil_protein_urin'] ?? null,
                    'perlu_pemeriksaan_lanjut' => $validated['perlu_pemeriksaan_lanjut'] ?? null,
                    'catatan_rujukan' => $validated['catatan_rujukan'] ?? null,
                    'done_status' => true, // Tandai sudah diproses
                ]
            );

            // Hapus resep obat lama
            ResepObat::where('riwayat_rujukan_id', $rujukan->id)->delete();

            // Simpan resep obat baru
            if (!empty($validated['resep_obat'])) {
                foreach ($validated['resep_obat'] as $index => $obat) {
                    if (!empty($obat)) {
                        ResepObat::create([
                            'riwayat_rujukan_id' => $rujukan->id,
                            'resep_obat' => $obat,
                            'dosis' => $validated['dosis'][$index] ?? null,
                            'penggunaan' => $validated['penggunaan'][$index] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('rs.skrining.show', $id)
            ->with('success', 'Data berhasil disimpan!');
    }
}