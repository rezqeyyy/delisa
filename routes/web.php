<?php

use Illuminate\Support\Facades\Route;

// Jadikan halaman login sebagai halaman utama
Route::get('/', function () {
    // Kita akan menempatkan view di dalam folder 'auth' agar rapi
    return view('auth.login-delisa');
});