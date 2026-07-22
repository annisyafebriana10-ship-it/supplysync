<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiClient
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct($baseUrl = '', $apiKey = '')
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Fungsi utama untuk melakukan GET Request dengan menyertakan API Key
     */
    public function get($endpoint, $queryParams = [])
    {
        // Menyisipkan API key ke dalam header atau parameter (tergantung kebutuhan API)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->get($this->baseUrl . $endpoint, $queryParams);

        return $response;
    }
}