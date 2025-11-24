<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PasienNifasController extends Controller
{
    public function index()
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        $puskesmasId = $bidan->puskesmas_id;

        $pasienNifas = DB::table('pasien_nifas_bidan')
            ->join('pasiens', 'pasien_nifas_bidan.pasien_id', '=', 'pasiens.id')
            ->join('users', 'pasiens.user_id', '=', 'users.id')
            ->select(
                'pasien_nifas_bidan.id',
                'pasien_nifas_bidan.pasien_id',
                'pasien_nifas_bidan.tanggal_mulai_nifas as tanggal',
                'pasien_nifas_bidan.created_at',
                'pasiens.nik',
                'users.name as nama_pasien',
                'users.phone as telp',
                'pasiens.PKecamatan as alamat',
                'pasiens.PWilayah as kelurahan'
            )
            ->where('pasien_nifas_bidan.bidan_id', $puskesmasId)
            ->orderByDesc('pasien_nifas_bidan.tanggal_mulai_nifas')
            ->orderByDesc('pasien_nifas_bidan.created_at')
            ->paginate(10);

        $ids = $pasienNifas->getCollection()->pluck('id')->all();
        $kfDone = DB::table('kf')
            ->selectRaw('id_nifas, MAX(kunjungan_nifas_ke)::int as max_ke')
            ->whereIn('id_nifas', $ids)
            ->groupBy('id_nifas')
            ->get()
            ->keyBy('id_nifas');
        $dueDays = [1=>3,2=>7,3=>14,4=>42];
        $today = Carbon::today();
        $pasienNifas->getCollection()->transform(function ($row) use ($kfDone, $dueDays, $today) {
            $maxKe = optional($kfDone->get($row->id))->max_ke ?? 0;
            $nextKe = min(4, $maxKe + 1);
            $days = $row->tanggal ? Carbon::parse($row->tanggal)->diffInDays($today) : 0;
            $due = $dueDays[$nextKe] ?? 42;
            if ($row->tanggal === null) { $label = 'Aman'; $cls = 'bg-[#2EDB58] text-white'; }
            elseif ($days > $due) { $label = 'Telat'; $cls = 'bg-[#FF3B30] text-white'; }
            elseif ($days >= max(0, $due - 1)) { $label = 'Mepet'; $cls = 'bg-[#FFC400] text-[#1D1D1D]'; }
            else { $label = 'Aman'; $cls = 'bg-[#2EDB58] text-white'; }
            $row->peringat_label = $label;
            $row->badge_class = $cls;
            $row->next_ke = $nextKe;
            return $row;
        });

        $totalPasienNifas = DB::table('pasien_nifas_bidan')
            ->where('bidan_id', $puskesmasId)
            ->count();

        $sudahKFI = 0;
        $belumKFI = $totalPasienNifas - $sudahKFI;

        return view('bidan.pasien-nifas.index', compact(
            'pasienNifas',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
        ));
    }
}