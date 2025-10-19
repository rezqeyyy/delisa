<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKehamilanGpa extends Model
{
    use HasFactory;

    // Tentukan nama tabelnya
    protected $table = 'riwayat_kehamilan_gpas';

    // Izinkan semua field diisi
    protected $guarded = [];

    /**
     * Relasi ke Skrining
     */
    public function skrining()
    {
        return $this->belongsTo(Skrining::class, 'skrining_id');
    }
}