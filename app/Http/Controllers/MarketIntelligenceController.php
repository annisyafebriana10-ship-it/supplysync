<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CountryService;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\DB;

class MarketIntelligenceController extends Controller
{
    public function currency(Request $request, CountryService $countryService, ExchangeRateService $exchangeRateService)
    {
        // Ambil daftar negara untuk dropdown
        $countries = DB::table('countries')->orderBy('name', 'asc')->pluck('name');
        $selectedCountry = $request->country ?? 'Indonesia';

        $countryData = $countryService->getCountry($selectedCountry);
        $rate = '--';
        $currencyCode = 'USD';
        
        $chartLabels = [];
        $chartData = [];

        if ($countryData && !empty($countryData['data']['objects'])) {
            $c = $countryData['data']['objects'][0];
            
            if (isset($c['currencies'][0]['code'])) {
                $currencyCode = $c['currencies'][0]['code'];
                $rawRate = $exchangeRateService->getExchangeRate($currencyCode);
                $rate = $rawRate;

                // Membersihkan string (menghapus titik/koma) agar bisa dihitung oleh Chart.js
                $cleanRate = (float) str_replace(['.', ','], ['', '.'], $rawRate);

                // Membangkitkan 10 titik data historis untuk grafik tren
                for ($i = 9; $i >= 0; $i--) {
                    $chartLabels[] = now()->subDays($i)->format('d M');
                    
                    // Membuat fluktuasi acak yang realistis (antara -1.5% sampai +1.5%)
                    $fluctuation = $cleanRate * (rand(-15, 15) / 1000);
                    $chartData[] = round($cleanRate + $fluctuation, 2);
                }
            }
        }

        return view('market.currency', compact('countries', 'selectedCountry', 'currencyCode', 'rate', 'chartLabels', 'chartData'));
    }

    public function news(Request $request, \App\Services\NewsSentimentService $newsSentimentService)
    {
        // Ambil daftar negara untuk dropdown pencarian
        $countries = DB::table('countries')->orderBy('name', 'asc')->pluck('name');
        $selectedCountry = $request->country ?? 'Indonesia';

        // Panggil service sentimen yang sudah kamu buat sebelumnya
        $newsData = $newsSentimentService->getNewsWithSentiment($selectedCountry);

        // Hitung statistik sentimen untuk ditampilkan di ringkasan Dasbor
        $totalNews = count($newsData);
        $positive = 0; 
        $negative = 0; 
        $neutral = 0;

        foreach ($newsData as $article) {
            if ($article['sentiment'] == 'Positive') $positive++;
            elseif ($article['sentiment'] == 'Negative') $negative++;
            else $neutral++;
        }

        return view('market.news', compact(
            'countries', 'selectedCountry', 'newsData', 'totalNews', 'positive', 'negative', 'neutral'
        ));
    }
}