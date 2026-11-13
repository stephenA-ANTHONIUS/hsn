@extends('layouts.app')

@section('title', 'Tambah Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';

    $barangHadiah = old('barang_hadiah', [
        ['barang_id' => '', 'jumlah_hadiah' => 1]
    ]);
@endphp

<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route($prefix . '.hadiah.index') }}"
           class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Data Hadiah
        </a>

        <h4 class="fw-bold mt-2 mb-1">Tambah Hadiah</h4>
        <p class="text-muted mb-0">
            Masukkan informasi hadiah dan tentukan produk yang berhak mendapatkannya.
        </p>
    </div>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data yang Anda masukkan.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route($prefix . '.hadiah.store') }}"
          method="POST">
        @csrf

        {{-- Informasi hadiah --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-gift me-2"></i>
                    Informasi Hadiah
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Kode Hadiah <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="kode_hadiah"
                               class="form-control"
                               value="{{ old('kode_hadiah') }}"
                               placeholder="Contoh: HD001"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Nama Hadiah <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nama_hadiah"
                               class="form-control"
                               value="{{ old('nama_hadiah') }}"
                               placeholder="Contoh: T-shirt HSN"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Stok Minimum <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               name="stok_minimum"
                               class="form-control"
                               value="{{ old('stok_minimum', 0) }}"
                               min="0"
                               required>
                        <small class="text-muted">
                            Batas minimum stok sebagai pengingat persediaan hadiah.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="form-select" required>
                            <option value="aktif"
                                {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="nonaktif"
                                {{ old('status') == 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Keterangan tambahan mengenai hadiah...">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Stok awal hadiah dibuat 0. Penambahan stok akan dilakukan melalui transaksi stok hadiah.
                </div>
            </div>
        </div>

        {{-- Produk pemicu hadiah --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold mb-1">
                            <i class="bi bi-box-seam me-2"></i>
                            Produk Pemicu Hadiah
                        </h6>
                        <p class="text-muted small mb-0">
                            Pilih produk yang memberikan hak kepada pelanggan untuk memperoleh hadiah ini.
                        </p>
                    </div>

                    <button type="button"
                            class="btn btn-outline-success btn-sm"
                            id="tambahProduk">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Produk
                    </button>
                </div>

                <div class="alert alert-warning small">
                    Jumlah hadiah dihitung untuk setiap satu unit produk yang dibeli.
                    Jika pelanggan membeli beberapa unit, jumlah hadiah akan mengikuti kuantitas pembelian.
                </div>

                <div id="daftarProduk" data-next-index="{{ count($barangHadiah) }}">
                    @foreach ($barangHadiah as $index => $item)
                        <div class="row g-2 align-items-end mb-3 baris-produk">
                            <div class="col-md-7">
                                <label class="form-label">
                                    Produk
                                    @if ($index === 0)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                <select name="barang_hadiah[{{ $index }}][barang_id]"
                                        class="form-select">
                                    <option value="">Pilih Produk</option>

                                    @foreach ($barang as $produk)
                                        <option value="{{ $produk->id }}"
                                            {{ (string) ($item['barang_id'] ?? '') === (string) $produk->id ? 'selected' : '' }}>
                                            {{ $produk->kode_barang }} - {{ $produk->nama_barang }}
                                            @if ($produk->status !== 'aktif')
                                                (Nonaktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Jumlah Hadiah</label>
                                <input type="number"
                                       name="barang_hadiah[{{ $index }}][jumlah_hadiah]"
                                       class="form-control"
                                       value="{{ $item['jumlah_hadiah'] ?? 1 }}"
                                       min="1">
                            </div>

                            <div class="col-md-2">
                                <button type="button"
                                        class="btn btn-outline-danger w-100 hapus-produk">
                                    <i class="bi bi-trash"></i>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <small class="text-muted">
                    Produk boleh memiliki lebih dari satu pilihan hadiah. Satu jenis hadiah juga dapat diberikan untuk beberapa produk.
                </small>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route($prefix . '.hadiah.index') }}"
               class="btn btn-light border">
                Batal
            </a>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i>
                Simpan Hadiah
            </button>
        </div>
    </form>
</div>

{{-- Template baris produk --}}
<template id="templateProduk">
    <div class="row g-2 align-items-end mb-3 baris-produk">
        <div class="col-md-7">
            <label class="form-label">Produk</label>
            <select class="form-select" data-field="barang_id">
                <option value="">Pilih Produk</option>
                @foreach ($barang as $produk)
                    <option value="{{ $produk->id }}">
                        {{ $produk->kode_barang }} - {{ $produk->nama_barang }}
                        @if ($produk->status !== 'aktif')
                            (Nonaktif)
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Jumlah Hadiah</label>
            <input type="number"
                   class="form-control"
                   data-field="jumlah_hadiah"
                   value="1"
                   min="1">
        </div>

        <div class="col-md-2">
            <button type="button"
                    class="btn btn-outline-danger w-100 hapus-produk">
                <i class="bi bi-trash"></i>
                Hapus
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const daftarProduk = document.getElementById('daftarProduk');
    const templateProduk = document.getElementById('templateProduk');
    const tombolTambah = document.getElementById('tambahProduk');

    let nomorBaris = Number(daftarProduk.dataset.nextIndex);

    tombolTambah.addEventListener('click', function () {
        const fragment = templateProduk.content.cloneNode(true);
        const baris = fragment.querySelector('.baris-produk');

        baris.querySelectorAll('[data-field]').forEach(function (field) {
            const namaField = field.dataset.field;
            field.name = `barang_hadiah[${nomorBaris}][${namaField}]`;
        });

        daftarProduk.appendChild(fragment);
        nomorBaris++;
    });

    daftarProduk.addEventListener('click', function (event) {
        const tombolHapus = event.target.closest('.hapus-produk');

        if (!tombolHapus) {
            return;
        }

        const semuaBaris = daftarProduk.querySelectorAll('.baris-produk');

        if (semuaBaris.length <= 1) {
            alert('Minimal harus tersedia satu baris produk.');
            return;
        }

        tombolHapus.closest('.baris-produk').remove();
    });
});
</script>
@endsection
