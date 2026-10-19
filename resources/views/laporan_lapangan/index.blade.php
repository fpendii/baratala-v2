@extends('layout.app')

@section('title', 'Daftar Laporan Lapangan')

@section('content')
    <style>
        .hover-card {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }

        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .icon-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-preview {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .img-preview:hover {
            opacity: 0.8;
        }
    </style>



    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold mb-0">Laporan Lapangan</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger btn-mobile-full shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRekapLaporan">
                <i class="ri-file-pdf-line me-1"></i> Rekap PDF
            </button>
            {{-- <a href="{{ route('laporan-lapangan.create') }}" class="btn btn-primary btn-mobile-full shadow-sm">
                <i class="ri-add-line me-1"></i> Tambah Laporan
            </a> --}}
        </div>
    </div>

    {{-- CARD FILTER --}}
    <div class="card mb-4">
        <h5 class="card-header">Filter Data Laporan</h5>
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-4">
                    <label for="filter_cari" class="form-label">Cari Kegiatan / Lokasi</label>
                    <input type="text" class="form-control" id="filter_cari" name="cari"
                        placeholder="Nama kegiatan atau alamat..." value="{{ request('cari') }}">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="filter_user" class="form-label">Pengguna</label>
                    <select name="user_id" id="filter_user" class="form-select">
                        <option value="">-- Semua Pengguna --</option>
                        @foreach (\App\Models\Pengguna::orderBy('nama')->get() as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-6 col-lg-3">
                    <label for="filter_tanggal_mulai" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="filter_tanggal_mulai" name="tanggal_mulai"
                        value="{{ request('tanggal_mulai') }}">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="filter_tanggal_selesai" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="filter_tanggal_selesai" name="tanggal_selesai"
                        value="{{ request('tanggal_selesai') }}">
                </div>

                <div class="col-12 col-md-auto col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="icon-base ri ri-filter-3-line icon-18px"></i> Filter
                    </button>
                   
                    @if (request()->hasAny(['cari', 'tanggal_mulai', 'tanggal_selesai']))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                            <i class="icon-base ri ri-refresh-line icon-18px"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="card">
        <h5 class="card-header">Riwayat Laporan Lapangan</h5>
        
        <div class="card-body pb-2">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Kegiatan</th>
                            <th style="width: 25%;">Lokasi & Alamat</th>
                            <th style="width: 15%;">Waktu Lapor</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($dataLaporan as $index => $item)
                            <tr>
                                <td>{{ $dataLaporan->firstItem() + $index }}</td>
                                <td>
                                    <strong class="text-primary">{{ $item->nama_kegiatan }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $item->nama }}</small>
                                </td>
                                <td>
                                    <div class="text-dark fw-semibold" style="font-size: 0.85rem;">
                                        <i class="ri-map-pin-2-line text-danger"></i>
                                        {{ Str::limit($item->alamat_lokasi, 45) }}
                                    </div>
                                    <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}"
                                        target="_blank" class="small text-decoration-none">
                                        <i class="ri-external-link-line"></i> Buka Koordinat
                                    </a>
                                </td>
                                <td>
                                    <i class="ri-calendar-line me-1"></i> {{ $item->created_at->format('d M Y') }}
                                    <br>
                                    <small class="text-muted"><i class="ri-time-line"></i>
                                        {{ $item->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none"
                                            data-bs-toggle="dropdown">
                                            <i class="icon-base ri ri-more-2-line icon-18px"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item btn-detail" href="javascript:void(0);"
                                                data-bs-toggle="modal" data-bs-target="#detailLaporanModal"
                                                data-kegiatan="{{ $item->nama_kegiatan }}"
                                                data-alamat="{{ $item->alamat_lokasi }}"
                                                data-waktu="{{ $item->created_at->format('d F Y, H:i') }}"
                                                data-foto="{{ url('/display-laporan/' . basename($item->foto)) }}"
                                                data-map="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}">
                                                <i class="icon-base ri ri-eye-line icon-18px me-1 text-info"></i> Detail
                                            </a>

                                            @if ($item->id_user == Auth::id())
                                                <a class="dropdown-item"
                                                    href="{{ route('laporan-lapangan.edit', $item->id) }}">
                                                    <i class="icon-base ri ri-pencil-line icon-18px me-1 text-warning"></i>
                                                    Edit
                                                </a>
                                                <form action="{{ route('laporan-lapangan.destroy', $item->id) }}"
                                                    method="POST" onsubmit="return confirm('Yakin hapus laporan ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="icon-base ri ri-delete-bin-6-line icon-18px me-1"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="ri-camera-off-line ri-3x d-block mb-2 text-muted"></i>
                                    <p class="mb-0">Belum ada laporan lapangan yang tercatat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINASI --}}
        @if ($dataLaporan->hasPages())
            <div class="card-footer bg-white border-0 d-flex justify-content-end">
                {{ $dataLaporan->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailLaporanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Laporan Lapangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="detailFoto" src="" class="img-fluid rounded shadow-sm border"
                            style="max-height: 250px; width: 100%; object-fit: cover;">
                    </div>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold" style="width: 35%;">Kegiatan</td>
                            <td>: <span id="detailKegiatan"></span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Waktu</td>
                            <td>: <span id="detailWaktu"></span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Alamat</td>
                            <td>: <span id="detailAlamat" class="small"></span></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer border-0">
                    <a id="btnDetailMap" href="" target="_blank" class="btn btn-outline-danger btn-sm">
                        <i class="ri-map-pin-2-fill"></i> Buka di Maps
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REKAP LAPORAN --}}
    <div class="modal fade" id="modalRekapLaporan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm"> {{-- Ukuran kecil agar pas --}}
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold">Opsi Rekap Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('laporan-lapangan.rekap.laporan') }}" method="GET" target="_blank">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Pengguna</label>
                            <select name="user_id" class="form-select">
                                <option value="">Semua Pengguna</option>
                                @foreach (\App\Models\Pengguna::orderBy('nama')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" class="form-control" required value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-uppercase">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="ri-download-cloud-2-line me-1"></i> Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika Detail Laporan
            const detailModal = document.getElementById('detailLaporanModal');
            detailModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                document.getElementById('detailKegiatan').textContent = button.getAttribute(
                'data-kegiatan');
                document.getElementById('detailAlamat').textContent = button.getAttribute('data-alamat');
                document.getElementById('detailWaktu').textContent = button.getAttribute('data-waktu');
                document.getElementById('detailFoto').src = button.getAttribute('data-foto');
                document.getElementById('btnDetailMap').href = button.getAttribute('data-map');
            });
        });
    </script>
@endsection
