<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasienNifasBidan extends Model
{
    protected $table = 'pasien_nifas_bidan';

    /**
     * Kolom yang boleh di-mass assign
     */
    protected $fillable = [
        'bidan_id',
        'pasien_id',
        'tanggal_mulai_nifas',
        // KOLOM KF BARU
        'kf1_tanggal', 'kf1_catatan', 'kf1_id',
        'kf2_tanggal', 'kf2_catatan', 'kf2_id',
        'kf3_tanggal', 'kf3_catatan', 'kf3_id',
        'kf4_tanggal', 'kf4_catatan', 'kf4_id',
    ];

    protected $casts = [
        'tanggal_mulai_nifas' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // CASTING KOLOM KF
        'kf1_tanggal' => 'datetime',
        'kf2_tanggal' => 'datetime',
        'kf3_tanggal' => 'datetime',
        'kf4_tanggal' => 'datetime',
    ];

    // ========== SCOPES UNTUK FILTER WILAYAH ==========
    
    /**
     * Scope untuk filter berdasarkan kecamatan puskesmas
     * DIPAKAI DI CONTROLLER untuk filter data
     */
    public function scopeFilterByKecamatan($query, $kecamatan)
    {
        return $query->whereHas('pasien', function($q) use ($kecamatan) {
            $q->whereRaw('LOWER("pasiens"."PKecamatan") = LOWER(?)', [$kecamatan]);
        });
    }

    /**
     * Scope untuk filter berdasarkan ID bidan
     */
    public function scopeFilterByBidan($query, $bidanId)
    {
        return $query->where('bidan_id', $bidanId);
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeFilterByDateRange($query, $startDate, $endDate = null)
    {
        if ($startDate) {
            $query->whereDate('tanggal_mulai_nifas', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tanggal_mulai_nifas', '<=', $endDate);
        }
        
        return $query;
    }

    // ========== RELASI ==========

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

    // ========== RELASI KF ==========

    /**
     * Relasi ke KfKunjungan untuk KF1
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
     * Relasi ke semua KF Kunjungan (morph)
     */
    public function kfKunjungans()
    {
        return $this->morphMany(KfKunjungan::class, 'nifasable');
    }

    // ========== METHOD KF (SAMA DENGAN PASIEN_NIFAS_RS) ==========

    /**
     * Cek apakah KF sudah selesai
     * DIPAKAI DI CONTROLLER: $pasienNifas->isKfSelesai($jenisKf)
     */
    public function isKfSelesai($jenisKf)
    {
        // Cek di tabel kf_kunjungans (sistem baru)
        $exists = DB::table('kf_kunjungans')
            ->where('nifasable_id', $this->id)
            ->where('nifasable_type', get_class($this)) // PERBAIKAN DI SINI
            ->where('jenis_kf', $jenisKf)
            ->exists();
        
        if ($exists) {
            return true;
        }
        
        // Fallback: cek kolom lama
        $tanggalField = "kf{$jenisKf}_tanggal";
        return !empty($this->$tanggalField);
    }

    /**
     * Hitung deadline KF berdasarkan tanggal mulai nifas
     */
    public function getKfDeadline($jenisKf)
    {
        if (!$this->tanggal_mulai_nifas) {
            return null;
        }

        $tanggalMulai = Carbon::parse($this->tanggal_mulai_nifas);

        switch ($jenisKf) {
            case 1:
                return $tanggalMulai->copy()->addDays(2); // 6 jam - 2 hari
            case 2:
                return $tanggalMulai->copy()->addDays(7); // Hari ke-3 - ke-7
            case 3:
                return $tanggalMulai->copy()->addDays(28); // Hari ke-8 - ke-28
            case 4:
                return $tanggalMulai->copy()->addDays(42); // Hari ke-29-42
            default:
                return null;
        }
    }

    /**
     * Cek status KF
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
     * Cek apakah KF bisa dilakukan
     * DIPAKAI DI VIEW: $item->canDoKf($jenisKf)
     */
    public function canDoKf($jenisKf)
    {
        $status = $this->getKfStatus($jenisKf);
        return in_array($status, ['dalam_periode', 'terlambat']);
    }

    /**
     * Hitung mulai periode KF
     * DIPAKAI DI CONTROLLER: $pasienNifas->getKfMulai($jenisKf)
     */
    public function getKfMulai($jenisKf)
    {
        if (!$this->tanggal_mulai_nifas) {
            return null;
        }

        $tanggalMulai = Carbon::parse($this->tanggal_mulai_nifas);

        switch ($jenisKf) {
            case 1:
                return $tanggalMulai->copy()->addHours(6);
            case 2:
                return $tanggalMulai->copy()->addDays(3);
            case 3:
                return $tanggalMulai->copy()->addDays(8);
            case 4:
                return $tanggalMulai->copy()->addDays(29);
            default:
                return null;
        }
    }

    /**
     * Cek apakah sudah ada KF (untuk accessor)
     */
    public function getHasKf1KunjunganAttribute()
    {
        return $this->isKfSelesai(1);
    }

    public function getHasKf2KunjunganAttribute()
    {
        return $this->isKfSelesai(2);
    }

    public function getHasKf3KunjunganAttribute()
    {
        return $this->isKfSelesai(3);
    }

    public function getHasKf4KunjunganAttribute()
    {
        return $this->isKfSelesai(4);
    }

    // ========== ACCESSORS & MUTATORS ==========

    /**
     * Format tanggal mulai nifas untuk display
     */
    public function getFormattedTanggalMulaiAttribute()
    {
        if (!$this->tanggal_mulai_nifas) {
            return '-';
        }
        
        try {
            return $this->tanggal_mulai_nifas->format('d/m/Y');
        } catch (\Exception $e) {
            return $this->tanggal_mulai_nifas;
        }
    }

    /**
     * Format tanggal mulai nifas lengkap
     */
    public function getTanggalMulaiLengkapAttribute()
    {
        if (!$this->tanggal_mulai_nifas) {
            return '-';
        }
        
        try {
            return $this->tanggal_mulai_nifas->format('l, d F Y');
        } catch (\Exception $e) {
            return $this->tanggal_mulai_nifas;
        }
    }

    /**
     * Hitung usia nifas dalam hari
     */
    public function getUsiaNifasHariAttribute()
    {
        if (!$this->tanggal_mulai_nifas) {
            return null;
        }
        
        try {
            return now()->diffInDays($this->tanggal_mulai_nifas);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Status nifas berdasarkan hari
     */
    public function getStatusNifasAttribute()
    {
        $usiaHari = $this->usia_nifas_hari;
        
        if ($usiaHari === null) {
            return 'Tidak diketahui';
        }
        
        if ($usiaHari <= 7) {
            return 'Nifas Awal (1-7 hari)';
        } elseif ($usiaHari <= 28) {
            return 'Nifas Menengah (8-28 hari)';
        } elseif ($usiaHari <= 42) {
            return 'Nifas Akhir (29-42 hari)';
        } else {
            return 'Nifas Selesai (>42 hari)';
        }
    }

    /**
     * Warna status berdasarkan usia nifas
     */
    public function getStatusColorAttribute()
    {
        $usiaHari = $this->usia_nifas_hari;
        
        if ($usiaHari === null) {
            return 'secondary';
        }
        
        if ($usiaHari <= 7) {
            return 'danger'; // Merah: periode kritis
        } elseif ($usiaHari <= 28) {
            return 'warning'; // Kuning: periode waspada
        } elseif ($usiaHari <= 42) {
            return 'info'; // Biru: periode pemulihan
        } else {
            return 'success'; // Hijau: selesai
        }
    }

    /**
     * Cek apakah sudah melewati masa nifas (42 hari)
     */
    public function getIsNifasSelesaiAttribute()
    {
        $usiaHari = $this->usia_nifas_hari;
        return $usiaHari !== null && $usiaHari > 42;
    }

    /**
     * Cek apakah masih dalam masa nifas awal (7 hari pertama)
     */
    public function getIsNifasAwalAttribute()
    {
        $usiaHari = $this->usia_nifas_hari;
        return $usiaHari !== null && $usiaHari <= 7;
    }

    /**
     * Get tanggal KF yang sudah di-format
     */
    public function getFormattedKf1TanggalAttribute()
    {
        return $this->kf1_tanggal ? $this->kf1_tanggal->format('d/m/Y H:i') : '-';
    }

    public function getFormattedKf2TanggalAttribute()
    {
        return $this->kf2_tanggal ? $this->kf2_tanggal->format('d/m/Y H:i') : '-';
    }

    public function getFormattedKf3TanggalAttribute()
    {
        return $this->kf3_tanggal ? $this->kf3_tanggal->format('d/m/Y H:i') : '-';
    }

    public function getFormattedKf4TanggalAttribute()
    {
        return $this->kf4_tanggal ? $this->kf4_tanggal->format('d/m/Y H:i') : '-';
    }

    // ========== METHOD UTILITAS ==========

    /**
     * Cek akses berdasarkan kecamatan puskesmas
     * DIPAKAI DI CONTROLLER untuk validasi akses
     */
    public function hasAccessByKecamatan($kecamatanPuskesmas)
    {
        $kecamatanPasien = optional($this->pasien)->PKecamatan;
        return $kecamatanPasien === $kecamatanPuskesmas;
    }

    /**
     * Cek apakah pasien sudah meninggal berdasarkan kesimpulan KF
     */
    public function isMeninggal()
    {
        return DB::table('kf_kunjungans')
            ->where('nifasable_id', $this->id)
            ->where('nifasable_type', get_class($this)) // Perbaikan di sini
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
            ->where('nifasable_id', $this->id)
            ->where('nifasable_type', get_class($this)) // Perbaikan di sini
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'meninggal'")
                  ->orWhereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'wafat'");
            })
            ->min('jenis_kf');
    }

    /**
     * Get data untuk chart/statistik
     */
    public static function getStatisticsByKecamatan($kecamatan)
    {
        $total = self::filterByKecamatan($kecamatan)->count();
        $nifasAwal = self::filterByKecamatan($kecamatan)
            ->get()
            ->filter(function ($item) {
                return $item->is_nifas_awal;
            })->count();
        
        $nifasSelesai = self::filterByKecamatan($kecamatan)
            ->get()
            ->filter(function ($item) {
                return $item->is_nifas_selesai;
            })->count();
        
        $nifasBerjalan = $total - $nifasSelesai;

        return [
            'total' => $total,
            'nifas_awal' => $nifasAwal,
            'nifas_berjalan' => $nifasBerjalan,
            'nifas_selesai' => $nifasSelesai,
        ];
    }

    /**
     * Get semua kecamatan yang ada data pasien nifas bidan
     */
    public static function getAllKecamatan()
    {
        return DB::table('pasien_nifas_bidan as pnb')
            ->join('pasiens as p', 'pnb.pasien_id', '=', 'p.id')
            ->select('p.PKecamatan as kecamatan')
            ->distinct()
            ->orderBy('kecamatan')
            ->pluck('kecamatan')
            ->toArray();
    }

    /**
     * Get data untuk dropdown filter
     */
    public static function getFilterOptions()
    {
        $bidans = DB::table('pasien_nifas_bidan')
            ->join('bidans', 'pasien_nifas_bidan.bidan_id', '=', 'bidans.id')
            ->select('bidans.id', 'bidans.nama')
            ->distinct()
            ->orderBy('bidans.nama')
            ->get();

        $tahun = DB::table('pasien_nifas_bidan')
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai_nifas) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        return [
            'bidans' => $bidans,
            'tahun' => $tahun,
        ];
    }

    /**
     * Export data untuk Excel/PDF
     */
    public function toExportArray()
    {
        return [
            'ID' => $this->id,
            'Nama Pasien' => optional($this->pasien->user)->name ?? '-',
            'NIK' => $this->pasien->nik ?? '-',
            'Bidan' => optional($this->bidan)->nama ?? '-',
            'Tanggal Mulai Nifas' => $this->formatted_tanggal_mulai,
            'Usia Nifas (hari)' => $this->usia_nifas_hari ?? '-',
            'Status Nifas' => $this->status_nifas,
            'Kecamatan' => $this->pasien->PKecamatan ?? '-',
            'Alamat' => optional($this->pasien->user)->address ?? '-',
            'No. Telepon' => optional($this->pasien->user)->phone ?? '-',
            'Tanggal Input' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    /**
     * Cek apakah data valid untuk ditampilkan
     */
    public function isValid()
    {
        return $this->pasien_id && $this->bidan_id && $this->tanggal_mulai_nifas;
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
}