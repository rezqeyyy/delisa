<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RujukanRs;
use App\Models\Skrining;

class RiwayatRujukan extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     * Mengacu ke: CREATE TABLE "riwayat_rujukans" (...)
     */
    protected $table = 'riwayat_rujukans';

    /**
     * Izinkan semua field diisi (mengikuti pola beberapa model lain).
     */
    protected $guarded = [];

    /**
     * Casting tipe data otomatis.
     * - tanggal_datang → date (tanpa waktu)
     * - created_at / updated_at → datetime (default Laravel)
     */
    protected $casts = [
        'tanggal_datang' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * Relasi ke RujukanRs
     * riwayat_rujukans.rujukan_id → rujukan_rs.id
     */
    public function rujukan()
    {
        return $this->belongsTo(RujukanRs::class, 'rujukan_id');
    }

    /**
     * Relasi ke Skrining
     * riwayat_rujukans.skrining_id → skrinings.id
     */
    public function skrining()
    {
        return $this->belongsTo(Skrining::class, 'skrining_id');
    }
}
