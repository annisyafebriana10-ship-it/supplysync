<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    // Daftarkan kolom mana saja yang boleh diisi datanya (Mass Assignment)
    protected $fillable = [
        'name',
        'code',
        'country_id',
        'lat',
        'lng'
    ];

    /**
     * Relasi ke tabel Countries
     * Satu pelabuhan hanya dimiliki oleh satu negara (belongsTo)
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}