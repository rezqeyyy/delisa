<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Models\RujukanRs;
use App\Models\Bidan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;
use Barryvdh\DomPDF\Facade\Pdf;

/*
|--------------------------------------------------------------------------
| SKRINING CONTROLLER
|--------------------------------------------------------------------------
| Fungsi: Mengelola data skrining preeklampsia pasien
| Fitur: List skrining, detail skrining, mark as viewed, follow up
|--------------------------------------------------------------------------
*/

class SkriningController extends Controller
{
    use SkriningHelpers;
    /*
    |--------------------------------------------------------------------------
    | METHOD: index()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan daftar semua skrining di puskesmas bidan
    | Return: View 'bidan.skrining.index' dengan data paginated
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }
        $puskesmasId = $bidan->puskesmas_id;
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $skrinings = Skrining::query()
            ->where('puskesmas_id', $puskesmasId)
            ->whereHas('puskesmas', function ($q) { $q->where('is_mandiri', true); })
            ->where('step_form', 6)
            ->when($q !== '', function ($query) use ($q) {
                return $query->where(function ($sub) use ($q) {
                    $sub->whereHas('pasien', function ($q1) use ($q) {
                        $q1->where('nik', 'like', '%' . $q . '%');
                    })->orWhereHas('pasien.user', function ($q1) use ($q) {
                        $q1->where('name', 'like', '%' . $q . '%');
                    });
                });
            })
            ->when(in_array($status, ['normal','risk']), function ($query) use ($status) {
                $map = [
                    'risk' => ['beresiko','berisiko','risiko tinggi','tinggi'],
                    'normal' => ['aman','normal','tidak berisiko','waspada','menengah','sedang','risiko sedang'],
                ];
                $query->whereIn(DB::raw('LOWER(kesimpulan)'), $map[$status]);
            })
            ->when($from, fn($q3) => $q3->whereDate('created_at', '>=', $from))
            ->when($to, fn($q3) => $q3->whereDate('created_at', '<=', $to))
            ->with(['pasien.user', 'kondisiKesehatan', 'riwayatKehamilanGpa'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $skrinings->setCollection(
            $skrinings->getCollection()->map(function ($s) {
                $label = strtolower(trim($s->kesimpulan ?? ''));
                $isRisk = in_array($label, ['beresiko','berisiko','risiko tinggi','tinggi']);
                $display = $isRisk ? 'Beresiko' : 'Normal';
                $variant = $isRisk ? 'risk' : 'normal';
                $s->setAttribute('conclusion_display', $display);
                $s->setAttribute('badge_variant', $variant);
                return $s;
            })
        );

        if ($skrinings->lastPage() >= 1 && $skrinings->currentPage() > $skrinings->lastPage()) {
            return redirect($skrinings->url($skrinings->lastPage()));
        }

        return view('bidan.skrining.index', compact('skrinings'));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: show()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan detail lengkap skrining pasien
    | Parameter: $skrining (Model Binding otomatis dari route)
    | Return: View 'bidan.skrining.show' dengan data detail
    |--------------------------------------------------------------------------
    */
    public function show(Skrining $skrining)
    {
        // 1. Validasi Akses Bidan
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id; // ID puskesmas bidan
        if ($skrining->puskesmas_id != $bidanPuskesmasId) { // Cek apakah skrining dari puskesmas bidan
            abort(404); // Jika tidak, tampilkan 404 Not Found
        }

        // 2. Load Relasi yang Dibutuhkan
        // Eager load relasi untuk hindari N+1 query
        $skrining->load([
            'pasien.user',           // Data pasien & user (nama, NIK, telp)
            'kondisiKesehatan',      // Data kesehatan (IMT, tekanan darah, MAP)
            'riwayatKehamilanGpa'    // Data GPA (Gravida, Para, Abortus)
        ]);

        // 3. Ambil Riwayat Penyakit Pasien (Individu)
        // Join tabel jawaban_kuisioners dengan kuisioner_pasiens
        $riwayatPenyakitPasien = DB::table('jawaban_kuisioners as j')
            ->join('kuisioner_pasiens as k','k.id','=','j.kuisioner_id') // Join dengan tabel kuisioner
            ->where('j.skrining_id', $skrining->id) // Filter per skrining ini
            ->where('k.status_soal','individu') // Hanya soal individu
            ->where('j.jawaban', true) // Hanya yang dijawab YA
            ->select('k.nama_pertanyaan','j.jawaban_lainnya') // Ambil nama pertanyaan & jawaban lainnya
            ->get()
            ->map(fn($r) => // Transform setiap row
                ($r->nama_pertanyaan === 'Lainnya' && $r->jawaban_lainnya) ? // Jika "Lainnya" dan ada isian
                ('Lainnya: '.$r->jawaban_lainnya) : // Gabungkan "Lainnya: [isian]"
                $r->nama_pertanyaan // Jika bukan, pakai nama pertanyaan
            )
            ->values()->all(); // Convert ke array biasa

        // 4. Ambil Riwayat Penyakit Keluarga
        // Sama seperti di atas, tapi status_soal = 'keluarga'
        $riwayatPenyakitKeluarga = DB::table('jawaban_kuisioners as j')
            ->join('kuisioner_pasiens as k','k.id','=','j.kuisioner_id')
            ->where('j.skrining_id', $skrining->id)
            ->where('k.status_soal','keluarga') // Hanya soal keluarga
            ->where('j.jawaban', true)
            ->select('k.nama_pertanyaan','j.jawaban_lainnya')
            ->get()
            ->map(fn($r) =>
                ($r->nama_pertanyaan === 'Lainnya' && $r->jawaban_lainnya) ?
                ('Lainnya: '.$r->jawaban_lainnya) :
                $r->nama_pertanyaan
            )
            ->values()->all();

        // 5. Setup Variable Kondisi Kesehatan & GPA
        $kk  = optional($skrining->kondisiKesehatan); // optional(): cegah error jika null
        $gpa = optional($skrining->riwayatKehamilanGpa);

        // 6. Inisialisasi Array Penyebab Risiko
        $sebabSedang = []; // Array untuk penyebab risiko sedang
        $sebabTinggi = []; // Array untuk penyebab risiko tinggi

        // 7. Cek Risiko Sedang dari Umur (≥35 tahun)
        $umur = null; // Inisialisasi umur
        try {
            $tgl = optional($skrining->pasien)->tanggal_lahir; // Ambil tanggal lahir
            if ($tgl) {
                $umur = Carbon::parse($tgl)->age; // Hitung umur dari tanggal lahir
            }
        } catch (\Throwable $e) {
            $umur = null; // Jika error, set null
        }
        
        if ($umur !== null && $umur >= 35) { // Jika umur ≥ 35 tahun
            $sebabSedang[] = "Usia ibu {$umur} tahun (≥35)"; // Tambah ke array risiko sedang
        }

        // 8. Cek Risiko Sedang dari Primigravida (G=1)
        if ($gpa && intval($gpa->total_kehamilan) === 1) { // Jika kehamilan pertama
            $sebabSedang[] = 'Primigravida (G=1)'; // Tambah ke risiko sedang
        }

        // 9. Cek Risiko Sedang dari IMT (>30)
        if ($kk && $kk->imt !== null && floatval($kk->imt) > 30) { // Jika IMT > 30 (obesitas)
            $sebabSedang[] = 'IMT ' . number_format(floatval($kk->imt), 2) . ' kg/m² (>30)';
        }

        // 10. Cek Risiko Tinggi dari Tekanan Darah (≥130/90)
        $sistol = $kk->sdp ?? null; // Systolic Blood Pressure
        $diastol = $kk->dbp ?? null; // Diastolic Blood Pressure
        
        if (($sistol !== null && $sistol >= 130) || // Sistol ≥ 130 ATAU
            ($diastol !== null && $diastol >= 90)) { // Diastol ≥ 90
            $sebabTinggi[] = 'Tekanan darah di atas 130/90 mmHg'; // Tambah ke risiko tinggi
        }

        // 11. Cek Risiko Sedang dari Kuisioner Pre-Eklampsia
        // Define pertanyaan yang menjadi indikator risiko sedang
        $preModerateNames = [
            'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
            'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
            'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
            'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
        ];
        
        // Define label yang lebih pendek untuk ditampilkan
        $preModerateLabels = [
            $preModerateNames[0] => 'Kehamilan kedua/lebih bukan dengan suami pertama',
            $preModerateNames[1] => 'Teknologi reproduksi berbantu',
            $preModerateNames[2] => 'Jarak 10 tahun dari kehamilan sebelumnya',
            $preModerateNames[3] => 'Riwayat keluarga preeklampsia',
        ];
        
        // Ambil data kuisioner yang sesuai
        $preKuisModerate = DB::table('kuisioner_pasiens')
            ->where('status_soal','pre_eklampsia') // Filter kuisioner pre-eklampsia
            ->whereIn('nama_pertanyaan',$preModerateNames) // Filter pertanyaan risiko sedang
            ->get(['id','nama_pertanyaan']) // Ambil id & nama
            ->keyBy('nama_pertanyaan'); // Index by nama pertanyaan
        
        // Ambil jawaban pasien untuk kuisioner tsb
        $preJawabModerate = DB::table('jawaban_kuisioners')
            ->where('skrining_id',$skrining->id) // Filter per skrining ini
            ->whereIn('kuisioner_id',$preKuisModerate->pluck('id')->all()) // Filter per kuisioner
            ->get(['kuisioner_id','jawaban']) // Ambil id & jawaban
            ->keyBy('kuisioner_id'); // Index by kuisioner_id
        
        // Loop setiap pertanyaan, cek apakah dijawab YA
        foreach ($preModerateNames as $nm) {
            $id = optional($preKuisModerate->get($nm))->id; // Ambil ID kuisioner
            if ($id && (bool) optional($preJawabModerate->get($id))->jawaban) { // Jika dijawab YA
                $sebabSedang[] = $preModerateLabels[$nm] ?? $nm; // Tambah ke risiko sedang
            }
        }

        // 12. Cek Risiko Tinggi dari Kuisioner Pre-Eklampsia
        // Sama seperti di atas, tapi untuk risiko tinggi
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
        
        foreach ($preHighNames as $nm) {
            $id = optional($preKuisHigh->get($nm))->id;
            if ($id && (bool) optional($preJawabHigh->get($id))->jawaban) {
                $sebabTinggi[] = $preHighLabels[$nm] ?? $nm;
            }
        }

        // 13. Hapus Duplikasi & Reset Index Array
$sebabSedang = array_values(array_unique($sebabSedang));
$sebabTinggi = array_values(array_unique($sebabTinggi));

// 14. Status Rujukan yang Diterima RS
$acceptedRujukan = \App\Models\RujukanRs::with('rumahSakit')
    ->where('skrining_id', $skrining->id)
    ->where('done_status', true)
    ->where('is_rujuk', true)
    ->orderByDesc('created_at')
    ->first();
$rujukanAccepted = (bool) $acceptedRujukan;
$rujukanRsName   = optional(optional($acceptedRujukan)->rumahSakit)->nama;

// 15. Kirim ke View
return view('bidan.skrining.show', compact(
    'skrining',
    'riwayatPenyakitPasien',
    'riwayatPenyakitKeluarga',
    'sebabSedang',
    'sebabTinggi',
    'rujukanAccepted',
    'rujukanRsName'
));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: markAsViewed()
    |--------------------------------------------------------------------------
    | Fungsi: Update status skrining menjadi "sudah dilihat" (via AJAX)
    | Parameter: $skrining (Model Binding)
    | Return: JSON response untuk redirect
    |--------------------------------------------------------------------------
    */
    public function markAsViewed(Request $request, Skrining $skrining)
    {
        // 1. Validasi Akses Bidan
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) { // Cek kepemilikan data
            return response()->json(['message' => 'Unauthorized'], 403); // Return error JSON
        }
        
        // 2. Update Status Checked
        $skrining->update(['checked_status' => true]); // Set checked_status = true
        
        // 3. Return JSON untuk Redirect
        return response()->json([
            'message' => 'Status updated successfully',
            'redirect_url' => route('bidan.skrining.show', $skrining->id) // URL detail skrining
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: followUp()
    |--------------------------------------------------------------------------
    | Fungsi: Tandai skrining sebagai "sudah diperiksa" oleh bidan
    | Parameter: $skrining (Model Binding)
    | Return: Redirect ke halaman detail dengan pesan sukses
    |--------------------------------------------------------------------------
    */
    public function followUp(Request $request, Skrining $skrining)
    {
        // 1. Validasi Akses Bidan
        $bidanPuskesmasId = Auth::user()->bidan->puskesmas_id;
        if ($skrining->puskesmas_id != $bidanPuskesmasId) {
            abort(403); // Forbidden jika bukan puskesmas bidan
        }

        // 2. Update Status Tindak Lanjut
        $skrining->update(['tindak_lanjut' => true]); // Set tindak_lanjut = true

        // 3. Redirect dengan Flash Message
        return redirect()->route('bidan.skrining.show', $skrining->id) // Redirect ke detail
                         ->with('success', 'Skrining telah ditandai selesai diperiksa.'); // Flash message
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: exportExcel()
    |--------------------------------------------------------------------------
    | Fungsi: Ekspor data skrining ibu hamil ke file CSV
    | Return: Stream response CSV dengan header yang sesuai
    |--------------------------------------------------------------------------
    */
    public function exportExcel(Request $request)
    {
        $bidan = Auth::user()->bidan;
        abort_unless($bidan, 403);
        $puskesmasId = $bidan->puskesmas_id;
        $facilityName = optional($bidan->puskesmas)->nama_puskesmas ?? 'Puskesmas';
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $skrinings = Skrining::query()
            ->where('puskesmas_id', $puskesmasId)
            ->whereHas('puskesmas', function ($q2) { $q2->where('is_mandiri', true); })
            ->where('step_form', 6)
            ->when($q !== '', function ($query) use ($q) {
                return $query->where(function ($sub) use ($q) {
                    $sub->whereHas('pasien', function ($q1) use ($q) {
                        $q1->where('nik', 'like', '%' . $q . '%');
                    })->orWhereHas('pasien.user', function ($q1) use ($q) {
                        $q1->where('name', 'like', '%' . $q . '%');
                    });
                });
            })
            ->when(in_array($status, ['normal','risk']), function ($query) use ($status) {
                $map = [
                    'risk' => ['beresiko','berisiko','risiko tinggi','tinggi'],
                    'normal' => ['aman','normal','tidak berisiko','waspada','menengah','sedang','risiko sedang'],
                ];
                $query->whereIn(DB::raw('LOWER(kesimpulan)'), $map[$status]);
            })
            ->when($from, fn($q3) => $q3->whereDate('created_at', '>=', $from))
            ->when($to, fn($q3) => $q3->whereDate('created_at', '<=', $to))
            ->with(['pasien.user'])
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn($s) => $this->isSkriningCompleteForSkrining($s))
            ->values();

        $fileName = 'data-skrining-ibu-hamil-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($skrinings) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No.', 'Nama Pasien', 'NIK', 'Tanggal Pengisian', 'Alamat', 'No Telp', 'Kesimpulan']);
            foreach ($skrinings as $index => $s) {
                $nama    = optional(optional($s->pasien)->user)->name ?? '-';
                $nik     = optional($s->pasien)->nik ?? '-';
                $tanggal = $s->created_at ? $s->created_at->format('d/m/Y') : '-';
                $alamat  = optional($s->pasien)->PKecamatan ?? optional($s->pasien)->PWilayah ?? '-';
                $telp    = optional(optional($s->pasien)->user)->phone ?? '-';
                $sedang  = (int)($s->jumlah_resiko_sedang ?? 0);
                $tinggi  = (int)($s->jumlah_resiko_tinggi ?? 0);
                $kesimpulan = ($tinggi >= 1 || $sedang >= 2) ? 'Beresiko' : 'Normal';
                fputcsv($file, [
                    $index + 1,
                    $nama,
                    '="' . $nik . '"',
                    $tanggal,
                    $alamat,
                    '="' . $telp . '"',
                    $kesimpulan,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: exportPDF()
    |--------------------------------------------------------------------------
    | Fungsi: Ekspor data skrining ibu hamil ke PDF (layout landscape)
    | Return: Download file PDF berisi daftar skrining
    |--------------------------------------------------------------------------
    */
    public function exportPDF(Request $request)
    {
        $bidan = Auth::user()->bidan;
        abort_unless($bidan, 403);
        $puskesmasId = $bidan->puskesmas_id;
        $facilityName = optional($bidan->puskesmas)->nama_puskesmas ?? 'Puskesmas';

        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $skrinings = Skrining::query()
            ->where('puskesmas_id', $puskesmasId)
            ->whereHas('puskesmas', function ($q2) { $q2->where('is_mandiri', true); })
            ->where('step_form', 6)
            ->when($q !== '', function ($query) use ($q) {
                return $query->where(function ($sub) use ($q) {
                    $sub->whereHas('pasien', function ($q1) use ($q) {
                        $q1->where('nik', 'like', '%' . $q . '%');
                    })->orWhereHas('pasien.user', function ($q1) use ($q) {
                        $q1->where('name', 'like', '%' . $q . '%');
                    });
                });
            })
            ->when(in_array($status, ['normal','risk']), function ($query) use ($status) {
                $map = [
                    'risk' => ['beresiko','berisiko','risiko tinggi','tinggi'],
                    'normal' => ['aman','normal','tidak berisiko','waspada','menengah','sedang','risiko sedang'],
                ];
                $query->whereIn(DB::raw('LOWER(kesimpulan)'), $map[$status]);
            })
            ->when($from, fn($q3) => $q3->whereDate('created_at', '>=', $from))
            ->when($to, fn($q3) => $q3->whereDate('created_at', '<=', $to))
            ->with(['pasien.user'])
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn($s) => $this->isSkriningCompleteForSkrining($s))
            ->values();

        $skrinings->transform(function ($s) {
            $sedang = (int)($s->jumlah_resiko_sedang ?? 0);
            $tinggi = (int)($s->jumlah_resiko_tinggi ?? 0);
            if ($tinggi >= 1 || $sedang >= 2) {
                $s->kesimpulan  = 'Beresiko';
                $s->badge_class = 'beresiko';
            } else {
                $s->kesimpulan  = 'Normal';
                $s->badge_class = 'normal';
            }
            return $s;
        });

        if ($skrinings->isEmpty()) {
            return back()->with('error', 'Tidak ada data skrining yang dapat diekspor.');
        }

        $pdf = Pdf::loadView('bidan.skrining.export-pdf', compact('skrinings', 'facilityName'))
            ->setPaper('a4', 'landscape')
            ->setOption('defaultFont', 'Arial')
            ->setOption('isRemoteEnabled', true);

        $fileName = 'data-skrining-ibu-hamil-' . date('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }
}    

/*
|--------------------------------------------------------------------------
| PENJELASAN FUNGSI-FUNGSI:
|--------------------------------------------------------------------------
|
| 1. Model Binding
|    - Parameter method otomatis diisi Laravel
|    - show(Skrining $skrining) -> Laravel cari skrining by ID dari URL
|    - Jika tidak ada -> 404 otomatis
|
| 2. with(['relasi'])
|    - Eager loading untuk load relasi
|    - with(['pasien.user']) -> load pasien, lalu load user dari pasien
|    - Hindari N+1 query problem
|
| 3. latest()
|    - orderBy('created_at', 'desc')
|    - Data terbaru di atas
|
| 4. paginate(n)
|    - Bagi hasil query jadi beberapa halaman
|    - n = jumlah data per halaman
|    - Return: LengthAwarePaginator object
|
| 5. getCollection()
|    - Ambil Collection dari Paginator
|    - Untuk manipulasi data paginated
|
| 6. transform(function)
|    - Modifikasi setiap item di Collection
|    - Ubah Collection in-place (tidak return baru)
|
| 7. setAttribute('key', 'value')
|    - Tambah attribute baru ke Model
|    - Attribute tidak disave ke database
|    - Hanya untuk passing data ke view
|
| 8. optional($var)
|    - Helper Laravel untuk cegah error null
|    - optional($x)->prop -> return null jika $x null
|    - Tanpa optional: $x->prop -> error jika $x null
|
| 9. keyBy('column')
|    - Index Collection by kolom tertentu
|    - Ubah array biasa jadi associative array
|    - Contoh: [['id'=>1], ['id'=>2]] -> [1=>['id'=>1], 2=>['id'=>2]]
|
| 10. array_unique($array)
|     - Hapus nilai duplikat dari array
|     - Return array dengan nilai unik
|
| 11. array_values($array)
|     - Reset index array jadi 0,1,2,3...
|     - Setelah array_unique, index bisa acak
|
| 12. response()->json($data, $code)
|     - Return response JSON
|     - $data = array/object yang di-encode jadi JSON
|     - $code = HTTP status code (200, 403, 404, dll)
|
| 13. with('key', 'value')
|     - Flash message ke session
|     - Data hanya ada untuk 1 request berikutnya
|     - Diakses di view dengan session('key')
|
|--------------------------------------------------------------------------
*/