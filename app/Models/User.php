<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $fillable = [
        'name','email','password','photo','phone','address','status','role_id','remember_token'
    ];
    protected $hidden = ['password','remember_token'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function bidan()
    {
        return $this->hasOne(Bidan::class, 'user_id');
    }

    public function rumahSakit()
    {
        return $this->hasOne(RumahSakit::class, 'user_id');
    }

    // scope pencarian umum
    public function scopeSearch($q, $keyword)
    {
        if (!$keyword) return;
        $q->where(function($qq) use ($keyword) {
            $qq->where('name','like',"%{$keyword}%")
               ->orWhere('email','like',"%{$keyword}%");
        });
    }
}
