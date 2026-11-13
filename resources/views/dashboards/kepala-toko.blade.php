@extends('layouts.app')

@section('title', 'Dashboard Kepala Toko')
@section('page-heading', 'Dashboard Kepala Toko')

@section('content')

    <style>
        .welcome-panel {
            background: var(--green-dark);
            color: white;
            border-radius: 12px;
            padding: 28px 30px;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .welcome-panel::after {
            content: 'HSN';
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 100px;
            font-weight: bold;
            color: rgba(255,255,255,.055);
            letter-spacing: -8px;
            pointer-events: none;
        }

        .welcome-label {
            color: #D6A84B;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .welcome-title {
            font-size: 25px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .welcome-description {
            color: #C5D1CA;
            margin: 0;
            max-width: 500px;
            line-height: 1.6;
        }

        .welcome-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 8px 12px;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 7px;
            color: #E3EAE5;
            font-size: 12px;
        }

        .section-heading {
            color: var(--green-dark);
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .section-description {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 18px;
        }

        .module-card {
            height: 100%;
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            transition: border-color .2s, transform .2s;
        }

        .module-card:hover {
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        .module-icon {
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #EDF2EE;
            color: var(--green-main);
            font-size: 20px;
            margin-bottom: 16px;
        }

        .module-title {
            color: var(--green-dark);
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .module-description {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        .module-status {
            display: inline-block;
            padding: 5px 9px;
            background: #F2F3F1;
            color: #69736C;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
        }

        .information-panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            margin-top: 28px;
        }

        .information-icon {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F8F2E5;
            color: #9A762C;
            border-radius: 8px;
            font-size: 18px;
            flex-shrink: 0;
        }

        @media (max-width: 576px) {
            .welcome-panel {
                padding: 23px 20px;
            }

            .welcome-panel::after {
                display: none;
            }

            .welcome-title {
                font-size: 21px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .module-card {
                transition: none;
            }

            .module-card:hover {
                transform: none;
            }
        }
    </style>

    <!-- Sambutan -->
    <div class="welcome-panel">

        <div class="welcome-label">
            HSN Service Perfection Palembang
        </div>

        <h2 class="welcome-title">
            Selamat datang, {{ auth()->user()->name }}!
        </h2>

        <p class="welcome-description">
            Selamat bekerja. Pantau kegiatan persediaan barang,
            penjualan, servis, dan hadiah toko melalui dashboard ini.
        </p>

        <div class="welcome-date">
            <i class="bi bi-calendar3"></i>
            {{ now()->format('d/m/Y') }}
        </div>

    </div>

    <!-- Modul Sistem -->
    <div class="mb-2">
        <h3 class="section-heading">Modul Pengelolaan Toko</h3>
        <p class="section-description">
            Berikut bagian-bagian utama yang tersedia dalam sistem HSN.
        </p>
    </div>

    <div class="row g-3">

        <div class="col-12 col-md-6 col-xl-4">
            <div class="module-card">
                <div class="module-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h4 class="module-title">Data Barang dan Sparepart</h4>
                <p class="module-description">
                    Mengelola informasi barang, kategori, satuan,
                    harga modal, harga jual, dan jumlah persediaan.
                </p>
                <span class="module-status">Modul pengelolaan barang</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="module-card">
                <div class="module-icon">
                    <i class="bi bi-box-arrow-in-down"></i>
                </div>
                <h4 class="module-title">Barang Masuk</h4>
                <p class="module-description">
                    Mencatat penerimaan barang dari pemasok,
                    termasuk jumlah barang dan informasi nota.
                </p>
                <span class="module-status">Modul persediaan</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="module-card">
                <div class="module-icon">
                    <i class="bi bi-cart3"></i>
                </div>
                <h4 class="module-title">Penjualan</h4>
                <p class="module-description">
                    Mengelola transaksi penjualan barang dan sparepart,
                    serta pencatatan nota penjualan.
                </p>
                <span class="module-status">Modul transaksi</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="module-card">
                <div class="module-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <h4 class="module-title">Servis</h4>
                <p class="module-description">
                    Mencatat servis pelanggan, kebutuhan sparepart,
                    biaya perbaikan, dan status pengerjaan.
                </p>
                <span class="module-status">Modul layanan servis</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="module-card">
                <div class="module-icon">
                    <i class="bi bi-gift"></i>
                </div>
                <h4 class="module-title">Pengelolaan Hadiah</h4>
                <p class="module-description">
                    Mengelola stok hadiah, transaksi hadiah masuk,
                    serta penyerahan hadiah kepada pelanggan.
                </p>
                <span class="module-status">Modul hadiah</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="module-card">
                <div class="module-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
<h4 class="module-title">Laporan</h4>
<p class="module-description">
    Memantau laporan barang masuk, barang keluar,
    riwayat transaksi, stok, dan penjualan kotor.
</p>
<span class="module-status">Laporan operasional</span>
            </div>
        </div>

    </div>

    <!-- Informasi -->
    <div class="information-panel">
        <div class="d-flex align-items-start gap-3">
            <div class="information-icon">
                <i class="bi bi-info-circle"></i>
            </div>

            <div>
                <h4 class="module-title mb-1">
                    Informasi Dashboard
                </h4>

                <p class="module-description mb-0">
                    Dashboard ini merupakan halaman utama Kepala Toko.
                    Nantinya, ringkasan jumlah persediaan, transaksi,
                    servis, hadiah, dan pendapatan akan ditampilkan
                    setelah modul pengelolaan masing-masing selesai dibuat.
                </p>
            </div>
        </div>
    </div>

@endsection
