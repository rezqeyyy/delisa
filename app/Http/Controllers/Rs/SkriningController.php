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
use Barryvdh\DomPDF\Facade\Pdf;

class SkriningController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | METHOD: index()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan daftar pasien rujukan dengan fitur filter
    | Filter: NIK, Nama, Tanggal (dari-sampai), Status Risiko
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // Query Builder
        $query = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->where('done_status', true)
            ->where('is_rujuk', true);

        // Filter berdasarkan NIK
        if ($request->filled('nik')) {
            $query->whereHas('skrining.pasien', function ($q) use ($request) {
                $q->where('nik', 'like', '%' . $request->nik . '%');
            });
        }

        // Filter berdasarkan Nama Pasien
        if ($request->filled('nama')) {
            $query->whereHas('skrining.pasien.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        // Filter berdasarkan Tanggal Mulai
        if ($request->filled('tanggal_dari')) {
            $query->whereHas('skrining', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->tanggal_dari);
            });
        }

        // Filter berdasarkan Tanggal Sampai
        if ($request->filled('tanggal_sampai')) {
            $query->whereHas('skrining', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->tanggal_sampai);
            });
        }

        // Filter berdasarkan Status Risiko
        if ($request->filled('risiko')) {
            $risikoFilter = $request->risiko;

            $query->whereHas('skrining', function ($q) use ($risikoFilter) {
                if ($risikoFilter === 'Beresiko') {
                    $q->where(function ($subQ) {
                        $subQ->where('jumlah_resiko_tinggi', '>', 0)
                            ->orWhereRaw("LOWER(TRIM(kesimpulan)) IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi')")
                            ->orWhereRaw("LOWER(TRIM(status_pre_eklampsia)) IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi')");
                    });
                } elseif ($risikoFilter === 'Waspada') {
                    $q->where(function ($subQ) {
                        $subQ->where('jumlah_resiko_sedang', '>', 0)
                            ->orWhereRaw("LOWER(TRIM(kesimpulan)) IN ('waspada', 'menengah', 'sedang', 'risiko sedang')")
                            ->orWhereRaw("LOWER(TRIM(status_pre_eklampsia)) IN ('waspada', 'menengah', 'sedang', 'risiko sedang')");
                    })->where('jumlah_resiko_tinggi', '<=', 0)
                        ->whereRaw("LOWER(TRIM(COALESCE(kesimpulan, ''))) NOT IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi')");
                } elseif ($risikoFilter === 'Tidak Berisiko') {
                    $q->where(function ($subQ) {
                        $subQ->where('jumlah_resiko_tinggi', '<=', 0)
                            ->where('jumlah_resiko_sedang', '<=', 0)
                            ->whereRaw("LOWER(TRIM(COALESCE(kesimpulan, ''))) NOT IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi', 'waspada', 'menengah', 'sedang', 'risiko sedang')")
                            ->whereRaw("LOWER(TRIM(COALESCE(status_pre_eklampsia, ''))) NOT IN ('beresiko', 'berisiko', 'risiko tinggi', 'tinggi', 'waspada', 'menengah', 'sedang', 'risiko sedang')");
                    });
                }
            });
        }

        // Order & Paginate
        $skrinings = $query->orderByDesc('created_at')->paginate(10);

        // Transform Data
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

            $rujukan->kesimpulan = $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' :
                'Tidak Berisiko');

            $rujukan->detail_url  = route('rs.skrining.show', $skr->id);
            $rujukan->process_url = route('rs.skrining.edit', $skr->id);

            return $rujukan;
        });

        // Append query string ke pagination
        $skrinings->appends($request->all());

        return view('rs.skrining.index', compact('skrinings'));
    }

    // Sisanya tetap sama seperti kode original...
    public function show($id)
    {
        $skrining = Skrining::with([
            'pasien.user',
            'kondisiKesehatan',
            'riwayatKehamilanGpa',
            'puskesmas'
        ])->findOrFail($id);

        $rsId = Auth::user()->rumahSakit->id ?? null;

        // 🔧 AMBIL RUJUKAN TERBARU UNTUK SKRINING + RS INI
        $rujukan = RujukanRs::where('skrining_id', $skrining->id)
            ->where('rs_id', $rsId)
            ->orderByDesc('created_at')
            ->first();

        $resepObats = collect();
        $riwayatRujukan = null;

        if ($rujukan) {
            $riwayatRujukan = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->first();

            $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

            if ($resepObats->isEmpty() && $riwayatRujukan) {
                $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get();
            }
        }

        return view('rs.skrining.show', compact('skrining', 'rujukan', 'riwayatRujukan', 'resepObats'));
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

        // 🔧 CARI RUJUKAN TERBARU UNTUK SKRINING + RS INI
        $rujukan = RujukanRs::where('skrining_id', $skrining->id)
            ->where('pasien_id', $skrining->pasien_id)
            ->where('rs_id', $rsId)
            ->orderByDesc('created_at')
            ->first();

        // Kalau belum pernah ada rujukan sama sekali, baru create
        if (! $rujukan) {
            $rujukan = RujukanRs::create([
                'skrining_id' => $skrining->id,
                'pasien_id'   => $skrining->pasien_id,
                'rs_id'       => $rsId,
                'done_status' => false,
                'is_rujuk'    => false,
            ]);
        }

        $riwayatRujukan = DB::table('riwayat_rujukans')
            ->where('rujukan_id', $rujukan->id)
            ->first();

        $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

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
            'pasien_datang'              => 'nullable|boolean',
            'perlu_pemeriksaan_lanjut'   => 'nullable|boolean',
            'catatan_rujukan'            => 'nullable|string',
            'riwayat_tekanan_darah'      => 'nullable|string',
            'hasil_protein_urin'         => 'nullable|string',
            'tindakan'                   => 'nullable|string',
            'catatan'                    => 'nullable|string',
            'anjuran_kontrol'            => 'nullable|in:fktp,rs',
            'kunjungan_berikutnya'       => 'nullable|string',
            'resep_obat'                 => 'nullable|array',
            'resep_obat.*'               => 'nullable|string',
            'dosis'                      => 'nullable|array',
            'dosis.*'                    => 'nullable|string',
            'penggunaan'                 => 'nullable|array',
            'penggunaan.*'               => 'nullable|string',
        ]);

        DB::transaction(function () use ($skrining, $rsId, $validated) {

            // 🔧 CARI RUJUKAN TERBARU UNTUK SKRINING + RS INI
            $rujukan = RujukanRs::where('skrining_id', $skrining->id)
                ->where('pasien_id', $skrining->pasien_id)
                ->where('rs_id', $rsId)
                ->orderByDesc('created_at')
                ->first();

            if (! $rujukan) {
                // Kalau entah bagaimana belum ada rujukan, kita buat baru
                $rujukan = RujukanRs::create([
                    'skrining_id'            => $skrining->id,
                    'pasien_id'              => $skrining->pasien_id,
                    'rs_id'                  => $rsId,
                    'pasien_datang'          => $validated['pasien_datang'] ?? null,
                    'perlu_pemeriksaan_lanjut' => $validated['perlu_pemeriksaan_lanjut'] ?? null,
                    'riwayat_tekanan_darah'  => $validated['riwayat_tekanan_darah'] ?? null,
                    'hasil_protein_urin'     => $validated['hasil_protein_urin'] ?? null,
                    'catatan_rujukan'        => $validated['catatan_rujukan'] ?? null,
                    'done_status'            => true,
                    'is_rujuk'               => true,
                ]);
            } else {
                // Update rujukan yang TERBARU
                $rujukan->update([
                    'pasien_datang'            => $validated['pasien_datang'] ?? null,
                    'perlu_pemeriksaan_lanjut' => $validated['perlu_pemeriksaan_lanjut'] ?? null,
                    'riwayat_tekanan_darah'    => $validated['riwayat_tekanan_darah'] ?? null,
                    'hasil_protein_urin'       => $validated['hasil_protein_urin'] ?? null,
                    'catatan_rujukan'          => $validated['catatan_rujukan'] ?? null,
                    'done_status'              => true,
                    'is_rujuk'                 => true,
                ]);
            }

            $existingRiwayat = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->first();

            $now = now();

            if ($existingRiwayat) {
                DB::table('riwayat_rujukans')
                    ->where('id', $existingRiwayat->id)
                    ->update([
                        'tindakan'              => $validated['tindakan'] ?? null,
                        'tekanan_darah'         => $validated['riwayat_tekanan_darah'] ?? null,
                        'catatan'               => $validated['catatan'] ?? null,
                        'anjuran_kontrol'       => $validated['anjuran_kontrol'] ?? null,
                        'kunjungan_berikutnya'  => $validated['kunjungan_berikutnya'] ?? null,
                        'updated_at'            => $now,
                    ]);

                $riwayatId = $existingRiwayat->id;
            } else {
                $riwayatId = DB::table('riwayat_rujukans')->insertGetId([
                    'rujukan_id'            => $rujukan->id,
                    'skrining_id'           => $skrining->id,
                    'tanggal_datang'        => $now->toDateString(),
                    'tekanan_darah'         => $validated['riwayat_tekanan_darah'] ?? null,
                    'tindakan'              => $validated['tindakan'] ?? null,
                    'catatan'               => $validated['catatan'] ?? null,
                    'anjuran_kontrol'       => $validated['anjuran_kontrol'] ?? null,
                    'kunjungan_berikutnya'  => $validated['kunjungan_berikutnya'] ?? null,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }

            // Resep obat: tetap sama seperti punyamu, hanya pastikan pakai $rujukan->id & $riwayatId yang benar
            ResepObat::where(function ($query) use ($rujukan, $riwayatId) {
                $query->where('rujukan_rs_id', $rujukan->id);

                if ($riwayatId) {
                    $query->orWhere('riwayat_rujukan_id', $riwayatId);
                }
            })->delete();

            if (!empty($validated['resep_obat'])) {
                foreach ($validated['resep_obat'] as $index => $obat) {
                    if (!empty($obat)) {
                        ResepObat::create([
                            'rujukan_rs_id'      => $rujukan->id,
                            'riwayat_rujukan_id' => $riwayatId,
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


    public function exportPdf($id)
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
            ->orderByDesc('created_at')
            ->first();


        $resepObats = collect();
        $riwayatRujukan = null;

        if ($rujukan) {
            $riwayatRujukan = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->first();

            $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

            if ($resepObats->isEmpty() && $riwayatRujukan) {
                $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get();
            }
        }

        $rumahSakit = Auth::user()->rumahSakit;

        $pdf = Pdf::loadView('rs.skrining.pdf', compact(
            'skrining',
            'rujukan',
            'riwayatRujukan',
            'resepObats',
            'rumahSakit'
        ));

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Hasil_Pemeriksaan_' . ($skrining->pasien->user->name ?? 'Pasien') . '_' . now()->format('Y-m-d') . '.pdf';

        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

        return $pdf->download($filename);
    }
}
