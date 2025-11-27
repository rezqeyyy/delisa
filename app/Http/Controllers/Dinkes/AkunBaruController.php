<?php

// Namespace: menandakan file ini milik modul / kelompok controller untuk Dinkes.
namespace App\Http\Controllers\Dinkes;

// Mengimpor base Controller Laravel.
use App\Http\Controllers\Controller;

// Mengimpor model User (tabel users).
use App\Models\User;

// Mengimpor model Role (tabel roles).
use App\Models\Role;

// Mengimpor model Puskesmas (tabel puskesmas).
use App\Models\Puskesmas;

// Mengimpor model RumahSakit (tabel rumah_sakits).
use App\Models\RumahSakit;

// Mengimpor model Bidan (tabel bidans).
use App\Models\Bidan;

// Mengimpor Request untuk mengambil data dari HTTP request.
use Illuminate\Http\Request;

// Mengimpor helper Str untuk generate string acak atau utilitas string lainnya.
use Illuminate\Support\Str;

// Mengimpor Hash untuk enkripsi / hashing password.
use Illuminate\Support\Facades\Hash;

// Mengimpor DB facade untuk operasi database level query builder / transaksi.
use Illuminate\Support\Facades\DB;

class AkunBaruController extends Controller
{
    /**
     * ============================================================
     *  METHOD: index
     *  URL   : GET dinkes/akun-baru (misal)
     *  FUNGSI: Menampilkan daftar pengajuan akun baru
     *          (users dengan status = false / 0 / pending)
     * ============================================================
     */

    // List pengajuan akun = users.status = false
    public function index(Request $request)
    {
        // Ambil parameter query string 'q' untuk pencarian nama/email.
        $q = $request->query('q');

        /**
         * Query ke tabel users dengan relasi role:
         * - with('role') => eager load relasi role agar tidak N+1.
         * - where('status', false) => hanya ambil akun dengan status pending.
         * - when($q, ...) => kalau $q ada, baru apply filter pencarian.
         */
        $requests = User::with('role')
            ->where('status', false)
            // when($q) berarti: jika $q tidak null/empty, jalankan closure-nya.
            ->when($q, function ($qq) use ($q) {
                // Bungkus dalam where(...) lain agar OR bisa dipakai di dalamnya.
                $qq->where(function ($w) use ($q) {
                    // Pencarian nama pakai ILIKE (PostgreSQL, case-insensitive).
                    $w->where('name', 'ilike', "%{$q}%")
                        // Atau email yang mirip dengan keyword q.
                        ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            // Urutkan pengajuan terbaru di atas (created_at desc).
            ->orderByDesc('created_at')
            // Paginate 10 data per halaman.
            ->paginate(10)
            // withQueryString() supaya parameter ?q=... tetap nempel saat ganti halaman.
            ->withQueryString();

        // Kirim variabel $q dan $requests ke view 'dinkes.akun-baru.akun-baru'.
        return view('dinkes.akun-baru.akun-baru', compact('q', 'requests'));
    }

    /**
     * ============================================================
     *  METHOD: store
     *  FUNGSI: Menyimpan pengajuan akun baru (membuat user dengan status=false)
     *          untuk kategori bidan / rumah sakit / puskesmas.
     * ============================================================
     */

    // Simpan pengajuan (buat user status=false)
    public function store(Request $request)
    {
        /**
         * Mapping value dari <select name="role"> di form
         * ke nilai sebenarnya yang disimpan di kolom roles.nama_role.
         *
         * Contoh:
         * - option value="rumah_sakit"  -> nama_role "rs"
         * - option value="bidan"        -> nama_role "bidan"
         * - option value="puskesmas"    -> nama_role "puskesmas"
         */
        $mapRole = [
            'bidan'        => 'bidan',
            'rumah_sakit'  => 'rs',
            'puskesmas'    => 'puskesmas',
        ];

        /**
         * Validasi input dari form pengajuan akun baru.
         * - name: wajib, string, max 255.
         * - email: wajib, format email valid, max 255, dan harus unik di tabel users.
         * - role: wajib, dan nilainya harus salah satu dari key $mapRole.
         */
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['required', 'in:' . implode(',', array_keys($mapRole))],
        ], [
            // Pesan khusus jika email sudah terdaftar.
            'email.unique' => 'E-mail sudah terdaftar.',
        ]);

        /**
         * Tentukan nama role sebenarnya yang akan dipakai di tabel roles.nama_role.
         * - $request->role berisi value dari form (bidan/rumah_sakit/puskesmas).
         * - $mapRole[...] mengkonversi ke 'bidan' | 'rs' | 'puskesmas'.
         */
        $roleName = $mapRole[$request->role];

        /**
         * Cari role berdasarkan nama_role.
         * Kalau belum ada, firstOrCreate akan membuat entri baru di tabel roles.
         */
        $role = Role::where('nama_role', $roleName)->firstOrCreate();

        /**
         * Buat password sementara untuk user baru:
         * - Jika Str::password() tersedia di versi Laravel ini → gunakan (lebih aman).
         * - Kalau belum ada (misal versi Laravel lebih lama) → fallback ke Str::random(12).
         */
        $tempPassword = method_exists(Str::class, 'password')
            ? Str::password(10)
            : Str::random(12);

        /**
         * Membuat user baru dengan status=false berarti akunnya masih "pending".
         * - password disimpan dalam bentuk hash (Hash::make).
         * - role_id mengacu ke id role yang sudah ditemukan / dibuat di atas.
         */
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($tempPassword),
            'status'   => false,        // pending (belum di-approve Dinkes)
            'role_id'  => $role->id,
        ]);

        /**
         * Redirect kembali ke halaman daftar akun baru
         * dan kirimkan flash message berisi informasi
         * termasuk password sementara (biasanya ditampilkan untuk Dinkes).
         */
        return redirect()
            ->route('dinkes.akun-baru')
            ->with('ok', "Pengajuan akun dibuat. Password sementara: {$tempPassword}");
    }

    /**
     * ============================================================
     *  METHOD: approve
     *  FUNGSI: Menerima pengajuan akun:
     *          - Mengubah status user menjadi aktif (status=true).
     *          - Membuat data detail di tabel puskesmas/rumah_sakits
     *            jika belum dibuat oleh RoleRegistrationController.
     * ============================================================
     */

    // Terima pengajuan: aktifkan user (status=true) + bikin detail sesuai role
    public function approve($id)
    {
        /**
         * Ambil user beserta role-nya:
         * - with('role'): eager load relasi role.
         * - where('status', false): hanya ambil user yang masih pending.
         * - findOrFail($id): jika tidak ditemukan → 404.
         */
        $user = User::with('role')
            ->where('status', false)
            ->findOrFail($id);

        /**
         * Bungkus dalam DB::transaction agar:
         * - Jika ada error di tengah proses, semua perubahan dibatalkan (rollback).
         * - Menjaga konsistensi data antara tabel users dan tabel detail (puskesmas/rumah_sakits/bidans).
         */
        DB::transaction(function () use ($user) {
            // Ubah status user menjadi aktif (true).
            $user->status = true;
            // Simpan perubahan ke database.
            $user->save();

            // Ambil nama role user: 'bidan' | 'rs' | 'puskesmas' (bisa null).
            $roleName = optional($user->role)->nama_role;

            /**
             * Jika role-nya puskesmas:
             * - Cek dulu apakah sudah ada data di tabel puskesmas yang punya user_id ini.
             * - Kalau belum ada → buat entry default.
             * - Kalau sudah ada (dibuat oleh RoleRegistrationController) → jangan disentuh.
             */
            if ($roleName === 'puskesmas') {
                // Cek apakah sudah ada data puskesmas dengan user_id = $user->id
                $exists = Puskesmas::where('user_id', $user->id)->exists();

                // Jika belum ada, insert data puskesmas default.
                if (!$exists) {
                    $puskesmas = new Puskesmas();
                    $puskesmas->user_id        = $user->id;
                    $puskesmas->nama_puskesmas = $user->name ?? 'Belum diisi';
                    $puskesmas->lokasi         = 'Belum diisi';
                    $puskesmas->kecamatan      = 'Belum diisi';
                    $puskesmas->is_mandiri     = 0;
                    $puskesmas->created_at     = now();
                    $puskesmas->updated_at     = now();
                    $puskesmas->save();
                }
            }
            // Jika role-nya rs (rumah sakit).
            elseif ($roleName === 'rs') {
                /**
                 * Untuk RS:
                 * - Sama seperti puskesmas, cek dulu data di tabel rumah_sakits.
                 * - Jika belum ada → buat data default.
                 * - Jika sudah ada → tidak diubah di sini.
                 */
                $exists = RumahSakit::where('user_id', $user->id)->exists();

                if (!$exists) {
                    $rs = new RumahSakit();
                    $rs->user_id    = $user->id;
                    $rs->nama       = $user->name ?? 'Belum diisi';
                    $rs->lokasi     = 'Belum diisi';
                    $rs->kecamatan  = 'Belum diisi';
                    $rs->kelurahan  = 'Belum diisi';
                    $rs->created_at = now();
                    $rs->updated_at = now();
                    $rs->save();
                }
            }
            // Jika role-nya bidan.
            elseif ($roleName === 'bidan') {
                /**
                 * Untuk bidan:
                 * - Detail bidan (tabel bidans) sudah dibuat pada saat registrasi (storeBidan).
                 * - Di sini tidak perlu menambah atau mengubah data detail.
                 * - Cukup mengaktifkan user (status=true) yang sudah dilakukan di atas.
                 */
            }
        });

        /**
         * Setelah transaksi selesai (berhasil),
         * redirect kembali dengan pesan sukses.
         */
        return back()->with('ok', "Pengajuan akun {$user->name} telah diterima (aktif).");
    }

    /**
     * ============================================================
     *  METHOD: reject
     *  FUNGSI: Menolak pengajuan akun:
     *          - Menghapus user pending.
     *          - Sekaligus menghapus data detail terkait (bidan/rs/puskesmas)
     *            kalau ada.
     * ============================================================
     */

    // Tolak pengajuan: hapus user pending
    public function reject($id)
    {
        /**
         * Cari user pending (status=false) berdasarkan id.
         * - with('role'): agar kita tahu dia bidan/rs/puskesmas.
         * - where('status', false): hanya boleh menolak akun yang belum aktif.
         */
        $user = User::with('role')
            ->where('status', false) // hanya akun pending
            ->findOrFail($id);

        // Simpan nama user untuk digunakan di pesan setelah delete.
        $name = $user->name;

        // Ambil nama role (bisa 'bidan' | 'rs' | 'puskesmas' | null).
        $roleName = optional($user->role)->nama_role;

        /**
         * DB::transaction agar:
         * - Hapus detail + user dilakukan secara atomik.
         * - Kalau ada error saat hapus detail, user tidak jadi terhapus sendiri-sendiri.
         */
        DB::transaction(function () use ($user, $roleName) {
            /**
             * Switch berdasarkan role:
             * - Jika bidan → hapus dari tabel bidans.
             * - Jika rs    → hapus dari tabel rumah_sakits.
             * - Jika puskesmas → hapus dari tabel puskesmas.
             */
            switch ($roleName) {
                case 'bidan':
                    Bidan::where('user_id', $user->id)->delete();
                    break;

                case 'rs':
                    RumahSakit::where('user_id', $user->id)->delete();
                    break;

                case 'puskesmas':
                    Puskesmas::where('user_id', $user->id)->delete();
                    break;
            }

            /**
             * Setelah detail spesifik role dihapus,
             * hapus record user itu sendiri dari tabel users.
             */
            $user->delete();
        });

        // Kembali ke halaman sebelumnya dengan pesan bahwa pengajuan ditolak & dihapus.
        return back()->with('ok', "Pengajuan akun {$name} telah ditolak & dihapus.");
    }
}
