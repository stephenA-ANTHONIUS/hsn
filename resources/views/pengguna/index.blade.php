@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Kelola User</h4>
            <p class="text-muted mb-0">
                Kelola akun Admin HSN Service Perfection.
            </p>
        </div>

        <a href="{{ route('kepala-toko.pengguna.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Admin
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form action="{{ route('kepala-toko.pengguna.index') }}"
                  method="GET"
                  class="row g-2 mb-4">

                <div class="col-md-5">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nama atau email Admin..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-search me-1"></i>
                        Cari
                    </button>

                    <a href="{{ route('kepala-toko.pengguna.index') }}"
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
                            <th>Nama Admin</th>
                            <th>Email</th>
                            <th>Status Akun</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pengguna as $index => $item)
                            <tr>
                                <td>
                                    {{ $pengguna->firstItem() + $index }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->name }}
                                </td>

                                <td>
                                    {{ $item->email }}
                                </td>

                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="{{ route('kepala-toko.pengguna.edit', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit Admin">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @if($item->is_active)
                                            <form action="{{ route('kepala-toko.pengguna.nonaktifkan', $item->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun Admin ini?')">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Nonaktifkan">
                                                    <i class="bi bi-toggle-off"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('kepala-toko.pengguna.aktifkan', $item->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Aktifkan kembali akun Admin ini?')">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Aktifkan">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-people fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada akun Admin.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">
                    Menampilkan {{ $pengguna->firstItem() ?? 0 }}
                    sampai {{ $pengguna->lastItem() ?? 0 }}
                    dari {{ $pengguna->total() }} Admin
                </small>

                {{ $pengguna->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
