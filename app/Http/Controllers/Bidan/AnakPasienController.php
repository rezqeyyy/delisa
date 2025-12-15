<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PasienNifasBidan;
use App\Models\AnakPasien;

/*
|--------------------------------------------------------------------------
| ANAK PASIEN CONTROLLER
|--------------------------------------------------------------------------
| Fungsi: Mengelola data anak pasien nifas (penambahan data anak)
| Fitur: Form create anak, simpan anak
|--------------------------------------------------------------------------
*/
class AnakPasienController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | METHOD: create($id)
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan form input data anak untuk pasien nifas
    | Parameter: $id (ID pada tabel `pasien_nifas_bidan`)
    | Return: View 'bidan.pasien-nifas.anak-create'
    |--------------------------------------------------------------------------
    */
    public function create($id)
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) abort(403);
        $row = PasienNifasBidan::find($id);
        if (!$row) {
            $rs = \App\Models\PasienNifasRs::find($id);
            if ($rs) {
                $row = PasienNifasBidan::where('pasien_id', $rs->pasien_id)->orderByDesc('created_at')->first();
            }
        }
        if (!$row) abort(404);
        return view('bidan.pasien-nifas.anak-create', ['nifasId' => $row->id, 'rowId' => $row->id, 'anak' => null]);
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: store(Request $request, $id)
    |--------------------------------------------------------------------------
    | Fungsi: Validasi dan menyimpan data anak baru untuk pasien nifas
    | Parameter: $request, $id (ID pada tabel `pasien_nifas_bidan`)
    | Return: Redirect dengan pesan sukses/error
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, $id)
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) abort(403);

        $row = PasienNifasBidan::findOrFail($id);

        $data = $request->validate([
            'nifas_id' => 'required|integer',
            'anak_ke' => 'required|integer|min:1',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nama_anak' => 'nullable|string|max:255',
            'usia_kehamilan_saat_lahir' => 'nullable|integer|min:20|max:45',
            'berat_lahir_anak' => 'nullable|integer',
            'panjang_lahir_anak' => 'nullable|numeric',
            'lingkar_kepala_anak' => 'nullable|numeric',
            'memiliki_buku_kia' => 'nullable|in:0,1',
            'buku_kia_bayi_kecil' => 'nullable|in:0,1',
            'imd' => 'nullable|in:0,1',
            'riwayat_penyakit' => 'nullable|string',
            'keterangan_masalah_lain' => 'nullable|string',
            'kondisi_ibu' => 'nullable|in:aman,perlu_tindak_lanjut',
            'catatan_kondisi_ibu' => 'nullable|string',
        ]);

        if ((int) $data['nifas_id'] !== (int) $row->id) {
            abort(403);
        }

        $riw = [];
        if (!empty($data['riwayat_penyakit'])) {
            $riw = array_values(array_filter(array_map(function ($v) {
                return trim($v);
            }, explode(',', $data['riwayat_penyakit']))));
        }

        $payload = [
            'nifas_bidan_id' => $row->id,
            'anak_ke' => $data['anak_ke'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'nama_anak' => $data['nama_anak'] ?? null,
            'usia_kehamilan_saat_lahir' => $data['usia_kehamilan_saat_lahir'] ?? null,
            'berat_lahir_anak' => $data['berat_lahir_anak'] ?? null,
            'panjang_lahir_anak' => $data['panjang_lahir_anak'] ?? null,
            'lingkar_kepala_anak' => $data['lingkar_kepala_anak'] ?? null,
            'memiliki_buku_kia' => isset($data['memiliki_buku_kia']) ? (int) $data['memiliki_buku_kia'] : null,
            'buku_kia_bayi_kecil' => isset($data['buku_kia_bayi_kecil']) ? (int) $data['buku_kia_bayi_kecil'] : null,
            'imd' => isset($data['imd']) ? (int) $data['imd'] : null,
            'riwayat_penyakit' => $riw,
            'keterangan_masalah_lain' => $data['keterangan_masalah_lain'] ?? null,
            'kondisi_ibu' => $data['kondisi_ibu'] ?? null,
            'catatan_kondisi_ibu' => $data['catatan_kondisi_ibu'] ?? null,
        ];

        try {
            AnakPasien::create($payload);
            return redirect()->route('bidan.pasien-nifas')->with('success', 'Data anak berhasil disimpan');
        } catch (\Throwable $e) {
            Log::error('Bidan Store Anak Pasien: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($id, $anakId)
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) abort(403);
        $row = PasienNifasBidan::findOrFail($id);
        $anak = AnakPasien::where('id', $anakId)->where('nifas_bidan_id', $row->id)->firstOrFail();
        return view('bidan.pasien-nifas.anak-create', ['nifasId' => $row->id, 'rowId' => $row->id, 'anak' => $anak]);
    }

    public function update(Request $request, $id, $anakId)
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) abort(403);
        $row = PasienNifasBidan::findOrFail($id);
        $anak = AnakPasien::where('id', $anakId)->where('nifas_bidan_id', $row->id)->firstOrFail();

        $data = $request->validate([
            'anak_ke' => 'required|integer|min:1',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nama_anak' => 'nullable|string|max:255',
            'usia_kehamilan_saat_lahir' => 'nullable|integer|min:20|max:45',
            'berat_lahir_anak' => 'nullable|integer',
            'panjang_lahir_anak' => 'nullable|numeric',
            'lingkar_kepala_anak' => 'nullable|numeric',
            'memiliki_buku_kia' => 'nullable|in:0,1',
            'buku_kia_bayi_kecil' => 'nullable|in:0,1',
            'imd' => 'nullable|in:0,1',
            'riwayat_penyakit' => 'nullable|string',
            'keterangan_masalah_lain' => 'nullable|string',
            'kondisi_ibu' => 'nullable|in:aman,perlu_tindak_lanjut',
            'catatan_kondisi_ibu' => 'nullable|string',
        ]);

        $riw = [];
        if (!empty($data['riwayat_penyakit'])) {
            $riw = array_values(array_filter(array_map(fn($v) => trim($v), explode(',', $data['riwayat_penyakit']))));
        }

        $anak->update([
            'anak_ke' => $data['anak_ke'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'nama_anak' => $data['nama_anak'] ?? null,
            'usia_kehamilan_saat_lahir' => $data['usia_kehamilan_saat_lahir'] ?? null,
            'berat_lahir_anak' => $data['berat_lahir_anak'] ?? null,
            'panjang_lahir_anak' => $data['panjang_lahir_anak'] ?? null,
            'lingkar_kepala_anak' => $data['lingkar_kepala_anak'] ?? null,
            'memiliki_buku_kia' => isset($data['memiliki_buku_kia']) ? (int) $data['memiliki_buku_kia'] : null,
            'buku_kia_bayi_kecil' => isset($data['buku_kia_bayi_kecil']) ? (int) $data['buku_kia_bayi_kecil'] : null,
            'imd' => isset($data['imd']) ? (int) $data['imd'] : null,
            'riwayat_penyakit' => $riw,
            'keterangan_masalah_lain' => $data['keterangan_masalah_lain'] ?? null,
            'kondisi_ibu' => $data['kondisi_ibu'] ?? null,
            'catatan_kondisi_ibu' => $data['catatan_kondisi_ibu'] ?? null,
        ]);

        return redirect()->route('bidan.pasien-nifas.detail', $id)->with('success', 'Data anak diperbarui');
    }

    public function destroy($id, $anakId)
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) abort(403);
        $row = PasienNifasBidan::findOrFail($id);
        $anak = AnakPasien::where('id', $anakId)->where('nifas_id', $row->id)->firstOrFail();
        $anak->delete();
        return redirect()->route('bidan.pasien-nifas.detail', $id)->with('success', 'Data anak dihapus');
    }
}
