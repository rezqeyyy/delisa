<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasienNifasBidan extends Model
{
    use HasFactory;

    protected $table = 'pasien_nifas_bidan';

    /**
     * Primary key default-nya "id" dan tipe bigInt sudah cocok
     * dengan struktur tabel di database-delisa.sql,
     * jadi tidak perlu diubah kecuali memang berbeda.
     */

    /**
     * Kolom yang boleh di-*mass assign* (diisi sekaligus, misal via create()).
     * Ini mengikuti struktur tabel:
     *
     *  - bidan_id
     *  - pasien_id
     *  - tanggal_mulai_nifas
     */
    protected $fillable = [
        'bidan_id',
        'pasien_id',
        'tanggal_mulai_nifas',
    ];

    /**
     * Relasi ke model Bidan.
     * Asumsi: secara logika "bidan_id" mengarah ke tabel "bidans" (model Bidan).
     *
     * Jika di schema database nanti sudah dibenerin foreign key-nya,
     * relasi ini akan makin konsisten.
     */
    public function bidan()
    {
        return $this->belongsTo(Bidan::class, 'bidan_id');
    }

    /**
     * Relasi ke model Pasien.
     * "pasien_id" -> tabel "pasiens".
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }
}
