@extends('layouts.app')

@section('title', 'Edit Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';

    $dataAwal = $hadiah->barang->map(function ($produk) {
        return [
            'barang_id' => $produk->id,
            'jumlah_hadiah' => $produk->pivot->jumlah_hadiah,
        ];
    })->toArray();

    $barangHadiah = old('barang_hadiah', $dataAwal);

    if (count($barangHadiah) === 0) {
        $barangHadiah = [
            ['barang_id' => '', 'jumlah_hadiah' => 1]
        ];
    }
@endphp

<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route($prefix . '.hadiah.index') }}"
           class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Data Hadiah
        </a>

        <h4 class="fw-bold mt-2 mb-1">Edit Hadiah</h4>
        <p class="text-muted mb-0">
            Perbarui informasi hadiah dan produk yang berhak mendapatkannya.
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

    <form action="{{ route($prefix . '.hadiah.update', $hadiah->id) }}"
          method="POST">
        @csrf
        @method('PUT')

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
                               value="{{ old('kode_hadiah', $hadiah->kode_hadiah) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Nama Hadiah <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nama_hadiah"
                               class="form-control"
                               value="{{ old('nama_hadiah', $hadiah->nama_hadiah) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Stok Saat Ini
                        </label>

                        <input type="text"
                               class="form-control bg-light"
                               value="{{ number_format($hadiah->stok, 0, ',', '.') }}"
                               readonly>

                        <small class="text-muted">
                            Stok hanya dapat berubah melalui transaksi stok hadiah.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Stok Minimum <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="stok_minimum"
                               class="form-control"
                               value="{{ old('stok_minimum', $hadiah->stok_minimum) }}"
                               min="0"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>

                        <select name="status" class="form-select" required>
                            <option value="aktif"
                                {{ old('status', $hadiah->status) == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="nonaktif"
                                {{ old('status', $hadiah->status) == 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>

                        <textarea name="deskripsi"
                                  class="form-control"
                                  rows="3"
                                  style="resize: none;"
                                  placeholder="Keterangan tambahan mengenai hadiah...">{{ old('deskripsi', $hadiah->deskripsi) }}</textarea>
                    </div>

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
                            Atur produk yang memberikan hak kepada pelanggan untuk memperoleh hadiah ini.
                        </p>
                    </div>

                    <button type="button"
                            class="btn btn-outline-success btn-sm"
                            id="tambahProduk">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Produk
                    </button>
                </div>

                <div class="alert alert-info small">
                    Jumlah hadiah berlaku untuk setiap satu unit produk yang dibeli.
                    Perubahan hubungan produk akan menggantikan pengaturan sebelumnya setelah disimpan.
                </div>

                <div id="daftarProduk">
                    @foreach ($barangHadiah as $index => $item)
                        <div class="row g-2 align-items-end mb-3 baris-produk">

                            <div class="col-md-7">
                                <label class="form-label">Produk</label>

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
                    Jika semua produk dihapus dan formulir disimpan, hadiah tidak lagi terhubung dengan produk pemicu.
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
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>

{{-- Template produk tambahan --}}
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

    let nomorBaris = Number('{{ count($barangHadiah) }}');

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

        tombolHapus.closest('.baris-produk').remove();
    });
});
</script>
@endsection
