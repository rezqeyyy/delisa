<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;

class SkriningController extends Controller
{
    public function create()
    {
        return view('pasien.skrining-create');
    }

    public function show(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);
        return view('pasien.skrining-show', compact('skrining'));
    }

    public function edit(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);
        return view('pasien.skrining-edit', compact('skrining'));
    }

    private function authorizeAccess(Skrining $skrining): void
    {
        $userPasienId = optional(Auth::user()->pasien)->id;
        abort_unless($skrining->pasien_id === $userPasienId, 403);
    }
}