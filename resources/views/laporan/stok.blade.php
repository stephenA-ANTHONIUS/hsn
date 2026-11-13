@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">Laporan Stok</h4>
            <p class="text-muted mb-0">
                Informasi ketersediaan barang, sparepart, dan hadiah.
            </p>
        </div>

        <a href="{{ route($prefix . '.laporan.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Kondisi Persediaan Terkini</h5>
            <small class="text-muted">
                Dicetak pada {{ now()->format('d-m-Y H:i') }}
            </small>
        </div>

        <button type="button"
                onclick="window.print()"
                class="btn btn-success no-print">
            <i class="bi bi-printer me-1"></i>
            Cetak Laporan
        </button>
    </div>

    {{-- LAPORAN BARANG --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Stok Barang dan Sparepart</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Stok</th>
                            <th>Stok Minimum</th>
                            <th>Harga Jual</th>

                            @if ($bolehLihatHargaModal)
                                <th>Harga Modal</th>
                            @endif

                            <th>Status Stok</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($barang as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->kode_barang }}</td>
                                <td>{{ $item->nama_barang }}</td>
                                <td class="fw-semibold">
                                    {{ number_format($item->stok, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{ number_format($item->stok_minimum, 0, ',', '.') }}
                                </td>
                                <td>
                                    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                </td>

                                @if ($bolehLihatHargaModal)
                                    <td>
                                        Rp {{ number_format($item->harga_modal, 0, ',', '.') }}
                                    </td>
                                @endif

                                <td>
                                    @if ($item->status !== 'aktif')
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @elseif ($item->stok <= $item->stok_minimum)
                                        <span class="badge bg-warning text-dark">Perlu Restok</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $bolehLihatHargaModal ? 8 : 7 }}"
                                    class="text-center text-muted py-4">
                                    Belum ada data barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- LAPORAN HADIAH --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Stok Hadiah</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Kode Hadiah</th>
                            <th>Nama Hadiah</th>
                            <th>Stok Tersedia</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($hadiah as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->kode_hadiah }}</td>
                                <td>{{ $item->nama_hadiah }}</td>
                                <td class="fw-semibold">
                                    {{ number_format($item->stok, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($item->status === 'aktif')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="text-center text-muted py-4">
                                    Belum ada data hadiah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
    @media print {
        .no-print,
        .sidebar,
        .topbar,
        .sidebar-footer {
            display: none !important;
        }

        .main-area {
            margin: 0 !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            font-size: 11px !important;
        }

        @page {
            size: landscape;
            margin: 12mm;
        }
    }
</style>
@endsection
