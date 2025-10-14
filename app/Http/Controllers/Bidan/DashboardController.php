<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('bidan.dashboard'); // resources/views/bidan/dashboard.blade.php
    }
}
