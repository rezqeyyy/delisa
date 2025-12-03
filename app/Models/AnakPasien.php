<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnakPasien extends Model
{
    protected $table = 'anak_pasien';
    
    protected $fillable = [
        'nifas_id',
        'nifas_bidan_id',
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
        'riwayat_penyakit',
        'keterangan_masalah_lain',
        'kondisi_ibu',
        'catatan_kondisi_ibu',
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
        'riwayat_penyakit' => 'array',
    ];

    /**
     * Anak ini milik 1 pasien (ibu)
     * nifas_id = pasien_id
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'nifas_id');
    }

    public function nifasBidan()
    {
        return $this->belongsTo(\App\Models\PasienNifasBidan::class, 'nifas_bidan_id');
    }
}
