@extends('layouts.app')

@section('title', 'Tambah Admin')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">Tambah Admin</h4>
        <p class="text-muted mb-0">
            Tambahkan akun pengguna baru untuk Admin HSN.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form action="{{ route('kepala-toko.pengguna.store') }}"
                  method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Nama Admin <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama Admin"
                               required>

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>

                        <input type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="Masukkan email Admin"
                               required>

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Password <span class="text-danger">*</span>
                        </label>

                        <input type="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimal 8 karakter"
                               required>

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>

                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Ulangi password"
                               required>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Akun Admin yang dibuat akan langsung memiliki status aktif.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kepala-toko.pengguna.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>
                        Simpan Admin
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
