<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("--- SEEDER SEDANG BERJALAN SEKARANG ---");

        // 1. Eksekusi Seeder Negara dan Pelabuhan BERURUTAN
        $this->call([
            CountrySeeder::class, // RUN PERTAMA: Siapkan 195 negara
            PortSeeder::class,    // RUN KEDUA: Masukkan pelabuhan, tautkan ke negara
        ]);
        
        // 2. Buat Akun Administrator Default
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@supplysync.com'], 
            [
                'name' => 'Administrator',
                'role' => 'admin', 
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 3. Isi Kamus Sentimen Positif
        $positiveWords = ['growth', 'increase', 'profit', 'stable', 'improve', 'success', 'boom', 'surge'];
        foreach ($positiveWords as $word) {
            DB::table('positive_words')->updateOrInsert(
                ['word' => $word],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Isi Kamus Sentimen Negatif
        $negativeWords = ['war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'drop', 'crash', 'loss'];
        foreach ($negativeWords as $word) {
            DB::table('negative_words')->updateOrInsert(
                ['word' => $word],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}