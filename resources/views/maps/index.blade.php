@extends('layouts.app')

@section('content')
<!-- LEAFLET CSS & MARKER CLUSTER CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

<!-- KUSTOMISASI UI PETA & LAYOUT -->
<style>
    /* Mengunci halaman agar padat, tidak scroll, dan menyisakan margin bawah */
    .map-container {
        position: relative;
        /* Kalkulasi: Tinggi layar penuh dikurangi ruang untuk header, padding atas, dan margin bawah */
        height: calc(107vh - 150px); 
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        margin-bottom: 20px; /* Margin bawah agar tidak mentok */
    }

    /* Panel Pencarian Mengambang di kiri atas */
    .map-search-box {
        position: absolute;
        top: 20px;
        left: 60px; /* Diberi jarak agar tidak menabrak tombol Zoom */
        z-index: 1000;
        width: 320px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.8);
    }

    /* Panel Kontrol Layer di kanan atas */
    .map-control-panel {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 12px 15px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.8);
        width: 220px;
    }

    /* Animasi Radar Cuaca (Lingkaran Merah Berkedip) */
    .radar-pulse {
        width: 50px;
        height: 50px;
        background: rgba(239, 68, 68, 0.2);
        border: 2px solid rgba(239, 68, 68, 0.8);
        border-radius: 50%;
        animation: radar 1.5s infinite ease-out;
    }

    @keyframes radar {
        0% { transform: scale(0.3); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
</style>

<!-- HEADER (COMPACT) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.2rem;"><i class="fa-solid fa-satellite-dish me-2" style="color: var(--matcha-500);"></i>Geospatial Intelligence</h4>
        <p class="text-secondary mb-0" style="font-size: 0.8rem;">Pemantauan Real-Time 4.000+ Pelabuhan & Anomali Cuaca</p>
    </div>
</div>

<!-- WADAH PETA FULL SCREEN -->
<div class="map-container">
    <!-- Div Peta Asli -->
    <div id="geospatialMap" style="width: 100%; height: 100%;"></div>

    <!-- Fitur Search Bar Keren di Atas Peta -->
    <div class="map-search-box p-2 d-flex align-items-center gap-2">
        <i class="fa-solid fa-magnifying-glass text-muted ms-2"></i>
        <input type="text" id="mapSearchInput" class="form-control border-0 bg-transparent shadow-none" placeholder="Cari pelabuhan atau negara..." style="font-size: 0.85rem; font-weight: 500;">
        <button class="btn btn-sm text-white rounded px-2 py-1" style="background-color: var(--matcha-500); font-size: 0.75rem;">Cari</button>
    </div>

    <!-- Panel Kontrol Layar (Mengambang di kanan) -->
    <div class="map-control-panel">
        <h6 class="fw-bold mb-2 border-bottom pb-2" style="font-size: 0.8rem;"><i class="fa-solid fa-layer-group me-2"></i>Map Layers</h6>
        
        <div class="form-check form-switch mb-1">
            <input class="form-check-input" type="checkbox" id="togglePorts" checked>
            <label class="form-check-label fw-bold" for="togglePorts" style="font-size: 0.75rem; color: #1e293b;">
                <i class="fa-solid fa-anchor text-primary me-1"></i> Port Clusters
            </label>
        </div>
        
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="toggleWeather" checked>
            <label class="form-check-label fw-bold" for="toggleWeather" style="font-size: 0.75rem; color: #1e293b;">
                <i class="fa-solid fa-circle-radiation text-danger me-1"></i> Weather Threats
            </label>
        </div>
    </div>
</div>

<!-- LEAFLET JS & MARKER CLUSTER JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. INISIALISASI PETA
        const map = L.map('geospatialMap', {
            center: [2.5, 112.5], 
            zoom: 4,
            zoomControl: false 
        });

        L.control.zoom({ position: 'bottomleft' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        // 2. LAYER GROUPS & CLUSTERING
        const weatherLayer = L.layerGroup().addTo(map);
        const portCluster = L.markerClusterGroup({
            maxClusterRadius: 50,
            iconCreateFunction: function(cluster) {
                return L.divIcon({ 
                    html: `<div style="background-color: var(--matcha-500); color: white; width: 32px; height: 32px; display:flex; align-items:center; justify-content:center; border-radius: 50%; font-weight: bold; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.2);">${cluster.getChildCount()}</div>`, 
                    className: 'my-cluster-icon', 
                    iconSize: L.point(32, 32) 
                });
            }
        });
        map.addLayer(portCluster);

        // 3. TARIK DATA DARI REST API BUATAN SENDIRI (AJAX/FETCH ES6)
        let realPorts = []; // Siapkan wadah kosong
        const portMarkers = {}; // Wadah referensi marker untuk pencarian

        const portIcon = L.divIcon({
            className: 'custom-port-icon',
            html: `<div style="background-color: #3b82f6; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.4);"></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        // Memanggil endpoint GET /api/ports yang baru saja kamu buat
        fetch('/api/ports')
            .then(response => response.json())
            .then(data => {
                realPorts = data; // Simpan data dari database ke variabel JavaScript

                // Looping data dari database untuk ditancapkan ke peta
                realPorts.forEach(port => {
                    // Pastikan lat dan lng bukan null
                    if (port.lat && port.lng) {
                        const marker = L.marker([port.lat, port.lng], {icon: portIcon});
                        
                        portMarkers[port.code] = marker;

                        marker.bindPopup(`
                            <div class="text-center p-1">
                                <strong style="font-size:0.95rem; color: var(--matcha-700);">${port.name}</strong>
                                <hr class="my-1">
                                <small class="text-muted d-block">Negara: <b>${port.country_name}</b></small>
                                <small class="text-muted d-block">Kode: <b>${port.code}</b></small>
                                <span class="badge bg-success mt-2" style="font-size: 0.65rem;">Status: Operational</span>
                            </div>
                        `);
                        
                        portCluster.addLayer(marker);
                    }
                });
            })
            .catch(error => console.error('Gagal mengambil data pelabuhan:', error));

        // 4. LOGIKA PENCARIAN (THE SEARCH ENGINE) DENGAN FUZZY LOGIC
        const searchInput = document.getElementById('mapSearchInput');
        const searchBtn = document.querySelector('.map-search-box button');

        // Fungsi "Pengukur Jarak Teks" (Levenshtein Distance)
        function getEditDistance(a, b) {
            if (a.length === 0) return b.length;
            if (b.length === 0) return a.length;
            const matrix = [];
            for (let i = 0; i <= b.length; i++) matrix[i] = [i];
            for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
            for (let i = 1; i <= b.length; i++) {
                for (let j = 1; j <= a.length; j++) {
                    if (b.charAt(i - 1) === a.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1, // Substitusi huruf
                            Math.min(matrix[i][j - 1] + 1, matrix[i - 1][j] + 1) // Tambah / Hapus huruf
                        );
                    }
                }
            }
            return matrix[b.length][a.length];
        }

        function executeSearch() {
            const query = searchInput.value.toLowerCase().trim();
            if(!query) return;

            let bestMatch = null;
            let lowestDistance = 999; // Inisialisasi jarak terjauh

            realPorts.forEach(p => {
                const portName = (p.name || '').toLowerCase();
                const countryName = (p.country_name || '').toLowerCase();
                const portCode = (p.code || '').toLowerCase();

                // Tahap 1: Pencarian Parsial (Ngetik setengah jalan tetap ketemu)
                if (portName.includes(query) || countryName.includes(query) || portCode.includes(query)) {
                    bestMatch = p;
                    lowestDistance = 0; // Jarak 0 = Cocok Sempurna
                    return; 
                }

                // Tahap 2: Typo Tolerance (Pencarian Fuzzy untuk yang salah ketik)
                // Hanya aktif jika user ngetik minimal 4 huruf agar akurat
                if (query.length >= 4 && lowestDistance > 0) {
                    // Kita potong nama pelabuhan di database agar panjangnya sama dengan ketikan user
                    const slicedPortName = portName.substring(0, query.length);
                    const slicedCountry = countryName.substring(0, query.length);
                    
                    const distName = getEditDistance(query, slicedPortName);
                    const distCountry = getEditDistance(query, slicedCountry);
                    const minError = Math.min(distName, distCountry);
                    
                    // Mentoleransi maksimal 2 huruf yang salah ketik (Jarak <= 2)
                    if (minError <= 2 && minError < lowestDistance) {
                        lowestDistance = minError;
                        bestMatch = p;
                    }
                }
            });

            // Eksekusi Peta jika menemukan hasil terbaik
            if (bestMatch) {
                map.flyTo([bestMatch.lat, bestMatch.lng], 13, {
                    animate: true,
                    duration: 2.0 
                });

                setTimeout(() => {
                    const targetMarker = portMarkers[bestMatch.code];
                    if (targetMarker) {
                        portCluster.zoomToShowLayer(targetMarker, function () {
                            targetMarker.openPopup();
                        });
                    }
                }, 2000);
                
            } else {
                const originalPlaceholder = searchInput.placeholder;
                searchInput.value = '';
                searchInput.placeholder = 'Pelabuhan tidak ditemukan!';
                searchInput.classList.add('text-danger');
                
                setTimeout(() => {
                    searchInput.placeholder = originalPlaceholder;
                    searchInput.classList.remove('text-danger');
                }, 2000);
            }
        }

        searchBtn.addEventListener('click', executeSearch);
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                executeSearch();
            }
        });

        // 5. ANIMASI RADAR CUACA (DINAMIS DARI OPEN-METEO)
        const radarIcon = L.divIcon({
            className: 'radar-pulse',
            iconSize: [50, 50],
            iconAnchor: [25, 25]
        });

        // Ambil data koordinat 195 negara
        fetch('/api/countries')
            .then(res => res.json())
            .then(countries => {
                countries.forEach(country => {
                    // Buat titik kecil transparan sebagai sensor cuaca
                    let weatherSensor = L.circleMarker([country.lat, country.lng], {
                        radius: 6, fillColor: "#ef4444", color: "#fff", weight: 1, fillOpacity: 0.5
                    }).bindPopup("📡 Memindai cuaca satelit...");

                    // Saat titik diklik, panggil API Open-Meteo
                    weatherSensor.on('click', function() {
                        fetch(`/api/weather/live?lat=${country.lat}&lng=${country.lng}`)
                            .then(res => res.json())
                            .then(data => {
                                // 1. BACA DATA DARI STRUKTUR 'CURRENT'
                                const current = data.current || {};
                                const temp = current.temperature_2m ?? '--';
                                const wind = current.wind_speed_10m ?? '--';
                                const code = current.weather_code ?? 0;

                                // 2. ALGORITMA MINI-DSS (Kalkulasi Risiko Badai Berdasarkan Kode WMO)
                                let stormRisk = 'Low';
                                let badgeColor = 'bg-success';
                                let condition = 'Cerah / Berawan';
                                let icon = 'fa-solid fa-cloud-sun text-secondary';

                                // Jika kode menunjukkan badai petir (95, 96, 99) atau angin > 60
                                if ([95, 96, 99].includes(code) || wind > 60) {
                                    stormRisk = 'High';
                                    badgeColor = 'bg-danger';
                                    condition = 'Peringatan Badai';
                                    icon = 'fa-solid fa-cloud-bolt text-danger';
                                } 
                                // Jika kode menunjukkan hujan (61-82) atau angin > 40
                                else if ([61, 63, 65, 80, 81, 82].includes(code) || wind > 40) {
                                    stormRisk = 'Medium';
                                    badgeColor = 'bg-warning text-dark';
                                    condition = 'Hujan / Angin Kencang';
                                    icon = 'fa-solid fa-cloud-showers-heavy text-primary';
                                }

                                // 3. TAMPILKAN KE POPUP UI
                                let popupHtml = `
                                    <div class="text-center p-1">
                                        <h6 class="mb-1 fw-bold text-dark">${country.name}</h6>
                                        <i class="${icon} mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-1 text-muted" style="font-size: 0.85rem;">${condition}</p>
                                        <hr class="my-1">
                                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">
                                            <span>🌡️ ${temp}°C</span>
                                            <span>💨 ${wind} km/h</span>
                                        </div>
                                        <span class="badge ${badgeColor} w-100 mt-1">Risk: ${stormRisk}</span>
                                    </div>
                                `;
                                weatherSensor.setPopupContent(popupHtml);

                                // 4. JIKA BADAI (High Risk), MUNCULKAN RADAR MERAH BERKEDIP!
                                if (stormRisk === 'High') {
                                    L.marker([country.lat, country.lng], {icon: radarIcon}).addTo(weatherLayer);
                                }
                            })
                            .catch(err => console.error("Gagal memuat cuaca", err));
                    });
                    
                    weatherSensor.addTo(weatherLayer);
                });
            });

        // 6. TOGGLE ON/OFF
        document.getElementById('togglePorts').addEventListener('change', function(e) {
            if (e.target.checked) map.addLayer(portCluster);
            else map.removeLayer(portCluster);
        });

        document.getElementById('toggleWeather').addEventListener('change', function(e) {
            if (e.target.checked) map.addLayer(weatherLayer);
            else map.removeLayer(weatherLayer);
        });

    });
</script>
@endsection