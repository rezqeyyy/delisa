<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Arahkan route utama ke halaman login untuk sementara
Route::get('/', function () {
    return redirect()->route('login');
});