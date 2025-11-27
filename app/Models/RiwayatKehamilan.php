<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKehamilan extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * Mengacu ke definisi di database-delisa.sql:
     * CREATE TABLE "riwayat_kehamilans" ( ... )
     */
    protected $table = 'riwayat_kehamilans';

    /**
     * Guarded kosong → semua kolom boleh di-mass assign.
     * (Ikut pola RiwayatKehamilanGpa & Kf di Models-delisa.zip)
     */
    protected $guarded = [];

    /**
     * Relasi ke Skrining
     *
     * Kolom: riwayat_kehamilans.skrining_id -> skrinings.id
     */
    public function skrining()
    {
        return $this->belongsTo(Skrining::class, 'skrining_id');
    }

    /**
     * Relasi ke Pasien
     *
     * Kolom: riwayat_kehamilans.pasien_id -> pasiens.id
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }
}
