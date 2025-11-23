<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataMasterController extends Controller
{
    private function roleId(string $name): int
    {
        // Alias nama role yang dianggap sama
        $aliases = [
            'rs'          => ['rs', 'rumah_sakit'],
            'rumah_sakit' => ['rs', 'rumah_sakit'],
            // role lain cukup pakai nama aslinya
            'bidan'      => ['bidan'],
            'puskesmas'  => ['puskesmas'],
            'dinkes'     => ['dinkes'],
            'pasien'     => ['pasien'],
        ];

        $key        = strtolower($name);
        $candidates = $aliases[$key] ?? [$key]; // kalau tidak ada di alias, pakai nama sendiri

        return (int) DB::table('roles')
            ->where(function ($q) use ($candidates) {
                foreach ($candidates as $i => $n) {
                    if ($i === 0) {
                        $q->whereRaw('LOWER(nama_role) = ?', [$n]);
                    } else {
                        $q->orWhereRaw('LOWER(nama_role) = ?', [$n]);
                    }
                }
            })
            ->value('id');
    }

    /**
     * List kecamatan Kota Depok (dipakai untuk dropdown Puskesmas).
     * Nama key = yang disimpan sebagai nama_puskesmas & kecamatan.
     * Label = teks yang ditampilkan di <option>.
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
     * List kelurahan di Kota Depok (dipakai untuk dropdown RS).
     * Key = nama kelurahan yang disimpan di DB
     * Value = label yang ditampilkan di <option>.
     *
     * Catatan: ini contoh, silakan lengkapi / revisi sesuai kebutuhan.
     */
    private function depokKelurahanOptions(): array
    {
        $grouped = $this->depokKelurahanByKecamatan();
        $flat = [];

        foreach ($grouped as $kecamatan => $kelurahanList) {
            foreach ($kelurahanList as $kel) {
                $flat[$kel] = $kel . ' (Kec. ' . $kecamatan . ')';
            }
        }

        return $flat;
    }



    /**
     * KECAMATAN AVAILABLE UNTUK CREATE (Puskesmas):
     * - ambil master 11 kecamatan
     * - buang yang sudah ada di tabel puskesmas (nama_puskesmas)
     */
    private function availableKecamatanForCreate(): array
    {
        $all = $this->depokKecamatanOptions();

        $taken = DB::table('puskesmas')
            ->pluck('nama_puskesmas')
            ->all();

        if (empty($taken)) {
            return $all;
        }

        // sama seperti RoleRegistrationController::showPuskesmasRegisterForm
        return array_diff_key($all, array_flip($taken));
    }

    /**
     * KECAMATAN AVAILABLE UNTUK EDIT (Puskesmas):
     * - sama seperti create, tapi KECAMATAN milik akun yang sedang diedit tidak dianggap "taken"
     *   sehingga tetap muncul di dropdown.
     */
    private function availableKecamatanForEdit(string $currentKecamatan): array
    {
        $all = $this->depokKecamatanOptions();

        $taken = DB::table('puskesmas')
            ->pluck('nama_puskesmas')
            ->all();

        // buang kecamatan milik data yang sedang diedit
        $taken = array_filter($taken, function ($nama) use ($currentKecamatan) {
            return $nama !== $currentKecamatan;
        });

        if (empty($taken)) {
            return $all;
        }

        return array_diff_key($all, array_flip($taken));
    }

    /** ===== A. LIST (inner join, tanpa has_detail) ===== */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'bidan'); // bidan | rs | puskesmas
        $q   = trim((string) $request->query('q', ''));

        $roleMap = ['bidan' => 'bidan', 'rs' => 'rs', 'puskesmas' => 'puskesmas'];
        $roleId  = $this->roleId($roleMap[$tab] ?? 'bidan');

        $base = DB::table('users')
            ->where('users.status', true)
            ->where('users.role_id', $roleId);

        if ($tab === 'rs') {
            $accounts = $base
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email')
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%")
                            ->orWhere('rumah_sakits.nama', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.created_at', 'desc')
                ->paginate(5)->withQueryString();
        } elseif ($tab === 'puskesmas') {
            $accounts = $base
                ->join('puskesmas', 'puskesmas.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email', 'puskesmas.nama_puskesmas')
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%")
                            ->orWhere('puskesmas.nama_puskesmas', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.created_at', 'desc')
                ->paginate(5)->withQueryString();
        } else { // bidan
            $accounts = $base
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->leftJoin('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
                ->select('users.id', 'users.name', 'users.email', 'puskesmas.nama_puskesmas')
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

        $puskesmasList = DB::table('puskesmas')
            ->select('id', 'nama_puskesmas')
            ->orderBy('nama_puskesmas')
            ->get();

        return view('dinkes.data-master.data-master', [
            'tab'           => $tab,
            'q'             => $q,
            'accounts'      => $accounts,
            'puskesmasList' => $puskesmasList,
        ]);
    }

    // ========= STORE =========

    public function storeRs(Request $request)
    {
        $kecamatanKeys = array_keys($this->depokKecamatanOptions());
        $kelurahanKeys = array_keys($this->depokKelurahanOptions());

        $payload = $request->validate([
            'pic_name'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'phone'     => 'nullable|string|max:50',
            'nama'      => 'required|string|max:255',
            // sekarang wajib salah satu kecamatan Depok
            'kecamatan' => 'required|string|in:' . implode(',', $kecamatanKeys),
            // dan salah satu kelurahan Depok
            'kelurahan' => 'required|string|in:' . implode(',', $kelurahanKeys),
            'lokasi'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($payload) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $payload['pic_name'],
                'email'      => $payload['email'],
                'password'   => Hash::make($payload['password']),
                'phone'      => $payload['phone'] ?? null,
                'address'    => $payload['lokasi'] ?? null,
                'status'     => 1,
                'role_id'    => $this->roleId('rs'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('rumah_sakits')->insert([
                'user_id'    => $userId,
                'nama'       => $payload['nama'],
                'lokasi'     => $payload['lokasi'] ?? '',
                'kecamatan'  => $payload['kecamatan'],
                'kelurahan'  => $payload['kelurahan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('ok', 'Data RS berhasil ditambahkan.');
    }

    public function storePuskesmas(Request $request)
    {
        $kecamatanKeys = array_keys($this->depokKecamatanOptions());

        $payload = $request->validate([
            'pic_name' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:50',

            'nama'     => 'required|string|in:' . implode(',', $kecamatanKeys) .
                '|unique:puskesmas,nama_puskesmas',

            'lokasi'   => 'nullable|string',
        ], [
            'nama.unique' => 'Puskesmas / Kecamatan tersebut sudah memiliki akun.',
            'nama.in'     => 'Nama Puskesmas harus salah satu dari daftar kecamatan Kota Depok.',
        ]);

        DB::transaction(function () use ($payload) {

            $userId = DB::table('users')->insertGetId([
                'name'       => $payload['pic_name'],
                'email'      => $payload['email'],
                'password'   => Hash::make($payload['password']),
                'phone'      => $payload['phone'] ?? null,
                'address'    => $payload['lokasi'] ?? null,
                'status'     => 1,
                'role_id'    => $this->roleId('puskesmas'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('puskesmas')->insert([
                'user_id'        => $userId,
                'nama_puskesmas' => $payload['nama'],
                'lokasi'         => $payload['lokasi'] ?? '',
                'kecamatan'      => $payload['nama'],
                'is_mandiri'     => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return back()->with('ok', 'Data Puskesmas berhasil ditambahkan.');
    }

    public function storeBidan(Request $request)
    {
        $payload = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|string|min:8',
            'phone'              => 'nullable|string|max:50',
            'address'            => 'nullable|string',
            'nomor_izin_praktek' => 'required|string|max:255',
            'puskesmas_id'       => 'required|exists:puskesmas,id',
        ]);

        DB::transaction(function () use ($payload) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $payload['name'],
                'email'      => $payload['email'],
                'password'   => Hash::make($payload['password']),
                'phone'      => $payload['phone'] ?? null,
                'address'    => $payload['address'] ?? null,
                'status'     => 1,
                'role_id'    => $this->roleId('bidan'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('bidans')->insert([
                'user_id'            => $userId,
                'nomor_izin_praktek' => $payload['nomor_izin_praktek'],
                'puskesmas_id'       => $payload['puskesmas_id'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        });

        return back()->with('ok', 'Akun Bidan berhasil ditambahkan.');
    }

    public function resetPassword(Request $request, int $user)
    {
        // Boleh tetap divalidasi minimal 8 karakter kalau diisi,
        // meskipun akhirnya tetap kita override ke 12345678.
        $request->validate([
            'new_password' => 'nullable|string|min:8',
        ]);

        // Password default yang DIWAJIBKAN
        $new = '12345678';

        DB::table('users')->where('id', $user)->update([
            'password'   => Hash::make($new),
            'updated_at' => now(),
        ]);

        return back()->with([
            'ok'           => 'Password berhasil direset ke nilai default 12345678.',
            'new_password' => $new,
            'pw_user_id'   => $user,
            'flash_kind'   => 'password-reset-fixed',
        ]);
    }


    /** =========================
     *  FORM CREATE
     *  =========================*/
    public function create(Request $request)
    {
        $tab = $request->query('tab', 'bidan');

        $puskesmasList = DB::table('puskesmas')
            ->join('users', 'users.id', '=', 'puskesmas.user_id')
            ->where('users.status', true)
            ->orderBy('puskesmas.nama_puskesmas')
            ->select('puskesmas.id', 'puskesmas.nama_puskesmas')
            ->get();

        $kecamatanOptions = $this->availableKecamatanForCreate();

        // Tambahan untuk RS
        $rsKecamatanOptions        = $this->depokKecamatanOptions();
        $rsKelurahanOptions        = $this->depokKelurahanOptions();
        $rsKelurahanByKecamatan    = $this->depokKelurahanByKecamatan();

        return view('dinkes.data-master.data-master-create', [
            'tab'                     => $tab,
            'puskesmasList'           => $puskesmasList,
            'kecamatanOptions'        => $kecamatanOptions,
            'rsKecamatanOptions'      => $rsKecamatanOptions,
            'rsKelurahanOptions'      => $rsKelurahanOptions,
            'rsKelurahanByKecamatan'  => $rsKelurahanByKecamatan,
        ]);
    }


    /** ===== B. DETAIL (inner join saja) ===== */
    public function show(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        if ($tab === 'rs') {
            $data = DB::table('users')
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select('users.*', 'rumah_sakits.nama', 'rumah_sakits.kecamatan', 'rumah_sakits.kelurahan', 'rumah_sakits.lokasi')
                ->first();
        } elseif ($tab === 'puskesmas') {
            $data = DB::table('users')
                ->join('puskesmas', 'puskesmas.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select('users.*', 'puskesmas.nama_puskesmas', 'puskesmas.kecamatan', 'puskesmas.lokasi')
                ->first();
        } else {
            $data = DB::table('users')
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->join('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
                ->where('users.id', $user)
                ->select('users.*', 'bidans.nomor_izin_praktek', 'puskesmas.nama_puskesmas')
                ->first();
        }

        abort_unless($data, 404);

        return view('dinkes.data-master.data-master-show', compact('tab', 'data'));
    }

    /** ===== C. EDIT (inner join, tanpa isNewDetail) ===== */
    public function edit(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        $puskesmasList = DB::table('puskesmas')
            ->select('id', 'nama_puskesmas')
            ->orderBy('nama_puskesmas')
            ->get();

        $kelurahanOptions = [];   // default
        $rsKelurahanByKecamatan = $this->depokKelurahanByKecamatan();


        if ($tab === 'rs') {
            $data = DB::table('users')
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select('users.*', 'rumah_sakits.nama', 'rumah_sakits.kecamatan', 'rumah_sakits.kelurahan', 'rumah_sakits.lokasi')
                ->first();

            $kecamatanOptions = $this->depokKecamatanOptions();
            $kelurahanOptions = $this->depokKelurahanOptions();
        } elseif ($tab === 'puskesmas') {
            $data = DB::table('users')
                ->join('puskesmas', 'puskesmas.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select(
                    'users.*',
                    'puskesmas.nama_puskesmas as nama',
                    'puskesmas.kecamatan',
                    'puskesmas.lokasi'
                )
                ->first();

            $kecamatanOptions = $data
                ? $this->availableKecamatanForEdit($data->kecamatan)
                : $this->depokKecamatanOptions();
        } else {
            $data = DB::table('users')
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select('users.*', 'bidans.nomor_izin_praktek', 'bidans.puskesmas_id', 'users.address')
                ->first();

            $kecamatanOptions = $this->depokKecamatanOptions();
        }

        abort_unless($data, 404);

        return view('dinkes.data-master.data-master-edit', [
            'tab'              => $tab,
            'data'             => $data,
            'puskesmasList'    => $puskesmasList,
            'kecamatanOptions' => $kecamatanOptions,
            'kelurahanOptions' => $kelurahanOptions,
            'rsKelurahanByKecamatan'  => $rsKelurahanByKecamatan,

        ]);
    }


    /** ===== D. UPDATE (tanpa upsert) ===== */
    public function update(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        if ($tab === 'rs') {
            $kecamatanKeys = array_keys($this->depokKecamatanOptions());
            $kelurahanKeys = array_keys($this->depokKelurahanOptions());

            $payload = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email,' . $user,
                'phone'     => 'nullable|string|max:50',
                'lokasi'    => 'nullable|string',
                'kecamatan' => 'required|string|in:' . implode(',', $kecamatanKeys),
                'kelurahan' => 'required|string|in:' . implode(',', $kelurahanKeys),
                'nama'      => 'required|string|max:255',
            ]);

            DB::transaction(function () use ($user, $payload) {
                DB::table('users')->where('id', $user)->update([
                    'name'       => $payload['name'],
                    'email'      => $payload['email'],
                    'phone'      => $payload['phone'] ?? null,
                    'address'    => $payload['lokasi'] ?? null,
                    'updated_at' => now(),
                ]);

                DB::table('rumah_sakits')->where('user_id', $user)->update([
                    'nama'       => $payload['nama'],
                    'lokasi'     => $payload['lokasi'] ?? '',
                    'kecamatan'  => $payload['kecamatan'],
                    'kelurahan'  => $payload['kelurahan'],
                    'updated_at' => now(),
                ]);
            });
        } elseif ($tab === 'puskesmas') {
            $kecamatanKeys = array_keys($this->depokKecamatanOptions());

            $payload = $request->validate([
                'name'   => 'required|string|max:255',
                'email'  => 'required|email|unique:users,email,' . $user,
                'phone'  => 'nullable|string|max:50',
                'lokasi' => 'nullable|string',

                'nama'   => 'required|string|in:' . implode(',', $kecamatanKeys),
            ]);

            DB::transaction(function () use ($user, $payload) {

                DB::table('users')->where('id', $user)->update([
                    'name'       => $payload['name'],
                    'email'      => $payload['email'],
                    'phone'      => $payload['phone'] ?? null,
                    'address'    => $payload['lokasi'] ?? null,
                    'updated_at' => now(),
                ]);

                DB::table('puskesmas')->where('user_id', $user)->update([
                    'nama_puskesmas' => $payload['nama'],
                    'lokasi'         => $payload['lokasi'] ?? '',
                    'kecamatan'      => $payload['nama'],
                    'is_mandiri'     => 0,
                    'updated_at'     => now(),
                ]);
            });
        } else { // bidan
            $payload = $request->validate([
                'name'               => 'required|string|max:255',
                'email'              => 'required|email|unique:users,email,' . $user,
                'phone'              => 'nullable|string|max:50',
                'address'            => 'nullable|string',
                'nomor_izin_praktek' => 'required|string|max:255',
                'puskesmas_id'       => 'required|exists:puskesmas,id',
            ]);

            DB::transaction(function () use ($user, $payload) {
                DB::table('users')->where('id', $user)->update([
                    'name'       => $payload['name'],
                    'email'      => $payload['email'],
                    'phone'      => $payload['phone'] ?? null,
                    'address'    => $payload['address'] ?? null,
                    'updated_at' => now(),
                ]);

                DB::table('bidans')->where('user_id', $user)->update([
                    'nomor_izin_praktek' => $payload['nomor_izin_praktek'],
                    'puskesmas_id'       => $payload['puskesmas_id'],
                    'updated_at'         => now(),
                ]);
            });
        }

        return redirect()->route('dinkes.data-master', [
            'tab' => $tab,
            'q'   => $request->query('q'),
        ])->with('ok', 'Data berhasil disimpan.');
    }

    public function destroy(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        try {
            DB::transaction(function () use ($tab, $user) {
                if ($tab === 'rs') {
                    DB::table('rumah_sakits')->where('user_id', $user)->delete();
                } elseif ($tab === 'puskesmas') {
                    $puskesmasId = DB::table('puskesmas')->where('user_id', $user)->value('id');

                    if ($puskesmasId) {
                        DB::table('bidans')
                            ->where('puskesmas_id', $puskesmasId)
                            ->update([
                                'puskesmas_id' => null,
                                'updated_at'   => now(),
                            ]);

                        DB::table('puskesmas')->where('id', $puskesmasId)->delete();
                    }
                } else {
                    DB::table('bidans')->where('user_id', $user)->delete();
                }

                DB::table('users')->where('id', $user)->delete();
            });

            return redirect()
                ->route('dinkes.data-master', [
                    'tab' => $tab,
                    'q'   => $request->query('q'),
                ])
                ->with('ok', 'Akun dan detail berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('dinkes.data-master', [
                    'tab' => $tab,
                    'q'   => request()->query('q'),
                ])
                ->with('err', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
