<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasienNifasRs extends Model
{
    use HasFactory;

    // Cocokkan dengan nama tabel di PostgreSQL
    protected $table = 'pasien_nifas_rs';

    protected $fillable = [
        'rs_id',
        'pasien_id',
        'tanggal_mulai_nifas',
    ];

    /**
     * Relasi ke tabel pasiens
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    /**
     * Relasi pendek "rs" yang dipakai di controller:
     * PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
     */
    public function rs()
    {
        return $this->belongsTo(RumahSakit::class, 'rs_id');
    }

    /**
     * Alias yang lebih deskriptif (opsional, bisa kamu pakai nanti di tempat lain)
     */
    public function rumahSakit()
    {
        return $this->rs();
    }

    /**
     * Relasi ke tabel anak_pasiens (satu nifas punya banyak anak)
     */
    public function anakPasien()
    {
        return $this->hasMany(AnakPasien::class, 'nifas_id');
    }
}
