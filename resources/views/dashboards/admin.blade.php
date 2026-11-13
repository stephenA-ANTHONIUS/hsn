@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-heading', 'Dashboard Admin')

@section('content')

    <style>
        .admin-welcome {
            background: var(--green-dark);
            color: white;
            border-radius: 12px;
            padding: 28px 30px;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .admin-welcome::after {
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
            color: var(--gold);
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
            max-width: 520px;
            line-height: 1.6;
            margin-bottom: 0;
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

        .task-card {
            height: 100%;
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            transition: border-color .2s, transform .2s;
        }

        .task-card:hover {
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        .task-icon {
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

        .task-title {
            color: var(--green-dark);
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .task-description {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .task-category {
            display: inline-block;
            padding: 5px 9px;
            background: #F2F3F1;
            color: #69736C;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 14px;
        }

        .admin-note {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            margin-top: 28px;
        }

        .note-icon {
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
            .admin-welcome {
                padding: 23px 20px;
            }

            .admin-welcome::after {
                display: none;
            }

            .welcome-title {
                font-size: 21px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .task-card {
                transition: none;
            }

            .task-card:hover {
                transform: none;
            }
        }
    </style>

    <!-- Sambutan Admin -->
    <div class="admin-welcome">

        <div class="welcome-label">
            HSN Service Perfection Palembang
        </div>

        <h2 class="welcome-title">
            Selamat datang, {{ auth()->user()->name }}!
        </h2>

        <p class="welcome-description">
            Kelola kegiatan operasional toko melalui sistem HSN.
            Catat barang masuk, transaksi penjualan, servis pelanggan,
            penyerahan hadiah, dan pendapatan dalam satu tempat.
        </p>

        <div class="welcome-date">
            <i class="bi bi-calendar3"></i>
            {{ now()->format('d/m/Y') }}
        </div>

    </div>

    <!-- Daftar Pekerjaan Admin -->
    <div class="mb-2">
        <h3 class="section-heading">Menu Operasional</h3>
        <p class="section-description">
            Pilih bagian pengelolaan sesuai kegiatan yang sedang dilakukan.
        </p>
    </div>

    <div class="row g-3">

        <div class="col-12 col-md-6 col-xl-4">
            <div class="task-card">
                <div class="task-icon">
                    <i class="bi bi-box-seam"></i>
                </div>

                <h4 class="task-title">Data Barang dan Sparepart</h4>

                <p class="task-description">
                    Melihat dan mengelola informasi barang,
                    kategori, satuan, serta persediaan yang tersedia.
                </p>

                <span class="task-category">Persediaan barang</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="task-card">
                <div class="task-icon">
                    <i class="bi bi-box-arrow-in-down"></i>
                </div>

                <h4 class="task-title">Barang Masuk</h4>

                <p class="task-description">
                    Mencatat penerimaan barang dari pemasok,
                    jumlah barang, dan nomor nota penerimaan.
                </p>

                <span class="task-category">Pencatatan stok</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="task-card">
                <div class="task-icon">
                    <i class="bi bi-cart3"></i>
                </div>

                <h4 class="task-title">Penjualan</h4>

                <p class="task-description">
                    Melayani transaksi penjualan barang dan sparepart,
                    serta membuat nota penjualan pelanggan.
                </p>

                <span class="task-category">Transaksi penjualan</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="task-card">
                <div class="task-icon">
                    <i class="bi bi-tools"></i>
                </div>

                <h4 class="task-title">Servis</h4>

                <p class="task-description">
                    Mencatat data servis pelanggan, kebutuhan sparepart,
                    biaya perbaikan, dan status pengerjaan servis.
                </p>

                <span class="task-category">Layanan servis</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="task-card">
                <div class="task-icon">
                    <i class="bi bi-gift"></i>
                </div>

                <h4 class="task-title">Penyerahan Hadiah</h4>

                <p class="task-description">
                    Mencatat penyerahan hadiah kepada pelanggan,
                    jumlah hadiah, dan foto bukti penyerahan.
                </p>

                <span class="task-category">Hadiah pelanggan</span>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="task-card">
                <div class="task-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
<h4 class="task-title">Laporan</h4>

<p class="task-description">
    Melihat laporan barang masuk, barang keluar, stok,
    riwayat transaksi penjualan, dan servis.
</p>

<span class="task-category">Laporan operasional</span>
            </div>
        </div>

    </div>

    <!-- Informasi -->
    <div class="admin-note">
        <div class="d-flex align-items-start gap-3">

            <div class="note-icon">
                <i class="bi bi-info-circle"></i>
            </div>

            <div>
                <h4 class="task-title mb-1">
                    Informasi Pekerjaan Admin
                </h4>

                <p class="task-description">
                    Setiap transaksi yang dicatat akan terhubung
                    dengan data persediaan dan riwayat transaksi.
                    Pastikan jumlah barang, informasi pelanggan,
                    serta data pembayaran dicatat dengan benar.
                </p>
            </div>

        </div>
    </div>

@endsection
