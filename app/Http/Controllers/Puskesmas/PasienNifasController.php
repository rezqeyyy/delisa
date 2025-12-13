<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Models\PasienNifasRs;
use App\Models\PasienNifasBidan;
use App\Models\KfKunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PasienNifasController extends Controller
{
    /**
     * Menampilkan daftar pasien nifas dari RS dan Bidan
     * Filter berdasarkan kecamatan puskesmas user
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // Dapatkan data puskesmas user
        $puskesmas = DB::table('puskesmas')
            ->select('id', 'kecamatan', 'nama_puskesmas')
            ->where('user_id', $userId)
            ->first();
        
        if (!$puskesmas) {
            return view('puskesmas.pasien-nifas.index', [
                'dataRs' => collect(),
                'dataBidan' => collect(),
                'totalPasienNifas' => 0,
                'sudahKFI' => 0,
                'belumKFI' => 0,
                'kecamatanPuskesmas' => null,
                'namaPuskesmas' => null,
                'type' => $request->get('type', 'all')
            ]);
        }
        
        $kecamatanPuskesmas = $puskesmas->kecamatan;
        $namaPuskesmas = $puskesmas->nama_puskesmas;
        $type = $request->get('type', 'all');
        $search = trim($request->get('search'));
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSelesai = $request->get('tanggal_selesai');

        
        // DATA DARI RS
        $dataRs = collect();
        if ($type === 'all' || $type === 'rs') {
            $dataRs = PasienNifasRs::with(['pasien.user', 'rs'])
    ->whereHas('pasien', function ($query) use (
        $kecamatanPuskesmas,
        $search,
        $tanggalMulai,
        $tanggalSelesai
    ) {

        // Filter kecamatan (WAJIB)
        $query->whereRaw(
            'LOWER("pasiens"."PKecamatan") = LOWER(?)',
            [$kecamatanPuskesmas]
        );

        // 🔍 SEARCH NAMA PASIEN (case-insensitive)
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%' . strtolower($search) . '%']
                );
            });
        }

        // 📅 FILTER TANGGAL MULAI NIFAS
        if ($tanggalMulai) {
            $query->whereDate('tanggal_mulai_nifas', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_mulai_nifas', '<=', $tanggalSelesai);
        }
    })
    ->orderBy('created_at', 'desc')
    ->paginate(10)
    ->withQueryString();
        }
        
        // DATA DARI BIDAN
        $dataBidan = collect();
        if ($type === 'all' || $type === 'bidan') {
            $dataBidan = PasienNifasBidan::with(['pasien.user', 'bidan'])
                ->whereHas('pasien', function($query) use ($kecamatanPuskesmas) {
                    $query->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatanPuskesmas]);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Hitung statistik untuk SEMUA data
        $totalPasienNifas = ($dataRs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $dataRs->total() : $dataRs->count()) 
                          + $dataBidan->count();
        
        $sudahKFI = $dataRs->filter(function($item) {
            return $item->isKfSelesai(1);
        })->count();
        
        $sudahKFI += $dataBidan->filter(function($item) {
            return $item->isKfSelesai(1);
        })->count();
        
        $belumKFI = $totalPasienNifas - $sudahKFI;
        
        return view('puskesmas.pasien-nifas.index', [
            'dataRs' => $dataRs,
            'dataBidan' => $dataBidan,
            'totalPasienNifas' => $totalPasienNifas,
            'sudahKFI' => $sudahKFI,
            'belumKFI' => $belumKFI,
            'kecamatanPuskesmas' => $kecamatanPuskesmas,
            'namaPuskesmas' => $namaPuskesmas,
            'type' => $type
        ]);
    }

    /**
     * Tampilkan detail pasien nifas (universal untuk RS dan Bidan)
     */
    public function show($type, $id)
    {
        $userId = Auth::id();
        
        // Dapatkan data puskesmas user
        $puskesmas = DB::table('puskesmas')
            ->select('id', 'kecamatan')
            ->where('user_id', $userId)
            ->first();
        
        abort_unless($puskesmas, 404, 'Puskesmas tidak ditemukan');
        
        // Pilih model berdasarkan type
        if ($type === 'rs') {
            $data = PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
                ->findOrFail($id);
            $modelClass = PasienNifasRs::class;
        } else {
            $data = PasienNifasBidan::with(['pasien.user', 'bidan', 'anakPasien'])
                ->findOrFail($id);
            $modelClass = PasienNifasBidan::class;
        }
        
        // Validasi akses: cek apakah pasien dari kecamatan yang sama
        $kecamatanPasien = optional($data->pasien)->PKecamatan;
        $allowed = ($kecamatanPasien === $puskesmas->kecamatan);
        
        abort_unless($allowed, 403, 'Anda tidak memiliki akses ke data pasien ini.');
        
        // Ambil semua data KF untuk pasien ini (menggunakan polymorphic)
        $kfKunjungans = KfKunjungan::where('nifasable_id', $id)
            ->where('nifasable_type', $modelClass)
            ->orderBy('jenis_kf')
            ->get();
        
        // Cek KF meninggal
        $deathKe = $kfKunjungans
            ->filter(function($kf) {
                return $kf->is_meninggal;
            })
            ->min('jenis_kf');
        
        return view('puskesmas.pasien-nifas.show', compact(
            'data', 'deathKe', 'kfKunjungans', 'type'
        ));
    }

    /**
     * Form untuk mencatat KF (universal untuk RS dan Bidan)
     */
    public function formCatatKf($type, $id, $jenisKf)
    {
        $userId = Auth::id();
        
        // Dapatkan data puskesmas user
        $puskesmas = DB::table('puskesmas')
            ->select('id', 'kecamatan')
            ->where('user_id', $userId)
            ->first();
        
        abort_unless($puskesmas, 404, 'Puskesmas tidak ditemukan');
        
        // Pilih model berdasarkan type
        if ($type === 'rs') {
            $data = PasienNifasRs::with(['pasien.user'])->findOrFail($id);
            $modelClass = PasienNifasRs::class;
        } else {
            $data = PasienNifasBidan::with(['pasien.user'])->findOrFail($id);
            $modelClass = PasienNifasBidan::class;
        }
        
        // Validasi akses
        $kecamatanPasien = optional($data->pasien)->PKecamatan;
        $allowed = ($kecamatanPasien === $puskesmas->kecamatan);
        abort_unless($allowed, 403, 'Anda tidak memiliki akses ke data pasien ini.');

        // Validasi jenis KF
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }

        // Cek apakah sudah ada KF dengan kesimpulan Meninggal/Wafat (menggunakan polymorphic)
        $deathKe = KfKunjungan::where('nifasable_id', $id)
            ->where('nifasable_type', $modelClass)
            ->where(function ($q) {
                $q->where('kesimpulan_pantauan', 'Meninggal')
                  ->orWhere('kesimpulan_pantauan', 'Wafat');
            })
            ->min('jenis_kf');

        if (!is_null($deathKe) && (int) $jenisKf > (int) $deathKe) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with(
                    'error',
                    "KF{$jenisKf} tidak dapat dicatat karena pada KF{$deathKe} pasien sudah tercatat meninggal/wafat."
                );
        }

        // Cek apakah sudah selesai
        if ($data->isKfSelesai($jenisKf)) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('error', "KF{$jenisKf} sudah selesai dicatat!");
        }

        // Cek status
        $status = $data->getKfStatus($jenisKf);

        // Untuk KF terlambat, tetap tampilkan form dengan warning
        if ($status == 'terlambat') {
            session()->flash('warning', "Periode normal KF{$jenisKf} sudah lewat. Anda tetap dapat mencatatnya sebagai kunjungan terlambat.");
        }

        // Untuk KF belum mulai, tetap block
        if ($status == 'belum_mulai') {
            $mulai = $data->getKfMulai($jenisKf);
            $pesan = $mulai
                ? "Belum waktunya untuk KF{$jenisKf}. Dapat dilakukan mulai " . $mulai->format('d/m/Y H:i')
                : "Belum dapat melakukan KF{$jenisKf}";

            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('error', $pesan);
        }

        return view('puskesmas.pasien-nifas.form-kf', compact('data', 'jenisKf', 'type'));
    }

    /**
     * Proses pencatatan KF (universal untuk RS dan Bidan)
     */
    public function catatKf(Request $request, $type, $id, $jenisKf)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'tanggal_kunjungan' => 'required|date|before_or_equal:' . now(),
            'sbp' => 'nullable|integer|min:50|max:300',
            'dbp' => 'nullable|integer|min:30|max:200',
            'map' => 'nullable|numeric|min:40|max:250',
            'keadaan_umum' => 'nullable|string|max:1000',
            'tanda_bahaya' => 'nullable|string|max:1000',
            'kesimpulan_pantauan' => 'required|in:Sehat,Dirujuk,Meninggal',
            'catatan' => 'nullable|string|max:2000',
        ], [
            'tanggal_kunjungan.before_or_equal' => 'Tanggal kunjungan tidak boleh lebih dari hari ini',
            'sbp.min' => 'SBP tidak valid',
            'dbp.min' => 'DBP tidak valid',
            'map.min' => 'MAP tidak valid',
            'kesimpulan_pantauan.required' => 'Kesimpulan pantauan harus dipilih',
            'kesimpulan_pantauan.in' => 'Pilihan kesimpulan tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validasi akses
        $userId = Auth::id();
        $puskesmas = DB::table('puskesmas')
            ->select('id', 'kecamatan')
            ->where('user_id', $userId)
            ->first();
        
        abort_unless($puskesmas, 404, 'Puskesmas tidak ditemukan');
        
        // Pilih model berdasarkan type
        if ($type === 'rs') {
            $data = PasienNifasRs::findOrFail($id);
            $modelClass = PasienNifasRs::class;
        } else {
            $data = PasienNifasBidan::findOrFail($id);
            $modelClass = PasienNifasBidan::class;
        }
        
        $kecamatanPasien = optional($data->pasien)->PKecamatan;
        abort_unless($kecamatanPasien === $puskesmas->kecamatan, 403, 'Anda tidak memiliki akses ke data pasien ini.');
        
        // Validasi jenis KF
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }
        
        // Cek apakah sudah selesai
        if ($data->isKfSelesai($jenisKf)) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('error', "KF{$jenisKf} sudah selesai dicatat!");
        }
        
        try {
            DB::beginTransaction();
            
            // Normalisasi MAP
            $mapValue = null;
            if ($request->filled('map')) {
                $rawMap = str_replace(',', '.', $request->map);
                $mapValue = (int) round((float) $rawMap);
            }
            
            // Simpan ke KF Kunjungan (menggunakan polymorphic)
            $kfKunjungan = KfKunjungan::updateOrCreate(
                [
                    'nifasable_id' => $id,
                    'nifasable_type' => $modelClass,
                    'jenis_kf' => $jenisKf,
                ],
                [
                    'tanggal_kunjungan' => Carbon::parse($request->tanggal_kunjungan),
                    'sbp' => $request->sbp ? (int) $request->sbp : null,
                    'dbp' => $request->dbp ? (int) $request->dbp : null,
                    'map' => $mapValue,
                    'keadaan_umum' => $request->keadaan_umum,
                    'tanda_bahaya' => $request->tanda_bahaya,
                    'kesimpulan_pantauan' => $request->kesimpulan_pantauan,
                    'catatan' => $request->catatan,
                ]
            );
            
            // Update foreign key di tabel nifas
            $data->update([
                "kf{$jenisKf}_id" => $kfKunjungan->id,
                "kf{$jenisKf}_tanggal" => Carbon::parse($request->tanggal_kunjungan),
                "kf{$jenisKf}_catatan" => $request->catatan,
            ]);
            
            DB::commit();
            
            // Pesan sukses
            $status = $data->getKfStatus($jenisKf);
            $pesan = "KF{$jenisKf} berhasil dicatat!";
            
            if ($status == 'terlambat') {
                $pesan .= " (Catatan: dilakukan di luar periode normal)";
            }
            
            $pesan .= " Kesimpulan: " . $request->kesimpulan_pantauan;
            
            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('success', $pesan);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan KF: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data KF: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Download PDF untuk single KF (universal untuk RS dan Bidan)
     */
    public function downloadKfPdf($type, $id, $jenisKf)
    {
        try {
            $userId = Auth::id();
            $puskesmas = DB::table('puskesmas')
                ->select('id', 'kecamatan')
                ->where('user_id', $userId)
                ->first();
            
            abort_unless($puskesmas, 404, 'Puskesmas tidak ditemukan');
            
            // Pilih model berdasarkan type
            if ($type === 'rs') {
                $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);
                $modelClass = PasienNifasRs::class;
            } else {
                $pasienNifas = PasienNifasBidan::with(['pasien.user', 'bidan'])->findOrFail($id);
                $modelClass = PasienNifasBidan::class;
            }
            
            // Validasi akses
            $kecamatanPasien = optional($pasienNifas->pasien)->PKecamatan;
            abort_unless($kecamatanPasien === $puskesmas->kecamatan, 403, 'Anda tidak memiliki akses ke data pasien ini.');

            // Cek KF (menggunakan polymorphic)
            $kfKunjungan = KfKunjungan::where('nifasable_id', $id)
                ->where('nifasable_type', $modelClass)
                ->where('jenis_kf', $jenisKf)
                ->first();

            if (!$kfKunjungan) {
                return back()->with('error', "KF{$jenisKf} belum dicatat untuk pasien ini");
            }

            // Siapkan data untuk PDF
            $data = [
                'pasienNifas' => $pasienNifas,
                'kfKunjungan' => $kfKunjungan,
                'jenisKf' => $jenisKf,
                'type' => $type,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title' => "Laporan KF{$jenisKf} - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('puskesmas.pdf.kf-single', $data);

            // Nama file PDF
            $fileName = "KF{$jenisKf}_" .
                str_replace(' ', '_', $pasienNifas->pasien->user->name ?? 'pasien') . "_" .
                now()->format('Ymd_His') . '.pdf';

            // Download PDF
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error generating KF PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF untuk semua KF (universal untuk RS dan Bidan)
     */
    public function downloadAllKfPdf($type, $id)
    {
        try {
            $userId = Auth::id();
            $puskesmas = DB::table('puskesmas')
                ->select('id', 'kecamatan')
                ->where('user_id', $userId)
                ->first();
            
            abort_unless($puskesmas, 404, 'Puskesmas tidak ditemukan');
            
            // Pilih model berdasarkan type
            if ($type === 'rs') {
                $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);
                $modelClass = PasienNifasRs::class;
            } else {
                $pasienNifas = PasienNifasBidan::with(['pasien.user', 'bidan'])->findOrFail($id);
                $modelClass = PasienNifasBidan::class;
            }
            
            // Validasi akses
            $kecamatanPasien = optional($pasienNifas->pasien)->PKecamatan;
            abort_unless($kecamatanPasien === $puskesmas->kecamatan, 403, 'Anda tidak memiliki akses ke data pasien ini.');

            // Ambil semua KF (menggunakan polymorphic)
            $kfKunjungan = KfKunjungan::where('nifasable_id', $id)
                ->where('nifasable_type', $modelClass)
                ->orderBy('jenis_kf')
                ->get();

            if ($kfKunjungan->isEmpty()) {
                return back()->with('error', 'Belum ada KF yang dicatat untuk pasien ini');
            }

            // Siapkan data untuk PDF
            $data = [
                'pasienNifas' => $pasienNifas,
                'kfKunjungan' => $kfKunjungan,
                'type' => $type,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title' => "Laporan Semua KF - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('puskesmas.pdf.kf-all', $data);

            // Nama file PDF
            $fileName = "Semua_KF_" .
                str_replace(' ', '_', $pasienNifas->pasien->user->name ?? 'pasien') . "_" .
                now()->format('Ymd_His') . '.pdf';

            // Download PDF
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error generating All KF PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    // ========== LEGACY METHODS (UNTUK ROUTE LAMA) ==========
    
    /**
     * Legacy method untuk show RS (tanpa parameter type)
     */
    public function showRs($id)
    {
        return $this->show('rs', $id);
    }

    /**
     * Legacy method untuk form catat KF RS (tanpa parameter type)
     */
    public function formCatatKfLegacy($id, $jenisKf)
    {
        return $this->formCatatKf('rs', $id, $jenisKf);
    }

    /**
     * Legacy method untuk catat KF RS (tanpa parameter type)
     */
    public function catatKfLegacy(Request $request, $id, $jenisKf)
    {
        return $this->catatKf($request, 'rs', $id, $jenisKf);
    }

    /**
     * Legacy method untuk download PDF KF (tanpa parameter type)
     */
    public function downloadKfPdfLegacy($id, $jenisKf)
    {
        return $this->downloadKfPdf('rs', $id, $jenisKf);
    }

    /**
     * Legacy method untuk download semua PDF KF (tanpa parameter type)
     */
    public function downloadAllKfPdfLegacy($id)
    {
        return $this->downloadAllKfPdf('rs', $id);
    }
}