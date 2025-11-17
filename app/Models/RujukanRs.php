<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RujukanRs extends Model
{
    use HasFactory;

    protected $table = 'rujukan_rs';

    protected $fillable = [
        'pasien_id',
        'rs_id',
        'skrining_id',
        'done_status',
        'catatan_rujukan',
        'is_rujuk',
        'pasien_datang',
        'riwayat_tekanan_darah',
        'hasil_protein_urin',
        'perlu_pemeriksaan_lanjut',
    ];

    protected $casts = [
        'done_status' => 'boolean',
        'is_rujuk' => 'boolean',
        'pasien_datang' => 'boolean',
        'perlu_pemeriksaan_lanjut' => 'boolean',
    ];

    // Relasi ke Pasien
    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    // Relasi ke Rumah Sakit
    public function rumahSakit()
    {
        return $this->belongsTo(RumahSakit::class, 'rs_id');
    }

    // Relasi ke Skrining
    public function skrining()
    {
        return $this->belongsTo(Skrining::class);
    }

    // Relasi ke Resep Obat
    public function resepObats()
    {
        return $this->hasMany(ResepObat::class, 'riwayat_rujukan_id');
    }
}