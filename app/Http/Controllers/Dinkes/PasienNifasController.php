<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasienNifasController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $rows = DB::table('pasiens as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.pasien_id', '=', 'p.id')
            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
            ->select(
                'p.id',
                'u.name',
                'p.nik',
                'p.tempat_lahir',
                'p.tanggal_lahir',
                DB::raw("CASE 
                            WHEN pnb.id IS NOT NULL THEN 'Bidan'
                            WHEN pnr.id IS NOT NULL THEN 'Rumah Sakit'
                            ELSE 'Puskesmas'
                         END as role_penanggung")
            )
            // ❗ hanya pasien yang benar-benar nifas (punya record di salah satu tabel nifas)
            ->where(function ($w) {
                $w->whereNotNull('pnb.id')
                  ->orWhereNotNull('pnr.id');
            })
            ->when($q !== '', function ($qr) use ($q) {
                // untuk Postgres -> ILIKE; jika MySQL ganti ke LIKE
                $qr->where(function ($w) use ($q) {
                    $w->where('u.name', 'ILIKE', "%{$q}%")
                      ->orWhere('p.nik', 'ILIKE', "%{$q}%");
                });
            })
            ->orderBy('u.name')
            ->paginate(10)
            ->withQueryString();

        return view('dinkes.pasien-nifas.pasien-nifas', [
            'rows' => $rows,
            'q'    => $q,
        ]);
    }

    public function destroy($pasienId)
    {
        DB::transaction(function () use ($pasienId) {
            // Hapus keanggotaan nifas di kedua sumber (bidan / rs) jika ada
            DB::table('pasien_nifas_bidan')->where('pasien_id', $pasienId)->delete();
            DB::table('pasien_nifas_rs')->where('pasien_id', $pasienId)->delete();

            // Opsional: juga bisa bersihkan data turunan nifas lain jika bergantung
            // (mis. rujukan_nifas/kf/anak_pasien) – abaikan bila tidak diperlukan.
        });

        return back()->with('success', 'Pasien dihapus dari daftar nifas.');
    }
}
