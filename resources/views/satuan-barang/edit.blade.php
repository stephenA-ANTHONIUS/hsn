@extends('layouts.app')

@section('title', 'Edit Satuan Barang')

@section('content')
<div class="container-fluid px-0">

    <div class="mb-4">
        <a href="{{ route('kepala-toko.satuan-barang.index') }}"
           class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Satuan Barang
        </a>

        <h4 class="fw-bold mt-3 mb-1">Edit Satuan Barang</h4>
        <p class="text-muted mb-0">
            Perbarui nama atau singkatan satuan barang.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route('kepala-toko.satuan-barang.update', $satuanBarang->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nama_satuan" class="form-label fw-semibold">
                        Nama Satuan <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="nama_satuan"
                           id="nama_satuan"
                           class="form-control @error('nama_satuan') is-invalid @enderror"
                           value="{{ old('nama_satuan', $satuanBarang->nama_satuan) }}"
                           maxlength="50"
                           required>

                    @error('nama_satuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="singkatan" class="form-label fw-semibold">
                        Singkatan <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="singkatan"
                           id="singkatan"
                           class="form-control @error('singkatan') is-invalid @enderror"
                           value="{{ old('singkatan', $satuanBarang->singkatan) }}"
                           maxlength="20"
                           required>

                    @error('singkatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <small class="text-muted">
                        Singkatan digunakan untuk menampilkan jumlah stok barang.
                    </small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kepala-toko.satuan-barang.index') }}"
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
    </div>

</div>
@endsection
