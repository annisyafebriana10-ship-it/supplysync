@extends('layouts.app')

@section('content')
<style>
    .btn-matcha { background-color: var(--matcha-500); color: white; transition: all 0.2s; border: none; }
    .btn-matcha:hover { background-color: #6b8a4f; transform: translateY(-1px); color: white; box-shadow: 0 4px 12px rgba(129, 162, 99, 0.3); }
    
    .card-modern {
        background: #ffffff; 
        border-radius: 16px; 
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-modern:hover { transform: translateY(-3px); box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.08); }
    
    .data-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #f1f5f9; }
    .data-row:last-child { border-bottom: none; }
</style>

<div class="container-fluid px-0">
    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark" style="letter-spacing: -0.5px;">My Watchlist</h4>
            <p class="text-secondary mb-0" style="font-size: 0.85rem;">Pantau kondisi real-time negara-negara kunci dalam rantai pasok Anda.</p>
        </div>
        
        <!-- FORM TAMBAH NEGARA -->
        <!-- FORM TAMBAH NEGARA -->
        <form action="{{ route('tools.watchlist.store') }}" method="POST" class="d-flex align-items-center bg-white rounded-pill px-2 py-1 border shadow-sm" style="max-width: 400px; width: 100%;">
            @csrf
            <i class="fa-solid fa-magnifying-glass text-secondary ms-2" style="font-size: 0.8rem;"></i>
            
            <!-- Ubah name menjadi country_id -->
            <select name="country_id" class="form-select border-0 bg-transparent shadow-none px-2 fw-bold text-dark" style="font-size: 0.85rem; cursor: pointer;" required>
                <option value="" disabled selected>Tambah negara ke pantauan...</option>
                
                <!-- Ubah format pemanggilan variabel $c -->
                @foreach($countriesList as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
                
            </select>
            <button type="submit" class="btn btn-matcha btn-sm rounded-pill px-3 fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-plus"></i></button>
        </form>
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success py-2 px-3 border-0 shadow-sm rounded-3" style="font-size: 0.85rem;"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-warning py-2 px-3 border-0 shadow-sm rounded-3" style="font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}</div>
    @endif

    <!-- GRID WATCHLIST -->
    @if(count($watchlistData) > 0)
        <div class="row g-4">
            @foreach($watchlistData as $data)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card-modern p-4 border-top border-4 border-{{ $data['risk']['color'] }} h-100 position-relative">
                    
                    <!-- Tombol Hapus -->
                    <form action="{{ route('tools.watchlist.destroy', $data['watchlist_id']) }}" method="POST" class="position-absolute top-0 end-0 mt-3 me-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" style="width: 30px; height: 30px; padding: 0;" onclick="return confirm('Hapus {{ $data['country_name'] }} dari Watchlist?')" title="Hapus Negara">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>

                    <!-- Nama Negara & Skor -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-1">
                        <h5 class="fw-bold text-dark mb-0">{{ $data['country_name'] }}</h5>
                        <h2 class="fw-black text-dark mb-0" style="font-weight: 900; letter-spacing: -1px;">{{ $data['risk']['score'] }}</h2>
                    </div>
                    
                    <span class="badge bg-{{ $data['risk']['color'] }}-subtle text-{{ $data['risk']['color'] }} fw-bold px-3 py-1 rounded-pill mb-3 d-inline-block" style="font-size: 0.7rem;">
                        {{ strtoupper($data['risk']['status']) }} RISK
                    </span>

                    <hr class="border-secondary opacity-10 my-2">

                    <!-- Rincian -->
                    <div class="data-row">
                        <span class="text-secondary fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-cloud-sun text-primary me-2"></i>Cuaca</span>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $data['risk']['breakdown']['weather'] }}/100</span>
                    </div>
                    <div class="data-row">
                        <span class="text-secondary fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-newspaper text-danger me-2"></i>Berita</span>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $data['risk']['breakdown']['news'] }}/100</span>
                    </div>
                    <div class="data-row">
                        <span class="text-secondary fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-arrow-trend-up text-warning me-2"></i>Inflasi</span>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $data['economy']['inflation'] }}%</span>
                    </div>
                    <div class="data-row">
                        <span class="text-secondary fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-coins text-indigo me-2" style="color: #4338ca;"></i>Kurs (vs USD)</span>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $data['exchangeRate'] }} {{ $data['currencyCode'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- State Kosong -->
        <div class="text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-satellite-dish text-secondary" style="font-size: 2rem;"></i>
            </div>
            <h6 class="fw-bold text-dark">Belum ada negara yang dipantau</h6>
            <p class="text-muted" style="font-size: 0.85rem;">Tambahkan negara melalui kolom pencarian di kanan atas.</p>
        </div>
    @endif
</div>
@endsection