@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">Tambah Barang Masuk</h4>
        <p class="text-muted mb-0">Catat penerimaan barang dari Pusat Jakarta.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route(auth()->user()->role === 'admin' ? 'admin.barang-masuk.store' : 'kepala-toko.barang-masuk.store') }}"
          method="POST">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <strong>Informasi Transaksi</strong>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Nomor Nota <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_nota"
                               class="form-control"
                               value="{{ old('nomor_nota') }}"
                               placeholder="Masukkan nomor nota"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_masuk"
                        class="form-control"
                        min="{{ date('Y-m-d') }}"
                        value="{{ old('tanggal_masuk', date('Y-m-d')) }}"
                        required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control"
                                  rows="2"
                                  placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <strong>Detail Barang Masuk</strong>

                <button type="button" class="btn btn-sm btn-success" id="tambahBarang">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                </button>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table align-middle" id="tabelBarang">
                        <thead>
                            <tr>
                                <th style="min-width:220px">Nama Barang</th>
                                <th style="min-width:110px">Jumlah</th>

                                @if(auth()->user()->role === 'kepala_toko')
                                    <th style="min-width:150px">Harga Modal</th>
                                @endif

                                <th style="min-width:150px">Harga Jual</th>
                                <th style="width:70px">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="daftarBarang">
                            <tr class="baris-barang">
                                <td>
                                    <select name="detail[0][barang_id]"
                                            class="form-select pilih-barang"
                                            required>
                                        <option value="">Pilih Barang</option>

                                        @foreach($barang as $item)
                                            <option value="{{ $item->id }}"
                                                    data-harga="{{ $item->harga_jual }}">
                                                {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <input type="number"
                                           name="detail[0][jumlah]"
                                           class="form-control"
                                           min="1"
                                           value="1"
                                           required>
                                </td>

                                @if(auth()->user()->role === 'kepala_toko')
                                    <td>
                                        <input type="number"
                                               name="detail[0][harga_modal]"
                                               class="form-control"
                                               min="0"
                                               step="0.01"
                                               placeholder="Harga modal">
                                    </td>
                                @endif

                                <td>
                                    <input type="number"
                                           name="detail[0][harga_jual]"
                                           class="form-control harga-jual"
                                           min="0"
                                           step="0.01"
                                           required>
                                </td>

                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger hapus-barang"
                                            disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <small class="text-muted">
                    Pastikan barang dan jumlah yang dimasukkan sudah sesuai dengan nota penerimaan.
                </small>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route(auth()->user()->role === 'admin' ? 'admin.barang-masuk.index' : 'kepala-toko.barang-masuk.index') }}"
               class="btn btn-light border">
                Batal
            </a>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i> Simpan Transaksi
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const daftarBarang = document.getElementById('daftarBarang');
    const tombolTambah = document.getElementById('tambahBarang');

    let nomorBaris = 1;

    function perbaruiTombolHapus() {
        const baris = daftarBarang.querySelectorAll('.baris-barang');

        baris.forEach(function (item) {
            const tombol = item.querySelector('.hapus-barang');
            tombol.disabled = baris.length === 1;
        });
    }

    function isiHargaJual(select) {
        const baris = select.closest('.baris-barang');
        const option = select.options[select.selectedIndex];
        const harga = option ? option.dataset.harga : '';

        baris.querySelector('.harga-jual').value = harga || '';
    }

    daftarBarang.addEventListener('change', function (event) {
        if (event.target.classList.contains('pilih-barang')) {
            isiHargaJual(event.target);
        }
    });

    tombolTambah.addEventListener('click', function () {
        const baris = document.createElement('tr');
        baris.classList.add('baris-barang');

        baris.innerHTML = `
            <td>
                <select name="detail[${nomorBaris}][barang_id]"
                        class="form-select pilih-barang" required>
                    <option value="">Pilih Barang</option>

                    @foreach($barang as $item)
                        <option value="{{ $item->id }}"
                                data-harga="{{ $item->harga_jual }}">
                            {{ $item->kode_barang }} - {{ $item->nama_barang }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number"
                       name="detail[${nomorBaris}][jumlah]"
                       class="form-control"
                       min="1"
                       value="1"
                       required>
            </td>

            @if(auth()->user()->role === 'kepala_toko')
                <td>
                    <input type="number"
                           name="detail[${nomorBaris}][harga_modal]"
                           class="form-control"
                           min="0"
                           step="0.01"
                           placeholder="Harga modal">
                </td>
            @endif

            <td>
                <input type="number"
                       name="detail[${nomorBaris}][harga_jual]"
                       class="form-control harga-jual"
                       min="0"
                       step="0.01"
                       required>
            </td>

            <td>
                <button type="button"
                        class="btn btn-sm btn-outline-danger hapus-barang">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        daftarBarang.appendChild(baris);

        nomorBaris++;
        perbaruiTombolHapus();
    });

    daftarBarang.addEventListener('click', function (event) {
        const tombol = event.target.closest('.hapus-barang');

        if (tombol && !tombol.disabled) {
            tombol.closest('.baris-barang').remove();
            perbaruiTombolHapus();
        }
    });

    perbaruiTombolHapus();
});
</script>
@endsection
