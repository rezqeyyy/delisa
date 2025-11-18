<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResepObat extends Model
{
    use HasFactory;

    protected $table = 'resep_obats';

    protected $fillable = [
        'rujukan_rs_id',
        'resep_obat',
        'dosis',
        'penggunaan',
    ];

    public function rujukanRs()
    {
        return $this->belongsTo(RujukanRs::class, 'rujukan_rs_id');
    }
}
