<?php
namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dinkes.dashboard'); // resources/views/dinkes/dashboard.blade.php
    }
}
