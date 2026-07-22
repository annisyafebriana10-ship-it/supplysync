<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    use HasFactory;

    // Ubah country_name menjadi country_id
    protected $fillable = [
        'user_id',
        'country_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}