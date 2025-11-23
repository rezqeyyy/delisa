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
            'pic_name' => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:50',

            // dropdown nama puskesmas (sekaligus kecamatan)
            // pastikan UNIQUE terhadap tabel puskesmas
            'nama'     => 'required|string|max:255|unique:puskesmas,nama_puskesmas',

            'lokasi'   => 'nullable|string', // alamat bebas isi
        ], [
            'nama.unique' => 'Puskesmas / Kecamatan tersebut sudah memiliki akun.',
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

            // 2) detail puskesmas melekat ke user
            // nama_puskesmas dan kecamatan disamakan sesuai requirement
            DB::table('puskesmas')->insert([
                'user_id'        => $userId,
                'nama_puskesmas' => $data['nama'],
                'kecamatan'      => $data['nama'],        // jadikan juga sebagai kecamatan
                'lokasi'         => $data['lokasi'] ?? '',
                'is_mandiri'     => 0,                    // centang mandiri dihilangkan ⇒ selalu 0
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('ok', 'Pengajuan akun Puskesmas terkirim. Menunggu persetujuan DINKES.');
    }

    public function showPuskesmasRegisterForm()
    {
        // 1) Master list Puskesmas Kecamatan Kota Depok
        // value array ini sengaja disamakan dengan yang akan disimpan ke nama_puskesmas
        $allPuskesmas = [
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


        // 2) Nama puskesmas yang sudah punya akun (tidak boleh daftar lagi)
        $alreadyTaken = DB::table('puskesmas')
            ->pluck('nama_puskesmas')
            ->all();

        // 3) Filter array: buang yang sudah ada di tabel puskesmas
        $available = array_diff_key(
            $allPuskesmas,
            array_flip($alreadyTaken) // convert list ke key agar cocok dengan key array master
        );

        return view('auth.register-puskesmas', [
            'kecamatanOptions' => $available,
        ]);
    }

    public function showRsRegisterForm()
    {
        return view('auth.register-rs', [
            'rsKecamatanOptions'     => $this->depokKecamatanOptions(),
            'rsKelurahanByKecamatan' => $this->depokKelurahanByKecamatan(),
        ]);
    }

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
            'Beji' => ['Beji', 'Beji Timur', 'Kemiri Muka', 'Kukusan', 'Pondok Cina', 'Tanah Baru'],
            'Bojongsari' => ['Bojongsari', 'Bojongsari Lama', 'Curug', 'Duren Mekar', 'Duren Seribu', 'Pondok Petir', 'Serua'],
            'Cilodong' => ['Cilodong', 'Jatimulya', 'Kalibaru', 'Kalimulya', 'Sukamaju', 'Sukamaju Baru'],
            'Cimanggis' => ['Cisalak', 'Cisalak Pasar', 'Curug', 'Harjamukti', 'Mekarsari', 'Pasir Gunung Selatan', 'Tugu'],
            'Cinere' => ['Cinere', 'Gandul', 'Pangkalan Jati', 'Pangkalan Jati Baru'],
            'Cipayung' => ['Cipayung', 'Cipayung Jaya', 'Cilangkap', 'Pondok Jaya', 'Ratu Jaya'],
            'Limo' => ['Grogol', 'Krukut', 'Limo', 'Meruyung'],
            'Pancoran Mas' => ['Depok', 'Depok Jaya', 'Depok Baru', 'Mampang', 'Pancoran Mas', 'Rangkapan Jaya', 'Rangkapan Jaya Baru'],
            'Sawangan' => ['Bedahan', 'Cinangka', 'Kedaung', 'Pasir Putih', 'Pengasinan', 'Sawangan', 'Sawangan Baru'],
            'Sukmajaya' => ['Abadijaya', 'Bakti Jaya', 'Cisalak', 'Mekarsari', 'Sukmajaya', 'Tirtajaya'],
            'Tapos' => ['Cimpaeun', 'Cilangkap', 'Jatijajar', 'Leuwinanggung', 'Sukatani', 'Sukamaju Baru', 'Tapos'],
        ];
    }

    private function depokKelurahanOptions(): array
    {
        $grouped = $this->depokKelurahanByKecamatan();
        $flat = [];

        foreach ($grouped as $kec => $list) {
            foreach ($list as $kel) {
                $flat[$kel] = $kel . " (Kec. $kec)";
            }
        }

        return $flat;
    }




    /** RUMAH SAKIT */
    public function storeRs(Request $r)
    {
        $kecamatanKeys = array_keys($this->depokKecamatanOptions());
        $kelurahanKeys = array_keys($this->depokKelurahanOptions());

        $data = $r->validate([
            'pic_name'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:50',
            'nama'      => 'required|string|max:255',
            'kecamatan' => 'required|string|in:' . implode(',', $kecamatanKeys),
            'kelurahan' => 'required|string|in:' . implode(',', $kelurahanKeys),
            'lokasi'    => 'nullable|string',
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


    /** BIDAN */
    public function storeBidan(Request $r)
    {
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

    /** PASIEN **/
    public function storePasien(Request $r)
    {
        $data = $r->validate([
            'nik'          => 'required|string|size:16|unique:pasiens,nik',
            'nama_lengkap' => 'required|string|min:3',
        ]);

        $roleId = $this->roleId('pasien');
        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'nama_role'  => 'pasien',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::transaction(function () use ($data, $roleId) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['nama_lengkap'],
                'email'      => null,
                'password'   => null,
                'role_id'    => $roleId,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pasiens')->insert([
                'user_id'    => $userId,
                'nik'        => $data['nik'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('pasien.login')
            ->with('ok', 'Registrasi pasien berhasil. Silakan login dengan NIK & Nama Lengkap.');
    }
}
