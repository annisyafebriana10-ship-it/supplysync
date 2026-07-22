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

class ComparisonController extends Controller
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
        // Default negara jika belum ada yang dicari
        $country1Name = $request->query('country1', 'Indonesia');
        $country2Name = $request->query('country2', 'Singapore');

        $countriesList = DB::table('countries')->orderBy('name', 'asc')->pluck('name');

        // Tarik data untuk kedua negara menggunakan fungsi helper
        $data1 = $this->fetchCountryData($country1Name);
        $data2 = $this->fetchCountryData($country2Name);

        if (!$data1 || !$data2) {
            return back()->with('error', 'Gagal memuat data perbandingan. Pastikan nama negara valid.');
        }

        // Ubah dari view('analytics.compare', ...) menjadi:
        return view('tools.compare', compact('countriesList', 'country1Name', 'country2Name', 'data1', 'data2'));
    }

    /**
     * Fungsi Helper untuk memproses semua API 1 Negara sekaligus
     */
    private function fetchCountryData($countryName)
    {
        $result = $this->countryService->getCountry($countryName);

        if (!$result || empty($result['data']['objects'])) {
            return null;
        }

        $country = $result['data']['objects'][0];
        $alpha2 = $country['codes']['alpha_2'] ?? 'ID';
        $currencyCode = $country['currencies'][0]['code'] ?? 'USD';

        $weather = null;
        if (isset($country['coordinates']['lat']) && isset($country['coordinates']['lng'])) {
            $lat = number_format((float) $country['coordinates']['lat'], 6, '.', '');
            $lng = number_format((float) $country['coordinates']['lng'], 6, '.', '');
            $weather = $this->weatherService->getCurrentWeather($lat, $lng);
        }

        $economy = $this->worldBankService->getEconomyData($alpha2);
        $exchangeRate = $this->exchangeRateService->getExchangeRate($currencyCode);
        $news = $this->newsSentimentService->getNewsWithSentiment($country['names']['common'] ?? $countryName);

        $risk = $this->riskScoringService->calculateRisk($weather, $economy, $news);

        return [
            'base' => $country,
            'weather' => $weather,
            'economy' => $economy,
            'currencyCode' => $currencyCode,
            'exchangeRate' => $exchangeRate,
            'news' => $news,
            'risk' => $risk
        ];
    }
}