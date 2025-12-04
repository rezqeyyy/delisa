<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PasienNifasRs extends Model
{
    use HasFactory;

    // Cocokkan dengan nama tabel di PostgreSQL
    protected $table = 'pasien_nifas_rs';

    protected $fillable = [
        'rs_id',
        'pasien_id',
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

        // KF4 (baru)
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

    /**
     * Relasi ke tabel pasiens
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    /**
     * Relasi pendek "rs" yang dipakai di controller:
     * PasienNifasRs::with(['pasien.user', 'rs', 'anakPasien'])
     */
    public function rs()
    {
        return $this->belongsTo(RumahSakit::class, 'rs_id');
    }

    /**
     * Alias yang lebih deskriptif (opsional, bisa kamu pakai nanti di tempat lain)
     */
    public function rumahSakit()
    {
        return $this->rs();
    }

    /**
     * Relasi ke tabel anak_pasiens (satu nifas punya banyak anak)
     */
    public function anakPasien()
    {
        return $this->hasMany(AnakPasien::class, 'nifas_id');
    }

    /**
     * ========== RELATIONSHIPS KE SISTEM KF BARU ==========
     */

    /**
     * Relationship dengan semua KF Kunjungan (sistem baru)
     */
    public function kfKunjungans(): HasMany
    {
        return $this->hasMany(KfKunjungan::class, 'pasien_nifas_id');
    }

    /**
     * Relationship untuk KF 1 (sistem baru)
     */
    public function kfKunjungan1(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 1);
    }

    /**
     * Relationship untuk KF 2 (sistem baru)
     */
    public function kfKunjungan2(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 2);
    }

    /**
     * Relationship untuk KF 3 (sistem baru)
     */
    public function kfKunjungan3(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 3);
    }

    public function kfKunjungan4(): HasOne
    {
        return $this->hasOne(KfKunjungan::class, 'pasien_nifas_id')
            ->where('jenis_kf', 4);
    }

    /**
     * Foreign key relationships (untuk integrasi dengan kolom kf1_id, kf2_id, kf3_id)
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



    /**
     * Accessor untuk cek apakah sudah ada KF1 (sistem baru)
     */
    public function getHasKf1KunjunganAttribute(): bool
    {
        return $this->kfKunjungan1()->exists();
    }

    /**
     * Accessor untuk cek apakah sudah ada KF2 (sistem baru)
     */
    public function getHasKf2KunjunganAttribute(): bool
    {
        return $this->kfKunjungan2()->exists();
    }

    /**
     * Accessor untuk cek apakah sudah ada KF3 (sistem baru)
     */
    public function getHasKf3KunjunganAttribute(): bool
    {
        return $this->kfKunjungan3()->exists();
    }

    /**
     * Accessor untuk cek apakah sudah ada KF4 (sistem baru)
     */
    public function getHasKf4KunjunganAttribute(): bool
    {
        return $this->kfKunjungan4()->exists();
    }

    /**
     * Accessor untuk mendapatkan data KF berdasarkan jenis (sistem baru)
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

    /**
     * ========== METHOD KF KOMPATIBILITAS LAMA & BARU ==========
     */

    /**
     * Cek apakah KF sudah selesai (KOMPATIBILITAS SISTEM LAMA & BARU)
     */
    public function isKfSelesai($jenisKf)
    {
        // Cek sistem baru dulu
        switch ($jenisKf) {
            case 1:
                return $this->has_kf1_kunjungan || !empty($this->{"kf{$jenisKf}_tanggal"});
            case 2:
                return $this->has_kf2_kunjungan || !empty($this->{"kf{$jenisKf}_tanggal"});
            case 3:
                return $this->has_kf3_kunjungan || !empty($this->{"kf{$jenisKf}_tanggal"});
            default:
                return !empty($this->{"kf{$jenisKf}_tanggal"});
        }
    }

    /**
     * Hitung deadline KF berdasarkan tanggal melahirkan
     */
    public function getKfDeadline($jenisKf)
    {
        if (!$this->tanggal_melahirkan) return null;

        $tanggalMelahirkan = Carbon::parse($this->tanggal_melahirkan);

        switch ($jenisKf) {
            case 1:
                return $tanggalMelahirkan->copy()->addDays(2); // 6 jam - 2 hari
            case 2:
                return $tanggalMelahirkan->copy()->addDays(7); // Hari ke-3 - ke-7
            case 3:
                return $tanggalMelahirkan->copy()->addDays(28); // Hari ke-8 - ke-28
            case 4:
                return $tanggalMelahirkan->copy()->addDays(42); // TAMBAHKAN: KF4 biasanya hari ke-29-42
            default:
                return null;
        }
    }

    /**
     * Cek status KF (belum, dalam periode, terlambat, selesai)
     */
    public function getKfStatus($jenisKf)
    {
        // Jika sudah selesai
        if ($this->isKfSelesai($jenisKf)) {
            return 'selesai';
        }

        $now = Carbon::now();
        $deadline = $this->getKfDeadline($jenisKf);

        if (!$deadline) return 'tidak_ada_data';

        // Jika sudah lewat deadline
        if ($now->greaterThan($deadline)) {
            return 'terlambat';
        }

        // Hitung mulai periode
        $mulai = $this->getKfMulai($jenisKf);

        // Jika sudah masuk periode
        if ($now->greaterThanOrEqualTo($mulai)) {
            return 'dalam_periode';
        }

        return 'belum_mulai';
    }

    /**
     * Hitung mulai periode KF
     */
    public function getKfMulai($jenisKf)
    {
        if (!$this->tanggal_melahirkan) return null;

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
     * Cek apakah KF bisa dilakukan (KOMPATIBILITAS SISTEM LAMA & BARU)
     */
    public function canDoKf($jenisKf)
    {
        // KF1 selalu bisa
        if ($jenisKf == 1) return true;

        // Untuk KF2 dan KF3, cek sistem baru atau lama
        if ($jenisKf == 2) {
            return $this->has_kf1_kunjungan || $this->isKfSelesai(1);
        }

        if ($jenisKf == 3) {
            return $this->has_kf2_kunjungan || $this->isKfSelesai(2);
        }

        if ($jenisKf == 4) { // TAMBAHKAN
            return $this->has_kf3_kunjungan || $this->isKfSelesai(3);
        }

        return false;
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
}
