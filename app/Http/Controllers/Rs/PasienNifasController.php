<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\PasienNifas;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PasienNifasController extends Controller
{
    public function index()
    {
        $pasienNifas = PasienNifas::with(['pasien', 'rs'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rs.pasien-nifas.index', compact('pasienNifas'));
    }

    public function show($id)
    {
        $pasienNifas = PasienNifas::with(['pasien', 'rs'])->findOrFail($id);
        
        return view('rs.pasien-nifas.show', compact('pasienNifas'));
    }

    public function downloadPDF()
    {
        $pasienNifas = PasienNifas::with(['pasien', 'rs'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('rs.pasien-nifas.pdf', compact('pasienNifas'));
        
        return $pdf->download('data-pasien-nifas-' . date('Y-m-d') . '.pdf');
    }
}