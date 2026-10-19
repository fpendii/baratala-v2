@extends('layout.app')

@section('title', 'Daftar Arsip Dokumen')

@section('content')
    <style>
        /* Desain Card Dokumen */
        .document-card {
            transition: all 0.3s ease-in-out;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background-color: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
            border-color: #696cff;
        }

        .card-title {
            white-space: normal !important;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
            line-height: 1.4;
            display: block;
        }

        /* Class Khusus untuk Menyembunyikan Elemen Pemilik secara default */
        .owner-only {
            display: none !important;
        }

        @media (max-width: 768px) {
            .btn-mobile-full { width: 100%; }
        }
    </style>

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold mb-0">Arsip Dokumen</h4>
        <div class="d-flex gap-2">
            {{-- Tombol Rekap PDF (Tetap Ada) --}}
            <button type="button" class="btn btn-outline-danger btn-mobile-full shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRekap">
                <i class="ri-file-pdf-line me-1"></i> Rekap PDF
            </button>
            <a href="{{ route('documents.create') }}" class="btn btn-primary btn-mobile-full shadow-sm">
                <i class="ri-add-line me-1"></i> Tambah Dokumen
            </a>
        </div>
    </div>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="ri-checkbox-circle-line me-2 fs-5"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- FILTER CARD --}}
    <div class="card border shadow-none mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Cari Judul / Nomor</label>
                    <input type="text" class="form-control" name="search" placeholder="Kata kunci..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Filter Tags</label>
                    <select name="filter_tags[]" id="filter_tags" class="form-select" multiple="multiple">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" {{ is_array(request('filter_tags')) && in_array($tag->id, request('filter_tags')) ? 'selected' : '' }}>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    @if (request()->anyFilled(['search', 'category', 'filter_tags']))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- CARD GRID SECTION --}}
    <div class="row g-4 mb-4">
        @forelse($documents as $item)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card document-card shadow-sm"
                    onclick="showActions('{{ $item->id }}', '{{ addslashes($item->title) }}', '{{ $item->latestVersion->id }}', '{{ $item->user_id }}')"
                    style="cursor: pointer;">

                    <div class="card-body p-4 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-label-primary rounded-pill">{{ $item->category->name }}</span>
                            <i class="ri-more-2-line text-muted fs-4"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2">{{ $item->title }}</h5>
                        <p class="text-muted small mb-3"><i class="ri-hashtag me-1"></i>{{ $item->doc_number ?? 'Tanpa Nomor' }}</p>
                        <div class="card-text text-muted small mb-4">{{ $item->description ?? 'Tidak ada deskripsi.' }}</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($item->tags->take(3) as $tag)
                                <span class="badge bg-label-info tag-badge">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top p-4 d-flex justify-content-between align-items-center">
                        <small class="text-muted">v{{ $item->latestVersion->version_number }} • {{ $item->updated_at->format('d M Y') }}</small>
                        <i class="ri-file-pdf-fill text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">Tidak ada dokumen yang ditemukan.</p>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $documents->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    {{-- MODAL AKSI UTAMA --}}
    <div class="modal fade" id="modalAksi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold text-dark">Opsi Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h6 id="actionDocTitle" class="fw-bold text-primary mb-1 card-title"></h6>
                        <small class="text-muted">Pilih tindakan</small>
                    </div>

                    <div class="d-grid gap-2">
                        <a id="btnDownload" href="#" class="btn btn-outline-primary d-flex align-items-center py-2">
                            <i class="ri-download-cloud-2-line me-3 fs-4"></i> Download Dokumen
                        </a>

                        <button id="btnRev" type="button" class="btn btn-outline-success d-flex align-items-center py-2 owner-only">
                            <i class="ri-upload-2-line me-3 fs-4"></i> Upload Revisi
                        </button>

                        <a id="btnEdit" href="#" class="btn btn-outline-warning d-flex align-items-center py-2 owner-only">
                            <i class="ri-edit-box-line me-3 fs-4"></i> Edit Data
                        </a>

                        <hr class="my-3 owner-only">

                        <form id="formDelete" method="POST" onsubmit="return confirm('Hapus dokumen ini?')" class="owner-only">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger d-flex align-items-center py-2 w-100">
                                <i class="ri-delete-bin-line me-3 fs-4"></i> Hapus Dokumen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REKAP TANGGAL (Tetap Ada) --}}
    <div class="modal fade" id="modalRekap" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Rekap Laporan PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('documents.recap.laporan') }}" method="GET">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-uppercase">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL UPLOAD REVISI --}}
    <div class="modal fade" id="modalRevisi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Upload Revisi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRevisi" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <p class="text-muted mb-1">Merevisi Dokumen:</p>
                        <h6 id="revisiDocTitle" class="fw-bold text-primary mb-3"></h6>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#filter_tags').select2({ placeholder: " Cari Tags...", allowClear: true, width: '100%' });
            });

            function showActions(id, title, versionId, ownerId) {
                $('#actionDocTitle').text(title);
                
                const currentUserId = "{{ auth()->id() }}";

                // Update Link Aksi
                $('#btnDownload').attr('href', `/documents/${versionId}/download`);
                $('#btnEdit').attr('href', `/documents/${id}/edit`);
                $('#formDelete').attr('action', `/documents/${id}`);

                // LOGIKA PROTEKSI KERAS (Iterasi semua elemen .owner-only)
                if (String(currentUserId) === String(ownerId)) {
                    // Jika user adalah pemilik: tampilkan semua tombol aksi pemilik
                    $('.owner-only').each(function() {
                        if ($(this).is('form')) {
                            $(this).attr('style', 'display: block !important');
                        } else if ($(this).hasClass('btn')) {
                            $(this).attr('style', 'display: flex !important');
                        } else {
                            $(this).attr('style', 'display: block !important');
                        }
                    });
                } else {
                    // Jika bukan pemilik: pastikan tersembunyi dengan !important
                    $('.owner-only').attr('style', 'display: none !important');
                }

                // Handler Klik Tombol Revisi
                $('#btnRev').off('click').on('click', function() {
                    bootstrap.Modal.getInstance(document.getElementById('modalAksi')).hide();
                    setTimeout(function() {
                        $('#revisiDocTitle').text(title);
                        $('#formRevisi').attr('action', `/documents/${id}/revision`);
                        new bootstrap.Modal(document.getElementById('modalRevisi')).show();
                    }, 400);
                });

                new bootstrap.Modal(document.getElementById('modalAksi')).show();
            }
        </script>
    @endpush
@endsection