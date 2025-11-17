<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResepObat extends Model
{
    use HasFactory;

    protected $table = 'resep_obats';

    protected $fillable = [
        'riwayat_rujukan_id',
        'resep_obat',
        'dosis',
        'penggunaan',
    ];

    // Relasi ke Rujukan RS
    public function rujukanRs()
    {
        return $this->belongsTo(RujukanRs::class, 'riwayat_rujukan_id');
    }
}