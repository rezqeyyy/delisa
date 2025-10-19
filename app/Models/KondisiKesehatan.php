<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KondisiKesehatan extends Model
{
    use HasFactory;
    
    // Tentukan nama tabelnya
    protected $table = 'kondisi_kesehatans';

    // Izinkan semua field diisi (jika kamu tidak pakai $fillable)
    protected $guarded = [];

    /**
     * Relasi ke Skrining
     */
    public function skrining()
    {
        return $this->belongsTo(Skrining::class, 'skrining_id');
    }
}