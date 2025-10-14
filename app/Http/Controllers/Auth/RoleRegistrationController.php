<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleRegistrationController extends Controller
{
    private function roleId(string $name): int
    {
        return (int) DB::table('roles')
            ->whereRaw('LOWER(nama_role)=?', [strtolower($name)])
            ->value('id');
    }

    /** PUSKESMAS */
    public function storePuskesmas(Request $r)
    {
        $data = $r->validate([
            'pic_name'   => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:6',
            'phone'      => 'nullable|string|max:50',
            'nama'       => 'required|string|max:255',         // nama puskesmas
            'kecamatan'  => 'required|string|max:255',
            'lokasi'     => 'nullable|string',                  // alamat
            'is_mandiri' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data) {
            // 1) user pending
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['pic_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['lokasi'] ?? null,
                'role_id'    => $this->roleId('puskesmas'),
                'status'     => 0, // pending
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2) detail puskesmas melekat ke user (sudah lengkap dari form)
            DB::table('puskesmas')->insert([
                'user_id'        => $userId,
                'nama_puskesmas' => $data['nama'],
                'kecamatan'      => $data['kecamatan'],
                'lokasi'         => $data['lokasi'] ?? '',
                'is_mandiri'     => !empty($data['is_mandiri']) ? 1 : 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('ok', 'Pengajuan akun Puskesmas terkirim. Menunggu persetujuan DINKES.');
    }

    /** RUMAH SAKIT */
    public function storeRs(Request $r)
    {
        $data = $r->validate([
            'pic_name'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:50',
            'nama'      => 'required|string|max:255',   // nama RS
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'lokasi'    => 'nullable|string',           // alamat
        ]);

        DB::transaction(function () use ($data) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['pic_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['lokasi'] ?? null,
                'role_id'    => $this->roleId('rs'),
                'status'     => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('rumah_sakits')->insert([
                'user_id'    => $userId,
                'nama'       => $data['nama'],
                'kecamatan'  => $data['kecamatan'],
                'kelurahan'  => $data['kelurahan'],
                'lokasi'     => $data['lokasi'] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('ok', 'Pengajuan akun Rumah Sakit terkirim. Menunggu persetujuan DINKES.');
    }

    /** BIDAN (mandiri/PKM – formmu sudah memuat puskesmas/wilayah kerja) */
    public function storeBidan(Request $r)
    {
        $puskesmasList = DB::table('puskesmas')->select('id', 'nama_puskesmas')->orderBy('nama_puskesmas')->get();

        $data = $r->validate([
            'pic_name'           => 'required|string|max:255',   // Nama lengkap PIC
            'email'              => 'required|email|max:255|unique:users,email',
            'password'           => 'required|string|min:6',
            'phone'              => 'nullable|string|max:50',
            'lokasi'             => 'nullable|string',           // alamat/klinik
            'nomor_izin_praktek' => 'required|string|max:255',
            'puskesmas_id'       => 'required|exists:puskesmas,id', // dropdown wilayah kerja
        ]);

        DB::transaction(function () use ($data) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['pic_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['lokasi'] ?? null,
                'role_id'    => $this->roleId('bidan'),
                'status'     => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('bidans')->insert([
                'user_id'            => $userId,
                'nomor_izin_praktek' => $data['nomor_izin_praktek'],
                'puskesmas_id'       => $data['puskesmas_id'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('ok', 'Pengajuan akun Bidan terkirim. Menunggu persetujuan DINKES.');
    }
}
