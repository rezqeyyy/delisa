<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Models\Bidan;
use Illuminate\Support\Carbon; 
use Illuminate\Support\Facades\DB;

class SkriningController extends Controller
{
    /**
     * Menampilkan list skrining untuk bidan.
     */
    public function index()
    {
        // ... (Kode method index() kamu) ...
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }
        $puskesmasId = $bidan->puskesmas_id;
        $skrinings = Skrining::where('puskesmas_id', $puskesmasId)
                            ->with(['pasien.user'])
                            ->latest()
                            ->paginate(10);

        $skrinings->getCollection()->transform(function ($s) {
            $label = strtolower(trim($s->kesimpulan ?? ''));
            $isRisk = in_array($label, ['beresiko','berisiko','risiko tinggi','tinggi']);
            $isWarn = in_array($label, ['waspada','menengah','sedang','risiko sedang']);
            $display = $isRisk ? 'Beresiko' : ($isWarn ? 'Waspada' : ($label === 'aman' ? 'Aman' : ($s->kesimpulan ?? 'Normal')));
            $variant = $isRisk ? 'risk' : ($isWarn ? 'warn' : 'safe');
            $s->setAttribute('conclusion_display', $display);
            $s->setAttribute('badge_variant', $variant);
            return $s;
        });

        return view('bidan.skrining.index', compact('skrinings'));
    }

    /**
     * Menampilkan halaman detail skrining.
     */
    public function show(Skrining $skrining) // <-- UBAH METHOD INI
    {
        // Pastikan bidan ini boleh melihat skrining ini
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            abort(404);
        }

        // Eager load semua relasi yang dibutuhkan untuk tabel detail
        $skrining->load(['pasien.user', 'kondisiKesehatan', 'riwayatKehamilanGpa']);

        // Tampilkan view detailnya (file baru di langkah 6)
        $riwayatPenyakitPasien = DB::table('jawaban_kuisioners as j')
            ->join('kuisioner_pasiens as k','k.id','=','j.kuisioner_id')
            ->where('j.skrining_id', $skrining->id)
            ->where('k.status_soal','individu')
            ->where('j.jawaban', true)
            ->select('k.nama_pertanyaan','j.jawaban_lainnya')
            ->get()
            ->map(fn($r) => ($r->nama_pertanyaan === 'Lainnya' && $r->jawaban_lainnya) ? ('Lainnya: '.$r->jawaban_lainnya) : $r->nama_pertanyaan)
            ->values()->all();

        $riwayatPenyakitKeluarga = DB::table('jawaban_kuisioners as j')
            ->join('kuisioner_pasiens as k','k.id','=','j.kuisioner_id')
            ->where('j.skrining_id', $skrining->id)
            ->where('k.status_soal','keluarga')
            ->where('j.jawaban', true)
            ->select('k.nama_pertanyaan','j.jawaban_lainnya')
            ->get()
            ->map(fn($r) => ($r->nama_pertanyaan === 'Lainnya' && $r->jawaban_lainnya) ? ('Lainnya: '.$r->jawaban_lainnya) : $r->nama_pertanyaan)
            ->values()->all();

        $kk  = optional($skrining->kondisiKesehatan);
        $gpa = optional($skrining->riwayatKehamilanGpa);

        $sebabSedang = [];
        $sebabTinggi = [];

        $umur = null;
        try { $tgl = optional($skrining->pasien)->tanggal_lahir; if ($tgl) { $umur = Carbon::parse($tgl)->age; } } catch (\Throwable $e) { $umur = null; }
        if ($umur !== null && $umur >= 35) { $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)"; }
        if ($gpa && intval($gpa->total_kehamilan) === 1) { $sebabSedang[] = 'Primigravida (G=1)'; }
        if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) { $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)'; }

        $sistol = $kk->sdp ?? null; $diastol = $kk->dbp ?? null;
        if (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90)) { $sebabTinggi[] = 'Tekanan darah di atas 130/90 mHg'; }

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

        $sebabSedang = array_values(array_unique($sebabSedang));
        $sebabTinggi = array_values(array_unique($sebabTinggi));

        return view('bidan.skrining.show', compact('skrining','riwayatPenyakitPasien','riwayatPenyakitKeluarga','sebabSedang','sebabTinggi'));
    }

    /**
     * Update status skrining menjadi "checked" (AJAX).
     */
    public function markAsViewed(Request $request, Skrining $skrining)
    {
        // ... (Kode method markAsViewed() kamu) ...
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $skrining->update(['checked_status' => true]);
        return response()->json([
            'message' => 'Status updated successfully',
            'redirect_url' => route('bidan.skrining.show', $skrining->id)
        ]);
    }

    // --- TAMBAHKAN METHOD BARU INI ---

    /**
     * Update status "tindak_lanjut" skrining (Tombol "Sudah Diperiksa").
     */
    public function followUp(Request $request, Skrining $skrining)
    {
        // Pastikan bidan ini boleh update
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            abort(403);
        }

        // Update status "tindak_lanjut"
        $skrining->update(['tindak_lanjut' => true]);

        // Redirect kembali ke halaman detail dengan pesan sukses
        return redirect()->route('bidan.skrining.show', $skrining->id)
                         ->with('success', 'Skrining telah ditandai selesai diperiksa.');
    }
}