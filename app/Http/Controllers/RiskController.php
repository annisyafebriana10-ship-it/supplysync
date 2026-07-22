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

class RiskController extends Controller
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
        // Ambil negara dari request, default ke Indonesia jika kosong
        $targetCountry = $request->query('country', 'Indonesia');
        
        $countriesList = DB::table('countries')->orderBy('name', 'asc')->pluck('name');
        $result = $this->countryService->getCountry($targetCountry);
        
        $country = null;
        $weather = null;
        $economy = null;
        $news = [];
        $riskData = null;

        if ($result && !empty($result['data']['objects'])) {
            $country = $result['data']['objects'][0];
            
            // 1. Ekstrak Cuaca
            if (isset($country['coordinates']['lat']) && isset($country['coordinates']['lng'])) {
                $lat = number_format((float) $country['coordinates']['lat'], 6, '.', '');
                $lng = number_format((float) $country['coordinates']['lng'], 6, '.', '');
                $weather = $this->weatherService->getCurrentWeather($lat, $lng);
            }

            // 2. Ekstrak Ekonomi
            if (isset($country['codes']['alpha_2'])) {
                $economy = $this->worldBankService->getEconomyData($country['codes']['alpha_2']);
            }

            // 3. Ekstrak Berita
            $countryName = $country['names']['common'] ?? $targetCountry;
            $news = $this->newsSentimentService->getNewsWithSentiment($countryName);
            
            // 4. Kalkulasi Risk Engine
            $riskData = $this->riskScoringService->calculateRisk($weather, $economy, $news);
        } else {
            return back()->with('error', 'Data negara tidak ditemukan untuk analisis risiko.');
        }

        // Return ke view risk.blade.php yang sudah kita buat sebelumnya
        return view('analytics.risk', compact('country', 'countriesList', 'riskData', 'targetCountry'));
    }
}