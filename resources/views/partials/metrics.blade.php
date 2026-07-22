<!-- BLOK 2: CORE INTELLIGENCE METRICS (4 PILAR) -->
<div class="row g-3 mb-3 flex-shrink-0">
    <!-- Peta Risiko (Scoring Engine) -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100" style="background-color: {{ $riskData['bg'] ?? 'var(--matcha-50)' }}; border: 1px solid {{ $riskData['border'] ?? 'var(--matcha-500)' }};">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase fw-bold text-dark" style="font-size: 0.70rem;">Total Risk Score</span>
                <i class="fa-solid fa-shield-halved text-{{ $riskData['color'] ?? 'success' }} fs-6"></i>
            </div>
            
            <h3 class="fw-bold text-dark mb-0">
                {{ $riskData['score'] ?? '0' }} 
                <span class="fs-6 text-muted fw-normal">/ {{ $riskData['status'] ?? 'N/A' }}</span>
            </h3>
            
            <p class="mt-1 mb-0 text-{{ $riskData['color'] ?? 'success' }}" style="font-size: 0.75rem; font-weight: 600;">
                <i class="fa-solid {{ $riskData['icon'] ?? 'fa-check-circle' }} me-1"></i> {{ $riskData['text'] ?? '--' }}
            </p>
        </div>
    </div>

    <!-- Ekonomi (World Bank) -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100" style="background: white;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase text-muted fw-bold" style="font-size: 0.70rem;">Economy (World Bank)</span>
                
                <!-- TOMBOL DETAIL EKONOMI -->
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#economyDetailModal" style="font-size: 0.7rem;">
                    <i class="fa-solid fa-expand"></i> Detail
                </button>
            </div>
            
            <h3 class="fw-bold text-dark mb-0">
                {{ $economy['inflation'] ?? '--' }}<span style="font-size: 1rem;">%</span> 
                <span class="fs-6 text-muted fw-normal">Inflasi</span>
            </h3>
            
            <p class="text-muted mt-1 mb-0" style="font-size: 0.75rem;">
                <i class="fa-solid fa-sack-dollar me-1"></i> GDP: {{ $economy['gdp'] ?? '--' }}
            </p>
        </div>
    </div>

    <!-- Cuaca (Open-Meteo) -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100" style="background: white;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase text-muted fw-bold" style="font-size: 0.70rem;">Weather (Open-Meteo)</span>
                
                <!-- TOMBOL DETAIL CUACA -->
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#weatherDetailModal" style="font-size: 0.7rem;">
                    <i class="fa-solid fa-expand"></i> Detail
                </button>
            </div>
            
            <h3 class="fw-bold text-dark mb-0">
                {{ isset($weather['current']['temperature_2m']) ? $weather['current']['temperature_2m'] : '--' }}<span style="font-size: 1rem;">°C</span>
            </h3>
            
            <p class="text-muted mt-1 mb-0" style="font-size: 0.75rem;">
                <i class="fa-solid fa-wind me-1"></i> Angin: {{ isset($weather['current']['wind_speed_10m']) ? $weather['current']['wind_speed_10m'] . ' km/h' : '--' }}
            </p>
        </div>
    </div>

    <!-- Kurs (ExchangeRate) -->
    <div class="col-md-3">
        <div class="card-corporate p-3 h-100" style="background: white;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-uppercase text-muted fw-bold" style="font-size: 0.70rem;">Currency Impact</span>
                <i class="fa-solid fa-coins text-secondary fs-6"></i>
            </div>
            
            <h3 class="fw-bold text-dark mb-0">
                {{ $exchangeRate ?? '--' }}
            </h3>
            
            <p class="text-muted mt-1 mb-0" style="font-size: 0.75rem;">
                <i class="fa-solid fa-money-bill-transfer me-1"></i> {{ $country['currencies'][0]['code'] ?? 'N/A' }} thd 1 USD
            </p>
        </div>
    </div>
</div>