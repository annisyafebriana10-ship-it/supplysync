@extends('layouts.app')

@section('content')
<!-- KUMPULAN CSS UNTUK ANIMASI & INTERAKSI -->
<style>
    /* Efek Kapsul Pencarian */
    .search-capsule {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    
    .search-capsule:hover, .search-capsule:focus-within {
        border-color: var(--matcha-500);
        box-shadow: 0 6px 16px rgba(129, 162, 99, 0.15); /* Disesuaikan dengan warna matcha */
        transform: translateY(-2px);
    }

    /* Efek Tombol Monitor */
    .btn-monitor {
        transition: all 0.2s ease-in-out;
        color: #ffffff !important;
    }
    .btn-monitor:hover {
        filter: brightness(0.85); 
        color: #ffffff !important;
        transform: scale(1.05);
    }
    .btn-monitor:active {
        transform: scale(0.95);
    }

    /* Animasi denyut untuk indikator server */
    .pulse-dot {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    /* Card Standard Startup */
    .card-startup {
        background: white; 
        border-radius: 12px; 
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }
</style>

<!-- HEADER & FORM PENCARIAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 flex-shrink-0 gap-3" style="animation: fadeInDown 0.5s ease-out;">
    
    <!-- Judul & Sub-judul -->
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">Currency Impact & Conversion</h4>
            
            <!-- Badge Status & Live Clock -->
            <div class="d-flex align-items-center bg-success-subtle border border-success-subtle rounded-pill px-2 py-1 gap-2 shadow-sm" style="font-size: 0.65rem;">
                <span class="d-flex align-items-center text-success fw-bold">
                    <span class="rounded-circle bg-success me-1 pulse-dot" style="width: 6px; height: 6px;"></span>
                    API Active
                </span>
                <span class="text-success opacity-25">|</span>
                <span class="text-success fw-bold" id="live-clock">
                    <i class="fa-regular fa-clock me-1"></i> 00:00:00
                </span>
            </div>
        </div>
        <p class="text-secondary mb-0" style="font-size: 0.85rem;"><i class="fa-solid fa-money-bill-transfer me-1"></i>Pemantauan fluktuasi nilai tukar dan kalkulator estimasi logistik.</p>
    </div>

    <!-- Form Pencarian (Interactive Capsule) -->
    <div class="d-flex align-items-center">
        <form id="monitor-form" action="{{ route('market.currency') }}" method="GET" class="search-capsule d-flex align-items-center p-1 rounded-pill" style="width: 360px;">
            <div class="ps-3 pe-2 text-muted flex-shrink-0">
                <i class="fa-solid fa-earth-americas" style="font-size: 0.9rem; color: var(--matcha-500);"></i>
            </div>

            <div class="flex-grow-1 px-1" style="min-width: 0;">
                <select name="country" class="form-select border-0 bg-transparent shadow-none p-0" style="width: 100%; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #374151;">
                    @foreach($countries as $c)
                        <option value="{{ $c }}" {{ $selectedCountry == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" id="btn-monitor" class="btn btn-monitor fw-bold d-flex align-items-center justify-content-center rounded-pill px-3 flex-shrink-0 border-0 shadow-sm" style="background-color: var(--matcha-500); height: 34px;">
                <i class="fa-solid fa-magnifying-glass-chart me-1" id="btn-icon" style="font-size: 0.75rem;"></i>
                <span id="btn-text" style="font-size: 0.75rem;">Analisis</span>
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    <!-- Area Grafik & Market Insight -->
    <div class="col-lg-8 col-md-12">
        <div class="card-startup p-4 h-100 d-flex flex-column">
            <!-- Header & Insight Informatif -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;"><i class="fa-solid fa-chart-area me-2 text-secondary"></i>Market Exchange Trends</h6>
                    <div class="d-flex align-items-center mt-1">
                        <span class="badge bg-light text-dark border me-2" style="font-size: 0.6rem;"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Insight</span>
                        <small class="text-success fw-bold" style="font-size: 0.75rem;">Menampilkan fluktuasi historis {{ $currencyCode }} terhadap USD selama 10 hari terakhir.</small>
                    </div>
                </div>
                
                <!-- Live Rate Snapshot -->
                <div class="text-end">
                    <p class="text-uppercase text-muted mb-0" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Live Rate (Base USD)</p>
                    <h4 class="fw-bold text-dark mb-0 mt-1">{{ $rate }}</h4>
                </div>
            </div>
            
            <!-- Tempat Chart.js -->
            <div class="w-100 mt-auto position-relative" style="height: 240px;">
                <canvas id="currencyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Area Smart Calculator (Tetap menggunakan proporsi yang rapi) -->
    <div class="col-lg-4 col-md-12">
        <div class="card-startup p-4 h-100 d-flex flex-column">
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;"><i class="fa-solid fa-calculator me-2 text-secondary"></i>Smart Converter</h6>
                <p class="text-muted" style="font-size: 0.75rem;">Hitung estimasi biaya transaksi logistik secara *real-time*.</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 0.75rem;">Jumlah Pembayaran</label>
                <input type="number" id="calcAmount" class="form-control form-control-lg text-end fw-bold shadow-none" value="1000" style="background-color: #f8fafc; border-color: #e2e8f0; border-radius: 8px;">
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-5">
                    <label class="form-label text-muted fw-bold" style="font-size: 0.75rem;">Dari</label>
                    <select id="calcFrom" class="form-select fw-bold shadow-none border-0" style="background-color: #f8fafc; border-radius: 8px;"></select>
                </div>
                <div class="col-2 d-flex align-items-center justify-content-center pt-4">
                    <i class="fa-solid fa-right-left text-muted"></i>
                </div>
                <div class="col-5">
                    <label class="form-label text-muted fw-bold" style="font-size: 0.75rem;">Ke</label>
                    <select id="calcTo" class="form-select fw-bold shadow-none border-0" style="background-color: #f8fafc; border-radius: 8px;"></select>
                </div>
            </div>

            <!-- Box Hasil Kalkulasi -->
            <div class="p-3 mt-auto rounded-3 border d-flex flex-column justify-content-center align-items-center text-center" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                <p class="mb-0 text-success fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Estimasi Hasil</p>
                <h3 class="fw-bold text-dark mb-0 mt-1" id="calcResult" style="letter-spacing: -0.5px;">0.00</h3>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT LOGIKA -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. LIVE CLOCK ENGINE
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const clockEl = document.getElementById('live-clock');
            if(clockEl) clockEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${timeString}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 2. LOADING STATE BUTTON
        const form = document.getElementById('monitor-form');
        const btn = document.getElementById('btn-monitor');
        const btnText = document.getElementById('btn-text');
        const btnIcon = document.getElementById('btn-icon');

        if(form && btn) {
            form.addEventListener('submit', function() {
                btn.style.opacity = '0.8';
                btn.style.cursor = 'wait';
                btnIcon.className = 'spinner-border spinner-border-sm me-1';
                btnText.innerText = 'Memuat...';
            });
        }

        // ==========================================
        // 3. RENDER GRAFIK CHART.JS (DENGAN TEMA CLEAN)
        // ==========================================
        const ctx = document.getElementById('currencyChart').getContext('2d');
        const labels = {!! json_encode($chartLabels) !!};
        const dataPoints = {!! json_encode($chartData) !!};

        let gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(129, 162, 99, 0.3)'); 
        gradient.addColorStop(1, 'rgba(129, 162, 99, 0.0)'); 

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nilai Tukar',
                    data: dataPoints,
                    borderColor: '#365E32',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#365E32',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 13, family: 'Inter' },
                        bodyFont: { size: 14, weight: 'bold', family: 'Inter' },
                        displayColors: false,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: false, 
                        grid: { borderDash: [4, 4], color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                    },
                    x: { 
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });

        // ==========================================
        // 4. LOGIKA SMART CURRENCY CONVERTER
        // ==========================================
        const targetCurrencyCode = "{{ $currencyCode }}";
        let exchangeRates = {};

        fetch('https://open.er-api.com/v6/latest/USD')
            .then(res => res.json())
            .then(data => {
                exchangeRates = data.rates;
                const fromSelect = document.getElementById('calcFrom');
                const toSelect = document.getElementById('calcTo');
                
                const popularCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'SGD', 'IDR', 'AUD', targetCurrencyCode];
                const uniqueCurrencies = [...new Set(popularCurrencies)];

                uniqueCurrencies.forEach(currency => {
                    if(exchangeRates[currency]) {
                        fromSelect.innerHTML += `<option value="${currency}">${currency}</option>`;
                        toSelect.innerHTML += `<option value="${currency}">${currency}</option>`;
                    }
                });

                fromSelect.value = 'USD';
                toSelect.value = targetCurrencyCode;

                calculateConversion();
            });

        function calculateConversion() {
            const amount = parseFloat(document.getElementById('calcAmount').value) || 0;
            const from = document.getElementById('calcFrom').value;
            const to = document.getElementById('calcTo').value;

            if (exchangeRates[from] && exchangeRates[to]) {
                const rateFrom = exchangeRates[from];
                const rateTo = exchangeRates[to];
                const result = (amount / rateFrom) * rateTo;

                document.getElementById('calcResult').innerText = new Intl.NumberFormat('en-US', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                }).format(result) + ' ' + to;
            }
        }

        document.getElementById('calcAmount').addEventListener('input', calculateConversion);
        document.getElementById('calcFrom').addEventListener('change', calculateConversion);
        document.getElementById('calcTo').addEventListener('change', calculateConversion);
    });
</script>
@endsection