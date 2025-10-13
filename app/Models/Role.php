<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['nama_role'];
    // kalau kolom timestamps ada, biarkan default true
    // kalau PK bukan auto-increment, set $incrementing = false (opsional)
}
