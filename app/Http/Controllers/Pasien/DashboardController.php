<?php

namespace App\Http\Controllers\Pasien;

// Mengimpor base Controller Laravel.
use App\Http\Controllers\Controller;
// Mengimpor Request untuk menangkap input dari HTTP.
use Illuminate\Http\Request;
// Mengimpor facade Auth untuk identitas pasien yang login.
use Illuminate\Support\Facades\Auth;
// Mengimpor facade DB untuk operasi query builder/transaksi.
use Illuminate\Support\Facades\DB;
// Mengimpor model Skrining (tabel skrinings).
use App\Models\Skrining;
// Mengimpor trait SkriningHelpers (helper validasi & rekalkulasi skrining).
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class DashboardController extends Controller
{
    use SkriningHelpers;

    /* {{-- ========== DASHBOARD — INDEX ========== --}} */
    
    /* 
     * List skrining (paginate), filter status & tanggal, ringkasan, status risiko terbaru
     */
    public function index(Request $request)
    {
        // Ambil parameter query string untuk filter:
        // - 'status' → alias kesimpulan/risk (Normal | Waspada | Berisiko | Skrining belum selesai)
        // - 'date_from' & 'date_to' → rentang tanggal created_at
        $status   = trim($request->get('status', ''));
        $dateFrom = trim($request->get('date_from', ''));
        $dateTo   = trim($request->get('date_to', ''));

        $pasienId = optional(Auth::user()->pasien)->id;

        /* {{-- ==== FILTER ALIAS (KESIMPULAN & STATUS PREEKLAMPSI) ==== --}} */
        
        /* 
         * Menyatukan variasi nilai di DB untuk dipakai pada dropdown filter
         */
        $kesimpulanAliases = [
            'Tidak berisiko preeklampsia' => ['Tidak berisiko preeklampsia','Tidak beresiko preeklampsia','Tidak berisiko','Tidak beresiko','Aman','Normal','Sehat'],
            'Berisiko preeklampsia'       => ['Berisiko preeklampsia','Beresiko preeklampsia','Berisiko','Beresiko','Waspada','Waspadai','Menengah'],
        ];
        $preeklampsiaAliases = [
            'Tidak berisiko preeklampsia' => ['Normal','Tidak berisiko','Tidak beresiko','Tidak berisiko preeklampsia','Tidak beresiko preeklampsia'],
            'Berisiko preeklampsia'       => ['Risiko Tinggi','Resiko Tinggi','Risiko Sedang','Resiko Sedang','Berisiko preeklampsia','Beresiko preeklampsia'],
        ];

        /* {{-- ==== QUERY DAFTAR SKRINING ==== --}} */
        /**
         * Query daftar skrining milik pasien:
         * - where('pasien_id', ...) → hanya milik pasien yang login
         * - when($status !== '', ...) → filter alias kesimpulan/status preeklampsia
         * - when(date range, ...) → filter berdasarkan created_at (antara from..to)
         * - with(['pasien.user']) → eager load relasi untuk tampilan
         * - latest() → urutkan paling baru di atas
         * - paginate(10) → batasi 10 per halaman
         * - withQueryString() → parameter filter tetap ada saat paging
         */
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

        /* {{-- ==== TRANSFORM TAMPILAN (KESIMPULAN & BADGE) ==== --}} */
        
        /* 
         * Menyusun data tampilan ringkasan: kesimpulan, badge status,
         * ringkasan risiko, pemicu sedang/tinggi, dan rekomendasi
         * Kesimpulan: belum selesai | berisiko (tinggi ≥1 atau sedang ≥2) | tidak berisiko
         * Transform hasil paginate untuk tampilan UI:
         * - Tentukan 'conclusion_display' dari kelengkapan & jumlah risiko
         * - Tetapkan 'badge_class' sesuai kesimpulan
         * - Set 'has_referral' bila skrining memiliki rujukan RS
         */
        $skrinings->getCollection()->transform(function ($s) {
            $resikoSedang = (int)($s->jumlah_resiko_sedang ?? 0);
            $resikoTinggi = (int)($s->jumlah_resiko_tinggi ?? 0);

            $isComplete = $this->isSkriningCompleteForSkrining($s);

            if (!$isComplete) {
                $conclusion = 'Skrining belum selesai';
            } elseif ($resikoTinggi >= 1 || $resikoSedang >= 2) {
                $conclusion = 'Berisiko preeklampsia';
            } else {
                $conclusion = 'Tidak berisiko preeklampsia';
            }

            $key = strtolower(trim($conclusion));
            $badgeClasses = [
                'berisiko preeklampsia' => 'bg-red-600 text-white',
                'tidak berisiko preeklampsia' => 'bg-green-500 text-white',
                'skrining belum selesai' => 'bg-gray-200 text-gray-900',
            ];

            $s->conclusion_display = $conclusion;
            $s->badge_class        = $badgeClasses[$key] ?? 'bg-[#E9E9E9] text-[#1D1D1D]';
            $s->has_referral       = DB::table('rujukan_rs')->where('skrining_id', $s->id)->exists();
            return $s;
        });

        /* {{-- ==== RINGKASAN TOTAL SKRINING ==== --}} */
        
        /* 
         * Menghitung total skrining, selesai, dan belum selesai
         */
        $allSkrinings = Skrining::where('pasien_id', $pasienId)
            ->with(['pasien.user'])
            ->get();

        $totalAll     = $allSkrinings->count();
        $totalSelesai = $allSkrinings->filter(fn ($s) => $this->isSkriningCompleteForSkrining($s))->count();
        $totalBelum   = max(0, $totalAll - $totalSelesai);

        /* {{-- ==== STATUS PREEKLAMPSIA TERBARU ==== --}} */
        
        /* 
         * Ambil dari skrining terbaru; jika belum lengkap → "Skrining belum selesai"
         */
        $latestAny = $allSkrinings->sortByDesc('created_at')->first();

        $riskPreeklampsia = null;
        if ($latestAny) {
            if (!$this->isSkriningCompleteForSkrining($latestAny)) {
                $riskPreeklampsia = 'Skrining belum selesai';
            } else {
                $raw = $latestAny->status_pre_eklampsia ?: $latestAny->kesimpulan;
                $rawLower = strtolower(trim($raw ?? ''));
                $riskValues = ['berisiko','beresiko','risiko tinggi','resiko tinggi','risiko sedang','resiko sedang','waspada','menengah'];
                if (in_array($rawLower, $riskValues, true)) {
                    $riskPreeklampsia = 'Berisiko preeklampsia';
                } else {
                    $riskPreeklampsia = 'Tidak berisiko preeklampsia';
                }
            }
        }

        $riskLower   = strtolower($riskPreeklampsia ?? '');
        $riskBoxClass = match ($riskLower) {
            'berisiko preeklampsia' => 'bg-red-600 text-white',
            'tidak berisiko preeklampsia' => 'bg-[#2EDB58] text-white',
            default => 'bg-[#E9E9E9] text-[#1D1D1D]',
        };

        /* {{-- ==== STATUS RUJUKAN MENUJU RUMAH SAKIT ==== --}} */
        $referralHospital = null;
        $referralAccepted = false;

        if ($latestAny) {
            $ref = \Illuminate\Support\Facades\DB::table('rujukan_rs as rr')
                ->leftJoin('rumah_sakits as rs', 'rs.id', '=', 'rr.rs_id')
                ->select('rs.nama as rs_nama', 'rr.done_status')
                ->where('rr.pasien_id', $pasienId)
                ->where('rr.skrining_id', $latestAny->id)
                ->orderByDesc('rr.created_at')
                ->first();

            if ($ref) {
                $referralHospital = $ref->rs_nama;
                $referralAccepted = (bool) $ref->done_status;
            }
        }

        /* {{-- ==== RETURN VIEW ==== --}} */
        return view('pasien.dashboard.dashboard', [
            'skrinings'         => $skrinings,
            'status'            => $status,
            'dateFrom'          => $dateFrom,
            'dateTo'            => $dateTo,
            'totalSelesai'      => $totalSelesai,
            'totalBelum'        => $totalBelum,
            'riskPreeklampsia'  => $riskPreeklampsia,
            'riskBoxClass'      => $riskBoxClass,
            'referralHospital'  => $referralHospital,
            'referralAccepted'  => $referralAccepted,
        ]);
    }
}