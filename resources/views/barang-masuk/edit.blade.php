
@extends('layouts.app')

@section('title', 'Edit Barang Masuk')

@section('content')
@php
    $routePrefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';

    // Jika validasi gagal, tampilkan kembali data yang sebelumnya diinput.
    $detailForm = old(
        'detail',
        $barangMasuk->detail->map(function ($detail) {
            return [
                'barang_id' => $detail->barang_id,
                'jumlah' => $detail->jumlah,
                'harga_modal' => $detail->harga_modal,
                'harga_jual' => $detail->harga_jual,
            ];
        })->toArray()
    );
@endphp

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Barang Masuk</h4>
            <p class="text-muted mb-0">
                Perbarui informasi transaksi barang masuk.
            </p>
        </div>

        <a href="{{ route($routePrefix . '.barang-masuk.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

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

    <form action="{{ route($routePrefix . '.barang-masuk.update', $barangMasuk->id) }}"
          method="POST">
        @csrf
        @method('PUT')

        {{-- Informasi transaksi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">Informasi Transaksi</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nomor_nota" class="form-label">
                            Nomor Nota <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nomor_nota"
                               id="nomor_nota"
                               class="form-control @error('nomor_nota') is-invalid @enderror"
                               value="{{ old('nomor_nota', $barangMasuk->nomor_nota) }}"
                               required>

                        @error('nomor_nota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_masuk" class="form-label">
                            Tanggal Masuk <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_masuk"
                               id="tanggal_masuk"
                               class="form-control @error('tanggal_masuk') is-invalid @enderror"
                               value="{{ old('tanggal_masuk', \Carbon\Carbon::parse($barangMasuk->tanggal_masuk)->format('Y-m-d')) }}"
                               required>

                        @error('tanggal_masuk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="keterangan" class="form-label">
                            Keterangan
                        </label>

                        <textarea name="keterangan"
                                  id="keterangan"
                                  rows="3"
                                  class="form-control @error('keterangan') is-invalid @enderror"
                                  placeholder="Masukkan keterangan jika diperlukan">{{ old('keterangan', $barangMasuk->keterangan) }}</textarea>

                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Detail barang --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Detail Barang</h5>

                <button type="button" class="btn btn-sm btn-success" id="tambahBarang">
                    <i class="bi bi-plus-circle"></i> Tambah Barang
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px;">Nama Barang</th>
                                <th style="min-width: 110px;">Jumlah</th>

                                @if (auth()->user()->role === 'kepala_toko')
                                    <th style="min-width: 150px;">Harga Modal</th>
                                @endif

                                <th style="min-width: 150px;">Harga Jual</th>
                                <th style="width: 70px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="daftarBarang"
                               data-next-index="{{ count($detailForm) }}">

                            @foreach ($detailForm as $index => $detail)
                                <tr>
                                    <td>
                                        <select name="detail[{{ $index }}][barang_id]"
                                                class="form-select"
                                                required>
                                            <option value="">Pilih Barang</option>

                                            @foreach ($barang as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ (string) ($detail['barang_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                                    {{ $item->nama_barang }}
                                                    @if ($item->status !== 'aktif')
                                                        (Nonaktif)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number"
                                               name="detail[{{ $index }}][jumlah]"
                                               class="form-control"
                                               min="1"
                                               value="{{ $detail['jumlah'] ?? 1 }}"
                                               required>
                                    </td>

                                    @if (auth()->user()->role === 'kepala_toko')
                                        <td>
                                            <input type="number"
                                                   name="detail[{{ $index }}][harga_modal]"
                                                   class="form-control"
                                                   min="0"
                                                   step="0.01"
                                                   value="{{ $detail['harga_modal'] ?? '' }}"
                                                   placeholder="Harga modal">
                                        </td>
                                    @endif

                                    <td>
                                        <input type="number"
                                               name="detail[{{ $index }}][harga_jual]"
                                               class="form-control"
                                               min="0"
                                               step="0.01"
                                               value="{{ $detail['harga_jual'] ?? 0 }}"
                                               required>
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger hapusBarang"
                                                title="Hapus barang">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <small class="text-muted">
                    Perubahan jumlah barang akan menyesuaikan stok secara otomatis.
                    Pastikan barang yang diubah sudah diperiksa sebelum menyimpan.
                </small>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route($routePrefix . '.barang-masuk.index') }}"
               class="btn btn-outline-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Simpan Perubahan
            </button>
        </div>

    </form>
</div>

{{-- Template baris barang baru --}}
<template id="templateBarang">
    <tr>
        <td>
            <select name="detail[__INDEX__][barang_id]"
                    class="form-select"
                    required>
                <option value="">Pilih Barang</option>

                @foreach ($barang->where('status', 'aktif') as $item)
                    <option value="{{ $item->id }}">
                        {{ $item->nama_barang }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="number"
                   name="detail[__INDEX__][jumlah]"
                   class="form-control"
                   min="1"
                   value="1"
                   required>
        </td>

        @if (auth()->user()->role === 'kepala_toko')
            <td>
                <input type="number"
                       name="detail[__INDEX__][harga_modal]"
                       class="form-control"
                       min="0"
                       step="0.01"
                       placeholder="Harga modal">
            </td>
        @endif

        <td>
            <input type="number"
                   name="detail[__INDEX__][harga_jual]"
                   class="form-control"
                   min="0"
                   step="0.01"
                   value="0"
                   required>
        </td>

        <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-outline-danger hapusBarang"
                    title="Hapus barang">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const daftarBarang = document.getElementById('daftarBarang');
    const templateBarang = document.getElementById('templateBarang');
    const tombolTambah = document.getElementById('tambahBarang');

    let nomorBaris = Number(daftarBarang.dataset.nextIndex || 0);

    // Menambahkan baris barang baru.
    tombolTambah.addEventListener('click', function () {
        const html = templateBarang.innerHTML.replaceAll(
            '__INDEX__',
            nomorBaris
        );

        daftarBarang.insertAdjacentHTML('beforeend', html);

        nomorBaris++;
    });

    // Menghapus baris barang.
    daftarBarang.addEventListener('click', function (event) {
        const tombolHapus = event.target.closest('.hapusBarang');

        if (!tombolHapus) {
            return;
        }

        const semuaBaris = daftarBarang.querySelectorAll('tr');

        // Minimal harus ada satu baris barang.
        if (semuaBaris.length <= 1) {
            alert('Minimal harus terdapat satu barang dalam transaksi.');
            return;
        }

        tombolHapus.closest('tr').remove();
    });
});
</script>
@endsection
