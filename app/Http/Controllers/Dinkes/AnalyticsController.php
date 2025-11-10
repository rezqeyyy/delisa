<?php
// app/Http/Controllers/Dinkes/AnalyticsController.php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // ==== 1) Ambil filter ====
        $filters = [
            'from'    => $request->date('from'),
            'to'      => $request->date('to'),
            'kec'     => $request->string('kec')->toString(),
            'hadir'   => $request->string('hadir')->toString(), // hadir|mangkir|''
            'ageMin'  => $request->integer('age_min', 10),
            'ageMax'  => $request->integer('age_max', 60),
            'imtMin'  => $request->integer('imt_min', 10),
            'imtMax'  => $request->integer('imt_max', 60),
            'outcome' => $request->string('outcome', 'pe')->toString(), // pe|dirujuk|meninggal
        ];

        // ==== 2) Subquery dataset DEDUP ====
        $latestS = "
            SELECT DISTINCT ON (pasien_id)
                id, pasien_id, created_at, checked_status,
                COALESCE(jumlah_resiko_sedang,0)  AS jumlah_resiko_sedang,
                COALESCE(jumlah_resiko_tinggi,0)  AS jumlah_resiko_tinggi
            FROM skrinings
            ORDER BY pasien_id, created_at DESC
        ";

        $latestK = "
            SELECT DISTINCT ON (skrining_id)
                skrining_id, imt, sdp, dbp, pemeriksaan_protein_urine
            FROM kondisi_kesehatans
            ORDER BY skrining_id, created_at DESC
        ";

        // ==== 3) Basis dataset (Dedup, hanya yang skrining) ====
        $base = DB::table('pasiens as p')
            ->joinSub($latestS, 'ls', 'ls.pasien_id', '=', 'p.id')
            ->leftJoinSub($latestK, 'k', 'k.skrining_id', '=', 'ls.id')
            ->selectRaw("
                p.id AS pid,
                p.\"PKecamatan\" AS kecamatan,
                p.tanggal_lahir,
                k.imt::float           AS imt,
                k.sdp::float           AS sbp,
                k.dbp::float           AS dbp,
                k.pemeriksaan_protein_urine AS protein_urine,
                ls.created_at::date    AS t_skrining,
                CASE WHEN ls.checked_status IS TRUE THEN 1 ELSE 0 END AS hadir_bin,
                CASE
                  WHEN ? = 'pe'
                    THEN CASE WHEN (ls.jumlah_resiko_tinggi > 0 OR ls.jumlah_resiko_sedang > 0) THEN 1 ELSE 0 END
                  WHEN ? = 'dirujuk'
                    THEN CASE WHEN EXISTS (
                           SELECT 1 FROM rujukan_rs rr
                           WHERE rr.skrining_id = ls.id AND COALESCE(rr.is_rujuk,false)=true
                         ) THEN 1 ELSE 0 END
                  WHEN ? = 'meninggal'
                    THEN 0
                  ELSE 0
                END AS y
            ", [$filters['outcome'], $filters['outcome'], $filters['outcome']])
            ->when($filters['from'], fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->when($filters['kec'],  fn($q, $v) => $q->whereRaw('p."PKecamatan" ILIKE ?', ['%' . $v . '%']))
            ->when($filters['hadir'] === 'hadir',   fn($q) => $q->where('ls.checked_status', true))
            ->when($filters['hadir'] === 'mangkir', fn($q) => $q->where('ls.checked_status', false));

        // ==== 4) Umur & filter rentang ====
        $rows = $base->get()->map(function ($r) {
            $r->age = null;
            if (!empty($r->tanggal_lahir)) {
                try {
                    $birth = \Carbon\Carbon::parse($r->tanggal_lahir);
                    if (!$birth->isFuture()) $r->age = (int)$birth->age;
                } catch (\Throwable $e) {
                    $r->age = null;
                }
            }
            return $r;
        });

        $ageOn = $request->hasAny(['age_min', 'age_max']);
        $imtOn = $request->hasAny(['imt_min', 'imt_max']);

        $rows = $rows->filter(function ($r) use ($filters, $ageOn, $imtOn) {
            $okAge = !$ageOn ?: (!is_null($r->age) && $r->age >= $filters['ageMin'] && $r->age <= $filters['ageMax']);
            $imt   = is_null($r->imt) ? null : (float)$r->imt;
            $okImt = !$imtOn ?: (!is_null($imt) && $imt >= $filters['imtMin'] && $imt <= $filters['imtMax']);
            return $okAge && $okImt;
        })->values();

        // ==== 5) Korelasi ringkas ====
        $calcCorr = function ($xKey) use ($rows) {
            $xs = [];
            $ys = [];
            foreach ($rows as $r) {
                $x = $r->{$xKey};
                if (is_null($x)) continue;
                $xs[] = (float)$x;
                $ys[] = (int)$r->y;
            }
            $n = count($xs);
            if ($n < 5) return null;
            $mx = array_sum($xs) / $n;
            $my = array_sum($ys) / $n;
            $num = 0;
            $dx2 = 0;
            $dy2 = 0;
            for ($i = 0; $i < $n; $i++) {
                $dx = $xs[$i] - $mx;
                $dy = $ys[$i] - $my;
                $num += $dx * $dy;
                $dx2 += $dx * $dx;
                $dy2 += $dy * $dy;
            }
            if ($dx2 == 0 || $dy2 == 0) return null;
            return $num / sqrt($dx2 * $dy2);
        };

        $candNumeric = [
            ['key' => 'imt', 'label' => 'IMT'],
            ['key' => 'age', 'label' => 'Umur'],
            ['key' => 'sbp', 'label' => 'Tekanan Sistolik'],
            ['key' => 'dbp', 'label' => 'Tekanan Diastolik'],
        ];

        $corrs = [];
        foreach ($candNumeric as $c) {
            $r = $calcCorr($c['key']);
            if (!is_null($r)) $corrs[] = ['label' => $c['label'], 'key' => $c['key'], 'type' => 'numeric', 'r' => round($r, 3)];
        }

        // OR Protein Urine (+) — pakai continuity correction
        $a = $b = $c = $d = 0;
        foreach ($rows as $r) {
            $plus = ($r->protein_urine !== 'Negatif' && $r->protein_urine !== 'Belum dilakukan Pemeriksaan');
            if ($plus && $r->y == 1) $a++;
            elseif ($plus && $r->y == 0) $b++;
            elseif (!$plus && $r->y == 1) $c++;
            else $d++;
        }
        $or = null;
        if (($a + $b + $c + $d) > 0) {
            if (($b * $c) > 0) {
                $or = ($a * $d) / ($b * $c);
            } else {
                $or = (($a + 0.5) * ($d + 0.5)) / (($b + 0.5) * ($c + 0.5)); // Haldane–Anscombe
            }
        }
        if ($or) {
            $corrs[] = ['label' => 'Protein Urine (+)', 'key' => 'protein_urine', 'type' => 'categorical', 'or' => round($or, 2)];
        }

        $top = collect($corrs)->map(function ($it) {
            $score = $it['type'] === 'numeric' ? abs($it['r']) : abs(log(max(0.001, $it['or'])));
            return ['score' => $score] + $it;
        })->sortByDesc('score')->values()->take(5)->all();

        $total = $rows->count();
        $rateY = $total ? round($rows->avg('y') * 100, 1) : 0;

        // Flag: outcome tak bervariasi (atau data minim)
        $noVarY = ($total < 5)
            || $rows->every(fn($r) => (int)$r->y === 0)
            || $rows->every(fn($r) => (int)$r->y === 1);

        // --- 6) Agregasi per kecamatan (dedup; hanya yang skrining)
        $byKec = DB::table('pasiens as p')
            ->joinSub($latestS, 'ls', 'ls.pasien_id', '=', 'p.id')
            ->selectRaw('p."PKecamatan" AS kec,
                        COUNT(ls.id) AS n,
                        SUM(CASE WHEN (ls.jumlah_resiko_tinggi>0 OR ls.jumlah_resiko_sedang>0) THEN 1 ELSE 0 END) AS y')
            ->when($filters['from'], fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->groupBy('kec')
            ->get()
            ->map(fn($r) => (object)[
                'kec' => $r->kec,
                'n' => (int)$r->n,
                'rate' => $r->n ? round($r->y / $r->n * 100, 1) : 0
            ]);

        // --- 7) Tren 12 bulan (dedup)
        $trend = DB::query()
            ->fromSub($latestS, 'ls')
            ->selectRaw("date_trunc('month', ls.created_at)::date AS bulan,
                         COUNT(*) AS total,
                         SUM(CASE WHEN (ls.jumlah_resiko_tinggi>0 OR ls.jumlah_resiko_sedang>0) THEN 1 ELSE 0 END) AS y_pe,
                         SUM(CASE WHEN EXISTS (
                                SELECT 1 FROM rujukan_rs rr
                                WHERE rr.skrining_id = ls.id AND COALESCE(rr.is_rujuk,false)=true
                             ) THEN 1 ELSE 0 END) AS y_rujuk")
            ->when($filters['from'], fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // --- 8) Data Quality (dedup)
        $kStats = DB::query()
            ->fromSub($latestS, 'ls')
            ->leftJoinSub($latestK, 'k', 'k.skrining_id', '=', 'ls.id')
            ->when($filters['from'], fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->selectRaw("COUNT(*) AS n,
                 SUM(CASE WHEN k.imt IS NULL THEN 1 ELSE 0 END) AS miss_imt,
                 SUM(CASE WHEN k.sdp IS NULL OR k.dbp IS NULL THEN 1 ELSE 0 END) AS miss_bp,
                 SUM(CASE WHEN k.pemeriksaan_protein_urine IS NULL
                           OR k.pemeriksaan_protein_urine = 'Belum dilakukan Pemeriksaan'
                          THEN 1 ELSE 0 END) AS miss_prot")
            ->first();

        $den = max(1, (int)($kStats->n ?? 0));
        $dq  = [
            'missing_imt'  => round(($kStats->miss_imt  ?? 0) / $den * 100, 1),
            'missing_bp'   => round(($kStats->miss_bp   ?? 0) / $den * 100, 1),
            'missing_prot' => round(($kStats->miss_prot ?? 0) / $den * 100, 1),
        ];

        // --- 9) Cohort Compare (B = semua latest skrining, periode sama)
        $oc = $filters['outcome'];
        $bAgg = DB::query()
            ->fromSub($latestS, 'ls')
            ->selectRaw("
                COUNT(*) AS n,
                SUM(
                  CASE
                    WHEN ? = 'pe'
                      THEN CASE WHEN (ls.jumlah_resiko_tinggi>0 OR ls.jumlah_resiko_sedang>0) THEN 1 ELSE 0 END
                    WHEN ? = 'dirujuk'
                      THEN CASE WHEN EXISTS (
                               SELECT 1 FROM rujukan_rs rr
                               WHERE rr.skrining_id = ls.id AND COALESCE(rr.is_rujuk,false)=true
                           ) THEN 1 ELSE 0 END
                    WHEN ? = 'meninggal' THEN 0
                    ELSE 0
                  END
                ) AS y
            ", [$oc, $oc, $oc])
            ->when($filters['from'], fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->first();

        $B_rate = ($bAgg && $bAgg->n) ? round($bAgg->y / $bAgg->n * 100, 1) : 0.0;
        $A_rate = (float)$rateY;
        $cohort = [
            'A_rate' => $A_rate,
            'B_rate' => $B_rate,
            'rd'     => round($A_rate - $B_rate, 1),
            'rr'     => $B_rate > 0 ? round($A_rate / $B_rate, 2) : null,
        ];

        return view('dinkes.analytics.index', [
            'filters' => $filters,
            'top'     => $top,
            'total'   => $total,
            'rateY'   => $rateY,
            'trend'   => $trend,
            'byKec'   => $byKec,
            'dq'      => $dq,
            'cohort'  => $cohort,
            'noVarY'  => $noVarY, // << kirim ke view
        ]);
    }

    public function showVariable(string $key, Request $request)
    {
        $oc = $request->string('outcome', 'pe')->toString();

        $latestS = "
        SELECT DISTINCT ON (pasien_id)
            id, pasien_id, created_at, checked_status,
            COALESCE(jumlah_resiko_sedang,0)  AS jumlah_resiko_sedang,
            COALESCE(jumlah_resiko_tinggi,0)  AS jumlah_resiko_tinggi
        FROM skrinings
        ORDER BY pasien_id, created_at DESC
    ";

        $latestK = "
        SELECT DISTINCT ON (skrining_id)
            skrining_id, imt, sdp, dbp, pemeriksaan_protein_urine
        FROM kondisi_kesehatans
        ORDER BY skrining_id, created_at DESC
    ";

        $base = DB::table('pasiens as p')
            ->joinSub($latestS, 'ls', 'ls.pasien_id', '=', 'p.id')
            ->leftJoinSub($latestK, 'k', 'k.skrining_id', '=', 'ls.id')
            ->selectRaw("
            p.id AS pid,
            p.\"PKecamatan\" AS kec,
            p.tanggal_lahir,
            k.imt::float  AS imt,
            k.sdp::float  AS sbp,
            k.dbp::float  AS dbp,
            k.pemeriksaan_protein_urine AS prot,
            ls.created_at::date AS tgl,
            CASE WHEN ls.checked_status IS TRUE THEN 1 ELSE 0 END AS hadir_bin,
            CASE
              WHEN ? = 'pe'
                THEN CASE WHEN (ls.jumlah_resiko_tinggi>0 OR ls.jumlah_resiko_sedang>0) THEN 1 ELSE 0 END
              WHEN ? = 'dirujuk'
                THEN CASE WHEN EXISTS (
                    SELECT 1 FROM rujukan_rs rr
                    WHERE rr.skrining_id = ls.id AND COALESCE(rr.is_rujuk,false)=true
                ) THEN 1 ELSE 0 END
              WHEN ? = 'meninggal'
                THEN 0
              ELSE 0
            END AS y
        ", [$oc, $oc, $oc])
            ->when($request->date('from'), fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($request->date('to'),   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->when($request->filled('kec'), function ($q) use ($request) {
                $q->whereRaw('p."PKecamatan" ILIKE ?', ['%' . $request->string('kec')->toString() . '%']);
            });

        $rows = $base->get()->map(function ($r) {
            $r->age = null;
            if (!empty($r->tanggal_lahir)) {
                try {
                    $birth = \Carbon\Carbon::parse($r->tanggal_lahir);
                    if (!$birth->isFuture()) $r->age = (int)$birth->age;
                } catch (\Throwable $e) {
                    $r->age = null;
                }
            }
            return $r;
        });

        $meta = [
            'age'  => ['label' => 'Umur (tahun)', 'type' => 'num'],
            'imt'  => ['label' => 'IMT',           'type' => 'num'],
            'sbp'  => ['label' => 'Sistolik (sdp)', 'type' => 'num'],
            'dbp'  => ['label' => 'Diastolik (dbp)', 'type' => 'num'],
            'prot' => [
                'label' => 'Protein urine',
                'type' => 'cat',
                'levels' => ['Negatif', 'Positif 1', 'Positif 2', 'Positif 3', 'Belum dilakukan Pemeriksaan'],
            ],
            'protein_urine' => [
                'label' => 'Protein urine',
                'type' => 'cat',
                'levels' => ['Negatif', 'Positif 1', 'Positif 2', 'Positif 3', 'Belum dilakukan Pemeriksaan'],
            ],
        ];
        abort_unless(isset($meta[$key]), 404);
        $m = $meta[$key];

        // === Helper quantile (linear interpolation) ===
        $quantileLinear = function (array $a, float $p): ?float {
            $n = count($a);
            if ($n === 0) return null;
            sort($a, SORT_NUMERIC);
            // rank in [0, n-1]
            $rank = $p * ($n - 1);
            $lo = (int) floor($rank);
            $hi = (int) ceil($rank);
            if ($lo === $hi) return (float) $a[$lo];
            $w = $rank - $lo; // 0..1
            return (1 - $w) * (float)$a[$lo] + $w * (float)$a[$hi];
        };

        $summary = [];
        $dist = [];
        $table2x2 = null;
        if ($m['type'] === 'num') {
            // NOTE: filter yang aman untuk 0: hanya buang null/'' saja
            $vals = $rows->pluck($key)
                ->filter(fn($v) => $v !== null && $v !== '')
                ->map(fn($v) => (float)$v)
                ->values();

            $n = $vals->count();
            if ($n > 0) {
                $arr = $vals->all();
                $cuts = match ($key) {
                    'imt' => [18.5, 25, 30],
                    'sbp' => [120, 140],
                    'dbp' => [80, 90],
                    default => [
                        $quantileLinear($arr, 0.25),
                        $quantileLinear($arr, 0.50),
                        $quantileLinear($arr, 0.75),
                    ],
                };

                $makeLabel = function (int $i, array $cut) {
                    $last = $cut[count($cut) - 1] ?? null;
                    if ($i === 0)                return "< " . $cut[0];
                    if ($i === count($cut))      return "≥ " . $last;
                    return $cut[$i - 1] . "–" . $cut[$i];
                };

                $buckets = [];
                $k = count($cuts) + 1;
                for ($i = 0; $i < $k; $i++) $buckets[$i] = ['label' => $makeLabel($i, $cuts), 'n' => 0, 'y' => 0];

                foreach ($rows as $r) {
                    $x = $r->{$key};
                    if ($x === null || $x === '') continue;
                    $x = (float)$x;
                    $idx = 0;
                    while ($idx < count($cuts) && $x >= (float)$cuts[$idx]) $idx++;
                    $buckets[$idx]['n']++;
                    $buckets[$idx]['y'] += (int)$r->y;
                }

                $dist = collect($buckets)
                    ->map(fn($b) => $b + ['rate' => $b['n'] ? round($b['y'] / $b['n'] * 100, 1) : 0])
                    ->values()
                    ->all();

                $overall = $rows->count() ? round($rows->avg('y') * 100, 1) : 0;
                $best = collect($dist)->sortByDesc('rate')->first();
                $summary = [
                    'overall_rate' => $overall,
                    'best_bucket'  => $best['label'] ?? null,
                    'best_rate'    => $best['rate'] ?? 0,
                    'n_total'      => $rows->count(),
                ];
            }
        } else {
            // 2x2 Protein urine (+) vs outcome y, continuity correction
            $a = $b = $c = $d = 0;
            foreach ($rows as $r) {
                if ($r->prot === null) continue;
                $plus = ($r->prot !== 'Negatif' && $r->prot !== 'Belum dilakukan Pemeriksaan');
                if ($plus && $r->y == 1) $a++;
                elseif ($plus && $r->y == 0) $b++;
                elseif (!$plus && $r->y == 1) $c++;
                else $d++;
            }
            $or = (($a + 0.5) * ($d + 0.5)) / (($b + 0.5) * ($c + 0.5));
            $se = sqrt(1 / ($a + 0.5) + 1 / ($b + 0.5) + 1 / ($c + 0.5) + 1 / ($d + 0.5));
            $lo = exp(log($or) - 1.96 * $se);
            $hi = exp(log($or) + 1.96 * $se);
            $table2x2 = ['a' => $a, 'b' => $b, 'c' => $c, 'd' => $d, 'or' => $or, 'lo' => $lo, 'hi' => $hi];
        }

        return view('dinkes.analytics.variable', [
            'key'      => $key,
            'meta'     => $m,
            'dist'     => $dist,
            'summary'  => $summary,
            'table2x2' => $table2x2,
            'filters'  => $request->all(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'delisa_analytics_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            if (function_exists('ob_get_level')) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
            }
            $out = fopen('php://output', 'w');

            // Header kolom
            fputcsv($out, ['pid_hash', 'kecamatan', 'age', 'imt', 'sbp', 'dbp', 'protein_urine', 'hadir', 'outcome']);

            try {
                $request->merge(['outcome' => $request->string('outcome', 'pe')->toString()]);
                $this->streamRows($request, function ($r) use ($out) {
                    $pidHash = hash('sha256', config('app.key') . '|' . $r->pid);
                    fputcsv($out, [
                        $pidHash,
                        $r->kecamatan,
                        $r->age ?? null,
                        $r->imt ?? null,
                        $r->sbp ?? null,
                        $r->dbp ?? null,
                        $r->protein_urine ?? null,
                        $r->hadir_bin ?? null,
                        $r->y ?? null,
                    ]);
                    if (function_exists('flush')) {
                        flush();
                    }
                });
            } catch (\Throwable $e) {
                fputcsv($out, ['ERROR', $e->getMessage()]);
            } finally {
                fclose($out);
            }
        }, $filename, [
            'Content-Type'           => 'text/csv; charset=UTF-8',
            'Cache-Control'          => 'no-store, no-cache, must-revalidate',
            'Pragma'                 => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // === Streaming helper: sudah DEDUP (latest screening per pasien) ===
    public function streamRows(Request $request, \Closure $each)
    {
        $oc = $request->string('outcome', 'pe')->toString();

        $latestS = "
            SELECT DISTINCT ON (pasien_id)
                id, pasien_id, created_at, checked_status,
                COALESCE(jumlah_resiko_sedang,0)  AS jumlah_resiko_sedang,
                COALESCE(jumlah_resiko_tinggi,0)  AS jumlah_resiko_tinggi
            FROM skrinings
            ORDER BY pasien_id, created_at DESC
        ";

        $latestK = "
            SELECT DISTINCT ON (skrining_id)
                skrining_id, imt, sdp, dbp, pemeriksaan_protein_urine
            FROM kondisi_kesehatans
            ORDER BY skrining_id, created_at DESC
        ";

        $q = DB::table('pasiens as p')
            ->joinSub($latestS, 'ls', 'ls.pasien_id', '=', 'p.id')
            ->leftJoinSub($latestK, 'k', 'k.skrining_id', '=', 'ls.id')
            ->selectRaw("
                p.id AS pid,
                p.\"PKecamatan\" AS kecamatan,
                p.tanggal_lahir,
                k.imt::float AS imt,
                k.sdp::float AS sbp,
                k.dbp::float AS dbp,
                k.pemeriksaan_protein_urine AS protein_urine,
                CASE WHEN ls.checked_status IS TRUE THEN 1 ELSE 0 END AS hadir_bin,
                CASE
                  WHEN ? = 'pe'
                    THEN CASE WHEN (ls.jumlah_resiko_tinggi>0 OR ls.jumlah_resiko_sedang>0) THEN 1 ELSE 0 END
                  WHEN ? = 'dirujuk'
                    THEN CASE WHEN EXISTS (
                           SELECT 1 FROM rujukan_rs rr
                           WHERE rr.skrining_id = ls.id AND COALESCE(rr.is_rujuk,false)=true
                         ) THEN 1 ELSE 0 END
                  WHEN ? = 'meninggal'
                    THEN 0
                  ELSE 0
                END AS y
            ", [$oc, $oc, $oc])
            ->when($request->date('from'), fn($q, $v) => $q->whereDate('ls.created_at', '>=', $v))
            ->when($request->date('to'),   fn($q, $v) => $q->whereDate('ls.created_at', '<=', $v))
            ->when($request->filled('kec'), function ($q) use ($request) {
                $q->whereRaw('p."PKecamatan" ILIKE ?', ['%' . $request->string('kec')->toString() . '%']);
            })
            ->orderBy('p.id');

        $q->chunk(1000, function ($chunk) use ($each) {
            foreach ($chunk as $r) {
                $r->age = null;
                if (!empty($r->tanggal_lahir)) {
                    try {
                        $birth = \Carbon\Carbon::parse($r->tanggal_lahir);
                        if (!$birth->isFuture()) $r->age = (int)$birth->age;
                    } catch (\Throwable $e) {
                        $r->age = null;
                    }
                }
                $each($r);
            }
        });
    }
}
