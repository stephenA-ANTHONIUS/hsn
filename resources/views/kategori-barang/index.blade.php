@extends('layouts.app')

@section('title', 'Kategori Barang')

@section('content')
@php
    $routePrefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Kategori Barang</h4>
            <p class="text-muted mb-0">
                Kelola kategori untuk mengelompokkan barang dan sparepart.
            </p>
        </div>

        <a href="{{ route($routePrefix . '.kategori-barang.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Kategori
        </a>
    </div>

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

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form action="{{ route($routePrefix . '.kategori-barang.index') }}"
                  method="GET"
                  class="row g-2 mb-4">

                <div class="col-md-5">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nama kategori..."
                           value="{{ $search }}">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>
                        Cari
                    </button>

                    <a href="{{ route($routePrefix . '.kategori-barang.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No.</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th width="130" class="text-center">Jumlah Barang</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($kategori as $index => $item)
                            <tr>
                                <td>{{ $kategori->firstItem() + $index }}</td>

                                <td class="fw-semibold">
                                    {{ $item->nama_kategori }}
                                </td>

                                <td>
                                    {{ $item->deskripsi ?: '-' }}
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $item->barang_count }} barang
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="{{ route($routePrefix . '.kategori-barang.edit', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ubah kategori">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="{{ route($routePrefix . '.kategori-barang.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Hapus kategori">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-folder2-open fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada kategori barang.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">
                    Menampilkan {{ $kategori->firstItem() ?? 0 }}
                    sampai {{ $kategori->lastItem() ?? 0 }}
                    dari {{ $kategori->total() }} kategori
                </small>

                {{ $kategori->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
