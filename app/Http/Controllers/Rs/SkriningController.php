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
        $rsId = Auth::user()->rumahSakit->id ?? null;

        $skrinings = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->orderByDesc('created_at')
            ->paginate(10);

        $skrinings->getCollection()->transform(function ($rujukan) {
            $skr = $rujukan->skrining;
            $pas = optional($skr)->pasien;
            $usr = optional($pas)->user;
            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));
            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0 || in_array($raw, ['beresiko','berisiko','risiko tinggi','tinggi']);
            $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0 || in_array($raw, ['waspada','menengah','sedang','risiko sedang']);
            $rujukan->risk_display = $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Tidak Berisiko');
            $rujukan->tanggal_display = optional($skr->created_at)->format('d/m/Y');
            $rujukan->nik_display = $pas->nik ?? '-';
            $rujukan->nama_display = $usr->name ?? 'Nama Tidak Tersedia';
            $rujukan->alamat_display = $pas->PKecamatan ?? $pas->PWilayah ?? '-';
            $rujukan->telp_display = $usr->phone ?? $pas->no_telepon ?? '-';
            $rujukan->detail_url = route('rs.skrining.show', $skr->id ?? 0);
            $rujukan->process_url = route('rs.skrining.edit', $skr->id ?? 0);
            return $rujukan;
        });

        return view('rs.skrining.index', compact('skrinings'));
    }

    // Method baru untuk detail view (readonly)
    public function show($id)
    {
        // Ambil data skrining dengan semua relasi
        $skrining = Skrining::with([
            'pasien.user',
            'kondisiKesehatan',
            'riwayatKehamilanGpa',
            'puskesmas'
        ])->findOrFail($id);

        // Ambil rujukan RS jika ada
        $rsId = Auth::user()->rumahSakit->id ?? null;
        $rujukan = RujukanRs::where('skrining_id', $skrining->id)
            ->where('rs_id', $rsId)
            ->first();

        // Ambil resep obat via tabel riwayat_rujukans -> resep_obats
        $riwayatRujukan = $rujukan
            ? DB::table('riwayat_rujukans')->where('rujukan_id', $rujukan->id)->first()
            : null;

        $resepObats = $riwayatRujukan
            ? ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get()
            : collect();

        return view('rs.skrining.show', compact('skrining', 'rujukan', 'resepObats'));
    }

    // Method untuk form edit/input
    public function edit($id)
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

        // Ambil resep obat yang sudah ada (fixed column)
        $riwayatRujukan = DB::table('riwayat_rujukans')
            ->where('rujukan_id', $rujukan->id)
            ->first();

        $resepObats = $riwayatRujukan
            ? ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get()
            : collect();

        return view('rs.skrining.edit', compact('skrining', 'rujukan', 'resepObats'));
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
                    'done_status' => true,
                ]
            );

            // Hapus resep obat lama
            ResepObat::where('rujukan_rs_id', $rujukan->id)->delete();

            // Simpan resep obat baru
            if (!empty($validated['resep_obat'])) {
                foreach ($validated['resep_obat'] as $index => $obat) {
                    if (!empty($obat)) {
                        ResepObat::create([
                            'rujukan_rs_id' => $rujukan->id,
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
