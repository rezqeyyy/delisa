<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KfKunjungan extends Model
{
    /**
     * Nama tabel di database
     */
    protected $table = 'kf_kunjungans';

    /**
     * Kolom yang bisa diisi (mass assignment)
     */
    protected $fillable = [
        'pasien_nifas_id',
        'jenis_kf',
        'tanggal_kunjungan',
        'sbp',
        'dbp',
        'map',
        'keadaan_umum',
        'tanda_bahaya',
        'kesimpulan_pantauan',
        'catatan',
    ];

    /**
     * Tipe data yang akan di-cast
     */
    protected $casts = [
        'jenis_kf' => 'integer',
        'tanggal_kunjungan' => 'date',
        'sbp' => 'integer',
        'dbp' => 'integer',
        'map' => 'integer',
    ];

    /**
     * Relationship dengan PasienNifasRs (data dari RS)
     */
    public function pasienNifasRs(): BelongsTo
    {
        return $this->belongsTo(PasienNifasRs::class, 'pasien_nifas_id');
    }

    /**
     * Accessor untuk jenis KF dalam bentuk teks
     */
    public function getJenisKfTextAttribute(): string
    {
        return match($this->jenis_kf) {
            1 => 'KF 1',
            2 => 'KF 2',
            3 => 'KF 3',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Accessor untuk tanggal kunjungan format Indonesia
     */
    public function getTanggalKunjunganFormattedAttribute(): string
    {
        return $this->tanggal_kunjungan->format('d/m/Y');
    }

    /**
     * Accessor untuk tensi lengkap
     */
    public function getTensiLengkapAttribute(): string
    {
        if (is_null($this->sbp) || is_null($this->dbp)) {
            return '-';
        }
        return "{$this->sbp}/{$this->dbp} mmHg";
    }
}