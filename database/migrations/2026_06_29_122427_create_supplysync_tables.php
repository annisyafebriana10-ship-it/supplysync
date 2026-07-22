<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan cetak biru untuk membuat tabel-tabel.
     */
    public function up(): void
    {
        // 1. Tabel Negara (Countries) - Murni Nama & Koordinat
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('lat', 10, 6)->nullable(); // Koordinat untuk Open-Meteo & Peta
            $table->decimal('lng', 10, 6)->nullable(); // Koordinat untuk Open-Meteo & Peta
            $table->timestamps();
        });

       // 2. Tabel Lokasi Pelabuhan (Ports)
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // TAMBAHKAN INI UNTUK KODE PELABUHAN
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();
        });

        // 3. Tabel Kamus Sentimen Positif (Untuk Fitur Data Science)
        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });

        // 4. Tabel Kamus Sentimen Negatif (Untuk Fitur Data Science)
        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });

        // 5. Tabel Penyimpanan Berita (News Cache)
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('sentiment')->default('Neutral'); // Hasil sentimen: Positive/Neutral/Negative
            $table->timestamps();
        });

        // 6. Tabel Skor Risiko (Risk Scores)
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->float('weather_score')->default(0);
            $table->float('inflation_score')->default(0);
            $table->float('news_score')->default(0);
            $table->float('currency_score')->default(0);
            $table->float('total_risk')->default(0);
            $table->string('risk_level'); // Level: Low, Medium, High
            $table->timestamps();
        });

        // 7. Tabel Daftar Pantauan Pengguna (Watchlists)
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->timestamps();
        });

        // 8. Tabel Artikel dari Admin (Articles)
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Membatalkan atau menghapus tabel jika terjadi kesalahan.
     */
    public function down(): void
    {
        // Menghapus tabel dengan urutan terbalik agar tidak error di relasinya
        Schema::dropIfExists('articles');
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('risk_scores');
        Schema::dropIfExists('news_cache');
        Schema::dropIfExists('negative_words');
        Schema::dropIfExists('positive_words');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('countries');
    }
};