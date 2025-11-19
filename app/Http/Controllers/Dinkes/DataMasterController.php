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

        $key       = strtolower($name);
        $candidates = $aliases[$key] ?? [$key];          // kalau tidak ada di alias, pakai nama sendiri

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
            ->select('id', 'nama_puskesmas')->orderBy('nama_puskesmas')->get();

        return view('dinkes.data-master.data-master', [
            'tab'           => $tab,
            'q'             => $q,
            'accounts'      => $accounts,
            'puskesmasList' => $puskesmasList,
        ]);
    }


    // app/Http/Controllers/Dinkes/DataMasterController.php

    public function storeRs(Request $request)
    {
        $payload = $request->validate([
            'pic_name'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'phone'     => 'nullable|string|max:50',
            'nama'      => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
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
        $payload = $request->validate([
            'pic_name'   => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8',
            'phone'      => 'nullable|string|max:50',
            'nama'       => 'required|string|max:255',
            'kecamatan'  => 'required|string|max:255',
            'lokasi'     => 'nullable|string',
            'is_mandiri' => 'nullable|boolean',
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
                'kecamatan'      => $payload['kecamatan'],
                'is_mandiri'     => !empty($payload['is_mandiri']) ? 1 : 0,
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
        $data = $request->validate([
            'new_password' => 'nullable|string|min:8',
        ]);

        // CASE 1: password DIISI MANUAL
        if (!empty($data['new_password'])) {
            $new = $data['new_password'];

            DB::table('users')->where('id', $user)->update([
                'password'   => Hash::make($new),
                'updated_at' => now(),
            ]);

            // ⚠️ TIDAK kirim password ke session (tidak disimpan di localStorage).
            // ✅ Kirim flag untuk MENGHAPUS password acak terakhir di browser.
            return back()->with([
                'ok'              => 'Password berhasil direset dengan password yang Anda tentukan.',
                'flash_kind'      => 'password-reset-manual',
                'pw_user_id_clear' => $user,   // ← ini penting
            ]);
        }

        // CASE 2: password DIKOSONGKAN → generate otomatis
        $new = Str::password(12); // password acak kuat

        DB::table('users')->where('id', $user)->update([
            'password'   => Hash::make($new),
            'updated_at' => now(),
        ]);

        // Kirim password & user id ke session → nanti disimpan di localStorage (browser)
        return back()->with([
            'ok'           => 'Password berhasil direset. Sistem membuat password acak baru.',
            'new_password' => $new,   // plaintext, sekali lewat ke browser
            'pw_user_id'   => $user,  // untuk key localStorage
            'flash_kind'   => 'password-reset-auto',
        ]);
    }





    /** =========================
     *  FORM CREATE (lama – tetap)
     *  =========================*/
    public function create(Request $request)
    {
        $tab = $request->query('tab', 'bidan');
        $puskesmasList = DB::table('puskesmas')
            ->join('users', 'users.id', '=', 'puskesmas.user_id')
            ->where('users.status', true) // hanya yang sudah di-approve Dinkes
            ->orderBy('puskesmas.nama_puskesmas')
            ->select('puskesmas.id', 'puskesmas.nama_puskesmas')
            ->get();
        return view('dinkes.data-master.data-master-create', [
            'tab' => $tab,
        ], compact('puskesmasList'));
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
                ->select('users.*', 'puskesmas.nama_puskesmas', 'puskesmas.kecamatan', 'puskesmas.is_mandiri', 'puskesmas.lokasi')
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
                ->select('users.*', 'puskesmas.nama_puskesmas as nama', 'puskesmas.kecamatan', 'puskesmas.is_mandiri', 'puskesmas.lokasi')
                ->first();
        } else {
            $data = DB::table('users')
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->where('users.id', $user)
                ->select('users.*', 'bidans.nomor_izin_praktek', 'bidans.puskesmas_id', 'users.address')
                ->first();
        }

        abort_unless($data, 404);

        return view('dinkes.data-master.data-master-edit', [
            'tab'           => $tab,
            'data'          => $data,
            'puskesmasList' => $puskesmasList,
        ]);
    }

    /** ===== D. UPDATE (tanpa upsert) ===== */
    public function update(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        if ($tab === 'rs') {
            $payload = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email,' . $user,
                'phone'     => 'nullable|string|max:50',
                'lokasi'    => 'nullable|string',
                'kecamatan' => 'required|string|max:255',
                'kelurahan' => 'required|string|max:255',
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
            $payload = $request->validate([
                'name'       => 'required|string|max:255',
                'email'      => 'required|email|unique:users,email,' . $user,
                'phone'      => 'nullable|string|max:50',
                'lokasi'     => 'nullable|string',
                'kecamatan'  => 'required|string|max:255',
                'nama'       => 'required|string|max:255',
                'is_mandiri' => 'nullable|boolean',
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
                    'kecamatan'      => $payload['kecamatan'],
                    'is_mandiri'     => !empty($payload['is_mandiri']) ? 1 : 0,
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

        return redirect()->route('dinkes.data-master', ['tab' => $tab, 'q' => $request->query('q')])
            ->with('ok', 'Data berhasil disimpan.');
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
                    'q'   => $request->query('q'), // <-- langsung dari $request
                ])
                ->with('ok', 'Akun dan detail berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('dinkes.data-master', [
                    'tab' => $tab,
                    'q'   => request()->query('q'), // <-- di sini juga, gunakan request() helper
                ])
                ->with('err', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
