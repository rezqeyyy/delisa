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
        'tanggal_mulai_nifas' => 'datetime',
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
     * Base date untuk hitung window KF.
     * Prioritas: tanggal_melahirkan (jika ada) > tanggal_mulai_nifas.
     * Catatan: pada Bidan, tanggal_melahirkan sering ada di episode RS.
     * Kalau kolom ini tidak ada di tabel bidan, tetap aman karena fallback.
     */
    public function getKfBaseDate(): ?Carbon
    {
        // 1) Prioritas: tanggal melahirkan dari episode RS terbaru (kalau ada)
        $rs = \App\Models\PasienNifasRs::where('pasien_id', $this->pasien_id)
            ->orderByDesc('created_at')
            ->first();

        $base = $rs?->tanggal_melahirkan
            ?? $rs?->tanggal_mulai_nifas
            ?? $this->tanggal_mulai_nifas
            ?? null;

        return $base ? Carbon::parse($base) : null;
    }


    /**
     * Waktu mulai (boleh dicatat) untuk KF tertentu.
     * KF1: +6 jam
     * KF2: hari ke-3 (startOfDay)
     * KF3: hari ke-8 (startOfDay)
     * KF4: hari ke-29 (startOfDay)
     */
    public function getKfMulai(int $jenisKf): ?Carbon
    {
        $base = $this->getKfBaseDate();
        if (!$base) return null;

        $base = Carbon::parse($base);

        return match ($jenisKf) {
            1 => $base->copy()->addHours(6),                 // ✅ pakai base asli (jam)
            2 => $base->copy()->startOfDay()->addDays(3),    // hari ke-3
            3 => $base->copy()->startOfDay()->addDays(8),    // hari ke-8
            4 => $base->copy()->startOfDay()->addDays(29),   // hari ke-29
            default => null,
        };
    }


    /**
     * Deadline akhir window KF.
     * KF1: +48 jam
     * KF2: hari ke-7 (endOfDay)
     * KF3: hari ke-28 (endOfDay)
     * KF4: hari ke-42 (endOfDay)
     */
    public function getKfDeadline(int $jenisKf): ?Carbon
    {
        $base = $this->getKfBaseDate();
        if (!$base) return null;

        $base = Carbon::parse($base);

        return match ($jenisKf) {
            1 => $base->copy()->addHours(48),                    // ✅ 48 jam dari base asli
            2 => $base->copy()->startOfDay()->addDays(7)->endOfDay(),
            3 => $base->copy()->startOfDay()->addDays(28)->endOfDay(),
            4 => $base->copy()->startOfDay()->addDays(42)->endOfDay(),
            default => null,
        };
    }


    /**
     * Cek apakah KF sudah selesai.
     * Mengikuti pola proyek kamu: kalau kf{n}_tanggal terisi = selesai.
     */
    public function isKfSelesai(int $jenisKf): bool
    {
        $col = "kf{$jenisKf}_tanggal";
        return !empty($this->{$col});
    }

    /**
     * Status KF:
     * - selesai
     * - belum_mulai  (ini yang kamu sebut “MENUNGGU”)
     * - dalam_periode
     * - terlambat
     */
    public function getKfStatus(int $jenisKf): string
    {
        if ($this->isKfSelesai($jenisKf)) return 'selesai';

        $mulai = $this->getKfMulai($jenisKf);
        $deadline = $this->getKfDeadline($jenisKf);

        if (!$mulai || !$deadline) return 'belum_mulai';

        $now = Carbon::now();

        if ($now->lt($mulai)) return 'belum_mulai';
        if ($now->lte($deadline)) return 'dalam_periode';
        return 'terlambat';
    }

    /**
     * Boleh dicatat jika sudah masuk waktu mulai (dalam periode atau terlambat).
     */
    public function canDoKf(int $jenisKf): bool
    {
        $status = $this->getKfStatus($jenisKf);
        return in_array($status, ['dalam_periode', 'terlambat'], true);
    }
}
