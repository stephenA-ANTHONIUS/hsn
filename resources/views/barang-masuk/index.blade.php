@extends('layouts.app')

@section('content')
@php
    $routePrefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';
@endphp

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Barang Masuk</h4>
            <p class="text-muted mb-0">
                Daftar transaksi barang masuk HSN.
            </p>
        </div>

        <a href="{{ route($routePrefix . '.barang-masuk.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Tambah Barang Masuk
        </a>
    </div>

    {{-- Pesan berhasil --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pesan kesalahan --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- Form pencarian --}}
            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nomor nota atau pemasok..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <input type="date"
                           name="tanggal"
                           class="form-control"
                           value="{{ request('tanggal') }}">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-success">
                        Cari
                    </button>

                    <a href="{{ route($routePrefix . '.barang-masuk.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>

            </form>

            {{-- Tabel transaksi --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nomor Nota</th>
                            <th>Tanggal Masuk</th>
                            <th>Pemasok</th>
                            <th>Jumlah Jenis Barang</th>
                            <th>Pengguna</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($barangMasuk as $index => $item)

                            @php
                                $tanggalTransaksi = \Carbon\Carbon::parse(
                                    $item->tanggal_masuk
                                )->toDateString();

                                $tanggalHariIni = now()->toDateString();

                                $bisaDiedit = $tanggalTransaksi >= $tanggalHariIni;
                            @endphp

                            <tr>

                                <td>
                                    {{ $barangMasuk->firstItem() + $index }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->nomor_nota }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d-m-Y') }}
                                </td>

                                <td>
                                    {{ $item->pemasok->nama_pemasok ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->detail_count }} jenis
                                </td>

                                <td>
                                    {{ $item->pengguna->name ?? '-' }}
                                </td>

                                <td>
                                    @if($bisaDiedit)
                                        <span class="badge bg-success">
                                            Dapat Diedit
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Terkunci
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-1">

                                        {{-- Detail selalu bisa dibuka --}}
                                        <a href="{{ route($routePrefix . '.barang-masuk.show', $item->id) }}"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>

                                        {{-- Edit hanya untuk transaksi hari ini --}}
                                        @if($bisaDiedit)
                                            <a href="{{ route($routePrefix . '.barang-masuk.edit', $item->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        @endif

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8"
                                    class="text-center text-muted py-4">
                                    Belum ada transaksi barang masuk.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            {{ $barangMasuk->links() }}

        </div>
    </div>
</div>
@endsection
