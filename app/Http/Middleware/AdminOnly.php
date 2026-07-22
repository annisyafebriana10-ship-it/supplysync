<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah pengguna sudah login, dan apakah rolenya adalah 'admin'
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // Lolos, silakan masuk ke halaman admin
        }

        // 2. Jika bukan admin, tendang ke halaman utama dengan pesan error keamanan
        return redirect('/')->with('error', 'Akses ditolak! Anda tidak memiliki hak akses untuk masuk ke halaman Admin Panel.');
    }
}