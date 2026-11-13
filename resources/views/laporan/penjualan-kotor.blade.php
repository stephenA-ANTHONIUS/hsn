@extends('layouts.app')

@section('title', 'Laporan Penjualan Kotor')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">Laporan Penjualan Kotor</h4>
            <p class="text-muted mb-0">
                Rekapitulasi nilai penjualan langsung dan transaksi servis.
            </p>
        </div>

        <a href="{{ route($prefix . '.laporan.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    {{-- FILTER --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET"
                  action="{{ route($prefix . '.laporan.penjualan-kotor') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date"
                               name="tanggal_mulai"
                               class="form-control"
                               value="{{ request('tanggal_mulai') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date"
                               name="tanggal_akhir"
                               class="form-control"
                               value="{{ request('tanggal_akhir') }}">
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit"
                                class="btn btn-success flex-grow-1">
                            <i class="bi bi-search me-1"></i>
                            Terapkan Filter
                        </button>

                        <a href="{{ route($prefix . '.laporan.penjualan-kotor') }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- JUDUL CETAK --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Rekapitulasi Penjualan Kotor</h5>

            <small class="text-muted">
                Periode:
                {{ !empty($periode['tanggal_mulai'])
                    ? \Carbon\Carbon::parse($periode['tanggal_mulai'])->format('d-m-Y')
                    : 'Semua tanggal' }}

                sampai

                {{ !empty($periode['tanggal_akhir'])
                    ? \Carbon\Carbon::parse($periode['tanggal_akhir'])->format('d-m-Y')
                    : 'Semua tanggal' }}
            </small>
        </div>

        <button type="button"
                onclick="window.print()"
                class="btn btn-success no-print">
            <i class="bi bi-printer me-1"></i>
            Cetak Laporan
        </button>
    </div>

    {{-- RINGKASAN --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Penjualan Barang</small>
                    <h5 class="fw-bold mt-2 mb-0">
                        Rp {{ number_format($totalPenjualan, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Transaksi Servis</small>
                    <h5 class="fw-bold mt-2 mb-0">
                        Rp {{ number_format($totalServis, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Total Penjualan Kotor</small>
                    <h5 class="fw-bold text-success mt-2 mb-0">
                        Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>

    </div>

    {{-- TABEL --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No.</th>
                            <th>Tanggal</th>
                            <th>Penjualan Barang</th>
                            <th>Transaksi Servis</th>
                            <th>Total Penjualan Kotor</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($baris as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['tanggal'] }}</td>
                                <td>{{ $item['penjualan'] }}</td>
                                <td>{{ $item['servis'] }}</td>
                                <td class="fw-bold">
                                    {{ $item['total'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="text-center text-muted py-5">
                                    Belum ada transaksi pada periode tersebut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if (count($baris) > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">
                                    Total Keseluruhan
                                </th>
                                <th>
                                    Rp {{ number_format($totalPenjualan, 0, ',', '.') }}
                                </th>
                                <th>
                                    Rp {{ number_format($totalServis, 0, ',', '.') }}
                                </th>
                                <th>
                                    Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

        </div>
    </div>

    <div class="alert alert-light border mt-4">
        <i class="bi bi-info-circle me-2"></i>
        Laporan ini menunjukkan nilai transaksi kotor berdasarkan tanggal nota,
        bukan jumlah uang yang sudah diterima. Pembayaran bertahap tidak
        ditambahkan sebagai transaksi baru. Hadiah tidak dihitung sebagai
        penjualan karena merupakan pengeluaran persediaan.
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

        @page {
            size: portrait;
            margin: 15mm;
        }
    }
</style>
@endsection
