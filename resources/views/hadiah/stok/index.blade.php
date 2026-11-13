@extends('layouts.app')

@section('title', 'Riwayat Stok Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold">Riwayat Stok Hadiah</h4>
            <p class="text-muted mb-0">
                Catatan seluruh perubahan stok hadiah.
            </p>
        </div>

        <a href="{{ route($prefix . '.hadiah.stok.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>
            Tambah Transaksi
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route($prefix . '.hadiah.stok.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Cari Hadiah</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Kode atau nama hadiah"
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Jenis Transaksi</label>
                        <select name="jenis_transaksi" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="masuk" @selected(request('jenis_transaksi') === 'masuk')>
                                Masuk
                            </option>
                            <option value="keluar" @selected(request('jenis_transaksi') === 'keluar')>
                                Keluar
                            </option>
                            <option value="penyesuaian" @selected(request('jenis_transaksi') === 'penyesuaian')>
                                Penyesuaian
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date"
                               name="tanggal_mulai"
                               class="form-control"
                               value="{{ request('tanggal_mulai') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date"
                               name="tanggal_selesai"
                               class="form-control"
                               value="{{ request('tanggal_selesai') }}">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="bi bi-search"></i>
                            Cari
                        </button>

                        <a href="{{ route($prefix . '.hadiah.stok.index') }}"
                           class="btn btn-outline-secondary"
                           title="Reset filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Kode Hadiah</th>
                            <th>Nama Hadiah</th>
                            <th>Jenis Transaksi</th>
                            <th>Jumlah</th>
                            <th>Dicatat Oleh</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($transaksi as $item)
                            <tr>
                                <td>
                                    {{ $transaksi->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    {{ $item->tanggal_transaksi->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $item->hadiah->kode_hadiah ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->hadiah->nama_hadiah ?? '-' }}
                                </td>

                                <td>
                                    @if ($item->jenis_transaksi === 'masuk')
                                        <span class="badge bg-success">Masuk</span>
                                    @elseif ($item->jenis_transaksi === 'keluar')
                                        <span class="badge bg-danger">Keluar</span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            Penyesuaian
                                        </span>
                                    @endif
                                </td>

                                <td class="fw-semibold">
                                    {{ number_format($item->jumlah, 0, ',', '.') }}
                                </td>

                                <td>
                                    {{ $item->pengguna->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Belum ada transaksi stok hadiah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

<div class="d-flex justify-content-end mt-3">
    {{ $transaksi->links('pagination::bootstrap-5') }}
</div>
        </div>
    </div>
</div>
@endsection
