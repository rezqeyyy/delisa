<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        'pasien_nifas_id', // Tetap pertahankan untuk backward compatibility
        'jenis_kf',
        'tanggal_kunjungan',
        'sbp',
        'dbp',
        'map',
        'keadaan_umum',
        'tanda_bahaya',
        'kesimpulan_pantauan',
        'catatan',
        // Tambahan untuk polymorphic
        'nifasable_id',
        'nifasable_type',
    ];

    /**
     * Tipe data yang akan di-cast
     */
    protected $casts = [
        'jenis_kf' => 'integer',
        'tanggal_kunjungan' => 'datetime', // Changed to datetime for consistency
        'sbp' => 'integer',
        'dbp' => 'integer',
        'map' => 'integer',
    ];

    // ========== RELASI ==========

    /**
     * Relationship dengan PasienNifasRs (data dari RS) - BACKWARD COMPATIBILITY
     */
    public function pasienNifasRs(): BelongsTo
    {
        return $this->belongsTo(PasienNifasRs::class, 'pasien_nifas_id');
    }

    /**
     * Polymorphic relationship ke PasienNifasRs dan PasienNifasBidan
     * INI YANG BARU UNTUK SUPPORT KEDUA TIPE
     */
    public function nifasable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper method untuk mendapatkan data pasien (melalui nifasable)
     */
    public function getPasienAttribute()
    {
        if ($this->nifasable) {
            return $this->nifasable->pasien;
        }
        
        // Fallback ke relasi lama
        if ($this->pasienNifasRs) {
            return $this->pasienNifasRs->pasien;
        }
        
        return null;
    }

    // ========== SCOPES ==========

    /**
     * Scope untuk filter berdasarkan jenis KF
     */
    public function scopeByJenisKf($query, $jenisKf)
    {
        return $query->where('jenis_kf', $jenisKf);
    }

    /**
     * Scope untuk filter berdasarkan kesimpulan
     */
    public function scopeByKesimpulan($query, $kesimpulan)
    {
        return $query->where('kesimpulan_pantauan', $kesimpulan);
    }

    /**
     * Scope untuk filter data meninggal/wafat
     */
    public function scopeMeninggal($query)
    {
        return $query->where(function($q) {
            $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
              ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
        });
    }

    /**
     * Scope untuk filter berdasarkan tipe nifasable
     */
    public function scopeByNifasableType($query, $type)
    {
        return $query->where('nifasable_type', $type);
    }

    /**
     * Scope untuk filter berdasarkan ID nifasable
     */
    public function scopeByNifasableId($query, $id)
    {
        return $query->where('nifasable_id', $id);
    }

    // ========== ACCESSORS ==========

    /**
     * Accessor untuk jenis KF dalam bentuk teks
     */
    public function getJenisKfTextAttribute(): string
    {
        return match($this->jenis_kf) {
            1 => 'KF 1',
            2 => 'KF 2',
            3 => 'KF 3',
            4 => 'KF 4',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Accessor untuk tanggal kunjungan format Indonesia
     */
    public function getTanggalKunjunganFormattedAttribute(): string
    {
        return $this->tanggal_kunjungan 
            ? $this->tanggal_kunjungan->format('d/m/Y')
            : '-';
    }

    /**
     * Accessor untuk tanggal kunjungan lengkap
     */
    public function getTanggalKunjunganLengkapAttribute(): string
    {
        return $this->tanggal_kunjungan 
            ? $this->tanggal_kunjungan->format('l, d F Y H:i')
            : '-';
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

    /**
     * Accessor untuk MAP format
     */
    public function getMapFormattedAttribute(): string
    {
        return $this->map ? "{$this->map} mmHg" : '-';
    }

    /**
     * Cek apakah kesimpulan meninggal/wafat
     */
    public function getIsMeninggalAttribute(): bool
    {
        $kesimpulan = strtolower(trim($this->kesimpulan_pantauan ?? ''));
        return in_array($kesimpulan, ['meninggal', 'wafat']);
    }

    /**
     * Cek apakah kesimpulan sehat
     */
    public function getIsSehatAttribute(): bool
    {
        return strtolower(trim($this->kesimpulan_pantauan ?? '')) === 'sehat';
    }

    /**
     * Cek apakah kesimpulan dirujuk
     */
    public function getIsDirujukAttribute(): bool
    {
        return strtolower(trim($this->kesimpulan_pantauan ?? '')) === 'dirujuk';
    }

    /**
     * Warna badge berdasarkan kesimpulan
     */
    public function getBadgeColorAttribute(): string
    {
        return match(strtolower(trim($this->kesimpulan_pantauan ?? ''))) {
            'sehat' => 'success',
            'dirujuk' => 'warning',
            'meninggal', 'wafat' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Icon berdasarkan kesimpulan
     */
    public function getBadgeIconAttribute(): string
    {
        return match(strtolower(trim($this->kesimpulan_pantauan ?? ''))) {
            'sehat' => '✓',
            'dirujuk' => '↗',
            'meninggal', 'wafat' => '✗',
            default => '?',
        };
    }

    /**
     * Get nifasable type untuk display
     */
    public function getNifasableTypeTextAttribute(): string
    {
        return match($this->nifasable_type) {
            'App\Models\PasienNifasRs' => 'Rumah Sakit',
            'App\Models\PasienNifasBidan' => 'Bidan',
            default => 'Tidak Diketahui',
        };
    }

    // ========== METHOD UTILITY ==========

    /**
     * Cek apakah data valid
     */
    public function isValid(): bool
    {
        return $this->jenis_kf && $this->tanggal_kunjungan && $this->kesimpulan_pantauan;
    }

    /**
     * Get data untuk export/PDF
     */
    public function toExportArray(): array
    {
        $pasien = $this->pasien;
        
        return [
            'Jenis KF' => $this->jenis_kf_text,
            'Tanggal Kunjungan' => $this->tanggal_kunjungan_formatted,
            'Nama Pasien' => optional($pasien->user)->name ?? '-',
            'NIK' => $pasien->nik ?? '-',
            'Tensi' => $this->tensi_lengkap,
            'MAP' => $this->map_formatted,
            'Keadaan Umum' => $this->keadaan_umum ?? '-',
            'Tanda Bahaya' => $this->tanda_bahaya ?? '-',
            'Kesimpulan' => $this->kesimpulan_pantauan ?? '-',
            'Catatan' => $this->catatan ?? '-',
            'Sumber Data' => $this->nifasable_type_text,
        ];
    }
}