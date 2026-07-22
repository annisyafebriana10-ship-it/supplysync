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

class CountryController extends Controller
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

    public function index()
    {
        $countriesList = DB::table('countries')->orderBy('name', 'asc')->pluck('name');
        $result = $this->countryService->getCountry('Indonesia');
        
        $country = null;
        $weather = null;
        $economy = null;
        $exchangeRate = null;
        $news = [];
        $riskData = null; // Wadah untuk Skor Risiko
        
        if ($result && !empty($result['data']['objects'])) {
            $country = $result['data']['objects'][0];
            
            if (isset($country['coordinates']['lat']) && isset($country['coordinates']['lng'])) {
                $lat = number_format((float) $country['coordinates']['lat'], 6, '.', '');
                $lng = number_format((float) $country['coordinates']['lng'], 6, '.', '');
                $weather = $this->weatherService->getCurrentWeather($lat, $lng);
            }

            if (isset($country['codes']['alpha_2'])) {
                $economy = $this->worldBankService->getEconomyData($country['codes']['alpha_2']);
            }

            if (isset($country['currencies'][0]['code'])) {
                $currencyCode = $country['currencies'][0]['code'];
                $exchangeRate = $this->exchangeRateService->getExchangeRate($currencyCode);
            }

            $countryName = $country['names']['common'] ?? 'Indonesia';
            $news = $this->newsSentimentService->getNewsWithSentiment($countryName);
            
            // Kalkulasi Skor Risiko Final
            $riskData = $this->riskScoringService->calculateRisk($weather, $economy, $news);
        }

        return view('dashboard', compact('country', 'countriesList', 'weather', 'economy', 'exchangeRate', 'news', 'riskData'));
    }

    public function search(Request $request)
    {
        $request->validate(['country' => 'required']);
        $countriesList = DB::table('countries')->orderBy('name', 'asc')->pluck('name');
        $result = $this->countryService->getCountry($request->country);

        if (!$result || empty($result['data']['objects'])) {
            return back()->with('error', 'Negara tidak ditemukan');
        }

        $country = $result['data']['objects'][0];
        $weather = null;
        $economy = null;
        $exchangeRate = null;
        $news = [];
        $riskData = null; // Wadah untuk Skor Risiko

        if (isset($country['coordinates']['lat']) && isset($country['coordinates']['lng'])) {
            $lat = number_format((float) $country['coordinates']['lat'], 6, '.', '');
            $lng = number_format((float) $country['coordinates']['lng'], 6, '.', '');
            $weather = $this->weatherService->getCurrentWeather($lat, $lng);
        }

        if (isset($country['codes']['alpha_2'])) {
            $economy = $this->worldBankService->getEconomyData($country['codes']['alpha_2']);
        }

        if (isset($country['currencies'][0]['code'])) {
            $currencyCode = $country['currencies'][0]['code'];
            $exchangeRate = $this->exchangeRateService->getExchangeRate($currencyCode);
        }

        $countryName = $country['names']['common'] ?? $request->country;
        $news = $this->newsSentimentService->getNewsWithSentiment($countryName);
        
        // Kalkulasi Skor Risiko Final
        $riskData = $this->riskScoringService->calculateRisk($weather, $economy, $news);

        return view('dashboard', compact('country', 'countriesList', 'weather', 'economy', 'exchangeRate', 'news', 'riskData'));
    }
}