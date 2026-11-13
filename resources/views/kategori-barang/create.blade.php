@extends('layouts.app')

@section('title', 'Tambah Kategori Barang')

@section('content')
@php
    $routePrefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="mb-4">
        <a href="{{ route($routePrefix . '.kategori-barang.index') }}"
           class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Kategori Barang
        </a>

        <h4 class="fw-bold mt-3 mb-1">Tambah Kategori Barang</h4>
        <p class="text-muted mb-0">
            Masukkan informasi kategori barang yang akan ditambahkan.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route($routePrefix . '.kategori-barang.store') }}"
                  method="POST">

                @csrf

                <div class="mb-3">
                    <label for="nama_kategori" class="form-label fw-semibold">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="nama_kategori"
                           id="nama_kategori"
                           class="form-control @error('nama_kategori') is-invalid @enderror"
                           value="{{ old('nama_kategori') }}"
                           placeholder="Contoh: Sparepart, Aksesoris, Peralatan"
                           maxlength="100"
                           required>

                    @error('nama_kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="deskripsi" class="form-label fw-semibold">
                        Deskripsi
                    </label>

                    <textarea name="deskripsi"
                              id="deskripsi"
                              rows="4"
                              class="form-control @error('deskripsi') is-invalid @enderror"
                              placeholder="Masukkan keterangan kategori jika diperlukan">{{ old('deskripsi') }}</textarea>

                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route($routePrefix . '.kategori-barang.index') }}"
                       class="btn btn-light border">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>
                        Simpan Kategori
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
