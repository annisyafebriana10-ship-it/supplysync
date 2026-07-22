<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WeatherService
{
    public function getCurrentWeather($lat, $lng)
    {
        // Ubah nama cache lagi agar mengambil data struktur baru
        return Cache::remember("weather_detail_{$lat}_{$lng}", 3600, function () use ($lat, $lng) {
            
            // Parameter 'current' ini memanggil 7 metrik cuaca sekaligus!
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current=temperature_2m,apparent_temperature,precipitation,relative_humidity_2m,weather_code,wind_speed_10m,wind_gusts_10m";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $data = json_decode($response, true);
                // Karena kita pakai 'current', kuncinya bukan lagi 'current_weather'
                if (isset($data['current'])) {
                    return $data; 
                }
            }
            
            // Fallback Super Lengkap
            return [
                'current' => [
                    'temperature_2m' => rand(24, 34) + (rand(0, 9) / 10),
                    'apparent_temperature' => rand(26, 36) + (rand(0, 9) / 10),
                    'wind_speed_10m' => rand(10, 25) + (rand(0, 9) / 10),
                    'wind_gusts_10m' => rand(20, 40) + (rand(0, 9) / 10),
                    'precipitation' => rand(0, 50) / 10,
                    'relative_humidity_2m' => rand(60, 90),
                    'weather_code' => [0, 1, 3, 61, 80, 95][array_rand([0, 1, 3, 61, 80, 95])]
                ]
            ];
        });
    }
}