<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\PasienNifas;
use Illuminate\Http\Request;
use PDF; // Pastikan install: composer require barryvdh/laravel-dompdf

class PasienNifasController extends Controller
{
    /**
     * Display a listing of pasien nifas
     */
    public function index()
    {
        $pasienNifas = PasienNifas::with(['pasien', 'rs'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rs.pasien-nifas.index', compact('pasienNifas'));
    }

    /**
     * Show detail pasien nifas
     */
    public function show($id)
    {
        $pasienNifas = PasienNifas::with(['pasien', 'rs'])->findOrFail($id);
        
        return view('rs.pasien-nifas.show', compact('pasienNifas'));
    }

    /**
     * Download PDF
     */
    public function downloadPDF()
    {
        $pasienNifas = PasienNifas::with(['pasien', 'rs'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = PDF::loadView('rs.pasien-nifas.pdf', compact('pasienNifas'));
        
        return $pdf->download('data-pasien-nifas-' . date('Y-m-d') . '.pdf');
    }
}