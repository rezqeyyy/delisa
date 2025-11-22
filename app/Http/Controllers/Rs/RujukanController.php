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

        $skrinings = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)
            ->where('done_status', false)
            ->orderByDesc('created_at')
            ->paginate(10);

        $skrinings->getCollection()->transform(function ($rujukan) {
            $skr = $rujukan->skrining;
            $pas = optional($skr)->pasien;
            $usr = optional($pas)->user;
            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));
            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0 || in_array($raw, ['beresiko','berisiko','risiko tinggi','tinggi']);
            $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0 || in_array($raw, ['waspada','menengah','sedang','risiko sedang']);
            $rujukan->risk_display = $isHigh ? 'Beresiko' : ($isMed ? 'Waspada' : 'Aman');
            $rujukan->tanggal_display = optional($skr->created_at)->format('d/m/Y');
            $rujukan->nik_display = $pas->nik ?? '-';
            $rujukan->nama_display = $usr->name ?? '-';
            $rujukan->alamat_display = $pas->PKecamatan ?? $pas->PWilayah ?? '-';
            $rujukan->telp_display = $usr->phone ?? $pas->no_telepon ?? '-';
            return $rujukan;
        });

        return view('rs.rujukan.index', compact('skrinings'));
    }

    public function accept($id)
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;
        $rujukan = RujukanRs::where('skrining_id', $id)->where('rs_id', $rsId)->firstOrFail();
        $rujukan->done_status = true;
        $rujukan->save();

        return redirect()->route('rs.penerimaan-rujukan.index')->with('success', 'Rujukan diterima.');
    }

    public function reject($id)
    {
        $rsId = Auth::user()->rumahSakit->id ?? null;
        $rujukan = RujukanRs::where('skrining_id', $id)->where('rs_id', $rsId)->firstOrFail();
        ResepObat::where('rujukan_rs_id', $rujukan->id)->delete();
        $rujukan->delete();

        return redirect()->route('rs.penerimaan-rujukan.index')->with('success', 'Rujukan ditolak.');
    }
}