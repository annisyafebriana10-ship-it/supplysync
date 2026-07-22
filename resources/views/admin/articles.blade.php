@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        
        <div class="d-flex justify-content-between align-items-end mb-4 flex-shrink-0">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--corporate-dark);">Artikel Analisis</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Terbitkan laporan intelijen risiko rantai pasok dan info logistik global.</p>
            </div>
            <div>
                <button type="button" class="btn btn-sm text-white fw-bold px-3 py-2 shadow-sm" style="background-color: var(--matcha-700); border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#addArticleModal">
                    <i class="fa-solid fa-pen-nib me-1"></i> Tulis Artikel Baru
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: var(--matcha-100); color: var(--matcha-700);">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card-corporate p-4 flex-grow-1 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold text-uppercase border-0" style="width: 40%;">Judul Artikel</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Penulis (Author)</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0">Tanggal Terbit</th>
                            <th class="text-secondary small fw-bold text-uppercase border-0 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: 2px solid #e2e8f0;">
                        @forelse($articles as $article)
                        <tr>
                            <td class="fw-semibold py-3" style="color: var(--corporate-dark);">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-3 d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px; color: var(--matcha-700);">
                                        <i class="fa-solid fa-newspaper fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block">{{ $article->title }}</span>
                                        <small class="text-muted fw-normal">{{ Str::limit($article->content, 60) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted py-3">
                                <i class="fa-solid fa-user-pen me-1 text-secondary"></i> {{ $article->author_name }}
                            </td>
                            <td class="text-muted small py-3">
                                {{ \Carbon\Carbon::parse($article->created_at)->format('d M Y, H:i') }}
                            </td>
                            
                            <td class="text-end py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-light text-primary rounded-3" data-bs-toggle="modal" data-bs-target="#editArticleModal{{ $article->id }}" title="Edit Artikel">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="{{ route('admin.articles.delete', $article->id) }}" method="POST" onsubmit="return confirm('Hapus artikel ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-3" title="Hapus Artikel">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="editArticleModal{{ $article->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                    <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
                                        <h5 class="modal-title fw-bold" style="color: var(--corporate-dark);">Edit Artikel</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('admin.articles.update', $article->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 py-3">
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Judul Analisis</label>
                                                <input type="text" name="title" class="form-control bg-light border-0 fw-bold" value="{{ $article->title }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold text-uppercase">Isi Artikel / Laporan</label>
                                                <textarea name="content" class="form-control bg-light border-0" rows="8" required>{{ $article->content }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                                            <button type="button" class="btn text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--matcha-700); border-radius: 8px;">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 mb-3 text-light"></i>
                                <p class="mb-0">Belum ada artikel analisis yang diterbitkan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addArticleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
                    <h5 class="modal-title fw-bold" style="color: var(--corporate-dark);">Tulis Artikel Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.articles.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Judul Analisis</label>
                            <input type="text" name="title" class="form-control form-control-lg bg-light border-0 fw-bold" placeholder="Misal: Dampak Badai di Selat Malaka terhadap Rantai Pasok" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Isi Artikel / Laporan</label>
                            <textarea name="content" class="form-control bg-light border-0" rows="8" placeholder="Tulis rincian analisis di sini..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--matcha-700); border-radius: 8px;">Terbitkan Artikel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection