<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnakPasien extends Model
{
    use HasFactory;

    // Nama tabel sesuai di database
    protected $table = 'anak_pasien';

    // Primary key default 'id' sudah sesuai

    protected $fillable = [
        'anak_ke',
        'tanggal_lahir',
        'jenis_kelamin',
        'nama_anak',
        'usia_kehamilan_saat_lahir',
        'berat_lahir_anak',
        'panjang_lahir_anak',
        'lingkar_kepala_anak',
        'memiliki_buku_kia',
        'buku_kia_bayi_kecil',
        'imd',
        'nifas_id',
    ];

    protected $casts = [
        'tanggal_lahir'        => 'date',
        'memiliki_buku_kia'    => 'boolean',
        'buku_kia_bayi_kecil'  => 'boolean',
        'imd'                  => 'boolean',
    ];

    /**
     * Relasi ke Pasien nifas
     * nifas_id -> pasiens.id
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'nifas_id');
    }

    /**
     * Relasi ke kunjungan KF (opsional, kalau mau dibutuhkan nanti)
     * id -> kf.id_anak
     */
    public function kf()
    {
        return $this->hasMany(Kf::class, 'id_anak');
    }
}
