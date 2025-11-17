<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasienNifas extends Model
{
    use HasFactory;
    
    protected $table = 'pasien_nifas_rs';
    
    protected $fillable = [
        'rs_id',
        'pasien_id',
        'tanggal_mulai_nifas',
        'status_kunjungan',
    ];
    
    protected $casts = [
        'tanggal_mulai_nifas' => 'datetime'
    ];
    
    /**
     * Relasi ke Pasien
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }
    
    /**
     * Relasi ke Rumah Sakit
     */
    public function rs()
    {
        return $this->belongsTo(RumahSakit::class, 'rs_id');
    }
    
    /**
     * Scope untuk filter berdasarkan RS
     */
    public function scopeByRs($query, $rsId)
    {
        return $query->where('rs_id', $rsId);
    }
    
    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_kunjungan', $status);
    }
    
    /**
     * Accessor untuk format tanggal Indonesia
     */
    public function getTanggalMulaiNifasFormattedAttribute()
    {
        return $this->tanggal_mulai_nifas ? $this->tanggal_mulai_nifas->format('d/m/Y') : '-';
    }
}