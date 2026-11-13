@extends('layouts.app')

@section('title', 'Penyerahan Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Penyerahan Hadiah</h4>
            <p class="text-muted mb-0">
                Catat dan lihat riwayat penyerahan hadiah kepada pelanggan.
            </p>
        </div>

        <a href="{{ route($prefix . '.penyerahan-hadiah.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Catat Penyerahan Hadiah
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- Pencarian dan Filter --}}
            <form action="{{ route($prefix . '.penyerahan-hadiah.index') }}"
                  method="GET"
                  class="row g-2 mb-4">

                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nomor nota atau nama pelanggan..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <input type="date"
                           name="tanggal_mulai"
                           class="form-control"
                           value="{{ request('tanggal_mulai') }}"
                           title="Tanggal mulai">
                </div>

                <div class="col-md-3">
                    <input type="date"
                           name="tanggal_akhir"
                           class="form-control"
                           value="{{ request('tanggal_akhir') }}"
                           title="Tanggal akhir">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>
                        Cari
                    </button>

                    <a href="{{ route($prefix . '.penyerahan-hadiah.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>

            {{-- Tabel Penyerahan Hadiah --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No.</th>
                            <th>Tanggal</th>
                            <th>Nomor Nota</th>
                            <th>Pelanggan</th>
                            <th>Hadiah Diserahkan</th>
                            <th>Petugas</th>
                            <th class="text-center">Bukti</th>
                            <th class="text-center" width="190">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($penyerahan as $index => $item)
                            <tr>
                                <td>
                                    {{ $penyerahan->firstItem() + $index }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($item->tanggal_penyerahan)->format('d-m-Y') }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->penjualan?->nomor_nota ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan umum' }}
                                </td>

                                <td>
                                    @foreach ($item->detailPenyerahanHadiah as $detail)
                                        <div class="mb-1">
                                            <span class="fw-semibold">
                                                {{ $detail->hadiah?->nama_hadiah ?? 'Hadiah tidak ditemukan' }}
                                            </span>

                                            <span class="badge bg-light text-dark border">
                                                {{ $detail->jumlah }} buah
                                            </span>
                                        </div>
                                    @endforeach
                                </td>

                                <td>
                                    {{ $item->pengguna?->name ?? '-' }}
                                </td>

                                <td class="text-center">
                                    @if ($item->foto_bukti)
                                        <a href="{{ asset('storage/' . $item->foto_bukti) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-image me-1"></i>
                                            Lihat Foto
                                        </a>
                                    @elseif ($item->keterangan)
                                        <span class="badge bg-light text-dark border">
                                            Ada Keterangan
                                        </span>

                                        <div class="small text-muted mt-1"
                                             title="{{ $item->keterangan }}">
                                            {{ \Illuminate\Support\Str::limit($item->keterangan, 45) }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
    <div class="d-flex flex-column flex-lg-row justify-content-center gap-1">

        <a href="{{ route($prefix . '.penyerahan-hadiah.riwayat', $item->id) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>
            Riwayat
        </a>

        <a href="{{ route($prefix . '.penyerahan-hadiah.edit', $item->id) }}"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil-square me-1"></i>
            Edit
        </a>

    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-gift fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada riwayat penyerahan hadiah.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">
                    Menampilkan {{ $penyerahan->firstItem() ?? 0 }}
                    sampai {{ $penyerahan->lastItem() ?? 0 }}
                    dari {{ $penyerahan->total() }} penyerahan hadiah
                </small>

                {{ $penyerahan->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
