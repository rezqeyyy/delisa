<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    // ✅ Izinkan kolom yang bisa diisi secara mass assignment
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

    // ✅ Hidden fields agar password tidak ikut dikirim ke response
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ✅ Relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
