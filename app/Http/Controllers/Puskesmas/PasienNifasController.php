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
                'type' => 'all',
                'search' => trim((string) $request->get('search')),
                'tanggalMulai' => $request->get('tanggal_mulai'),
                'tanggalSelesai' => $request->get('tanggal_selesai'),
            ]);
        }

        $kecamatanPuskesmas = $puskesmas->kecamatan;
        $namaPuskesmas = $puskesmas->nama_puskesmas;
        $puskesmasId = (int) $puskesmas->id;

        $search = trim((string) $request->get('search'));
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSelesai = $request->get('tanggal_selesai');

        // ======================
        // DATA DARI RS (yang ditugaskan ke puskesmas ini)
        // ======================
        $queryRs = PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
            ->where('puskesmas_id', $puskesmasId);

        if ($search) {
            $queryRs->whereHas('pasien.user', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }
        if ($tanggalMulai) {
            $queryRs->whereDate('tanggal_mulai_nifas', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            $queryRs->whereDate('tanggal_mulai_nifas', '<=', $tanggalSelesai);
        }

        $dataRs = $queryRs
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Set tanggal_melahirkan dari tanggal lahir anak (tanpa ubah DB)
        $dataRs->getCollection()->transform(function ($item) {
            $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($item);
            $item->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;
            return $item;
        });

        // ======================
        // DATA DARI BIDAN (berdasarkan bidan pada puskesmas ini)
        // ======================
        $queryBidan = PasienNifasBidan::with(['pasien.user', 'bidan'])
            ->whereHas('bidan', function ($q) use ($puskesmasId) {
                $q->where('puskesmas_id', $puskesmasId);
            });

        if ($search) {
            $queryBidan->whereHas('pasien.user', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }
        if ($tanggalMulai) {
            $queryBidan->whereDate('tanggal_mulai_nifas', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            $queryBidan->whereDate('tanggal_mulai_nifas', '<=', $tanggalSelesai);
        }

        $dataBidan = $queryBidan->orderBy('created_at', 'desc')->get();

        // ======================
        // STATISTIK
        // ======================
        $totalPasienNifas = $dataRs->total() + PasienNifasBidan::whereHas('bidan', function ($q) use ($puskesmasId) {
            $q->where('puskesmas_id', $puskesmasId);
        })->count();

        $sudahKFI = DB::table('kf_kunjungans as kk')
            ->join('pasien_nifas_rs as pnr', 'pnr.id', '=', 'kk.pasien_nifas_id')
            ->where('pnr.puskesmas_id', $puskesmasId)
            ->where('kk.jenis_kf', 1)
            ->count();

        $belumKFI = max(0, $totalPasienNifas - $sudahKFI);

        return view('puskesmas.pasien-nifas.index', [
            'dataRs' => $dataRs,
            'dataBidan' => $dataBidan,
            'totalPasienNifas' => $totalPasienNifas,
            'sudahKFI' => $sudahKFI,
            'belumKFI' => $belumKFI,
            'kecamatanPuskesmas' => $kecamatanPuskesmas,
            'namaPuskesmas' => $namaPuskesmas,
            'type' => 'all',
            'search' => $search,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
        ]);
    }

    /**
     * Ambil tanggal melahirkan dari data anak (anak_pasien.tanggal_lahir).
     * Jika lebih dari satu anak, ambil yang paling awal (min).
     */
    private function deriveTanggalMelahirkanFromAnak($pasienNifas): ?Carbon
    {
        if (!$pasienNifas) return null;

        // pastikan relasi anakPasien sudah diload
        if (!isset($pasienNifas->anakPasien)) {
            return null;
        }

        $tgl = $pasienNifas->anakPasien
            ->pluck('tanggal_lahir')
            ->filter()
            ->min();

        return $tgl ? Carbon::parse($tgl) : null;
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

        // Batasi hanya RS
        abort_unless($type === 'rs', 404, 'Tipe data nifas tidak valid');

        $data = PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
            ->findOrFail($id);

        // ✅ Tanggal melahirkan diambil dari tanggal lahir anak
        $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($data);
        $data->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;


        // Validasi akses: cek apakah pasien dari kecamatan yang sama
        $kecamatanPasien = optional($data->pasien)->PKecamatan;
        // Validasi akses: WAJIB puskesmas_id sama (ini inti logika terbaru)
        $allowed = ((int) $data->puskesmas_id === (int) $puskesmas->id);
        abort_unless($allowed, 403, 'Anda tidak memiliki akses ke data pasien ini.');


        // Ambil semua data KF untuk pasien ini
        $kfKunjungans = KfKunjungan::where('pasien_nifas_id', $id)
            ->orderBy('jenis_kf')
            ->get();


        // Cek KF meninggal
        $deathKe = $kfKunjungans
            ->filter(function ($kf) {
                return $kf->is_meninggal;
            })
            ->min('jenis_kf');

        return view('puskesmas.pasien-nifas.show', compact(
            'data',
            'deathKe',
            'kfKunjungans',
            'type'
        ));
    }

    /**
     * Hapus data pasien nifas (universal untuk RS dan Bidan) oleh Puskesmas
     * - Validasi akses wajib: sesuai puskesmas yang login
     * - RS: wajib pasien_nifas_rs.puskesmas_id == puskesmas.id
     * - Bidan: wajib bidan.puskesmas_id == puskesmas.id
     */
    public function destroy($type, $id)
    {
        $userId = Auth::id();

        $puskesmas = DB::table('puskesmas')
            ->select('id')
            ->where('user_id', $userId)
            ->first();

        abort_unless($puskesmas, 404, 'Puskesmas tidak ditemukan');

        try {
            DB::beginTransaction();

            if ($type === 'rs') {
                $data = PasienNifasRs::with(['pasien.user'])
                    ->findOrFail($id);

                // Validasi akses: wajib milik puskesmas ini
                abort_unless(((int) $data->puskesmas_id === (int) $puskesmas->id), 403, 'Anda tidak memiliki akses menghapus data ini.');

                // Hapus kunjungan KF yang terkait (umumnya memang nempel ke pasien_nifas_rs)
                KfKunjungan::where('pasien_nifas_id', $data->id)->delete();

                // Hapus data nifas RS
                $data->delete();

                DB::commit();

                return redirect()
                    ->route('puskesmas.pasien-nifas.index')
                    ->with('success', 'Data pasien nifas (RS) berhasil dihapus.');
            }

            if ($type === 'bidan') {
                $data = PasienNifasBidan::with(['pasien.user', 'bidan'])
                    ->findOrFail($id);

                // Validasi akses: bidan harus berada pada puskesmas ini
                $allowed = (int) optional($data->bidan)->puskesmas_id === (int) $puskesmas->id;
                abort_unless($allowed, 403, 'Anda tidak memiliki akses menghapus data ini.');

                // NOTE:
                // Tidak menghapus kf_kunjungans di sini untuk menghindari salah-hapus,
                // karena struktur FK KF untuk bidan bisa berbeda di proyekmu.
                $data->delete();

                DB::commit();

                return redirect()
                    ->route('puskesmas.pasien-nifas.index')
                    ->with('success', 'Data pasien nifas (Bidan) berhasil dihapus.');
            }

            // Kalau type bukan rs/bidan
            abort(404, 'Tipe data nifas tidak valid');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus pasien nifas puskesmas: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
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

        // Batasi hanya RS
        abort_unless($type === 'rs', 404, 'Tipe data nifas tidak valid');

        $data = PasienNifasRs::with(['pasien.user', 'anakPasien'])->findOrFail($id);

        // ✅ Tanggal melahirkan diambil dari tanggal lahir anak
        $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($data);
        $data->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;

        // Validasi akses
        $kecamatanPasien = optional($data->pasien)->PKecamatan;
        $allowed = ((int) $data->puskesmas_id === (int) $puskesmas->id);
        abort_unless($allowed, 403, 'Anda tidak memiliki akses ke data pasien ini.');


        // Validasi jenis KF
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }

        // Cek apakah sudah ada KF dengan kesimpulan Meninggal/Wafat
        $deathKe = KfKunjungan::where('pasien_nifas_id', $id)
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

        $pasienNifas = $data;
        return view('puskesmas.pasien-nifas.form-kf', compact('pasienNifas', 'jenisKf', 'type'));
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

        // Batasi hanya RS
        abort_unless($type === 'rs', 404, 'Tipe data nifas tidak valid');

        $data = PasienNifasRs::with('anakPasien')->findOrFail($id);

        // ✅ Tanggal melahirkan diambil dari tanggal lahir anak
        $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($data);
        $data->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;
        abort_unless(((int) $data->puskesmas_id === (int) $puskesmas->id), 403, 'Anda tidak memiliki akses ke data pasien ini.');

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

            // Batasi hanya RS
            abort_unless($type === 'rs', 404, 'Tipe data nifas tidak valid');

            $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);
            $pasienNifas->loadMissing('anakPasien');
            $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($pasienNifas);
            $pasienNifas->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;


            // Validasi akses
            abort_unless(((int) $pasienNifas->puskesmas_id === (int) $puskesmas->id), 403, 'Anda tidak memiliki akses ke data pasien ini.');

            $kfKunjungan = KfKunjungan::where('pasien_nifas_id', $id)
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
            $pdf = Pdf::loadView('puskesmas.pdf.kf-all', [
                'pasienNifas'   => $pasienNifas,
                'kfKunjungan'   => collect([$kfKunjungan]), // 👈 jadikan collection
                'type'          => $type,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title'         => "Laporan KF{$jenisKf} - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ]);

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

            // Batasi hanya RS
            abort_unless($type === 'rs', 404, 'Tipe data nifas tidak valid');

            $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);
            $pasienNifas->loadMissing('anakPasien');
            $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($pasienNifas);
            $pasienNifas->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;


            // Validasi akses
            abort_unless(((int) $pasienNifas->puskesmas_id === (int) $puskesmas->id), 403, 'Anda tidak memiliki akses ke data pasien ini.');

            $kfKunjungan = KfKunjungan::where('pasien_nifas_id', $id)
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
