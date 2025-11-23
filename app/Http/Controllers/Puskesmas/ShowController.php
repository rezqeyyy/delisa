<?php
// app/Http/Controllers/Puskesmas/SkriningController.php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SkriningController extends Controller
{
    // Method untuk list skrining (sudah ada)
    public function index()
    {
        // ... kode existing untuk list skrining
    }

    // METHOD BARU: Show detail skrining dengan fitur rujukan
    public function show($id)
    {
        // Get data skrining dengan join pasien
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

        if (!$skrining) {
            abort(404, 'Data skrining tidak ditemukan');
        }

        // Format data untuk view
        $conclusion = $skrining->kesimpulan ?? 'Tidak ada kesimpulan';
        $cls = $this->getConclusionClass($conclusion);
        
        // Parse penyebab risiko dari hasil_akhir atau field lainnya
        $sebabTinggi = $this->parseRiskFactors($skrining->hasil_akhir ?? '');
        $sebabSedang = [];

        // Cek apakah sudah ada rujukan aktif
        $hasReferral = DB::table('rujukan_rs')
            ->where('skrining_id', $id)
            ->where('done_status', false)
            ->where('is_rujuk', true)
            ->exists();

        return view('puskesmas.skrining.show', [
            'skrining' => $skrining,
            'nama' => $skrining->nama,
            'nik' => $skrining->nik,
            'tanggal' => \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y'),
            'alamat' => $skrining->alamat,
            'telp' => $skrining->telp,
            'conclusion' => $conclusion,
            'cls' => $cls,
            'sebabTinggi' => $sebabTinggi,
            'sebabSedang' => $sebabSedang,
            'hasReferral' => $hasReferral
        ]);
    }

    // Helper method untuk menentukan class CSS berdasarkan kesimpulan
    private function getConclusionClass($conclusion)
    {
        $label = strtolower(trim($conclusion));
        $isRisk = in_array($label, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi', 'high risk']);
        $isWarn = in_array($label, ['waspada', 'menengah', 'sedang', 'risiko sedang', 'medium risk']);
        
        return $isRisk ? 'bg-[#E20D0D] text-white' : 
               ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white');
    }

    // Helper method untuk parse faktor risiko
    private function parseRiskFactors($hasilAkhir)
    {
        // Logic untuk parse faktor risiko dari hasil_akhir
        // Sesuaikan dengan format data Anda
        $factors = [];
        
        if (strpos(strtolower($hasilAkhir), 'primigravida') !== false) {
            $factors[] = 'Primigravida (G=1)';
        }
        if (strpos(strtolower($hasilAkhir), 'obesitas') !== false) {
            $factors[] = 'Obesitas';
        }
        if (strpos(strtolower($hasilAkhir), 'hipertensi') !== false) {
            $factors[] = 'Riwayat Hipertensi';
        }
        // Tambahkan faktor risiko lainnya sesuai kebutuhan
        
        return $factors;
    }
}