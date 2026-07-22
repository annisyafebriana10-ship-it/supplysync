<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        // Nantinya kita bisa melempar data dari sini, 
        // untuk sekarang kita panggil view-nya saja.
        return view('maps.index');
    }
}