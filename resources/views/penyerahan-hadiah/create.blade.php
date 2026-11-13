@extends('layouts.app')

@section('title', 'Penyerahan Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';
@endphp

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Penyerahan Hadiah</h4>
            <p class="text-muted mb-0">
                Catat penyerahan hadiah berdasarkan transaksi pembelian pelanggan.
            </p>
        </div>

        <a href="{{ route($prefix . '.penyerahan-hadiah.index') }}"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

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

    <form action="{{ route($prefix . '.penyerahan-hadiah.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="formPenyerahan">
        @csrf

        {{-- INFORMASI PEMBELIAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Informasi Pembelian</h6>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nota Penjualan <span class="text-danger">*</span>
                    </label>

                    <select name="penjualan_id"
                            id="penjualan_id"
                            class="form-select"
                            required>
                        <option value="">Pilih nota penjualan</option>

                        @foreach ($transaksiPenjualan as $item)
                            <option value="{{ $item['id'] }}"
                                @selected(old('penjualan_id') == $item['id'])>
                                {{ $item['nomor_nota'] }} -
                                {{ $item['pelanggan'] }} -
                                {{ $item['tanggal'] }}
                            </option>
                        @endforeach
                    </select>

                    @if ($transaksiPenjualan->isEmpty())
                        <small class="text-danger">
                            Belum ada nota penjualan yang tersedia untuk penyerahan hadiah.
                        </small>
                    @endif
                </div>

                <div id="informasiPembelian"
                     class="alert alert-light border d-none">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Nomor Nota</small>
                            <div class="fw-semibold" id="infoNota">-</div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Pelanggan</small>
                            <div class="fw-semibold" id="infoPelanggan">-</div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Tanggal Pembelian</small>
                            <div class="fw-semibold" id="infoTanggal">-</div>
                        </div>
                    </div>

                    <hr>

                    <strong>Barang yang Dibeli</strong>
                    <ul id="daftarBarang" class="mb-0 mt-2"></ul>
                </div>
            </div>
        </div>

        {{-- HADIAH YANG DISERAHKAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Hadiah yang Diserahkan</h6>

                <button type="button"
                        class="btn btn-sm btn-outline-success"
                        id="tambahHadiah">
                    + Tambah Hadiah
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    Pilihan hadiah dihitung berdasarkan barang dan jumlah pembelian.
                    Jika jumlah hadiah melebihi hak pelanggan, alasan pemberian
                    tambahan wajib diisi pada hadiah tersebut.
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="35%">Nama Hadiah</th>
                                <th width="15%">Stok Tersedia</th>
                                <th width="15%">Jumlah</th>
                                <th width="30%">Alasan Pemberian Tambahan</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>

                        <tbody id="daftarHadiah">
                            <tr id="barisKosong">
                                <td colspan="5"
                                    class="text-center text-muted py-4">
                                    Pilih nota penjualan untuk menampilkan
                                    hadiah yang berhak diterima.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- BUKTI PENYERAHAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Bukti Penyerahan</h6>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Perhatian:</strong>
                    Foto bukti wajib diunggah pada setiap penyerahan hadiah.
                    Catatan penyerahan bersifat opsional dan tidak menggantikan
                    kewajiban mengunggah foto.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Tanggal Penyerahan <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_penyerahan"
                               class="form-control"
                               value="{{ old('tanggal_penyerahan', now()->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Foto Bukti Penyerahan
                            <span class="text-danger">*</span>
                        </label>

                        <input type="file"
                               name="foto_bukti"
                               id="foto_bukti"
                               class="form-control"
                               accept=".jpg,.jpeg,.png"
                               required>

                        <small class="text-muted">
                            Format JPG, JPEG, atau PNG. Maksimal 2 MB.
                        </small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Catatan Penyerahan
                        </label>

                        <textarea name="keterangan"
                                  id="keterangan"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Contoh: Hadiah diserahkan langsung kepada pelanggan di toko">{{ old('keterangan') }}</textarea>

                        <small class="text-muted">
                            Catatan ini bersifat opsional.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOMBOL --}}
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route($prefix . '.penyerahan-hadiah.index') }}"
               class="btn btn-light border">
                Batal
            </a>

            <button type="submit"
                    class="btn btn-success"
                    @disabled($transaksiPenjualan->isEmpty())>
                Simpan Penyerahan Hadiah
            </button>
        </div>
    </form>
</div>

<script>
    const transaksiPenjualan = @json($transaksiPenjualan);
    const daftarHadiahTersedia = @json($hadiah);

    const pilihanPenjualan = document.getElementById('penjualan_id');
    const informasiPembelian = document.getElementById('informasiPembelian');
    const daftarBarang = document.getElementById('daftarBarang');
    const daftarHadiah = document.getElementById('daftarHadiah');

    let nomorBaris = 0;
    let hakHadiahAktif = new Map();

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    /*
     * Memeriksa apakah jumlah hadiah melebihi hak pelanggan.
     * Jika melebihi, alasan pemberian tambahan wajib diisi.
     */
    function perbaruiValidasiAlasan(row) {
        const select = row.querySelector('.pilih-hadiah');
        const inputJumlah = row.querySelector('.input-jumlah');
        const inputAlasan = row.querySelector('.input-alasan');

        const hadiahId = Number(select.value);
        const jumlah = Number(inputJumlah.value);

        const hak = hakHadiahAktif.get(hadiahId)?.jumlah ?? 0;

        const melebihiHak = select.value !== ''
            && jumlah > hak;

        inputAlasan.required = melebihiHak;

        inputAlasan.placeholder = melebihiHak
            ? 'Wajib isi alasan pemberian tambahan'
            : 'Opsional jika sesuai hak pelanggan';

        inputAlasan.title = melebihiHak
            ? 'Alasan wajib diisi karena jumlah hadiah melebihi hak pelanggan.'
            : 'Alasan tidak wajib jika jumlah hadiah sesuai hak pelanggan.';
    }

    function buatBarisHadiah(hadiahId = '', jumlah = 1, alasan = '') {
        const index = nomorBaris++;
        const row = document.createElement('tr');

        const pilihan = daftarHadiahTersedia.map(hadiah => `
            <option value="${hadiah.id}"
                    data-stok="${hadiah.stok}"
                    ${String(hadiah.id) === String(hadiahId) ? 'selected' : ''}>
                ${escapeHtml(hadiah.nama_hadiah)}
            </option>
        `).join('');

        row.innerHTML = `
            <td>
                <select name="hadiah[${index}][hadiah_id]"
                        class="form-select pilih-hadiah"
                        required>
                    <option value="">Pilih hadiah</option>
                    ${pilihan}
                </select>
            </td>

            <td>
                <span class="stok-hadiah text-muted">-</span>
            </td>

            <td>
                <input type="number"
                       name="hadiah[${index}][jumlah]"
                       class="form-control input-jumlah"
                       min="1"
                       value="${jumlah}"
                       required>
            </td>

            <td>
                <input type="text"
                       name="hadiah[${index}][keterangan]"
                       class="form-control input-alasan"
                       maxlength="500"
                       value="${escapeHtml(alasan)}"
                       placeholder="Opsional jika sesuai hak pelanggan">
            </td>

            <td>
                <button type="button"
                        class="btn btn-sm btn-outline-danger hapus-hadiah">
                    Hapus
                </button>
            </td>
        `;

        daftarHadiah.appendChild(row);

        const select = row.querySelector('.pilih-hadiah');

        function tampilkanStok() {
            const option = select.selectedOptions[0];

            row.querySelector('.stok-hadiah').textContent =
                option && option.value ? option.dataset.stok : '-';

            perbaruiValidasiAlasan(row);
        }

        select.addEventListener('change', tampilkanStok);

        row.querySelector('.input-jumlah')
            .addEventListener('input', () => perbaruiValidasiAlasan(row));

        row.querySelector('.hapus-hadiah').addEventListener('click', () => {
            row.remove();

            if (!daftarHadiah.querySelector('tr')) {
                tampilkanBarisKosong();
            }
        });

        tampilkanStok();
    }

    function tampilkanBarisKosong() {
        daftarHadiah.innerHTML = `
            <tr id="barisKosong">
                <td colspan="5"
                    class="text-center text-muted py-4">
                    Belum ada hadiah dipilih.
                </td>
            </tr>
        `;
    }

    function pilihTransaksi(id) {
        return transaksiPenjualan.find(item => String(item.id) === String(id));
    }

    function tampilkanTransaksi() {
        const transaksi = pilihTransaksi(pilihanPenjualan.value);

        daftarHadiah.innerHTML = '';
        daftarBarang.innerHTML = '';
        nomorBaris = 0;
        hakHadiahAktif = new Map();

        if (!transaksi) {
            informasiPembelian.classList.add('d-none');
            tampilkanBarisKosong();
            return;
        }

        informasiPembelian.classList.remove('d-none');

        document.getElementById('infoNota').textContent = transaksi.nomor_nota;
        document.getElementById('infoPelanggan').textContent = transaksi.pelanggan;
        document.getElementById('infoTanggal').textContent = transaksi.tanggal;

        const hakHadiah = new Map();

        transaksi.barang.forEach(barang => {
            const li = document.createElement('li');
            li.textContent = `${barang.nama} (${barang.jumlah} unit)`;
            daftarBarang.appendChild(li);

            barang.hadiah.forEach(hadiah => {
                const id = Number(hadiah.id);

                const jumlah =
                    Number(barang.jumlah) * Number(hadiah.jumlah_per_unit);

                const data = hakHadiah.get(id) || {
                    id: id,
                    jumlah: 0
                };

                data.jumlah += jumlah;
                hakHadiah.set(id, data);
            });
        });

        hakHadiahAktif = hakHadiah;

        hakHadiah.forEach(hadiah => {
            buatBarisHadiah(hadiah.id, hadiah.jumlah);
        });

        if (daftarHadiah.children.length === 0) {
            tampilkanBarisKosong();
        }
    }

    pilihanPenjualan.addEventListener('change', tampilkanTransaksi);

    document.getElementById('tambahHadiah').addEventListener('click', () => {
        const kosong = document.getElementById('barisKosong');

        if (kosong) {
            kosong.remove();
        }

        buatBarisHadiah();
    });

    document.getElementById('formPenyerahan')
        .addEventListener('submit', function (event) {
            const rows = daftarHadiah.querySelectorAll('tr');

            const foto =
                document.getElementById('foto_bukti').files.length > 0;

            if (
                !pilihanPenjualan.value ||
                rows.length === 0 ||
                document.getElementById('barisKosong')
            ) {
                event.preventDefault();
                alert('Pilih nota penjualan dan minimal satu hadiah.');
                return;
            }

            if (!foto) {
                event.preventDefault();
                alert('Foto bukti penyerahan hadiah wajib diunggah.');
                document.getElementById('foto_bukti').focus();
            }
        });

    if (pilihanPenjualan.value) {
        tampilkanTransaksi();
    }
</script>
@endsection
