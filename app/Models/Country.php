<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    // Hanya izinkan 3 kolom ini untuk diisi
    protected $fillable = ['name', 'lat', 'lng'];

    public function ports()
    {
        return $this->hasMany(Port::class);
    }
}