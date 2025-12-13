<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Models\PasienNifasRs;
use App\Models\KfKunjungan; // TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PasienNifasController extends Controller
{
    /**
     * Menampilkan daftar pasien nifas dari rumah sakit beserta statistiknya.
     */
    public function index()
    {
        $user = auth()->user();

        // Ambil puskesmas berdasarkan user login
        $puskesmas = \App\Models\Puskesmas::where('user_id', $user->id)->firstOrFail();

        // Ambil pasien nifas khusus puskesmas ini
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])
            ->where('puskesmas_id', $puskesmas->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalPasienNifas = PasienNifasRs::where('puskesmas_id', $puskesmas->id)->count();

        $sudahKFI = PasienNifasRs::where('puskesmas_id', $puskesmas->id)
            ->whereNotNull('kf1_tanggal')
            ->count();

        $belumKFI = $totalPasienNifas - $sudahKFI;

        return view('puskesmas.pasien-nifas.index', compact(
            'pasienNifas',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
        ));
    }


    /**
     * Tampilkan detail pasien nifas
     */
    /**
     * Tampilkan detail pasien nifas
     */
    public function show($id)
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
            ->findOrFail($id);

        // Cari KF pertama yang berkesimpulan Meninggal/Wafat
        $deathKe = KfKunjungan::query()
            ->where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                    ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->min('jenis_kf'); // bisa null jika belum ada yang wafat

        return view('puskesmas.pasien-nifas.show', compact('pasienNifas', 'deathKe'));
    }


    /**
     * Form untuk mencatat KF
     */
    public function formCatatKf($id, $jenisKf)
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user'])->findOrFail($id);

        // Validasi input jenis KF
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }

        // STOP: jika sudah ada KF dengan kesimpulan Meninggal/Wafat,
        // maka KF sesudahnya tidak boleh dicatat lagi.
        $deathKe = KfKunjungan::query()
            ->where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                    ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->selectRaw('MIN((jenis_kf)::int) as death_ke')
            ->value('death_ke');

        if (!is_null($deathKe) && (int) $jenisKf > (int) $deathKe) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', $id)
                ->with(
                    'error',
                    "KF{$jenisKf} tidak dapat dicatat karena pada KF{$deathKe} pasien sudah tercatat meninggal/wafat."
                );
        }


        // 1. Cek apakah sudah selesai
        if ($pasienNifas->isKfSelesai($jenisKf)) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', $id)
                ->with('error', "KF{$jenisKf} sudah selesai dicatat!");
        }

        // 2. Cek status
        $status = $pasienNifas->getKfStatus($jenisKf);

        // Untuk KF terlambat, tetap tampilkan form dengan warning
        if ($status == 'terlambat') {
            session()->flash('warning', "Periode normal KF{$jenisKf} sudah lewat. Anda tetap dapat mencatatnya sebagai kunjungan terlambat.");
        }

        // Untuk KF belum mulai, tetap block
        if ($status == 'belum_mulai') {
            $mulai = $pasienNifas->getKfMulai($jenisKf);
            $pesan = $mulai
                ? "Belum waktunya untuk KF{$jenisKf}. Dapat dilakukan mulai " . $mulai->format('d/m/Y H:i')
                : "Belum dapat melakukan KF{$jenisKf}";

            return redirect()
                ->route('puskesmas.pasien-nifas.show', $id)
                ->with('error', $pesan);
        }

        return view('puskesmas.pasien-nifas.form-kf', compact('pasienNifas', 'jenisKf'));
    }

    /**
     * Proses pencatatan KF (SISTEM BARU)
     */
    public function catatKf(Request $request, $id, $jenisKf)
    {
        // ========== VALIDASI BARU SESUAI REQUIREMENT PKM ==========
        $validator = Validator::make($request->all(), [
            'tanggal_kunjungan' => 'required|date|before_or_equal:' . now(),
            'sbp' => 'nullable|integer|min:50|max:300',
            'dbp' => 'nullable|integer|min:30|max:200',
            // map boleh desimal di form → numeric, nanti di-cast ke integer
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

        $pasienNifas = PasienNifasRs::findOrFail($id);

        // Validasi jenis KF
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }

        // STOP: jika sudah ada KF dengan kesimpulan Meninggal/Wafat,
        // maka KF sesudahnya tidak boleh disimpan.
        $deathKe = KfKunjungan::query()
            ->where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                    ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->selectRaw('MIN((jenis_kf)::int) as death_ke')
            ->value('death_ke');

        if (!is_null($deathKe) && (int) $jenisKf > (int) $deathKe) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', $id)
                ->with(
                    'error',
                    "KF{$jenisKf} tidak dapat disimpan karena pada KF{$deathKe} pasien sudah tercatat meninggal/wafat."
                );
        }

        // Cek apakah sudah selesai (sistem baru atau lama)


        // Cek apakah sudah selesai (sistem baru atau lama)
        if ($pasienNifas->isKfSelesai($jenisKf)) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', $id)
                ->with('error', "KF{$jenisKf} sudah selesai dicatat!");
        }

        try {
            DB::beginTransaction();

            // Normalisasi MAP: dukung "80,00" atau "80.00"
            $mapValue = null;
            if ($request->filled('map')) {
                $rawMap = str_replace(',', '.', $request->map);
                $mapValue = (int) round((float) $rawMap);
            }

            // ========== SIMPAN KE TABEL BARU (kf_kunjungans) ==========
            $kfKunjungan = KfKunjungan::updateOrCreate(
                [
                    'pasien_nifas_id' => $id,
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

            // ========== UPDATE FOREIGN KEY DI TABEL LAMA ==========
            $pasienNifas->update([
                "kf{$jenisKf}_id" => $kfKunjungan->id,

                // ========== TETAP UPDATE KOLOM LAMA UNTUK KOMPATIBILITAS ==========
                "kf{$jenisKf}_tanggal" => Carbon::parse($request->tanggal_kunjungan),
                "kf{$jenisKf}_catatan" => $request->catatan,
            ]);

            DB::commit();

            // Tentukan pesan berdasarkan status
            $status = $pasienNifas->getKfStatus($jenisKf);
            $pesan = "KF{$jenisKf} berhasil dicatat!";

            if ($status == 'terlambat') {
                $pesan .= " (Catatan: dilakukan di luar periode normal)";
            }

            // Tambahkan info kesimpulan
            $pesan .= " Kesimpulan: " . $request->kesimpulan_pantauan;

            return redirect()
                ->route('puskesmas.pasien-nifas.show', $id)
                ->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log error untuk debugging
            Log::error('Gagal menyimpan KF (sistem baru): ' . $e->getMessage(), [
                'id' => $id,
                'jenis_kf' => $jenisKf,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menyimpan data KF. Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadKfPdf($id, $jenisKf)
    {
        try {
            // 1. Ambil data pasien nifas
            $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);

            // 2. Cek apakah KF ini sudah dicatat di tabel baru (KfKunjungan)
            $kfKunjungan = KfKunjungan::where('pasien_nifas_id', $id)
                ->where('jenis_kf', $jenisKf)
                ->first();

            if (!$kfKunjungan) {
                return back()->with('error', "KF{$jenisKf} belum dicatat untuk pasien ini");
            }

            // 3. Siapkan data untuk PDF
            $data = [
                'pasienNifas' => $pasienNifas,
                'kfKunjungan' => $kfKunjungan,
                'jenisKf' => $jenisKf,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title' => "Laporan KF{$jenisKf} - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ];

            // 4. Generate PDF
            $pdf = Pdf::loadView('puskesmas.pdf.kf-single', $data);

            // 5. Nama file PDF
            $fileName = "KF{$jenisKf}_" .
                str_replace(' ', '_', $pasienNifas->pasien->user->name ?? 'pasien') . "_" .
                now()->format('Ymd_His') . '.pdf';

            // 6. Download PDF
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error generating KF PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF untuk semua KF
     */
    public function downloadAllKfPdf($id)
    {
        try {
            // 1. Ambil data pasien nifas
            $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);

            // 2. Ambil semua KF dari tabel baru (KfKunjungan)
            $kfKunjungan = KfKunjungan::where('pasien_nifas_id', $id)
                ->orderBy('jenis_kf')
                ->get();

            if ($kfKunjungan->isEmpty()) {
                return back()->with('error', 'Belum ada KF yang dicatat untuk pasien ini');
            }

            // 3. Siapkan data untuk PDF
            $data = [
                'pasienNifas' => $pasienNifas,
                'kfKunjungan' => $kfKunjungan,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title' => "Laporan Semua KF - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ];

            // 4. Generate PDF
            $pdf = Pdf::loadView('puskesmas.pdf.kf-all', $data);

            // 5. Nama file PDF
            $fileName = "Semua_KF_" .
                str_replace(' ', '_', $pasienNifas->pasien->user->name ?? 'pasien') . "_" .
                now()->format('Ymd_His') . '.pdf';

            // 6. Download PDF
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error generating All KF PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }
}
