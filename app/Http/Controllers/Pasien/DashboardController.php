<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = trim($request->get('status', ''));

        $pasienId = optional(Auth::user()->pasien)->id;

        $skrinings = Skrining::where('pasien_id', $pasienId)
            ->when($status !== '', function ($q) use ($status) {
                $q->where(function ($w) use ($status) {
                    $w->where('kesimpulan', $status)
                      ->orWhere('status_pre_eklampsia', $status);
                });
            })
            ->with(['pasien.user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $skrinings->getCollection()->transform(function ($s) {
            $resikoSedang = (int)($s->jumlah_resiko_sedang ?? 0);
            $resikoTinggi = (int)($s->jumlah_resiko_tinggi ?? 0);
            $conclusion = (($s->step_form ?? 0) < 6)
                ? 'Skrining belum selesai'
                : (($resikoTinggi === 0 && $resikoSedang <= 1) ? 'Tidak berisiko' : 'Berisiko');

            $key = strtolower(trim($conclusion));
    
            $badgeClasses = [
                'berisiko'               => 'bg-red-600 text-white',
                'beresiko'               => 'bg-red-600 text-white',    // dukung ejaan lama
                'tidak berisiko'         => 'bg-green-500 text-white',
                'tidak beresiko'         => 'bg-green-500 text-white',  // dukung ejaan lama
                'waspada'                => 'bg-yellow-400 text-black',
                'aman'                   => 'bg-green-500 text-white',
                'normal'                 => 'bg-green-500 text-white',
                'skrining belum selesai' => 'bg-gray-200 text-gray-900',
            ];

            $s->conclusion_display = $conclusion;
            $s->badge_class = $badgeClasses[$key] ?? 'bg-[#E9E9E9] text-[#1D1D1D]';
            return $s;
        });

        // Hitung total selesai/belum dan risiko preeklamsia 
        $baseQuery = Skrining::where('pasien_id', $pasienId);

        $totalAll     = (clone $baseQuery)->count();        
        $totalSelesai = (clone $baseQuery)->where('step_form', '>=', 6)->count();
        $totalBelum   = max(0, $totalAll - $totalSelesai);

        // Ambil status preeklamsia dari skrining terbaru yang sudah diisi
        $riskPreeklampsia = (clone $baseQuery)
            ->where('step_form', '>=', 6)
            ->latest()
            ->value('status_pre_eklampsia');

        // Fallback jika kolom status_pre_eklampsia kosong: pakai kesimpulan
        if (!$riskPreeklampsia) {
            $riskPreeklampsia = (clone $baseQuery)
                ->where('step_form', '>=', 6)
                ->latest()
                ->value('kesimpulan');
        }

        $riskLower = strtolower($riskPreeklampsia ?? '');
        $riskBoxClass = match ($riskLower) {
            'berisiko', 'beresiko'                       => 'bg-[#EB1D1D] text-white',
            'waspada'                                    => 'bg-[#FFC700] text-white',
            'normal', 'tidak berisiko', 'tidak beresiko' => 'bg-[#2EDB58] text-white',
            default                                      => 'bg-[#E9E9E9] text-[#1D1D1D]',
        };

        return view('pasien.dashboard', [            
            'skrinings'         => $skrinings,
            'status'            => $status,
            'totalSelesai'      => $totalSelesai,
            'totalBelum'        => $totalBelum,
            'riskPreeklampsia'  => $riskPreeklampsia,
            'riskBoxClass'      => $riskBoxClass, 
        ]);
    }
    
}
