@extends('layouts.app')

@section('title', 'Transaksi Penjualan')

@section('content')
@php
    $prefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Transaksi Penjualan</h4>
            <p class="text-muted mb-0">
                Daftar transaksi penjualan barang dan sparepart.
            </p>
        </div>

        <a href="{{ route($prefix . '.penjualan.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Penjualan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route($prefix . '.penjualan.index') }}"
                  class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Cari transaksi</label>
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Nomor nota atau nama pelanggan">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal penjualan</label>
                    <input type="date"
                           name="tanggal"
                           class="form-control"
                           value="{{ request('tanggal') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status pembayaran</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="belum_dibayar"
                            @selected(request('status') === 'belum_dibayar')>
                            Belum Dibayar
                        </option>
                        <option value="sebagian"
                            @selected(request('status') === 'sebagian')>
                            Sebagian
                        </option>
                        <option value="lunas"
                            @selected(request('status') === 'lunas')>
                            Lunas
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-success flex-fill">
                        Cari
                    </button>
                    <a href="{{ route($prefix . '.penjualan.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
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
                            <th>Nomor Nota</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Jumlah Jenis Barang</th>
                            <th>Total Penjualan</th>
                            <th>Status Pembayaran</th>
                            <th>Petugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($penjualan as $index => $item)
                            <tr>
                                <td>
                                    {{ $penjualan->firstItem() + $index }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->nomor_nota }}
                                </td>

                                <td>
                                    {{ $item->tanggal_penjualan->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $item->pelanggan->nama_pelanggan ?? 'Pelanggan Umum' }}
                                </td>

                                <td>
                                    {{ $item->detail_penjualan_count }} jenis barang
                                </td>

                                <td>
                                    Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}
                                </td>

                                <td>
                                    @if($item->status_pembayaran === 'lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($item->status_pembayaran === 'sebagian')
                                        <span class="badge bg-warning text-dark">Sebagian</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Dibayar</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $item->pengguna->name ?? '-' }}
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-1">

                                        @if($item->tanggal_penjualan->isToday())
                                            <a href="{{ route($prefix . '.penjualan.edit', $item->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square"></i>
                                                Edit
                                            </a>
                                        @endif

                                        <a href="{{ route($prefix . '.penjualan.riwayat', $item->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-clock-history"></i>
                                            Riwayat
                                        </a>

                                        <a href="{{ route($prefix . '.penjualan.nota', $item->id) }}"
                                        class="btn btn-sm btn-outline-success"
                                        target="_blank">
                                            <i class="bi bi-printer"></i>
                                            Nota
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9"
                                    class="text-center text-muted py-4">
                                    Belum ada transaksi penjualan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $penjualan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
