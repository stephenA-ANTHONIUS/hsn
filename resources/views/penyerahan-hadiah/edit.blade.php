@extends('layouts.app')

@section('title', 'Edit Penyerahan Hadiah')

@section('content')
@php
    $prefix = request()->routeIs('admin.*') ? 'admin' : 'kepala-toko';

    $barisHadiah = old(
        'hadiah',
        $penyerahanHadiah->detailPenyerahanHadiah->map(function ($detail) {
            return [
                'hadiah_id' => $detail->hadiah_id,
                'jumlah' => $detail->jumlah,
                'keterangan' => $detail->keterangan,
            ];
        })->toArray()
    );

    $hadiahTersediaUntukJs = $hadiah->map(function ($item) use ($hakHadiah) {
        return [
            'id' => $item->id,
            'nama' => $item->nama_hadiah,
            'stok' => $item->stok,
            'hak' => $hakHadiah[$item->id] ?? 0,
            'status' => $item->status,
        ];
    })->values();
@endphp

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Penyerahan Hadiah</h4>
            <p class="text-muted mb-0">
                Perbaiki data penyerahan hadiah yang sudah dicatat.
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

    <form action="{{ route($prefix . '.penyerahan-hadiah.update', $penyerahanHadiah->id) }}"
          method="POST"
          enctype="multipart/form-data"
          id="formEditPenyerahan">
        @csrf
        @method('PUT')

        {{-- INFORMASI PEMBELIAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Informasi Pembelian</h6>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Nomor Nota</label>
                        <div class="fw-semibold">
                            {{ $penyerahanHadiah->penjualan?->nomor_nota ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Pelanggan</label>
                        <div class="fw-semibold">
                            {{ $penyerahanHadiah->pelanggan?->nama_pelanggan ?? 'Pelanggan umum' }}
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Tanggal Pembelian</label>
                        <div class="fw-semibold">
                            {{ $penyerahanHadiah->penjualan?->tanggal_penjualan?->format('d-m-Y') ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border mb-0">
                    <strong>Barang yang dibeli:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($penyerahanHadiah->penjualan->detailPenjualan as $detail)
                            <li>
                                {{ $detail->barang->nama_barang }}
                                ({{ $detail->jumlah }} unit)
                            </li>
                        @endforeach
                    </ul>
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
                    Hak hadiah dihitung berdasarkan barang yang dibeli.
                    Jika jumlah hadiah melebihi hak pelanggan, alasan
                    pemberian tambahan wajib diisi pada hadiah tersebut.
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="30%">Nama Hadiah</th>
                                <th width="15%">Stok Tersedia</th>
                                <th width="15%">Hak Pelanggan</th>
                                <th width="15%">Jumlah</th>
                                <th width="20%">Alasan Pemberian Tambahan</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>

                        <tbody id="daftarHadiah">
                            @foreach ($barisHadiah as $index => $item)
                                <tr>
                                    <td>
                                        <select name="hadiah[{{ $index }}][hadiah_id]"
                                                class="form-select pilih-hadiah"
                                                required>
                                            <option value="">Pilih hadiah</option>

                                            @foreach ($hadiah as $pilihan)
                                                <option value="{{ $pilihan->id }}"
                                                        data-stok="{{ $pilihan->stok }}"
                                                        data-hak="{{ $hakHadiah[$pilihan->id] ?? 0 }}"
                                                    @selected($item['hadiah_id'] == $pilihan->id)>
                                                    {{ $pilihan->nama_hadiah }}
                                                    @if ($pilihan->status !== 'aktif')
                                                        (Nonaktif)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <span class="stok-hadiah text-muted">-</span>
                                    </td>

                                    <td>
                                        <span class="hak-hadiah text-muted">-</span>
                                    </td>

                                    <td>
                                        <input type="number"
                                               name="hadiah[{{ $index }}][jumlah]"
                                               class="form-control input-jumlah"
                                               min="1"
                                               value="{{ $item['jumlah'] }}"
                                               required>
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="hadiah[{{ $index }}][keterangan]"
                                               class="form-control input-alasan"
                                               maxlength="500"
                                               value="{{ $item['keterangan'] ?? '' }}"
                                               placeholder="Opsional jika sesuai hak pelanggan">
                                    </td>

                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger hapus-hadiah">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
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
                    Foto bukti wajib tersedia pada setiap penyerahan hadiah.
                    Foto lama tetap digunakan apabila tidak mengunggah foto baru.
                    Catatan penyerahan tidak dapat menggantikan foto bukti.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Tanggal Penyerahan
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_penyerahan"
                               class="form-control"
                               value="{{ old('tanggal_penyerahan', $penyerahanHadiah->tanggal_penyerahan->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Foto Bukti Penyerahan
                            @if (!$penyerahanHadiah->foto_bukti)
                                <span class="text-danger">*</span>
                            @endif
                        </label>

                        <input type="file"
                               name="foto_bukti"
                               id="foto_bukti"
                               class="form-control"
                               accept=".jpg,.jpeg,.png"
                               @if (!$penyerahanHadiah->foto_bukti) required @endif>

                        <small class="text-muted">
                            JPG, JPEG, atau PNG. Maksimal 2 MB.
                            @if ($penyerahanHadiah->foto_bukti)
                                Foto lama tetap digunakan jika tidak diganti.
                            @else
                                Foto wajib diunggah karena belum tersedia foto sebelumnya.
                            @endif
                        </small>

                        @if ($penyerahanHadiah->foto_bukti)
                            <div class="mt-3">
                                <div class="text-muted small mb-2">
                                    Foto bukti saat ini:
                                </div>

                                <a href="{{ asset('storage/' . $penyerahanHadiah->foto_bukti) }}"
                                   target="_blank">
                                    <img src="{{ asset('storage/' . $penyerahanHadiah->foto_bukti) }}"
                                         alt="Foto bukti penyerahan"
                                         style="max-width: 180px; max-height: 150px; object-fit: cover;"
                                         class="rounded border">
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Catatan Penyerahan
                        </label>

                        <textarea name="keterangan"
                                  id="keterangan"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Contoh: Hadiah diserahkan langsung kepada pelanggan di toko">{{ old('keterangan', $penyerahanHadiah->keterangan) }}</textarea>

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

            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    const hadiahTersedia = @json($hadiahTersediaUntukJs);

    const daftarHadiah = document.getElementById('daftarHadiah');

    const fotoLamaTersedia = @json((bool) $penyerahanHadiah->foto_bukti);

    let nomorBaris = {{ count($barisHadiah) }};

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

        const option = select.selectedOptions[0];

        const hak = option && option.value
            ? Number(option.dataset.hak || 0)
            : 0;

        const jumlah = Number(inputJumlah.value);

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

    function perbaruiInformasiBaris(row) {
        const select = row.querySelector('.pilih-hadiah');
        const option = select.selectedOptions[0];

        row.querySelector('.stok-hadiah').textContent =
            option && option.value ? option.dataset.stok : '-';

        row.querySelector('.hak-hadiah').textContent =
            option && option.value ? option.dataset.hak : '-';

        perbaruiValidasiAlasan(row);
    }

    function pasangEventBaris(row) {
        row.querySelector('.pilih-hadiah')
            .addEventListener('change', () => perbaruiInformasiBaris(row));

        row.querySelector('.input-jumlah')
            .addEventListener('input', () => perbaruiValidasiAlasan(row));

        row.querySelector('.hapus-hadiah')
            .addEventListener('click', () => {
                row.remove();
            });

        perbaruiInformasiBaris(row);
    }

    function tambahBarisHadiah() {
        const index = nomorBaris++;

        const options = hadiahTersedia.map(item => `
            <option value="${item.id}"
                    data-stok="${item.stok}"
                    data-hak="${item.hak}">
                ${escapeHtml(item.nama)}
                ${item.status !== 'aktif' ? '(Nonaktif)' : ''}
            </option>
        `).join('');

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <select name="hadiah[${index}][hadiah_id]"
                        class="form-select pilih-hadiah"
                        required>
                    <option value="">Pilih hadiah</option>
                    ${options}
                </select>
            </td>

            <td><span class="stok-hadiah text-muted">-</span></td>
            <td><span class="hak-hadiah text-muted">-</span></td>

            <td>
                <input type="number"
                       name="hadiah[${index}][jumlah]"
                       class="form-control input-jumlah"
                       min="1"
                       value="1"
                       required>
            </td>

            <td>
                <input type="text"
                       name="hadiah[${index}][keterangan]"
                       class="form-control input-alasan"
                       maxlength="500"
                       placeholder="Opsional jika sesuai hak pelanggan">
            </td>

            <td>
                <button type="button"
                        class="btn btn-sm btn-outline-danger hapus-hadiah">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        daftarHadiah.appendChild(row);
        pasangEventBaris(row);
    }

    // Pasang event dan validasi untuk baris hadiah yang sudah ada.
    daftarHadiah.querySelectorAll('tr').forEach(pasangEventBaris);

    // Tambah hadiah.
    document.getElementById('tambahHadiah')
        .addEventListener('click', tambahBarisHadiah);

    // Validasi sebelum menyimpan perubahan.
    document.getElementById('formEditPenyerahan')
        .addEventListener('submit', function (event) {
            const jumlahBaris = daftarHadiah.querySelectorAll('tr').length;

            const fotoInput = document.getElementById('foto_bukti');

            const fotoBaru =
                fotoInput.files && fotoInput.files.length > 0;

            if (jumlahBaris === 0) {
                event.preventDefault();
                alert('Pilih minimal satu hadiah.');
                return;
            }

            if (!fotoBaru && !fotoLamaTersedia) {
                event.preventDefault();
                alert('Foto bukti penyerahan hadiah wajib diunggah.');
                fotoInput.focus();
            }
        });
</script>
@endsection
