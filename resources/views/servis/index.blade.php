@extends('layouts.app')

@section('content')
@php
    $statusLabel = [
        'diterima' => 'Diterima',
        'pemeriksaan' => 'Pemeriksaan',
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'proses' => 'Dalam Proses',
        'selesai' => 'Selesai',
        'diambil' => 'Sudah Diambil',
        'dibatalkan' => 'Dibatalkan',
    ];

    $statusClass = [
        'diterima' => 'secondary',
        'pemeriksaan' => 'info',
        'menunggu_persetujuan' => 'warning',
        'proses' => 'primary',
        'selesai' => 'success',
        'diambil' => 'success',
        'dibatalkan' => 'danger',
    ];
@endphp

<style>
    .servis-page { color: #26332D; }
    .servis-page .page-title { color: #172D27; font-weight: 700; }
    .servis-page .panel {
        background: #fff;
        border: 1px solid #DCE2DD;
        border-radius: 12px;
    }
    .servis-page .btn-hsn {
        background: #28483A;
        color: #fff;
        border: 1px solid #28483A;
    }
    .servis-page .btn-hsn:hover {
        background: #172D27;
        color: #fff;
    }
    .servis-page .table thead th {
        background: #F4F5F1;
        color: #526158;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .servis-page .table td { vertical-align: middle; }
    .servis-page .form-control,
    .servis-page .form-select { border-color: #DCE2DD; }
    .servis-page .form-control:focus,
    .servis-page .form-select:focus {
        border-color: #28483A;
        box-shadow: 0 0 0 .2rem rgba(40,72,58,.12);
    }
</style>

<div class="container-fluid servis-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">Transaksi Servis</h4>
            <div class="text-muted small">Kelola penerimaan dan proses perbaikan pelanggan.</div>
        </div>

        <a href="{{ route($routePrefix . '.servis.create') }}" class="btn btn-hsn">
            <i class="bi bi-plus-lg me-1"></i> Tambah Servis
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Data belum dapat diproses:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel p-3 mb-3">
        <form method="GET" action="{{ route($routePrefix . '.servis.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold">Cari transaksi</label>
                    <input type="text" name="cari" class="form-control"
                        value="{{ request('cari') }}"
                        placeholder="Nomor nota, pelanggan, telepon...">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statusLabel as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-semibold">Tanggal dari</label>
                    <input type="date" name="tanggal_dari" class="form-control"
                        value="{{ request('tanggal_dari') }}">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-semibold">Tanggal sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control"
                        value="{{ request('tanggal_sampai') }}">
                </div>

                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-hsn flex-grow-1">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    <a href="{{ route($routePrefix . '.servis.index') }}"
                        class="btn btn-outline-secondary" aria-label="Bersihkan filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="fw-semibold">Daftar transaksi servis</div>
            <span class="small text-muted">{{ $servis->total() }} transaksi</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nomor Nota</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Teknisi</th>
                        <th>Jumlah Sparepart</th>
                        <th>Total Biaya</th>
                        <th>Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servis as $item)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $item->nomor_nota }}</td>
                            <td>{{ $item->tanggal_servis->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $item->pelanggan->nama_pelanggan ?? 'Pelanggan umum' }}
                                </div>
                                <small class="text-muted">
                                    {{ $item->pelanggan->no_telepon ?? '-' }}
                                </small>
                            </td>
                            <td>{{ $item->nama_teknisi ?: '-' }}</td>
                            <td>{{ $item->detail_servis_count }} jenis</td>
                            <td class="fw-semibold">
                                Rp {{ number_format((float) $item->total_biaya, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $statusClass[$item->status_perbaikan] ?? 'secondary' }}">
                                    {{ $statusLabel[$item->status_perbaikan] ?? $item->status_perbaikan }}
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route($routePrefix . '.servis.show', $item->id) }}"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-tools fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold">Belum ada transaksi servis</div>
                                <div class="text-muted small">
                                    Tambahkan transaksi servis untuk mulai mencatat perbaikan pelanggan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($servis->hasPages())
            <div class="p-3 border-top">
                {{ $servis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
