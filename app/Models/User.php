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

    /**
     * Relasi ke Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // --- TAMBAHKAN INI ---

    /**
     * Relasi ke Bidan (jika user ini adalah Bidan)
     */
    public function bidan()
    {
        return $this->hasOne(Bidan::class, 'user_id');
    }

    /**
     * Relasi ke Pasien (jika user ini adalah Pasien)
     */
    public function pasien()
    {
        return $this->hasOne(Pasien::class, 'user_id');
    }

    /**
     * Relasi ke RumahSakit (jika user ini adalah admin RS)
     */
    public function rumahSakit()
    {
        return $this->hasOne(RumahSakit::class, 'user_id');
    }
}