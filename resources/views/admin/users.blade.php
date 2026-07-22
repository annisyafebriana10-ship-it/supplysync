@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-end mb-4 flex-shrink-0">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--corporate-dark);">Manajemen Pengguna</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola hak akses, tambah akun, dan pantau pengguna sistem.</p>
            </div>
            <div>
                <!-- Tombol Tambah User -->
                <button type="button" class="btn btn-sm text-white fw-bold px-3 py-2 shadow-sm" style="background-color: var(--matcha-500); border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Pengguna Baru
                </button>
            </div>
        </div>

        <!-- Notifikasi Sukses -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: var(--matcha-100); color: var(--matcha-700);">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Validasi -->
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

        <!-- Area Tabel -->
        <div class="card-corporate p-4 flex-grow-1 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Nama Pengguna</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Alamat Email</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Hak Akses (Role)</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Tanggal Terdaftar</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: 2px solid #e2e8f0;">
                        @foreach($users as $user)
                        <tr>
                            <td class="fw-semibold py-3" style="color: var(--corporate-dark);">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3 text-secondary" style="width: 35px; height: 35px;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="text-muted py-3">{{ $user->email }}</td>
                            <td class="py-3">
                                @if($user->role === 'admin')
                                    <span class="badge" style="background-color: var(--corporate-dark); padding: 0.5em 0.8em;">Administrator</span>
                                @else
                                    <span class="badge" style="background-color: var(--matcha-500); padding: 0.5em 0.8em;">User Biasa</span>
                                @endif
                            </td>
                            <td class="text-muted small py-3">
                                {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y, H:i') }}
                            </td>
                            
                            <!-- KOLOM AKSI (EDIT & HAPUS) -->
                            <td class="text-end py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Tombol Edit (Trigger Modal Edit) -->
                                    <button type="button" class="btn btn-sm btn-light text-primary rounded-3" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit Akun">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    @if($user->email !== 'admin@supplysync.com')
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Apakah kamu yakin ingin menghapus akun {{ $user->name }} secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-3" title="Hapus Akun">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT PENGGUNA (Spesifik per User) -->
                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                    
                                    <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
                                        <h5 class="modal-title fw-bold" style="color: var(--corporate-dark);">Edit Akun: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    
                                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 py-3">
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Nama Lengkap</label>
                                                <input type="text" name="name" class="form-control form-control-lg bg-light border-0" value="{{ $user->name }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Alamat Email</label>
                                                <input type="email" name="email" class="form-control form-control-lg bg-light border-0" value="{{ $user->email }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Hak Akses (Role)</label>
                                                <select name="role" class="form-select form-select-lg bg-light border-0" required>
                                                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User Biasa (Akses Dasbor Saja)</option>
                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh)</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Ganti Password (Opsional)</label>
                                                <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="Kosongkan jika tidak ingin ganti password" minlength="8">
                                            </div>

                                        </div>
                                        
                                        <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                                            <button type="button" class="btn text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--matcha-500); border-radius: 8px;">Simpan Perubahan</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                        <!-- Akhir Modal Edit -->

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>

    <!-- MODAL TAMBAH PENGGUNA BARU -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                
                <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
                    <h5 class="modal-title fw-bold" id="addUserModalLabel" style="color: var(--corporate-dark);">Buat Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0" placeholder="Masukkan nama..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Alamat Email</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light border-0" placeholder="nama@perusahaan.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Hak Akses (Role)</label>
                            <select name="role" class="form-select form-select-lg bg-light border-0" required>
                                <option value="user" selected>User Biasa (Akses Dasbor Saja)</option>
                                <option value="admin">Administrator (Akses Penuh)</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Password Sementara</label>
                            <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="Minimal 8 karakter..." required minlength="8">
                        </div>

                    </div>
                    
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--matcha-500); border-radius: 8px;">Simpan Akun</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection