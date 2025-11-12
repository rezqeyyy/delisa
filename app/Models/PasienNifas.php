<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasienNifas extends Model
{
    protected $table = 'pasien_nifas_rs';
    protected $guarded = [];

    // Relasi ke Pasien (sesuaikan dengan nama model Anda)
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    // Relasi ke RS (sesuaikan dengan nama model Anda)
    public function rs()
    {
        return $this->belongsTo(Rs::class, 'rs_id');
    }
}