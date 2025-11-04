<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SkriningController extends Controller
{
    public function index()
    {
        // Dummy data untuk preview UI
        $data = [
            [
                'id' => '#0000000000000000',
                'nama_pasien' => 'Asep Dadang',
                'tanggal' => '01/01/2025',
                'alamat' => 'Beji',
                'no_telp' => '0000000000',
                'kesimpulan' => 'Beresiko', // atau 'Aman', 'Waspadai'
                'view_url' => '#'
            ],
            [
                'id' => '#0000000000000001',
                'nama_pasien' => 'Asep Dadang',
                'tanggal' => '01/01/2025',
                'alamat' => 'Beji',
                'no_telp' => '0000000000',
                'kesimpulan' => 'Aman',
                'view_url' => '#'
            ],
            [
                'id' => '#0000000000000002',
                'nama_pasien' => 'Asep Dadang',
                'tanggal' => '01/01/2025',
                'alamat' => 'Beji',
                'no_telp' => '0000000000',
                'kesimpulan' => 'Waspadai',
                'view_url' => '#'
            ],
            // Tambahkan lebih banyak dummy data jika perlu
        ];

        return view('puskesmas.skrining.index', compact('data'));
    }
}