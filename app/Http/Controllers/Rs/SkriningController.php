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
            ->where('done_status', true)
            ->where(function ($q) {
                $q->whereNotNull('pasien_datang')
                    ->orWhereNotNull('riwayat_tekanan_darah')
                    ->orWhereNotNull('hasil_protein_urin')
                    ->orWhereNotNull('perlu_pemeriksaan_lanjut')
                    ->orWhereNotNull('catatan_rujukan');
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        $skrinings->getCollection()->transform(function ($rujukan) {
            $skr = $rujukan->skrining;
            $pas = optional($skr)->pasien;
            $usr = optional($pas)->user;
            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));
            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0
                || in_array($raw, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);
            $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0
                || in_array($raw, ['waspada', 'menengah', 'sedang', 'risiko sedang']);

            $rujukan->nik        = $pas->nik ?? '-';
            $rujukan->nama       = $usr->name ?? 'Nama Tidak Tersedia';
            $rujukan->tanggal    = optional($skr->created_at)->format('d/m/Y');
            $rujukan->alamat     = $pas->PKecamatan ?? $pas->PWilayah ?? '-';
            $rujukan->telp       = $usr->phone ?? $pas->no_telepon ?? '-';
            $rujukan->kesimpulan = $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Tidak Berisiko');
            $rujukan->detail_url = route('rs.skrining.show', $skr->id ?? 0);
            $rujukan->process_url = route('rs.skrining.edit', $skr->id ?? 0);
            return $rujukan;
        });

        return view('rs.skrining.index', compact('skrinings'));
    }

    // Method baru untuk detail view (readonly)
    public function show($id)
    {
        $skrining = Skrining::with([
            'pasien.user',
            'kondisiKesehatan',
            'riwayatKehamilanGpa',
            'puskesmas'
        ])->findOrFail($id);

        $rsId = Auth::user()->rumahSakit->id ?? null;

        $rujukan = RujukanRs::where('skrining_id', $skrining->id)
            ->where('rs_id', $rsId)
            ->first();

        // ================ RESEP OBAT ================
        $resepObats = collect();

        if ($rujukan) {
            // Struktur BARU: langsung ke rujukan_rs_id
            $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

            // Kalau masih kosong, kemungkinan data lama (pakai riwayat_rujukan_id)
            if ($resepObats->isEmpty()) {
                $riwayat = DB::table('riwayat_rujukans')
                    ->where('rujukan_id', $rujukan->id)
                    ->first();

                if ($riwayat) {
                    $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayat->id)->get();
                }
            }
        }

        return view('rs.skrining.show', compact('skrining', 'rujukan', 'resepObats'));
    }



    // Method untuk form edit/input
    public function edit($id)
    {
        $skrining = Skrining::with([
            'pasien.user',
            'kondisiKesehatan',
            'riwayatKehamilanGpa',
            'puskesmas'
        ])->findOrFail($id);

        $rsId = Auth::user()->rumahSakit->id ?? null;

        $rujukan = RujukanRs::firstOrCreate(
            [
                'skrining_id' => $skrining->id,
                'pasien_id'   => $skrining->pasien_id,
                'rs_id'       => $rsId,
            ],
            [
                'done_status' => false,
                'is_rujuk'    => false,
            ]
        );

        // ============ RIWAYAT RUJUKAN (untuk tindakan) ============
        $riwayatRujukan = DB::table('riwayat_rujukans')
            ->where('rujukan_id', $rujukan->id)
            ->first();

        // ============ RESEP OBAT ============
        $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

        // fallback ke struktur lama kalau kosong
        if ($resepObats->isEmpty() && $riwayatRujukan) {
            $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get();
        }

        return view('rs.skrining.edit', compact(
            'skrining',
            'rujukan',
            'resepObats',
            'riwayatRujukan'   // 👈 penting untuk view tindakan
        ));
    }



    public function update(Request $request, $id)
    {
        $skrining = Skrining::findOrFail($id);
        $rsId = Auth::user()->rumahSakit->id ?? null;

        $validated = $request->validate([
            'pasien_datang'            => 'nullable|boolean',
            'riwayat_tekanan_darah'    => 'nullable|string',
            'hasil_protein_urin'       => 'nullable|string',
            'perlu_pemeriksaan_lanjut' => 'nullable|boolean',
            'catatan_rujukan'          => 'nullable|string',
            'tindakan'                 => 'nullable|string',

            'resep_obat'      => 'nullable|array',
            'resep_obat.*'    => 'nullable|string',
            'dosis'           => 'nullable|array',
            'dosis.*'         => 'nullable|string',
            'penggunaan'      => 'nullable|array',
            'penggunaan.*'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($skrining, $rsId, $validated) {
            // ============ RUJUKAN_RS (lanjutan pemeriksaan) ============
            $rujukan = RujukanRs::updateOrCreate(
                [
                    'skrining_id' => $skrining->id,
                    'pasien_id'   => $skrining->pasien_id,
                    'rs_id'       => $rsId
                ],
                [
                    'pasien_datang'            => $validated['pasien_datang'] ?? null,
                    'riwayat_tekanan_darah'    => $validated['riwayat_tekanan_darah'] ?? null,
                    'hasil_protein_urin'       => $validated['hasil_protein_urin'] ?? null,
                    'perlu_pemeriksaan_lanjut' => $validated['perlu_pemeriksaan_lanjut'] ?? null,
                    'catatan_rujukan'          => $validated['catatan_rujukan'] ?? null,
                    'done_status'              => true,
                ]
            );

            // ============ RIWAYAT_RUJUKANS (khusus tindakan) ============
            $existingRiwayat = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->first();

            $now = now();

            if ($existingRiwayat) {
                DB::table('riwayat_rujukans')
                    ->where('id', $existingRiwayat->id)
                    ->update([
                        'tindakan'   => $validated['tindakan'] ?? null,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('riwayat_rujukans')->insert([
                    'rujukan_id'          => $rujukan->id,
                    'skrining_id'         => $skrining->id,
                    'tanggal_datang'      => $now->toDateString(), // boleh disesuaikan
                    'tekanan_darah'       => $validated['riwayat_tekanan_darah'] ?? null,
                    'anjuran_kontrol'     => null,
                    'kunjungan_berikutnya' => null,
                    'tindakan'            => $validated['tindakan'] ?? null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }

            // ============ RESEP OBAT (pakai rujukan_rs_id) ============
            ResepObat::where('rujukan_rs_id', $rujukan->id)->delete();

            if (!empty($validated['resep_obat'])) {
                foreach ($validated['resep_obat'] as $index => $obat) {
                    if (!empty($obat)) {
                        ResepObat::create([
                            'rujukan_rs_id' => $rujukan->id,
                            'resep_obat'    => $obat,
                            'dosis'         => $validated['dosis'][$index] ?? null,
                            'penggunaan'    => $validated['penggunaan'][$index] ?? null,
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