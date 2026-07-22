@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        
        <div class="d-flex justify-content-between align-items-end mb-4 flex-shrink-0">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--corporate-dark);">Dataset Pelabuhan</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data World Port Index untuk visualisasi peta pengiriman.</p>
            </div>
            <div>
                <button type="button" class="btn btn-sm text-white fw-bold px-3 py-2 shadow-sm" style="background-color: var(--matcha-500); border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#addPortModal">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Pelabuhan
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: var(--matcha-100); color: var(--matcha-700);">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card-corporate p-4 flex-grow-1 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Nama Pelabuhan</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Negara</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Latitude</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Longitude</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: 2px solid #e2e8f0;">
                        @forelse($ports as $port)
                        <tr>
                            <td class="fw-semibold py-3" style="color: var(--corporate-dark);">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 35px; height: 35px; color: var(--matcha-500);">
                                        <i class="fa-solid fa-anchor"></i>
                                    </div>
                                    {{ $port->name }}
                                </div>
                            </td>
                            <td class="text-muted py-3">
                                <i class="fa-solid fa-location-dot me-1 text-secondary"></i> {{ $port->country_name }}
                            </td>
                            <td class="py-3"><span class="badge bg-light text-dark border">{{ $port->lat }}</span></td>
                            <td class="py-3"><span class="badge bg-light text-dark border">{{ $port->lng }}</span></td>
                            
                            <td class="text-end py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-light text-primary rounded-3" data-bs-toggle="modal" data-bs-target="#editPortModal{{ $port->id }}" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="{{ route('admin.ports.delete', $port->id) }}" method="POST" onsubmit="return confirm('Hapus pelabuhan {{ $port->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-3" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="editPortModal{{ $port->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                    <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
                                        <h5 class="modal-title fw-bold" style="color: var(--corporate-dark);">Edit Pelabuhan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('admin.ports.update', $port->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 py-3">
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Nama Pelabuhan</label>
                                                <input type="text" name="name" class="form-control bg-light border-0" value="{{ $port->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Negara Pemilik</label>
                                                <select name="country_id" class="form-select bg-light border-0" required>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" {{ $port->country_id == $country->id ? 'selected' : '' }}>
                                                            {{ $country->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label text-secondary small fw-bold text-uppercase">Latitude</label>
                                                    <input type="number" step="any" name="lat" class="form-control bg-light border-0" value="{{ $port->lat }}" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label text-secondary small fw-bold text-uppercase">Longitude</label>
                                                    <input type="number" step="any" name="lng" class="form-control bg-light border-0" value="{{ $port->lng }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                                            <button type="button" class="btn text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--matcha-500); border-radius: 8px;">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-ship fs-1 mb-3 text-light"></i>
                                <p class="mb-0">Belum ada data pelabuhan yang ditambahkan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPortModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
                    <h5 class="modal-title fw-bold" style="color: var(--corporate-dark);">Tambah Titik Pelabuhan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.ports.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Nama Pelabuhan</label>
                            <input type="text" name="name" class="form-control bg-light border-0" placeholder="Misal: Port of Shanghai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Negara Pemilik</label>
                            <select name="country_id" class="form-select bg-light border-0" required>
                                <option value="" disabled selected>Pilih Negara...</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label text-secondary small fw-bold text-uppercase">Latitude</label>
                                <input type="number" step="any" name="lat" class="form-control bg-light border-0" placeholder="-6.111" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label text-secondary small fw-bold text-uppercase">Longitude</label>
                                <input type="number" step="any" name="lng" class="form-control bg-light border-0" placeholder="106.883" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--matcha-500); border-radius: 8px;">Simpan Pelabuhan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection