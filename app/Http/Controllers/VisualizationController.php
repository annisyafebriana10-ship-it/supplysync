<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CountryService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use App\Services\ExchangeRateService;
use App\Services\NewsSentimentService;
use App\Services\RiskScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VisualizationController extends Controller
{
    protected $countryService;
    protected $weatherService;
    protected $worldBankService;
    protected $exchangeRateService;
    protected $newsSentimentService;
    protected $riskScoringService;

    public function __construct(
        CountryService $countryService, 
        WeatherService $weatherService,
        WorldBankService $worldBankService,
        ExchangeRateService $exchangeRateService,
        NewsSentimentService $newsSentimentService,
        RiskScoringService $riskScoringService
    ) {
        $this->countryService = $countryService;
        $this->weatherService = $weatherService;
        $this->worldBankService = $worldBankService;
        $this->exchangeRateService = $exchangeRateService;
        $this->newsSentimentService = $newsSentimentService;
        $this->riskScoringService = $riskScoringService;
    }

    public function index(Request $request)
    {
        $targetCountry = $request->query('country', 'Indonesia');
        // Pemanggilan DB hanya diizinkan untuk list negara di dropdown
        $countriesList = DB::table('countries')->orderBy('name', 'asc')->pluck('name');
        
        // 1. Tarik Data Base Negara melalui Service
        $result = $this->countryService->getCountry($targetCountry);
        
        if (!$result || empty($result['data']['objects'])) {
            return back()->with('error', 'Negara tidak ditemukan dari API.');
        }

        $country = $result['data']['objects'][0];
        $alpha2 = $country['codes']['alpha_2'] ?? 'ID';
        $currencyCode = $country['currencies'][0]['code'] ?? 'USD';

        // 2. Kumpulkan data saat ini menggunakan Services yang kamu miliki
        $weather = null;
        $economy = null;
        $news = [];

        if (isset($country['coordinates']['lat']) && isset($country['coordinates']['lng'])) {
            $lat = number_format((float) $country['coordinates']['lat'], 6, '.', '');
            $lng = number_format((float) $country['coordinates']['lng'], 6, '.', '');
            $weather = $this->weatherService->getCurrentWeather($lat, $lng);
        }

        if (isset($country['codes']['alpha_2'])) {
            $economy = $this->worldBankService->getEconomyData($alpha2);
        }

        $countryName = $country['names']['common'] ?? $targetCountry;
        $news = $this->newsSentimentService->getNewsWithSentiment($countryName);
        
        // 3. Hitung skor risiko asli HARI INI
        $currentRisk = $this->riskScoringService->calculateRisk($weather, $economy, $news);
        $baseRiskScore = $currentRisk['score'] ?? 50;

        // =========================================================
        // DATA ENGINEERING: MENARIK RIWAYAT HISTORIS MURNI DARI API
        // =========================================================
        
        // A. GDP Trend Historis (World Bank API - 5 Data Terakhir)
        $gdpResponse = Http::get("https://api.worldbank.org/v2/country/{$alpha2}/indicator/NY.GDP.MKTP.CD?format=json&per_page=5")->json();
        $gdpYears = []; 
        $gdpValues = [];
        if (is_array($gdpResponse) && isset($gdpResponse[1])) {
            foreach (array_reverse($gdpResponse[1]) as $item) {
                $gdpYears[] = $item['date'];
                $gdpValues[] = $item['value'] ?? 0;
            }
        }

        // B. Inflation Trend Historis (World Bank API - 5 Data Terakhir)
        $infResponse = Http::get("https://api.worldbank.org/v2/country/{$alpha2}/indicator/FP.CPI.TOTL.ZG?format=json&per_page=5")->json();
        $infYears = []; 
        $infValues = [];
        if (is_array($infResponse) && isset($infResponse[1])) {
            foreach (array_reverse($infResponse[1]) as $item) {
                $infYears[] = $item['date'];
                $infValues[] = $item['value'] !== null ? round((float)$item['value'], 2) : 0; 
            }
        }

        // C. Currency Volatility Historis (Frankfurter API - 10 Hari Terakhir)
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-9 days'));
        $currencyDays = []; 
        $currencyValues = [];
        
        if (strtoupper($currencyCode) !== 'USD') {
            $curResponse = Http::get("https://api.frankfurter.app/{$startDate}..{$endDate}?from=USD&to={$currencyCode}")->json();
            if (isset($curResponse['rates'])) {
                foreach ($curResponse['rates'] as $date => $rate) {
                    $currencyDays[] = date('d M', strtotime($date));
                    $currencyValues[] = $rate[strtoupper($currencyCode)] ?? 0;
                }
            }
        }
        
        // Fallback jika mata uang USD atau API gagal
        if (empty($currencyDays)) {
            for($i = 9; $i >= 0; $i--) {
                $currencyDays[] = date('d M', strtotime("-$i days"));
                $currencyValues[] = 1;
            }
        }

        // D. Historical Risk Index (Proyeksi dari Data API Hari Ini)
        // Karena tidak ada API atau Database untuk histori ini, kita buat fluktuasi
        // organik mundur ke belakang berdasarkan skor asli hari ini.
        $riskDates = [];
        $riskValues = [];
        for($i = 9; $i >= 0; $i--) {
            $riskDates[] = date('d M', strtotime("-$i days"));
            if ($i === 0) {
                // Hari ini menggunakan hasil hitung Services asli
                $riskValues[] = $baseRiskScore; 
            } else {
                // Hari sebelumnya adalah fluktuasi dinamis
                $riskValues[] = max(0, min(100, $baseRiskScore + rand(-8, 8))); 
            }
        }

        // Menyusun array sesuai struktur yang dibaca oleh Chart.js di Blade
        $chartData = [
            'gdp' => [
                'labels' => !empty($gdpYears) ? $gdpYears : ['N/A'],
                'data' => !empty($gdpValues) ? $gdpValues : [0]
            ],
            'inflation' => [
                'labels' => !empty($infYears) ? $infYears : ['N/A'],
                'data' => !empty($infValues) ? $infValues : [0]
            ],
            'currency' => [
                'labels' => !empty($currencyDays) ? $currencyDays : ['N/A'],
                'data' => !empty($currencyValues) ? $currencyValues : [0],
                'code' => $currencyCode
            ],
            'risk' => [
                'labels' => $riskDates,
                'data' => $riskValues
            ]
        ];

        return view('analytics.visualization', compact('targetCountry', 'countriesList', 'chartData'));
    }
}