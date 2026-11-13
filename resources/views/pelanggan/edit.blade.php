@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Edit Pelanggan</h4>
        <p class="text-muted mb-0">Perbarui informasi pelanggan.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('kepala-toko.pelanggan.update', $pelanggan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Nama Pelanggan <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nama_pelanggan"
                               class="form-control @error('nama_pelanggan') is-invalid @enderror"
                               value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}"
                               required>

                        @error('nama_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor Telepon</label>

                        <input type="text"
                               name="no_telepon"
                               class="form-control @error('no_telepon') is-invalid @enderror"
                               value="{{ old('no_telepon', $pelanggan->no_telepon) }}">

                        @error('no_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>

                        <textarea name="alamat"
                                  rows="3"
                                  class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $pelanggan->alamat) }}</textarea>

                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kepala-toko.pelanggan.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
