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
        box-shadow: 0 6px 16px rgba(129, 162, 99, 0.15);
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

    /* Animasi denyut indikator */
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
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
    }
    .card-corporate:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    /* Progress bar khusus breakdown */
    .progress-weight {
        height: 6px;
        border-radius: 4px;
        background-color: #f1f5f9;
    }
</style>

<!-- HEADER & FORM PENCARIAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-2 flex-shrink-0 gap-3" style="animation: fadeInDown 0.5s ease-out;">
    
    <!-- Judul & Sub-judul -->
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">Risk Scoring Engine</h4>
            
            <!-- Badge Status & Live Clock -->
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
        <p class="text-secondary mb-0" style="font-size: 0.85rem;"><i class="fa-solid fa-microchip me-1"></i>Transparansi algoritma Weighted Risk Model untuk <strong class="text-dark">{{ $targetCountry }}</strong>.</p>
    </div>

    <!-- Form Pencarian (Native Dropdown) -->
    <div class="d-flex align-items-center">
        <!-- Menggunakan GET karena ini halaman analitik yang cocok menggunakan query string -->
        <form id="monitor-form" action="{{ route('analytics.risk') }}" method="GET" class="search-capsule d-flex align-items-center p-1 rounded-pill" style="width: 360px;">
            <div class="ps-3 pe-2 text-muted flex-shrink-0">
                <i class="fa-solid fa-earth-americas" style="font-size: 0.9rem; color: var(--matcha-500);"></i>
            </div>

            <div class="flex-grow-1 px-1" style="min-width: 0;">
                <select name="country" class="form-select border-0 bg-transparent shadow-none p-0" style="width: 100%; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #374151;">
                    @php
                        $list = $countriesList ?? []; 
                        $selected = request('country', $targetCountry ?? 'Indonesia');
                    @endphp
                    
                    @forelse($list as $c)
                        <option value="{{ $c }}" {{ $selected == $c ? 'selected' : '' }}>{{ $c }}</option>
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

<!-- ALERT JIKA ADA ERROR -->
@if(session('error'))
    <div class="alert alert-danger mb-4 border-0 shadow-sm d-flex align-items-center py-2 px-3" style="background-color: #fef2f2; border-left: 4px solid #ef4444 !important; border-radius: 8px; color: #991b1b; animation: fadeIn 0.4s ease-out;">
        <i class="fa-solid fa-triangle-exclamation me-3 fs-5"></i>
        <div>
            <strong style="font-size: 0.85rem;">Peringatan Sistem:</strong> 
            <span style="font-size: 0.85rem;">{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.7rem;"></button>
    </div>
@endif

@if(isset($riskData))
<div class="row g-4">
    <!-- KOLOM KIRI: HASIL AKHIR & GRAFIK -->
    <div class="col-lg-5 col-md-12 d-flex flex-column">
        <!-- Kartu Skor Final -->
        <div class="card-corporate p-4 mb-4 border-start border-4 border-{{ $riskData['color'] }} flex-shrink-0" style="background-color: {{ $riskData['bg'] }};">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="text-uppercase fw-bold text-dark opacity-75" style="font-size: 0.75rem; letter-spacing: 1px;">Calculated Final Score</span>
                <i class="fa-solid fa-calculator text-{{ $riskData['color'] }} fs-5"></i>
            </div>
            
            <div class="d-flex align-items-center gap-4">
                <h1 class="fw-bold mb-0 text-{{ $riskData['color'] }}" style="font-size: 4.5rem; letter-spacing: -2px; line-height: 1;">
                    {{ $riskData['score'] }}
                </h1>
                <div>
                    <h5 class="fw-bold text-dark mb-1">Status: {{ $riskData['status'] }} Risk</h5>
                    <p class="mb-0 text-{{ $riskData['color'] }} fw-bold" style="font-size: 0.85rem;">
                        <i class="fa-solid {{ $riskData['icon'] }} me-1"></i> {{ $riskData['text'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Kartu Grafik Radar -->
        <div class="card-corporate p-4 flex-grow-1 d-flex flex-column">
            <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;"><i class="fa-solid fa-spider me-2 text-secondary"></i>Risk Dimension Mapping</h6>
            <div class="w-100 mt-auto position-relative d-flex justify-content-center" style="height: 250px;">
                <canvas id="riskRadarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: ALGORITHM BREAKDOWN -->
    <div class="col-lg-7 col-md-12">
        <div class="card-corporate p-4 h-100 d-flex flex-column">
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;"><i class="fa-solid fa-gears me-2 text-secondary"></i>Weighted Scoring Breakdown</h6>
                <p class="text-muted" style="font-size: 0.75rem;">Detail kalkulasi indeks risiko berdasarkan parameter yang ditarik secara <i>real-time</i>.</p>
            </div>

            <div class="row g-3 flex-grow-1">
                <!-- 1. Cuaca (30%) -->
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary text-white" style="font-size: 0.65rem;">BOBOT: 30%</span>
                            <i class="fa-solid fa-cloud-bolt text-primary"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Weather Risk</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <span class="text-muted" style="font-size: 0.70rem;">Raw Score</span>
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $riskData['breakdown']['weather'] }} <small class="text-muted fw-normal">/ 100</small></span>
                        </div>
                        <div class="progress progress-weight">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $riskData['breakdown']['weather'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Berita & Sentimen (40%) -->
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger text-white" style="font-size: 0.65rem;">BOBOT: 40%</span>
                            <i class="fa-solid fa-newspaper text-danger"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">News Sentiment Risk</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <span class="text-muted" style="font-size: 0.70rem;">Raw Score</span>
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $riskData['breakdown']['news'] }} <small class="text-muted fw-normal">/ 100</small></span>
                        </div>
                        <div class="progress progress-weight">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $riskData['breakdown']['news'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- 3. Inflasi (20%) -->
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">BOBOT: 20%</span>
                            <i class="fa-solid fa-arrow-trend-up text-warning"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Inflation Risk</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <span class="text-muted" style="font-size: 0.70rem;">Raw Score</span>
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $riskData['breakdown']['inflation'] }} <small class="text-muted fw-normal">/ 100</small></span>
                        </div>
                        <div class="progress progress-weight">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $riskData['breakdown']['inflation'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- 4. Mata Uang (10%) -->
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 h-100" style="background-color: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-success text-white" style="font-size: 0.65rem;">BOBOT: 10%</span>
                            <i class="fa-solid fa-coins text-success"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Currency Volatility Risk</h6>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <span class="text-muted" style="font-size: 0.70rem;">Raw Score</span>
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $riskData['breakdown']['currency'] }} <small class="text-muted fw-normal">/ 100</small></span>
                        </div>
                        <div class="progress progress-weight">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $riskData['breakdown']['currency'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rumus Kalkulasi -->
            <div class="mt-4 p-3 rounded-3" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                <span class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.65rem;">Mathematical Formula</span>
                <code class="text-dark fw-bold" style="font-size: 0.75rem;">
                    Final Score = (Weather × 0.3) + (News × 0.4) + (Inflation × 0.2) + (Currency × 0.1)
                </code>
            </div>

        </div>
    </div>
</div>
@endif

<!-- SCRIPT LOGIKA -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. LIVE CLOCK ENGINE
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const clockEl = document.getElementById('live-clock');
            if (clockEl) clockEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${timeString}`;
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
                btn.style.pointerEvents = 'none'; 
                
                btnIcon.className = 'spinner-border spinner-border-sm me-1';
                btnText.innerText = 'Memuat...';
            });
        }

        // 3. RADAR CHART.JS
        @if(isset($riskData))
        const ctxRadar = document.getElementById('riskRadarChart');
        if (ctxRadar) {
            const weatherScore = {{ $riskData['breakdown']['weather'] }};
            const newsScore = {{ $riskData['breakdown']['news'] }};
            const inflationScore = {{ $riskData['breakdown']['inflation'] }};
            const currencyScore = {{ $riskData['breakdown']['currency'] }};

            new Chart(ctxRadar.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: ['Weather (30%)', 'News (40%)', 'Inflation (20%)', 'Currency (10%)'],
                    datasets: [{
                        label: 'Risk Raw Score',
                        data: [weatherScore, newsScore, inflationScore, currencyScore],
                        backgroundColor: 'rgba(34, 197, 94, 0.15)', // Matcha transparent
                        borderColor: 'var(--matcha-500)',
                        pointBackgroundColor: 'var(--matcha-500)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'var(--matcha-500)',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { color: 'rgba(0, 0, 0, 0.05)' },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            pointLabels: {
                                font: { family: 'Inter', size: 11, weight: 'bold' },
                                color: '#64748b'
                            },
                            ticks: {
                                display: false,
                                min: 0,
                                max: 100,
                                stepSize: 20
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 10,
                            bodyFont: { size: 13, family: 'Inter' },
                            displayColors: false,
                            cornerRadius: 6,
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endsection