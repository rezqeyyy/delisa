<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    //HasFactory digunakan untuk membuat data dummy
    use HasFactory;

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