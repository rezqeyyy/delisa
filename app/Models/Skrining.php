<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skrining extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'skrinings';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pasien_id',
        'puskesmas_id',
        'status_pre_eklampsia',
        'jumlah_resiko_sedang',
        'jumlah_resiko_tinggi',
        'kesimpulan',
        'step_form',
        'tindak_lanjut',
        'checked_status',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Pasien.
     * Setiap skrining dimiliki oleh satu pasien.
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Puskesmas.
     * Setiap skrining terikat pada satu puskesmas.
     */
    public function puskesmas()
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id');
    }

    /**
     * Mendefinisikan relasi "hasOne" ke model KondisiKesehatan.
     */
    public function kondisiKesehatan()
    {
        // Asumsi nama modelnya adalah KondisiKesehatan
        return $this->hasOne(KondisiKesehatan::class, 'skrining_id');
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model JawabanKuisioner.
     */
    public function jawabanKuisioners()
    {
        // Asumsi nama modelnya adalah JawabanKuisioner
        return $this->hasMany(JawabanKuisioner::class, 'skrining_id');
    }
    
    /**
     * Mendefinisikan relasi "hasMany" ke model RiwayatKehamilan.
     */
    public function riwayatKehamilans()
    {
        // Asumsi nama modelnya adalah RiwayatKehamilan
        return $this->hasMany(RiwayatKehamilan::class, 'skrining_id');
    }

    /**
     * Mendefinisikan relasi "hasOne" ke model RiwayatKehamilanGpa.
     */
    public function riwayatKehamilanGpa()
    {
        return $this->hasOne(RiwayatKehamilanGpa::class, 'skrining_id');
    }
}