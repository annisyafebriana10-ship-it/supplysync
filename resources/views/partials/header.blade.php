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
</style>

<!-- HEADER & FORM PENCARIAN -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 flex-shrink-0 gap-3" style="animation: fadeInDown 0.5s ease-out;">
    
    <!-- Judul & Sub-judul -->
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">Global Country Dashboard</h4>
            
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
        <p class="text-secondary mb-0" style="font-size: 0.85rem;"><i class="fa-solid fa-satellite-dish me-1"></i>Pusat komando pemantauan risiko rantai pasok global.</p>
    </div>

    <!-- Form Pencarian (Interactive Capsule) -->
    <div class="d-flex align-items-center">
        <form id="monitor-form" action="{{ route('country.search') }}" method="POST" class="search-capsule d-flex align-items-center p-1 rounded-pill" style="width: 360px;">
            @csrf
            
            <div class="ps-3 pe-2 text-muted flex-shrink-0">
                <i class="fa-solid fa-earth-americas" style="font-size: 0.9rem; color: var(--matcha-500);"></i>
            </div>

            <div class="flex-grow-1 px-1" style="min-width: 0;">
                <!-- KEMBALI KE SELECT BAWAAN (TANPA SELECT2) AGAR IDENTIK DENGAN CURRENCY -->
                <select name="country" class="form-select border-0 bg-transparent shadow-none p-0" style="width: 100%; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #374151;">
                    @php
                        $list = $countries ?? $countriesList ?? []; 
                        $selected = request('country', $selectedCountry ?? 'Indonesia');
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

<!-- SCRIPT UNTUK INTERAKSI -->
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
                btn.style.pointerEvents = 'none'; 
                
                btnIcon.className = 'spinner-border spinner-border-sm me-1';
                btnText.innerText = 'Memuat...';
            });
        }
    });
</script>