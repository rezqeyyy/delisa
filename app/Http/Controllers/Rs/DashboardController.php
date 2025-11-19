<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\PasienNifas;
use App\Models\PasienPreEklampsia;
use App\Models\RumahSakit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Data Daerah Asal Pasien (berdasarkan PKabupaten)
        $pasienDepok = Pasien::where('PKabupaten', 'LIKE', '%Depok%')->count();
        $pasienNonDepok = Pasien::where('PKabupaten', 'NOT LIKE', '%Depok%')
            ->orWhereNull('PKabupaten')
            ->count();

        // Data Resiko Eklampsia (sesuaikan dengan kolom yang ada)
        $pasienNormal = 0;
        $pasienBeresikoEklampsia = 0;

        // Data Pasien Hadir (sesuaikan dengan kolom status_perkawinan atau status lain)
        $pasienHadir = 0;
        $pasienTidakHadir = 0;

        // Data Pasien Nifas
        $totalPasienNifas = PasienNifas::count();
        $sudahKF1 = 0; // Sesuaikan dengan kolom yang ada

        // Data Pemantauan (sesuaikan dengan kolom yang ada)
        $pemantauanSehat = 0;
        $pemantauanDirujuk = 0;
        $pemantauanMeninggal = 0;

        // Data Pasien Pre Eklampsia (5 terbaru) - dari tabel pasiens
        $pasienPreEklampsia = Pasien::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_pasien' => $item->nik ?? $item->id,
                    'nama' => $item->user->name ?? 'Nama Tidak Tersedia',
                    'tanggal' => $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->format('d/m/Y') : '-',
                    'status' => $item->PKabupaten ?? 'N/A',
                    'no_telp' => $item->no_telepon ?? '0000000000',
                    'klasifikasi' => 'Beresiko'
                ];
            });

        return view('rs.dashboard', compact(
            'pasienDepok',
            'pasienNonDepok',
            'pasienNormal',
            'pasienBeresikoEklampsia',
            'pasienHadir',
            'pasienTidakHadir',
            'totalPasienNifas',
            'sudahKF1',
            'pemantauanSehat',
            'pemantauanDirujuk',
            'pemantauanMeninggal',
            'pasienPreEklampsia'
        ));
    }

    /**
     * Show detail pasien
     */
    public function showPasien($id)
    {
        try {
            $pasien = Pasien::with('user')->findOrFail($id);

            return view('rs.show', compact('pasien'));
        } catch (\Exception $e) {
            return redirect()
                ->route('rs.dashboard')
                ->with('error', 'Data pasien tidak ditemukan');
        }
    }

    /**
     * ✨ FITUR BARU: Proses pasien ke data nifas
     */
    public function prosesPasienNifas(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Cari data pasien
            $pasien = Pasien::findOrFail($id);

            // Get RS ID dari user yang login
            $rs_id = $this->getRsId();

            // Cek apakah pasien sudah ada di data nifas
            $existingNifas = PasienNifas::where('pasien_id', $pasien->id)
                ->where('rs_id', $rs_id)
                ->first();

            if ($existingNifas) {
                DB::commit();

                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar dalam data nifas.');
            }

            // Buat data pasien nifas baru
            $pasienNifas = PasienNifas::create([
                'rs_id' => $rs_id,
                'pasien_id' => $pasien->id,
                'tanggal_mulai_nifas' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('rs.pasien-nifas.show', $pasienNifas->id)
                ->with('success', 'Pasien berhasil diproses ke data nifas! Silakan tambah data anak.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error Proses Pasien Nifas: ' . $e->getMessage());

            return redirect()
                ->route('rs.dashboard')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Ambil ID Rumah Sakit milik user yang sedang login.
     */
    private function getRsId()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            throw new \RuntimeException('User belum login.');
        }

        // Cari rumah sakit yang terhubung dengan user ini
        $rs = RumahSakit::where('user_id', $user->id)->first();

        if (!$rs) {
            // Bisa juga diubah jadi abort(403) atau redirect,
            // tapi untuk sekarang biar ketahuan jelas di flash message
            throw new \RuntimeException('Rumah sakit untuk akun ini belum terdaftar.');
        }

        return $rs->id;
    }
}
