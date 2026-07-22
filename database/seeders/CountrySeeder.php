<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Menyiapkan 195 Negara Dasar (Offline Base Data)...');

        // Daftar lengkap 195 negara murni tanpa negara abal-abal/duplikat
        $allCountries = [
            'Afghanistan'=>[33.0,65.0], 'Albania'=>[41.0,20.0], 'Algeria'=>[28.0,3.0], 'Andorra'=>[42.5,1.5], 'Angola'=>[-12.5,18.5], 
            'Antigua and Barbuda'=>[17.05,-61.8], 'Argentina'=>[-34.0,-64.0], 'Armenia'=>[40.0,45.0], 'Australia'=>[-27.0,133.0], 'Austria'=>[47.33,13.33], 
            'Azerbaijan'=>[40.5,47.5], 'Bahamas'=>[24.25,-76.0], 'Bahrain'=>[26.0,50.55], 'Bangladesh'=>[24.0,90.0], 'Barbados'=>[13.16,-59.53], 
            'Belarus'=>[53.0,28.0], 'Belgium'=>[50.83,4.0], 'Belize'=>[17.25,-88.75], 'Benin'=>[9.5,2.25], 'Bhutan'=>[27.5,90.5], 
            'Bolivia'=>[-17.0,-65.0], 'Bosnia and Herzegovina'=>[44.0,18.0], 'Botswana'=>[-22.0,24.0], 'Brazil'=>[-10.0,-55.0], 'Brunei'=>[4.5,114.66], 
            'Bulgaria'=>[43.0,25.0], 'Burkina Faso'=>[13.0,-2.0], 'Burundi'=>[-3.5,30.0], 'Cabo Verde'=>[16.0,-24.0], 'Cambodia'=>[13.0,105.0], 
            'Cameroon'=>[6.0,12.0], 'Canada'=>[60.0,-95.0], 'Central African Republic'=>[7.0,21.0], 'Chad'=>[15.0,19.0], 'Chile'=>[-30.0,-71.0], 
            'China'=>[35.0,105.0], 'Colombia'=>[4.0,-72.0], 'Comoros'=>[-12.16,44.25], 'Congo'=>[-1.0,15.0], 'Costa Rica'=>[10.0,-84.0], 
            'Cote d\'Ivoire'=>[8.0,-5.0], 'Croatia'=>[45.16,15.5], 'Cuba'=>[21.5,-80.0], 'Cyprus'=>[35.0,33.0], 'Czechia'=>[49.75,15.5], 
            'Democratic Republic of the Congo'=>[-3.0,23.0], 'Denmark'=>[56.0,10.0], 'Djibouti'=>[11.5,43.0], 'Dominica'=>[15.41,-61.33], 'Dominican Republic'=>[19.0,-70.66], 
            'Ecuador'=>[-2.0,-77.5], 'Egypt'=>[27.0,30.0], 'El Salvador'=>[13.83,-88.91], 'Equatorial Guinea'=>[2.0,10.0], 'Eritrea'=>[15.0,39.0], 
            'Estonia'=>[59.0,26.0], 'Eswatini'=>[-26.5,31.5], 'Ethiopia'=>[8.0,38.0], 'Fiji'=>[-18.0,175.0], 'Finland'=>[64.0,26.0], 
            'France'=>[46.0,2.0], 'Gabon'=>[-1.0,11.75], 'Gambia'=>[13.46,-16.56], 'Georgia'=>[42.0,43.5], 'Germany'=>[51.0,9.0], 
            'Ghana'=>[8.0,-2.0], 'Greece'=>[39.0,22.0], 'Grenada'=>[12.11,-61.66], 'Guatemala'=>[15.5,-90.25], 'Guinea'=>[11.0,-10.0], 
            'Guinea-Bissau'=>[12.0,-15.0], 'Guyana'=>[5.0,-59.0], 'Haiti'=>[19.0,-72.41], 'Honduras'=>[15.0,-86.5], 'Hungary'=>[47.0,20.0], 
            'Iceland'=>[65.0,-18.0], 'India'=>[20.0,77.0], 'Indonesia'=>[-5.0,120.0], 'Iran'=>[32.0,53.0], 'Iraq'=>[33.0,44.0], 
            'Ireland'=>[53.0,-8.0], 'Israel'=>[31.5,34.75], 'Italy'=>[42.83,12.83], 'Jamaica'=>[18.25,-77.5], 'Japan'=>[36.0,138.0], 
            'Jordan'=>[31.0,36.0], 'Kazakhstan'=>[48.0,68.0], 'Kenya'=>[1.0,38.0], 'Kiribati'=>[1.41,173.0], 'Kuwait'=>[29.5,45.75], 
            'Kyrgyzstan'=>[41.0,75.0], 'Laos'=>[18.0,105.0], 'Latvia'=>[57.0,25.0], 'Lebanon'=>[33.83,35.83], 'Lesotho'=>[-29.5,28.5], 
            'Liberia'=>[6.5,-9.5], 'Libya'=>[25.0,17.0], 'Liechtenstein'=>[47.26,9.53], 'Lithuania'=>[56.0,24.0], 'Luxembourg'=>[49.75,6.16], 
            'Madagascar'=>[-20.0,47.0], 'Malawi'=>[-13.5,34.0], 'Malaysia'=>[2.5,112.5], 'Maldives'=>[3.25,73.0], 'Mali'=>[17.0,-4.0], 
            'Malta'=>[35.91,14.41], 'Marshall Islands'=>[9.0,168.0], 'Mauritania'=>[20.0,-12.0], 'Mauritius'=>[-20.28,57.55], 'Mexico'=>[23.0,-102.0], 
            'Micronesia'=>[6.91,158.25], 'Moldova'=>[47.0,29.0], 'Monaco'=>[43.73,7.4], 'Mongolia'=>[46.0,105.0], 'Montenegro'=>[42.5,19.3], 
            'Morocco'=>[32.0,-5.0], 'Mozambique'=>[-21.25,35.5], 'Myanmar'=>[22.0,98.0], 'Namibia'=>[-22.0,17.0], 'Nauru'=>[-0.53,166.91], 
            'Nepal'=>[28.0,84.0], 'Netherlands'=>[52.5,5.75], 'New Zealand'=>[-41.0,174.0], 'Nicaragua'=>[13.0,-85.0], 'Niger'=>[16.0,8.0], 
            'Nigeria'=>[10.0,8.0], 'North Korea'=>[40.0,127.0], 'North Macedonia'=>[41.83,22.0], 'Norway'=>[62.0,10.0], 'Oman'=>[21.0,57.0], 
            'Pakistan'=>[30.0,70.0], 'Palau'=>[7.5,134.5], 'Palestine'=>[31.9,35.2], 'Panama'=>[9.0,-80.0], 'Papua New Guinea'=>[-6.0,147.0], 
            'Paraguay'=>[-23.0,-58.0], 'Peru'=>[-10.0,-76.0], 'Philippines'=>[13.0,122.0], 'Poland'=>[52.0,20.0], 'Portugal'=>[39.5,-8.0], 
            'Qatar'=>[25.5,51.25], 'Romania'=>[46.0,25.0], 'Russia'=>[60.0,100.0], 'Rwanda'=>[-2.0,30.0], 'Saint Kitts and Nevis'=>[17.33,-62.75], 
            'Saint Lucia'=>[13.88,-60.96], 'Saint Vincent and the Grenadines'=>[13.25,-61.2], 'Samoa'=>[-13.58,-172.33], 'San Marino'=>[43.76,12.41], 
            'Sao Tome and Principe'=>[1.0,7.0], 'Saudi Arabia'=>[25.0,45.0], 'Senegal'=>[14.0,-14.0], 'Serbia'=>[44.0,21.0], 'Seychelles'=>[-4.58,55.66], 
            'Sierra Leone'=>[8.5,-11.5], 'Singapore'=>[1.36,103.81], 'Slovakia'=>[48.66,19.5], 'Slovenia'=>[46.11,14.81], 'Solomon Islands'=>[-8.0,159.0], 
            'Somalia'=>[10.0,49.0], 'South Africa'=>[-29.0,24.0], 'South Korea'=>[37.0,127.5], 'South Sudan'=>[7.0,30.0], 'Spain'=>[40.0,-4.0], 
            'Sri Lanka'=>[7.0,81.0], 'Sudan'=>[15.0,30.0], 'Suriname'=>[4.0,-56.0], 'Sweden'=>[62.0,15.0], 'Switzerland'=>[47.0,8.0], 
            'Syria'=>[35.0,38.0], 'Taiwan'=>[23.5,121.0], 'Tajikistan'=>[39.0,71.0], 'Tanzania'=>[-6.0,35.0], 'Thailand'=>[15.0,100.0], 
            'Timor-Leste'=>[-8.83,125.91], 'Togo'=>[8.0,1.16], 'Tonga'=>[-20.0,-175.0], 'Trinidad and Tobago'=>[11.0,-61.0], 'Tunisia'=>[34.0,9.0], 
            'Turkey'=>[39.0,35.0], 'Turkmenistan'=>[40.0,60.0], 'Tuvalu'=>[-8.0,178.0], 'Uganda'=>[1.0,32.0], 'Ukraine'=>[49.0,32.0], 
            'United Arab Emirates'=>[24.0,54.0], 'United Kingdom'=>[54.0,-2.0], 'United States'=>[38.0,-97.0], 'Uruguay'=>[-33.0,-56.0], 
            'Uzbekistan'=>[41.0,64.0], 'Vanuatu'=>[-16.0,167.0], 'Vatican City'=>[43.73,7.4], 'Venezuela'=>[8.0,-66.0], 'Vietnam'=>[16.0,106.0], 
            'Yemen'=>[15.0,48.0], 'Zambia'=>[-15.0,30.0], 'Zimbabwe'=>[-20.0,30.0]
        ];

        DB::statement("SET session_replication_role = 'replica';");

        // Menggunakan updateOrInsert agar kebal dari error duplicate entry!
        foreach ($allCountries as $name => $coords) {
            DB::table('countries')->updateOrInsert(
                ['name' => $name], // Kunci pencarian, jika ada di-update, jika tidak di-insert
                [
                    'lat' => $coords[0],
                    'lng' => $coords[1],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::statement("SET session_replication_role = 'origin';");

        $count = DB::table('countries')->count();
        $this->command->info("SUKSES! {$count} Negara PBB telah disuntikkan tanpa memotong kuota API-mu.");
    }
}