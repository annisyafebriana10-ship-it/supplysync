<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CountryService
{
    public function getCountry($country)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.restcountries.api_key'),
            'Accept' => 'application/json',
        ])->get(
            config('services.restcountries.base_url') . '/names.common/' . urlencode($country)
        );

        return $response->json();
    }
}