<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    public function getExchangeRate($targetCurrency)
    {
        // Jika negara yang dicari memang menggunakan USD (misal Amerika Serikat), langsung kembalikan angka 1
        if (strtoupper($targetCurrency) === 'USD') {
            return 1.00;
        }

        // Cache data selama 12 jam (43200 detik)
        return Cache::remember("exchange_rate_{$targetCurrency}", 43200, function () use ($targetCurrency) {
            
            // Mengambil data nilai tukar terbaru dengan base USD
            $url = "https://open.er-api.com/v6/latest/USD";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                
                // Cari apakah mata uang negara tersebut ada di dalam daftar response API
                if (isset($data['rates'][$targetCurrency])) {
                    $rate = $data['rates'][$targetCurrency];
                    
                    // Format angka: jika nilainya besar (seperti Rupiah), hilangkan desimalnya
                    if ($rate > 100) {
                        return number_format($rate, 0, ',', '.');
                    } else {
                        // Jika nilainya kecil (seperti Euro/Poundsterling), tampilkan 2 desimal
                        return number_format($rate, 2, ',', '.');
                    }
                }
            }
            
            return '--';
        });
    }
}