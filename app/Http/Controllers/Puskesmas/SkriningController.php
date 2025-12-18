<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Skrining;
use App\Models\RumahSakit;
use App\Models\RujukanRs;
use App\Http\Controllers\Pasien\Skrining\Concerns\SkriningHelpers;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Controller untuk menangani proses skrining pasien.
 * Meliputi daftar skrining, detail skrining, pencarian rumah sakit, pengajuan rujukan, dan ekspor data.
 */
class SkriningController extends Controller
{
    // Catatan: Menggunakan trait SkriningHelpers untuk fungsi-fungsi bantu.
    use SkriningHelpers;

    private function resolvePuskesmasContext(?int $userId)
    {
        if (!$userId) return null;

        // 1) Normal: user memang “pemilik” row puskesmas (puskesmas.user_id = users.id)
        $ps = DB::table('puskesmas')
            ->select('id', 'kecamatan', 'is_mandiri')
            ->where('user_id', $userId)
            ->first();

        if ($ps) return $ps;

        // 2) Bidan: user punya row di bidans, dan bidans.puskesmas_id menunjuk puskesmas (mandiri atau nebeng)
        return DB::table('bidans')
            ->join('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
            ->where('bidans.user_id', $userId)
            ->select('puskesmas.id', 'puskesmas.kecamatan', 'puskesmas.is_mandiri')
            ->first();
    }

    /**
     * Menghapus data skrining.
     *
     * @param Skrining $skrining
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Skrining $skrining)
    {
        $skrining->delete();

        return redirect()->back()->with('success', 'Data skrining berhasil dihapus.');
    }


    private function applyWilayahFilter($query, $ps)
    {
        $kecamatan = trim((string) ($ps->kecamatan ?? ''));

        return $query->where(function ($w) use ($ps, $kecamatan) {
            // 1) Skrining yang dilakukan di faskes ini (puskesmas user)
            $w->where('puskesmas_id', $ps->id);

            // 2) Skrining yang dilakukan di faskes manapun yang kecamatannya sama
            //    (ini otomatis mencakup klinik mandiri karena mereka juga row di tabel puskesmas)
            if ($kecamatan !== '') {
                $w->orWhereHas('puskesmas', function ($q) use ($kecamatan) {
                    $q->whereRaw('LOWER("kecamatan") = LOWER(?)', [$kecamatan]);
                });
            }
        });
    }



    /**
     * Menampilkan daftar skrining yang telah lengkap beserta informasi pasien.
     *
     * @param Request $request Request HTTP yang masuk.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = trim($request->search);

        $ps = $this->resolvePuskesmasContext(optional($user)->id);
        if (!$ps) {
            return view('puskesmas.skrining.index', ['skrinings' => collect()]);
        }


        $query = Skrining::query()
            ->with(['pasien.user', 'puskesmas'])
            ->whereNotNull('status_pre_eklampsia');

        // 🔍 SEARCH (Nama, NIK, No Telp)
        if ($search) {
            $query->whereHas('pasien', function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'ilike', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // 🔒 Filter wilayah (berdasarkan TEMPAT skrining / faskes), BUKAN domisili pasien
        $this->applyWilayahFilter($query, $ps);

        // DEBUG STEP 0: pastikan puskesmas & kecamatan kebaca
        Log::info('DEBUG skrining.index: puskesmas context', [
            'user_id' => optional($user)->id,
            'puskesmas_id' => optional($ps)->id,
            'kecamatan_puskesmas' => optional($ps)->kecamatan,
            'is_mandiri' => optional($ps)->is_mandiri,
        ]);

        // DEBUG STEP 1: cek SQL dan bindings query utama
        Log::info('DEBUG skrining.index: base query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        // DEBUG STEP 2: hitung jumlah sebelum complete-filter
        Log::info('DEBUG skrining.index: count before complete', [
            'count' => (clone $query)->count(),
        ]);

        // DEBUG STEP 3: ambil sample kecil untuk lihat apa yang nyangkut
        $sample = (clone $query)->latest()->take(5)->get(['id', 'pasien_id', 'puskesmas_id', 'status_pre_eklampsia', 'created_at']);
        Log::info('DEBUG skrining.index: sample rows', $sample->toArray());


        // ✅ Untuk puskesmas: cukup pastikan skrining sudah punya status_pre_eklampsia
        $skrinings = (clone $query)
            ->latest()
            ->paginate(10);

        $skrinings->getCollection()->transform(function ($s) {
            $resikoSedang = (int) $s->jumlah_resiko_sedang;
            $resikoTinggi = (int) $s->jumlah_resiko_tinggi;

            if ($resikoTinggi >= 1 || $resikoSedang >= 2) {
                $s->conclusion_display = 'Berisiko';
                $s->badge_class = 'bg-red-600 text-white';
            } else {
                $s->conclusion_display = 'Normal';
                $s->badge_class = 'bg-green-500 text-white';
            }

            return $s;
        });

        $skrinings->appends($request->except('page'));

        return view('puskesmas.skrining.index', compact('skrinings'));
    }


    /**
     * Menampilkan detail skrining berdasarkan ID beserta informasi pasien, faktor risiko, dan status rujukan.
     *
     * @param Skrining $skrining Model skrining yang diambil berdasarkan ID.
     * @return \Illuminate\Contracts\View\View
     */

    public function show(Skrining $skrining)
    {
        // Catatan: Mendapatkan ID pengguna yang sedang login.
        $userId = optional(Auth::user())->id;
        $ps = $this->resolvePuskesmasContext($userId);
        abort_unless($ps, 404);

        // Catatan: Ambil kecamatan pasien & faskes (puskesmas/bidan) untuk validasi akses.
        $kecPasienRaw = optional($skrining->pasien)->PKecamatan;
        $kecPasien    = mb_strtolower(trim((string) $kecPasienRaw));
        $kecFaskesRaw = optional($skrining->puskesmas)->kecamatan;
        $isBidanMandiri = optional($skrining->puskesmas)->is_mandiri ?? false;
        // Catatan: Cek apakah skrining milik puskesmas ini atau berada di kecamatan yang sama (pasien ATAU faskes tempat skrining).
        $kecPuskesmas = mb_strtolower(trim((string) ($ps->kecamatan ?? '')));
        $kecFaskes    = mb_strtolower(trim((string) (optional($skrining->puskesmas)->kecamatan ?? '')));

        $allowed = (
            ($skrining->puskesmas_id === $ps->id)
            || ($kecFaskes !== '' && $kecPuskesmas !== '' && $kecFaskes === $kecPuskesmas)
        );
        abort_unless($allowed, 403);

        // Catatan: Jika tidak diizinkan, kembalikan error 403 (Forbidden).
        abort_unless($allowed, 403);
        // Catatan: Jika skrining belum lengkap, kembalikan error 404.
        abort_unless(!is_null($skrining->status_pre_eklampsia), 404);        // Catatan: Hitung jumlah risiko untuk menentukan kesimpulan & status berisiko/tidak.
        $resikoSedang = (int)($skrining->jumlah_resiko_sedang ?? 0);
        $resikoTinggi = (int)($skrining->jumlah_resiko_tinggi ?? 0);

        $isBerisiko = ($resikoTinggi >= 1 || $resikoSedang >= 2);
        $conclusion = $isBerisiko ? 'Berisiko preeklampsia' : 'Tidak berisiko preeklampsia';

        $key = strtolower(trim($conclusion));
        $badgeClasses = [
            'berisiko preeklampsia' => 'bg-red-600 text-white',
            'tidak berisiko preeklampsia' => 'bg-green-500 text-white',
            'skrining belum selesai' => 'bg-gray-200 text-gray-900',
        ];
        $cls = $badgeClasses[$key] ?? 'bg-[#E9E9E9] text-[#1D1D1D]';

        // Debug: log kondisi risiko & kesimpulan
        Log::info('Puskesmas.SkriningController@show', [
            'skrining_id'        => $skrining->id,
            'resiko_sedang'      => $resikoSedang,
            'resiko_tinggi'      => $resikoTinggi,
            'is_berisiko'        => $isBerisiko,
            'conclusion_display' => $conclusion,
            'is_bidan_mandiri'   => $isBidanMandiri,
            'kecamatan_pasien'   => $kecPasienRaw,
            'kecamatan_puskesmas' => $ps->kecamatan,
        ]);

        // Catatan: Load relasi-relasi yang dibutuhkan untuk tampilan detail.
        $skrining->load(['pasien.user', 'kondisiKesehatan', 'riwayatKehamilanGpa', 'puskesmas']);

        // Catatan: Ambil data kondisi kesehatan dan G-P-A (Gravida-Para-Abortus).
        $kk = optional($skrining->kondisiKesehatan);
        $gpa = optional($skrining->riwayatKehamilanGpa);

        // Catatan: Inisialisasi array untuk faktor risiko sedang dan tinggi.
        $sebabSedang = [];
        $sebabTinggi = [];

        // Catatan: Ambil dan hitung usia ibu.
        $umur = null;
        try {
            $tgl = optional($skrining->pasien)->tanggal_lahir;
            if ($tgl) {
                $umur = \Carbon\Carbon::parse($tgl)->age;
            }
        } catch (\Throwable $e) {
            $umur = null;
        }
        if ($umur !== null && $umur >= 35) {
            $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)";
        }

        // Catatan: Cek apakah primigravida (kehamilan pertama).
        if ($gpa && intval($gpa->total_kehamilan) === 1) {
            $sebabSedang[] = 'Primigravida (G=1)';
        }

        // Catatan: Cek apakah IMT tinggi.
        if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) {
            $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)';
        }

        // Catatan: Cek apakah tekanan darah tinggi.
        $sistol = $kk->sdp ?? null;
        $diastol = $kk->dbp ?? null;
        if (($sistol !== null && $sistol >= 130) || ($diastol !== null && $diastol >= 90)) {
            $sebabTinggi[] = 'Tekanan darah di atas 130/90 mHg';
        }

        // Catatan: Daftar pertanyaan kuisioner risiko sedang.
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

        // Catatan: Ambil ID kuisioner risiko sedang dari database.
        $preKuisModerate = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preModerateNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        // Catatan: Ambil jawaban kuisioner risiko sedang dari database.
        $preJawabModerate = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $preKuisModerate->pluck('id')->all())
            ->get(['kuisioner_id', 'jawaban'])
            ->keyBy('kuisioner_id');

        // Catatan: Tambahkan faktor risiko sedang berdasarkan jawaban.
        foreach ($preModerateNames as $nm) {
            $id = optional($preKuisModerate->get($nm))->id;
            if ($id && (bool) optional($preJawabModerate->get($id))->jawaban) {
                $sebabSedang[] = $preModerateLabels[$nm] ?? $nm;
            }
        }

        // Catatan: Daftar pertanyaan kuisioner risiko tinggi.
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

        // Catatan: Ambil ID kuisioner risiko tinggi dari database.
        $preKuisHigh = DB::table('kuisioner_pasiens')
            ->where('status_soal', 'pre_eklampsia')
            ->whereIn('nama_pertanyaan', $preHighNames)
            ->get(['id', 'nama_pertanyaan'])
            ->keyBy('nama_pertanyaan');

        // Catatan: Ambil jawaban kuisioner risiko tinggi dari database.
        $preJawabHigh = DB::table('jawaban_kuisioners')
            ->where('skrining_id', $skrining->id)
            ->whereIn('kuisioner_id', $preKuisHigh->pluck('id')->all())
            ->get(['kuisioner_id', 'jawaban'])
            ->keyBy('kuisioner_id');

        // Catatan: Tambahkan faktor risiko tinggi berdasarkan jawaban.
        foreach ($preHighNames as $nm) {
            $id = optional($preKuisHigh->get($nm))->id;
            if ($id && (bool) optional($preJawabHigh->get($id))->jawaban) {
                $sebabTinggi[] = $preHighLabels[$nm] ?? $nm;
            }
        }

        // Catatan: Ambil riwayat penyakit pribadi dari jawaban kuisioner individu.
        $riwayatPenyakitPasien = DB::table('jawaban_kuisioners as j')
            ->join('kuisioner_pasiens as k', 'k.id', '=', 'j.kuisioner_id')
            ->where('j.skrining_id', $skrining->id)
            ->where('k.status_soal', 'individu')
            ->where('j.jawaban', true)
            ->select('k.nama_pertanyaan', 'j.jawaban_lainnya')
            ->get()
            ->map(fn($r) => !empty($r->jawaban_lainnya) ? ('Lainnya: ' . $r->jawaban_lainnya) : $r->nama_pertanyaan)
            ->values()->all();

        // Catatan: Ambil riwayat penyakit keluarga dari jawaban kuisioner keluarga.
        $riwayatPenyakitKeluarga = DB::table('jawaban_kuisioners as j')
            ->join('kuisioner_pasiens as k', 'k.id', '=', 'j.kuisioner_id')
            ->where('j.skrining_id', $skrining->id)
            ->where('k.status_soal', 'keluarga')
            ->where('j.jawaban', true)
            ->select('k.nama_pertanyaan', 'j.jawaban_lainnya')
            ->get()
            ->map(function ($r) {
                // Jika pertanyaan adalah "Lainnya" dan ada jawaban custom, tampilkan "Lainnya: <jawaban>"
                if (strtolower($r->nama_pertanyaan) === 'lainnya' && !empty($r->jawaban_lainnya)) {
                    return 'Lainnya: ' . $r->jawaban_lainnya;
                }
                // Jika pertanyaan bukan "Lainnya", tapi ada jawaban_lainnya (walaupun jarang terjadi di logic normal, handle saja)
                if (!empty($r->jawaban_lainnya)) {
                    return $r->nama_pertanyaan . ': ' . $r->jawaban_lainnya;
                }
                // Default: nama pertanyaan
                return $r->nama_pertanyaan;
            })
            ->values()->all();

        // Catatan: Ambil data pasien untuk ditampilkan.
        $nama    = optional(optional($skrining->pasien)->user)->name ?? '-';
        $nik     = optional($skrining->pasien)->nik ?? '-';
        $tanggal = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
        $alamat  = optional(optional($skrining->pasien)->user)->address ?? '-';
        $telp    = optional(optional($skrining->pasien)->user)->phone ?? '-';

        // Catatan: Cek apakah sudah ada rujukan AKTIF untuk skrining ini
        // (done_status = 0, is_rujuk = 1 → status "menunggu")
        $hasReferral = DB::table('rujukan_rs')
            ->where('skrining_id', $skrining->id)
            ->where('is_rujuk', 1)
            ->where('done_status', 0)
            ->exists();

        // 🔧 FIX ERROR: definisikan variabel sebelum dikirim ke view
        $kecamatan_pasien = $kecPasienRaw ?? null;
        $kecamatan_puskesmas = $ps->kecamatan ?? null;


        // Catatan: Kirim data ke view untuk ditampilkan.
        return view('puskesmas.skrining.show', compact(
            'skrining',
            'nama',
            'nik',
            'tanggal',
            'alamat',
            'telp',
            'conclusion',
            'cls',
            'sebabSedang',
            'sebabTinggi',
            'hasReferral',
            'riwayatPenyakitPasien',
            'riwayatPenyakitKeluarga',
            'isBerisiko',
            'isBidanMandiri',
            'kecamatan_pasien',
            'kecamatan_puskesmas' // ✅ penting untuk Blade agar hanya pasien berisiko yang bisa rujuk
        ));
    }


    /**
     * Endpoint AJAX untuk mencari rumah sakit berdasarkan input pencarian.
     *
     * @param Request $request Request yang berisi parameter pencarian 'q'.
     * @return \Illuminate\Http\JsonResponse
     */
    public function rsSearch(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $query  = DB::table('rumah_sakits');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kecamatan', 'like', "%{$search}%")
                        ->orWhere('kelurahan', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%");
                });
            }

            $data = $query
                ->select(
                    'id',
                    'nama',
                    DB::raw('lokasi as alamat'),
                    'kecamatan',
                    'kelurahan'
                )
                ->orderBy('nama')
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }



    /**
     * Mengajukan rujukan skrining ke rumah sakit tertentu.
     *
     * @param Request $request Request yang berisi ID rumah sakit.
     * @param Skrining $skrining Model skrining yang akan dirujuk.
     * @return \Illuminate\Http\RedirectResponse
     */

    public function rujuk(Request $request, Skrining $skrining)
    {
        // Catatan: Mendapatkan ID pengguna yang sedang login.
        $userId = optional(Auth::user())->id;
        $ps = $this->resolvePuskesmasContext($userId);
        abort_unless($ps, 404);

        // Catatan: Cek apakah skrining milik puskesmas ini, berada di kecamatan yang sama, atau dari bidan mandiri di kecamatan yang sama.
        $kecPuskesmas = mb_strtolower(trim((string) ($ps->kecamatan ?? '')));
        $kecFaskes    = mb_strtolower(trim((string) (optional($skrining->puskesmas)->kecamatan ?? '')));

        $allowed = (
            ($skrining->puskesmas_id === $ps->id)
            || ($kecFaskes !== '' && $kecPuskesmas !== '' && $kecFaskes === $kecPuskesmas)
        );
        abort_unless($allowed, 403);


        // Catatan: Jika tidak diizinkan, kembalikan error 403 (Forbidden).
        abort_unless($allowed, 403);

        // Catatan: Pastikan skrining sudah lengkap sebelum dirujuk.
        abort_unless(!is_null($skrining->status_pre_eklampsia), 404);

        // Catatan: Validasi input ID rumah sakit.
        $validated = $request->validate([
            'rs_id' => 'required|exists:rumah_sakits,id',
        ]);

        // Catatan: Cegah duplikasi rujukan untuk skrining yang sama.
        $already = RujukanRs::where('skrining_id', $skrining->id)->exists();
        if ($already) {
            return redirect()->route('puskesmas.skrining.show', $skrining->id)
                ->with('status', 'Rujukan sudah diajukan untuk skrining ini.');
        }

        // Catatan: Buat rujukan baru di database.
        RujukanRs::create([
            'pasien_id'   => $skrining->pasien_id,
            'rs_id'       => $validated['rs_id'],
            'skrining_id' => $skrining->id,
            'is_rujuk'    => true, // Menandakan bahwa ini adalah rujukan aktif.
            'done_status' => false, // Belum selesai.
        ]);

        // Catatan: Redirect kembali ke halaman detail skrining dengan pesan sukses.
        return redirect()->route('puskesmas.skrining.show', $skrining->id)
            ->with('status', 'Permintaan rujukan dikirim ke rumah sakit.');
    }
    /**
     * Memverifikasi skrining oleh petugas puskesmas (mengubah checked_status menjadi true).
     *
     * @param Request $request
     * @param Skrining $skrining
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, Skrining $skrining)
    {
        // Catatan: Mendapatkan ID pengguna yang sedang login.
        $userId = optional(Auth::user())->id;
        $ps = $this->resolvePuskesmasContext($userId);
        abort_unless($ps, 404);

        // Catatan: Cek apakah skrining milik puskesmas ini, berada di kecamatan yang sama, atau dari bidan mandiri di kecamatan yang sama.
        $kecPuskesmas = mb_strtolower(trim((string) ($ps->kecamatan ?? '')));
        $kecFaskes    = mb_strtolower(trim((string) (optional($skrining->puskesmas)->kecamatan ?? '')));

        $allowed = (
            ($skrining->puskesmas_id === $ps->id)
            || ($kecFaskes !== '' && $kecPuskesmas !== '' && $kecFaskes === $kecPuskesmas)
        );
        abort_unless($allowed, 403);


        // Catatan: Jika tidak diizinkan, kembalikan error 403 (Forbidden).
        abort_unless($allowed, 403);

        // Catatan: Pastikan skrining sudah lengkap sebelum diverifikasi.
        abort_unless(!is_null($skrining->status_pre_eklampsia), 404);

        // Catatan: Jika sudah diverifikasi sebelumnya, tidak perlu diulang.
        if ($skrining->checked_status) {
            return redirect()
                ->route('puskesmas.skrining.show', $skrining->id)
                ->with('status', 'Skrining sudah diverifikasi sebelumnya.');
        }

        // Catatan: Set checked_status menjadi true dan simpan.
        $skrining->checked_status = true;
        $skrining->save();

        return redirect()
            ->route('puskesmas.skrining.show', $skrining->id)
            ->with('success', 'Skrining berhasil diverifikasi.');
    }

    /**
     * Export data skrining ke format CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportExcel()
    {
        try {
            // Catatan: Mendapatkan ID pengguna yang sedang login.
            $userId = optional(Auth::user())->id;

            // Catatan: Mengambil data puskesmas milik pengguna.
            $ps = $this->resolvePuskesmasContext($userId);


            // Catatan: Mengambil ID dan kecamatan puskesmas.
            $puskesmasId = optional($ps)->id;
            $kecamatan   = optional($ps)->kecamatan;

            // Catatan: Ambil data skrining beserta relasi pasien, user, dan puskesmas.
            $skrinings = Skrining::query()
                ->with(['pasien.user', 'puskesmas'])
                // Catatan: Menyaring skrining berdasarkan ID puskesmas, kecamatan pasien, atau bidan mandiri.
                ->when($puskesmasId || $kecamatan, function ($q) use ($puskesmasId, $kecamatan) {
                    $q->where(function ($w) use ($puskesmasId, $kecamatan) {
                        if ($puskesmasId) {
                            $w->orWhere('puskesmas_id', $puskesmasId);
                        }
                        if ($kecamatan) {
                            $w->orWhereHas('pasien', function ($ww) use ($kecamatan) {
                                $ww->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatan]);
                            })
                                ->orWhereHas('puskesmas', function ($wp) use ($kecamatan) {
                                    $wp->whereRaw('LOWER("puskesmas"."kecamatan") = LOWER(?)', [$kecamatan]);
                                })
                                ->orWhere(function ($sub) use ($kecamatan) {
                                    $sub->whereHas('puskesmas', function ($wp) {
                                        $wp->where('is_mandiri', true);
                                    })
                                        ->whereHas('pasien', function ($wp) use ($kecamatan) {
                                            $wp->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatan]);
                                        });
                                });
                        }
                    });
                })
                ->latest()
                ->get();

            // Catatan: Filter hanya skrining yang sudah lengkap.
            $skrinings = $skrinings->filter(function ($s) {
                return !is_null($s->status_pre_eklampsia);
            })->values();


            // Catatan: Nama file CSV yang akan diunduh.
            $fileName = 'data-skrining-ibu-hamil-' . date('Y-m-d') . '.csv';

            // Catatan: Header HTTP untuk memicu download file.
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            // Catatan: Callback untuk menulis data ke output stream.
            $callback = function () use ($skrinings) {
                $file = fopen('php://output', 'w');

                // Catatan: Tambahkan BOM (Byte Order Mark) untuk UTF-8 agar tampilan di Excel benar.
                fwrite($file, "\xEF\xBB\xBF");

                // Catatan: Header CSV.
                fputcsv($file, ['No.', 'Nama Pasien', 'NIK', 'Tanggal Pengisian', 'Alamat', 'No Telp', 'Kesimpulan']);

                // Catatan: Data CSV.
                foreach ($skrinings as $index => $skrining) {
                    $nama = optional(optional($skrining->pasien)->user)->name ?? '-';
                    $nik = optional($skrining->pasien)->nik ?? '-';
                    $tanggal = $skrining->created_at ? $skrining->created_at->format('d/m/Y') : '-';
                    $alamat = optional(optional($skrining->pasien)->user)->address ?? '-';
                    $telp = optional(optional($skrining->pasien)->user)->phone ?? '-';

                    $resikoSedang = (int)($skrining->jumlah_resiko_sedang ?? 0);
                    $resikoTinggi = (int)($skrining->jumlah_resiko_tinggi ?? 0);

                    // Catatan: Menentukan kesimpulan berdasarkan jumlah risiko.
                    if ($resikoTinggi >= 1 || $resikoSedang >= 2) {
                        $kesimpulan = 'Berisiko preeklampsia';
                    } else {
                        $kesimpulan = 'Tidak berisiko preeklampsia';
                    }

                    // Catatan: Tulis baris data ke file CSV.
                    fputcsv($file, [
                        $index + 1,
                        $nama,
                        $nik,
                        $tanggal,
                        $alamat,
                        $telp,
                        $kesimpulan
                    ]);
                }

                fclose($file);
            };

            // Catatan: Kembalikan response stream untuk download file.
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            // Catatan: Log error jika terjadi exception.
            Log::error('Export Error: ' . $e->getMessage());
            // Catatan: Kembali ke halaman sebelumnya dengan pesan error.
            return back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Export data skrining ke format PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPDF()
    {
        try {
            // Catatan: Mendapatkan ID pengguna yang sedang login.
            $userId = optional(Auth::user())->id;

            // Catatan: Mengambil data puskesmas milik pengguna.
            $ps = $this->resolvePuskesmasContext($userId);

            // Catatan: Mengambil ID dan kecamatan puskesmas.
            $puskesmasId = optional($ps)->id;
            $kecamatan   = optional($ps)->kecamatan;

            // Catatan: Ambil data skrining beserta relasi pasien, user, dan puskesmas.
            $skrinings = Skrining::query()
                ->with(['pasien.user', 'puskesmas'])
                // Catatan: Menyaring skrining berdasarkan ID puskesmas, kecamatan pasien, atau bidan mandiri.
                ->when($puskesmasId || $kecamatan, function ($q) use ($puskesmasId, $kecamatan) {
                    $q->where(function ($w) use ($puskesmasId, $kecamatan) {
                        if ($puskesmasId) {
                            $w->orWhere('puskesmas_id', $puskesmasId);
                        }
                        if ($kecamatan) {
                            $w->orWhereHas('pasien', function ($ww) use ($kecamatan) {
                                $ww->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatan]);
                            })
                                ->orWhereHas('puskesmas', function ($wp) use ($kecamatan) {
                                    $wp->whereRaw('LOWER("puskesmas"."kecamatan") = LOWER(?)', [$kecamatan]);
                                })
                                ->orWhere(function ($sub) use ($kecamatan) {
                                    $sub->whereHas('puskesmas', function ($wp) {
                                        $wp->where('is_mandiri', true);
                                    })
                                        ->whereHas('pasien', function ($wp) use ($kecamatan) {
                                            $wp->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatan]);
                                        });
                                });
                        }
                    });
                })
                ->latest()
                ->get();

            // Catatan: Filter hanya skrining yang sudah lengkap.
            $skrinings = $skrinings->filter(function ($s) {
                return !is_null($s->status_pre_eklampsia);
            })->values();

            // Catatan: Transformasi data untuk menambahkan informasi kesimpulan.
            $skrinings->transform(function ($s) {
                $resikoSedang = (int)($s->jumlah_resiko_sedang ?? 0);
                $resikoTinggi = (int)($s->jumlah_resiko_tinggi ?? 0);

                $isComplete = !is_null($s->status_pre_eklampsia);
                // Catatan: Menentukan kesimpulan berdasarkan jumlah risiko.
                if (!$isComplete) {
                    $s->kesimpulan = 'Skrining belum selesai';
                    $s->badge_class = 'skrining-belum-selesai';
                } elseif ($resikoTinggi >= 1 || $resikoSedang >= 2) {
                    $s->kesimpulan = 'Berisiko preeklampsia';
                    $s->badge_class = 'berisiko';
                } else {
                    $s->kesimpulan = 'Tidak berisiko preeklampsia';
                    $s->badge_class = 'tidak-berisiko';
                }

                return $s;
            });

            // Debug: Cek apakah ada data
            if ($skrinings->isEmpty()) {
                return back()->with('error', 'Tidak ada data skrining yang dapat diekspor.');
            }

            // Catatan: Generate PDF
            $pdf = Pdf::loadView('puskesmas.skrining.export-pdf', compact('skrinings'))
                ->setPaper('a4', 'landscape')
                ->setOption('defaultFont', 'Arial')
                ->setOption('isRemoteEnabled', true);

            $fileName = 'data-skrining-ibu-hamil-' . date('Y-m-d') . '.pdf';

            // Catatan: Download PDF
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            // Catatan: Log error jika terjadi exception.
            Log::error('PDF Export Error: ' . $e->getMessage());
            Log::error('PDF Export Trace: ' . $e->getTraceAsString());

            // Catatan: Kembali ke halaman sebelumnya dengan pesan error.
            return back()->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }
}
