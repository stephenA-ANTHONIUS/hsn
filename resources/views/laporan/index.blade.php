@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';

    $daftarLaporan = [
        [
            'judul' => 'Laporan Barang Masuk',
            'deskripsi' => 'Melihat riwayat penerimaan barang dan sparepart berdasarkan nota pemasok.',
            'icon' => 'bi-box-arrow-in-down',
            'route' => 'barang-masuk',
        ],
        [
            'judul' => 'Laporan Barang Keluar',
            'deskripsi' => 'Melihat pengeluaran barang, penggunaan sparepart servis, dan penyerahan hadiah.',
            'icon' => 'bi-box-arrow-up',
            'route' => 'barang-keluar',
        ],
        [
            'judul' => 'Laporan Riwayat Transaksi',
            'deskripsi' => 'Melihat rekapitulasi transaksi penjualan langsung, servis, dan penyerahan hadiah.',
            'icon' => 'bi-receipt',
            'route' => 'riwayat-transaksi',
        ],
        [
            'judul' => 'Laporan Stok',
            'deskripsi' => 'Melihat informasi ketersediaan stok barang, sparepart, dan hadiah.',
            'icon' => 'bi-box-seam',
            'route' => 'stok',
        ],
        [
            'judul' => 'Laporan Penjualan Kotor',
            'deskripsi' => 'Melihat rekap nilai penjualan barang dan jasa servis harian berdasarkan tanggal transaksi.',
            'icon' => 'bi-graph-up-arrow',
            'route' => 'penjualan-kotor',
        ],
    ];
@endphp

<div class="container-fluid px-0">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">Laporan</h4>
        <p class="text-muted mb-0">
            Lihat dan cetak laporan aktivitas persediaan serta transaksi HSN Service Perfection Palembang.
        </p>
    </div>

    <div class="row g-3">

        @foreach ($daftarLaporan as $laporan)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="rounded-3 bg-light p-3">
                                <i class="bi {{ $laporan['icon'] }} fs-3 text-success"></i>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-2">
                            {{ $laporan['judul'] }}
                        </h6>

                        <p class="text-muted small flex-grow-1">
                            {{ $laporan['deskripsi'] }}
                        </p>

                        <a href="{{ route($prefix . '.laporan.' . $laporan['route']) }}"
                           class="btn btn-outline-success">
                            Lihat Laporan
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>

                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="alert alert-light border mt-4">
        <i class="bi bi-info-circle me-2"></i>
        Laporan dapat digunakan untuk memantau aktivitas transaksi,
        perubahan persediaan, dan penjualan kotor toko berdasarkan
        data yang telah dicatat dalam sistem.
    </div>

</div>
@endsection
