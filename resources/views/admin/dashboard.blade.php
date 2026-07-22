@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        
        <div class="d-flex justify-content-between align-items-end mb-4 flex-shrink-0">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--corporate-dark);">Admin Control Panel</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola master data, pengguna, dan artikel analisis secara terpusat.</p>
            </div>
            <div>
                <a href="/" class="btn btn-sm btn-outline-secondary fw-bold px-3 py-2" style="border-radius: 8px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard Utama
                </a>
            </div>
        </div>

        <div class="row g-4 flex-shrink-0">
            
            <div class="col-md-4">
                <div class="card-corporate p-4 h-100 border-start border-4" style="border-color: var(--corporate-dark) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fs-1" style="color: var(--corporate-dark);">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <h1 class="fw-bold mb-0" style="color: var(--corporate-dark);">{{ $totalUsers }}</h1>
                    </div>
                    <h6 class="fw-bold text-dark">Manajemen Pengguna</h6>
                    <p class="text-muted small mb-4">Atur hak akses admin dan pantau aktivitas pengguna platform.</p>
                    <a href="{{ route('admin.users') }}" class="btn w-100 fw-bold text-white py-2" style="background-color: var(--corporate-dark); border-radius: 8px; font-size: 0.85rem;">
                        <i class="fa-solid fa-gear me-1"></i> Kelola User
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-corporate p-4 h-100 border-start border-4" style="border-color: var(--matcha-500) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fs-1" style="color: var(--matcha-500);">
                            <i class="fa-solid fa-anchor"></i>
                        </div>
                        <h1 class="fw-bold mb-0" style="color: var(--corporate-dark);">{{ $totalPorts }}</h1>
                    </div>
                    <h6 class="fw-bold text-dark">Dataset Pelabuhan</h6>
                    <p class="text-muted small mb-4">Kelola data dan titik koordinat World Port Index.</p>
                    <a href="{{ route('admin.ports') }}" class="btn w-100 fw-bold text-white py-2" style="background-color: var(--matcha-500); border-radius: 8px; font-size: 0.85rem;">
                        <i class="fa-solid fa-map-location-dot me-1"></i> Kelola Pelabuhan
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-corporate p-4 h-100 border-start border-4" style="border-color: var(--matcha-700) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fs-1" style="color: var(--matcha-700);">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <h1 class="fw-bold mb-0" style="color: var(--corporate-dark);">{{ $totalArticles }}</h1>
                    </div>
                    <h6 class="fw-bold text-dark">Artikel Analisis</h6>
                    <p class="text-muted small mb-4">Tulis dan terbitkan laporan analisis intelijen risiko.</p>
                    <a href="{{ route('admin.articles') }}" class="btn w-100 fw-bold text-white py-2" style="background-color: var(--matcha-700); border-radius: 8px; font-size: 0.85rem;">
                        <i class="fa-solid fa-pen-nib me-1"></i> Kelola Artikel
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection