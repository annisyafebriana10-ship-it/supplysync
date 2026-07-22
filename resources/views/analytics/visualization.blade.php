@extends('layouts.app')

@section('content')
<!-- KUMPULAN CSS UNTUK TEMA CORPORATE MODERN -->
<style>
    .search-capsule {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .search-capsule:hover, .search-capsule:focus-within {
        border-color: var(--matcha-500);
        box-shadow: 0 6px 16px rgba(129, 162, 99, 0.15);
        transform: translateY(-2px);
    }
    .btn-monitor { transition: all 0.2s ease-in-out; color: #ffffff !important; }
    .btn-monitor:hover { filter: brightness(0.85); transform: scale(1.05); }
    .btn-monitor:active { transform: scale(0.95); }

    .pulse-dot { animation: pulse 1.5s infinite; }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    /* Card Corporate Standard */
    .card-corporate {
        background: white; 
        border-radius: 12px; 
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
    }
    .card-corporate:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
    }

    /* Kotak Insight Informatif */
    .insight-box {
        background-color: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 15px;
        font-size: 0.75rem;
        color: #475569;
    }
</style>

<!-- HEADER & FORM PENCARIAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 flex-shrink-0 gap-3" style="animation: fadeInDown 0.5s ease-out;">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">Data Visualization Dashboard</h4>
            <div class="d-flex align-items-center bg-success-subtle border border-success-subtle rounded-pill px-2 py-1 gap-2 shadow-sm" style="font-size: 0.65rem;">
                <span class="d-flex align-items-center text-success fw-bold">
                    <span class="rounded-circle bg-success me-1 pulse-dot" style="width: 6px; height: 6px;"></span>
                    Node 10 Active
                </span>
                <span class="text-success opacity-25">|</span>
                <span class="text-success fw-bold" id="live-clock">
                    <i class="fa-regular fa-clock me-1"></i> 00:00:00
                </span>
            </div>
        </div>
        <p class="text-secondary mb-0" style="font-size: 0.85rem;"><i class="fa-solid fa-chart-line me-1"></i>Analitik makroekonomi dan tren risiko historis: <strong class="text-dark">{{ $targetCountry }}</strong></p>
    </div>

    <!-- Form Pencarian -->
    <div class="d-flex align-items-center">
        <form id="monitor-form" action="{{ route('analytics.visualization') }}" method="GET" class="search-capsule d-flex align-items-center p-1 rounded-pill" style="width: 360px;">
            <div class="ps-3 pe-2 text-muted flex-shrink-0">
                <i class="fa-solid fa-earth-americas" style="font-size: 0.9rem; color: var(--matcha-500);"></i>
            </div>
            <div class="flex-grow-1 px-1" style="min-width: 0;">
                <select name="country" class="form-select border-0 bg-transparent shadow-none p-0 fw-bold" style="width: 100%; cursor: pointer; font-size: 0.85rem; color: #374151;">
                    @php $list = $countriesList ?? []; @endphp
                    @forelse($list as $c)
                        <option value="{{ $c }}" {{ $targetCountry == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @empty
                        <option value="Indonesia">Indonesia</option>
                    @endforelse
                </select>
            </div>
            <button type="submit" id="btn-monitor" class="btn btn-monitor fw-bold d-flex align-items-center justify-content-center rounded-pill px-3 flex-shrink-0 border-0 shadow-sm" style="background-color: var(--matcha-500); height: 34px;">
                <i class="fa-solid fa-magnifying-glass-chart me-1" id="btn-icon" style="font-size: 0.75rem;"></i>
                <span id="btn-text" style="font-size: 0.75rem;">Analisis</span>
            </button>
        </form>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger mb-4 py-2 px-3 border-0 shadow-sm" style="background-color: #fef2f2; border-left: 4px solid #ef4444 !important; font-size: 0.85rem; border-radius: 8px;">
        <i class="fa-solid fa-triangle-exclamation me-3 fs-5 text-danger"></i>{{ session('error') }}
    </div>
@endif

<!-- KUADRAN GRAFIK (TEMA CORPORATE MATCHA & MODERN) -->
<div class="row g-4 mb-4">
    
    <!-- 1. GDP Trend Chart -->
    <div class="col-lg-6 col-md-12">
        <div class="card-corporate p-4 d-flex flex-column h-100">
            <div class="mb-3 d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Gross Domestic Product (GDP)</h6>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">Kekuatan daya beli & pasar (World Bank API)</p>
                </div>
                <i class="fa-solid fa-money-bill-trend-up text-success bg-success-subtle p-2 rounded-circle"></i>
            </div>
            
            <div class="position-relative w-100 mt-2 flex-grow-1" style="min-height: 250px;">
                <canvas id="gdpChart"></canvas>
            </div>
            
            <!-- Insight Box -->
            <div class="insight-box mt-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-lightbulb text-warning"></i>
                <span><strong>Insight:</strong> Pertumbuhan GDP berbanding lurus dengan kapasitas konsumsi pasar domestik negara terkait.</span>
            </div>
        </div>
    </div>

    <!-- 2. Inflation Trend Chart -->
    <div class="col-lg-6 col-md-12">
        <div class="card-corporate p-4 d-flex flex-column h-100">
            <div class="mb-3 d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Inflation Fluctuation Rate</h6>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">Risiko lonjakan biaya operasional & logistik (%)</p>
                </div>
                <i class="fa-solid fa-arrow-trend-up text-warning bg-warning-subtle p-2 rounded-circle"></i>
            </div>
            
            <div class="position-relative w-100 mt-2 flex-grow-1" style="min-height: 250px;">
                <canvas id="inflationChart"></canvas>
            </div>

            <!-- Insight Box -->
            <div class="insight-box mt-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-lightbulb text-warning"></i>
                <span><strong>Insight:</strong> Tren inflasi yang menanjak dapat mengindikasikan membengkaknya biaya penyimpanan dan distribusi.</span>
            </div>
        </div>
    </div>

    <!-- 3. Currency Volatility Chart -->
    <div class="col-lg-6 col-md-12">
        <div class="card-corporate p-4 d-flex flex-column h-100">
            <div class="mb-3 d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Currency Volatility ({{ $chartData['currency']['code'] }} / USD)</h6>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">Pergerakan historis nilai tukar</p>
                </div>
                <i class="fa-solid fa-coins" style="color: #6366f1; background-color: #e0e7ff; padding: 0.5rem; border-radius: 50%;"></i>
            </div>
            
            <div class="position-relative w-100 mt-2 flex-grow-1" style="min-height: 250px;">
                <canvas id="currencyChart"></canvas>
            </div>

            <!-- Insight Box -->
            <div class="insight-box mt-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-lightbulb text-warning"></i>
                <span><strong>Insight:</strong> Volatilitas tinggi pada grafik ini mewajibkan langkah <i>hedging</i> (lindung nilai) sebelum impor.</span>
            </div>
        </div>
    </div>

    <!-- 4. Historical Risk Index Chart -->
    <div class="col-lg-6 col-md-12">
        <div class="card-corporate p-4 d-flex flex-column h-100">
            <div class="mb-3 d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Aggregated Risk Index Trend</h6>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">Akumulasi sentimen, cuaca & ekonomi (0-100)</p>
                </div>
                <i class="fa-solid fa-shield-halved text-danger bg-danger-subtle p-2 rounded-circle"></i>
            </div>
            
            <div class="position-relative w-100 mt-2 flex-grow-1" style="min-height: 250px;">
                <canvas id="riskChart"></canvas>
            </div>

            <!-- Insight Box -->
            <div class="insight-box mt-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-lightbulb text-warning"></i>
                <span><strong>Insight:</strong> Skor di atas 60 menandakan *High Risk*. Perhatikan puncak grafik untuk evaluasi rute alternatif.</span>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT LOGIKA CHART.JS TINGKAT LANJUT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // LIVE CLOCK
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const clockEl = document.getElementById('live-clock');
            if(clockEl) clockEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${timeString}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // BUTTON LOADING
        const form = document.getElementById('monitor-form');
        const btn = document.getElementById('btn-monitor');
        if(form && btn) {
            form.addEventListener('submit', function() {
                btn.style.opacity = '0.8'; btn.style.cursor = 'wait';
                document.getElementById('btn-icon').className = 'spinner-border spinner-border-sm me-1';
                document.getElementById('btn-text').innerText = 'Memproses...';
            });
        }

        // KONFIGURASI GLOBAL KORPORAT CHART.JS
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#64748b'; // Soft corporate text
        
        const tooltipConfig = {
            backgroundColor: '#1e293b', 
            padding: 12, 
            titleFont: { size: 13, family: 'Inter' },
            bodyFont: { size: 14, weight: 'bold', family: 'Inter' }, 
            cornerRadius: 8, 
            displayColors: false,
            boxPadding: 6
        };
        
        // Grid modern (hilangkan garis vertikal, pertahankan horizontal dengan dash)
        const gridConfig = { color: '#e2e8f0', borderDash: [4, 4], drawBorder: false };

        const data = @json($chartData);

        // FUNGSI BANTUAN UNTUK GRADASI KEKINIAN
        function createGradient(ctx, colorStart, colorEnd) {
            let gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, colorStart);
            gradient.addColorStop(1, colorEnd);
            return gradient;
        }

        // ==========================================
        // 1. GDP CHART (Modern Bar with Matcha Gradient)
        // ==========================================
        const ctxGdp = document.getElementById('gdpChart').getContext('2d');
        const gradientGdp = createGradient(ctxGdp, 'rgba(54, 94, 50, 0.8)', 'rgba(129, 162, 99, 0.2)'); // Tema Corporate Matcha
        
        new Chart(ctxGdp, {
            type: 'bar',
            data: {
                labels: data.gdp.labels,
                datasets: [{
                    label: 'GDP (USD)', 
                    data: data.gdp.data,
                    backgroundColor: gradientGdp,
                    hoverBackgroundColor: '#365E32',
                    borderRadius: 6,
                    borderWidth: 0,
                    barPercentage: 0.6
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false }, 
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            label: function(context) {
                                return '$ ' + context.raw.toLocaleString('en-US');
                            }
                        }
                    } 
                }, 
                scales: { 
                    y: { 
                        grid: gridConfig,
                        ticks: {
                            callback: function(value) {
                                // Menyederhanakan tampilan angka di sumbu Y (Triliun/Miliar)
                                if(value >= 1e12) return (value / 1e12).toFixed(1) + 'T';
                                if(value >= 1e9) return (value / 1e9).toFixed(1) + 'B';
                                return value.toLocaleString();
                            }
                        }
                    }, 
                    x: { grid: { display: false } } 
                } 
            }
        });

        // ==========================================
        // 2. INFLATION CHART (Smooth Line Area - Warning Orange)
        // ==========================================
        const ctxInf = document.getElementById('inflationChart').getContext('2d');
        const gradientInf = createGradient(ctxInf, 'rgba(245, 158, 11, 0.4)', 'rgba(245, 158, 11, 0.0)');
        
        new Chart(ctxInf, {
            type: 'line',
            data: {
                labels: data.inflation.labels,
                datasets: [{
                    label: 'Inflation', 
                    data: data.inflation.data,
                    borderColor: '#f59e0b', 
                    backgroundColor: gradientInf,
                    borderWidth: 3, 
                    fill: true, 
                    tension: 0.4, // Membuat garis melengkung kekinian
                    pointBackgroundColor: '#ffffff', 
                    pointBorderColor: '#f59e0b', 
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false }, 
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: { label: (context) => context.raw.toFixed(2) + '%' }
                    } 
                }, 
                scales: { y: { grid: gridConfig }, x: { grid: { display: false } } } 
            }
        });

        // ==========================================
        // 3. CURRENCY CHART (Smooth Line Area - Corporate Indigo/Blue)
        // ==========================================
        const ctxCur = document.getElementById('currencyChart').getContext('2d');
        const gradientCur = createGradient(ctxCur, 'rgba(99, 102, 241, 0.4)', 'rgba(99, 102, 241, 0.0)');
        
        new Chart(ctxCur, {
            type: 'line',
            data: {
                labels: data.currency.labels,
                datasets: [{
                    label: 'Exchange Rate', 
                    data: data.currency.data,
                    borderColor: '#6366f1', 
                    backgroundColor: gradientCur,
                    borderWidth: 3, 
                    fill: true, 
                    tension: 0.4, 
                    pointRadius: 0, // Sembunyikan titik agar terlihat bersih
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false }, 
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: { label: (context) => context.raw.toLocaleString('en-US') + ' ' + data.currency.code }
                    } 
                }, 
                scales: { 
                    y: { 
                        grid: gridConfig,
                        ticks: { callback: (value) => value.toLocaleString('en-US') }
                    }, 
                    x: { grid: { display: false } } 
                } 
            }
        });

        // ==========================================
        // 4. RISK TREND CHART (Smooth Line - Danger Red)
        // ==========================================
        const ctxRisk = document.getElementById('riskChart').getContext('2d');
        const gradientRisk = createGradient(ctxRisk, 'rgba(239, 68, 68, 0.3)', 'rgba(239, 68, 68, 0.0)');

        new Chart(ctxRisk, {
            type: 'line',
            data: {
                labels: data.risk.labels,
                datasets: [{
                    label: 'Risk Score', 
                    data: data.risk.data,
                    borderColor: '#ef4444', 
                    backgroundColor: gradientRisk,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#ffffff', 
                    pointBorderColor: '#ef4444',
                    pointBorderWidth: 2,
                    pointRadius: 4, 
                    pointHoverRadius: 6
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false }, 
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: { label: (context) => context.raw + ' Poin Risiko' }
                    } 
                }, 
                scales: { 
                    y: { min: 0, max: 100, grid: gridConfig }, 
                    x: { grid: { display: false } } 
                } 
            }
        });
    });
</script>
@endsection