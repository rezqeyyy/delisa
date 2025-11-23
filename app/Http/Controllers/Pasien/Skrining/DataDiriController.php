<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Skrining;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class DataDiriController extends Controller
{
    /* {{-- ========== DATA DIRI — CREATE ========== --}} */

    // - Jika puskesmas valid, membuat record skrining baru untuk pasien (step_form=1).
    // - Mengarahkan ke form data diri untuk melengkapi profil dan alamat.
    public function create(Request $request)
    {
        $puskesmasId = (int) $request->query('puskesmas_id');
        $bidanId = (int) $request->query('bidan_id');
        if (!$puskesmasId && $bidanId) {
            $puskesmasId = (int) DB::table('bidans')->where('id', $bidanId)->value('puskesmas_id');
        }
        $user        = Auth::user();
        $pasienId    = optional($user->pasien)->id;

        // Validasi ringan: hanya membuat skrining jika puskesmas_id valid dan pasien terautentikasi
        if ($puskesmasId && $pasienId && \App\Models\Puskesmas::whereKey($puskesmasId)->exists()) {
            // Cegah duplikasi: hanya buat skrining baru jika tidak ada skrining aktif/incomplete
            $latest = Skrining::where('pasien_id', $pasienId)->latest()->first();
            if (!$latest || $this->isSkriningCompleteForSkrining($latest)) {
                Skrining::create([
                    'pasien_id'            => $pasienId,
                    'puskesmas_id'         => $puskesmasId,
                    'status_pre_eklampsia' => null,
                    'jumlah_resiko_sedang' => null,
                    'jumlah_resiko_tinggi' => null,
                    'kesimpulan'           => null,
                    'step_form'            => 1,
                    'tindak_lanjut'        => false,
                    'checked_status'       => false,
                ]);
            }
        }

        return view('pasien.skrining.data-diri');
    }

    
    /* {{-- ========== DATA DIRI — STORE PENGAJUAN ========== --}} */
    
    // Endpoint pengajuan skrining:
    // - Validasi puskesmas_id lalu buat skrining baru.
    // - Redirect kembali ke form data diri dengan pesan sukses.
    public function storePengajuan(Request $request)
    {
        $payload = $request->validate([
            // Sumber nilai: form pengajuan. Wajib ada dan harus merupakan id puskesmas yang valid.
            'puskesmas_id' => ['required', 'integer', 'exists:puskesmas,id'],
        ]);

        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 403);

        // Cegah duplikasi: jika ada skrining belum selesai, jangan buat baru
        $latest = Skrining::where('pasien_id', $pasienId)->latest()->first();
        if ($latest && !$this->isSkriningCompleteForSkrining($latest)) {
            return redirect()
                ->route('pasien.data-diri', ['puskesmas_id' => $payload['puskesmas_id']])
                ->with('ok', 'Ada skrining yang belum selesai. Silakan lanjutkan skrining tersebut.');
        }

        Skrining::create([
            'pasien_id'            => $pasienId,
            'puskesmas_id'         => $payload['puskesmas_id'],
            'status_pre_eklampsia' => null,
            'jumlah_resiko_sedang' => null,
            'jumlah_resiko_tinggi' => null,
            'kesimpulan'           => null,
            'step_form'            => 1,
            'tindak_lanjut'        => false,
            'checked_status'       => false,
        ]);

        return redirect()
            ->route('pasien.data-diri', ['puskesmas_id' => $payload['puskesmas_id']])
            ->with('ok', 'Pengajuan skrining dibuat. Silakan isi Data Diri.');
    }

    use SkriningHelpers;

    /* {{-- ========== DATA DIRI — STORE ========== --}} */
    
    // Penyimpanan Data Diri:
    // - Update kontak/alamat di tabel users.
    // - Update demografi di tabel pasiens.
    // - Rehitung status preeklampsia setelah profil diperbarui.
    // - Lanjut ke GPA (Langkah 2).

    // Catatan parameter (field yang sering membingungkan):
    //  - 'status_perkawinan' (in:0,1): 0=Belum Kawin, 1=Kawin.
    //  - 'PKecamatan', 'PKabupaten', 'PProvinsi', 'PWilayah': bagian alamat domisili (string).
    //  - 'golongan_darah' (in:A,B,AB,O): pilihan golongan darah standar.
    //  - 'pembiayaan_kesehatan' + 'no_jkn':
    //     Jika pembiayaan 'BPJS Kesehatan', maka 'no_jkn' wajib (required_if).
    //     Jika bukan BPJS, 'no_jkn' akan di-set null meskipun dikirim.
    //  - 'skrining_id' (hidden input opsional): id skrining yang sedang dilanjutkan.
    //     Jika kosong, helper akan mengambil skrining terbaru milik pasien.
    public function store(Request $request)
    {
        $data = $request->validate([
            'tempat_lahir'         => ['required', 'string', 'max:150'],
            'tanggal_lahir'        => ['required', 'date'],
            'phone'                => ['required', 'string', 'max:30'],
            'address'              => ['required', 'string', 'max:255'],
            'status_perkawinan'    => ['required', 'in:0,1'],
            'PKecamatan'           => ['required', 'string', 'max:150'],
            'PKabupaten'           => ['required', 'string', 'max:150'],
            'PProvinsi'            => ['required', 'string', 'max:150'],
            'PWilayah'             => ['required', 'string', 'max:150'],
            'rt'                   => ['required', 'string'],
            'rw'                   => ['required', 'string'],
            'kode_pos'             => ['required', 'string', 'max:10'],
            'pekerjaan'            => ['required', 'string', 'max:150'],
            'pendidikan'           => ['required', 'string', 'max:150'],
            'pembiayaan_kesehatan' => ['required', 'string', 'max:100'],
            'golongan_darah'       => ['required', 'string', 'in:A,B,AB,O'],
            'no_jkn'               => ['nullable', 'string', 'max:30', 'required_if:pembiayaan_kesehatan,BPJS Kesehatan'],
        ]);

        $user   = Auth::user();
        $pasien = $user->pasien;

        DB::transaction(function () use ($user, $pasien, $data) {
            abort_unless($user && $pasien, 401);

            // Simpan kontak dan alamat di tabel users
            \App\Models\User::query()
                ->whereKey($user->id)
                ->update([
                    'phone'   => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);

            // Simpan detail demografi di tabel pasiens
            $pasien->update([
                'tempat_lahir'         => $data['tempat_lahir'] ?? null,
                'tanggal_lahir'        => $data['tanggal_lahir'] ?? null,
                'status_perkawinan'    => isset($data['status_perkawinan']) ? (int) $data['status_perkawinan'] : null,
                'PKecamatan'           => $data['PKecamatan'] ?? null,
                'PKabupaten'           => $data['PKabupaten'] ?? null,
                'PProvinsi'            => $data['PProvinsi'] ?? null,
                'PWilayah'             => $data['PWilayah'] ?? null,
                'kode_pos'             => $data['kode_pos'] ?? null,
                'rt'                   => $data['rt'] ?? null,
                'rw'                   => $data['rw'] ?? null,
                'pekerjaan'            => $data['pekerjaan'] ?? null,
                'pendidikan'           => $data['pendidikan'] ?? null,
                'pembiayaan_kesehatan' => $data['pembiayaan_kesehatan'] ?? null,
                'golongan_darah'       => $data['golongan_darah'] ?? null,
                'no_jkn'               => ($data['pembiayaan_kesehatan'] ?? null) === 'BPJS Kesehatan'
                                          ? ($data['no_jkn'] ?? null)
                                          : null,
            ]);
        });

        // 'skrining_id' diambil dari input tersembunyi pada form (opsional).
        // Tujuan: melanjutkan skrining yang sama agar tidak membuat skrining baru.
        $skriningId = (int) $request->input('skrining_id');
        $skrining = $this->requireSkriningForPasien($skriningId);

        // Hitung ulang risiko setelah data diri diperbarui
        $this->recalcPreEklampsia($skrining);

        return redirect()
            // Kirim 'skrining_id' ke halaman GPA agar proses tetap pada skrining yang sama
            ->route('pasien.riwayat-kehamilan-gpa', ['skrining_id' => $skriningId ?: null])
            ->with('ok', 'Data diri berhasil disimpan.');
    }
    
}