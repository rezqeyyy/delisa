<?php

namespace App\Http\Controllers;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
}