<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\AdminOnly;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\MapController;
use App\Models\Port; 
use App\Models\Country;
use App\Services\WeatherService;
use App\Http\Controllers\MarketIntelligenceController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\VisualizationController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\FirebaseAuthController;


// RUTE HALAMAN UTAMA / DASHBOARD DEPAN (PUBLIK)
Route::get('/', [CountryController::class, 'index']); 


// FIREWALL: RATE LIMITING (MITIGASI BRUTE-FORCE & DDOS)
// Membatasi akses maksimal 10 kali dalam 1 menit
Route::middleware(['throttle:10,1'])->group(function () {
    
    // 1. Perlindungan Jalur Autentikasi (Anti Brute-Force)
    Route::post('/firebase-login', [FirebaseAuthController::class, 'login']);
    Route::post('/firebase-logout', [FirebaseAuthController::class, 'logout']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/logout', [FirebaseAuthController::class, 'logout'])->name('logout');

    // 2. Perlindungan Jalur API & Pencarian (Anti DDoS / Spam Data)
    Route::post('/country', [CountryController::class, 'search'])->name('country.search');
    
    Route::get('/api/ports', function () {
        return response()->json(
            Port::select('ports.name', 'ports.code', 'ports.lat', 'ports.lng', 'countries.name as country_name')
                ->join('countries', 'ports.country_id', '=', 'countries.id')
                ->get()
        );
    });

    Route::get('/api/countries', function () {
        return response()->json(Country::select('name', 'lat', 'lng')->get());
    });

    Route::get('/api/weather/live', function (Request $request, WeatherService $weatherService) {
        $request->validate(['lat' => 'required', 'lng' => 'required']);
        return response()->json($weatherService->getCurrentWeather($request->lat, $request->lng));
    });

});


// RUTE KHUSUS ADMIN (DIGEMBOK MIDDLEWARE AUTH & PREFIX /admin)
Route::middleware(['auth', PreventBackHistory::class, AdminOnly::class])->prefix('admin')->group(function () {    
    
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Manajemen Pengguna
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store'); 
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Manajemen Pelabuhan
    Route::get('/ports', [AdminController::class, 'ports'])->name('admin.ports');
    Route::post('/ports', [AdminController::class, 'storePort'])->name('admin.ports.store');
    Route::put('/ports/{id}', [AdminController::class, 'updatePort'])->name('admin.ports.update');
    Route::delete('/ports/{id}', [AdminController::class, 'deletePort'])->name('admin.ports.delete');

    // Manajemen Artikel
    Route::get('/articles', [AdminController::class, 'articles'])->name('admin.articles');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('admin.articles.store');
    Route::put('/articles/{id}', [AdminController::class, 'updateArticle'])->name('admin.articles.update');
    Route::delete('/articles/{id}', [AdminController::class, 'deleteArticle'])->name('admin.articles.delete');
    
     // BACKUP
    Route::get('/backup-database', function () {
        try {
            // 1. Eksekusi proses pencadangan database
            \Illuminate\Support\Facades\Artisan::call('backup:run', ['--only-db' => true]);
            
            // 2. Cari file .zip terbaru yang baru saja dibuat
            $backupName = config('backup.backup.name');
            $files = \Illuminate\Support\Facades\Storage::disk('local')->files($backupName);
            
            // Ambil file zip yang paling terakhir dibuat
            $latestBackup = end($files);
            
            // 3. Langsung unduh file tersebut ke laptop
            if ($latestBackup) {
                return \Illuminate\Support\Facades\Storage::disk('local')->download($latestBackup);
            }
            return "Backup gagal ditemukan di server.";
            
        } catch (\Exception $e) {
            return "Terjadi kesalahan: " . $e->getMessage();
        }
    });
});


// RUTE ALAT & VISUALISASI LAINNYA (PUBLIK)
Route::get('/country', [CountryController::class, 'index'])->name('country.index');
Route::get('/geospatial/map', [MapController::class, 'index'])->name('map.index');
Route::get('/market/currency', [MarketIntelligenceController::class, 'currency'])->name('market.currency');
Route::get('/market/news', [MarketIntelligenceController::class, 'news'])->name('market.news');
Route::get('/analytics/risk', [RiskController::class, 'index'])->name('analytics.risk');
Route::get('/analytics/visualization', [VisualizationController::class, 'index'])->name('analytics.visualization');
Route::get('/tools/compare', [ComparisonController::class, 'index'])->name('tools.compare');


// RUTE TERPROTEKSI (WAJIB LOGIN)
Route::middleware(['auth'])->group(function () {
    
    Route::get('/tools/watchlist', [WatchlistController::class, 'index'])->name('tools.watchlist');
    Route::post('/tools/watchlist', [WatchlistController::class, 'store'])->name('tools.watchlist.store');
    Route::delete('/tools/watchlist/{id}', [WatchlistController::class, 'destroy'])->name('tools.watchlist.destroy');

});


   