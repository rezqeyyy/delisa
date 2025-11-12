<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuisionerPasien extends Model
{
    use HasFactory;

    protected $table = 'kuisioner_pasiens';
    protected $guarded = [];

    public function jawaban()
    {
        return $this->hasMany(JawabanKuisioner::class, 'kuisioner_id');
    }
}