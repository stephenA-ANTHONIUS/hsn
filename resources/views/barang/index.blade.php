@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Data Barang</h4>
            <p class="text-muted mb-0">Kelola data barang dan sparepart HSN.</p>
        </div>

      
<div class="d-flex gap-2 flex-wrap">

    <a href="{{ route($isKepalaToko ? 'kepala-toko.barang.riwayat' : 'admin.barang.riwayat') }}"
       class="btn btn-outline-success">
        <i class="bi bi-clock-history me-1"></i>
        Riwayat Perubahan
    </a>

    <a href="{{ route($isKepalaToko ? 'kepala-toko.barang.create' : 'admin.barang.create') }}"
       class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>
        Tambah Barang
    </a>

</div>

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

            <form action="{{ route($isKepalaToko ? 'kepala-toko.barang.index' : 'admin.barang.index') }}"
                  method="GET"
                  class="mb-3">

                <div class="row g-2">

                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari kode atau nama barang..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <select name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>

                            @foreach($kategori as $item)
                                <option value="{{ $item->id }}"
                                    {{ request('kategori') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>

                        <a href="{{ route($isKepalaToko ? 'kepala-toko.barang.index' : 'admin.barang.index') }}"
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
                            <th>No.</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>

                            @if($isKepalaToko)
                                <th>Harga Modal</th>
                            @endif

                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Status Stok</th>
                            <th>Status Barang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($barang as $item)
                            <tr>
                                <td>{{ $barang->firstItem() + $loop->index }}</td>
                                <td>{{ $item->kode_barang }}</td>
                                <td class="fw-semibold">{{ $item->nama_barang }}</td>
                                <td>{{ $item->kategori->nama_kategori }}</td>
                                <td>{{ $item->satuan->nama_satuan }}</td>

                                @if($isKepalaToko)
                                    <td>
                                        Rp {{ number_format($item->harga_modal, 0, ',', '.') }}
                                    </td>
                                @endif

                                <td>
                                    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                </td>

                                <td>
                                    {{ $item->stok }}
                                    {{ $item->satuan->singkatan }}
                                </td>

                                <td>
                                    @if($item->stok <= $item->stok_minimum)
                                        <span class="badge bg-danger">Stok Menipis</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                </td>

                                <td>
                                    @if($item->status === 'aktif')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-1">

                                        <a href="{{ route($isKepalaToko ? 'kepala-toko.barang.edit' : 'admin.barang.edit', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @if($item->status === 'aktif')
                                            <form action="{{ route($isKepalaToko ? 'kepala-toko.barang.nonaktifkan' : 'admin.barang.nonaktifkan', $item->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan barang ini?')">

                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Nonaktifkan">
                                                    <i class="bi bi-toggle-off"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route($isKepalaToko ? 'kepala-toko.barang.aktifkan' : 'admin.barang.aktifkan', $item->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Aktifkan kembali barang ini?')">

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
                                <td colspan="{{ $isKepalaToko ? 11 : 10 }}"
                                    class="text-center py-4 text-muted">
                                    Belum ada data barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Menampilkan {{ $barang->count() }} dari {{ $barang->total() }} barang
                </small>

                {{ $barang->links() }}
            </div>

        </div>
    </div>

</div>

@endsection
