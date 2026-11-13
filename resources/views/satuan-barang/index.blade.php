@extends('layouts.app')

@section('title', 'Satuan Barang')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Satuan Barang</h4>
            <p class="text-muted mb-0">
                Kelola satuan yang digunakan pada barang dan sparepart.
            </p>
        </div>

        <a href="{{ route('kepala-toko.satuan-barang.create') }}"
           class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Satuan
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- Pencarian --}}
            <form action="{{ route('kepala-toko.satuan-barang.index') }}"
                  method="GET"
                  class="row g-2 mb-4">

                <div class="col-md-5">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nama atau singkatan satuan..."
                           value="{{ $search }}">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>
                        Cari
                    </button>

                    <a href="{{ route('kepala-toko.satuan-barang.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No.</th>
                            <th>Nama Satuan</th>
                            <th>Singkatan</th>
                            <th width="140" class="text-center">Jumlah Barang</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($satuan as $index => $item)
                            <tr>
                                <td>
                                    {{ $satuan->firstItem() + $index }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->nama_satuan }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $item->singkatan }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $item->barang_count }} barang
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="{{ route('kepala-toko.satuan-barang.edit', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit satuan">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="{{ route('kepala-toko.satuan-barang.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus satuan ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Hapus satuan">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-rulers fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada satuan barang.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">
                    Menampilkan {{ $satuan->firstItem() ?? 0 }}
                    sampai {{ $satuan->lastItem() ?? 0 }}
                    dari {{ $satuan->total() }} satuan
                </small>

                {{ $satuan->links() }}
            </div>

        </div>
    </div>

</div>
@endsection

