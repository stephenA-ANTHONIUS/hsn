@extends('layouts.app')

@section('title', 'Riwayat Perubahan Penjualan')

@section('content')
@php
    $prefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Perubahan Penjualan</h4>
            <p class="text-muted mb-0">
                Riwayat perubahan transaksi {{ $penjualan->nomor_nota }}.
            </p>
        </div>

        <a href="{{ route($prefix . '.penjualan.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted">Nomor Nota</small>
                    <div class="fw-semibold">{{ $penjualan->nomor_nota }}</div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Tanggal Penjualan</small>
                    <div class="fw-semibold">
                        {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Total Saat Ini</small>
                    <div class="fw-bold text-success">
                        Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @forelse($riwayat as $item)
        @php
            $sebelum = $item->data_sebelum;
            $sesudah = $item->data_sesudah;
        @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>
                            Perubahan Transaksi
                        </span>
                        <div class="small text-muted mt-1">
                            Oleh: {{ $item->pengguna->name ?? '-' }}
                        </div>
                    </div>

                    <div class="text-end small text-muted">
                        {{ $item->created_at->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted mb-3">
                    {{ $item->keterangan ?? 'Perubahan data transaksi penjualan.' }}
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Sebelum Diedit</th>
                                <th>Sesudah Diedit</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Pelanggan</td>
<td>{{ $sebelum['nama_pelanggan'] ?? 'Pelanggan Umum' }}</td>
<td>{{ $sesudah['nama_pelanggan'] ?? 'Pelanggan Umum' }}</td>
                            </tr>

                            <tr>
                                <td>Total Penjualan</td>
                                <td>
                                    Rp {{ number_format($sebelum['total_penjualan'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    Rp {{ number_format($sesudah['total_penjualan'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td>Metode Pembayaran</td>
                                <td>{{ $sebelum['metode_pembayaran'] ?? '-' }}</td>
                                <td>{{ $sesudah['metode_pembayaran'] ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td>Status Pembayaran</td>
                                <td>{{ $sebelum['status_pembayaran'] ?? '-' }}</td>
                                <td>{{ $sesudah['status_pembayaran'] ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td>Keterangan</td>
                                <td>{{ $sebelum['keterangan'] ?? '-' }}</td>
                                <td>{{ $sesudah['keterangan'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mt-4 mb-3">Detail Barang Sebelum Diedit</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sebelum['detail'] ?? [] as $detail)
                                <tr>
                                    <td>{{ $detail['nama_barang'] }}</td>
                                    <td>{{ $detail['jumlah'] }}</td>
                                    <td>
                                        Rp {{ number_format($detail['harga_jual'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        Rp {{ number_format($detail['subtotal'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Tidak ada detail barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mt-4 mb-3">Detail Barang Sesudah Diedit</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sesudah['detail'] ?? [] as $detail)
                                <tr>
                                    <td>{{ $detail['nama_barang'] }}</td>
                                    <td>{{ $detail['jumlah'] }}</td>
                                    <td>
                                        Rp {{ number_format($detail['harga_jual'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        Rp {{ number_format($detail['subtotal'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Tidak ada detail barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-clock-history fs-1 text-muted"></i>
                <h6 class="fw-bold mt-3">Belum Ada Riwayat Perubahan</h6>
                <p class="text-muted mb-0">
                    Transaksi ini belum pernah diedit.
                </p>
            </div>
        </div>
    @endforelse

    <div class="mt-3">
        {{ $riwayat->links() }}
    </div>
</div>
@endsection
