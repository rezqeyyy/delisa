<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnakPasien;

class Kf extends Model
{
    use HasFactory;

    // Nama tabel di database (dari database-delisa.sql: CREATE TABLE "kf" (...))
    protected $table = 'kf';

    // Primary key default sudah 'id', jadi tidak perlu diubah.
    // Jika di DB id-nya auto increment (bigint auto), default Laravel juga sudah cocok.

    // Kolom yang boleh di-mass-assign
    protected $fillable = [
        'id_nifas',             // foreign key ke tabel pasiens (id)
        'id_anak',              // foreign key ke tabel anak_pasien (id)
        'kunjungan_nifas_ke',
        'tanggal_kunjungan',
        'sbp',
        'dbp',
        'map',
        'keadaan_umum',
        'tanda_bahaya',
        'kesimpulan_pantauan',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    /**
     * Relasi ke Pasien (id_nifas -> pasiens.id)
     * Biar nanti bisa akses: $kf->pasien
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_nifas');
    }

    /**
     * Relasi ke AnakPasien (id_anak -> anak_pasien.id)
     * Kalau model AnakPasien sudah ada.
     */
    public function anak()
    {
        return $this->belongsTo(AnakPasien::class, 'id_anak');
    }
}
