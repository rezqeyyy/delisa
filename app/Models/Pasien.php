<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model Pasien berfungsi untuk mempresentasikan tabel pasien di database dan mengatur field yang bisa diisi
class Pasien extends Model
{
    // Menetapkan nama tabel yang dipakai Eloquent untuk model ini
    protected $table = 'pasiens';

    // Daftar atribut yang diizinkan untuk mass assignment (create, update, fill)
    // Melindungi dari mass assignment vulnerability dengan whitelist kolom yang aman
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

    /**
     * Relasi ke model `User` (Pasien dimiliki oleh satu User).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}