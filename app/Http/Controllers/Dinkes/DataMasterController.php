<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // ⬅️ untuk reset password


class DataMasterController extends Controller
{
    /** Helper ambil role_id dari nama_role */
    private function roleId(string $name): int
    {
        return (int) DB::table('roles')->where('nama_role', $name)->value('id');
    }

    /** List Data Master */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'bidan');     // bidan | rs | puskesmas
        $q   = trim((string) $request->query('q', ''));

        // Query per tab
        if ($tab === 'rs') {
            $accounts = DB::table('users')
                ->join('rumah_sakits', 'rumah_sakits.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email')
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.id', 'asc')
                ->paginate(10)->withQueryString();
        } elseif ($tab === 'puskesmas') {
            $accounts = DB::table('puskesmas')
                ->join('users', 'users.id', '=', 'puskesmas.user_id')
                ->select('users.id', 'users.name', 'users.email', 'puskesmas.nama_puskesmas')
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%")
                            ->orWhere('puskesmas.nama_puskesmas', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.id', 'asc')
                ->paginate(10)->withQueryString();
        } else { // bidan
            $accounts = DB::table('users')
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email')
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('users.name', 'ilike', "%$q%")
                            ->orWhere('users.email', 'ilike', "%$q%");
                    });
                })
                ->orderBy('users.id', 'asc')
                ->paginate(10)->withQueryString();
        }

        // Untuk dropdown puskesmas saat tambah bidan
        $puskesmasList = DB::table('puskesmas')
            ->select('id', 'nama_puskesmas')->orderBy('nama_puskesmas')->get();

        return view('dinkes.data-master', [
            'tab' => $tab,
            'q'   => $q,
            'accounts' => $accounts,
            'puskesmasList' => $puskesmasList,
        ]);
    }

    /** Halaman create (form) */
    public function create(Request $request)
    {
        $tab = $request->query('tab', 'bidan');
        $puskesmasList = DB::table('puskesmas')->select('id', 'nama_puskesmas')->orderBy('nama_puskesmas')->get();

        return view('dinkes.data-master-create', [
            'tab' => $tab,
            'puskesmasList' => $puskesmasList,
        ]);
    }

    /** Simpan Rumah Sakit + akun user role rs */
    public function storeRs(Request $request)
    {
        $data = $request->validate([
            'pic_name'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:50',
            'nama'      => 'required|string|max:255',
            'lokasi'    => 'nullable|string',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['pic_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['lokasi'] ?? null,
                'role_id'    => $this->roleId('rs'),
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('rumah_sakits')->insert([
                'user_id'    => $userId,
                'nama'       => $data['nama'],
                'lokasi'     => $data['lokasi'] ?? '',
                'kecamatan'  => $data['kecamatan'],
                'kelurahan'  => $data['kelurahan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('dinkes.data-master', ['tab' => 'rs'])
            ->with('ok', 'Rumah Sakit berhasil ditambahkan.');
    }

    /** Simpan Puskesmas + akun user role puskesmas
     *  Catatan: tabel puskesmas tidak punya user_id di skema kamu.
     *  Jadi data akun (email/pass) tersimpan di users (role puskesmas),
     *  sedangkan identitas faskes di tabel puskesmas berdiri sendiri.
     */
    public function storePuskesmas(Request $request)
    {
        $data = $request->validate([
            'pic_name'   => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6',
            'phone'      => 'nullable|string|max:50',
            'nama'       => 'required|string|max:255',
            'lokasi'     => 'nullable|string',
            'kecamatan'  => 'required|string|max:255',
            'is_mandiri' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data) {
            // 1) buat akun user role puskesmas
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['pic_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['lokasi'] ?? null,
                'role_id'    => $this->roleId('puskesmas'),
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2) simpan identitas puskesmas + user_id
            DB::table('puskesmas')->insert([
                'user_id'        => $userId,
                'nama_puskesmas' => $data['nama'],
                'lokasi'         => $data['lokasi'] ?? '',
                'kecamatan'      => $data['kecamatan'],
                'is_mandiri'     => !empty($data['is_mandiri']) ? 1 : 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return redirect()
            ->route('dinkes.data-master', ['tab' => 'puskesmas'])
            ->with('ok', 'Puskesmas berhasil ditambahkan.');
    }


    /** Simpan Bidan + akun user role bidan, relasi ke bidans */
    public function storeBidan(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|string|min:6',
            'phone'              => 'nullable|string|max:50',
            'address'            => 'nullable|string',
            'nomor_izin_praktek' => 'required|string|max:255',
            'puskesmas_id'       => 'required|exists:puskesmas,id',
        ]);

        DB::transaction(function () use ($data) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['address'] ?? null,
                'role_id'    => $this->roleId('bidan'),
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('bidans')->insert([
                'user_id'           => $userId,
                'nomor_izin_praktek' => $data['nomor_izin_praktek'],
                'puskesmas_id'      => $data['puskesmas_id'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        });

        return redirect()
            ->route('dinkes.data-master', ['tab' => 'bidan'])
            ->with('ok', 'Akun Bidan berhasil ditambahkan.');
    }

    public function resetPassword(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        // generate password baru
        $newPass = Str::random(10);

        DB::table('users')->where('id', $user)->update([
            'password'   => Hash::make($newPass),
            'updated_at' => now(),
        ]);

        // Catatan: Di real-world tidak disarankan menampilkan password baru.
        // Demi demo, kita tampilkan via flash message.
        return redirect()
            ->route('dinkes.data-master', ['tab' => $tab, 'q' => $request->query('q')])
            ->with('ok', "Password baru: {$newPass}");
    }

    /** ---------------------------
     *  B. DETAIL
     *  ---------------------------*/
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
        } else { // bidan
            $data = DB::table('users')
                ->join('bidans', 'bidans.user_id', '=', 'users.id')
                ->join('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
                ->where('users.id', $user)
                ->select('users.*', 'bidans.nomor_izin_praktek', 'puskesmas.nama_puskesmas')
                ->first();
        }

        abort_unless($data, 404);

        return view('dinkes.data-master-show', [
            'tab'  => $tab,
            'data' => $data,
        ]);
    }

    /** ---------------------------
     *  C. EDIT
     *  ---------------------------*/
    public function edit(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        $puskesmasList = DB::table('puskesmas')->select('id', 'nama_puskesmas')->orderBy('nama_puskesmas')->get();

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

        return view('dinkes.data-master-edit', [
            'tab'            => $tab,
            'data'           => $data,
            'puskesmasList'  => $puskesmasList,
        ]);
    }

    /** ---------------------------
     *  D. UPDATE
     *  ---------------------------*/
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

        return redirect()
            ->route('dinkes.data-master', ['tab' => $tab, 'q' => $request->query('q')])
            ->with('ok', 'Data berhasil diupdate.');
    }

    /** ---------------------------
     *  E. DELETE
     *  ---------------------------*/
    public function destroy(Request $request, int $user)
    {
        $tab = $request->query('tab', 'bidan');

        DB::transaction(function () use ($tab, $user) {
            if ($tab === 'puskesmas') {
                // pastikan baris puskesmas ikut terhapus (jaga-jaga bila tidak ada FK cascade)
                DB::table('puskesmas')->where('user_id', $user)->delete();
            }
            DB::table('users')->where('id', $user)->delete();
        });

        return redirect()
            ->route('dinkes.data-master', ['tab' => $tab, 'q' => $request->query('q')])
            ->with('ok', 'Data berhasil dihapus.');
    }
}
