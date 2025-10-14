<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dinkes.dashboard'); // resources/views/dinkes/dashboard.blade.php
    }
}
