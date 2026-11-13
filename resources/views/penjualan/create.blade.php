@extends('layouts.app')

@section('title', 'Tambah Penjualan')

@section('content')
@php
    $prefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">Tambah Penjualan</h4>
        <p class="text-muted mb-0">
            Masukkan informasi pelanggan dan barang yang dibeli.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route($prefix . '.penjualan.store') }}"
          id="formPenjualan">

        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Informasi Penjualan</h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Pelanggan</label>

                        <select name="pelanggan_id" class="form-select">
                            <option value="">Pelanggan Umum</option>

                            @foreach($pelanggan as $item)
                                <option value="{{ $item->id }}"
                                    @selected(old('pelanggan_id') == $item->id)>
                                    {{ $item->nama_pelanggan }}
                                    @if($item->no_telepon)
                                        - {{ $item->no_telepon }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <small class="text-muted">
                            Pilih pelanggan yang sudah terdaftar.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Penjualan</label>

                        <input type="date"
                               name="tanggal_penjualan"
                               class="form-control"
                               value="{{ old('tanggal_penjualan', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan</label>

                        <textarea name="keterangan"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Barang yang Dibeli</h6>

                <button type="button"
                        class="btn btn-sm btn-outline-success"
                        id="tambahBaris">
                    <i class="bi bi-plus-lg me-1"></i>
                    Tambah Barang
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle" id="tabelBarang">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 250px;">Barang</th>
                                <th>Stok</th>
                                <th>Harga Jual</th>
                                <th style="width: 120px;">Jumlah</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="daftarBarang">
                            <tr class="baris-barang">
                                <td>
                                    <select name="detail[0][barang_id]"
                                            class="form-select pilih-barang"
                                            required>
                                        <option value="">Pilih barang</option>

                                        @foreach($barang as $item)
                                            <option value="{{ $item->id }}"
                                                    data-stok="{{ $item->stok }}"
                                                    data-harga="{{ $item->harga_jual }}">
                                                {{ $item->kode_barang }}
                                                - {{ $item->nama_barang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="stok-barang">-</td>
                                <td class="harga-barang">-</td>

                                <td>
                                    <input type="number"
                                           name="detail[0][jumlah]"
                                           class="form-control jumlah-barang"
                                           min="1"
                                           value="1"
                                           required>
                                </td>

                                <td class="subtotal-barang">Rp 0</td>

                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger hapus-baris"
                                            disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <div class="text-end">
                        <div class="text-muted">Total Penjualan</div>
                        <h4 class="fw-bold text-success" id="totalPenjualan">
                            Rp 0
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Pembayaran</h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Metode Pembayaran
                        </label>

                        <select name="metode_pembayaran"
                                id="metodePembayaran"
                                class="form-select"
                                required>
                            <option value="">Pilih metode pembayaran</option>
                            <option value="Tunai"
                                @selected(old('metode_pembayaran') === 'Tunai')>
                                Tunai
                            </option>
                            <option value="Transfer"
                                @selected(old('metode_pembayaran') === 'Transfer')>
                                Transfer
                            </option>
                            <option value="QRIS"
                                @selected(old('metode_pembayaran') === 'QRIS')>
                                QRIS
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Status Pembayaran
                        </label>

                        <input type="text"
                               class="form-control"
                               value="Lunas"
                               readonly>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-success border mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Total yang harus dibayar</span>
                                <strong class="fs-5" id="ringkasanTotal">
                                    Rp 0
                                </strong>
                            </div>

                            <small class="d-block mt-2">
                                Pembayaran penjualan wajib dilakukan secara
                                penuh sesuai total transaksi.
                            </small>

<div id="informasiHadiah"
     class="alert alert-light border mt-3 mb-0"
     aria-live="polite">

    <div class="fw-semibold mb-1">
        <i class="bi bi-gift me-2"></i>
        Informasi Hadiah
    </div>

    <div id="isiInformasiHadiah" class="small text-muted">
        Pilih barang untuk memeriksa kelayakan hadiah pelanggan.
    </div>

</div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route($prefix . '.penjualan.index') }}"
               class="btn btn-outline-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i>
                Simpan Penjualan
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const daftarBarang = document.getElementById('daftarBarang');
    const tombolTambah = document.getElementById('tambahBaris');
    const informasiHadiah = document.getElementById('informasiHadiah');

const hadiahBarangUntukJs = @json($hadiahBarangUntukJs);

    let indeks = 1;

    function rupiah(nilai) {
        return 'Rp ' + Number(nilai || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 0
        });
    }

function perbaruiInformasiHadiah() {
    const daftarHakHadiah = new Map();

    let adaBarangDipilih = false;

    daftarBarang.querySelectorAll('.baris-barang').forEach(function (baris) {
        const pilihan = baris.querySelector('.pilih-barang');
        const jumlah = Number(
            baris.querySelector('.jumlah-barang').value || 0
        );

        if (!pilihan.value || jumlah <= 0) {
            return;
        }

        adaBarangDipilih = true;

        const daftarHadiah = hadiahBarangUntukJs[pilihan.value] || [];

        daftarHadiah.forEach(function (hadiah) {
            const jumlahHak = jumlah * hadiah.jumlah_per_unit;

            if (!daftarHakHadiah.has(hadiah.id)) {
                daftarHakHadiah.set(hadiah.id, {
                    nama: hadiah.nama,
                    jumlah: 0,
                    stok: hadiah.stok,
                });
            }

            const data = daftarHakHadiah.get(hadiah.id);

            data.jumlah += jumlahHak;
        });
    });

    const isi = document.getElementById('isiInformasiHadiah');

    isi.replaceChildren();

    if (!adaBarangDipilih) {
        informasiHadiah.className = 'alert alert-light border mt-3 mb-0';

        isi.textContent =
            'Pilih barang untuk memeriksa kelayakan hadiah pelanggan.';

        return;
    }

    if (daftarHakHadiah.size === 0) {
        informasiHadiah.className = 'alert alert-light border mt-3 mb-0';

        isi.textContent =
            'Barang yang dipilih belum memiliki ketentuan hadiah.';

        return;
    }

    let stokTidakCukup = false;

    daftarHakHadiah.forEach(function (hadiah) {
        if (hadiah.stok < hadiah.jumlah) {
            stokTidakCukup = true;
        }
    });

    informasiHadiah.className = stokTidakCukup
        ? 'alert alert-warning mt-3 mb-0'
        : 'alert alert-success mt-3 mb-0';

    const judul = document.createElement('div');
    judul.className = 'fw-semibold mb-2';

    judul.innerHTML = '<i class="bi bi-gift me-2"></i>';

    judul.appendChild(document.createTextNode(
        stokTidakCukup
            ? 'Pelanggan memenuhi syarat hadiah, tetapi stok perlu diperiksa.'
            : 'Pelanggan berhak mendapatkan hadiah!'
    ));

    isi.appendChild(judul);

    const daftar = document.createElement('ul');
    daftar.className = 'mb-0 ps-4';

    daftarHakHadiah.forEach(function (hadiah) {
        const item = document.createElement('li');

        item.textContent =
            hadiah.nama
            + ' sebanyak '
            + hadiah.jumlah
            + ' unit. '
            + (
                hadiah.stok >= hadiah.jumlah
                    ? 'Stok tersedia: ' + hadiah.stok
                    : 'Stok tidak mencukupi. Tersedia: ' + hadiah.stok
            );

        daftar.appendChild(item);
    });

    isi.appendChild(daftar);
}


    function hitungTotal() {
        let total = 0;

        daftarBarang.querySelectorAll('.baris-barang').forEach(function (baris) {
            const pilihan = baris.querySelector('.pilih-barang');
            const jumlahInput = baris.querySelector('.jumlah-barang');
            const opsi = pilihan.options[pilihan.selectedIndex];

            const stok = Number(opsi?.dataset.stok || 0);
            const harga = Number(opsi?.dataset.harga || 0);
            const jumlah = Number(jumlahInput.value || 0);

            baris.querySelector('.stok-barang').textContent =
                pilihan.value ? stok : '-';

            baris.querySelector('.harga-barang').textContent =
                pilihan.value ? rupiah(harga) : '-';

            if (pilihan.value) {
                jumlahInput.max = stok;
            } else {
                jumlahInput.removeAttribute('max');
            }

            const subtotal = harga * jumlah;

            baris.querySelector('.subtotal-barang').textContent =
                rupiah(subtotal);

            total += subtotal;
        });

        document.getElementById('totalPenjualan').textContent = rupiah(total);
        document.getElementById('ringkasanTotal').textContent = rupiah(total);
        perbaruiInformasiHadiah();
    }

    function perbaruiPilihan() {
        const semuaSelect = daftarBarang.querySelectorAll('.pilih-barang');

        const terpilih = Array.from(semuaSelect)
            .map(select => select.value)
            .filter(value => value !== '');

        semuaSelect.forEach(function (select) {
            Array.from(select.options).forEach(function (option) {
                if (!option.value) return;

                option.disabled =
                    terpilih.includes(option.value) &&
                    select.value !== option.value;
            });
        });
    }

    function pasangEvent(baris) {
        baris.querySelector('.pilih-barang').addEventListener('change', function () {
            perbaruiPilihan();
            hitungTotal();
        });

        baris.querySelector('.jumlah-barang').addEventListener('input', hitungTotal);

        baris.querySelector('.hapus-baris').addEventListener('click', function () {
            baris.remove();
            perbaruiPilihan();
            perbaruiTombolHapus();
            hitungTotal();
        });
    }

    function perbaruiTombolHapus() {
        const baris = daftarBarang.querySelectorAll('.baris-barang');

        baris.forEach(function (item) {
            item.querySelector('.hapus-baris').disabled = baris.length <= 1;
        });
    }

    tombolTambah.addEventListener('click', function () {
        const barisAwal = daftarBarang.querySelector('.baris-barang');
        const barisBaru = barisAwal.cloneNode(true);

        barisBaru.querySelector('.pilih-barang').name =
            `detail[${indeks}][barang_id]`;

        barisBaru.querySelector('.pilih-barang').value = '';

        barisBaru.querySelector('.jumlah-barang').name =
            `detail[${indeks}][jumlah]`;

        barisBaru.querySelector('.jumlah-barang').value = 1;

        barisBaru.querySelector('.stok-barang').textContent = '-';
        barisBaru.querySelector('.harga-barang').textContent = '-';
        barisBaru.querySelector('.subtotal-barang').textContent = 'Rp 0';

        daftarBarang.appendChild(barisBaru);

        indeks++;

        pasangEvent(barisBaru);
        perbaruiPilihan();
        perbaruiTombolHapus();
        hitungTotal();
    });

    pasangEvent(daftarBarang.querySelector('.baris-barang'));
    perbaruiTombolHapus();
    hitungTotal();
});
</script>
@endsection
