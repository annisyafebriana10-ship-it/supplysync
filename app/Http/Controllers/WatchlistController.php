<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CountryService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use App\Services\ExchangeRateService;
use App\Services\NewsSentimentService;
use App\Services\RiskScoringService;

class WatchlistController extends Controller
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

    // 1. Tampilkan Halaman Watchlist
    public function index()
    {
        $user = Auth::user();
        
        // Kita join dengan tabel countries agar mendapat nama negaranya
        $watchlists = Watchlist::join('countries', 'watchlists.country_id', '=', 'countries.id')
                        ->where('watchlists.user_id', $user->id)
                        ->select('watchlists.*', 'countries.name as country_name')
                        ->get();

        // Ambil data id dan name untuk dropdown form
        $countriesList = DB::table('countries')->orderBy('name', 'asc')->get(['id', 'name']);

        $watchlistData = [];

        foreach ($watchlists as $item) {
            $data = $this->fetchCountryData($item->country_name);
            if ($data) {
                $data['watchlist_id'] = $item->id;
                $data['country_name'] = $item->country_name;
                $watchlistData[] = $data;
            }
        }

        return view('tools.watchlist', compact('watchlistData', 'countriesList'));
    }

    // 2. Simpan Negara ke Watchlist
    public function store(Request $request)
    {
        // Validasi sekarang mencari id, bukan string nama
        $request->validate([
            'country_id' => 'required|integer'
        ]);

        $user = Auth::user();

        // Cek duplikasi berdasarkan ID negara
        $exists = Watchlist::where('user_id', $user->id)
                           ->where('country_id', $request->country_id)
                           ->exists();

        if ($exists) {
            return back()->with('error', 'Negara tersebut sudah ada di Watchlist Anda.');
        }

        Watchlist::create([
            'user_id' => $user->id,
            'country_id' => $request->country_id
        ]);

        return back()->with('success', 'Negara berhasil ditambahkan ke Watchlist!');
    }
    // 3. Hapus Negara dari Watchlist
    public function destroy($id)
    {
        $watchlist = Watchlist::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $name = $watchlist->country_name;
        $watchlist->delete();

        return back()->with('success', $name . ' berhasil dihapus dari Watchlist.');
    }

    /**
     * Fungsi Helper untuk memproses API (Sama seperti di ComparisonController)
     */
    private function fetchCountryData($countryName)
    {
        $result = $this->countryService->getCountry($countryName);
        
        if (!$result || empty($result['data']['objects'])) return null;

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
            'weather' => $weather,
            'economy' => $economy,
            'currencyCode' => $currencyCode,
            'exchangeRate' => $exchangeRate,
            'risk' => $risk
        ];
    }
}