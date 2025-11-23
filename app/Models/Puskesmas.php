<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puskesmas extends Model
{
    protected $table = 'puskesmas';
    protected $fillable = ['nama_puskesmas','lokasi','kecamatan'];
    public function bidans() { return $this->hasMany(Bidan::class); }

    // Catatan: tabel puskesmas TIDAK punya kolom user_id.
    // Akun PIC puskesmas tetap disimpan di tabel users dengan role 'puskesmas'.
}
