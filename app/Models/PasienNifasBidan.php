<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasienNifasBidan extends Model
{
    protected $table = 'pasien_nifas_bidan';

    protected $fillable = [
        'bidan_id',
        'pasien_id',
        'tanggal_mulai_nifas',
        'kf1_id',
        'kf1_tanggal',
        'kf1_catatan',
        'kf2_id',
        'kf2_tanggal',
        'kf2_catatan',
        'kf3_id',
        'kf3_tanggal',
        'kf3_catatan',
        'kf4_id',
        'kf4_tanggal',
        'kf4_catatan',
    ];

    protected $casts = [
        'tanggal_mulai_nifas' => 'date',
        'kf1_tanggal' => 'datetime',
        'kf2_tanggal' => 'datetime',
        'kf3_tanggal' => 'datetime',
        'kf4_tanggal' => 'datetime',
    ];

    /**
     * Relasi ke model Bidan
     */
    public function bidan()
    {
        return $this->belongsTo(Bidan::class, 'bidan_id');
    }

    /**
     * Relasi ke model Pasien
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    /**
     * Relasi ke anak pasien
     */
    public function anakPasien()
    {
        return $this->hasMany(AnakPasien::class, 'nifas_id');
    }

    /**
     * Relasi ke KF Kunjungan
     */
    public function kf1()
    {
        return $this->belongsTo(KfKunjungan::class, 'kf1_id');
    }

    public function kf2()
    {
        return $this->belongsTo(KfKunjungan::class, 'kf2_id');
    }

    public function kf3()
    {
        return $this->belongsTo(KfKunjungan::class, 'kf3_id');
    }

    public function kf4()
    {
        return $this->belongsTo(KfKunjungan::class, 'kf4_id');
    }

    // ========== METHOD UNTUK LOGIKA KF ==========

    /**
     * Mendapatkan waktu mulai periode KF
     */
    public function getKfMulai($jenisKf)
    {
        if (!$this->tanggal_mulai_nifas) {
            return null;
        }

        $mulaiNifas = Carbon::parse($this->tanggal_mulai_nifas);

        switch ($jenisKf) {
            case 1:
                return $mulaiNifas->copy()->addHours(6);
            case 2:
                return $mulaiNifas->copy()->addDays(4);
            case 3:
                return $mulaiNifas->copy()->addDays(8);
            case 4:
                return $mulaiNifas->copy()->addDays(36);
            default:
                return null;
        }
    }

    /**
     * Mendapatkan waktu selesai periode KF
     */
    public function getKfSelesai($jenisKf)
    {
        if (!$this->tanggal_mulai_nifas) {
            return null;
        }

        $mulaiNifas = Carbon::parse($this->tanggal_mulai_nifas);

        switch ($jenisKf) {
            case 1:
                return $mulaiNifas->copy()->addDays(3)->endOfDay();
            case 2:
                return $mulaiNifas->copy()->addDays(7)->endOfDay();
            case 3:
                return $mulaiNifas->copy()->addDays(35)->endOfDay();
            case 4:
                return $mulaiNifas->copy()->addDays(42)->endOfDay();
            default:
                return null;
        }
    }

    /**
     * Cek apakah sudah boleh melakukan KF
     */
    public function canDoKf($jenisKf)
    {
        $mulai = $this->getKfMulai($jenisKf);
        if (!$mulai) {
            return false;
        }
        return now()->gte($mulai);
    }

    /**
     * Cek apakah KF sudah selesai dicatat
     */
    public function isKfSelesai($jenisKf)
    {
        return !is_null($this->{"kf{$jenisKf}_tanggal"});
    }

    /**
     * Mendapatkan status KF
     * Return: 'belum_mulai' | 'dalam_periode' | 'terlambat' | 'selesai'
     */
    public function getKfStatus($jenisKf)
    {
        // Jika sudah selesai dicatat
        if ($this->isKfSelesai($jenisKf)) {
            return 'selesai';
        }

        $mulai = $this->getKfMulai($jenisKf);
        $selesai = $this->getKfSelesai($jenisKf);

        if (!$mulai || !$selesai) {
            return 'belum_mulai';
        }

        $now = now();

        // Belum waktunya
        if ($now->lt($mulai)) {
            return 'belum_mulai';
        }

        // Dalam periode normal
        if ($now->gte($mulai) && $now->lte($selesai)) {
            return 'dalam_periode';
        }

        // Sudah lewat periode normal
        return 'terlambat';
    }
}