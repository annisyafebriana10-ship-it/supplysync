<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class NewsSentimentService
{
    // Kamus Lexicon Sederhana (Sesuai brief dosen)
    protected $positiveWords = ['growth', 'increase', 'profit', 'stable', 'improve', 'success', 'boom', 'positive', 'recovery', 'gain', 'up', 'safe'];
    protected $negativeWords = ['war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'loss', 'unstable', 'risk', 'drop', 'down', 'conflict', 'disruption', 'strike'];

    public function getNewsWithSentiment($countryName)
    {
        // Cache berita selama 4 jam (14400 detik) agar kuota API gratis awet
        // Cache berita selama 4 jam (14400 detik) agar kuota API gratis awet
        return Cache::remember("news_sentiment_{$countryName}", 14400, function () use ($countryName) {
            
            // Mengambil dari config/services.php (Best Practice)
            $apiKey = config('services.gnews.key'); 
            
            // Cari berita ekonomi/logistik spesifik negara tersebut
            $query = urlencode("{$countryName} economy OR supply chain OR logistics");
            $url = "https://gnews.io/api/v4/search?q={$query}&lang=en&max=5&apikey={$apiKey}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            curl_close($ch);

            $newsData = [];

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['articles'])) {
                    foreach ($data['articles'] as $article) {
                        // Gabungkan judul dan deskripsi untuk dianalisis
                        $textToAnalyze = $article['title'] . " " . $article['description'];
                        
                        // Eksekusi fungsi Sentiment Analyzer
                        $sentimentResult = $this->analyzeSentiment($textToAnalyze);

                        $newsData[] = [
                            'title' => $article['title'],
                            'url' => $article['url'],
                            'source' => $article['source']['name'] ?? 'News',
                            'sentiment' => $sentimentResult['sentiment'],
                            'score' => $sentimentResult['score'] // Akan dipakai untuk Risk Engine nanti
                        ];
                    }
                }
            }
            return $newsData;
        });
    }

    private function analyzeSentiment($text)
    {
        // 1. Bersihkan teks (buang tanda baca) dan ubah ke huruf kecil
        $text = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $text));
        
        // 2. Pecah kalimat menjadi kata per kata
        $words = explode(' ', $text);

        $positiveScore = 0;
        $negativeScore = 0;

        // 3. Cocokkan dengan kamus Lexicon
        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords)) {
                $positiveScore++;
            }
            if (in_array($word, $this->negativeWords)) {
                $negativeScore++;
            }
        }

        // 4. Tentukan hasil sentimen akhir
        if ($positiveScore > $negativeScore) {
            $sentiment = "Positive";
            $score = 1; // Poin Positif
        } elseif ($negativeScore > $positiveScore) {
            $sentiment = "Negative";
            $score = -1; // Poin Negatif
        } else {
            $sentiment = "Neutral";
            $score = 0; // Netral
        }

        return [
            'sentiment' => $sentiment,
            'score' => $score
        ];
    }
}