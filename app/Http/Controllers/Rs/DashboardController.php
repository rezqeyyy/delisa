<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\PasienNifasRs;
use App\Models\Skrining;
use App\Models\RumahSakit; // <-- TAMBAHAN
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

        // Data Resiko Eklampsia (sementara 0 dulu)
        $pasienNormal = 0;
        $pasienBeresikoEklampsia = 0;

        // Data Pasien Hadir (sementara 0 dulu)
        $pasienHadir = 0;
        $pasienTidakHadir = 0;

        // Data Pasien Nifas
        $totalPasienNifas = PasienNifasRs::count();
        $sudahKF1 = 0;

        // Data Pemantauan (sementara 0 dulu)
        $pemantauanSehat = 0;
        $pemantauanDirujuk = 0;
        $pemantauanMeninggal = 0;

        // Data Pasien Pre Eklampsia — skrining yang sudah ada kesimpulan/status
        $pePatients = Skrining::with(['pasien.user'])
            ->where(function ($q) {
                $q->whereNotNull('kesimpulan')
                  ->orWhereNotNull('status_pre_eklampsia');
            })
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($s) {
                $pasien = $s->pasien;
                $user   = optional($pasien)->user;
                $kes    = $s->kesimpulan ?? $s->status_pre_eklampsia ?? 'Normal';

                return (object) [
                    'id'          => $pasien->id ?? null,
                    'rujukan_id'  => $s->id,
                    'nik'         => $pasien->nik ?? '-',
                    'nama'        => $user->name ?? 'Nama Tidak Tersedia',
                    'tanggal'     => optional($s->created_at)->format('d/m/Y') ?? '-',
                    'alamat'      => $pasien->PKecamatan ?? $pasien->PWilayah ?? '-',
                    'telp'        => $user->phone ?? $pasien->no_telepon ?? '-',
                    'kesimpulan'  => ucfirst($kes),
                    'detail_url'  => route('rs.skrining.edit', $s->id),
                    'process_url' => $pasien && $pasien->id
                        ? route('rs.dashboard.proses-nifas', ['id' => $pasien->id])
                        : null,
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
            'pePatients'
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
            $existingNifas = PasienNifasRs::where('pasien_id', $pasien->id)
                ->where('rs_id', $rs_id)
                ->first();

            if ($existingNifas) {
                DB::commit();

                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar dalam data nifas.');
            }

            // Buat data pasien nifas baru
            $pasienNifas = PasienNifasRs::create([
                'rs_id'               => $rs_id,
                'pasien_id'           => $pasien->id,
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
     * Get RS ID from authenticated user
     */
    private function getRsId()
    {
        $user = Auth::user();

        if (!$user) {
            throw new \RuntimeException('User belum login.');
        }

        // 1) Kalau user punya kolom rumah_sakit_id → pakai itu
        if (!empty($user->rumah_sakit_id)) {
            return $user->rumah_sakit_id;
        }

        // 2) Atau kolom rs_id
        if (!empty($user->rs_id)) {
            return $user->rs_id;
        }

        // 3) Kalau di model User ada relasi rumahSakit(), coba pakai
        if (method_exists($user, 'rumahSakit')) {
            $rs = $user->rumahSakit()->first();
            if ($rs) {
                return $rs->id;
            }
        }

        // 4) Fallback: ambil RS pertama yang benar-benar ada di tabel rumah_sakits
        $rs = RumahSakit::query()->orderBy('id')->first();

        if (!$rs) {
            // Ini benar-benar darurat: tidak ada data rumah sakit sama sekali
            throw new \RuntimeException('Belum ada data rumah sakit di tabel rumah_sakits.');
        }

        return $rs->id;
    }
}
