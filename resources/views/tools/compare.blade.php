@extends('layouts.app')

@section('content')
<style>
    /* Mencegah Scroll Horizontal secara paksa pada wrapper */
    .compare-wrapper {
        overflow-x: hidden;
        width: 100%;
        padding-bottom: 2rem;
    }

    /* Transisi Form Kapsul */
    .compare-capsule {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .compare-capsule:focus-within {
        border-color: var(--matcha-500);
        box-shadow: 0 4px 20px rgba(129, 162, 99, 0.15);
    }
    
    .btn-matcha { background-color: var(--matcha-500); color: white; transition: all 0.2s; border: none; }
    .btn-matcha:hover { background-color: #6b8a4f; transform: translateY(-1px); color: white; box-shadow: 0 4px 12px rgba(129, 162, 99, 0.3); }

    .pulse-dot { animation: pulse 1.5s infinite; }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    /* Card Corporate Modern */
    .card-modern {
        background: #ffffff; 
        border-radius: 16px; 
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .card-modern:hover { 
        transform: translateY(-3px);
        box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.08); 
    }
    
    /* Modern Icon Box */
    .icon-box {
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px;
        font-size: 1rem;
    }

    /* Thin Progress Bar untuk UI Kekinian */
    .progress-thin {
        height: 6px;
        border-radius: 10px;
        background-color: #f1f5f9;
        overflow: hidden;
        margin-top: 6px;
    }
    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }

    /* Custom Data Row */
    .data-row {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #f1f5f9;
    }
    .data-row:last-child { border-bottom: none; }
</style>

<div class="compare-wrapper container-fluid px-0">
    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3" style="animation: fadeInDown 0.4s ease-out;">
        <div>
            <h4 class="mb-1 fw-bold text-dark" style="letter-spacing: -0.5px;">Komparasi Negara</h4>
            <p class="text-secondary mb-0" style="font-size: 0.85rem;">Analisis cerdas untuk menentukan rute logistik paling aman.</p>
        </div>
        <div class="d-flex align-items-center bg-success-subtle border border-success-subtle rounded-pill px-3 py-2 gap-2 shadow-sm" style="font-size: 0.75rem;">
            <span class="d-flex align-items-center text-success fw-bold">
                <span class="rounded-circle bg-success me-2 pulse-dot" style="width: 8px; height: 8px;"></span>
                Node 10 Sinkron
            </span>
            <span class="text-success opacity-25">|</span>
            <span class="text-success fw-bold" id="live-clock"><i class="fa-regular fa-clock me-1"></i> 00:00</span>
        </div>
    </div>

    <!-- FORM PERBANDINGAN RESPONSIVE -->
    <div class="compare-capsule p-2 mb-4 mx-auto w-100" style="max-width: 900px;">
        <form id="compare-form" action="{{ route('tools.compare') }}" method="GET" class="d-flex flex-column flex-lg-row align-items-center justify-content-between m-0 gap-2">
            
            <!-- Negara 1 -->
            <div class="d-flex align-items-center bg-light rounded-pill px-3 py-2 border flex-grow-1 w-100">
                <i class="fa-solid fa-location-dot text-secondary me-2"></i>
                <select name="country1" class="form-select border-0 bg-transparent shadow-none p-0 fw-bold text-dark" style="font-size: 0.9rem; cursor: pointer;">
                    @foreach($countriesList as $c)
                        <option value="{{ $c }}" {{ $country1Name == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Icon VS -->
            <div class="d-none d-lg-block">
                <span class="badge rounded-circle text-white fw-bold shadow-sm d-flex align-items-center justify-content-center" style="background-color: #cbd5e1; width: 32px; height: 32px; font-size: 0.7rem;">VS</span>
            </div>

            <!-- Negara 2 -->
            <div class="d-flex align-items-center bg-light rounded-pill px-3 py-2 border flex-grow-1 w-100">
                <i class="fa-solid fa-location-dot text-secondary me-2"></i>
                <select name="country2" class="form-select border-0 bg-transparent shadow-none p-0 fw-bold text-dark" style="font-size: 0.9rem; cursor: pointer;">
                    @foreach($countriesList as $c)
                        <option value="{{ $c }}" {{ $country2Name == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol -->
            <button type="submit" id="btn-compare" class="btn btn-matcha fw-bold rounded-pill px-4 py-2 w-100 w-lg-auto mt-2 mt-lg-0" style="font-size: 0.85rem; white-space: nowrap;">
                <i class="fa-solid fa-bolt me-1" id="btn-icon"></i> <span id="btn-text">Analisis Sekarang</span>
            </button>
        </form>
    </div>

    @if(session('error'))
        <div class="alert alert-danger py-2 px-3 border-0 shadow-sm rounded-3 mx-auto" style="max-width: 900px; font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}</div>
    @endif

    @if(isset($data1) && isset($data2))
    <!-- AREA DATA (Grid Responsif 1 kolom di HP, 2 kolom di Laptop) -->
    <div class="row g-4 align-items-stretch">

        <!-- NEGARA 1 -->
        <div class="col-12 col-lg-6">
            <div class="card-modern p-4 border-top border-4 border-{{ $data1['risk']['color'] }} position-relative h-100">
                
                <!-- LOGIKA REKOMENDASI NEGARA 1 (Casting Langsung) -->
                @if((float)$data1['risk']['score'] > (float)$data2['risk']['score'])
                    <div class="position-absolute top-0 start-50 translate-middle-x mt-n3">
                        <span class="badge bg-success rounded-pill shadow px-3 py-2 border border-2 border-white" style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; z-index: 10;">
                            <i class="fa-solid fa-crown text-warning me-1"></i> REKOMENDASI UTAMA
                        </span>
                    </div>
                @elseif((float)$data1['risk']['score'] == (float)$data2['risk']['score'])
                    <div class="position-absolute top-0 start-50 translate-middle-x mt-n3">
                        <span class="badge bg-secondary rounded-pill shadow px-3 py-2 border border-2 border-white" style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; z-index: 10;">
                            <i class="fa-solid fa-scale-balanced text-light me-1"></i> RISIKO SEIMBANG
                        </span>
                    </div>
                @endif

                <!-- Header Negara -->
                <div class="text-center mt-3 mb-4">
                    <h4 class="fw-bold text-dark mb-1">{{ $country1Name }}</h4>
                    <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" style="font-weight: 500;">
                        {{ $data1['base']['names']['common'] ?? '-' }}
                    </span>
                </div>

                <!-- Skor Modern -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-4 mb-4" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1px solid #e2e8f0;">
                    <div>
                        <span class="fw-bold text-muted d-block mb-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Tingkat Skor</span>
                        <span class="badge bg-{{ $data1['risk']['color'] }}-subtle text-{{ $data1['risk']['color'] }} fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem;">
                            {{ strtoupper($data1['risk']['status']) }}
                        </span>
                    </div>
                    <h1 class="fw-black text-dark mb-0" style="font-size: 3.2rem; font-weight: 900; line-height: 1; letter-spacing: -2px;">
                        {{ $data1['risk']['score'] }}
                    </h1>
                </div>

                <hr class="border-secondary opacity-10 my-4">

                <!-- Rincian Data Informatif -->
                <div class="data-row gap-3">
                    <div class="icon-box bg-primary-subtle text-primary"><i class="fa-solid fa-cloud-sun"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Anomali Cuaca</span>
                            <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $data1['risk']['breakdown']['weather'] }} / 100</span>
                        </div>
                        <div class="progress-thin"><div class="progress-bar-custom bg-primary" style="width: {{ $data1['risk']['breakdown']['weather'] }}%"></div></div>
                    </div>
                </div>

                <div class="data-row gap-3">
                    <div class="icon-box bg-danger-subtle text-danger"><i class="fa-solid fa-newspaper"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Stabilitas Sosial & Berita</span>
                            <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $data1['risk']['breakdown']['news'] }} / 100</span>
                        </div>
                        <div class="progress-thin"><div class="progress-bar-custom bg-danger" style="width: {{ $data1['risk']['breakdown']['news'] }}%"></div></div>
                    </div>
                </div>

                <div class="data-row gap-3">
                    <div class="icon-box bg-warning-subtle text-warning"><i class="fa-solid fa-arrow-trend-up"></i></div>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Laju Inflasi Tahunan</span>
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.85rem; font-weight: 700;">{{ $data1['economy']['inflation'] }}%</span>
                    </div>
                </div>

                <div class="data-row gap-3 border-0">
                    <div class="icon-box bg-indigo-subtle text-indigo" style="background-color: #e0e7ff; color: #4338ca;"><i class="fa-solid fa-coins"></i></div>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Kekuatan Nilai Tukar</span>
                        <div class="text-end">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $data1['exchangeRate'] }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ $data1['currencyCode'] }} per USD</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- NEGARA 2 -->
        <div class="col-12 col-lg-6">
            <div class="card-modern p-4 border-top border-4 border-{{ $data2['risk']['color'] }} position-relative h-100">
                
                <!-- LOGIKA REKOMENDASI NEGARA 2 (Casting Langsung) -->
                @if((float)$data2['risk']['score'] > (float)$data1['risk']['score'])
                    <div class="position-absolute top-0 start-50 translate-middle-x mt-n3">
                        <span class="badge bg-success rounded-pill shadow px-3 py-2 border border-2 border-white" style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; z-index: 10;">
                            <i class="fa-solid fa-crown text-warning me-1"></i> REKOMENDASI UTAMA
                        </span>
                    </div>
                @elseif((float)$data2['risk']['score'] == (float)$data1['risk']['score'])
                    <div class="position-absolute top-0 start-50 translate-middle-x mt-n3">
                        <span class="badge bg-secondary rounded-pill shadow px-3 py-2 border border-2 border-white" style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; z-index: 10;">
                            <i class="fa-solid fa-scale-balanced text-light me-1"></i> RISIKO SEIMBANG
                        </span>
                    </div>
                @endif

                <!-- Header Negara -->
                <div class="text-center mt-3 mb-4">
                    <h4 class="fw-bold text-dark mb-1">{{ $country2Name }}</h4>
                    <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" style="font-weight: 500;">
                        {{ $data2['base']['names']['common'] ?? '-' }}
                    </span>
                </div>

                <!-- Skor Modern -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-4 mb-4" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1px solid #e2e8f0;">
                    <div>
                        <span class="fw-bold text-muted d-block mb-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Tingkat Skor</span>
                        <span class="badge bg-{{ $data2['risk']['color'] }}-subtle text-{{ $data2['risk']['color'] }} fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem;">
                            {{ strtoupper($data2['risk']['status']) }}
                        </span>
                    </div>
                    <h1 class="fw-black text-dark mb-0" style="font-size: 3.2rem; font-weight: 900; line-height: 1; letter-spacing: -2px;">
                        {{ $data2['risk']['score'] }}
                    </h1>
                </div>

                <hr class="border-secondary opacity-10 my-4">

                <!-- Rincian Data Informatif -->
                <div class="data-row gap-3">
                    <div class="icon-box bg-primary-subtle text-primary"><i class="fa-solid fa-cloud-sun"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Anomali Cuaca</span>
                            <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $data2['risk']['breakdown']['weather'] }} / 100</span>
                        </div>
                        <div class="progress-thin"><div class="progress-bar-custom bg-primary" style="width: {{ $data2['risk']['breakdown']['weather'] }}%"></div></div>
                    </div>
                </div>

                <div class="data-row gap-3">
                    <div class="icon-box bg-danger-subtle text-danger"><i class="fa-solid fa-newspaper"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Stabilitas Sosial & Berita</span>
                            <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $data2['risk']['breakdown']['news'] }} / 100</span>
                        </div>
                        <div class="progress-thin"><div class="progress-bar-custom bg-danger" style="width: {{ $data2['risk']['breakdown']['news'] }}%"></div></div>
                    </div>
                </div>

                <div class="data-row gap-3">
                    <div class="icon-box bg-warning-subtle text-warning"><i class="fa-solid fa-arrow-trend-up"></i></div>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Laju Inflasi Tahunan</span>
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.85rem; font-weight: 700;">{{ $data2['economy']['inflation'] }}%</span>
                    </div>
                </div>

                <div class="data-row gap-3 border-0">
                    <div class="icon-box bg-indigo-subtle text-indigo" style="background-color: #e0e7ff; color: #4338ca;"><i class="fa-solid fa-coins"></i></div>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-bold" style="font-size: 0.8rem;">Kekuatan Nilai Tukar</span>
                        <div class="text-end">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $data2['exchangeRate'] }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ $data2['currencyCode'] }} per USD</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // LIVE CLOCK Sederhana
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const clockEl = document.getElementById('live-clock');
            if(clockEl) clockEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${timeString}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // BUTTON LOADING
        const form = document.getElementById('compare-form');
        const btn = document.getElementById('btn-compare');
        if(form && btn) {
            form.addEventListener('submit', function() {
                btn.style.opacity = '0.9'; btn.style.cursor = 'wait'; btn.style.pointerEvents = 'none';
                document.getElementById('btn-icon').className = 'spinner-border spinner-border-sm me-2';
                document.getElementById('btn-text').innerText = 'Menganalisis...';
            });
        }
    });
</script>
@endsection