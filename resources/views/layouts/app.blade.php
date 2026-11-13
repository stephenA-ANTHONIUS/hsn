
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') - HSN Service Perfection</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --green-dark: #172D27;
            --green-main: #28483A;
            --gold: #D6A84B;
            --background: #F4F5F1;
            --text: #26332D;
            --muted: #78817B;
            --border: #DCE2DD;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 250px;
            min-width: 250px;
            background: var(--green-dark);
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 25px 22px;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            border-radius: 11px;
            background: var(--gold);
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 15px;
            font-weight: bold;
            line-height: 1.4;
        }

        .brand-caption {
            color: #B9C8BF;
            font-size: 11px;
        }

        .sidebar-menu {
            padding: 22px 14px;
            flex: 1;
        }

        .menu-label {
            color: #9EAEA4;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 0 12px;
            margin: 18px 0 10px;
        }

        .menu-label:first-child {
            margin-top: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            margin-bottom: 5px;
            border-radius: 8px;
            color: #D5DED8;
            text-decoration: none;
            transition: background .2s, color .2s;
        }

        a.sidebar-link:hover {
            background: rgba(255,255,255,.08);
            color: white;
        }

        a.sidebar-link.active {
            background: var(--gold);
            color: var(--green-dark);
            font-weight: bold;
        }

        .sidebar-link i {
            font-size: 17px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-link.disabled-menu {
            color: #82948A;
            cursor: not-allowed;
            opacity: .75;
        }

        .menu-coming-soon {
            font-size: 9px;
            color: #A9B7AE;
            margin-left: auto;
            white-space: nowrap;
        }

        .sidebar-footer {
            padding: 18px 22px;
            border-top: 1px solid rgba(255,255,255,.12);
            color: #B9C8BF;
            font-size: 11px;
            line-height: 1.7;
        }

        /* MAIN AREA */

        .main-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            min-height: 76px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 30px;
        }

        .page-heading {
            font-size: 17px;
            font-weight: bold;
            color: var(--green-dark);
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #E7EEE9;
            color: var(--green-main);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: bold;
            font-size: 13px;
        }

        .user-role {
            font-size: 11px;
            color: var(--muted);
        }

        .content-area {
            padding: 30px;
            flex: 1;
        }

        .content-title {
            color: var(--green-dark);
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .content-subtitle {
            color: var(--muted);
            margin-bottom: 25px;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--gold);
            outline-offset: 3px;
        }

        /* RESPONSIVE */

        @media (max-width: 768px) {
            .app-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-width: 0;
            }

            .sidebar-brand {
                padding: 15px 20px;
            }

            .sidebar-menu {
                padding: 12px 14px;
            }

            .sidebar-footer {
                display: none;
            }

            .topbar {
                min-height: 70px;
                padding: 15px 20px;
            }

            .content-area {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-info {
                width: 100%;
                justify-content: flex-start;
            }

            .content-area {
                padding: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>


@php
    $user = auth()->user();
    $role = $user?->role;

    $navigationByRole = [

        // MENU KEPALA TOKO
        'kepala_toko' => [
            [
                'label' => 'MENU UTAMA',
                'items' => [
                    [
                        'name' => 'Dashboard',
                        'icon' => 'bi-grid',
                        'route' => 'kepala-toko.dashboard',
                    ],
                ],
            ],

            [
                'label' => 'MASTER DATA',
                'items' => [
                    [
                        'name' => 'Data Barang',
                        'icon' => 'bi-box-seam',
                        'route' => 'kepala-toko.barang.index',
                    ],
                    [
    'name' => 'Riwayat Perubahan Barang',
    'icon' => 'bi-clock-history',
    'route' => 'kepala-toko.barang.riwayat',
],

                    [
                        'name' => 'Kategori Barang',
                        'icon' => 'bi-tags',
                        'route' => 'kepala-toko.kategori-barang.index',
                    ],
                    [
                        'name' => 'Satuan Barang',
                        'icon' => 'bi-rulers',
                        'route' => 'kepala-toko.satuan-barang.index',
                    ],
                    [
                        'name' => 'Pemasok',
                        'icon' => 'bi-truck',
                        'route' => 'kepala-toko.pemasok.index',
                    ],
                    [
                        'name' => 'Pelanggan',
                        'icon' => 'bi-people',
                        'route' => 'kepala-toko.pelanggan.index',
                    ],
                    [
                        'name' => 'Kelola User',
                        'icon' => 'bi-person-gear',
                        'route' => 'kepala-toko.pengguna.index',
                    ],
                ],
            ],

            [
                'label' => 'TRANSAKSI',
                'items' => [
                    [
                        'name' => 'Barang Masuk',
                        'icon' => 'bi-box-arrow-in-down',
                        'route' => 'kepala-toko.barang-masuk.index',
                    ],
                    [
                        'name' => 'Penjualan',
                        'icon' => 'bi-receipt',
                        'route' => 'kepala-toko.penjualan.index',
                    ],
                    [
                        'name' => 'Transaksi Servis',
                        'icon' => 'bi-tools',
                        'route' => 'kepala-toko.servis.index',
                    ],
                ],
            ],

            [
                'label' => 'PENGELOLAAN HADIAH',
                'items' => [
                    [
                        'name' => 'Data Hadiah',
                        'icon' => 'bi-gift',
                        'route' => 'kepala-toko.hadiah.index',
                    ],
                    [
                        'name' => 'Riwayat Stok Hadiah',
                        'icon' => 'bi-clock-history',
                        'route' => 'kepala-toko.hadiah.stok.index',
                    ],
                    [
                        'name' => 'Penyerahan Hadiah',
                        'icon' => 'bi-gift',
                        'route' => 'kepala-toko.penyerahan-hadiah.index',
                    ],
                ],
            ],

            [
                'label' => 'LAPORAN',
                'items' => [
[
    'name' => 'Laporan',
    'icon' => 'bi-file-earmark-bar-graph',
    'route' => 'kepala-toko.laporan.index',
],
                ],
            ],
        ],

        // MENU ADMIN
        'admin' => [
            [
                'label' => 'MENU UTAMA',
                'items' => [
                    [
                        'name' => 'Dashboard',
                        'icon' => 'bi-grid',
                        'route' => 'admin.dashboard',
                    ],
                ],
            ],

            [
                'label' => 'OPERASIONAL',
                'items' => [
                    [
                        'name' => 'Barang Masuk',
                        'icon' => 'bi-box-arrow-in-down',
                        'route' => 'admin.barang-masuk.index',
                    ],
                    [
                        'name' => 'Penjualan',
                        'icon' => 'bi-receipt',
                        'route' => 'admin.penjualan.index',
                    ],
                    [
                        'name' => 'Transaksi Servis',
                        'icon' => 'bi-tools',
                        'route' => 'admin.servis.index',
                    ],
                    [
                        'name' => 'Data Hadiah',
                        'icon' => 'bi-gift',
                        'route' => 'admin.hadiah.index',
                    ],
                    [
                        'name' => 'Riwayat Stok Hadiah',
                        'icon' => 'bi-clock-history',
                        'route' => 'admin.hadiah.stok.index',
                    ],
                    [
                        'name' => 'Penyerahan Hadiah',
                        'icon' => 'bi-gift',
                        'route' => 'admin.penyerahan-hadiah.index',
                    ],
[
    'name' => 'Laporan',
    'icon' => 'bi-file-earmark-bar-graph',
    'route' => 'admin.laporan.index',
],
                ],
            ],
        ],
    ];

    $menuGroups = $navigationByRole[$role] ?? [];
@endphp


<div class="app-wrapper">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="d-flex align-items-center">

                <div class="brand-logo">
                    HSN
                </div>

                <div>
                    <div class="brand-name">
                        HSN Service
                    </div>

                    <div class="brand-caption">
                        Perfection Palembang
                    </div>
                </div>

            </div>
        </div>

        <nav class="sidebar-menu" aria-label="Navigasi utama">

            @foreach ($menuGroups as $group)

                <div class="menu-label">
                    {{ $group['label'] }}
                </div>

                @foreach ($group['items'] as $item)

                    @if (!empty($item['route']))

                        <a href="{{ route($item['route']) }}"
                           class="sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                           @if (request()->routeIs($item['route'])) aria-current="page" @endif>

                            <i class="bi {{ $item['icon'] }}"></i>

                            <span>
                                {{ $item['name'] }}
                            </span>

                        </a>

                    @else

                        <span class="sidebar-link disabled-menu"
                              aria-disabled="true"
                              title="Fitur belum tersedia">

                            <i class="bi {{ $item['icon'] }}"></i>

                            <span>
                                {{ $item['name'] }}
                            </span>

                            <span class="menu-coming-soon">
                                Segera
                            </span>

                        </span>

                    @endif

                @endforeach

            @endforeach

        </nav>

        <div class="sidebar-footer">
            Sistem Informasi Manajemen HSN<br>
            © {{ date('Y') }} HSN Service Perfection
        </div>

    </aside>

    <!-- AREA UTAMA -->

    <main class="main-area">

        <header class="topbar">

            <h1 class="page-heading">
                @yield('page-heading', 'Dashboard')
            </h1>

            <div class="user-info">

                <div class="user-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div>
                    <div class="user-name">
                        {{ $user->name }}
                    </div>

                    <div class="user-role">
                        {{ $role === 'kepala_toko' ? 'Kepala Toko' : 'Admin' }}
                    </div>
                </div>

                <form action="{{ route('logout') }}"
                      method="POST"
                      class="ms-2">

                    @csrf

                    <button type="submit"
                            class="btn btn-sm btn-outline-danger">

                        <i class="bi bi-box-arrow-right"></i>
                        Keluar

                    </button>

                </form>

            </div>

        </header>

        <section class="content-area">
            @yield('content')
        </section>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>
