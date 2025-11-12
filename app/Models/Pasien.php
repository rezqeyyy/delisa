<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Pasien berfungsi untuk mempresentasikan tabel pasien di database dan mengatur field yang bisa diisi
class Pasien extends Model
{
    protected $table = 'pasiens';

    protected $fillable = [
        'user_id',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'status_perkawinan',
        'PKecamatan',
        'PKabupaten',
        'PProvinsi',
        'PPelayanan',
        'PKarakteristik',
        'PWilayah',
        'kode_pos',
        'rt',
        'rw',
        'pekerjaan',
        'pendidikan',
        'pembiayaan_kesehatan',
        'golongan_darah',
        'no_jkn',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}