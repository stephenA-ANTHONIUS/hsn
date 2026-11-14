@extends('layouts.app')

@section('title', 'Tambah Transaksi Stok Hadiah')
@section('page-heading', 'Tambah Transaksi Stok Hadiah')

@section('content')

@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Tambah Transaksi Stok Hadiah</h4>
            <p class="text-muted mb-0">
                Catat penambahan atau penyesuaian stok hadiah.
            </p>
        </div>

        <a href="{{ route($prefix . '.hadiah.stok.index') }}"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($hadiah->isEmpty())

        <div class="alert alert-warning">
            <h6 class="fw-bold">Belum Ada Hadiah Aktif</h6>
            <p class="mb-2">
                Tambahkan data hadiah terlebih dahulu sebelum mencatat transaksi stok.
            </p>

            <a href="{{ route($prefix . '.hadiah.create') }}"
               class="btn btn-success btn-sm">
                Tambah Data Hadiah
            </a>
        </div>

    @else

        <form action="{{ route($prefix . '.hadiah.stok.store') }}"
              method="POST">

            @csrf

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Informasi Transaksi Stok</h6>
                </div>

                <div class="card-body">

                    {{-- PILIH HADIAH --}}
                    <div class="mb-3">
                        <label for="hadiah_id" class="form-label fw-semibold">
                            Nama Hadiah <span class="text-danger">*</span>
                        </label>

                        <select name="hadiah_id"
                                id="hadiah_id"
                                class="form-select @error('hadiah_id') is-invalid @enderror"
                                required>

                            <option value="">Pilih hadiah</option>

                            @foreach ($hadiah as $item)
                                <option value="{{ $item->id }}"
                                        data-stok="{{ $item->stok }}"
                                    @selected(old('hadiah_id') == $item->id)>
                                    {{ $item->kode_hadiah }} -
                                    {{ $item->nama_hadiah }}
                                </option>
                            @endforeach

                        </select>

                        @error('hadiah_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STOK SAAT INI --}}
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Stok Saat Ini</span>
                            <strong id="stokSaatIni">-</strong>
                        </div>
                    </div>

                    {{-- JENIS TRANSAKSI --}}
                    <div class="mb-3">
                        <label for="jenis_transaksi"
                               class="form-label fw-semibold">
                            Jenis Transaksi <span class="text-danger">*</span>
                        </label>

                        <select name="jenis_transaksi"
                                id="jenis_transaksi"
                                class="form-select @error('jenis_transaksi') is-invalid @enderror"
                                required>

                            <option value="masuk"
                                @selected(old('jenis_transaksi', 'masuk') === 'masuk')>
                                Stok Masuk
                            </option>

                            <option value="penyesuaian"
                                @selected(old('jenis_transaksi') === 'penyesuaian')>
                                Penyesuaian Stok
                            </option>

                        </select>

                        @error('jenis_transaksi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted" id="keteranganJenis">
                            Stok hadiah akan bertambah sesuai jumlah yang dimasukkan.
                        </small>
                    </div>

                    {{-- JUMLAH --}}
                    <div class="mb-3">
                        <label for="jumlah"
                               class="form-label fw-semibold"
                               id="labelJumlah">
                            Jumlah Stok Masuk <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="jumlah"
                               id="jumlah"
                               class="form-control @error('jumlah') is-invalid @enderror"
                               min="1"
                               step="1"
                               value="{{ old('jumlah', 1) }}"
                               required>

                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted" id="bantuanJumlah">
                            Masukkan jumlah hadiah yang masuk.
                        </small>
                    </div>

                    {{-- TANGGAL --}}
                    <div class="mb-3">
                        <label for="tanggal_transaksi"
                               class="form-label fw-semibold">
                            Tanggal Transaksi <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_transaksi"
                               id="tanggal_transaksi"
                               class="form-control @error('tanggal_transaksi') is-invalid @enderror"
                               value="{{ old('tanggal_transaksi', now()->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               required>

                        @error('tanggal_transaksi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="mb-3">
                        <label for="keterangan"
                               class="form-label fw-semibold">
                            Keterangan
                        </label>

                        <textarea name="keterangan"
                                  id="keterangan"
                                  class="form-control @error('keterangan') is-invalid @enderror"
                                  rows="3"
                                  maxlength="2000"
                                  placeholder="Masukkan keterangan transaksi jika diperlukan">{{ old('keterangan') }}</textarea>

                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">

                <a href="{{ route($prefix . '.hadiah.stok.index') }}"
                   class="btn btn-light border">
                    Batal
                </a>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save me-1"></i>
                    Simpan Transaksi
                </button>

            </div>

        </form>

    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const pilihanHadiah = document.getElementById('hadiah_id');
    const jenisTransaksi = document.getElementById('jenis_transaksi');
    const jumlah = document.getElementById('jumlah');

    const stokSaatIni = document.getElementById('stokSaatIni');
    const labelJumlah = document.getElementById('labelJumlah');
    const bantuanJumlah = document.getElementById('bantuanJumlah');
    const keteranganJenis = document.getElementById('keteranganJenis');

    if (!pilihanHadiah || !jenisTransaksi || !jumlah) {
        return;
    }

    function tampilkanStok() {
        const pilihan = pilihanHadiah.selectedOptions[0];

        if (!pilihan || !pilihan.value) {
            stokSaatIni.textContent = '-';
            return;
        }

        stokSaatIni.textContent =
            Number(pilihan.dataset.stok).toLocaleString('id-ID');
    }

    function aturJenisTransaksi() {

        if (jenisTransaksi.value === 'masuk') {

            jumlah.min = 1;

            labelJumlah.innerHTML =
                'Jumlah Stok Masuk <span class="text-danger">*</span>';

            bantuanJumlah.textContent =
                'Masukkan jumlah hadiah yang akan ditambahkan ke stok.';

            keteranganJenis.textContent =
                'Stok hadiah akan bertambah sesuai jumlah yang dimasukkan.';

        } else {

            jumlah.min = 0;

            labelJumlah.innerHTML =
                'Jumlah Stok Fisik <span class="text-danger">*</span>';

            bantuanJumlah.textContent =
                'Masukkan jumlah stok fisik terbaru. Stok sistem akan disesuaikan dengan jumlah ini.';

            keteranganJenis.textContent =
                'Penyesuaian akan mengganti stok sistem dengan jumlah stok fisik terbaru.';
        }
    }

    pilihanHadiah.addEventListener('change', tampilkanStok);
    jenisTransaksi.addEventListener('change', aturJenisTransaksi);

    tampilkanStok();
    aturJenisTransaksi();

});
</script>

@endsection
