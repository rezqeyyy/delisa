<?php
// app/Http/Controllers/Puskesmas/SkriningController.php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk menangani proses skrining pasien.
 * Termasuk menampilkan daftar skrining dan detail skrining lengkap dengan fitur rujukan.
 */
class SkriningController extends Controller
{
    // Method untuk list skrining (sudah ada)
    public function index()
    {
        // ... kode existing untuk list skrining
    }

    /**
     * METHOD BARU: Menampilkan detail skrining berdasarkan ID beserta informasi pasien dan status rujukan.
     * Digunakan untuk menampilkan hasil skrining dan memberikan opsi untuk merujuk.
     *
     * @param int $id ID dari skrining.
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        // Catatan: Ambil data skrining beserta informasi pasien melalui join.
        $skrining = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->where('skrinings.id', $id)
            ->select(
                'skrinings.*',
                'pasiens.nama',
                'pasiens.nik', 
                'pasiens.alamat',
                'pasiens.no_telepon as telp',
                'pasiens.tanggal_lahir'
            )
            ->first();

        // Catatan: Jika skrining tidak ditemukan, tampilkan error 404.
        if (!$skrining) {
            abort(404, 'Data skrining tidak ditemukan');
        }

        // Catatan: Format data untuk ditampilkan di view.
        $conclusion = $skrining->kesimpulan ?? 'Tidak ada kesimpulan';
        $cls = $this->getConclusionClass($conclusion);
        
        // Catatan: Parse faktor risiko tinggi dari field hasil_akhir atau field lainnya.
        $sebabTinggi = $this->parseRiskFactors($skrining->hasil_akhir ?? '');
        $sebabSedang = []; // Catatan: Untuk saat ini, tidak diisi. Bisa dikembangkan lebih lanjut.

        // Catatan: Cek apakah sudah ada rujukan aktif untuk skrining ini.
        $hasReferral = DB::table('rujukan_rs')
            ->where('skrining_id', $id)
            ->where('done_status', false) // Rujukan belum selesai
            ->where('is_rujuk', true) // Rujukan yang benar-benar diajukan
            ->exists();

        // Catatan: Kirim data ke view untuk ditampilkan.
        return view('puskesmas.skrining.show', [
            'skrining' => $skrining,
            'nama' => $skrining->nama,
            'nik' => $skrining->nik,
            'tanggal' => \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y'), // Catatan: Format tanggal untuk ditampilkan.
            'alamat' => $skrining->alamat,
            'telp' => $skrining->telp,
            'conclusion' => $conclusion,
            'cls' => $cls, // Catatan: Kelas CSS untuk styling kesimpulan.
            'sebabTinggi' => $sebabTinggi, // Catatan: Faktor risiko tinggi.
            'sebabSedang' => $sebabSedang, // Catatan: Faktor risiko sedang (kosong untuk sekarang).
            'hasReferral' => $hasReferral // Catatan: Status apakah sudah ada rujukan aktif.
        ]);
    }

    /**
     * Helper method untuk menentukan class CSS berdasarkan kesimpulan skrining.
     * Digunakan untuk memberi warna yang sesuai pada tampilan (misalnya merah untuk risiko tinggi).
     *
     * @param string $conclusion Kesimpulan dari hasil skrining.
     * @return string Kelas CSS untuk styling.
     */
    private function getConclusionClass($conclusion)
    {
        // Catatan: Ubah kesimpulan menjadi huruf kecil dan trim spasi untuk pencocokan.
        $label = strtolower(trim($conclusion));
        $isRisk = in_array($label, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi', 'high risk']);
        $isWarn = in_array($label, ['waspada', 'menengah', 'sedang', 'risiko sedang', 'medium risk']);
        
        // Catatan: Kembalikan kelas CSS berdasarkan level risiko.
        return $isRisk ? 'bg-[#E20D0D] text-white' : 
            ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white');
    }

    /**
     * Helper method untuk menguraikan faktor risiko dari hasil skrining.
     * Digunakan untuk menampilkan faktor-faktor yang menyebabkan risiko tinggi/sedang.
     *
     * @param string $hasilAkhir Isi dari field hasil_akhir atau data lain yang menyimpan faktor risiko.
     * @return array Daftar faktor risiko yang ditemukan.
     */
    private function parseRiskFactors($hasilAkhir)
    {
        // Catatan: Logic untuk parse faktor risiko dari hasil_akhir.
        // Sesuaikan dengan format data Anda.
        $factors = [];
        
        // Catatan: Cek apakah kata kunci faktor risiko ditemukan dalam teks hasil akhir.
        if (strpos(strtolower($hasilAkhir), 'primigravida') !== false) {
            $factors[] = 'Primigravida (G=1)';
        }
        if (strpos(strtolower($hasilAkhir), 'obesitas') !== false) {
            $factors[] = 'Obesitas';
        }
        if (strpos(strtolower($hasilAkhir), 'hipertensi') !== false) {
            $factors[] = 'Riwayat Hipertensi';
        }
        // Catatan: Tambahkan faktor risiko lainnya sesuai kebutuhan.
        
        return $factors;
    }
}