@extends('layout.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Arsip /</span> Tambah Dokumen
        </h4>
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- KOLOM KIRI: INFO UTAMA --}}
            <div class="col-md-8">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Dokumen <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title') }}" placeholder="Contoh: Laporan Keuangan Q1">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="4" placeholder="Berikan keterangan singkat mengenai isi dokumen...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="p-4 bg-light border rounded-3 text-center">
                            <label class="form-label fw-bold d-block mb-3">Upload File Dokumen <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror">
                            <div class="form-text mt-2">Format yang didukung: PDF, DOCX, XLSX (Maks. 10MB)</div>
                            @error('file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: METADATA --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        {{-- NOMOR DOKUMEN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Dokumen</label>
                            <input type="text" name="doc_number" class="form-control @error('doc_number') is-invalid @enderror" 
                                   value="{{ old('doc_number') }}" placeholder="Otomatis jika kosong">
                            @error('doc_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- KATEGORI --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="category_id" id="category_select" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCat">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                            @error('category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- TAGS (CHECKBOX METHOD) --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Tags / Label</label>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalTag">
                                    + Tambah Tag
                                </button>
                            </div>
                            
                            <div class="border rounded p-3 bg-white" style="max-height: 220px; overflow-y: auto; border-style: dashed !important;">
                                <div class="row g-2" id="tag_container">
                                    @forelse($tags as $tag)
                                        <div class="col-6">
                                            <div class="form-check custom-option custom-option-basic">
                                                <input class="form-check-input" type="checkbox" name="tags[]" 
                                                       value="{{ $tag->id }}" id="tag_{{ $tag->id }}"
                                                       {{ (is_array(old('tags')) && in_array($tag->id, old('tags'))) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="tag_{{ $tag->id }}">
                                                    {{ $tag->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-2">
                                            <small class="text-muted italic">Belum ada tag.</small>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Centang satu atau lebih label terkait.</small>
                        </div>

                        {{-- KERAHASIAAN --}}
                      
                        <hr>

                        <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm">
                            <i class="ri-save-line me-1"></i> Simpan Arsip
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- MODAL TAMBAH KATEGORI --}}
<div class="modal fade" id="modalCat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="new_cat_name" class="form-control" placeholder="Nama Kategori...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addCategory()">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH TAG --}}
<div class="modal fade" id="modalTag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tag Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="new_tag_name" class="form-control" placeholder="Nama Tag/Label...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addTag()">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // AJAX Tambah Kategori
    function addCategory() {
        let name = $('#new_cat_name').val();
        if(!name) return alert('Nama kategori wajib diisi');

        $.post("{{ route('categories.store_ajax') }}", { 
            _token: "{{ csrf_token() }}", 
            name: name 
        }, function(data) {
            $('#category_select').append(new Option(data.name, data.id, true, true));
            $('#modalCat').modal('hide');
            $('#new_cat_name').val('');
        }).fail(() => alert('Gagal menambah kategori'));
    }

    // AJAX Tambah Tag (Langsung jadi Checkbox Tercentang)
    function addTag() {
        let name = $('#new_tag_name').val();
        if(!name) return alert('Nama tag wajib diisi');

        $.post("{{ route('tags.store_ajax') }}", { 
            _token: "{{ csrf_token() }}", 
            name: name 
        }, function(data) {
            let html = `
                <div class="col-6">
                    <div class="form-check custom-option custom-option-basic">
                        <input class="form-check-input" type="checkbox" name="tags[]" 
                               value="${data.id}" id="tag_${data.id}" checked>
                        <label class="form-check-label small" for="tag_${data.id}">
                            ${data.name}
                        </label>
                    </div>
                </div>
            `;
            
            // Hapus pesan "Belum ada tag" jika ada
            if($('#tag_container').find('.text-muted').length > 0) {
                $('#tag_container').empty();
            }

            $('#tag_container').append(html);
            $('#modalTag').modal('hide');
            $('#new_tag_name').val('');
        }).fail(() => alert('Gagal menambah tag'));
    }
</script>
@endpush