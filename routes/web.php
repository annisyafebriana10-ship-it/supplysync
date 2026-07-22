<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\AdminOnly;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\MapController;
use App\Models\Port; // Jangan lupa panggil model Port di atas
use App\Models\Country;
use App\Services\WeatherService;
use App\Http\Controllers\MarketIntelligenceController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\VisualizationController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\WatchlistController;
// ==========================================
// TEST DULU
// ==========================================

use App\Http\Controllers\FirebaseAuthController;

Route::post('/firebase-login', [FirebaseAuthController::class, 'login']);
Route::post('/firebase-logout', [FirebaseAuthController::class, 'logout']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

// ==========================================
// RUTE AUTENTIKASI (PUBLIK)
// ==========================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [AuthController::class, 'login'])->name('login.process');
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [FirebaseAuthController::class, 'logout'])
    ->name('logout');

// ==========================================
// RUTE HALAMAN UTAMA / DASHBOARD DEPAN (PUBLIK)
// ==========================================

// GAS! Arahkan langsung ke CountryController agar semua API otomatis jalan
Route::get('/', [CountryController::class, 'index']); 


// ==========================================
// RUTE KHUSUS ADMIN (DIGEMBOK MIDDLEWARE AUTH & PREFIX /admin)
// ==========================================
Route::middleware(['auth', PreventBackHistory::class, AdminOnly::class])->prefix('admin')->group(function () {    
    
    // Rute Dasbor Admin (/admin/dashboard)
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Rute Manajemen Pengguna
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store'); 
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Rute Manajemen Pelabuhan
    Route::get('/ports', [AdminController::class, 'ports'])->name('admin.ports');
    Route::post('/ports', [AdminController::class, 'storePort'])->name('admin.ports.store');
    Route::put('/ports/{id}', [AdminController::class, 'updatePort'])->name('admin.ports.update');
    Route::delete('/ports/{id}', [AdminController::class, 'deletePort'])->name('admin.ports.delete');

    // Rute Manajemen Artikel
    Route::get('/articles', [AdminController::class, 'articles'])->name('admin.articles');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('admin.articles.store');
    Route::put('/articles/{id}', [AdminController::class, 'updateArticle'])->name('admin.articles.update');
    Route::delete('/articles/{id}', [AdminController::class, 'deleteArticle'])->name('admin.articles.delete');
    
});

// Rute Pencarian Negara (dari teman)
Route::get('/country', [CountryController::class, 'index'])->name('country.index');
Route::post('/country', [CountryController::class, 'search'])->name('country.search');

// 1. Rute untuk menampilkan Halaman Peta
Route::get('/geospatial/map', [MapController::class, 'index'])->name('map.index');

// 2. Rute REST API untuk menyedot data pelabuhan
// Tambahkan '/api' di sini agar cocok dengan perintah fetch() di Javascript
Route::get('/api/ports', function () {
    return response()->json(
        Port::select('ports.name', 'ports.code', 'ports.lat', 'ports.lng', 'countries.name as country_name')
            ->join('countries', 'ports.country_id', '=', 'countries.id')
            ->get()
    );
});


// Endpoint untuk mengambil titik koordinat negara di peta
Route::get('/api/countries', function () {
    return response()->json(Country::select('name', 'lat', 'lng')->get());
});

// Endpoint untuk menyedot cuaca satelit secara Real-Time via WeatherService
Route::get('/api/weather/live', function (Request $request, WeatherService $weatherService) {
    $request->validate(['lat' => 'required', 'lng' => 'required']);
    
    $weather = $weatherService->getCurrentWeather($request->lat, $request->lng);
    return response()->json($weather);
});


// Rute untuk Market Intelligence
Route::get('/market/currency', [MarketIntelligenceController::class, 'currency'])->name('market.currency');
Route::get('/market/news', [MarketIntelligenceController::class, 'news'])->name('market.news');



// Route untuk halaman Risk Scoring Engine
Route::get('/analytics/risk', [RiskController::class, 'index'])->name('analytics.risk');


Route::get('/analytics/visualization', [VisualizationController::class, 'index'])->name('analytics.visualization');

Route::get('/tools/compare', [ComparisonController::class, 'index'])->name('tools.compare');

// Rute Terproteksi (WAJIB LOGIN)
Route::middleware(['auth'])->group(function () {
    
    // ... rute-rute dashboard kamu sebelumnya ada di sini ...

    // Rute Watchlist yang benar
    Route::get('/tools/watchlist', [WatchlistController::class, 'index'])->name('tools.watchlist');
    Route::post('/tools/watchlist', [WatchlistController::class, 'store'])->name('tools.watchlist.store');
    Route::delete('/tools/watchlist/{id}', [WatchlistController::class, 'destroy'])->name('tools.watchlist.destroy');

});