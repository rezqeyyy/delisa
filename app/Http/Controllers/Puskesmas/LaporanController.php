<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller; // Pastikan ini diimpor
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        // Tambahkan logika Anda di sini, misalnya mengambil data laporan
        // Untuk sekarang, kita hanya menampilkan view
        return view('puskesmas.laporan.index');
    }
}