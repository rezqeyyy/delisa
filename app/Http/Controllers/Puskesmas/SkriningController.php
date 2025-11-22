<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Skrining;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

class SkriningController extends Controller
{
    use SkriningHelpers;

    public function index(Request $request)
    {
        $userId = optional(Auth::user())->id;

        $ps = DB::table('puskesmas')
            ->select('id','kecamatan')
            ->where('user_id', $userId)
            ->first();

        $puskesmasId = optional($ps)->id;
        $kecamatan   = optional($ps)->kecamatan;

        $skrinings = Skrining::query()
            ->with(['pasien.user'])
            ->when($puskesmasId || $kecamatan, function ($q) use ($puskesmasId, $kecamatan) {
                $q->where(function ($w) use ($puskesmasId, $kecamatan) {
                    if ($puskesmasId) {
                        $w->orWhere('puskesmas_id', $puskesmasId);
                    }
                    if ($kecamatan) {
                        $w->orWhereHas('pasien', function ($ww) use ($kecamatan) {
                            $ww->where('PKecamatan', $kecamatan);
                        });
                    }
                });
            })
            ->latest()
            ->get();

        $skrinings = $skrinings->filter(function ($s) {
            return $this->isSkriningCompleteForSkrining($s);
        })->values();

        $skrinings->transform(function ($s) {
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
            return $s;
        });

        return view('puskesmas.skrining.index', compact('skrinings'));
    }

    public function show(Skrining $skrining)
    {
        $userId = optional(Auth::user())->id;
        $ps = DB::table('puskesmas')->select('id','kecamatan')->where('user_id', $userId)->first();
        abort_unless($ps, 404);

        $kecPasien = optional($skrining->pasien)->PKecamatan;
        $allowed = (($skrining->puskesmas_id === $ps->id) || ($kecPasien === $ps->kecamatan));
        abort_unless($allowed, 403);
        abort_unless($this->isSkriningCompleteForSkrining($skrining), 404);

        $resikoSedang = (int)($skrining->jumlah_resiko_sedang ?? 0);
        $resikoTinggi = (int)($skrining->jumlah_resiko_tinggi ?? 0);
        $conclusion = ($resikoTinggi >= 1 || $resikoSedang >= 2) ? 'Berisiko preeklampsia' : 'Tidak berisiko preeklampsia';
        $key = strtolower(trim($conclusion));
        $badgeClasses = [
            'berisiko preeklampsia' => 'bg-red-600 text-white',
            'tidak berisiko preeklampsia' => 'bg-green-500 text-white',
            'skrining belum selesai' => 'bg-gray-200 text-gray-900',
        ];
        $cls = $badgeClasses[$key] ?? 'bg-[#E9E9E9] text-[#1D1D1D]';

        $kk = optional($skrining->kondisiKesehatan);
        $gpa = optional($skrining->riwayatKehamilanGpa);

        $sebabSedang = [];
        $sebabTinggi = [];

        $umur = null;
        try { $tgl = optional($skrining->pasien)->tanggal_lahir; if ($tgl) { $umur = \Carbon\Carbon::parse($tgl)->age; } } catch (\Throwable $e) { $umur = null; }
        if ($umur !== null && $umur >= 35) { $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)"; }
        if ($gpa && intval($gpa->total_kehamilan) === 1) { $sebabSedang[] = 'Primigravida (G=1)'; }
        if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) { $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)'; }
        $sistol = $kk->sdp ?? null; $diastol = $kk->dbp ?? null; if (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90)) { $sebabTinggi[] = 'Tekanan darah di atas 130/90 mHg'; }

        $preModerateNames = [
            'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
            'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
            'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
            'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
        ];
        $preModerateLabels = [
            $preModerateNames[0] => 'Kehamilan kedua/lebih bukan dengan suami pertama',
            $preModerateNames[1] => 'Teknologi reproduksi berbantu',
            $preModerateNames[2] => 'Jarak 10 tahun dari kehamilan sebelumnya',
            $preModerateNames[3] => 'Riwayat keluarga preeklampsia',
        ];
        $preKuisModerate = DB::table('kuisioner_pasiens')
            ->where('status_soal','pre_eklampsia')
            ->whereIn('nama_pertanyaan',$preModerateNames)
            ->get(['id','nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');
        $preJawabModerate = DB::table('jawaban_kuisioners')
            ->where('skrining_id',$skrining->id)
            ->whereIn('kuisioner_id',$preKuisModerate->pluck('id')->all())
            ->get(['kuisioner_id','jawaban'])
            ->keyBy('kuisioner_id');
        foreach ($preModerateNames as $nm) { $id = optional($preKuisModerate->get($nm))->id; if ($id && (bool) optional($preJawabModerate->get($id))->jawaban) { $sebabSedang[] = $preModerateLabels[$nm] ?? $nm; } }

        $preHighNames = [
            'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
            'Apakah kehamilan anda saat ini adalah kehamilan kembar',
            'Apakah anda memiliki diabetes dalam masa kehamilan',
            'Apakah anda memiliki penyakit ginjal',
            'Apakah anda memiliki penyakit autoimun, SLE',
            'Apakah anda memiliki penyakit Anti Phospholipid Syndrome',
        ];
        $preHighLabels = [
            $preHighNames[0] => 'Riwayat preeklampsia sebelumnya',
            $preHighNames[1] => 'Kehamilan kembar',
            $preHighNames[2] => 'Diabetes dalam kehamilan',
            $preHighNames[3] => 'Penyakit ginjal',
            $preHighNames[4] => 'Penyakit autoimun (SLE)',
            $preHighNames[5] => 'Anti Phospholipid Syndrome',
        ];
        $preKuisHigh = DB::table('kuisioner_pasiens')
            ->where('status_soal','pre_eklampsia')
            ->whereIn('nama_pertanyaan',$preHighNames)
            ->get(['id','nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');
        $preJawabHigh = DB::table('jawaban_kuisioners')
            ->where('skrining_id',$skrining->id)
            ->whereIn('kuisioner_id',$preKuisHigh->pluck('id')->all())
            ->get(['kuisioner_id','jawaban'])
            ->keyBy('kuisioner_id');
        foreach ($preHighNames as $nm) { $id = optional($preKuisHigh->get($nm))->id; if ($id && (bool) optional($preJawabHigh->get($id))->jawaban) { $sebabTinggi[] = $preHighLabels[$nm] ?? $nm; } }

        $nama    = optional(optional($skrining->pasien)->user)->name ?? '-';
        $nik     = optional($skrining->pasien)->nik ?? '-';
        $tanggal = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
        $alamat  = optional(optional($skrining->pasien)->user)->address ?? '-';
        $telp    = optional(optional($skrining->pasien)->user)->phone ?? '-';

        return view('puskesmas.skrining.show', compact(
            'skrining','nama','nik','tanggal','alamat','telp','conclusion','cls','sebabSedang','sebabTinggi'
        ));
    }
}