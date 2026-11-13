@extends('layouts.app')

@section('title', 'Edit Admin')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">Edit Admin</h4>
        <p class="text-muted mb-0">
            Perbarui informasi akun Admin.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form action="{{ route('kepala-toko.pengguna.update', $pengguna->id) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Admin</label>

                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $pengguna->name) }}"
                               required>

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>

                        <input type="email"
                               class="form-control"
                               value="{{ $pengguna->email }}"
                               disabled>

                        <small class="text-muted">
                            Email digunakan sebagai identitas login dan tidak dapat diubah di halaman ini.
                        </small>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">Ubah Password</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password Baru</label>

                        <input type="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Kosongkan jika tidak ingin mengubah">

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>

                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Ulangi password baru">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kepala-toko.pengguna.index') }}"
                       class="btn btn-outline-secondary">
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
