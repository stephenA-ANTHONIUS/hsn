@extends('layouts.app')

@section('title', 'Riwayat Perubahan Penyerahan Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Perubahan Penyerahan Hadiah</h4>
            <p class="text-muted mb-0">
                Catatan seluruh aktivitas pencatatan dan perubahan penyerahan hadiah.
            </p>
        </div>

        <a href="{{ route($prefix . '.penyerahan-hadiah.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    {{-- INFORMASI PENYERAHAN --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Informasi Penyerahan Hadiah</h6>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted">Nomor Nota</small>
                    <div class="fw-semibold">
                        {{ $penyerahanHadiah->penjualan?->nomor_nota ?? '-' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Pelanggan</small>
                    <div class="fw-semibold">
                        {{ $penyerahanHadiah->pelanggan?->nama_pelanggan ?? 'Pelanggan umum' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Tanggal Penyerahan</small>
                    <div class="fw-semibold">
                        {{ $penyerahanHadiah->tanggal_penyerahan?->format('d-m-Y') ?? '-' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Petugas Pencatat Awal</small>
                    <div class="fw-semibold">
                        {{ $penyerahanHadiah->pengguna?->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KRONOLOGI PERUBAHAN --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-1"></i>
                Kronologi Perubahan
            </h6>
        </div>

        <div class="card-body">

            @forelse ($riwayat as $item)

                <div class="border rounded p-3 mb-4">

                    {{-- INFORMASI AKTIVITAS --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                        <div>
                            @if ($item->aksi === 'dibuat')
                                <span class="badge bg-success">
                                    Pencatatan Awal
                                </span>
                            @else
                                <span class="badge bg-primary">
                                    Perubahan Data
                                </span>
                            @endif

                            <div class="fw-bold mt-2">
                                {{ $item->aksi === 'dibuat'
                                    ? 'Penyerahan hadiah dibuat'
                                    : 'Penyerahan hadiah diperbarui' }}
                            </div>
                        </div>

                        <div class="text-md-end">
                            <div class="fw-semibold">
                                {{ $item->pengguna?->name ?? 'Pengguna tidak ditemukan' }}
                            </div>

                            <small class="text-muted">
                                {{ $item->created_at?->format('d-m-Y H:i:s') }}
                            </small>
                        </div>

                    </div>

                    @if ($item->keterangan)
                        <div class="alert alert-light border py-2">
                            {{ $item->keterangan }}
                        </div>
                    @endif

                    <div class="row g-3">

                        @foreach ([
                            'Data Sebelum Perubahan' => $item->data_sebelum,
                            'Data Sesudah Perubahan' => $item->data_sesudah,
                        ] as $judul => $snapshot)

                            <div class="col-lg-6">
                                <div class="border rounded h-100 p-3">

                                    <h6 class="fw-bold mb-3">
                                        {{ $judul }}
                                    </h6>

                                    @if (!$snapshot)

                                        <div class="text-muted small">
                                            Tidak ada data sebelumnya.
                                            Ini merupakan pencatatan pertama.
                                        </div>

                                    @else

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                Tanggal Penyerahan
                                            </small>
                                            <div class="fw-semibold">
                                                {{ !empty($snapshot['tanggal_penyerahan'])
                                                    ? \Carbon\Carbon::parse($snapshot['tanggal_penyerahan'])->format('d-m-Y')
                                                    : '-' }}
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                Nomor Nota
                                            </small>
                                            <div class="fw-semibold">
                                                {{ $snapshot['nomor_nota'] ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                Pelanggan
                                            </small>
                                            <div class="fw-semibold">
                                                {{ $snapshot['pelanggan'] ?? '-' }}
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                Hadiah yang Diserahkan
                                            </small>

                                            @forelse (($snapshot['hadiah'] ?? []) as $hadiah)
                                                <div class="border-bottom py-2">
                                                    <div class="fw-semibold">
                                                        {{ $hadiah['nama_hadiah'] ?? '-' }}
                                                    </div>

                                                    <div class="small text-muted">
                                                        Kode:
                                                        {{ $hadiah['kode_hadiah'] ?? '-' }}
                                                        |
                                                        Jumlah:
                                                        {{ number_format($hadiah['jumlah'] ?? 0, 0, ',', '.') }}
                                                        buah
                                                    </div>

                                                    @if (!empty($hadiah['keterangan']))
                                                        <div class="small mt-1">
                                                            Alasan tambahan:
                                                            {{ $hadiah['keterangan'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="text-muted small">
                                                    Tidak ada detail hadiah.
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                Catatan Penyerahan
                                            </small>
                                            <div>
                                                {{ $snapshot['keterangan'] ?: '-' }}
                                            </div>
                                        </div>

                                        <div>
                                            <small class="text-muted d-block mb-2">
                                                Foto Bukti
                                            </small>

                                            @if (!empty($snapshot['foto_bukti']))

                                                <a href="{{ asset('storage/' . $snapshot['foto_bukti']) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-image me-1"></i>
                                                    Lihat Foto Bukti
                                                </a>

                                            @else
                                                <span class="text-muted small">
                                                    Tidak ada foto.
                                                </span>
                                            @endif
                                        </div>

                                    @endif

                                </div>
                            </div>

                        @endforeach

                    </div>
                </div>

            @empty

                <div class="text-center py-5">
                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">
                        Belum ada riwayat perubahan penyerahan hadiah.
                    </p>
                </div>

            @endforelse

        </div>
    </div>

</div>
@endsection
