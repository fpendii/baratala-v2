@extends('layout.app')

@section('title', 'Edit Laporan Lapangan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Laporan Lapangan /</span> Edit Data
    </h4>
    <a href="{{ route('laporan-lapangan.index') }}" class="btn btn-outline-secondary">
        <i class="icon-base ri ri-arrow-left-line icon-18px me-1"></i> Kembali
    </a>
</div>

{{-- ALERT VALIDASI --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Terjadi Kesalahan!</strong> Mohon periksa input Anda.
    <ul class="mt-2 mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <h5 class="card-header">Formulir Edit Laporan Lapangan</h5>
    <div class="card-body">
        <form action="{{ route('laporan-lapangan.update', $laporan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama Kegiatan --}}
            <div class="mb-3">
                <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                <input type="text" name="nama_kegiatan" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                       value="{{ old('nama_kegiatan', $laporan->nama_kegiatan) }}" required>
                @error('nama_kegiatan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Lokasi GPS --}}
            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="lat" class="form-control bg-light @error('latitude') is-invalid @enderror"
                           value="{{ old('latitude', $laporan->latitude) }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="lng" class="form-control bg-light @error('longitude') is-invalid @enderror"
                           value="{{ old('longitude', $laporan->longitude) }}" readonly>
                </div>
                {{-- <div class="col-12 mt-2">
                    <button type="button" onclick="getLocation()" class="btn btn-sm btn-outline-danger">
                        <i class="ri-map-pin-user-line me-1"></i> Perbarui Lokasi Sekarang
                    </button>
                </div> --}}
            </div>

            {{-- Alamat/Keterangan Lokasi --}}
            <div class="mb-3">
                <label class="form-label">Alamat Lengkap / Keterangan Lokasi <span class="text-danger">*</span></label>

                @error('alamat_lokasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Foto Kegiatan --}}
            <div class="mb-4">
                <label class="form-label">Foto Kegiatan</label>
                <div class="d-flex align-items-start gap-3 mb-2">
                    @if ($laporan->foto == null)
                        <p>Tidak ada foto</p>
                    @else
                    <img id="imgPreview" src="{{ asset('storage/' . $laporan->foto) }}"
                         class="rounded border shadow-sm" style="object-fit: cover;">
                    @endif
                </div>
                @error('foto')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="pt-3 border-top">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="ri ri-save-line me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('laporan-lapangan.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Preview Gambar saat Upload
    const fotoInput = document.getElementById('fotoInput');
    const imgPreview = document.getElementById('imgPreview');

    fotoInput.onchange = evt => {
        const [file] = fotoInput.files
        if (file) {
            imgPreview.src = URL.createObjectURL(file)
        }
    }

    // Ambil Lokasi GPS
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('lat').value = position.coords.latitude;
                document.getElementById('lng').value = position.coords.longitude;
                alert('Lokasi GPS berhasil diperbarui sesuai posisi Anda saat ini.');
            }, function(error) {
                let msg = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED: msg = "Izin lokasi ditolak."; break;
                    case error.POSITION_UNAVAILABLE: msg = "Informasi lokasi tidak tersedia."; break;
                    case error.TIMEOUT: msg = "Waktu permintaan habis."; break;
                    default: msg = "Terjadi kesalahan tidak dikenal."; break;
                }
                alert('Gagal mengambil lokasi: ' + msg);
            });
        } else {
            alert('Browser Anda tidak mendukung fitur Geolocation.');
        }
    }
</script>
@endpush
