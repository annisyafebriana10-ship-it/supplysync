<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        if (auth()->check()) {

            return auth()->user()->role == 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect('/');

        }

        return view('auth.register');
    }
}