<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Models\PasienNifasRs;
use App\Models\KfKunjungan;
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
    public function index(Request $request)
    {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        $userId = Auth::id();

        // Dapatkan data puskesmas user
        $puskesmas = DB::table('puskesmas')
            ->select('id', 'kecamatan', 'nama_puskesmas')
            ->where('user_id', $userId)
            ->first();

        if (!$puskesmas) {
            return view('puskesmas.pasien-nifas.index', [
                'dataRs' => collect(),
                'totalPasienNifas' => 0,
                'sudahKFI' => 0,
                'belumKFI' => 0,
                'kecamatanPuskesmas' => null,
                'namaPuskesmas' => null,
                'type' => 'rs',
                'search' => trim($request->get('search')),
                'tanggalMulai' => $request->get('tanggal_mulai'),
                'tanggalSelesai' => $request->get('tanggal_selesai'),
            ]);
        }

        $kecamatanPuskesmas = $puskesmas->kecamatan;
        $namaPuskesmas = $puskesmas->nama_puskesmas;
        $puskesmasId = (int) $puskesmas->id;

        // Karena BIDAN tidak dipakai lagi, type dibatasi hanya: rs
        $type = $request->get('type', 'rs');
        if (!in_array($type, ['rs', 'all'], true)) {
            $type = 'rs';
        }

        $search = trim((string) $request->get('search'));
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSelesai = $request->get('tanggal_selesai');

        // ======================
        // DATA DARI RS (yang ditugaskan ke puskesmas ini)
        // ======================
        $queryRs = PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
            ->where('puskesmas_id', $puskesmasId);

        // 🔍 Search nama pasien
        if ($search) {
            $queryRs->whereHas('pasien.user', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // 📅 Filter tanggal mulai nifas
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

        // ✅ Set tanggal_melahirkan dari tanggal lahir anak (tanpa ubah DB)
        $dataRs->getCollection()->transform(function ($item) {
            $tglMelahirkan = $this->deriveTanggalMelahirkanFromAnak($item);

            // timpa property runtime supaya view tetap pakai $pasienNifas->tanggal_melahirkan
            $item->tanggal_melahirkan = $tglMelahirkan ? $tglMelahirkan->toDateString() : null;

            return $item;
        });


        // Hitung statistik (hanya RS)
        $totalPasienNifas = $dataRs->total();

        // Sudah KF1 (hanya dari item halaman ini)
        $sudahKFI = collect($dataRs->items())
            ->filter(fn($item) => $item->isKfSelesai(1))
            ->count();

        $belumKFI = max(0, $totalPasienNifas - $sudahKFI);

        return view('puskesmas.pasien-nifas.index', [
            'dataRs' => $dataRs,
            'totalPasienNifas' => $totalPasienNifas,
            'sudahKFI' => $sudahKFI,
            'belumKFI' => $belumKFI,
            'kecamatanPuskesmas' => $kecamatanPuskesmas,
            'namaPuskesmas' => $namaPuskesmas,
            'type' => $type,
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
=======
        $user = auth()->user();

        // Ambil puskesmas berdasarkan user login
        $puskesmas = \App\Models\Puskesmas::where('user_id', $user->id)->firstOrFail();

        // Query untuk data RS dengan pagination dan search
        $queryRs = PasienNifasRs::with(['pasien.user', 'rs'])
            ->where('puskesmas_id', $puskesmas->id);

        // Query untuk data Bidan - akses puskesmas via relasi bidan
        $queryBidan = PasienNifasBidan::with(['pasien.user', 'bidan'])
            ->whereHas('bidan', function($q) use ($puskesmas) {
                $q->where('puskesmas_id', $puskesmas->id);
            });

        // Jika ada pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            
            $queryRs->whereHas('pasien.user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });

            $queryBidan->whereHas('pasien.user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Ambil data dengan pagination untuk RS
        $dataRs = $queryRs->orderBy('created_at', 'desc')->paginate(10);
        
        // Ambil semua data Bidan (tanpa pagination)
        $dataBidan = $queryBidan->orderBy('created_at', 'desc')->get();

        // Hitung statistik
        $totalPasienNifas = PasienNifasRs::where('puskesmas_id', $puskesmas->id)->count() +
                           PasienNifasBidan::whereHas('bidan', function($q) use ($puskesmas) {
                               $q->where('puskesmas_id', $puskesmas->id);
                           })->count();

        $sudahKFI = DB::table('kf_kunjungans as kk')
            ->join('pasien_nifas_rs as pnr', 'pnr.id', '=', 'kk.pasien_nifas_id')
            ->where('pnr.puskesmas_id', $puskesmas->id)
            ->where('kk.jenis_kf', 1)
            ->count();

        $belumKFI = $totalPasienNifas - $sudahKFI;

=======
        $user = auth()->user();

        // Ambil puskesmas berdasarkan user login
        $puskesmas = \App\Models\Puskesmas::where('user_id', $user->id)->firstOrFail();

        // Query untuk data RS dengan pagination dan search
        $queryRs = PasienNifasRs::with(['pasien.user', 'rs'])
            ->where('puskesmas_id', $puskesmas->id);

        // Query untuk data Bidan - akses puskesmas via relasi bidan
        $queryBidan = PasienNifasBidan::with(['pasien.user', 'bidan'])
            ->whereHas('bidan', function($q) use ($puskesmas) {
                $q->where('puskesmas_id', $puskesmas->id);
            });

        // Jika ada pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            
            $queryRs->whereHas('pasien.user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });

            $queryBidan->whereHas('pasien.user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Ambil data dengan pagination untuk RS
        $dataRs = $queryRs->orderBy('created_at', 'desc')->paginate(10);
        
        // Ambil semua data Bidan (tanpa pagination)
        $dataBidan = $queryBidan->orderBy('created_at', 'desc')->get();

        // Hitung statistik
        $totalPasienNifas = PasienNifasRs::where('puskesmas_id', $puskesmas->id)->count() +
                           PasienNifasBidan::whereHas('bidan', function($q) use ($puskesmas) {
                               $q->where('puskesmas_id', $puskesmas->id);
                           })->count();

        $sudahKFI = DB::table('kf_kunjungans as kk')
            ->join('pasien_nifas_rs as pnr', 'pnr.id', '=', 'kk.pasien_nifas_id')
            ->where('pnr.puskesmas_id', $puskesmas->id)
            ->where('kk.jenis_kf', 1)
            ->count();

        $belumKFI = $totalPasienNifas - $sudahKFI;

>>>>>>> Stashed changes
        return view('puskesmas.pasien-nifas.index', compact(
            'dataRs',
            'dataBidan',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        ));
    }

    /**
     * Tampilkan detail pasien nifas
     */
    public function show($type, $id)
    {
        // Tentukan model berdasarkan type
        if ($type === 'rs') {
            $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
                ->findOrFail($id);
        } elseif ($type === 'bidan') {
            $pasienNifas = PasienNifasBidan::with(['pasien.user', 'bidan', 'anakPasien'])
                ->findOrFail($id);
        } else {
            abort(404, 'Tipe data tidak valid');
        }

        // Cari KF pertama yang berkesimpulan Meninggal/Wafat
        $deathKe = KfKunjungan::query()
            ->where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                    ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->min('jenis_kf');

        return view('puskesmas.pasien-nifas.show', compact('pasienNifas', 'deathKe', 'type'));
    }

    /**
     * Form untuk mencatat KF
     */
    public function formCatatKf($type, $id, $jenisKf)
    {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
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
=======
        // Tentukan model berdasarkan type
        if ($type === 'rs') {
            $pasienNifas = PasienNifasRs::with(['pasien.user'])->findOrFail($id);
        } elseif ($type === 'bidan') {
            $pasienNifas = PasienNifasBidan::with(['pasien.user'])->findOrFail($id);
        } else {
            abort(404, 'Tipe data tidak valid');
        }

        // Validasi input jenis KF
>>>>>>> Stashed changes
=======
        // Tentukan model berdasarkan type
        if ($type === 'rs') {
            $pasienNifas = PasienNifasRs::with(['pasien.user'])->findOrFail($id);
        } elseif ($type === 'bidan') {
            $pasienNifas = PasienNifasBidan::with(['pasien.user'])->findOrFail($id);
        } else {
            abort(404, 'Tipe data tidak valid');
        }

        // Validasi input jenis KF
>>>>>>> Stashed changes
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // Cek apakah sudah ada KF dengan kesimpulan Meninggal/Wafat
        $deathKe = KfKunjungan::where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->where('kesimpulan_pantauan', 'Meninggal')
                    ->orWhere('kesimpulan_pantauan', 'Wafat');
=======
        // STOP: jika sudah ada KF dengan kesimpulan Meninggal/Wafat
        $deathKe = KfKunjungan::query()
            ->where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                    ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
>>>>>>> Stashed changes
            })
            ->selectRaw('MIN((jenis_kf)::int) as death_ke')
            ->value('death_ke');

=======
        // STOP: jika sudah ada KF dengan kesimpulan Meninggal/Wafat
        $deathKe = KfKunjungan::query()
            ->where('pasien_nifas_id', $id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                    ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->selectRaw('MIN((jenis_kf)::int) as death_ke')
            ->value('death_ke');
>>>>>>> Stashed changes

        if (!is_null($deathKe) && (int) $jenisKf > (int) $deathKe) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with(
                    'error',
                    "KF{$jenisKf} tidak dapat dicatat karena pada KF{$deathKe} pasien sudah tercatat meninggal/wafat."
                );
        }

        // Cek apakah sudah selesai
        if ($pasienNifas->isKfSelesai($jenisKf)) {
            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('error', "KF{$jenisKf} sudah selesai dicatat!");
        }

        // Cek status
        $status = $pasienNifas->getKfStatus($jenisKf);

        if ($status == 'terlambat') {
            session()->flash('warning', "Periode normal KF{$jenisKf} sudah lewat. Anda tetap dapat mencatatnya sebagai kunjungan terlambat.");
        }

        if ($status == 'belum_mulai') {
            $mulai = $pasienNifas->getKfMulai($jenisKf);
            $pesan = $mulai
                ? "Belum waktunya untuk KF{$jenisKf}. Dapat dilakukan mulai " . $mulai->format('d/m/Y H:i')
                : "Belum dapat melakukan KF{$jenisKf}";

            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('error', $pesan);
        }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        $pasienNifas = $data;
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        return view('puskesmas.pasien-nifas.form-kf', compact('pasienNifas', 'jenisKf', 'type'));
    }

    /**
     * Proses pencatatan KF
     */
    public function catatKf(Request $request, $type, $id, $jenisKf)
    {
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
            'tanggal_kunjungan.required' => 'Tanggal kunjungan harus diisi',
            'tanggal_kunjungan.before_or_equal' => 'Tanggal kunjungan tidak boleh lebih dari hari ini',
            'kesimpulan_pantauan.required' => 'Kesimpulan pantauan harus dipilih',
            'kesimpulan_pantauan.in' => 'Pilihan kesimpulan tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
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
=======
=======
>>>>>>> Stashed changes
        // Tentukan model berdasarkan type
        if ($type === 'rs') {
            $pasienNifas = PasienNifasRs::findOrFail($id);
        } elseif ($type === 'bidan') {
            $pasienNifas = PasienNifasBidan::findOrFail($id);
        } else {
            abort(404, 'Tipe data tidak valid');
        }
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

        // Validasi jenis KF
        if (!in_array($jenisKf, [1, 2, 3, 4])) {
            abort(404, 'Jenis KF tidak valid');
        }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
        // Cek death record
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
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with(
                    'error',
                    "KF{$jenisKf} tidak dapat disimpan karena pada KF{$deathKe} pasien sudah tercatat meninggal/wafat."
                );
        }

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        // Cek apakah sudah selesai
        if ($pasienNifas->isKfSelesai($jenisKf)) {
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

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
            // Simpan ke tabel KfKunjungan
>>>>>>> Stashed changes
=======
            // Simpan ke tabel KfKunjungan
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
<<<<<<< Updated upstream

            // Update foreign key di tabel nifas
            $data->update([
=======
            // Update foreign key di tabel lama
            $pasienNifas->update([
>>>>>>> Stashed changes
=======
            // Update foreign key di tabel lama
            $pasienNifas->update([
>>>>>>> Stashed changes
                "kf{$jenisKf}_id" => $kfKunjungan->id,
                "kf{$jenisKf}_tanggal" => Carbon::parse($request->tanggal_kunjungan),
                "kf{$jenisKf}_catatan" => $request->catatan,
            ]);

            DB::commit();

<<<<<<< Updated upstream
<<<<<<< Updated upstream
            // Pesan sukses
            $status = $data->getKfStatus($jenisKf);
            $pesan = "KF{$jenisKf} berhasil dicatat!";

            if ($status == 'terlambat') {
=======
            // Refresh untuk mendapatkan status terbaru setelah update
            $pasienNifas->refresh();
            
            $pesan = "KF{$jenisKf} berhasil dicatat!";

            // Cek apakah pencatatan terlambat berdasarkan tanggal kunjungan vs periode
            $selesai = $pasienNifas->getKfSelesai($jenisKf);
            if ($selesai && Carbon::parse($request->tanggal_kunjungan)->gt($selesai)) {
>>>>>>> Stashed changes
=======
            // Refresh untuk mendapatkan status terbaru setelah update
            $pasienNifas->refresh();
            
            $pesan = "KF{$jenisKf} berhasil dicatat!";

            // Cek apakah pencatatan terlambat berdasarkan tanggal kunjungan vs periode
            $selesai = $pasienNifas->getKfSelesai($jenisKf);
            if ($selesai && Carbon::parse($request->tanggal_kunjungan)->gt($selesai)) {
>>>>>>> Stashed changes
                $pesan .= " (Catatan: dilakukan di luar periode normal)";
            }

            $pesan .= " Kesimpulan: " . $request->kesimpulan_pantauan;

            return redirect()
                ->route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $id])
                ->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            Log::error('Gagal menyimpan KF: ' . $e->getMessage());
=======
=======
>>>>>>> Stashed changes

            Log::error('Gagal menyimpan KF: ' . $e->getMessage(), [
                'id' => $id,
                'type' => $type,
                'jenis_kf' => $jenisKf,
                'trace' => $e->getTraceAsString(),
            ]);
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

            return redirect()->back()
                ->with('error', 'Gagal menyimpan data KF. Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadKfPdf($type, $id, $jenisKf)
    {
        try {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
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

=======
=======
>>>>>>> Stashed changes
            // Tentukan model berdasarkan type
            if ($type === 'rs') {
                $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);
            } elseif ($type === 'bidan') {
                $pasienNifas = PasienNifasBidan::with(['pasien.user', 'bidan'])->findOrFail($id);
            } else {
                abort(404, 'Tipe data tidak valid');
            }

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
            $kfKunjungan = KfKunjungan::where('pasien_nifas_id', $id)
                ->where('jenis_kf', $jenisKf)
                ->first();


            if (!$kfKunjungan) {
                return back()->with('error', "KF{$jenisKf} belum dicatat untuk pasien ini");
            }

            $data = [
                'pasienNifas' => $pasienNifas,
                'kfKunjungan' => $kfKunjungan,
                'jenisKf' => $jenisKf,
                'type' => $type,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title' => "Laporan KF{$jenisKf} - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ];

<<<<<<< Updated upstream
<<<<<<< Updated upstream
            // Generate PDF
            $pdf = Pdf::loadView('puskesmas.pdf.kf-all', [
                'pasienNifas'   => $pasienNifas,
                'kfKunjungan'   => collect([$kfKunjungan]), // 👈 jadikan collection
                'type'          => $type,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title'         => "Laporan KF{$jenisKf} - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ]);
=======
=======
>>>>>>> Stashed changes
            $pdf = Pdf::loadView('puskesmas.pdf.kf-single', $data);
>>>>>>> Stashed changes

            $fileName = "KF{$jenisKf}_" .
                str_replace(' ', '_', $pasienNifas->pasien->user->name ?? 'pasien') . "_" .
                now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error generating KF PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function downloadAllKfPdf($type, $id)
    {
        try {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
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

=======
=======
>>>>>>> Stashed changes
            // Tentukan model berdasarkan type
            if ($type === 'rs') {
                $pasienNifas = PasienNifasRs::with(['pasien.user', 'rs'])->findOrFail($id);
            } elseif ($type === 'bidan') {
                $pasienNifas = PasienNifasBidan::with(['pasien.user', 'bidan'])->findOrFail($id);
            } else {
                abort(404, 'Tipe data tidak valid');
            }

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
            $kfKunjungan = KfKunjungan::where('pasien_nifas_id', $id)
                ->orderBy('jenis_kf')
                ->get();


            if ($kfKunjungan->isEmpty()) {
                return back()->with('error', 'Belum ada KF yang dicatat untuk pasien ini');
            }

            $data = [
                'pasienNifas' => $pasienNifas,
                'kfKunjungan' => $kfKunjungan,
                'type' => $type,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'title' => "Laporan Semua KF - " . ($pasienNifas->pasien->user->name ?? 'N/A'),
            ];

            $pdf = Pdf::loadView('puskesmas.pdf.kf-all', $data);

            $fileName = "Semua_KF_" .
                str_replace(' ', '_', $pasienNifas->pasien->user->name ?? 'pasien') . "_" .
                now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Error generating All KF PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }
<<<<<<< Updated upstream
<<<<<<< Updated upstream

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
=======
}
>>>>>>> Stashed changes
=======
}
>>>>>>> Stashed changes
