<?php

namespace App\Services;

class RiskScoringService
{
    public function calculateRisk($weather, $economy, $news)
    {
        // 1. Weather Risk (Skala 0-100) - Bobot 30%
        $weatherScore = 20; // Default Aman
        if (isset($weather['current']['weather_code'])) {
            $code = $weather['current']['weather_code'];
            if ($code >= 95) $weatherScore = 100; // Badai Petir (Bahaya Ekstrem)
            elseif ($code >= 80) $weatherScore = 80;  // Hujan Lebat/Badai
            elseif ($code >= 51) $weatherScore = 50;  // Hujan Ringan
        }

        // 2. Inflation Risk (Skala 0-100) - Bobot 20%
        $inflationScore = 30; // Default Waspada Ringan
        if (isset($economy['inflation']) && $economy['inflation'] !== '--') {
            $inf = (float) $economy['inflation'];
            if ($inf > 10 || $inf < 0) $inflationScore = 90; // Inflasi > 10% atau Deflasi parah
            elseif ($inf > 5) $inflationScore = 60; // Inflasi menengah
            else $inflationScore = 20; // Inflasi stabil
        }

        // 3. Currency Risk (Skala 0-100) - Bobot 10%
        // Karena kita butuh data historis untuk volatilitas, kita buat statis menengah untuk purwarupa ini
        $currencyScore = 50; 

        // 4. News Sentiment Risk (Skala 0-100) - Bobot 40%
        $newsScore = 50; // Default Netral
        if (!empty($news)) {
            $totalScore = 0;
            foreach ($news as $article) {
                // Ingat: Di NewsSentimentService, Positif = 1, Netral = 0, Negatif = -1
                $totalScore += $article['score']; 
            }
            // Cari rata-rata sentimen
            $avg = $totalScore / count($news); 
            
            if ($avg > 0) $newsScore = 20; // Berita didominasi sentimen Positif (Risiko Rendah)
            elseif ($avg < 0) $newsScore = 90; // Berita didominasi sentimen Negatif (Risiko Tinggi)
        }

        // 5. KALKULASI FINAL (Weighted Sum)
        $finalScore = ($weatherScore * 0.30) + ($inflationScore * 0.20) + ($currencyScore * 0.10) + ($newsScore * 0.40);
        $finalScore = round($finalScore); // Bulatkan angka

        // 6. Tentukan Status & Visual UI
        if ($finalScore <= 33) {
            $status = 'Low';
            $color = 'success';
            $icon = 'fa-check-circle';
            $text = 'Aman untuk operasional';
            $bg = 'var(--matcha-50)';
            $border = 'var(--matcha-500)';
        } elseif ($finalScore <= 66) {
            $status = 'Medium';
            $color = 'warning';
            $icon = 'fa-exclamation-circle';
            $text = 'Waspada gangguan logistik';
            $bg = '#fffbeb';
            $border = '#f59e0b';
        } else {
            $status = 'High';
            $color = 'danger';
            $icon = 'fa-triangle-exclamation';
            $text = 'Bahaya rantai pasok';
            $bg = '#fff1f2';
            $border = '#ef4444';
        }

        return [
            'score' => $finalScore,
            'status' => $status,
            'color' => $color,
            'icon' => $icon,
            'text' => $text,
            'bg' => $bg,
            'border' => $border,

            // TAMBAHKAN INI UNTUK CHART.JS
            'breakdown' => [
                'weather' => $weatherScore,
                'inflation' => $inflationScore,
                'currency' => $currencyScore,
                'news' => $newsScore
            ]
        ];
    }
}