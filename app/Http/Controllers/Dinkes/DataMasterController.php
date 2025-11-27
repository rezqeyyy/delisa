<?php

/**
 * Controller Data Master (Dinkes):
 * - Mengelola data master akun RS, Puskesmas, dan Bidan.
 * - Menyediakan helper roleId, daftar kecamatan/kelurahan Depok,
 *   serta operasi CRUD (list, create, edit, update, delete).
 */

namespace App\Http\Controllers\Dinkes;

// Mengimpor base controller Laravel.
use App\Http\Controllers\Controller;

// Mengimpor model yang diperlukan.
use App\Models\User;
use App\Models\Role;
use App\Models\Puskesmas;
use App\Models\RumahSakit;
use App\Models\Bidan;


// Request untuk mengambil data dari HTTP (query string, form, dsb.).
use Illuminate\Http\Request;

// DB facade untuk query builder, transaksi, dan raw query.
use Illuminate\Support\Facades\DB;

// Hash facade untuk hashing password sebelum disimpan ke DB.
use Illuminate\Support\Facades\Hash;

// Str helper untuk utility string (tidak terlalu dipakai di file ini, tapi sudah siap).
use Illuminate\Support\Str;

class DataMasterController extends Controller
{
    /**
     * Helper untuk mendapatkan role_id berdasarkan nama role.
     *
     * - Menerima nama role (mis: 'rs', 'rumah_sakit', 'bidan', dsb).
     * - Menggunakan alias agar 'rs' dan 'rumah_sakit' dianggap sama.
     * - Mencari di tabel roles berdasarkan kolom nama_role (case-insensitive).
     * - Mengembalikan id role sebagai integer.
     */
    private function roleId(string $name): int
    {
        // Alias nama role yang dianggap sama
        // Contoh: 'rs' dan 'rumah_sakit' mengacu ke role yang sama.
        $aliases = [
            'rs'          => ['rs', 'rumah_sakit'],
            'rumah_sakit' => ['rs', 'rumah_sakit'],
            // role lain cukup pakai nama aslinya
            'bidan'      => ['bidan'],
            'puskesmas'  => ['puskesmas'],
            'dinkes'     => ['dinkes'],
            'pasien'     => ['pasien'],
        ];

        // Normalisasi nama role ke lowercase
        $key = strtolower($name);

        // Jika ada di alias, pakai daftar alias; kalau tidak, pakai dirinya sendiri.
        $candidates = $aliases[$key] ?? [$key]; // kalau tidak ada di alias, pakai nama sendiri

        // Query ke tabel roles untuk mencari id berdasarkan nama_role (LOWER(nama_role) = ?)
        return (int) Role::query()
            ->where(function ($q) use ($candidates) {
                // Loop semua kandidat nama role (alias)
                foreach ($candidates as $i => $n) {
                    if ($i === 0) {
                        // Kondisi pertama pakai where
                        $q->whereRaw('LOWER(nama_role) = ?', [$n]);
                    } else {
                        // Sisanya pakai orWhere
                        $q->orWhereRaw('LOWER(nama_role) = ?', [$n]);
                    }
                }
            })
            // Ambil kolom id pertama yang cocok
            ->value('id');
    }

    /**
     * Mengembalikan list kecamatan Kota Depok (dipakai untuk dropdown Puskesmas/RS).
     *
     * Key array = nama kecamatan yang disimpan di DB (misalnya di kolom nama_puskesmas / kecamatan).
     * Value array = label yang ditampilkan di <option> (lebih ramah user).
     */
    private function depokKecamatanOptions(): array
    {
        return [
            'Beji'         => 'Kecamatan Beji',
            'Bojongsari'   => 'Kecamatan Bojongsari',
            'Cilodong'     => 'Kecamatan Cilodong',
            'Cimanggis'    => 'Kecamatan Cimanggis',
            'Cinere'       => 'Kecamatan Cinere',
            'Cipayung'     => 'Kecamatan Cipayung',
            'Limo'         => 'Kecamatan Limo',
            'Pancoran Mas' => 'Kecamatan Pancoran Mas',
            'Sawangan'     => 'Kecamatan Sawangan',
            'Sukmajaya'    => 'Kecamatan Sukmajaya',
            'Tapos'        => 'Kecamatan Tapos',
        ];
    }

    /**
     * Mengembalikan mapping kecamatan -> daftar kelurahan di Kota Depok.
     *
     * - Key level pertama = nama kecamatan.
     * - Value = array nama kelurahan yang berada di kecamatan tersebut.
     *
     * Dipakai sebagai master kelurahan untuk RS.
     */
    private function depokKelurahanByKecamatan(): array
    {
        return [
            'Beji' => [
                'Beji',
                'Beji Timur',
                'Kemiri Muka',
                'Kukusan',
                'Pondok Cina',
                'Tanah Baru',
            ],
            'Bojongsari' => [
                'Bojongsari',
                'Bojongsari Lama',
                'Curug',
                'Duren Mekar',
                'Duren Seribu',
                'Pondok Petir',
                'Serua',
            ],
            'Cilodong' => [
                'Cilodong',
                'Jatimulya',
                'Kalibaru',
                'Kalimulya',
                'Sukamaju',
                'Sukamaju Baru',
            ],
            'Cimanggis' => [
                'Cisalak',
                'Cisalak Pasar',
                'Curug',
                'Harjamukti',
                'Mekarsari',
                'Pasir Gunung Selatan',
                'Tugu',
            ],
            'Cinere' => [
                'Cinere',
                'Gandul',
                'Pangkalan Jati',
                'Pangkalan Jati Baru',
            ],
            'Cipayung' => [
                'Cipayung',
                'Cipayung Jaya',
                'Cilangkap',
                'Pondok Jaya',
                'Ratu Jaya',
            ],
            'Limo' => [
                'Grogol',
                'Krukut',
                'Limo',
                'Meruyung',
            ],
            'Pancoran Mas' => [
                'Depok',
                'Depok Jaya',
                'Depok Baru',
                'Mampang',
                'Pancoran Mas',
                'Rangkapan Jaya',
                'Rangkapan Jaya Baru',
            ],
            'Sawangan' => [
                'Bedahan',
                'Cinangka',
                'Kedaung',
                'Pasir Putih',
                'Pengasinan',
                'Sawangan',
                'Sawangan Baru',
            ],
            'Sukmajaya' => [
                'Abadijaya',
                'Bakti Jaya',
                'Cisalak',
                'Mekarsari',
                'Sukmajaya',
                'Tirtajaya',
            ],
            'Tapos' => [
                'Cimpaeun',
                'Cilangkap',
                'Jatijajar',
                'Leuwinanggung',
                'Sukatani',
                'Sukamaju Baru',
                'Tapos',
            ],
        ];
    }

    /**
     * Mengembalikan list kelurahan Kota Depok dalam bentuk flat (1 dimensi).
     *
     * - Key = nama kelurahan (yang disimpan di DB).
     * - Value = label lengkap yang tampil di UI, misalnya:
     *   "Beji Timur (Kec. Beji)".
     *
     * Data dihasilkan dengan "meratakan" hasil depokKelurahanByKecamatan().
     */
    private function depokKelurahanOptions(): array
    {
        // Ambil grouped kelurahan per kecamatan
        $grouped = $this->depokKelurahanByKecamatan();

        // Siapkan array flat
        $flat = [];

        // Loop setiap kecamatan
        foreach ($grouped as $kecamatan => $kelurahanList) {
            // Loop setiap kelurahan di kecamatan tersebut
            foreach ($kelurahanList as $kel) {
                // Key = nama kelurahan, value = label "Kelurahan (Kec. ...)"
                $flat[$kel] = $kel . ' (Kec. ' . $kecamatan . ')';
            }
        }

        // Kembalikan array flat
        return $flat;
    }

    /**
     * Menentukan kecamatan yang MASIH tersedia untuk dibuatkan akun Puskesmas baru.
     *
     * - Ambil master 11 kecamatan Depok.
     * - Ambil nama_puskesmas yang sudah ada di tabel puskesmas.
     * - Kembalikan kecamatan yang belum terpakai (belum punya akun Puskesmas).
     */
    private function availableKecamatanForCreate(): array
    {
        // Semua kecamatan master
        $all = $this->depokKecamatanOptions();

        // Ambil nama_puskesmas yang sudah dipakai di tabel puskesmas
        $taken = Puskesmas::query()
            ->pluck('nama_puskesmas')
            ->all();

        // Jika belum ada puskesmas sama sekali, semua kecamatan masih available.
        if (empty($taken)) {
            return $all;
        }

        // Buang key yang sudah ada di $taken.
        // array_flip($taken) menjadikan value sebagai key.
        // array_diff_key menghapus kecamatan yang key-nya ada di flipped $taken.
        return array_diff_key($all, array_flip($taken));
    }

    /**
     * Menentukan kecamatan yang tersedia untuk EDIT data Puskesmas tertentu.
     *
     * - Sama seperti availableKecamatanForCreate(),
     *   tetapi kecamatan milik data yang sedang diedit TIDAK dianggap "taken".
     *   Artinya, user tetap bisa memilih kecamatan yang sedang ia miliki.
     */
    private function availableKecamatanForEdit(string $currentKecamatan): array
    {
        // Semua kecamatan master
        $all = $this->depokKecamatanOptions();

        // Ambil daftar nama_puskesmas yang sudah ada
        $taken = Puskesmas::query()
            ->pluck('nama_puskesmas')
            ->all();

        // Buang kecamatan milik data yang sedang diedit dari daftar taken,
        // supaya tetap bisa dipilih.
        $taken = array_filter($taken, function ($nama) use ($currentKecamatan) {
            return $nama !== $currentKecamatan;
        });

        // Jika setelah difilter, tidak ada taken, berarti semua kecamatan available.
        if (empty($taken)) {
            return $all;
        }

        // Buang kecamatan yang key-nya ada di taken.
        return array_diff_key($all, array_flip($taken));
    }

    /**
     * ================================
     *  A. LIST DATA MASTER (INDEX)
     * ================================
     *
     * Menampilkan daftar akun berdasarkan tab:
     * - tab = 'bidan'     → daftar bidan
     * - tab = 'rs'        → daftar rumah sakit
     * - tab = 'puskesmas' → daftar puskesmas
     *
     * Query menggunakan inner join, hanya untuk akun yang:
     * - status = true (aktif)
     * - role_id sesuai role yang dipilih.
     */
    public function index(Request $request)
    {
        // Ambil query string 'tab', default 'bidan'
        $tab = $request->query('tab', 'bidan'); // bidan | rs | puskesmas

        // Ambil query string 'q' untuk pencarian, di-trim dan di-cast string
        $q = trim((string) $request->query('q', ''));

        // Map tab -> nama role yang dipakai
        $roleMap = ['bidan' => 'bidan', 'rs' => 'rs', 'puskesmas' => 'puskesmas'];

        // Dapatkan role_id berdasarkan nama role (bidan / rs / puskesmas)
        $roleId = $this->roleId($roleMap[$tab] ?? 'bidan');

        // Base query: semua user aktif dengan role_id tersebut
        $base = User::query()
            ->where('users.status', true)
            ->where('users.role_id', $roleId);

        // Jika tab = 'rs' (rumah sakit)
        if ($tab === 'rs') {
            $accounts = $base
                // Join ke tabel rumah_sakits berdasarkan user_id
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                // Pilih kolom yang akan ditampilkan
                ->select('users.id', 'users.name', 'users.email')
                // Jika ada pencarian (q tidak kosong), filter by name/email/nama RS
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%")
                            ->orWhere('rumah_sakits.nama', 'ilike', "%$q%");
                    });
                })
                // Urutkan berdasarkan tanggal pembuatan user, terbaru di atas
                ->orderBy('users.created_at', 'desc')
                // Paginate 5 per halaman, dengan query string dipertahankan
                ->paginate(5)->withQueryString();

            // Jika tab = 'puskesmas'
        } elseif ($tab === 'puskesmas') {
            $accounts = $base
                // Join ke tabel puskesmas berdasarkan user_id
                ->join('puskesmas', 'puskesmas.user_id', '=', 'users.id')
                // Pilih kolom yang akan ditampilkan
                ->select('users.id', 'users.name', 'users.email', 'puskesmas.nama_puskesmas')
                // Filter pencarian berdasarkan nama user, email, atau nama_puskesmas
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%")
                            ->orWhere('puskesmas.nama_puskesmas', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.created_at', 'desc')
                ->paginate(5)->withQueryString();

            // Selain itu, dianggap tab = 'bidan'
        } else { // bidan
            $accounts = $base
                // Join ke tabel bidans berdasarkan user_id
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                // Left join ke puskesmas untuk mendapatkan nama_puskesmas (boleh null)
                ->leftJoin('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
                // Pilih kolom yang akan ditampilkan
                ->select('users.id', 'users.name', 'users.email', 'puskesmas.nama_puskesmas')
                // Filter pencarian berdasarkan nama user, email, atau nama_puskesmas
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%")
                            ->orWhere('puskesmas.nama_puskesmas', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.created_at', 'desc')
                ->paginate(5)->withQueryString();
        }

        // Daftar semua puskesmas yang ada, dipakai misalnya untuk dropdown filter di view.
        $puskesmasList = Puskesmas::query()
            ->select('id', 'nama_puskesmas')
            ->orderBy('nama_puskesmas')
            ->get();

        // Tampilkan view data-master utama dengan data yang sudah disiapkan
        return view('dinkes.data-master.data-master', [
            'tab'           => $tab,
            'q'             => $q,
            'accounts'      => $accounts,
            'puskesmasList' => $puskesmasList,
        ]);
    }

    // ========= STORE RS =========

    /**
     * Menyimpan data RS baru (akun user + entri di tabel rumah_sakits).
     */
    public function storeRs(Request $request)
    {
        // Ambil daftar key kecamatan Depok valid
        $kecamatanKeys = array_keys($this->depokKecamatanOptions());
        // Ambil daftar key kelurahan Depok valid (flat)
        $kelurahanKeys = array_keys($this->depokKelurahanOptions());

        // Validasi input form pembuatan RS
        $payload = $request->validate([
            'pic_name'  => 'required|string|max:255',       // nama PIC / user
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'phone'     => 'nullable|string|max:50',
            'nama'      => 'required|string|max:255',       // nama RS
            // kecamatan wajib salah satu dari daftar kecamatan Depok
            'kecamatan' => 'required|string|in:' . implode(',', $kecamatanKeys),
            // kelurahan wajib salah satu dari daftar kelurahan Depok
            'kelurahan' => 'required|string|in:' . implode(',', $kelurahanKeys),
            'lokasi'    => 'nullable|string',               // alamat / lokasi RS
        ]);

        // Transaksi: insert ke tabel users dan rumah_sakits harus berjalan bersama
        DB::transaction(function () use ($payload) {
            // Buat user RS
            $user = new User();
            $user->name     = $payload['pic_name'];
            $user->email    = $payload['email'];
            $user->password = Hash::make($payload['password']);
            $user->phone    = $payload['phone'] ?? null;
            $user->address  = $payload['lokasi'] ?? null;
            $user->status   = 1;
            $user->role_id  = $this->roleId('rs');
            $user->save();

            // Buat record rumah sakit
            $rs = new RumahSakit();
            $rs->user_id   = $user->id;
            $rs->nama      = $payload['nama'];
            $rs->lokasi    = $payload['lokasi'] ?? '';
            $rs->kecamatan = $payload['kecamatan'];
            $rs->kelurahan = $payload['kelurahan'];
            $rs->save();
        });

        // Redirect balik dengan pesan sukses
        return back()->with('ok', 'Data RS berhasil ditambahkan.');
    }

    /**
     * Menyimpan data Puskesmas baru (akun user + entri di tabel puskesmas).
     */
    public function storePuskesmas(Request $request)
    {
        // Ambil daftar key kecamatan Depok valid (nama-nama kecamatan)
        $kecamatanKeys = array_keys($this->depokKecamatanOptions());

        // Validasi input form Puskesmas
        $payload = $request->validate([
            'pic_name' => 'required|string|max:255',   // nama PIC (user)
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:50',

            // nama di sini harus salah satu kecamatan Depok
            // dan unik di tabel puskesmas.nama_puskesmas
            'nama'     => 'required|string|in:' . implode(',', $kecamatanKeys) .
                '|unique:puskesmas,nama_puskesmas',

            'lokasi'   => 'nullable|string',
        ], [
            // Pesan custom untuk error validasi
            'nama.unique' => 'Puskesmas / Kecamatan tersebut sudah memiliki akun.',
            'nama.in'     => 'Nama Puskesmas harus salah satu dari daftar kecamatan Kota Depok.',
        ]);

        // Transaksi agar insert users + puskesmas berjalan atomik
        DB::transaction(function () use ($payload) {
            $user = new User();
            $user->name     = $payload['pic_name'];
            $user->email    = $payload['email'];
            $user->password = Hash::make($payload['password']);
            $user->phone    = $payload['phone'] ?? null;
            $user->address  = $payload['lokasi'] ?? null;
            $user->status   = 1;
            $user->role_id  = $this->roleId('puskesmas');
            $user->save();

            $pusk = new Puskesmas();
            $pusk->user_id        = $user->id;
            $pusk->nama_puskesmas = $payload['nama'];
            $pusk->lokasi         = $payload['lokasi'] ?? '';
            $pusk->kecamatan      = $payload['nama'];
            $pusk->is_mandiri     = 0;
            $pusk->save();
        });


        // Redirect balik dengan pesan sukses
        return back()->with('ok', 'Data Puskesmas berhasil ditambahkan.');
    }

    /**
     * Menyimpan data Bidan baru (akun user + entri di tabel bidans).
     */
    public function storeBidan(Request $request)
    {
        // Validasi input pendaftaran bidan
        $payload = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|string|min:8',
            'phone'              => 'nullable|string|max:50',
            'address'            => 'nullable|string',
            'nomor_izin_praktek' => 'required|string|max:255',
            'puskesmas_id'       => 'required|exists:puskesmas,id', // harus ada di tabel puskesmas
        ]);

        // Transaksi untuk insert user + bidans
        DB::transaction(function () use ($payload) {
            $user = new User();
            $user->name     = $payload['name'];
            $user->email    = $payload['email'];
            $user->password = Hash::make($payload['password']);
            $user->phone    = $payload['phone'] ?? null;
            $user->address  = $payload['address'] ?? null;
            $user->status   = 1;
            $user->role_id  = $this->roleId('bidan');
            $user->save();

            $bidan = new Bidan();
            $bidan->user_id            = $user->id;
            $bidan->nomor_izin_praktek = $payload['nomor_izin_praktek'];
            $bidan->puskesmas_id       = $payload['puskesmas_id'];
            $bidan->save();
        });


        // Redirect balik dengan pesan sukses
        return back()->with('ok', 'Akun Bidan berhasil ditambahkan.');
    }

    /**
     * Reset password user ke nilai default '12345678'.
     *
     * Catatan:
     * - Input new_password tetap divalidasi jika ada, tapi nilai akhirnya diabaikan
     *   dan selalu diset ke '12345678' demi konsistensi SOP.
     */
    public function resetPassword(Request $request, int $user)
    {
        // Validasi opsional: jika user mengisi new_password, minimal 8 karakter
        $request->validate([
            'new_password' => 'nullable|string|min:8',
        ]);

        // Password default yang DIWAJIBKAN
        $new = '12345678';

        // Update kolom password user dengan hash dari nilai default
        User::where('id', $user)->update([
            'password'   => Hash::make($new),
            'updated_at' => now(),
        ]);

        // Kirim flash message + informasi password baru (untuk ditampilkan ke Dinkes)
        return back()->with([
            'ok'           => 'Password berhasil direset ke nilai default 12345678.',
            'new_password' => $new,
            'pw_user_id'   => $user,
            'flash_kind'   => 'password-reset-fixed',
        ]);
    }

    /**
     * =========================
     *  FORM CREATE (GET)
     * =========================
     *
     * Menampilkan form create untuk:
     * - Bidan
     * - RS
     * - Puskesmas
     * Sesuai tab yang dipilih (query string 'tab').
     */
    public function create(Request $request)
    {
        // Ambil tab dari query string, default 'bidan'
        $tab = $request->query('tab', 'bidan');

        // Ambil daftar puskesmas aktif (users.status = true), join ke users
        $puskesmasList = Puskesmas::query()
            ->join('users', 'users.id', '=', 'puskesmas.user_id')
            ->where('users.status', true)
            ->orderBy('puskesmas.nama_puskesmas')
            ->select('puskesmas.id', 'puskesmas.nama_puskesmas')
            ->get();

        // Kategori kecamatan yang masih tersedia untuk dibuatkan Puskesmas baru
        $kecamatanOptions = $this->availableKecamatanForCreate();

        // Tambahan master untuk RS: semua kecamatan dan kelurahan Depok
        $rsKecamatanOptions       = $this->depokKecamatanOptions();
        $rsKelurahanOptions       = $this->depokKelurahanOptions();
        $rsKelurahanByKecamatan   = $this->depokKelurahanByKecamatan();

        // Tampilkan view create data master
        return view('dinkes.data-master.data-master-create', [
            'tab'                    => $tab,
            'puskesmasList'          => $puskesmasList,
            'kecamatanOptions'       => $kecamatanOptions,
            'rsKecamatanOptions'     => $rsKecamatanOptions,
            'rsKelurahanOptions'     => $rsKelurahanOptions,
            'rsKelurahanByKecamatan' => $rsKelurahanByKecamatan,
        ]);
    }

    /**
     * =========================
     *  B. DETAIL (SHOW)
     * =========================
     *
     * Menampilkan detail satu akun (RS / Puskesmas / Bidan)
     * berdasarkan tab dan id user.
     */
    public function show(Request $request, int $user)
    {
        // Ambil tab dari query string, default 'bidan'
        $tab = $request->query('tab', 'bidan');

        // Jika tab = 'rs', join users + rumah_sakits
        if ($tab === 'rs') {
            $data = User::query()
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'rumah_sakits.nama',
                    'rumah_sakits.kecamatan',
                    'rumah_sakits.kelurahan',
                    'rumah_sakits.lokasi'
                )
                ->first();

            // Jika tab = 'puskesmas', join users + puskesmas
        } elseif ($tab === 'puskesmas') {
            $data = User::query()
                ->join('puskesmas', 'puskesmas.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'puskesmas.nama_puskesmas',
                    'puskesmas.kecamatan',
                    'puskesmas.lokasi'
                )
                ->first();

            // Selain itu, dianggap tab = 'bidan'
        } else {
            $data = User::query()
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->join('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'bidans.nomor_izin_praktek',
                    'puskesmas.nama_puskesmas'
                )
                ->first();
        }

        // Jika data tidak ditemukan, lempar 404
        abort_unless($data, 404);

        // Tampilkan view detail dengan tab dan data
        return view('dinkes.data-master.data-master-show', compact('tab', 'data'));
    }

    /**
     * =========================
     *  C. EDIT (GET)
     * =========================
     *
     * Menampilkan form edit akun master (RS / Puskesmas / Bidan).
     */
    public function edit(Request $request, int $user)
    {
        // Ambil tab dari query string, default 'bidan'
        $tab = $request->query('tab', 'bidan');

        // Ambil list Puskesmas untuk dropdown (misal di form Bidan)
        $puskesmasList = Puskesmas::query()
            ->select('id', 'nama_puskesmas')
            ->orderBy('nama_puskesmas')
            ->get();

        // Default kelurahanOptions kosong, akan diisi jika tab = rs
        $kelurahanOptions = [];

        // Master kelurahanByKecamatan, kepake untuk JS di view
        $rsKelurahanByKecamatan = $this->depokKelurahanByKecamatan();

        // Jika tab = 'rs'
        if ($tab === 'rs') {
            // Join users + rumah_sakits untuk data RS
            $data = User::query()
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'rumah_sakits.nama',
                    'rumah_sakits.kecamatan',
                    'rumah_sakits.kelurahan',
                    'rumah_sakits.lokasi'
                )
                ->first();

            // Semua kecamatan dan kelurahan Depok jadi opsi untuk RS
            $kecamatanOptions = $this->depokKecamatanOptions();
            $kelurahanOptions = $this->depokKelurahanOptions();

            // Jika tab = 'puskesmas'
        } elseif ($tab === 'puskesmas') {
            // Join users + puskesmas
            $data = User::query()
                ->join('puskesmas', 'puskesmas.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'puskesmas.nama_puskesmas as nama',
                    'puskesmas.kecamatan',
                    'puskesmas.lokasi'
                )
                ->first();

            // Jika data ada, hitung kecamatan yang bisa dipilih (exclude kecamatan lain yang sudah dipakai)
            $kecamatanOptions = $data
                ? $this->availableKecamatanForEdit($data->kecamatan)
                : $this->depokKecamatanOptions();

            // Selain itu, tab = 'bidan'
        } else {
            // Join users + bidans
            $data = User::query()
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'bidans.nomor_izin_praktek',
                    'bidans.puskesmas_id',
                    'users.address'
                )
                ->first();

            // Master kecamatan (bisa dipakai jika di view butuh)
            $kecamatanOptions = $this->depokKecamatanOptions();
        }

        // Jika data tidak ditemukan, lempar 404
        abort_unless($data, 404);

        // Tampilkan view edit
        return view('dinkes.data-master.data-master-edit', [
            'tab'                   => $tab,
            'data'                  => $data,
            'puskesmasList'         => $puskesmasList,
            'kecamatanOptions'      => $kecamatanOptions,
            'kelurahanOptions'      => $kelurahanOptions,
            'rsKelurahanByKecamatan' => $rsKelurahanByKecamatan,
        ]);
    }

    /**
     * =========================
     *  D. UPDATE (POST/PUT)
     * =========================
     *
     * Mengupdate data RS / Puskesmas / Bidan.
     * Tanpa upsert, hanya update data yang sudah ada.
     */
    public function update(Request $request, int $user)
    {
        // Tab menentukan jenis akun yang diupdate
        $tab = $request->query('tab', 'bidan');

        // Jika tab = 'rs'
        if ($tab === 'rs') {
            // Ambil key kecamatan dan kelurahan valid
            $kecamatanKeys = array_keys($this->depokKecamatanOptions());
            $kelurahanKeys = array_keys($this->depokKelurahanOptions());

            // Validasi input edit RS
            $payload = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email,' . $user,
                'phone'     => 'nullable|string|max:50',
                'lokasi'    => 'nullable|string',
                'kecamatan' => 'required|string|in:' . implode(',', $kecamatanKeys),
                'kelurahan' => 'required|string|in:' . implode(',', $kelurahanKeys),
                'nama'      => 'required|string|max:255',
            ]);

            // Transaksi untuk update users + rumah_sakits
            DB::transaction(function () use ($user, $payload) {
                // Update tabel users
                User::where('id', $user)->update([
                    'name'       => $payload['name'],
                    'email'      => $payload['email'],
                    'phone'      => $payload['phone'] ?? null,
                    'address'    => $payload['lokasi'] ?? null,
                    'updated_at' => now(),
                ]);

                // Update tabel rumah_sakits
                RumahSakit::where('user_id', $user)->update([
                    'nama'       => $payload['nama'],
                    'lokasi'     => $payload['lokasi'] ?? '',
                    'kecamatan'  => $payload['kecamatan'],
                    'kelurahan'  => $payload['kelurahan'],
                    'updated_at' => now(),
                ]);
            });

            // Jika tab = 'puskesmas'
        } elseif ($tab === 'puskesmas') {
            // Ambil key kecamatan Depok
            $kecamatanKeys = array_keys($this->depokKecamatanOptions());

            // Validasi input Puskesmas
            $payload = $request->validate([
                'name'   => 'required|string|max:255',
                'email'  => 'required|email|unique:users,email,' . $user,
                'phone'  => 'nullable|string|max:50',
                'lokasi' => 'nullable|string',
                'nama'   => 'required|string|in:' . implode(',', $kecamatanKeys),
            ]);

            // Transaksi update users + puskesmas
            DB::transaction(function () use ($user, $payload) {

                // Update user PIC
                User::where('id', $user)->update([
                    'name'       => $payload['name'],
                    'email'      => $payload['email'],
                    'phone'      => $payload['phone'] ?? null,
                    'address'    => $payload['lokasi'] ?? null,
                    'updated_at' => now(),
                ]);

                // Update data puskesmas
                Puskesmas::where('user_id', $user)->update([
                    'nama_puskesmas' => $payload['nama'],
                    'lokasi'         => $payload['lokasi'] ?? '',
                    'kecamatan'      => $payload['nama'],
                    'is_mandiri'     => 0,
                    'updated_at'     => now(),
                ]);
            });

            // Selain itu, berarti tab = 'bidan'
        } else { // bidan
            // Validasi input bidan
            $payload = $request->validate([
                'name'               => 'required|string|max:255',
                'email'              => 'required|email|unique:users,email,' . $user,
                'phone'              => 'nullable|string|max:50',
                'address'            => 'nullable|string',
                'nomor_izin_praktek' => 'required|string|max:255',
                'puskesmas_id'       => 'required|exists:puskesmas,id',
            ]);

            // Transaksi update users + bidans
            DB::transaction(function () use ($user, $payload) {
                // Update user
                User::where('id', $user)->update([
                    'name'       => $payload['name'],
                    'email'      => $payload['email'],
                    'phone'      => $payload['phone'] ?? null,
                    'address'    => $payload['address'] ?? null,
                    'updated_at' => now(),
                ]);

                // Update detail bidan
                Bidan::where('user_id', $user)->update([
                    'nomor_izin_praktek' => $payload['nomor_izin_praktek'],
                    'puskesmas_id'       => $payload['puskesmas_id'],
                    'updated_at'         => now(),
                ]);
            });
        }

        // Setelah update, redirect ke halaman index dengan tab dan q yang sama
        return redirect()->route('dinkes.data-master', [
            'tab' => $tab,
            'q'   => $request->query('q'),
        ])->with('ok', 'Data berhasil disimpan.');
    }

    /**
     * Menghapus akun (RS / Puskesmas / Bidan) beserta detailnya.
     *
     * - Untuk RS: hapus rumah_sakits + user.
     * - Untuk Puskesmas: detach bidans (set puskesmas_id = null),
     *   lalu hapus puskesmas + user.
     * - Untuk Bidan: hapus bidans + user.
     */
    public function destroy(Request $request, int $user)
    {
        // Tab menentukan jenis akun
        $tab = $request->query('tab', 'bidan');

        try {
            // Jalankan operasi hapus dalam transaksi
            DB::transaction(function () use ($tab, $user) {
                if ($tab === 'rs') {
                    // Hapus detail RS
                    RumahSakit::where('user_id', $user)->delete();
                } elseif ($tab === 'puskesmas') {
                    // Cari id puskesmas berdasarkan user_id
                    $puskesmasId = Puskesmas::where('user_id', $user)->value('id');

                    if ($puskesmasId) {
                        // Putus relasi bidan dengan puskesmas yang akan dihapus
                        Bidan::where('puskesmas_id', $puskesmasId)
                            ->update([
                                'puskesmas_id' => null,
                                'updated_at'   => now(),
                            ]);

                        // Hapus data puskesmas
                        Puskesmas::where('id', $puskesmasId)->delete();
                    }
                } else {
                    // Tab = bidan: hapus detail bidan
                    Bidan::where('user_id', $user)->delete();
                }

                // Terakhir, hapus user-nya
                User::where('id', $user)->delete();
            });

            // Jika transaksi sukses, redirect dengan pesan OK
            return redirect()
                ->route('dinkes.data-master', [
                    'tab' => $tab,
                    'q'   => $request->query('q'),
                ])
                ->with('ok', 'Akun dan detail berhasil dihapus.');
        } catch (\Throwable $e) {
            // Jika terjadi error, tangkap dan kirim pesan error ke UI
            return redirect()
                ->route('dinkes.data-master', [
                    'tab' => $tab,
                    'q'   => request()->query('q'),
                ])
                ->with('err', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
