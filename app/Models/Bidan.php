<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidan extends Model
{
    protected $table = 'bidans';
    protected $fillable = ['user_id','nomor_izin_praktek','puskesmas_id'];

    public function user()       { return $this->belongsTo(User::class); }
    public function puskesmas()  { return $this->belongsTo(Puskesmas::class); }
}
