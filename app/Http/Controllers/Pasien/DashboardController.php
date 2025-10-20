<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = trim($request->get('status', ''));

        $pasienId = optional(Auth::user()->pasien)->id;

        $skrinings = Skrining::where('pasien_id', $pasienId)
            ->when($status !== '', function ($q) use ($status) {
                $q->where(function ($w) use ($status) {
                    $w->where('kesimpulan', $status)
                      ->orWhere('status_pre_eklampsia', $status);
                });
            })
            ->with(['pasien.user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pasien.dashboard', [
            'skrinings' => $skrinings,
            'status'    => $status,
        ]);
    }
    
}
