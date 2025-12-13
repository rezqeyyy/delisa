<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class PasienNifasRs extends Model
{
    use HasFactory;

    // Cocokkan dengan nama tabel di PostgreSQL
    protected $table = 'pasien_nifas_rs';

    protected $fillable = [
        'rs_id',
        'pasien_id',
        'puskesmas_id', 
        'tanggal_mulai_nifas',
        'tanggal_melahirkan',

        // KF1
        'kf1_tanggal',
        'kf1_catatan',
        'kf1_id',

        // KF2
        'kf2_tanggal',
        'kf2_catatan',
        'kf2_id',

        // KF3
        'kf3_tanggal',
        'kf3_catatan',
        'kf3_id',

        // KF4
        'kf4_tanggal',
        'kf4_catatan',
        'kf4_id',
    ];

    // TAMBAH INI: Casting untuk tanggal
    protected $casts = [
        'tanggal_melahirkan' => 'date',
        'tanggal_mulai_nifas'  => 'date',
        'kf1_tanggal' => 'datetime',
        'kf2_tanggal' => 'datetime',
        'kf3_tanggal' => 'datetime',
        'kf4_tanggal' => 'datetime',
    ];

    // ========== SCOPES UNTUK FILTER WILAYAH ==========
    
    /**
     * Scope untuk filter berdasarkan kecamatan puskesmas
     */
    public function scopeFilterByKecamatan($query, $kecamatan)
    {
        return $query->whereHas('pasien', function($q) use ($kecamatan) {
            $q->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatan]);
        });
    }

    /**
     * Scope untuk filter berdasarkan ID puskesmas
     */
    public function scopeFilterByPuskesmas($query, $puskesmasId)
    {
        // Jika diperlukan di masa depan
        return $query;
    }

    // ========== RELASI DASAR ==========

    /**
     * Relasi ke tabel pasiens
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    /**
     * Relasi ke rumah sakit
     */
    public function rs()
    {
        return $this->belongsTo(RumahSakit::class, 'rs_id');
    }

    /**
     * Relasi ke anak pasien
     */
    public function anakPasien()
    {
        return $this->hasMany(AnakPasien::class, 'nifas_id');
    }

    // ========== RELASI KE SISTEM KF BARU ==========

    /**
     * Relationship dengan semua KF Kunjungan
     */
    public function kfKunjungans(): HasMany
    {
        return $this->hasMany(KfKunjungan::class, 'pasien_nifas_id');
    }

    /**
     * Relationship untuk KF 1
     */
    public function kfKunjungan1(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 1);
    }

    /**
     * Relationship untuk KF 2
     */
    public function kfKunjungan2(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 2);
    }

    /**
     * Relationship untuk KF 3
     */
    public function kfKunjungan3(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 3);
    }

    /**
     * Relationship untuk KF 4
     */
    public function kfKunjungan4(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 4);
    }

    /**
     * Foreign key relationships
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

    // ========== ACCESSORS ==========

    /**
     * Accessor untuk cek apakah sudah ada KF1
     */
    public function getHasKf1KunjunganAttribute(): bool
    {
        return $this->kfKunjungan1()->exists();
    }

    /**
     * Accessor untuk cek apakah sudah ada KF2
     */
    public function getHasKf2KunjunganAttribute(): bool
    {
        return $this->kfKunjungan2()->exists();
    }

    /**
     * Accessor untuk cek apakah sudah ada KF3
     */
    public function getHasKf3KunjunganAttribute(): bool
    {
        return $this->kfKunjungan3()->exists();
    }

    /**
     * Accessor untuk cek apakah sudah ada KF4
     */
    public function getHasKf4KunjunganAttribute(): bool
    {
        return $this->kfKunjungan4()->exists();
    }

    /**
     * Accessor untuk mendapatkan semua data KF
     */
    public function getKfKunjunganAttribute(): array
    {
        return [
            'kf1' => $this->kfKunjungan1,
            'kf2' => $this->kfKunjungan2,
            'kf3' => $this->kfKunjungan3,
            'kf4' => $this->kfKunjungan4,
        ];
    }

    // ========== METHOD UTILITAS ==========

    /**
     * Cek apakah KF sudah selesai (KOMPATIBILITAS SISTEM LAMA & BARU)
     * DIPAKAI DI CONTROLLER: $pasienNifas->isKfSelesai($jenisKf)
     */
    public function isKfSelesai($jenisKf)
    {
        // Cek sistem baru dulu (tabel kf_kunjungans)
        $existsInNewSystem = DB::table('kf_kunjungans')
            ->where('pasien_nifas_id', $this->id)
            ->where('jenis_kf', $jenisKf)
            ->exists();
        
        if ($existsInNewSystem) {
            return true;
        }
        
        // Fallback: cek kolom lama
        $tanggalField = "kf{$jenisKf}_tanggal";
        return !empty($this->$tanggalField);
    }

    /**
     * Hitung deadline KF berdasarkan tanggal melahirkan
     */
    public function getKfDeadline($jenisKf)
    {
        if (!$this->tanggal_melahirkan) {
            return null;
        }

        $tanggalMelahirkan = Carbon::parse($this->tanggal_melahirkan);

        switch ($jenisKf) {
            case 1:
                return $tanggalMelahirkan->copy()->addDays(2); // 6 jam - 2 hari
            case 2:
                return $tanggalMelahirkan->copy()->addDays(7); // Hari ke-3 - ke-7
            case 3:
                return $tanggalMelahirkan->copy()->addDays(28); // Hari ke-8 - ke-28
            case 4:
                return $tanggalMelahirkan->copy()->addDays(42); // Hari ke-29-42
            default:
                return null;
        }
    }

    /**
     * Cek status KF (belum, dalam periode, terlambat, selesai)
     * DIPAKAI DI CONTROLLER: $pasienNifas->getKfStatus($jenisKf)
     */
    public function getKfStatus($jenisKf)
    {
        // Jika sudah selesai
        if ($this->isKfSelesai($jenisKf)) {
            return 'selesai';
        }

        $now = Carbon::now();
        $deadline = $this->getKfDeadline($jenisKf);

        if (!$deadline) {
            return 'tidak_ada_data';
        }

        // Hitung mulai periode
        $mulai = $this->getKfMulai($jenisKf);

        // Jika belum mulai
        if ($mulai && $now->lessThan($mulai)) {
            return 'belum_mulai';
        }

        // Jika sudah lewat deadline
        if ($now->greaterThan($deadline)) {
            return 'terlambat';
        }

        // Jika sudah masuk periode
        if ($mulai && $now->greaterThanOrEqualTo($mulai)) {
            return 'dalam_periode';
        }

        return 'belum_mulai';
    }

    /**
     * Hitung mulai periode KF
     * DIPAKAI DI CONTROLLER: $pasienNifas->getKfMulai($jenisKf)
     */
    public function getKfMulai($jenisKf)
    {
        if (!$this->tanggal_melahirkan) {
            return null;
        }

        $tanggalMelahirkan = Carbon::parse($this->tanggal_melahirkan);

        switch ($jenisKf) {
            case 1:
                return $tanggalMelahirkan->copy()->addHours(6);
            case 2:
                return $tanggalMelahirkan->copy()->addDays(3);
            case 3:
                return $tanggalMelahirkan->copy()->addDays(8);
            case 4:
                return $tanggalMelahirkan->copy()->addDays(29);
            default:
                return null;
        }
    }

    /**
     * Cek apakah KF bisa dilakukan
     */
    public function canDoKf($jenisKf)
    {
        // KF1 selalu bisa
        if ($jenisKf == 1) {
            return true;
        }

        // Untuk KF2,3,4 cek KF sebelumnya sudah selesai
        for ($i = 1; $i < $jenisKf; $i++) {
            if (!$this->isKfSelesai($i)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get warna badge berdasarkan status
     */
    public function getKfBadgeColor($jenisKf)
    {
        $status = $this->getKfStatus($jenisKf);

        switch ($status) {
            case 'selesai':
                return 'success';
            case 'dalam_periode':
                return 'warning';
            case 'terlambat':
                return 'danger';
            case 'belum_mulai':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    /**
     * Get icon berdasarkan status
     */
    public function getKfIcon($jenisKf)
    {
        $status = $this->getKfStatus($jenisKf);

        switch ($status) {
            case 'selesai':
                return '✓';
            case 'dalam_periode':
                return '!';
            case 'terlambat':
                return '⚠';
            case 'belum_mulai':
                return '○';
            default:
                return '?';
        }
    }

    /**
     * Get data KF untuk chart/statistik
     */
    public function getKfStatistics()
    {
        $stats = [
            'total' => 0,
            'selesai' => 0,
            'belum' => 0,
            'terlambat' => 0,
        ];

        for ($i = 1; $i <= 4; $i++) {
            $stats['total']++;
            $status = $this->getKfStatus($i);
            
            if ($status === 'selesai') {
                $stats['selesai']++;
            } elseif ($status === 'terlambat') {
                $stats['terlambat']++;
            } else {
                $stats['belum']++;
            }
        }

        return $stats;
    }

    /**
     * Cek apakah pasien sudah meninggal berdasarkan kesimpulan KF
     */
    public function isMeninggal()
    {
        return DB::table('kf_kunjungans')
            ->where('pasien_nifas_id', $this->id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                  ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->exists();
    }

    /**
     * Get KF kematian pertama
     */
    public function getDeathKf()
    {
        return DB::table('kf_kunjungans')
            ->where('pasien_nifas_id', $this->id)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                  ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->min('jenis_kf');
    }

    /**
     * Format tanggal untuk display
     */
    public function getFormattedDate($field)
    {
        if (empty($this->$field)) {
            return '-';
        }
        
        try {
            return Carbon::parse($this->$field)->format('d/m/Y');
        } catch (\Exception $e) {
            return $this->$field;
        }
    }
}