<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Skrining;
use App\Models\RujukanRs;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SkriningController extends Controller
{
    public function index()
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // ✅ PERBAIKAN: Ambil semua rujukan yang sudah diterima
        $skrinings = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->where('done_status', true)  // Sudah diterima
            ->where('is_rujuk', true)     // Sudah dirujuk
            ->orderByDesc('created_at')
            ->paginate(10);

        $skrinings->getCollection()->transform(function ($rujukan) {
            $skr = $rujukan->skrining;
            $pas = optional($skr)->pasien;
            $usr = optional($pas)->user;

            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));
            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0
                || in_array($raw, ['beresiko','berisiko','risiko tinggi','tinggi']);
            $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0
                || in_array($raw, ['waspada','menengah','sedang','risiko sedang']);

            $rujukan->nik        = $pas->nik ?? '-';
            $rujukan->nama       = $usr->name ?? 'Nama Tidak Tersedia';
            $rujukan->tanggal    = optional($skr->created_at)->format('d/m/Y');
            $rujukan->alamat     = $pas->PKecamatan ?? $pas->PWilayah ?? '-';
            $rujukan->telp       = $usr->phone ?? $pas->no_telepon ?? '-';
            // ✅ PERBAIKAN: Ganti format risiko sama seperti rujukan
            $rujukan->kesimpulan = $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Tidak Berisiko');

            $rujukan->detail_url  = route('rs.skrining.show', $skr->id);
            $rujukan->process_url = route('rs.skrining.edit', $skr->id);

            return $rujukan;
        });

        return view('rs.skrining.index', compact('skrinings'));
    }

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

        $resepObats = collect();

        if ($rujukan) {
            // PRIORITAS: pakai relasi rujukan_rs_id (data existing di DB sekarang)
            $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

            // FALLBACK (jika suatu saat ada data yang pakai riwayat_rujukan_id)
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

        $riwayatRujukan = DB::table('riwayat_rujukans')
            ->where('rujukan_id', $rujukan->id)
            ->first();

        // PRIORITAS: resep berdasarkan rujukan_rs_id
        $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

        // FALLBACK: kalau kosong dan ada riwayat_rujukan, coba ambil via riwayat_rujukan_id
        if ($resepObats->isEmpty() && $riwayatRujukan) {
            $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get();
        }

        return view('rs.skrining.edit', compact(
            'skrining',
            'rujukan',
            'resepObats',
            'riwayatRujukan'
        ));
    }

    public function update(Request $request, $id)
    {
        $skrining = Skrining::findOrFail($id);
        $rsId = Auth::user()->rumahSakit->id ?? null;

        $validated = $request->validate([
            // kolom yang ada
            'catatan_rujukan'       => 'nullable|string',
            'riwayat_tekanan_darah' => 'nullable|string',
            'tindakan'              => 'nullable|string',

            'resep_obat'      => 'nullable|array',
            'resep_obat.*'    => 'nullable|string',
            'dosis'           => 'nullable|array',
            'dosis.*'         => 'nullable|string',
            'penggunaan'      => 'nullable|array',
            'penggunaan.*'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($skrining, $rsId, $validated) {
            $rujukan = RujukanRs::updateOrCreate(
                [
                    'skrining_id' => $skrining->id,
                    'pasien_id'   => $skrining->pasien_id,
                    'rs_id'       => $rsId
                ],
                [
                    'catatan_rujukan' => $validated['catatan_rujukan'] ?? null,
                    'done_status'     => true,
                    'is_rujuk'        => true,
                ]
            );

            // ================== RIWAYAT_RUJUKANS ==================
            $existingRiwayat = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->first();

            $now = now();

            if ($existingRiwayat) {
                DB::table('riwayat_rujukans')
                    ->where('id', $existingRiwayat->id)
                    ->update([
                        'tindakan'      => $validated['tindakan'] ?? null,
                        'tekanan_darah' => $validated['riwayat_tekanan_darah'] ?? null,
                        'updated_at'    => $now,
                    ]);

                $riwayatId = $existingRiwayat->id;
            } else {
                $riwayatId = DB::table('riwayat_rujukans')->insertGetId([
                    'rujukan_id'     => $rujukan->id,
                    'skrining_id'    => $skrining->id,
                    'tanggal_datang' => $now->toDateString(),
                    'tekanan_darah'  => $validated['riwayat_tekanan_darah'] ?? null,
                    'tindakan'       => $validated['tindakan'] ?? null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }

            // ================== RESEP OBAT ==================
            // Hapus resep lama untuk rujukan ini (pakai rujukan_rs_id)
            ResepObat::where('rujukan_rs_id', $rujukan->id)->delete();

            if (!empty($validated['resep_obat'])) {
                foreach ($validated['resep_obat'] as $index => $obat) {
                    if (!empty($obat)) {
                        ResepObat::create([
                            'rujukan_rs_id'      => $rujukan->id,   // 👈 WAJIB, supaya tidak NULL
                            // Opsional: kalau mau disimpan juga ke riwayat_rujukan_id
                            'resep_obat'         => $obat,
                            'dosis'              => $validated['dosis'][$index] ?? null,
                            'penggunaan'         => $validated['penggunaan'][$index] ?? null,
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