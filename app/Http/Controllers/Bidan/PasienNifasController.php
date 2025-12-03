<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\PasienNifasBidan;
use App\Models\Pasien;
use App\Models\User;
use App\Models\Skrining;
use App\Models\AnakPasien;
use App\Models\Kf;

/*
|--------------------------------------------------------------------------
| PASIEN NIFAS CONTROLLER
|--------------------------------------------------------------------------
| Fungsi: Mengelola data pasien nifas (pasien setelah melahirkan)
| Fitur: List pasien nifas, tambah pasien nifas baru, hitung peringatan KF
|--------------------------------------------------------------------------
*/

class PasienNifasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | METHOD: index()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan daftar pasien nifas dengan status KF & peringatan
    | Return: View 'bidan.pasien-nifas.index' dengan data paginated
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // 1. Validasi Bidan Login
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        $puskesmasId = $bidan->puskesmas_id;

        // 2. Ambil Data Pasien Nifas dengan Join
        // Join 3 tabel: pasien_nifas_bidan, pasiens, users
        $pasienNifas = DB::table('pasien_nifas_bidan')
            ->join('pasiens', 'pasien_nifas_bidan.pasien_id', '=', 'pasiens.id') // Join ke tabel pasiens
            ->join('users', 'pasiens.user_id', '=', 'users.id') // Join ke tabel users
            ->select(
                'pasien_nifas_bidan.id',                             // ID relasi nifas-bidan
                'pasien_nifas_bidan.pasien_id',                      // ID pasien
                'pasien_nifas_bidan.tanggal_mulai_nifas as tanggal', // Tanggal mulai nifas (alias: tanggal)
                'pasien_nifas_bidan.created_at',                     // Tanggal dibuat
                'pasiens.nik',                                       // NIK pasien
                'users.name as nama_pasien',                         // Nama pasien dari tabel users
                'users.phone as telp',                               // No telp dari users
                'pasiens.PKecamatan as alamat',                      // Kecamatan sebagai alamat
                'pasiens.PWilayah as kelurahan'                      // Kelurahan
            )
            ->where('pasien_nifas_bidan.bidan_id', $puskesmasId) // Filter per puskesmas
            ->orderByDesc('pasien_nifas_bidan.tanggal_mulai_nifas') // Urutkan tanggal terbaru
            ->orderByDesc('pasien_nifas_bidan.created_at')          // Urutkan created_at terbaru
            ->paginate(10); // 10 data per halaman

        // 3. Ambil Status KF (Kunjungan Nifas) Terakhir
        $idsPasien = $pasienNifas->getCollection()->pluck('pasien_id')->all();
        $kfDone = DB::table('kf')
            ->selectRaw('id_nifas, MAX(kunjungan_nifas_ke)::int as max_ke')
            ->whereIn('id_nifas', $idsPasien)
            ->groupBy('id_nifas')
            ->get()
            ->keyBy('id_nifas');

        // 4. Define Jadwal KF (Kunjungan Nifas)
        // KF1: 6–48 jam (~2 hari), KF2: 3–7 hari, KF3: 8–28 hari, KF4: 29–42 hari
        $dueDays = [1=>2, 2=>7, 3=>28, 4=>42];
        
        $today = Carbon::today(); // Tanggal hari ini

        // 5. Transform Data untuk Hitung Peringatan
        $pasienNifas->getCollection()->transform(function ($row) use ($kfDone, $dueDays, $today) {
            $maxKe = optional($kfDone->get($row->pasien_id))->max_ke ?? 0;
            $nextKe = min(4, $maxKe + 1);
            $days = $row->tanggal ? Carbon::parse($row->tanggal)->diffInDays($today) : 0;
            $due = $dueDays[$nextKe] ?? 42;
            if ($row->tanggal === null) {
                $label = 'Aman';
                $cls = 'bg-[#2EDB58] text-white';
            } elseif ($days > $due) {
                $label = 'Telat';
                $cls = 'bg-[#FF3B30] text-white';
            } elseif ($days >= max(0, $due - 1)) {
                $label = 'Mepet';
                $cls = 'bg-[#FFC400] text-[#1D1D1D]';
            } else {
                $label = 'Aman';
                $cls = 'bg-[#2EDB58] text-white';
            }
            $row->peringat_label = $label;
            $row->badge_class = $cls;
            $row->next_ke = $nextKe;
            $row->max_ke = $maxKe;
            return $row;
        });

        // 6. Hitung Total Statistik
        $totalPasienNifas = DB::table('pasien_nifas_bidan')
            ->where('bidan_id', $puskesmasId)
            ->count(); // Total pasien nifas di puskesmas ini

        $sudahKFI = 0; // TODO: Hitung yang sudah KF1 (belum diimplementasi)
        $belumKFI = $totalPasienNifas - $sudahKFI; // Belum KF1

        // 7. Kirim ke View
        return view('bidan.pasien-nifas.index', compact(
            'pasienNifas',      // Data pasien nifas (paginated)
            'totalPasienNifas', // Total pasien nifas
            'sudahKFI',         // Sudah KF1
            'belumKFI'          // Belum KF1
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: create()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan form untuk tambah pasien nifas baru
    | Return: View 'bidan.pasien-nifas.create'
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('bidan.pasien-nifas.create');
    }

    public function cekNik(Request $request)
    {
        $nik = $request->input('nik');
        if (!$nik || strlen($nik) !== 16) {
            return response()->json(['found' => false, 'message' => 'NIK tidak valid. Harus 16 digit.']);
        }
        try {
            $pasien = Pasien::where('nik', $nik)
                ->with(['user', 'skrinings' => function ($q) { $q->orderBy('created_at', 'desc')->limit(1); }])
                ->first();
            if ($pasien) {
                $status = $this->getStatusRisikoFromSkrining($pasien);
                return response()->json([
                    'found' => true,
                    'message' => 'Pasien ditemukan',
                    'pasien' => [
                        'id' => $pasien->id,
                        'nik' => $pasien->nik,
                        'nama' => $pasien->user->name ?? '',
                        'no_telepon' => $pasien->user->phone ?? '',
                        'provinsi' => $pasien->PProvinsi ?? '',
                        'kota' => $pasien->PKabupaten ?? '',
                        'kecamatan' => $pasien->PKecamatan ?? '',
                        'kelurahan' => $pasien->PWilayah ?? '',
                        'domisili' => $pasien->address ?? $this->buildDomisili($pasien),
                        'rt' => $pasien->rt ?? '',
                        'rw' => $pasien->rw ?? '',
                        'kode_pos' => $pasien->kode_pos ?? '',
                        'tempat_lahir' => $pasien->tempat_lahir ?? '',
                        'tanggal_lahir' => $pasien->tanggal_lahir ?? '',
                        'status_perkawinan' => is_null($pasien->status_perkawinan) ? '' : (int) $pasien->status_perkawinan,
                        'pekerjaan' => $pasien->pekerjaan ?? '',
                        'pendidikan' => $pasien->pendidikan ?? '',
                        'pembiayaan_kesehatan' => $pasien->pembiayaan_kesehatan ?? '',
                        'golongan_darah' => $pasien->golongan_darah ?? '',
                        'no_jkn' => $pasien->no_jkn ?? '',
                        'status_risiko' => $status['label'],
                        'status_type' => $status['type'],
                        'has_skrining' => $pasien->skrinings->count() > 0,
                    ],
                ]);
            }
            return response()->json(['found' => false, 'message' => 'Pasien dengan NIK tersebut tidak ditemukan. Silakan isi data baru.']);
        } catch (\Exception $e) {
            Log::error('Bidan Cek NIK: ' . $e->getMessage());
            return response()->json(['found' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    private function getStatusRisikoFromSkrining($pasien)
    {
        if (!$pasien) return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
        $skrining = Skrining::where('pasien_id', $pasien->id)->orderBy('created_at', 'desc')->first();
        if (!$skrining) return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
        $rt = $skrining->jumlah_resiko_tinggi ?? 0;
        $rs = $skrining->jumlah_resiko_sedang ?? 0;
        $kes = strtolower(trim($skrining->kesimpulan ?? ''));
        $pe  = strtolower(trim($skrining->status_pre_eklampsia ?? ''));
        $high = $rt > 0 || in_array($kes, ['beresiko','berisiko','risiko tinggi','tinggi']) || in_array($pe, ['beresiko','berisiko','risiko tinggi','tinggi']);
        $mid  = $rs > 0 || in_array($kes, ['waspada','menengah','sedang','risiko sedang']) || in_array($pe, ['waspada','menengah','sedang','risiko sedang']);
        if ($high) return ['label' => 'Beresiko', 'type' => 'beresiko'];
        if ($mid)  return ['label' => 'Waspada', 'type' => 'waspada'];
        return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
    }

    private function buildDomisili($pasien)
    {
        $parts = [];
        if (!empty($pasien->rt)) $parts[] = 'RT ' . $pasien->rt;
        if (!empty($pasien->rw)) $parts[] = 'RW ' . $pasien->rw;
        if (!empty($pasien->PWilayah)) $parts[] = 'Kel. ' . $pasien->PWilayah;
        if (!empty($pasien->PKecamatan)) $parts[] = 'Kec. ' . $pasien->PKecamatan;
        return implode(', ', $parts);
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: store()
    |--------------------------------------------------------------------------
    | Fungsi: Menyimpan data pasien nifas baru ke database
    | Parameter: $request (form data)
    | Return: Redirect dengan pesan sukses/error
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'nik'         => 'required|digits:16',
            'no_telepon'  => 'required|string|max:20',
            'provinsi'    => 'required|string|max:100',
            'kota'        => 'required|string|max:100',
            'kecamatan'   => 'required|string|max:100',
            'kelurahan'   => 'required|string|max:100',
            'domisili'    => 'required|string',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|date',
            'status_perkawinan' => 'nullable|in:0,1',
            'rt'                => 'nullable|string|max:10',
            'rw'                => 'nullable|string|max:10',
            'kode_pos'          => 'nullable|string|max:10',
            'pekerjaan'         => 'nullable|string|max:100',
            'pendidikan'        => 'nullable|string|max:100',
            'pembiayaan_kesehatan' => 'nullable|string|max:50',
            'golongan_darah'    => 'nullable|string|max:5',
            'no_jkn'            => 'nullable|string|max:20',
        ]);

        try {
            // 2. Mulai Database Transaction
            // Transaction: semua query sukses semua, atau rollback semua jika ada error
            DB::beginTransaction();

            // 3. Cek Apakah Pasien Sudah Terdaftar (by NIK)
            $existingPasien = Pasien::with('user') // Eager load relasi user
                                    ->where('nik', $validated['nik']) // Cari by NIK
                                    ->first(); // Ambil 1 data pertama
            
            if ($existingPasien) {
                // Jika pasien sudah ada, UPDATE data yang berubah
                
                // Update data user (no telp)
                if ($existingPasien->user) {
                    // Jika relasi user ada, update via relasi
                    $existingPasien->user->update(['phone' => $validated['no_telepon']]);
                } else {
                    // Jika relasi tidak load, update langsung by user_id
                    User::where('id', $existingPasien->user_id)->update(['phone' => $validated['no_telepon']]);
                }

                $updateData = [
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                    'rt'                => $validated['rt'] ?? $existingPasien->rt,
                    'rw'                => $validated['rw'] ?? $existingPasien->rw,
                    'kode_pos'          => $validated['kode_pos'] ?? $existingPasien->kode_pos,
                    'tempat_lahir'      => $validated['tempat_lahir'] ?? $existingPasien->tempat_lahir,
                    'tanggal_lahir'     => $validated['tanggal_lahir'] ?? $existingPasien->tanggal_lahir,
                    'status_perkawinan' => isset($validated['status_perkawinan']) ? (int) $validated['status_perkawinan'] : $existingPasien->status_perkawinan,
                    'pekerjaan'         => $validated['pekerjaan'] ?? $existingPasien->pekerjaan,
                    'pendidikan'        => $validated['pendidikan'] ?? $existingPasien->pendidikan,
                    'pembiayaan_kesehatan' => $validated['pembiayaan_kesehatan'] ?? $existingPasien->pembiayaan_kesehatan,
                    'golongan_darah'    => $validated['golongan_darah'] ?? $existingPasien->golongan_darah,
                    'no_jkn'            => $validated['no_jkn'] ?? $existingPasien->no_jkn,
                ];
                if (Schema::hasColumn('pasiens', 'address')) {
                    $updateData['address'] = $validated['domisili'];
                } else if ($existingPasien->user) {
                    $existingPasien->user->update(['address' => $validated['domisili']]);
                }
                $existingPasien->update($updateData);

                $pasien = $existingPasien; // Set variable $pasien ke existing pasien
            } else {
                // Jika pasien belum ada, BUAT data baru (User + Pasien)
                
                // 3a. Ambil Role "pasien"
                $role = DB::table('roles')->where('nama_role', 'pasien')->first();
                if (!$role) {
                    throw new \Exception('Role "pasien" tidak ditemukan'); // Error jika role tidak ada
                }

                // 3b. Generate Email Unik
                $baseEmail = $validated['nik'] . '@pasien.delisa.id'; // Email default dari NIK
                
                // Cek apakah email sudah ada
                $email = User::where('email', $baseEmail)->exists()
                    ? ($validated['nik'] . '.' . time() . '@pasien.delisa.id') // Jika ada, tambah timestamp
                    : $baseEmail; // Jika tidak ada, pakai base email

                // 3c. Buat User Baru
                $user = User::create([
                    'name'     => $validated['nama_pasien'],
                    'email'    => $email,
                    'password' => bcrypt('password'), // Password default: "password"
                    'role_id'  => $role->id,          // Set role sebagai pasien
                    'phone'    => $validated['no_telepon'],
                ]);

                $pasienData = [
                    'user_id'    => $user->id,
                    'nik'        => $validated['nik'],
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                    'rt'                => $validated['rt'] ?? null,
                    'rw'                => $validated['rw'] ?? null,
                    'kode_pos'          => $validated['kode_pos'] ?? null,
                    'tempat_lahir'      => $validated['tempat_lahir'] ?? null,
                    'tanggal_lahir'     => $validated['tanggal_lahir'] ?? null,
                    'status_perkawinan' => isset($validated['status_perkawinan']) ? (int) $validated['status_perkawinan'] : null,
                    'pekerjaan'         => $validated['pekerjaan'] ?? null,
                    'pendidikan'        => $validated['pendidikan'] ?? null,
                    'pembiayaan_kesehatan' => $validated['pembiayaan_kesehatan'] ?? null,
                    'golongan_darah'    => $validated['golongan_darah'] ?? null,
                    'no_jkn'            => $validated['no_jkn'] ?? null,
                ];
                if (Schema::hasColumn('pasiens', 'address')) {
                    $pasienData['address'] = $validated['domisili'];
                } else {
                    $user->update(['address' => $validated['domisili']]);
                }
                $pasien = Pasien::create($pasienData);
            }

            // 4. Ambil Data Bidan yang Login
            $bidan = Auth::user()->bidan;
            if (!$bidan) {
                throw new \RuntimeException('Akses Bidan tidak valid'); // Error jika bukan bidan
            }
            $bidanId = $bidan->puskesmas_id; // ID puskesmas bidan

            // 5. Cek Apakah Pasien Sudah Terdaftar di Nifas Bidan Ini
            $existingNifas = PasienNifasBidan::where('pasien_id', $pasien->id)
                ->where('bidan_id', $bidanId)
                ->first();

            if ($existingNifas) {
                // Jika sudah terdaftar, commit transaction dan redirect dengan pesan info
                DB::commit();
                return redirect()->route('bidan.pasien-nifas')
                    ->with('info', 'Pasien sudah terdaftar dalam daftar nifas.');
            }

            // 6. Buat Relasi Pasien Nifas - Bidan
            PasienNifasBidan::create([
                'bidan_id'             => $bidanId,    // ID puskesmas bidan
                'pasien_id'            => $pasien->id, // ID pasien
                'tanggal_mulai_nifas'  => now(),       // Tanggal mulai nifas = sekarang
            ]);

            // 7. Commit Transaction (Simpan Semua Perubahan)
            DB::commit();
            
            // 8. Redirect dengan Pesan Sukses
            return redirect()->route('bidan.pasien-nifas')
                ->with('success', 'Data pasien nifas berhasil ditambahkan');
                
        } catch (\Exception $e) {
            // Jika ada error, ROLLBACK semua perubahan
            DB::rollBack();
            
            // Log error ke file log
            Log::error('Bidan Store Pasien Nifas: ' . $e->getMessage());
            
            // Redirect kembali dengan input lama dan pesan error
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bidan = Auth::user()->bidan;
            abort_unless($bidan, 403);
            $bidanId = $bidan->puskesmas_id;

            $row = PasienNifasBidan::where('id', $id)
                ->where('bidan_id', $bidanId)
                ->first();

            if (!$row) {
                return back()->with('error', 'Data nifas tidak ditemukan atau bukan milik puskesmas Anda.');
            }

            $row->delete();

            return redirect()->route('bidan.pasien-nifas')
                ->with('success', 'Data pasien nifas berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Bidan Destroy Pasien Nifas: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        $bidan = Auth::user()->bidan;
        abort_unless($bidan, 403);

        $pasienNifas = PasienNifasBidan::with(['pasien.user'])->findOrFail($id);
        $status = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $pasienNifas->status_display = $status['label'];
        $pasienNifas->status_type = $status['type'];

        $anakPasien = AnakPasien::where('nifas_id', $pasienNifas->id)->get();
        $kfList = Kf::with('anak')
            ->where('id_nifas', $pasienNifas->pasien_id)
            ->orderByDesc('tanggal_kunjungan')
            ->get();

        return view('bidan.pasien-nifas.show', compact('pasienNifas', 'anakPasien', 'kfList'));
    }

    public function formKf($id, $jenisKf)
    {
        $bidan = Auth::user()->bidan;
        abort_unless($bidan, 403);
        if (!in_array((int)$jenisKf, [1,2,3,4], true)) abort(404);

        $pasienNifas = PasienNifasBidan::with(['pasien.user'])->findOrFail($id);
        $anakList = AnakPasien::where('nifas_id', $id)->get();
        $status = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $pasienNifas->status_display = $status['label'];
        $pasienNifas->status_type = $status['type'];

        $selectedAnakId = $anakList->count() === 1 ? $anakList->first()->id : null;
        $existingKf = null;
        if ($selectedAnakId) {
            $existingKf = Kf::where('id_nifas', $pasienNifas->pasien_id)
                ->where('id_anak', $selectedAnakId)
                ->where('kunjungan_nifas_ke', (int)$jenisKf)
                ->first();
        }

        return view('bidan.pasien-nifas.kf-form', compact('pasienNifas', 'jenisKf', 'anakList', 'selectedAnakId', 'existingKf'));
    }

    public function catatKf(Request $request, $id, $jenisKf)
    {
        $bidan = Auth::user()->bidan;
        abort_unless($bidan, 403);
        if (!in_array((int)$jenisKf, [1,2,3,4], true)) abort(404);

        $pasienNifas = PasienNifasBidan::findOrFail($id);

        $data = $request->validate([
            'id_anak' => 'required|exists:anak_pasien,id',
            'tanggal_kunjungan' => 'required|date',
            'sbp' => 'nullable|integer',
            'dbp' => 'nullable|integer',
            'map' => 'nullable|numeric',
            'keadaan_umum' => 'nullable|string',
            'tanda_bahaya' => 'nullable|string',
            'kesimpulan_pantauan' => 'required|in:Sehat,Dirujuk,Meninggal',
        ]);

        $mapRaw = $request->input('map', null);
        if (is_string($mapRaw)) {
            $mapRaw = str_replace(',', '.', $mapRaw);
        }
        $map = null;
        if (isset($data['sbp'], $data['dbp']) && is_numeric($data['sbp']) && is_numeric($data['dbp'])) {
            $map = round(((float)$data['sbp'] + (2 * (float)$data['dbp'])) / 3, 2);
        } elseif ($mapRaw !== null && is_numeric($mapRaw)) {
            $map = round((float)$mapRaw, 2);
        } else {
            $map = null;
        }
        $mapInt = $map !== null ? (int) round($map) : null;

        $payload = [
            'id_nifas' => $pasienNifas->pasien_id,
            'id_anak' => $data['id_anak'],
            'kunjungan_nifas_ke' => (int)$jenisKf,
            'tanggal_kunjungan' => $data['tanggal_kunjungan'],
            'sbp' => $data['sbp'] ?? null,
            'dbp' => $data['dbp'] ?? null,
            'map' => $mapInt,
            'keadaan_umum' => $data['keadaan_umum'] ?? null,
            'tanda_bahaya' => $data['tanda_bahaya'] ?? null,
            'kesimpulan_pantauan' => $data['kesimpulan_pantauan'],
        ];

        $existing = Kf::where('id_nifas', $pasienNifas->pasien_id)
            ->where('id_anak', $data['id_anak'])
            ->where('kunjungan_nifas_ke', (int)$jenisKf)
            ->first();

        if ($existing) {
            $existing->update($payload);
        } else {
            Kf::create($payload);
        }

        return redirect()->route('bidan.pasien-nifas.detail', $id)->with('success', 'KF'.$jenisKf.' berhasil disimpan');
    }
}

/*
|--------------------------------------------------------------------------
| PENJELASAN FUNGSI-FUNGSI:
|--------------------------------------------------------------------------
|
| 1. DB::table('nama_tabel')
|    - Query builder Laravel
|    - Akses tabel database tanpa Model
|    - Return: Builder object
|
| 2. join('tabel', 'kolom1', '=', 'kolom2')
|    - Join tabel
|    - Gabungkan data dari beberapa tabel
|    - Contoh: join('users', 'pasiens.user_id', '=', 'users.id')
|
| 3. selectRaw('query')
|    - Jalankan raw SQL di SELECT
|    - Untuk fungsi agregat kompleks (MAX, MIN, SUM, dll)
|    - Contoh: selectRaw('MAX(kolom)::int as alias')
|
| 4. Carbon::parse($tanggal)
|    - Parse string tanggal jadi Carbon object
|    - Bisa manipulasi tanggal (tambah, kurang, diff, format)
|    - Contoh: Carbon::parse('2024-01-01')->diffInDays(Carbon::now())
|
| 5. diffInDays($tanggal_lain)
|    - Hitung selisih hari antara 2 tanggal
|    - Return: integer (jumlah hari)
|    - Contoh: $tgl1->diffInDays($tgl2)
|
| 6. min($a, $b)
|    - Ambil nilai minimum dari 2 nilai
|    - Return: nilai terkecil
|    - Contoh: min(4, 3+1) -> 4
|
| 7. max($a, $b)
|    - Ambil nilai maksimum dari 2 nilai
|    - Return: nilai terbesar
|    - Contoh: max(0, 3-1) -> 2
|
| 8. DB::beginTransaction()
|    - Mulai database transaction
|    - Semua query setelah ini tidak langsung disimpan
|    - Harus commit() untuk simpan, atau rollBack() untuk batal
|
| 9. DB::commit()
|    - Simpan semua perubahan dalam transaction
|    - Setelah commit, data benar-benar tersimpan di database
|
| 10. DB::rollBack()
|     - Batalkan semua perubahan dalam transaction
|     - Database kembali ke state sebelum beginTransaction()
|     - Digunakan jika ada error
|
| 11. bcrypt($password)
|     - Hash password dengan algoritma bcrypt
|     - Satu arah (tidak bisa di-decrypt)
|     - Laravel verify password otomatis saat login
|
| 12. now()
|     - Helper Laravel untuk tanggal/waktu sekarang
|     - Return: Carbon instance
|     - Sama dengan: Carbon::now()
|
| 13. back()
|     - Redirect ke halaman sebelumnya
|     - Return: RedirectResponse
|     - Biasa dipakai setelah submit form
|
| 14. withInput()
|     - Simpan input form ke session
|     - Bisa diakses dengan old('nama_field')
|     - Berguna untuk repopulate form setelah error
|
| 15. Log::error($message)
|     - Tulis error ke file log
|     - File: storage/logs/laravel.log
|     - Untuk debugging & monitoring
|
| 16. throw new \Exception($message)
|     - Lempar exception (error)
|     - Stop eksekusi code
|     - Bisa di-catch dengan try-catch
|
|--------------------------------------------------------------------------
*/