<?php

namespace App\Http\Controllers\Pasien\Skrining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Skrining;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class DataDiriController extends Controller
{
    public function create(Request $request)
    {
        $puskesmasId = (int) $request->query('puskesmas_id');
        $user        = Auth::user();
        $pasienId    = optional($user->pasien)->id;

        if ($puskesmasId && $pasienId && \App\Models\Puskesmas::whereKey($puskesmasId)->exists()) {
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

        return view('pasien.skrining.data-diri');
    }
    
    public function storePengajuan(Request $request)
    {
        $payload = $request->validate([
            'puskesmas_id' => ['required', 'integer', 'exists:puskesmas,id'],
        ]);

        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 403);

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

        $skriningId = (int) $request->input('skrining_id');
        $skrining = $this->requireSkriningForPasien($skriningId);

        // Pastikan hasil risiko ter-update setelah Data Diri
        $this->recalcPreEklampsia($skrining);

        return redirect()
            ->route('pasien.riwayat-kehamilan-gpa', ['skrining_id' => $skriningId ?: null])
            ->with('ok', 'Data diri berhasil disimpan.');
    }
    
}