<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'phone',
        'address',
        'status',
        'role_id',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function bidan()
    {
        return $this->hasOne(Bidan::class, 'user_id');
    }

    public function pasien()
    {
        return $this->hasOne(Pasien::class, 'user_id');
    }

    public function rumahSakit()
    {
        return $this->hasOne(RumahSakit::class, 'user_id');
    }

    // ===== TAMBAHKAN INI =====
    public function puskesmas()
    {
        return $this->hasOne(Puskesmas::class, 'user_id');
    }
}