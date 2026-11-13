@extends('layouts.app')

@section('title', 'Riwayat Perubahan Barang')

@section('content')

@php
    $labelPerubahan = [
        'ditambahkan' => 'Barang Ditambahkan',
        'diperbarui' => 'Data Barang Diperbarui',
        'dinonaktifkan' => 'Barang Dinonaktifkan',
        'diaktifkan' => 'Barang Diaktifkan',
        'barang_masuk_ditambahkan' => 'Barang Masuk Ditambahkan',
        'barang_masuk_diperbarui' => 'Barang Masuk Diperbarui',
    ];

    $warnaPerubahan = [
        'ditambahkan' => 'success',
        'diperbarui' => 'primary',
        'dinonaktifkan' => 'secondary',
        'diaktifkan' => 'success',
        'barang_masuk_ditambahkan' => 'info',
        'barang_masuk_diperbarui' => 'warning',
    ];

    $labelKolom = [
        'kode_barang' => 'Kode Barang',
        'nama_barang' => 'Nama Barang',
        'kategori_barang_id' => 'Kategori Barang (ID)',
        'satuan_barang_id' => 'Satuan Barang (ID)',
        'harga_modal' => 'Harga Modal',
        'harga_jual' => 'Harga Jual',
        'stok_minimum' => 'Stok Minimum',
        'deskripsi' => 'Deskripsi',
        'status' => 'Status Barang',
        'nomor_nota' => 'Nomor Nota',
        'tanggal_masuk' => 'Tanggal Masuk',
        'pemasok' => 'Pemasok',
        'keterangan' => 'Keterangan',
    ];

    $formatNilai = function ($kolom, $nilai) {
        if (is_array($nilai)) {
            return 'Rincian tersedia pada tabel detail.';
        }

        if ($nilai === null || $nilai === '') {
            return '-';
        }

        if (in_array($kolom, ['harga_modal', 'harga_jual'])) {
            return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
        }

        if ($kolom === 'status') {
            return $nilai === 'aktif' ? 'Aktif' : 'Nonaktif';
        }

        return (string) $nilai;
    };

    $kolomDetail = [
        'jumlah' => 'Jumlah',
        'harga_jual' => 'Harga Jual',
    ];

    if ($isKepalaToko) {
        $kolomDetail['harga_modal'] = 'Harga Modal';
    }
@endphp

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Perubahan Barang</h4>
            <p class="text-muted mb-0">
                Catatan perubahan data barang dan transaksi barang masuk.
            </p>
        </div>

        <a href="{{ route($routePrefix . '.barang.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Data Barang
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form method="GET"
                  action="{{ route($routePrefix . '.barang.riwayat') }}"
                  class="mb-4">

                <div class="row g-2">

                    <div class="col-md-3">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari barang, nota, atau pengguna..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <select name="jenis_perubahan" class="form-select">
                            <option value="">Semua Perubahan</option>

                            @foreach($labelPerubahan as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(request('jenis_perubahan') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date"
                               name="tanggal_mulai"
                               class="form-control"
                               value="{{ request('tanggal_mulai') }}"
                               title="Tanggal mulai">
                    </div>

                    <div class="col-md-2">
                        <input type="date"
                               name="tanggal_akhir"
                               class="form-control"
                               value="{{ request('tanggal_akhir') }}"
                               title="Tanggal akhir">
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search me-1"></i>
                            Cari
                        </button>

                        <a href="{{ route($routePrefix . '.barang.riwayat') }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Waktu</th>
                            <th>Barang / Nota</th>
                            <th>Jenis Perubahan</th>
                            <th>Pengguna</th>
                            <th>Detail</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($riwayat as $item)

                            @php
                                $sebelum = $item->data_sebelum ?? [];
                                $sesudah = $item->data_sesudah ?? [];

                                $kodeBarang = $sesudah['kode_barang']
                                    ?? $sebelum['kode_barang']
                                    ?? $item->barang?->kode_barang
                                    ?? null;

                                $namaBarang = $sesudah['nama_barang']
                                    ?? $sebelum['nama_barang']
                                    ?? $item->barang?->nama_barang
                                    ?? null;

                                $nomorNota = $sesudah['nomor_nota']
                                    ?? $sebelum['nomor_nota']
                                    ?? null;

                                $judul = $nomorNota
                                    ? 'Nota ' . $nomorNota
                                    : ($namaBarang ?? 'Barang telah dihapus');

                                $semuaKolom = array_unique(array_merge(
                                    array_keys($sebelum),
                                    array_keys($sesudah)
                                ));

                                // Mengambil detail barang masuk sebelum dan sesudah perubahan.
                                $detailSebelum = collect(
                                    is_array($sebelum['detail'] ?? null)
                                        ? $sebelum['detail']
                                        : []
                                )->keyBy('barang_id');

                                $detailSesudah = collect(
                                    is_array($sesudah['detail'] ?? null)
                                        ? $sesudah['detail']
                                        : []
                                )->keyBy('barang_id');

                                $semuaBarangId = $detailSebelum->keys()
                                    ->merge($detailSesudah->keys())
                                    ->unique()
                                    ->values();

                                $adaDetail = $semuaBarangId->isNotEmpty();
                            @endphp

                            <tr>

                                <td>
                                    {{ $riwayat->firstItem() + $loop->index }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $item->created_at->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $item->created_at->format('H:i') }}
                                    </small>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $judul }}
                                    </div>

                                    @if($kodeBarang)
                                        <small class="text-muted">
                                            {{ $kodeBarang }}
                                        </small>
                                    @elseif($item->barang_id)
                                        <small class="text-muted">
                                            {{ $item->barang?->nama_barang ?? 'Barang telah dihapus' }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge text-bg-{{ $warnaPerubahan[$item->jenis_perubahan] ?? 'secondary' }}">
                                        {{ $labelPerubahan[$item->jenis_perubahan] ?? $item->jenis_perubahan }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->pengguna->name ?? '-' }}
                                </td>

                                <td>
                                    <details>
                                        <summary class="text-success fw-semibold"
                                                 style="cursor:pointer">
                                            Lihat Detail
                                        </summary>

                                        <div class="mt-3" style="min-width:320px">

                                            @if($item->keterangan)
                                                <p class="small text-muted">
                                                    {{ $item->keterangan }}
                                                </p>
                                            @endif

                                            {{-- INFORMASI UTAMA --}}
                                            <div class="fw-semibold mb-2">
                                                Informasi Utama
                                            </div>

                                            <div class="table-responsive mb-3">

                                                <table class="table table-sm table-bordered">

                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Informasi</th>
                                                            <th>Sebelum</th>
                                                            <th>Sesudah</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>

                                                        @foreach($semuaKolom as $kolom)

                                                            @if($kolom !== 'detail' && $kolom !== 'barang_masuk_id')

                                                                @php
                                                                    $nilaiLama = $sebelum[$kolom] ?? null;
                                                                    $nilaiBaru = $sesudah[$kolom] ?? null;
                                                                @endphp

                                                                @if($nilaiLama != $nilaiBaru)

                                                                    <tr>
                                                                        <td class="fw-semibold">
                                                                            {{ $labelKolom[$kolom] ?? $kolom }}
                                                                        </td>

                                                                        <td>
                                                                            {{ $formatNilai($kolom, $nilaiLama) }}
                                                                        </td>

                                                                        <td>
                                                                            {{ $formatNilai($kolom, $nilaiBaru) }}
                                                                        </td>
                                                                    </tr>

                                                                @endif

                                                            @endif

                                                        @endforeach

                                                    </tbody>

                                                </table>
                                            </div>

                                            {{-- DETAIL BARANG MASUK --}}
                                            @if($adaDetail)

                                                <div class="fw-semibold mb-2">
                                                    Rincian Barang Masuk
                                                </div>

                                                <div class="table-responsive">

                                                    <table class="table table-sm table-bordered">

                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Barang</th>

                                                                @foreach($kolomDetail as $label)
                                                                    <th>{{ $label }} Sebelum</th>
                                                                    <th>{{ $label }} Sesudah</th>
                                                                @endforeach

                                                            </tr>
                                                        </thead>

                                                        <tbody>

                                                            @foreach($semuaBarangId as $barangId)

                                                                @php
                                                                    $lama = $detailSebelum->get($barangId);
                                                                    $baru = $detailSesudah->get($barangId);

                                                                    $nama = $baru['nama_barang']
                                                                        ?? $lama['nama_barang']
                                                                        ?? 'Barang';

                                                                    $kode = $baru['kode_barang']
                                                                        ?? $lama['kode_barang']
                                                                        ?? '';

                                                                @endphp

                                                                <tr>

                                                                    <td>
                                                                        <div class="fw-semibold">
                                                                            {{ $nama }}
                                                                        </div>

                                                                        <small class="text-muted">
                                                                            {{ $kode }}
                                                                        </small>
                                                                    </td>

                                                                    @foreach($kolomDetail as $kolom => $label)

                                                                        <td>
                                                                            {{ $formatNilai($kolom, $lama[$kolom] ?? null) }}
                                                                        </td>

                                                                        <td>
                                                                            {{ $formatNilai($kolom, $baru[$kolom] ?? null) }}
                                                                        </td>

                                                                    @endforeach

                                                                </tr>

                                                            @endforeach

                                                        </tbody>

                                                    </table>

                                                </div>

                                            @endif

                                        </div>
                                    </details>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted py-4">
                                    Belum ada riwayat perubahan.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">

                <small class="text-muted">
                    Menampilkan {{ $riwayat->count() }}
                    dari {{ $riwayat->total() }} riwayat
                </small>

                {{ $riwayat->links() }}

            </div>

        </div>
    </div>

</div>

@endsection
