<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class DashboardController extends Controller
{
    use SkriningHelpers;

    /* =========================================================
     * DASHBOARD — INDEX
     * =========================================================
     * List skrining (paginate), filter status & tanggal, ringkasan, status risiko terbaru
     */
    public function index(Request $request)
    {
        // Ambil nilai filter status dari query string, kosong jika tidak ada
        // Nilai yang dikirim dari dropdown: "Normal", "Waspada", "Berisiko"
        $status   = trim($request->get('status', ''));
        $dateFrom = trim($request->get('date_from', ''));
        $dateTo   = trim($request->get('date_to', ''));

        $pasienId = optional(Auth::user()->pasien)->id;

        /* ==== FILTER ALIAS (KESIMPULAN & STATUS PREEKLAMPSIA) ====
         * Menyatukan variasi nilai di DB untuk dipakai pada dropdown filter
         */
        $kesimpulanAliases = [
            'Normal'   => ['Normal', 'Aman', 'Tidak berisiko', 'Tidak beresiko'],
            'Waspada'  => ['Waspada', 'Waspadai'],
            'Berisiko' => ['Berisiko', 'Beresiko'],
        ];
        $preeklampsiaAliases = [
            'Normal'   => ['Normal'],
            'Waspada'  => ['Risiko Sedang'],
            'Berisiko' => ['Risiko Tinggi'],
        ];

        /* ==== QUERY DAFTAR SKRINING ==== */
        $skrinings = Skrining::where('pasien_id', $pasienId)
            ->when($status !== '', function ($q) use ($status, $kesimpulanAliases, $preeklampsiaAliases) {
                if ($status === 'Skrining belum selesai') {
                    $q->where('step_form', '<', 6);
                } else {
                    $kesVals = $kesimpulanAliases[$status] ?? [$status];
                    $preVals = $preeklampsiaAliases[$status] ?? [$status];
                    $q->where(function ($w) use ($kesVals, $preVals) {
                        $w->whereIn('kesimpulan', $kesVals)
                          ->orWhereIn('status_pre_eklampsia', $preVals);
                    });
                }
            })
            ->when(($dateFrom !== '' || $dateTo !== ''), function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom !== '' && $dateTo !== '') {
                    $q->whereDate('created_at', '>=', $dateFrom)
                      ->whereDate('created_at', '<=', $dateTo);
                } elseif ($dateFrom !== '') {
                    $q->whereDate('created_at', '>=', $dateFrom);
                } else {
                    $q->whereDate('created_at', '<=', $dateTo);
                }
            })
            ->with(['pasien.user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /* ==== TRANSFORM TAMPILAN (KESIMPULAN & BADGE) ====
         * Kesimpulan: belum selesai | berisiko (tinggi ≥1 atau sedang ≥2) | tidak berisiko
         */
        $skrinings->getCollection()->transform(function ($s) {
            $resikoSedang = (int)($s->jumlah_resiko_sedang ?? 0);
            $resikoTinggi = (int)($s->jumlah_resiko_tinggi ?? 0);

            $isComplete = $this->isSkriningCompleteForSkrining($s);

            if (!$isComplete) {
                $conclusion = 'Skrining belum selesai';
            } elseif ($resikoTinggi >= 1 || $resikoSedang >= 2) {
                $conclusion = 'Berisiko';
            } else {
                $conclusion = 'Tidak berisiko';
            }

            $key = strtolower(trim($conclusion));
            $badgeClasses = [
                'berisiko'               => 'bg-red-600 text-white',
                'beresiko'               => 'bg-red-600 text-white',
                'tidak berisiko'         => 'bg-green-500 text-white',
                'tidak beresiko'         => 'bg-green-500 text-white',
                'aman'                   => 'bg-green-500 text-white',
                'normal'                 => 'bg-green-500 text-white',
                'skrining belum selesai' => 'bg-gray-200 text-gray-900',
            ];

            $s->conclusion_display = $conclusion;
            $s->badge_class        = $badgeClasses[$key] ?? 'bg-[#E9E9E9] text-[#1D1D1D]';
            return $s;
        });

        /* ==== RINGKASAN TOTAL SKRINING ==== */
        $allSkrinings = Skrining::where('pasien_id', $pasienId)
            ->with(['pasien.user'])
            ->get();

        $totalAll     = $allSkrinings->count();
        $totalSelesai = $allSkrinings->filter(fn ($s) => $this->isSkriningCompleteForSkrining($s))->count();
        $totalBelum   = max(0, $totalAll - $totalSelesai);

        /* ==== STATUS PREEKLAMPSIA TERBARU ====
         * Ambil dari skrining terbaru; jika belum lengkap → "Skrining belum selesai"
         */
        $latestAny = $allSkrinings->sortByDesc('created_at')->first();

        $riskPreeklampsia = null;
        if ($latestAny) {
            if (!$this->isSkriningCompleteForSkrining($latestAny)) {
                $riskPreeklampsia = 'Skrining belum selesai';
            } else {
                $riskPreeklampsia = $latestAny->status_pre_eklampsia ?: $latestAny->kesimpulan;
            }
        }

        $riskLower   = strtolower($riskPreeklampsia ?? '');
        $riskBoxClass = match ($riskLower) {
            'berisiko', 'beresiko', 'risiko tinggi', 'resiko tinggi' => 'bg-red-600 text-white',
            'normal', 'aman', 'tidak berisiko', 'tidak beresiko' => 'bg-[#2EDB58] text-white',
            default => 'bg-[#E9E9E9] text-[#1D1D1D]',
        };

        /* ==== RETURN VIEW ==== */
        return view('pasien.dashboard', [
            'skrinings'         => $skrinings,
            'status'            => $status,
            'dateFrom'          => $dateFrom,
            'dateTo'            => $dateTo,
            'totalSelesai'      => $totalSelesai,
            'totalBelum'        => $totalBelum,
            'riskPreeklampsia'  => $riskPreeklampsia,
            'riskBoxClass'      => $riskBoxClass,
        ]);
    }
}