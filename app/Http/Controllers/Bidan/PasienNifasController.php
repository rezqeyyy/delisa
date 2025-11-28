<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PasienNifasBidan;
use App\Models\Pasien;
use App\Models\User;

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
        $ids = $pasienNifas->getCollection()->pluck('id')->all(); // Ambil semua ID pasien nifas
        
        $kfDone = DB::table('kf') // Query tabel kf (kunjungan nifas)
            ->selectRaw('id_nifas, MAX(kunjungan_nifas_ke)::int as max_ke') // Ambil kunjungan terakhir
            ->whereIn('id_nifas', $ids) // Filter per ID pasien nifas
            ->groupBy('id_nifas') // Group per pasien
            ->get()
            ->keyBy('id_nifas'); // Index by id_nifas untuk lookup cepat

        // 4. Define Jadwal KF (Kunjungan Nifas)
        // KF1: 3 hari, KF2: 7 hari, KF3: 14 hari, KF4: 42 hari setelah melahirkan
        $dueDays = [1=>3, 2=>7, 3=>14, 4=>42];
        
        $today = Carbon::today(); // Tanggal hari ini

        // 5. Transform Data untuk Hitung Peringatan
        $pasienNifas->getCollection()->transform(function ($row) use ($kfDone, $dueDays, $today) {
            // Ambil kunjungan terakhir (max_ke), default 0 jika belum ada
            $maxKe = optional($kfDone->get($row->id))->max_ke ?? 0;
            
            // Hitung kunjungan berikutnya yang harus dilakukan
            $nextKe = min(4, $maxKe + 1); // Maksimal KF4, jadi min(4, maxKe+1)
            
            // Hitung selisih hari dari tanggal mulai nifas sampai hari ini
            $days = $row->tanggal ? Carbon::parse($row->tanggal)->diffInDays($today) : 0;
            
            // Ambil batas hari untuk kunjungan berikutnya
            $due = $dueDays[$nextKe] ?? 42; // Default 42 hari jika tidak ada
            
            // Tentukan Label & Warna Badge Peringatan
            if ($row->tanggal === null) {
                // Jika tanggal null, status aman (tidak ada deadline)
                $label = 'Aman';
                $cls = 'bg-[#2EDB58] text-white'; // Hijau
            }
            elseif ($days > $due) {
                // Jika sudah lewat deadline -> TELAT
                $label = 'Telat';
                $cls = 'bg-[#FF3B30] text-white'; // Merah
            }
            elseif ($days >= max(0, $due - 1)) {
                // Jika 1 hari sebelum deadline atau pas deadline -> MEPET
                $label = 'Mepet';
                $cls = 'bg-[#FFC400] text-[#1D1D1D]'; // Kuning
            }
            else {
                // Jika masih jauh dari deadline -> AMAN
                $label = 'Aman';
                $cls = 'bg-[#2EDB58] text-white'; // Hijau
            }
            
            // Set attribute baru ke object
            $row->peringat_label = $label; // Label peringatan (Aman/Mepet/Telat)
            $row->badge_class = $cls;      // Class CSS untuk badge
            $row->next_ke = $nextKe;       // KF ke berapa berikutnya
            
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
            'nama_pasien' => 'required|string|max:255',  // Nama wajib, string, max 255 karakter
            'nik'         => 'required|digits:16',       // NIK wajib, harus 16 digit
            'no_telepon'  => 'required|string|max:20',   // No telp wajib
            'provinsi'    => 'required|string|max:100',  // Provinsi wajib
            'kota'        => 'required|string|max:100',  // Kota wajib
            'kecamatan'   => 'required|string|max:100',  // Kecamatan wajib
            'kelurahan'   => 'required|string|max:100',  // Kelurahan wajib
            'domisili'    => 'required|string',          // Domisili wajib
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

                // Update data alamat pasien
                $existingPasien->update([
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ]);

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

                // 3d. Buat Data Pasien Baru
                $pasien = Pasien::create([
                    'user_id'    => $user->id, // FK ke user yang baru dibuat
                    'nik'        => $validated['nik'],
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ]);
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