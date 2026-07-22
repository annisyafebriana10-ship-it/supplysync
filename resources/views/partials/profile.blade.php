<!-- BLOK 1: PROFIL NEGARA (COMPACT BANNER) -->
<div class="card-corporate p-3 mb-3 border-start border-4 shadow-sm flex-shrink-0" style="border-color: var(--matcha-500) !important; background: white;">
    <div class="row align-items-center">
        <!-- Sisi Kiri: Identitas -->
        <div class="col-md-6 d-flex align-items-center gap-3">
            @if(isset($country['flag']['url_png']))
                <img src="{{ $country['flag']['url_png'] }}" width="70" class="rounded border shadow-sm" alt="Flag">
            @else
                <div class="bg-light rounded border d-flex justify-content-center align-items-center" style="width: 70px; height: 46px;">
                    <i class="fa-solid fa-flag text-secondary"></i>
                </div>
            @endif
            <div>
                <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">{{ $country['names']['common'] ?? 'N/A' }}</h4>
                <div class="d-flex gap-2">
                    <span class="badge bg-light text-secondary border"><i class="fa-solid fa-building-columns me-1"></i> {{ $country['capitals'][0]['name'] ?? ($country['capitals'][0] ?? 'N/A') }}</span>
                    <span class="badge bg-light text-secondary border"><i class="fa-solid fa-earth-americas me-1"></i> {{ $country['region'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        
        <!-- Sisi Kanan: Demografi Ringkas & TOMBOL MODAL -->
        <div class="col-md-6 d-flex justify-content-end align-items-center gap-4 text-end">
            <div>
                <p class="text-muted mb-0" style="font-size: 0.70rem; text-transform: uppercase; font-weight: 700;">Populasi</p>
                <h6 class="fw-bold text-dark mb-0">{{ isset($country['population']) ? number_format($country['population']) : '--' }}</h6>
            </div>
            <div>
                <p class="text-muted mb-0" style="font-size: 0.70rem; text-transform: uppercase; font-weight: 700;">Mata Uang</p>
                <h6 class="fw-bold text-dark mb-0">{{ $country['currencies'][0]['code'] ?? '--' }}</h6>
            </div>
            <div>
                <p class="text-muted mb-0" style="font-size: 0.70rem; text-transform: uppercase; font-weight: 700;">Kode Negara</p>
                <h6 class="fw-bold text-dark mb-0">{{ $country['codes']['alpha_2'] ?? '--' }}</h6>
            </div>
            
            <!-- Tombol untuk memicu Modal -->
            <div class="border-start ps-3 ms-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#countryDetailModal">
                    <i class="fa-solid fa-list-ul me-1"></i> Detail
                </button>
            </div>
        </div>
    </div>
</div>