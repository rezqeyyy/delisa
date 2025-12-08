<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\RujukanRs;
use App\Models\ResepObat;

class RujukanController extends Controller
{
    public function index()
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // Rujukan MASUK ke RS ini, belum diterima/ditolak
        $skrinings = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->where('done_status', false) // hanya yang belum diterima
            ->orderByDesc('created_at')
            ->paginate(10);

        $skrinings->getCollection()->transform(function ($rujukan) {
            $skr = $rujukan->skrining;
            $pas = optional($skr)->pasien;
            $usr = optional($pas)->user;

            if (!$skr) {
                $rujukan->nik        = '-';
                $rujukan->nama       = 'Data Skrining Tidak Tersedia';
                $rujukan->tanggal    = '-';
                $rujukan->alamat     = '-';
                $rujukan->telp       = '-';
                $rujukan->kesimpulan = '-';
                $rujukan->detail_url = '#';
                return $rujukan;
            }

            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));
            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0
                || in_array($raw, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);
            $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0
                || in_array($raw, ['waspada', 'menengah', 'sedang', 'risiko sedang']);

            $rujukan->nik        = $pas->nik ?? '-';
            $rujukan->nama       = $usr->name ?? 'Nama Tidak Tersedia';
            $rujukan->tanggal    = optional($skr->created_at)->format('d/m/Y');
            $rujukan->alamat     = $pas->PKecamatan ?? $pas->PWilayah ?? '-';
            $rujukan->telp       = $usr->phone ?? $pas->no_telepon ?? '-';
            $rujukan->kesimpulan = $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Tidak Berisiko');
            $rujukan->detail_url = route('rs.skrining.show', $skr->id ?? 0);

            return $rujukan;
        });

        return view('rs.rujukan.index', compact('skrinings'));
    }

    public function accept($id)
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // $id di sini adalah skrining_id
        $rujukan = RujukanRs::where('skrining_id', $id)
            ->where('rs_id', $rsId)
            ->where('done_status', false)
            ->firstOrFail();

        // Terima rujukan:
        // - tandai sudah diterima (done_status = true)
        // - reset SEMUA field lanjutan ke NULL
        $rujukan->update([
            'is_rujuk'    => true,
            'done_status' => true,
            'catatan_rujukan' => null,
        ]);

        return redirect()
            ->route('rs.penerimaan-rujukan.index')
            ->with('success', 'Rujukan berhasil diterima. Pasien masuk ke daftar rujukan pre eklampsia di dashboard.');
    }


    public function reject($id)
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;

        $rujukan = RujukanRs::where('skrining_id', $id)
            ->where('rs_id', $rsId)
            ->where('done_status', false)
            ->firstOrFail();

        // JANGAN HAPUS! UPDATE STATUS SAJA
        $rujukan->update([
            'done_status' => true, // true = sudah diproses (ditolak/disetujui)
            'is_rujuk' => false,   // false = tidak dirujuk (ditolak)
            // 'catatan_rujukan' => request('alasan_penolakan', 'Rujukan ditolak oleh RS'),
            'updated_at' => now()
        ]);

        // Opsional: Hapus resep obat jika ada (tetap bisa dihapus)
        ResepObat::where('rujukan_rs_id', $rujukan->id)->delete();

        return redirect()
            ->route('rs.penerimaan-rujukan.index')
            ->with('success', 'Rujukan telah ditolak.');
    }
}