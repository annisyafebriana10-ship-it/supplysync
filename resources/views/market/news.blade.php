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

    /* Animasi denyut untuk indikator server */
    .pulse-dot {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    /* Hover Effect untuk Kartu Berita (Corporate) */
    .news-card {
        transition: all 0.25s ease;
    }
    .news-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>

<!-- HEADER & FORM PENCARIAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 flex-shrink-0 gap-3" style="animation: fadeInDown 0.5s ease-out;">
    
    <!-- Judul & Sub-judul -->
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">News & Market Sentiment</h4>
            
            <!-- Badge Status & Live Clock -->
            <div class="d-flex align-items-center bg-success-subtle border border-success-subtle rounded-pill px-2 py-1 gap-2 shadow-sm" style="font-size: 0.65rem;">
                <span class="d-flex align-items-center text-success fw-bold">
                    <span class="rounded-circle bg-success me-1 pulse-dot" style="width: 6px; height: 6px;"></span>
                    Lexicon AI Active
                </span>
                <span class="text-success opacity-25">|</span>
                <span class="text-success fw-bold" id="live-clock">
                    <i class="fa-regular fa-clock me-1"></i> 00:00:00
                </span>
            </div>
        </div>
        <p class="text-secondary mb-0" style="font-size: 0.85rem;"><i class="fa-solid fa-brain me-1"></i>Analisis sentimen berita ekonomi dan rantai pasok Real-time.</p>
    </div>

    <!-- Form Pencarian (Interactive Capsule) -->
    <div class="d-flex align-items-center">
        <form id="monitor-form" action="{{ route('market.news') }}" method="GET" class="search-capsule d-flex align-items-center p-1 rounded-pill" style="width: 360px;">
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

<!-- RINGKASAN SENTIMEN (STATS ROW) - GAYA CORPORATE -->
<div class="row g-3 mb-4">
    @php
        $posPct = $totalNews > 0 ? round(($positive / $totalNews) * 100) : 0;
        $neuPct = $totalNews > 0 ? round(($neutral / $totalNews) * 100) : 0;
        $negPct = $totalNews > 0 ? round(($negative / $totalNews) * 100) : 0;
    @endphp

    <!-- Total Berita -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100 d-flex flex-column position-relative overflow-hidden">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase fw-bold text-dark" style="font-size: 0.70rem;">Total Data Berita</span>
                <i class="fa-solid fa-newspaper text-secondary fs-6"></i>
            </div>
            <div class="mt-auto">
                <h3 class="fw-bold text-dark mb-0">{{ $totalNews }}</h3>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">Artikel diekstrak</p>
                <div style="height: 4px;" class="mt-2"></div>
            </div>
            
        </div>
    </div>
    
    <!-- Sentimen Positif -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100 d-flex flex-column border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase fw-bold text-success" style="font-size: 0.70rem;">Sentimen Positif</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold" style="font-size: 0.65rem;">{{ $posPct }}%</span>
            </div>
            <div class="mt-auto">
                <h3 class="fw-bold text-dark mb-0">{{ $positive }}</h3>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">Sinyal pasar kondusif</p>
                <div class="progress mt-2" style="height: 4px; border-radius: 2px; background-color: #f1f5f9;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $posPct }}%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sentimen Netral -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100 d-flex flex-column border-start border-4 border-secondary">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase fw-bold text-secondary" style="font-size: 0.70rem;">Sentimen Netral</span>
                <span class="badge bg-light text-secondary border fw-bold" style="font-size: 0.65rem;">{{ $neuPct }}%</span>
            </div>
            <div class="mt-auto">
                <h3 class="fw-bold text-dark mb-0">{{ $neutral }}</h3>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">Kondisi stabil/berimbang</p>
                <div class="progress mt-2" style="height: 4px; border-radius: 2px; background-color: #f1f5f9;">
                    <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $neuPct }}%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sentimen Negatif -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100 d-flex flex-column border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase fw-bold text-danger" style="font-size: 0.70rem;">Sentimen Negatif</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold" style="font-size: 0.65rem;">{{ $negPct }}%</span>
            </div>
            <div class="mt-auto">
                <h3 class="fw-bold text-dark mb-0">{{ $negative }}</h3>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">Peringatan risiko operasional</p>
                <div class="progress mt-2" style="height: 4px; border-radius: 2px; background-color: #f1f5f9;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $negPct }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DAFTAR ARTIKEL HASIL ANALISIS LEXICON (2 KOLOM KOMPAK) -->
<div class="row g-3">
    @forelse($newsData as $article)
        @php
            if ($article['sentiment'] == 'Positive') {
                $borderColor = '#22c55e';
                $badgeClass = 'bg-success';
            } elseif ($article['sentiment'] == 'Negative') {
                $borderColor = '#ef4444';
                $badgeClass = 'bg-danger';
            } else {
                $borderColor = '#9ca3af';
                $badgeClass = 'bg-secondary';
            }
        @endphp

        <!-- Diubah menjadi col-lg-6 agar tidak terlalu lebar -->
        <div class="col-lg-6 col-md-12">
            <div class="card-corporate news-card p-3 d-flex flex-column h-100 border-start border-4" style="border-left-color: {{ $borderColor }} !important;">
                
                <!-- Header Kartu Berita -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">
                        <i class="fa-regular fa-building me-1"></i>{{ $article['source'] }}
                    </span>
                    <span class="badge {{ $badgeClass }}" style="font-size: 0.65rem;">{{ $article['sentiment'] }}</span>
                </div>
                
                <!-- Judul Berita (Dibatasi maksimal 2 baris) -->
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $article['title'] }}
                </h6>
                
                <!-- Bagian Bawah & Tombol -->
                <div class="mt-auto d-flex justify-content-between align-items-end pt-2">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        Indeks: <strong class="{{ $badgeClass === 'bg-secondary' ? 'text-secondary' : ($badgeClass === 'bg-success' ? 'text-success' : 'text-danger') }}">{{ $article['score'] }} Poin</strong>
                    </small>
                    
                    <a href="{{ $article['url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-3 rounded-pill d-inline-flex align-items-center" style="font-size: 0.7rem; height: 26px;">
                        Detail <i class="fa-solid fa-expand ms-1" style="font-size: 0.6rem;"></i>
                    </a>
                </div>

            </div>
        </div>
    @empty
        <div class="col-md-12">
            <div class="card-corporate p-5 text-center d-flex flex-column align-items-center justify-content-center">
                <i class="fa-regular fa-folder-open text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-dark mb-1">Tidak ada berita terkait</h5>
                <p class="text-muted" style="font-size: 0.85rem;">Algoritma kami tidak menemukan berita logistik atau ekonomi terkini untuk negara ini.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- SCRIPT UNTUK INTERAKSI -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // LIVE CLOCK ENGINE
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const clockEl = document.getElementById('live-clock');
            if(clockEl) clockEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${timeString}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // LOADING STATE BUTTON
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
    });
</script>
@endsection