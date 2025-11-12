<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasienNifasController extends Controller
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
                'pengingat' => 'Aman', // atau 'Terlambat', 'Waspadai'
                'action_urls' => ['#', '#', '#'] // URL untuk M1, M2, M3
            ],
            [
                'id' => '#0000000000000001',
                'nama_pasien' => 'Asep Dadang',
                'tanggal' => '01/01/2025',
                'alamat' => 'Beji',
                'no_telp' => '0000000000',
                'pengingat' => 'Terlambat',
                'action_urls' => ['#', '#', '#']
            ],
            [
                'id' => '#0000000000000002',
                'nama_pasien' => 'Asep Dadang',
                'tanggal' => '01/01/2025',
                'alamat' => 'Beji',
                'no_telp' => '0000000000',
                'pengingat' => 'Waspadai',
                'action_urls' => ['#', '#', '#']
            ],
            // Tambahkan lebih banyak dummy data jika perlu
        ];

        return view('puskesmas.pasien-nifas.index', compact('data'));
    }
}