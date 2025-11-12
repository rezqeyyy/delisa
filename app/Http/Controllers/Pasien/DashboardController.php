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

    /**
     * Halaman dashboard pasien.
     * - Menampilkan daftar skrining pasien (paginate)
     * - Mendukung filter berdasarkan dropdown status (Normal/Waspada/Berisiko)
     * - Menghitung ringkasan total skrining dan status preeklamsia terbaru
     */
    public function index(Request $request)
    {
        // Ambil nilai filter status dari query string, kosong jika tidak ada
        // Nilai yang dikirim dari dropdown: "Normal", "Waspada", "Berisiko"
        $status = trim($request->get('status', ''));

        // Pasien ID dari user yang login (bisa null jika data relasi belum lengkap)
        $pasienId = optional(Auth::user()->pasien)->id;

        /**
         * Normalisasi alias untuk filter "kesimpulan" dan "status_pre_eklampsia".
         * Tujuannya: mengakomodasi variasi penyimpanan nilai di DB.
         *
         * - Kesimpulan bisa beragam: "Berisiko"/"Beresiko", "Normal"/"Aman"/"Tidak berisiko",
         *   serta "Waspada"/"Waspadai".
         * - Status preeklampsia: "Normal" / "Risiko Sedang" / "Risiko Tinggi".
         * Mapping di bawah menyetarakan dropdown → nilai-nilai yang ekuivalen di DB.
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

        // Query utama daftar skrining milik pasien
        $skrinings = Skrining::where('pasien_id', $pasienId)
            // Terapkan filter jika status tidak kosong
            ->when($status !== '', function ($q) use ($status, $kesimpulanAliases, $preeklampsiaAliases) {
                $kesVals = $kesimpulanAliases[$status] ?? [$status];
                $preVals = $preeklampsiaAliases[$status] ?? [$status];

                $q->where(function ($w) use ($kesVals, $preVals) {
                    // Cocokkan ke salah satu alias kesimpulan ATAU ke salah satu alias status preeklampsia
                    $w->whereIn('kesimpulan', $kesVals)
                      ->orWhereIn('status_pre_eklampsia', $preVals);
                });
            })
            ->with(['pasien.user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /**
         * Transformasi untuk kebutuhan tampilan:
         * - Menghasilkan "conclusion_display" (teks kesimpulan yang rapi/terstandar)
         * - Menentukan "badge_class" untuk warna label di tabel
         *
         * Rumus kesimpulan BERDASARKAN KELENGKAPAN DATA lintas halaman skrining:
         * - Jika tidak lengkap → "Skrining belum selesai"
         * - Jika jumlah_resiko_tinggi >= 1 atau jumlah_resiko_sedang >= 2 → "Berisiko"
         * - Jika jumlah_resiko_sedang >= 1 → "Waspada"
         * - Selain itu → "Tidak berisiko"
         */
        $skrinings->getCollection()->transform(function ($s) {
            $resikoSedang = (int)($s->jumlah_resiko_sedang ?? 0);
            $resikoTinggi = (int)($s->jumlah_resiko_tinggi ?? 0);

            $isComplete = $this->isSkriningCompleteForSkrining($s);

            if (!$isComplete) {
                $conclusion = 'Skrining belum selesai';
            } elseif ($resikoTinggi >= 1 || $resikoSedang >= 2) {
                $conclusion = 'Berisiko';
            } elseif ($resikoSedang >= 1) {
                $conclusion = 'Waspada';
            } else {
                $conclusion = 'Tidak berisiko';
            }

            // Key lowercase untuk mencari kelas badge
            $key = strtolower(trim($conclusion));

            // Pemetaan kelas badge (warna) termasuk beberapa alias ejaan
            $badgeClasses = [
                'berisiko'               => 'bg-red-600 text-white',
                'beresiko'               => 'bg-red-600 text-white',
                'waspada'                => 'bg-yellow-500 text-white',
                'tidak berisiko'         => 'bg-green-500 text-white',
                'tidak beresiko'         => 'bg-green-500 text-white',
                'aman'                   => 'bg-green-500 text-white',
                'normal'                 => 'bg-green-500 text-white',
                'skrining belum selesai' => 'bg-gray-200 text-gray-900',
            ];

            // Simpan properti tambahan untuk dipakai view
            $s->conclusion_display = $conclusion;
            $s->badge_class        = $badgeClasses[$key] ?? 'bg-[#E9E9E9] text-[#1D1D1D]';
            return $s;
        });

        /**
         * Ringkasan total skrining:
         * - totalAll: semua entri skrining
         * - totalSelesai: yang data-nya lengkap lintas halaman
         * - totalBelum: sisanya
         */
        $allSkrinings = Skrining::where('pasien_id', $pasienId)
            ->with(['pasien.user'])
            ->get();

        $totalAll     = $allSkrinings->count();
        $totalSelesai = $allSkrinings->filter(fn ($s) => $this->isSkriningCompleteForSkrining($s))->count();
        $totalBelum   = max(0, $totalAll - $totalSelesai);

        /**
         * Status Preeklampsia terbaru:
         * - Ambil dari skrining terbaru yang LENGKAP
         * - Jika kolom status_pre_eklampsia kosong, fallback ke kesimpulan
         */
        $latestComplete = $allSkrinings
            ->filter(fn ($s) => $this->isSkriningCompleteForSkrining($s))
            ->sortByDesc('created_at')
            ->first();

        $riskPreeklampsia = null;
        if ($latestComplete) {
            $riskPreeklampsia = $latestComplete->status_pre_eklampsia ?: $latestComplete->kesimpulan;
        }

        // Tentukan warna box berdasarkan nilai (ikut alias)
        $riskLower   = strtolower($riskPreeklampsia ?? '');
        $riskBoxClass = match ($riskLower) {
            'berisiko', 'beresiko', 'risiko tinggi', 'resiko tinggi' => 'bg-red-600 text-white',
            'waspada', 'risiko sedang' => 'bg-yellow-500 text-white',
            'normal', 'aman', 'tidak berisiko', 'tidak beresiko' => 'bg-[#2EDB58] text-white',
            default => 'bg-[#E9E9E9] text-[#1D1D1D]',
        };

        // Kirim data ke view
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