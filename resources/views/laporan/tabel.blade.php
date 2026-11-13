@extends('layouts.app')

@section('title', $judul)

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">{{ $judul }}</h4>
            <p class="text-muted mb-0">{{ $deskripsi }}</p>
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
                  action="{{ route($prefix . '.laporan.' . $jenis) }}">

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
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="bi bi-search me-1"></i>
                            Terapkan Filter
                        </button>

                        <a href="{{ route($prefix . '.laporan.' . $jenis) }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- JUDUL CETAK --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">{{ $judul }}</h5>

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
    @if (!empty($ringkasan))
        <div class="row g-3 mb-4">
            @foreach ($ringkasan as $item)
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">{{ $item['label'] }}</small>
                            <h5 class="fw-bold mb-0 mt-2">
                                {{ $item['nilai'] }}
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- TABEL --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="55">No.</th>

                            @foreach ($kolom as $judulKolom)
                                <th>{{ $judulKolom }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($baris as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                @foreach ($kolom as $field => $judulKolom)
                                    <td>{{ $item[$field] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($kolom) + 1 }}"
                                    class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data laporan pada periode tersebut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-muted small mt-2">
                Jumlah data: {{ count($baris) }} baris.
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

        body {
            background: white !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            font-size: 10px !important;
        }

        @page {
            size: landscape;
            margin: 12mm;
        }
    }
</style>
@endsection
