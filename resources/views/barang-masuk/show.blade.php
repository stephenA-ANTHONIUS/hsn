@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Detail Barang Masuk</h4>
            <p class="text-muted mb-0">Informasi lengkap transaksi penerimaan barang.</p>
        </div>

        <a href="{{ route(auth()->user()->role === 'admin' ? 'admin.barang-masuk.index' : 'kepala-toko.barang-masuk.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <strong>Informasi Transaksi</strong>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <small class="text-muted">Nomor Nota</small>
                    <div class="fw-semibold">{{ $barangMasuk->nomor_nota }}</div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Tanggal Masuk</small>
                    <div class="fw-semibold">
                        {{ $barangMasuk->tanggal_masuk->format('d-m-Y') }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Pemasok</small>
                    <div class="fw-semibold">
                        {{ $barangMasuk->pemasok->nama_pemasok ?? '-' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Dicatat Oleh</small>
                    <div class="fw-semibold">
                        {{ $barangMasuk->pengguna->name ?? '-' }}
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Keterangan</small>
                    <div>{{ $barangMasuk->keterangan ?: '-' }}</div>
                </div>

            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <strong>Daftar Barang Masuk</strong>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>

                            @if(auth()->user()->role === 'kepala_toko')
                                <th>Harga Modal</th>
                            @endif

                            <th>Harga Jual</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($barangMasuk->detail as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->barang->kode_barang ?? '-' }}</td>
                                <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                <td>{{ $detail->barang->kategori->nama_kategori ?? '-' }}</td>
                                <td>{{ number_format($detail->jumlah, 0, ',', '.') }}</td>

                                @if(auth()->user()->role === 'kepala_toko')
                                    <td>
                                        @if($detail->harga_modal !== null)
                                            Rp {{ number_format($detail->harga_modal, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif

                                <td>
                                    Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role === 'kepala_toko' ? 7 : 6 }}"
                                    class="text-center text-muted py-4">
                                    Tidak ada detail barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
