<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AkunBaruController extends Controller
{
    // List pengajuan akun = users.status = false
    public function index(Request $request)
    {
        $q = $request->query('q');

        $requests = User::with('role')
            ->where('status', false)
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('dinkes.akun-baru.akun-baru', compact('q', 'requests'));
    }

    // Simpan pengajuan (buat user status=false)
    public function store(Request $request)
    {
        // mapping value <select> -> isi kolom roles.nama_role
        $mapRole = [
            'bidan'        => 'bidan',
            'rumah_sakit'  => 'rs',
            'puskesmas'    => 'puskesmas',
        ];

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['required', 'in:' . implode(',', array_keys($mapRole))],
        ], [
            'email.unique' => 'E-mail sudah terdaftar.',
        ]);

        $roleName = $mapRole[$request->role];
        $role = Role::where('nama_role', $roleName)->firstOrCreate();

        // password sementara
        $tempPassword = method_exists(Str::class, 'password')
            ? Str::password(10)
            : Str::random(12);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($tempPassword),
            'status'   => false,        // pending
            'role_id'  => $role->id,
        ]);

        return redirect()
            ->route('dinkes.akun-baru')
            ->with('ok', "Pengajuan akun dibuat. Password sementara: {$tempPassword}");
    }

    // Terima pengajuan: aktifkan user (status=true) + bikin detail sesuai role
    public function approve($id)
    {
        // ambil user + role-nya
        $user = User::with('role')
            ->where('status', false)
            ->findOrFail($id);

        DB::transaction(function () use ($user) {
            // aktifkan user
            $user->status = true;
            $user->save();

            $roleName = optional($user->role)->nama_role; // 'bidan' | 'rs' | 'puskesmas'

            if ($roleName === 'puskesmas') {
                // JANGAN overwrite data yg sudah dibuat RoleRegistrationController
                $exists = DB::table('puskesmas')
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$exists) {
                    DB::table('puskesmas')->insert([
                        'user_id'        => $user->id,
                        'nama_puskesmas' => $user->name ?? 'Belum diisi',
                        'lokasi'         => 'Belum diisi',
                        'kecamatan'      => 'Belum diisi',
                        'is_mandiri'     => 0,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            } elseif ($roleName === 'rs') {
                // Sama: cek dulu apakah sudah ada detail rumah_sakits dari RoleRegistrationController
                $exists = DB::table('rumah_sakits')
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$exists) {
                    DB::table('rumah_sakits')->insert([
                        'user_id'    => $user->id,
                        'nama'       => $user->name ?? 'Belum diisi',
                        'lokasi'     => 'Belum diisi',
                        'kecamatan'  => 'Belum diisi',
                        'kelurahan'  => 'Belum diisi',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($roleName === 'bidan') {
                // Untuk bidan: detail sudah dibuat saat registrasi (storeBidan),
                // jadi di sini cukup aktifkan user saja.
            }
        });

        return back()->with('ok', "Pengajuan akun {$user->name} telah diterima (aktif).");
    }

    // Tolak pengajuan: hapus user pending
    public function reject($id)
    {
        // pastikan ambil juga role, supaya tahu detail mana yang harus dibersihkan
        $user = User::with('role')
            ->where('status', false)
            ->findOrFail($id);

        $name = $user->name;
        $role = optional($user->role)->nama_role; // 'bidan' | 'rs' | 'puskesmas'

        DB::transaction(function () use ($user, $role) {
            if ($role === 'bidan') {
                DB::table('bidans')->where('user_id', $user->id)->delete();
            } elseif ($role === 'rs') {
                DB::table('rumah_sakits')->where('user_id', $user->id)->delete();
            } elseif ($role === 'puskesmas') {
                DB::table('puskesmas')->where('user_id', $user->id)->delete();
            }
            // terakhir: hapus user-nya
            $user->delete();
        });

        return back()->with('ok', "Pengajuan akun {$name} telah ditolak & dihapus.");
    }
}
