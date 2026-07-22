<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use App\Models\Port;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai Proses ETL (Kaggle Dataset) untuk World Port Index...');

        $csvFile = storage_path('app/world_ports.csv');

        // Validasi apakah file ada di tempat yang benar
        if (!file_exists($csvFile)) {
            $this->command->error("File dataset tidak ditemukan! Pastikan file world_ports.csv ada di folder storage/app/");
            return;
        }

        $handle = fopen($csvFile, 'r');
        
        $header = fgetcsv($handle);
        $header = array_map('strtolower', $header); // Ubah header jadi huruf kecil semua

        // DETEKSI KOLOM KAGGLE
        $idxName = array_search('main port name', $header);
        $idxCountry = array_search('country code', $header);
        $idxLat = array_search('latitude', $header);
        $idxLng = array_search('longitude', $header);

        if ($idxName === false || $idxCountry === false || $idxLat === false || $idxLng === false) {
            $this->command->error("❌ Gagal menemukan kolom wajib di CSV Kaggle.");
            fclose($handle);
            return;
        }

        DB::statement("SET session_replication_role = 'replica';");
        DB::table('ports')->truncate();

        $dataToInsert = [];
        
        // Kamus sederhana untuk mengubah kode 2 huruf menjadi nama negara utuh
        $countryMap = [
            'ID' => 'Indonesia', 'US' => 'United States', 'CN' => 'China', 'SG' => 'Singapore',
            'MY' => 'Malaysia', 'JP' => 'Japan', 'DE' => 'Germany', 'GB' => 'United Kingdom',
            'AU' => 'Australia', 'NL' => 'Netherlands', 'BR' => 'Brazil', 'ZA' => 'South Africa',
            'IN' => 'India', 'FR' => 'France', 'IT' => 'Italy', 'ES' => 'Spain', 'KR' => 'South Korea',
            'AE' => 'United Arab Emirates', 'SA' => 'Saudi Arabia', 'EG' => 'Egypt', 'CA' => 'Canada',
            'RU' => 'Russia', 'NZ' => 'New Zealand', 'PH' => 'Philippines', 'VN' => 'Vietnam',
            'TH' => 'Thailand', 'TR' => 'Turkey', 'MX' => 'Mexico', 'AR' => 'Argentina', 'CL' => 'Chile'
        ];

        $this->command->info('⚙️ Menyedot 3.800+ data dari CSV Kaggle, mohon tunggu beberapa detik...');

        while (($row = fgetcsv($handle)) !== false) {
            $name = $row[$idxName] ?? null;
            $rawCountryCode = $row[$idxCountry] ?? null;
            $lat = $row[$idxLat] ?? null;
            $lng = $row[$idxLng] ?? null;

            if (!$name || !$rawCountryCode || $lat === null || $lng === null) {
                continue; // Skip jika datanya bolong
            }

            // Terjemahkan kode negara. Jika tidak ada di kamus, gunakan nama lengkap dari database (firstOrCreate)
            $countryName = $countryMap[trim($rawCountryCode)] ?? trim($rawCountryCode);

            $country = Country::firstOrCreate(
                ['name' => $countryName],
                [
                    'lat' => $lat, 
                    'lng' => $lng,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $dataToInsert[] = [
                'code' => substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 5),
                'name' => $name,
                'country_id' => $country->id,
                'lat' => $lat,
                'lng' => $lng,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // CHUNKING: Eksekusi query setiap terkumpul 500 baris agar RAM tidak berat
            if (count($dataToInsert) >= 500) {
                DB::table('ports')->insert($dataToInsert);
                $dataToInsert = []; 
            }
        }

        // Jangan lupa masukkan sisa data yang kurang dari 500 baris terakhir
        if (!empty($dataToInsert)) {
            DB::table('ports')->insert($dataToInsert);
        }

        fclose($handle);
        DB::statement("SET session_replication_role = 'origin';");

        $count = Port::count();
        $this->command->info("FINALISASI ETL SUKSES! {$count} pelabuhan dari Dataset Kaggle telah masuk ke sistemmu.");
    }
}