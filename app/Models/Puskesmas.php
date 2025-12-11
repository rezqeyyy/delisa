<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puskesmas extends Model
{
    protected $table = 'puskesmas';
    
    // TAMBAHKAN 'user_id' ke sini
    protected $fillable = [
        'nama_puskesmas',
        'lokasi',
        'kecamatan',
        'user_id',  // ← TAMBAHKAN INI
        'is_mandiri'
    ];
    
    public function bidans() { 
        return $this->hasMany(Bidan::class); 
    }
    
    // ===== TAMBAHKAN INI =====
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}