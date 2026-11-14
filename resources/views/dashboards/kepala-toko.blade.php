@extends('layouts.app')

@section('title', 'Dashboard Kepala Toko')
@section('page-heading', 'Dashboard Kepala Toko')

@php
    $menuItems = [
        [
            'title' => 'Data Barang dan Sparepart',
            'description' => 'Lihat dan kelola informasi barang, kategori, satuan, harga jual, harga modal, serta jumlah persediaan.',
            'category' => 'Master data',
            'icon' => 'bi-box-seam',
            'route' => 'kepala-toko.barang.index',
        ],
        [
            'title' => 'Barang Masuk',
            'description' => 'Pantau penerimaan barang dari kantor pusat, jumlah barang, harga, dan informasi faktur.',
            'category' => 'Persediaan',
            'icon' => 'bi-box-arrow-in-down',
            'route' => 'kepala-toko.barang-masuk.index',
        ],
        [
            'title' => 'Penjualan',
            'description' => 'Pantau transaksi penjualan barang dan sparepart beserta nota yang diterbitkan.',
            'category' => 'Transaksi',
            'icon' => 'bi-receipt',
            'route' => 'kepala-toko.penjualan.index',
        ],
        [
            'title' => 'Transaksi Servis',
            'description' => 'Pantau data servis, penggunaan sparepart, pembayaran, dan status perbaikan pelanggan.',
            'category' => 'Layanan',
            'icon' => 'bi-tools',
            'route' => 'kepala-toko.servis.index',
        ],
        [
            'title' => 'Data Hadiah',
            'description' => 'Kelola jenis hadiah dan produk yang menjadi ketentuan pelanggan untuk menerima hadiah.',
            'category' => 'Pengelolaan hadiah',
            'icon' => 'bi-gift',
            'route' => 'kepala-toko.hadiah.index',
        ],
        [
            'title' => 'Riwayat Stok Hadiah',
            'description' => 'Periksa riwayat penambahan, pengeluaran, dan penyesuaian persediaan hadiah.',
            'category' => 'Riwayat persediaan',
            'icon' => 'bi-clock-history',
            'route' => 'kepala-toko.hadiah.stok.index',
        ],
        [
            'title' => 'Penyerahan Hadiah',
            'description' => 'Pantau penyerahan hadiah kepada pelanggan beserta dokumentasi foto serah terima.',
            'category' => 'Hadiah pelanggan',
            'icon' => 'bi-gift-fill',
            'route' => 'kepala-toko.penyerahan-hadiah.index',
        ],
        [
            'title' => 'Laporan',
            'description' => 'Evaluasi barang masuk, barang keluar, stok, riwayat transaksi, dan penjualan kotor.',
            'category' => 'Informasi operasional',
            'icon' => 'bi-file-earmark-bar-graph',
            'route' => 'kepala-toko.laporan.index',
        ],
    ];
@endphp

@push('styles')
<style>
    .dashboard-shell {
        width: 100%;
        max-width: 1500px;
        margin: 0 auto;
    }

    /* WELCOME PANEL */

    .dashboard-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: linear-gradient(
            120deg,
            var(--green-dark),
            var(--green-main)
        );
        color: #fff;
        border-radius: 16px;
        padding: 36px 38px;
        margin-bottom: 34px;
        box-shadow: 0 12px 28px rgba(23, 45, 39, .12);
    }

    .dashboard-hero::before {
        content: "";
        position: absolute;
        z-index: -1;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: var(--gold);
    }

    .dashboard-hero::after {
        content: "HSN";
        position: absolute;
        z-index: -1;
        right: 35px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, .055);
        font-size: clamp(90px, 12vw, 150px);
        font-weight: 900;
        letter-spacing: -10px;
        pointer-events: none;
        line-height: 1;
    }

    .dashboard-hero-content {
        position: relative;
        z-index: 1;
        max-width: 680px;
    }

    .dashboard-eyebrow {
        color: var(--gold);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 13px;
    }

    .dashboard-hero-title {
        color: #fff;
        font-size: clamp(23px, 3vw, 30px);
        font-weight: 750;
        line-height: 1.3;
        margin: 0 0 12px;
    }

    .dashboard-hero-description {
        color: #D1DCD5;
        font-size: 13px;
        line-height: 1.8;
        max-width: 580px;
        margin: 0;
    }

    .dashboard-date {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-top: 23px;
        padding: 9px 13px;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 8px;
        color: #EDF2EE;
        font-size: 12px;
        background: rgba(255, 255, 255, .055);
    }

    .dashboard-date i {
        color: var(--gold);
        font-size: 15px;
    }

    /* SECTION HEADING */

    .dashboard-section {
        margin-bottom: 18px;
    }

    .dashboard-section-heading {
        color: var(--green-dark);
        font-size: 19px;
        font-weight: 750;
        margin: 0 0 7px;
    }

    .dashboard-section-description {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.7;
        margin: 0;
    }

    /* MENU GRID */

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 17px;
    }

    .dashboard-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 218px;
        padding: 23px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 13px;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 3px 10px rgba(23, 45, 39, .025);
        transition:
            transform .2s ease,
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .dashboard-card:hover {
        color: inherit;
        text-decoration: none;
        transform: translateY(-4px);
        border-color: var(--gold);
        box-shadow: 0 12px 25px rgba(23, 45, 39, .09);
    }

    .dashboard-card:focus-visible {
        outline: 3px solid var(--gold);
        outline-offset: 4px;
    }

    .dashboard-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }

    .dashboard-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 11px;
        background: #EDF2EE;
        color: var(--green-main);
        font-size: 22px;
        transition: background .2s ease, color .2s ease;
    }

    .dashboard-card:hover .dashboard-card-icon {
        background: var(--green-main);
        color: #fff;
    }

    .dashboard-card-arrow {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #F4F5F1;
        color: #758078;
        font-size: 15px;
        transition: background .2s ease, color .2s ease;
    }

    .dashboard-card:hover .dashboard-card-arrow {
        background: var(--gold);
        color: var(--green-dark);
    }

    .dashboard-card-title {
        color: var(--green-dark);
        font-size: 15px;
        font-weight: 750;
        line-height: 1.45;
        margin: 0 0 8px;
    }

    .dashboard-card-description {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.75;
        margin: 0;
        flex: 1;
    }

    .dashboard-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 20px;
        padding-top: 14px;
        border-top: 1px solid #EDF0ED;
    }

    .dashboard-card-category {
        color: #6F7A72;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .dashboard-card-action {
        color: var(--green-main);
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
    }

    /* INFORMATION PANEL */

    .dashboard-information {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-top: 29px;
        padding: 21px 23px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 13px;
    }

    .dashboard-information-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 10px;
        background: #F8F2E5;
        color: #99752E;
        font-size: 19px;
    }

    .dashboard-information-title {
        color: var(--green-dark);
        font-size: 13px;
        font-weight: 750;
        margin: 1px 0 6px;
    }

    .dashboard-information-text {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.8;
        margin: 0;
    }

    /* RESPONSIVE */

    @media (max-width: 1100px) {
        .dashboard-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .dashboard-hero {
            padding: 27px 23px;
            margin-bottom: 27px;
            border-radius: 12px;
        }

        .dashboard-hero::after {
            display: none;
        }

        .dashboard-hero-title {
            font-size: 23px;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 13px;
        }

        .dashboard-card {
            min-height: 195px;
            padding: 20px;
        }

        .dashboard-section-heading {
            font-size: 17px;
        }

        .dashboard-information {
            padding: 17px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-card,
        .dashboard-card-icon,
        .dashboard-card-arrow {
            transition: none;
        }

        .dashboard-card:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')

<div class="dashboard-shell">

    {{-- WELCOME PANEL --}}
    <section class="dashboard-hero">
        <div class="dashboard-hero-content">

            <div class="dashboard-eyebrow">
                HSN Service Perfection Palembang
            </div>

            <h2 class="dashboard-hero-title">
                Selamat datang, {{ auth()->user()->name }}!
            </h2>

            <p class="dashboard-hero-description">
                Selamat bekerja. Pantau dan kelola seluruh kegiatan toko,
                mulai dari persediaan barang, penjualan, servis pelanggan,
                hingga pengelolaan hadiah dan laporan operasional.
            </p>

            <div class="dashboard-date">
                <i class="bi bi-calendar3"></i>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>

        </div>
    </section>

    {{-- MENU SECTION --}}
    <section>

        <div class="dashboard-section">
            <h3 class="dashboard-section-heading">
                Modul Pengelolaan Toko
            </h3>

            <p class="dashboard-section-description">
                Akses cepat ke modul pengelolaan dan pemantauan kegiatan HSN.
            </p>
        </div>

        <div class="dashboard-grid">

            @foreach ($menuItems as $item)

                <a href="{{ route($item['route']) }}"
                   class="dashboard-card"
                   aria-label="Buka menu {{ $item['title'] }}">

                    <div class="dashboard-card-top">

                        <div class="dashboard-card-icon">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </div>

                        <div class="dashboard-card-arrow">
                            <i class="bi bi-arrow-up-right"></i>
                        </div>

                    </div>

                    <h4 class="dashboard-card-title">
                        {{ $item['title'] }}
                    </h4>

                    <p class="dashboard-card-description">
                        {{ $item['description'] }}
                    </p>

                    <div class="dashboard-card-footer">

                        <span class="dashboard-card-category">
                            {{ $item['category'] }}
                        </span>

                        <span class="dashboard-card-action">
                            Buka menu
                        </span>

                    </div>

                </a>

            @endforeach

        </div>

    </section>

    {{-- INFORMATION --}}
    <section class="dashboard-information">

        <div class="dashboard-information-icon">
            <i class="bi bi-info-circle"></i>
        </div>

        <div>
            <h4 class="dashboard-information-title">
                Informasi Kepala Toko
            </h4>

            <p class="dashboard-information-text">
                Dashboard ini menyediakan akses ke seluruh modul pengelolaan
                toko sesuai kewenangan Kepala Toko, termasuk informasi harga
                modal, pemantauan persediaan, penjualan, servis, penyerahan
                hadiah, serta laporan operasional.
            </p>
        </div>

    </section>

</div>

@endsection
