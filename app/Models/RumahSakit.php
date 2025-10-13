<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RumahSakit extends Model
{
    protected $table = 'rumah_sakits';
    protected $fillable = ['user_id','nama','lokasi','kecamatan','kelurahan'];

    public function user() { return $this->belongsTo(User::class); }
}
