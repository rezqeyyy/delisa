<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnakPasien extends Model
{
    protected $table = 'anak_pasien';
    
    protected $fillable = [
        'nifas_id',
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
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
        'riwayat_penyakit' => 'array',
    ];
    
    public function pasienNifas()
    {
        return $this->belongsTo(PasienNifas::class, 'nifas_id');
    }
}
