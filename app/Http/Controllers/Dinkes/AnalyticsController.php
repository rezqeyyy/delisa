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

        // ==== 2) Basis dataset ====
        $base = DB::table('pasiens as p')
            ->leftJoin('skrinings as s', 's.pasien_id', '=', 'p.id')
            ->leftJoin('kondisi_kesehatans as k', 'k.skrining_id', '=', 's.id')
            ->selectRaw("
                p.id as pid,
                p.\"PKecamatan\" as PKecamatan,
                p.tanggal_lahir,
                COALESCE(k.imt, NULL)::float as imt,
                COALESCE(k.sdp, NULL)::float as sbp,
                COALESCE(k.dbp, NULL)::float as dbp,
                k.pemeriksaan_protein_urine as protein_urine,
                s.created_at::date as t_skrining,
                CASE WHEN s.checked_status IS TRUE THEN 1 ELSE 0 END as hadir_bin,
                CASE
                  WHEN ? =   'pe'
                    THEN CASE WHEN (COALESCE(s.jumlah_resiko_tinggi,0)>0 OR COALESCE(s.jumlah_resiko_sedang,0)>0) THEN 1 ELSE 0 END
                  WHEN ? =   'dirujuk'
                    THEN CASE WHEN EXISTS (
                           SELECT 1 FROM rujukan_rs rr
                           WHERE rr.skrining_id = s.id AND COALESCE(rr.is_rujuk, false) = true
                         ) THEN 1 ELSE 0 END
                  WHEN ? = 'meninggal'
                    THEN 0
                  ELSE 0
                END as y
            ", [$filters['outcome'], $filters['outcome'], $filters['outcome']]);
        // ==== 3) Filter dinamis ====
        $base = $base
            ->when($filters['from'], fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
            // Kolom PKecamatan di-quote dalam skema, jadi tetap pakai "PKecamatan"
            ->when($filters['kec'], function ($q, $v) {
                $q->whereRaw('p."PKecamatan" ILIKE ?', ['%' . $v . '%']);
            })
            ->when($filters['hadir'] === 'hadir',   fn($q) => $q->where('s.checked_status', true))
            ->when($filters['hadir'] === 'mangkir', fn($q) => $q->where('s.checked_status', false));

        // ==== 4) Umur & filter rentang ====
        $rows = $base->get()->map(function ($r) {
            $r->age = null;
            if (!empty($r->tanggal_lahir)) {
                try {
                    $r->age = now()->diffInYears(\Carbon\Carbon::parse($r->tanggal_lahir));
                } catch (\Throwable $e) {
                }
            }
            return $r;
        });

        $rows = $rows->filter(function ($r) use ($filters) {
            $okAge = is_null($r->age) ? true : ($r->age >= $filters['ageMin'] && $r->age <= $filters['ageMax']);
            $imt   = is_null($r->imt) ? null : (float)$r->imt;
            $okImt = is_null($imt) ? true : ($imt >= $filters['imtMin'] && $imt <= $filters['imtMax']);

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
            $meanX = array_sum($xs) / $n;
            $meanY = array_sum($ys) / $n;
            $num = 0;
            $denX = 0;
            $denY = 0;
            for ($i = 0; $i < $n; $i++) {
                $dx = $xs[$i] - $meanX;
                $dy = $ys[$i] - $meanY;
                $num += $dx * $dy;
                $denX += $dx * $dx;
                $denY += $dy * $dy;
            }
            if ($denX == 0 || $denY == 0) return null;
            return $num / sqrt($denX * $denY);
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

        // OR sederhana untuk protein urine (+) vs outcome
        $a = $b = $c = $d = 0;
        foreach ($rows as $r) {
            // Di skema: enum {'Negatif','Positif 1','Positif 2','Positif 3','Belum dilakukan Pemeriksaan'}
            $plus = ($r->protein_urine !== 'Negatif' && $r->protein_urine !== 'Belum dilakukan Pemeriksaan');
            if ($plus && $r->y == 1) $a++;
            elseif ($plus && $r->y == 0) $b++;
            elseif (!$plus && $r->y == 1) $c++;
            else $d++;
        }
        if (($b * $c) > 0) {
            $or = ($a * $d) / ($b * $c);
            $corrs[] = ['label' => 'Protein Urine (+)', 'key' => 'protein_urine', 'type' => 'categorical', 'or' => round($or, 2)];
        }

        $top = collect($corrs)->map(function ($it) {
            $score = $it['type'] === 'numeric' ? abs($it['r']) : abs(log(max(0.001, $it['or']))); // ranking
            return ['score' => $score] + $it;
        })->sortByDesc('score')->values()->take(5)->all();

        $total = $rows->count();
        $rateY = $total ? round($rows->avg('y') * 100, 1) : 0;
        // --- 6) Agregasi per kecamatan (untuk kartu choropleth sederhana)
        $byKec = DB::table('pasiens as p')
            ->leftJoin('skrinings as s', 's.pasien_id', '=', 'p.id')
            ->selectRaw('p."PKecamatan" as kec, count(*) n,
       sum(CASE WHEN (COALESCE(s.jumlah_resiko_tinggi,0)>0 OR COALESCE(s.jumlah_resiko_sedang,0)>0) THEN 1 ELSE 0 END) y')
            ->when($filters['from'], fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
            ->groupBy('kec')
            ->get()
            ->map(fn($r) => (object)[
                'kec'  => $r->kec,
                'n'    => (int)$r->n,
                'rate' => $r->n ? round($r->y / $r->n * 100, 1) : 0
            ]);

        // --- 7) Tren 12 bulan
        $trend = DB::table('skrinings as s')
            ->selectRaw("date_trunc('month', s.created_at)::date as bulan,
                 count(*) as total,
                 sum(CASE WHEN (COALESCE(s.jumlah_resiko_tinggi,0)>0 OR COALESCE(s.jumlah_resiko_sedang,0)>0) THEN 1 ELSE 0 END) as y_pe,
                 sum(CASE WHEN EXISTS (
                        SELECT 1 FROM rujukan_rs rr
                        WHERE rr.skrining_id = s.id AND COALESCE(rr.is_rujuk,false)=true
                 ) THEN 1 ELSE 0 END) as y_rujuk")
            ->when($filters['from'], fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // --- 8) Data Quality (missingness) pada periode terpilih
        $kStats = DB::table('kondisi_kesehatans as k')
            ->join('skrinings as s', 's.id', '=', 'k.skrining_id')
            ->when($filters['from'], fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
            ->selectRaw('count(*) as n,
                 sum(CASE WHEN k.imt IS NULL THEN 1 ELSE 0 END) as miss_imt,
                 sum(CASE WHEN k.sdp IS NULL OR k.dbp IS NULL THEN 1 ELSE 0 END) as miss_bp,
                 sum(CASE WHEN k.pemeriksaan_protein_urine IS NULL
                           OR k.pemeriksaan_protein_urine = \'Belum dilakukan Pemeriksaan\'
                          THEN 1 ELSE 0 END) as miss_prot')
            ->first();

        $den = max(1, (int)($kStats->n ?? 0));
        $dq = [
            'missing_imt'  => round(($kStats->miss_imt  ?? 0) / $den * 100, 1),
            'missing_bp'   => round(($kStats->miss_bp   ?? 0) / $den * 100, 1),
            'missing_prot' => round(($kStats->miss_prot ?? 0) / $den * 100, 1),
        ];

        // --- 9) Cohort Compare (A: filter sekarang, B: semua kecamatan – tetap periode sama)
        $oc = $filters['outcome'];
        $bAgg = DB::table('skrinings as s')
            ->selectRaw("
        count(*) as n,
        sum(
          CASE
            WHEN ? =   'pe'
              THEN CASE WHEN (COALESCE(s.jumlah_resiko_tinggi,0)>0 OR COALESCE(s.jumlah_resiko_sedang,0)>0)
                        THEN 1 ELSE 0 END
            WHEN ? =   'dirujuk'
              THEN CASE WHEN EXISTS (
                       SELECT 1 FROM rujukan_rs rr
                       WHERE rr.skrining_id = s.id AND COALESCE(rr.is_rujuk,false)=true
                   ) THEN 1 ELSE 0 END
            WHEN ? =   'meninggal' THEN 0
            ELSE 0
          END
        ) as y
    ", [$oc, $oc, $oc])
            ->when($filters['from'], fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($filters['to'],   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
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
        ]);
    }

    public function showVariable(string $key, Request $request)
    {
        // Ambil outcome & filter yang sama seperti index()
        $oc = $request->string('outcome', 'pe')->toString();

        // Basis query sama (tanpa map umur dulu)
        $base = DB::table('pasiens as p')
            ->leftJoin('skrinings as s', 's.pasien_id', '=', 'p.id')
            ->leftJoin('kondisi_kesehatans as k', 'k.skrining_id', '=', 's.id')
            ->selectRaw("
            p.id as pid,
            p.\"PKecamatan\" as kec,
            p.tanggal_lahir,
            COALESCE(k.imt, NULL)::float imt,
            COALESCE(k.sdp, NULL)::float sbp,
            COALESCE(k.dbp, NULL)::float dbp,
            k.pemeriksaan_protein_urine as prot,
            s.created_at::date as tgl,
            CASE WHEN s.checked_status IS TRUE THEN 1 ELSE 0 END hadir_bin,
            CASE
              WHEN ? = 'pe'
                THEN CASE WHEN (COALESCE(s.jumlah_resiko_tinggi,0)>0 OR COALESCE(s.jumlah_resiko_sedang,0)>0) THEN 1 ELSE 0 END
              WHEN ? = 'dirujuk'
                THEN CASE WHEN EXISTS (
                  SELECT 1 FROM rujukan_rs rr
                  WHERE rr.skrining_id = s.id AND COALESCE(rr.is_rujuk,false) = true
                ) THEN 1 ELSE 0 END
              WHEN ? = 'meninggal'
                THEN 0
              ELSE 0
            END y
        ", [$oc, $oc, $oc])
            ->when($request->date('from'), fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($request->date('to'),   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
            ->when($request->filled('kec'), function ($q) use ($request) {
                $q->whereRaw('p."PKecamatan" ILIKE ?', ['%' . $request->string('kec')->toString() . '%']);
            });
        $rows = $base->get()->map(function ($r) {
            // umur
            $r->age = null;
            if ($r->tanggal_lahir) {
                try {
                    $r->age = now()->diffInYears(\Carbon\Carbon::parse($r->tanggal_lahir));
                } catch (\Throwable $e) {
                }
            }
            return $r;
        });

        // Pilih variabel
        $meta = [
            'age'  => ['label' => 'Umur (tahun)', 'type' => 'num'],
            'imt'  => ['label' => 'IMT', 'type' => 'num'],
            'sbp'  => ['label' => 'Sistolik (sdp)', 'type' => 'num'],
            'dbp'  => ['label' => 'Diastolik (dbp)', 'type' => 'num'],
            'prot' => [
                'label' => 'Protein urine',
                'type' => 'cat',
                'levels' => ['Negatif', 'Positif 1', 'Positif 2', 'Positif 3', 'Belum dilakukan Pemeriksaan']
            ],
        ];
        abort_unless(isset($meta[$key]), 404);
        $m = $meta[$key];

        $summary = [];
        $dist = [];
        $table2x2 = null;
        $note = null;

        if ($m['type'] === 'num') {
            // Buckets: cut-off klinis + quartile fallback
            $vals = $rows->pluck($key)->filter()->map(fn($v) => (float)$v)->values();
            $n = $vals->count();

            if ($n > 0) {
                // cut-off klinis contoh (IMT & SBP/DBP)
                $cuts = match ($key) {
                    'imt' => [18.5, 25, 30],     // <18.5, 18.5-24.9, 25-29.9, ≥30
                    'sbp' => [120, 140],         // <120, 120–139, ≥140
                    'dbp' => [80, 90],
                    default => [$vals->quantile(0.25), $vals->quantile(0.5), $vals->quantile(0.75)],
                };

                // bikin bucket label & hitung rate per bucket
                $makeLabel = function ($i, $cut) {
                    if ($i === 0) return "< " . $cut[0];
                    if ($i === count($cut)) return "≥ " . end($cut);
                    return $cut[$i - 1] . "–" . $cut[$i];
                };

                $buckets = [];
                $k = count($cuts) + 1;
                for ($i = 0; $i < $k; $i++) $buckets[$i] = ['label' => $makeLabel($i, $cuts), 'n' => 0, 'y' => 0];

                foreach ($rows as $r) {
                    $x = $r->{$key};
                    if ($x === null) continue;
                    $idx = 0;
                    while ($idx < count($cuts) && $x >= $cuts[$idx]) $idx++;
                    $buckets[$idx]['n']++;
                    $buckets[$idx]['y'] += (int)$r->y;
                }

                $dist = collect($buckets)->map(function ($b) {
                    $rate = $b['n'] ? round($b['y'] / $b['n'] * 100, 1) : 0;
                    return $b + ['rate' => $rate];
                })->values()->all();

                $overall = $rows->count() ? round($rows->avg('y') * 100, 1) : 0;
                $best = collect($dist)->sortByDesc('rate')->first();
                $summary = [
                    'overall_rate' => $overall,
                    'best_bucket' => $best['label'] ?? null,
                    'best_rate' => $best['rate'] ?? 0,
                    'n_total' => $rows->count(),
                ];
            }
        } else {
            // kategori → 2x2 OR + 95% CI (plus = semua selain 'Negatif' dan 'Belum dilakukan Pemeriksaan')
            $a = $b = $c = $d = 0;
            foreach ($rows as $r) {
                if ($r->prot === null) continue;
                $plus = ($r->prot !== 'Negatif' && $r->prot !== 'Belum dilakukan Pemeriksaan');
                if ($plus && $r->y == 1) $a++;
                elseif ($plus && $r->y == 0) $b++;
                elseif (!$plus && $r->y == 1) $c++;
                else $d++;
            }
            $or = ($b * $c) > 0 ? ($a * $d) / ($b * $c) : null;

            // CI log(OR) ~ N( log(OR), SE ), SE = sqrt(1/a+1/b+1/c+1/d)
            if ($or && $a > 0 && $b > 0 && $c > 0 && $d > 0) {
                $se = sqrt(1 / $a + 1 / $b + 1 / $c + 1 / $d);
                $lo = exp(log($or) - 1.96 * $se);
                $hi = exp(log($or) + 1.96 * $se);
                $table2x2 = compact('a', 'b', 'c', 'd', 'or', 'lo', 'hi');
            } else {
                $table2x2 = compact('a', 'b', 'c', 'd') + ['or' => null, 'lo' => null, 'hi' => null];
            }
        }

        return view('dinkes.analytics.variable', [
            'key' => $key,
            'meta' => $m,
            'dist' => $dist,
            'summary' => $summary,
            'table2x2' => $table2x2,
            'filters' => $request->all(),
        ]);
    }


    public function export(Request $request): StreamedResponse
    {
        $filename = 'delisa_analytics_export_' . now()->format('Ymd_His') . '.csv';

        // Pakai streamDownload supaya header & alur download rapi
        return response()->streamDownload(function () use ($request) {
            // Pastikan tidak ada buffer sisa
            if (function_exists('ob_get_level')) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
            }

            $out = fopen('php://output', 'w');

            // (Opsional) BOM agar Excel Windows baca UTF-8 dengan benar
            // fwrite($out, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($out, [
                'pid_hash',
                'kecamatan',
                'age',
                'imt',
                'sbp',
                'dbp',
                'protein_urine',
                'hadir',
                'outcome'
            ]);

            // Jalankan stream baris demi baris
            try {
                // Pastikan outcome default ada
                $request->merge([
                    'outcome' => $request->string('outcome', 'pe')->toString(),
                ]);

                $this->streamRows($request, function ($r) use ($out) {
                    $pidHash = hash('sha256', config('app.key') . '|' . $r->pid);

                    fputcsv($out, [
                        $pidHash,
                        $r->PKecamatan,
                        $r->age ?? null,
                        $r->imt ?? null,
                        $r->sbp ?? null,
                        $r->dbp ?? null,
                        $r->protein_urine ?? null,
                        $r->hadir_bin ?? null,
                        $r->y ?? null,
                    ]);

                    // dorong buffer ke client secara bertahap
                    if (function_exists('flush')) {
                        flush();
                    }
                });
            } catch (\Throwable $e) {
                // Tulis 1 baris error agar respons tetap valid CSV (tidak bikin ERR_INVALID_RESPONSE)
                fputcsv($out, ['ERROR', $e->getMessage()]);
            } finally {
                fclose($out);
            }
        }, $filename, [
            'Content-Type'              => 'text/csv; charset=UTF-8',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate',
            'Pragma'                    => 'no-cache',
            'X-Content-Type-Options'    => 'nosniff',
        ]);
    }


    // Streaming helper (untuk export)
    public function streamRows(Request $request, \Closure $each)
    {
        $oc = $request->string('outcome', 'pe')->toString();

        $q = DB::table('pasiens as p')
            ->leftJoin('skrinings as s', 's.pasien_id', '=', 'p.id')
            ->leftJoin('kondisi_kesehatans as k', 'k.skrining_id', '=', 's.id')
            ->selectRaw("
                p.id as pid,
                p.\"PKecamatan\" as PKecamatan,
                p.tanggal_lahir,
                COALESCE(k.imt, NULL)::float as imt,
                COALESCE(k.sdp, NULL)::float as sbp,
                COALESCE(k.dbp, NULL)::float as dbp,
                k.pemeriksaan_protein_urine as protein_urine,
                CASE WHEN s.checked_status IS TRUE THEN 1 ELSE 0 END as hadir_bin,
                CASE
                  WHEN ? = 'pe'
                    THEN CASE WHEN (COALESCE(s.jumlah_resiko_tinggi,0)>0 OR COALESCE(s.jumlah_resiko_sedang,0)>0) THEN 1 ELSE 0 END
                  WHEN ? = 'dirujuk'
                    THEN CASE WHEN EXISTS (
                           SELECT 1 FROM rujukan_rs rr
                           WHERE rr.skrining_id = s.id AND COALESCE(rr.is_rujuk, false) = true
                         ) THEN 1 ELSE 0 END
                  WHEN ? = 'meninggal'
                    THEN 0
                  ELSE 0
                END as y
            ", [$oc, $oc, $oc])
            ->when($request->date('from'), fn($q, $v) => $q->whereDate('s.created_at', '>=', $v))
            ->when($request->date('to'),   fn($q, $v) => $q->whereDate('s.created_at', '<=', $v))
            // benar:
            ->when($request->filled('kec'), function ($q) use ($request) {
                $q->whereRaw('p."PKecamatan" ILIKE ?', ['%' . $request->string('kec')->toString() . '%']);
            })->orderBy('p.id');

        $q->chunk(1000, function ($chunk) use ($each) {
            foreach ($chunk as $r) {
                $r->age = null;
                if (!empty($r->tanggal_lahir)) {
                    try {
                        $r->age = now()->diffInYears(\Carbon\Carbon::parse($r->tanggal_lahir));
                    } catch (\Throwable $e) {
                    }
                }
                $each($r);
            }
        });
    }
}
