@extends('layouts.app')

@section('title', 'Data Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Data Hadiah</h4>
            <p class="text-muted mb-0">
                Kelola data hadiah dan produk yang berhak mendapatkannya.
            </p>
        </div>

        <a href="{{ route($prefix . '.hadiah.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Hadiah
        </a>
    </div>

    {{-- Pesan berhasil --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pesan validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter pencarian --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route($prefix . '.hadiah.index') }}"
                  method="GET"
                  class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Cari Hadiah</label>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari kode atau nama hadiah..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>
                            Aktif
                        </option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>
                        Cari
                    </button>

                    <a href="{{ route($prefix . '.hadiah.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Keterangan indikator stok --}}
    <div class="d-flex flex-wrap gap-3 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success">Stok Aman</span>
            <small class="text-muted">Di atas stok minimum</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning-subtle text-warning-emphasis">Mencapai Minimum</span>
            <small class="text-muted">Sama dengan stok minimum</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger-subtle text-danger">Di Bawah Minimum</span>
            <small class="text-muted">Di bawah stok minimum</small>
        </div>
    </div>

    {{-- Tabel data hadiah --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Daftar Hadiah</h6>
                <span class="text-muted small">
                    Total: {{ $hadiah->total() }} data
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No.</th>
                            <th>Kode Hadiah</th>
                            <th>Nama Hadiah</th>
                            <th>Stok</th>
                            <th>Stok Minimum</th>
                            <th>Kondisi Stok</th>
                            <th>Produk Pemicu</th>
                            <th>Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($hadiah as $index => $item)
                            <tr>
                                <td>
                                    {{ $hadiah->firstItem() + $index }}
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        {{ $item->kode_hadiah }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->nama_hadiah }}
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        {{ number_format($item->stok, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    {{ number_format($item->stok_minimum, 0, ',', '.') }}
                                </td>

                                <td>
                                    @if ($item->stok < $item->stok_minimum)
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Di Bawah Minimum
                                        </span>
                                    @elseif ($item->stok == $item->stok_minimum)
                                        <span class="badge bg-warning-subtle text-warning-emphasis">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Mencapai Minimum
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Stok Aman
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $item->barang_count }} produk
                                </td>

                                <td>
                                    @if ($item->status === 'aktif')
                                        <span class="badge bg-success-subtle text-success">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route($prefix . '.hadiah.edit', $item->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit Hadiah">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-gift fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada data hadiah.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $hadiah->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
