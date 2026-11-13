@extends('layouts.app')

@section('title', 'Edit Penjualan')

@section('content')
@php
    $prefix = auth()->user()->role === 'admin'
        ? 'admin'
        : 'kepala-toko';

    $detailForm = old('detail', $penjualan->detailPenjualan
        ->map(fn ($detail) => [
            'barang_id' => $detail->barang_id,
            'jumlah' => $detail->jumlah,
        ])
        ->toArray());

    // Mengambil aturan hadiah aktif dari setiap barang.
    $barang->load([
        'hadiah' => function ($query) {
            $query->where('hadiah.status', 'aktif');
        },
    ]);

    $hadiahBarangUntukJs = $barang->mapWithKeys(function ($item) {
        return [
            $item->id => $item->hadiah->map(function ($hadiah) {
                return [
                    'id' => (int) $hadiah->id,
                    'nama' => $hadiah->nama_hadiah,
                    'jumlah_per_unit' => (int) $hadiah->pivot->jumlah_hadiah,
                    'stok' => (int) $hadiah->stok,
                ];
            })->values()->all(),
        ];
    })->all();
@endphp

<div class="container-fluid px-0">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Penjualan</h4>
            <p class="text-muted mb-0">
                Perbaiki data transaksi penjualan.
            </p>
        </div>

        <a href="{{ route($prefix . '.penjualan.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    {{-- NOTIFIKASI ERROR --}}
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

    {{-- INFORMASI HADIAH SUDAH DISERAHKAN --}}
    @if($adaPenyerahanHadiah)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Hadiah dari transaksi ini sudah diserahkan.
            Data pelanggan dan barang tidak dapat diubah.
            Anda masih dapat memperbaiki metode pembayaran dan keterangan.
        </div>
    @endif

    <form method="POST"
          action="{{ route($prefix . '.penjualan.update', $penjualan->id) }}"
          id="formEditPenjualan">

        @csrf
        @method('PUT')

        {{-- INFORMASI TRANSAKSI --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Informasi Transaksi</h5>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Nomor Nota</label>

                        <input type="text"
                               class="form-control"
                               value="{{ $penjualan->nomor_nota }}"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Penjualan</label>

                        <input type="text"
                               class="form-control"
                               value="{{ $penjualan->tanggal_penjualan->format('d/m/Y') }}"
                               readonly>

                        <small class="text-muted">
                            Tanggal transaksi tidak dapat diubah.
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pelanggan</label>

                        <select name="pelanggan_id"
                                class="form-select"
                                {{ $adaPenyerahanHadiah ? 'disabled' : '' }}>

                            <option value="">Pelanggan Umum</option>

                            @foreach($pelanggan as $item)
                                <option value="{{ $item->id }}"
                                    @selected(
                                        old('pelanggan_id', $penjualan->pelanggan_id) == $item->id
                                    )>
                                    {{ $item->nama_pelanggan }}
                                </option>
                            @endforeach
                        </select>

                        @if($adaPenyerahanHadiah)
                            <input type="hidden"
                                   name="pelanggan_id"
                                   value="{{ $penjualan->pelanggan_id }}">
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Metode Pembayaran</label>

                        <select name="metode_pembayaran"
                                class="form-select"
                                required>
                            <option value="Tunai"
                                @selected(old('metode_pembayaran', $penjualan->metode_pembayaran) === 'Tunai')>
                                Tunai
                            </option>

                            <option value="Transfer"
                                @selected(old('metode_pembayaran', $penjualan->metode_pembayaran) === 'Transfer')>
                                Transfer
                            </option>

                            <option value="QRIS"
                                @selected(old('metode_pembayaran', $penjualan->metode_pembayaran) === 'QRIS')>
                                QRIS
                            </option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Keterangan</label>

                        <input type="text"
                               name="keterangan"
                               class="form-control"
                               value="{{ old('keterangan', $penjualan->keterangan) }}"
                               placeholder="Keterangan tambahan (opsional)">
                    </div>

                </div>
            </div>
        </div>

        {{-- DETAIL BARANG --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Detail Barang</h5>

                        <small class="text-muted">
                            Pembayaran penjualan harus lunas.
                        </small>
                    </div>

                    @if(!$adaPenyerahanHadiah)
                        <button type="button"
                                class="btn btn-outline-success btn-sm"
                                id="tambahBarang">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Barang
                        </button>
                    @endif
                </div>

                <div id="daftarBarang">

                    @foreach($detailForm as $index => $detail)

                        @php
                            $barangTerpilih = $barang->firstWhere(
                                'id',
                                $detail['barang_id']
                            );
                        @endphp

                        <div class="row g-2 align-items-end mb-3 baris-barang">

                            <div class="col-md-6">
                                <label class="form-label">Barang</label>

                                @if($adaPenyerahanHadiah)

                                    <input type="hidden"
                                           name="detail[{{ $index }}][barang_id]"
                                           value="{{ $detail['barang_id'] }}">

                                    <input type="text"
                                           class="form-control"
                                           value="{{ $barangTerpilih->nama_barang ?? 'Barang tidak ditemukan' }}"
                                           readonly>

                                @else

                                    <select name="detail[{{ $index }}][barang_id]"
                                            class="form-select pilih-barang"
                                            required>

                                        <option value="">Pilih Barang</option>

                                        @foreach($barang as $item)
                                            <option value="{{ $item->id }}"
                                                    data-harga="{{ $item->harga_jual }}"
                                                    data-stok="{{ $item->stok }}"
                                                @selected($detail['barang_id'] == $item->id)>

                                                {{ $item->nama_barang }}
                                                - Rp {{ number_format($item->harga_jual, 0, ',', '.') }}

                                            </option>
                                        @endforeach

                                    </select>

                                @endif
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Jumlah</label>

                                <input type="number"
                                       name="detail[{{ $index }}][jumlah]"
                                       class="form-control jumlah-barang"
                                       min="1"
                                       value="{{ $detail['jumlah'] }}"
                                       required
                                       {{ $adaPenyerahanHadiah ? 'readonly' : '' }}>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>

                                <input type="text"
                                       class="form-control subtotal-barang"
                                       value="Rp {{ number_format(
                                           ($barangTerpilih->harga_jual ?? 0) * $detail['jumlah'],
                                           0,
                                           ',',
                                           '.'
                                       ) }}"
                                       readonly>
                            </div>

                            @if(!$adaPenyerahanHadiah)

                                <div class="col-md-1">
                                    <button type="button"
                                            class="btn btn-outline-danger w-100 hapus-barang">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>

                <hr>

                {{-- TOTAL PENJUALAN --}}
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total Penjualan</span>

                    <h4 class="fw-bold text-success mb-0"
                        id="totalPenjualan">
                        Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}
                    </h4>
                </div>

                <div class="mt-2 text-end">
                    <span class="badge bg-success">Pembayaran Lunas</span>
                </div>

            </div>
        </div>

        {{-- INFORMASI HAK HADIAH --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-gift me-2"></i>
                    Informasi Hak Hadiah
                </h6>
            </div>

            <div class="card-body">

                <div id="informasiHadiah"
                     class="alert alert-light border mb-0"
                     aria-live="polite">

                    <div id="isiInformasiHadiah" class="small text-muted">
                        Menghitung hak hadiah pelanggan...
                    </div>

                </div>

                <small class="text-muted d-block mt-2">
                    Hak hadiah dihitung berdasarkan jenis barang dan jumlah
                    yang tercantum pada nota penjualan.
                </small>

                @if($adaPenyerahanHadiah)
                    <div class="small text-warning mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Hadiah telah diserahkan. Informasi ini hanya untuk
                        melihat hak hadiah berdasarkan transaksi.
                    </div>
                @endif

            </div>
        </div>

        {{-- PEMBAYARAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Status Pembayaran</h5>

                <div class="alert alert-success mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total pembayaran</span>

                        <strong class="fs-5" id="ringkasanTotal">
                            Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}
                        </strong>
                    </div>

                    <small class="d-block mt-2">
                        Pembayaran penjualan wajib dilakukan secara penuh
                        sesuai total transaksi.
                    </small>
                </div>

            </div>
        </div>

        {{-- TOMBOL --}}
        <div class="d-flex justify-content-end gap-2 mb-4">

            <a href="{{ route($prefix . '.penjualan.index') }}"
               class="btn btn-outline-secondary">
                Batal
            </a>

            <button type="submit"
                    class="btn btn-success"
                    onclick="return confirm('Simpan perubahan transaksi penjualan ini?')">

                <i class="bi bi-save me-1"></i>
                Simpan Perubahan

            </button>

        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const daftarBarang = document.getElementById('daftarBarang');
    const totalPenjualan = document.getElementById('totalPenjualan');
    const ringkasanTotal = document.getElementById('ringkasanTotal');
    const informasiHadiah = document.getElementById('informasiHadiah');
    const isiInformasiHadiah = document.getElementById('isiInformasiHadiah');

    const tombolTambah = document.getElementById('tambahBarang');

    const adaPenyerahanHadiah = @json((bool) $adaPenyerahanHadiah);

    const hadiahBarangUntukJs = @json($hadiahBarangUntukJs);

    let nomorBaris = {{ count($detailForm) }};

    function formatRupiah(angka) {
        return 'Rp ' + Number(angka || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 0
        });
    }

    /*
     * Mengambil ID barang dari baris.
     * Mendukung select maupun hidden input ketika barang terkunci.
     */
    function ambilBarangId(baris) {
        const select = baris.querySelector('.pilih-barang');

        if (select) {
            return select.value;
        }

        const input = baris.querySelector('input[name$="[barang_id]"]');

        return input ? input.value : '';
    }

    /*
     * Menghitung total penjualan.
     * Hanya dijalankan ketika barang masih dapat diedit.
     */
    function hitungTotal() {

        if (adaPenyerahanHadiah) {
            return;
        }

        let total = 0;

        daftarBarang.querySelectorAll('.baris-barang').forEach(function (baris) {

            const select = baris.querySelector('.pilih-barang');
            const jumlahInput = baris.querySelector('.jumlah-barang');
            const subtotalInput = baris.querySelector('.subtotal-barang');

            if (!select || !jumlahInput || !subtotalInput) {
                return;
            }

            const option = select.options[select.selectedIndex];

            const harga = Number(option?.dataset.harga || 0);
            const jumlah = Number(jumlahInput.value || 0);

            const subtotal = harga * jumlah;

            subtotalInput.value = formatRupiah(subtotal);

            total += subtotal;
        });

        totalPenjualan.textContent = formatRupiah(total);
        ringkasanTotal.textContent = formatRupiah(total);
    }

    /*
     * Menghitung hak hadiah berdasarkan seluruh barang dalam transaksi.
     *
     * Apabila barang yang berbeda memberikan hadiah yang sama,
     * jumlah hak hadiah akan dijumlahkan.
     */
    function perbaruiInformasiHadiah() {

        const daftarHakHadiah = new Map();

        let adaBarangDipilih = false;

        daftarBarang.querySelectorAll('.baris-barang').forEach(function (baris) {

            const barangId = ambilBarangId(baris);

            const inputJumlah = baris.querySelector('.jumlah-barang');

            const jumlah = Number(inputJumlah?.value || 0);

            if (!barangId || jumlah <= 0) {
                return;
            }

            adaBarangDipilih = true;

            const daftarHadiah = hadiahBarangUntukJs[barangId] || [];

            daftarHadiah.forEach(function (hadiah) {

                const jumlahHak = jumlah * Number(hadiah.jumlah_per_unit);

                if (!daftarHakHadiah.has(hadiah.id)) {
                    daftarHakHadiah.set(hadiah.id, {
                        nama: hadiah.nama,
                        jumlah: 0,
                        stok: Number(hadiah.stok)
                    });
                }

                const data = daftarHakHadiah.get(hadiah.id);

                data.jumlah += jumlahHak;

            });
        });

        isiInformasiHadiah.replaceChildren();

        /*
         * Belum ada barang yang dipilih.
         */
        if (!adaBarangDipilih) {

            informasiHadiah.className =
                'alert alert-light border mb-0';

            isiInformasiHadiah.textContent =
                'Pilih barang untuk memeriksa hak hadiah pelanggan.';

            return;
        }

        /*
         * Barang tidak memiliki aturan hadiah.
         */
        if (daftarHakHadiah.size === 0) {

            informasiHadiah.className =
                'alert alert-light border mb-0';

            isiInformasiHadiah.textContent =
                'Barang yang dibeli pelanggan tidak memiliki ketentuan hadiah aktif.';

            return;
        }

        /*
         * Memeriksa kecukupan stok hadiah.
         */
        let stokTidakCukup = false;

        daftarHakHadiah.forEach(function (hadiah) {

            if (hadiah.stok < hadiah.jumlah) {
                stokTidakCukup = true;
            }

        });

        informasiHadiah.className = stokTidakCukup
            ? 'alert alert-warning mb-0'
            : 'alert alert-success mb-0';

        /*
         * Judul informasi.
         */
        const judul = document.createElement('div');

        judul.className = 'fw-semibold mb-2';

        judul.textContent = stokTidakCukup
            ? 'Pelanggan memiliki hak hadiah, tetapi stok perlu diperiksa.'
            : 'Pelanggan berhak mendapatkan hadiah!';

        isiInformasiHadiah.appendChild(judul);

        /*
         * Daftar hadiah.
         */
        const daftar = document.createElement('ul');

        daftar.className = 'mb-0 ps-4';

        daftarHakHadiah.forEach(function (hadiah) {

            const item = document.createElement('li');

            item.textContent =
                hadiah.nama
                + ' sebanyak '
                + hadiah.jumlah
                + ' buah. '
                + (
                    hadiah.stok >= hadiah.jumlah
                        ? 'Stok tersedia: ' + hadiah.stok
                        : 'Stok tidak mencukupi. Tersedia: ' + hadiah.stok
                );

            daftar.appendChild(item);

        });

        isiInformasiHadiah.appendChild(daftar);

    }

    /*
     * Mencegah satu barang dipilih lebih dari satu baris.
     */
    function perbaruiPilihan() {

        const semuaSelect = daftarBarang.querySelectorAll('.pilih-barang');

        const terpilih = Array.from(semuaSelect)
            .map(select => select.value)
            .filter(value => value !== '');

        semuaSelect.forEach(function (select) {

            Array.from(select.options).forEach(function (option) {

                if (!option.value) {
                    return;
                }

                option.disabled =
                    terpilih.includes(option.value)
                    && select.value !== option.value;

            });

        });

    }

    /*
     * Mengatur tombol hapus.
     */
    function perbaruiTombolHapus() {

        const baris = daftarBarang.querySelectorAll('.baris-barang');

        baris.forEach(function (item) {

            const tombol = item.querySelector('.hapus-barang');

            if (tombol) {
                tombol.disabled = baris.length <= 1;
            }

        });

    }

    /*
     * Membuat baris barang baru.
     */
    function buatBaris() {

        const index = nomorBaris++;

        const baris = document.createElement('div');

        baris.className =
            'row g-2 align-items-end mb-3 baris-barang';

        baris.innerHTML = `
            <div class="col-md-6">

                <label class="form-label">Barang</label>

                <select name="detail[${index}][barang_id]"
                        class="form-select pilih-barang"
                        required>

                    <option value="">Pilih Barang</option>

                    @foreach($barang as $item)
                        @if($item->status === 'aktif')

                            <option value="{{ $item->id }}"
                                    data-harga="{{ $item->harga_jual }}"
                                    data-stok="{{ $item->stok }}">

                                {{ $item->nama_barang }}
                                - Rp {{ number_format($item->harga_jual, 0, ',', '.') }}

                            </option>

                        @endif
                    @endforeach

                </select>

            </div>

            <div class="col-md-2">

                <label class="form-label">Jumlah</label>

                <input type="number"
                       name="detail[${index}][jumlah]"
                       class="form-control jumlah-barang"
                       min="1"
                       value="1"
                       required>

            </div>

            <div class="col-md-3">

                <label class="form-label">Subtotal</label>

                <input type="text"
                       class="form-control subtotal-barang"
                       value="Rp 0"
                       readonly>

            </div>

            <div class="col-md-1">

                <button type="button"
                        class="btn btn-outline-danger w-100 hapus-barang">

                    <i class="bi bi-trash"></i>

                </button>

            </div>
        `;

        daftarBarang.appendChild(baris);

        perbaruiPilihan();
        perbaruiTombolHapus();
        hitungTotal();
        perbaruiInformasiHadiah();

    }

    /*
     * Tombol tambah barang.
     */
    if (tombolTambah) {

        tombolTambah.addEventListener('click', buatBaris);

    }

    /*
     * Event perubahan pilihan barang.
     */
    daftarBarang.addEventListener('change', function (event) {

        if (event.target.classList.contains('pilih-barang')) {

            perbaruiPilihan();
            hitungTotal();
            perbaruiInformasiHadiah();

        }

    });

    /*
     * Event perubahan jumlah barang.
     */
    daftarBarang.addEventListener('input', function (event) {

        if (event.target.classList.contains('jumlah-barang')) {

            hitungTotal();
            perbaruiInformasiHadiah();

        }

    });

    /*
     * Event hapus barang.
     */
    daftarBarang.addEventListener('click', function (event) {

        const tombol = event.target.closest('.hapus-barang');

        if (!tombol) {
            return;
        }

        const baris = tombol.closest('.baris-barang');

        if (
            daftarBarang.querySelectorAll('.baris-barang').length > 1
        ) {

            baris.remove();

            perbaruiPilihan();
            perbaruiTombolHapus();
            hitungTotal();
            perbaruiInformasiHadiah();

        } else {

            alert('Minimal harus ada satu barang dalam transaksi.');

        }

    });

    /*
     * Hitung seluruh informasi saat halaman pertama dibuka.
     */
    perbaruiPilihan();
    perbaruiTombolHapus();
    hitungTotal();
    perbaruiInformasiHadiah();

});
</script>
@endsection
