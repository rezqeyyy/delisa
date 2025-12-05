<?php

// Namespace: controller untuk fitur Pasien
namespace App\Http\Controllers\Pasien;

// Mengimpor base Controller Laravel
use App\Http\Controllers\Controller;
// Mengimpor Request untuk akses data dari HTTP request
use Illuminate\Http\Request;
// Mengimpor facade Auth untuk otentikasi pengguna
use Illuminate\Support\Facades\Auth;
// Mengimpor facade DB untuk operasi query builder/transaksi
use Illuminate\Support\Facades\DB;
// Carbon untuk manipulasi tanggal
use Carbon\Carbon;
// Schema untuk deteksi kolom tersedia
use Illuminate\Support\Facades\Schema;

// Mengimpor model Skrining (tabel skrinings).
use App\Models\Skrining;
// Mengimpor model Puskesmas (tabel puskesmas).
use App\Models\Puskesmas;
// Mengimpor trait SkriningHelpers (helper utilitas skrining).
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;



/**
 * Controller Pasien — Skrining
 * Mengelola alur hasil skrining, edit, hapus, dan pencarian fasilitas.
 * Rekomendasi dan status rujukan dihitung di controller.
 */
class SkriningController extends Controller
{
    use SkriningHelpers;    

    /* {{-- ========== SKRINING — SHOW ========== --}} */

    /**
     * Menampilkan halaman hasil skrining pasien.
     * Alur singkat:
     * - Validasi akses milik pasien
     * - Hitung ulang status preeklampsia bila perlu
     * - Susun data tampilan via buildSkriningShowData()
     */
    public function show(Skrining $skrining)
    {
        // $this adalah instance dari SkriningController. authorizeAccess() memastikan
        // skrining yang diakses benar-benar milik pasien yang login (abort 403 jika bukan).
        $this->authorizeAccess($skrining);

        // Jika belum ada kesimpulan sama sekali, arahkan pasien untuk menyelesaikan skrining dulu.
        if (is_null($skrining->kesimpulan) && is_null($skrining->status_pre_eklampsia)) {
            return redirect()
                ->route('pasien.preeklampsia')
                ->with('error', 'Silakan lakukan skrining preeklampsia terlebih dahulu untuk melihat hasil.');
        }

        // Hitung ulang status preeklampsia berdasarkan data terbaru yang tersimpan.
        $this->recalcPreEklampsia($skrining);
        // Susun semua variabel untuk view, termasuk rekomendasi dan status rujukan.
        $data = $this->buildSkriningShowData($skrining);

        return view('pasien.dashboard.skrining-show', $data);
    }

    /* {{-- ========== SKRINING — BUILD SHOW DATA ========== --}} */
    /**
     * Menyusun semua data untuk view skrining-show.
     * Termasuk: identitas, kondisi kesehatan, GPA, ringkasan risiko & pemicu,
     * kesimpulan, rekomendasi, serta status rujukan (badge label & class).
     * Catatan: Logika perhitungan risiko tidak diubah.
     */
    private function buildSkriningShowData(Skrining $skrining): array
    {

        // Relasi utama dari skrining:
        // $pasien: model Pasien pemilik skrining (optional jika relasi belum ada)
        // $kk    : kondisi kesehatan yang tercatat untuk skrining ini
        // $gpa   : riwayat kehamilan (Gravida, Para, Abortus) untuk skrining ini
        $pasien = optional($skrining->pasien);
        $kk     = optional($skrining->kondisiKesehatan);
        $gpa    = optional($skrining->riwayatKehamilanGpa);

        // Identitas dasar; fallback ke data user login bila data pasien belum lengkap
        $nama = $pasien->nama ?? Auth::user()->name ?? '-';
        $nik  = $pasien->nik ?? Auth::user()->nik ?? '-';

        // Ringkasan kondisi saat skrining
        // $tanggal   : tanggal skrining (fallback ke created_at skrining)
        // $berat/$tinggi: data antropometri; $imt dihitung jika belum tersedia di DB
        // $anjuranBb : rekomendasi kenaikan berat badan berdasarkan IMT
        $tanggal  = $kk->tanggal_skrining ?? optional($skrining)->created_at ?? null;
        $berat    = $kk->berat_badan_saat_hamil ?? null;
        $tinggi   = $kk->tinggi_badan ?? null;
        $imt      = $kk->imt ?? (($berat && $tinggi) ? round($berat / pow($tinggi/100, 2), 2) : null);
        $anjuranBb = $kk->anjuran_kenaikan_bb ?? null;

        // Tekanan darah dan turunan
        // $sistol/$diastol: Systolic/Diastolic
        // $map            : Mean Arterial Pressure (rumus: diastol + (sistol - diastol)/3)
        // $proteinUrine   : hasil pemeriksaan protein urin
        $sistol  = $kk->sdp ?? null;
        $diastol = $kk->dbp ?? null;
        $map     = $kk->map ?? (($sistol && $diastol) ? round(($diastol + (($sistol - $diastol) / 3)), 2) : null);
        $proteinUrine = $kk->pemeriksaan_protein_urine ?? null;

        // Kehamilan berjalan
        $usiaKehamilan      = $kk->usia_kehamilan ?? '-';
        $taksiranPersalinan = $kk->tanggal_perkiraan_persalinan ?? null;

        // GPA (Gravida, Para, Abortus)
        $gravida = $gpa->total_kehamilan ?? '-';
        $para    = $gpa->total_persalinan ?? '-';
        $abortus = $gpa->total_abortus ?? '-';

        // Akumulasi pemicu risiko dari hasil skrining
        $resikoSedang = $skrining->jumlah_resiko_sedang ?? 0;
        $resikoTinggi = $skrining->jumlah_resiko_tinggi ?? 0;

        // Cek kelengkapan semua data wajib skrining
        $isComplete = $this->isSkriningCompleteForSkrining($skrining);

        // $kesimpulan: status akhir skrining
        // - "Skrining belum selesai" jika data wajib belum lengkap
        // - "Berisiko Preeklampsia" jika tinggi ≥1 atau sedang ≥2
        // - selain itu dianggap "Tidak berisiko"
        $kesimpulan = (!$isComplete)
            ? 'Skrining belum selesai'
            : (($resikoTinggi >= 1 || $resikoSedang >= 2)
                ? 'Berisiko Preeklampsia'
                : 'Tidak berisiko');

        // $rekomendasi: pesan tindak lanjut default sebelum mempertimbangkan status rujukan RS
        $rekomendasi  = ($resikoTinggi >= 1 || $resikoSedang >= 2)
            ? 'Beresiko preeklampsia. Silahkan untuk menghubungi petugas Puskesmas untuk mendapatkan rujukan Dokter atau Rumah Sakit untuk pengobatan lanjutan.'
            : 'Kondisi normal. Lanjutkan ANC sesuai standar dan ulang skrining di trimester berikutnya.';
        // $catatan: catatan bebas dari petugas / sistem
        $catatan      = $skrining->catatan ?? null;

        // Ambil status rujukan RS untuk skrining ini (record terbaru)
        // done_status=false → menunggu konfirmasi RS; true → rujukan diterima RS
        // rs_nama digunakan untuk menampilkan nama RS pada rekomendasi
        $rujukan = DB::table('rujukan_rs as rr')
            ->leftJoin('rumah_sakits as rs','rs.id','=','rr.rs_id')
            ->select('rs.nama as rs_nama','rr.done_status')
            ->where('rr.skrining_id', $skrining->id)
            ->orderByDesc('rr.created_at')
            ->first();

        // Badge status untuk UI rekomendasi
        $statusRujukanLabel = null;
        $statusRujukanClass = null;

        if (!$isComplete) {
            $rekomendasi = 'Lengkapi skrining terlebih dahulu.';
        } elseif (($resikoTinggi >= 1) || ($resikoSedang >= 2)) {
            $referralRequested = (bool) $rujukan;
            $referralAccepted  = (bool) optional($rujukan)->done_status;
            $referralHospital  = optional($rujukan)->rs_nama;

            $statusRujukanLabel = 'Belum diajukan';
            $statusRujukanClass = 'bg-[#F2F2F2] text-[#7C7C7C]';

            if ($referralRequested && !$referralAccepted) {
                $rekomendasi = 'Rujukan sudah diajukan oleh puskesmas. Menunggu konfirmasi dari rumah sakit.';
                $statusRujukanLabel = 'Menunggu RS';
                $statusRujukanClass = 'bg-[#FEF3C7] text-[#92400E]';
            } elseif ($referralAccepted && $referralHospital) {
                $rekomendasi = 'Rujukan telah diterima oleh ' . $referralHospital . '. Ikuti instruksi dari rumah sakit untuk pemeriksaan lanjutan.';
                $statusRujukanLabel = 'Diterima RS';
                $statusRujukanClass = 'bg-[#D1FAE5] text-[#065F46]';
            }
        }

        // ===== Pemicu risiko =====
        $sebabSedang = [];
        $sebabTinggi = [];

        // Usia ibu
        $umur = null;
        try {
            $tglLahir = optional($skrining->pasien)->tanggal_lahir;
            if ($tglLahir) {
                $umur = Carbon::parse($tglLahir)->age;
            }
        } catch (\Throwable $e) {
            $umur = null;
        }
        if ($umur !== null && $umur >= 35) {
            $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)";
        }

        // Primigravida (G = 1)
        if ($gpa && intval($gpa->total_kehamilan) === 1) {
            $sebabSedang[] = 'Primigravida (G=1)';
        }

        // IMT > 30
        if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) {
            $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)';
        }

        // Tensi ≥ 130/90 mmHg
        if (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90)) {
            $sebabTinggi[] = 'Tekanan darah di atas 130/90 mHg';
        }

        // Risiko sedang dari Preeklampsia (non-overlap, pakai nama tetap)
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
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preModerateNames)
            ->get(['id','nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $preJawabModerate = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $preKuisModerate->pluck('id')->all())
            ->get(['kuisioner_id','jawaban'])
            ->keyBy('kuisioner_id');

        foreach ($preModerateNames as $nm) {
            $id = optional($preKuisModerate->get($nm))->id;
            if ($id && (bool) optional($preJawabModerate->get($id))->jawaban) {
                $sebabSedang[] = $preModerateLabels[$nm] ?? $nm;
            }
        }

        // Risiko tinggi dari Preeklampsia (pakai nama tetap)
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
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preHighNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        $preJawabHigh = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $preKuisHigh->pluck('id')->all())
            ->get(['kuisioner_id', 'jawaban'])
            ->keyBy('kuisioner_id');

        foreach ($preHighNames as $nm) {
            $id = optional($preKuisHigh->get($nm))->id;
            if ($id && (bool) optional($preJawabHigh->get($id))->jawaban) {
                $sebabTinggi[] = $preHighLabels[$nm] ?? $nm;
            }
        }

        $sebabSedang = array_values(array_unique($sebabSedang));
        $sebabTinggi = array_values(array_unique($sebabTinggi));

        return compact(
            'skrining','nama','nik','tanggal','berat','tinggi','imt','anjuranBb',
            'sistol','diastol','map','proteinUrine','usiaKehamilan','taksiranPersalinan',
            'gravida','para','abortus','resikoSedang','resikoTinggi','kesimpulan',
            'rekomendasi','catatan','sebabSedang','sebabTinggi',
            'statusRujukanLabel','statusRujukanClass'
        );
    }

    /* {{-- ========== SKRINING — EDIT ========== --}} */

    /**
     * Mengarahkan ke langkah Data Diri untuk mengedit skrining.
     * Skrining yang sudah diajukan rujukan tidak bisa diedit.
     */
    public function edit(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        $locked = \Illuminate\Support\Facades\DB::table('rujukan_rs')->where('skrining_id', $skrining->id)->exists();
        if ($locked) {
            return redirect()->route('pasien.skrining.show', $skrining->id)
                ->with('error', 'Skrining sudah diajukan rujukan dan tidak dapat diedit.');
        }

        return redirect()->route('pasien.data-diri', ['skrining_id' => $skrining->id]);
    }

    /* {{-- ========== SKRINING — UPDATE ========== --}} */
    
    /**
     * Endpoint update: tidak mengubah data langkah, hanya redirect balik ke halaman hasil (show).
     */
    public function update(Request $request, Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        return redirect()
            ->route('pasien.skrining.show', $skrining->id)
            ->with('ok', 'Puskesmas tetap sama seperti saat pengajuan.');
    }

    /* {{-- ========== SKRINING — DESTROY ========== --}} */
    
    /**
     * Menghapus skrining beserta relasi jawaban, riwayat, dan kondisi.
     * Skrining yang sudah diajukan rujukan tidak dapat dihapus.
     */
    public function destroy(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        $locked = \Illuminate\Support\Facades\DB::table('rujukan_rs')->where('skrining_id', $skrining->id)->exists();
        if ($locked) {
            return redirect()->route('pasien.skrining.show', $skrining->id)
                ->with('error', 'Skrining sudah diajukan rujukan dan tidak dapat dihapus.');
        }

        //DB::transaction -> operasi database yang saling berkaitan dan harus dijalankan secara aman serta konsisten.
        DB::transaction(function () use ($skrining) {
            DB::table('jawaban_kuisioners')->where('skrining_id', $skrining->id)->delete();
            DB::table('riwayat_kehamilans')->where('skrining_id', $skrining->id)->delete();
            DB::table('riwayat_kehamilan_gpas')->where('skrining_id', $skrining->id)->delete();
            DB::table('kondisi_kesehatans')->where('skrining_id', $skrining->id)->delete();

            $skrining->delete();
        });

        return redirect()->route('pasien.dashboard')->with('ok', 'Skrining berhasil dihapus.');
    }

    /* {{-- ========== PUSKESMAS — SEARCH ========== --}} */
    
    /**
     * Pencarian fasilitas (puskesmas/bidan) untuk modal pengajuan rujukan pasien.
     * Input: query string 'q'. Output: list JSON berisi hasil gabungan puskesmas & bidan.
     */
    public function puskesmasSearch(Request $request)
    {
        $qRaw   = (string) $request->query('q', '');
        $qLower = mb_strtolower(trim($qRaw));
        $tokens = array_values(array_filter(preg_split('/\s+/', $qLower, -1, PREG_SPLIT_NO_EMPTY)));

        $typeFilter = null; // 'puskesmas' | 'bidan' | null
        foreach ($tokens as $i => $t) {
            if (in_array($t, ['puskesmas','pkm'], true)) { $typeFilter = 'puskesmas'; unset($tokens[$i]); }
            elseif (in_array($t, ['bidan','klinik'], true)) { $typeFilter = 'bidan'; unset($tokens[$i]); }
        }
        $tokens = array_values($tokens);

        $applyTokensOr = function ($qr, array $columns) use ($tokens, $qLower) {
            $terms = !empty($tokens) ? $tokens : ($qLower !== '' ? [$qLower] : []);
            if (empty($terms)) return;
            $qr->where(function ($w) use ($terms, $columns) {
                foreach ($terms as $t) {
                    foreach ($columns as $col) {
                        $w->orWhereRaw('LOWER(' . $col . ') LIKE ?', ['%' . $t . '%']);
                    }
                }
            });
        };

        // PUSKESMAS
        $puskesmas = collect();
        if ($typeFilter !== 'bidan') {
            $pkmQ = Puskesmas::query()->where('is_mandiri', false);
            $pkmCols = Schema::hasColumn('puskesmas','lokasi') ? ['nama_puskesmas','kecamatan','lokasi'] : ['nama_puskesmas','kecamatan'];
            $applyTokensOr($pkmQ, $pkmCols);
            $puskesmas = $pkmQ
                ->orderBy('nama_puskesmas')
                ->limit(20)
                ->get(['id','nama_puskesmas','kecamatan'])
                ->map(fn($row) => [
                    'id' => $row->id,
                    'nama_puskesmas' => $row->nama_puskesmas,
                    'kecamatan' => $row->kecamatan,
                    'type' => 'puskesmas',
                ]);
        }

        // BIDAN MANDIRI
        $bidan = collect();
        if ($typeFilter !== 'puskesmas') {
            $bdQ = DB::table('bidans as b')
                ->join('users as u', 'u.id', '=', 'b.user_id')
                ->join('puskesmas as p', 'p.id', '=', 'b.puskesmas_id')
                ->where('p.is_mandiri', true);
            $applyTokensOr($bdQ, ['u.name','p.nama_puskesmas','p.kecamatan']);
            $bidan = $bdQ
                ->orderBy('u.name')
                ->limit(20)
                ->get(['b.id as id','u.name as klinik_nama','p.kecamatan as kecamatan','p.id as puskesmas_id'])
                ->map(fn($row) => [
                    'id' => $row->id,
                    'klinik_nama' => $row->klinik_nama,
                    'kecamatan' => $row->kecamatan,
                    'puskesmas_id' => $row->puskesmas_id,
                    'type' => 'bidan',
                ]);
        }

        $combined = $puskesmas->merge($bidan)->take(20)->values();
        return response()->json($combined);
    }

    /* {{-- ========== AUTH — AUTHORIZE ACCESS ========== --}} */
    
    /**
     * Memastikan skrining adalah milik pasien yang sedang login.
     * Aksi: abort 403 bila bukan pemilik.
     */
    private function authorizeAccess(Skrining $skrining): void
    {
        $userPasienId = optional(Auth::user()->pasien)->id;
        abort_unless($skrining->pasien_id === $userPasienId, 403);
    }

    /* {{-- ========== HELPER — GET SKRINING FROM QUERY ========== --}} */
    
    /**
     * Mengambil skrining berdasarkan skrining_id pada query atau skrining terbaru milik pasien.
     * Return: [Skrining|null, int $pasienId]
     */
    private function getSkriningFromQuery($skriningId): array
    {
        $skriningId = (int) $skriningId;
        $user       = Auth::user();
        $pasienId   = optional($user->pasien)->id;

        $skrining = null;
        if ($skriningId) {
            $skrining = Skrining::where('pasien_id', $pasienId)->whereKey($skriningId)->first();
        }
        if (!$skrining && $pasienId) {
            $skrining = Skrining::where('pasien_id', $pasienId)->latest()->first();
        }

        return [$skrining, $pasienId];
    }

}