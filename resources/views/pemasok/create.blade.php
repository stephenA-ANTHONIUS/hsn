@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Tambah Pemasok</h4>
        <p class="text-muted mb-0">Masukkan informasi pemasok baru.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('kepala-toko.pemasok.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Pemasok <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nama_pemasok"
                               class="form-control @error('nama_pemasok') is-invalid @enderror"
                               value="{{ old('nama_pemasok') }}"
                               placeholder="Masukkan nama pemasok"
                               required>
                        @error('nama_pemasok')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text"
                               name="nama_kontak"
                               class="form-control @error('nama_kontak') is-invalid @enderror"
                               value="{{ old('nama_kontak') }}"
                               placeholder="Nama orang yang dapat dihubungi">
                        @error('nama_kontak')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text"
                               name="no_telepon"
                               class="form-control @error('no_telepon') is-invalid @enderror"
                               value="{{ old('no_telepon') }}"
                               placeholder="Contoh: 081234567890">
                        @error('no_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="Contoh: pemasok@email.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat"
                                  rows="3"
                                  class="form-control @error('alamat') is-invalid @enderror"
                                  placeholder="Masukkan alamat pemasok">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kepala-toko.pemasok.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Simpan Pemasok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
