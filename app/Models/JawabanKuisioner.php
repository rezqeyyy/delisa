<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanKuisioner extends Model
{
    use HasFactory;

    protected $table = 'jawaban_kuisioners';
    protected $guarded = [];

    public function skrining()
    {
        return $this->belongsTo(Skrining::class, 'skrining_id');
    }

    public function kuisioner()
    {
        return $this->belongsTo(KuisionerPasien::class, 'kuisioner_id');
    }
}