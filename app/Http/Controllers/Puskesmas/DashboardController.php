<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Pastikan controller puskesmas menampilkan view puskesmas
        return view('puskesmas.dashboard'); // resources/views/puskesmas/dashboard.blade.php
    }
}
