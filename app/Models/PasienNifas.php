<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasienNifas extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang benar
    protected $table = 'pasien_nifas_rs';

    protected $fillable = [
        'rs_id',
        'pasien_id',
        'tanggal_mulai_nifas'
    ];

    protected $casts = [
        'tanggal_mulai_nifas' => 'date'
    ];

    // Relasi ke model Pasien
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    // Relasi ke model RumahSakit  
    public function rs()
    {
        return $this->belongsTo(RumahSakit::class, 'rs_id');
    }
}