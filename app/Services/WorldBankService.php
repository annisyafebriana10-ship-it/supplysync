<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WorldBankService
{
    public function getEconomyData($iso2Code)
    {
        // Ganti nama cache agar Laravel mau menarik 5 data baru ini
        return Cache::remember("economy_wb_full_{$iso2Code}", 86400, function () use ($iso2Code) {
            
            // 5 Indikator sesuai brief dosen
            $indicators = [
                'gdp' => 'NY.GDP.MKTP.CD',
                'inflation' => 'FP.CPI.TOTL.ZG',
                'population' => 'SP.POP.TOTL',
                'exports' => 'NE.EXP.GNFS.CD',
                'imports' => 'NE.IMP.GNFS.CD'
            ];

            $results = [
                'gdp' => '--',
                'inflation' => '--',
                'population' => '--',
                'exports' => '--',
                'imports' => '--'
            ];

            foreach ($indicators as $key => $indicator) {
                // mrnev=1 akan mencari data tahun terbaru yang tidak kosong
                $url = "https://api.worldbank.org/v2/country/{$iso2Code}/indicator/{$indicator}?format=json&mrnev=1";
                $data = $this->fetchNative($url);

                if (isset($data[1][0]['value'])) {
                    $val = $data[1][0]['value'];
                    
                    if ($key === 'inflation') {
                        $results[$key] = number_format($val, 1);
                    } elseif ($key === 'population') {
                        $results[$key] = number_format($val, 0, ',', '.');
                    } else {
                        // Format Uang untuk GDP, Ekspor, Impor
                        $results[$key] = $this->formatMoney($val);
                    }
                }
            }

            return $results;
        });
    }

    private function formatMoney($val)
    {
        if ($val >= 1000000000000) {
            return '$' . number_format($val / 1000000000000, 2) . ' T';
        } elseif ($val >= 1000000000) {
            return '$' . number_format($val / 1000000000, 2) . ' B';
        } elseif ($val >= 1000000) {
            return '$' . number_format($val / 1000000, 2) . ' M';
        } else {
            return '$' . number_format($val);
        }
    }

    private function fetchNative($url)
    {
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
            return json_decode($response, true);
        }
        return null;
    }
}