@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Data Pelanggan</h4>
            <p class="text-muted mb-0">Kelola informasi pelanggan HSN Service Perfection.</p>
        </div>

        <a href="{{ route('kepala-toko.pelanggan.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pelanggan
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
            <form action="{{ route('kepala-toko.pelanggan.index') }}" method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari nama, nomor telepon, atau alamat..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>

                        <a href="{{ route('kepala-toko.pelanggan.index') }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No.</th>
                            <th>Nama Pelanggan</th>
                            <th>Nomor Telepon</th>
                            <th>Alamat</th>
                            <th>Riwayat Transaksi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pelanggan as $item)
                            <tr>
                                <td>{{ $pelanggan->firstItem() + $loop->index }}</td>

                                <td class="fw-semibold">
                                    {{ $item->nama_pelanggan }}
                                </td>

                                <td>{{ $item->no_telepon ?? '-' }}</td>

                                <td>
                                    {{ $item->alamat ? Str::limit($item->alamat, 50) : '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $item->penjualan_count + $item->servis_count + $item->penyerahan_hadiah_count }}
                                        transaksi
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('kepala-toko.pelanggan.edit', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="{{ route('kepala-toko.pelanggan.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada data pelanggan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Menampilkan {{ $pelanggan->count() }} dari {{ $pelanggan->total() }} pelanggan
                </small>

                {{ $pelanggan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
