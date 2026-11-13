@extends('layouts.app')

@section('content')

@php
    $statusLabel = [
        'diterima' => 'Diterima',
        'pemeriksaan' => 'Pemeriksaan',
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'proses' => 'Dalam Proses',
        'selesai' => 'Selesai',
        'diambil' => 'Sudah Diambil',
        'dibatalkan' => 'Dibatalkan',
    ];

    $statusClass = [
        'diterima' => 'secondary',
        'pemeriksaan' => 'info',
        'menunggu_persetujuan' => 'warning',
        'proses' => 'primary',
        'selesai' => 'success',
        'diambil' => 'success',
        'dibatalkan' => 'danger',
    ];

    $totalBiaya = (float) $servis->total_biaya;
    $nominalDibayar = (float) $servis->nominal_dibayar;
    $sisaTagihan = max(0, $totalBiaya - $nominalDibayar);
    $totalSparepart = (float) $servis->detailServis->sum('subtotal');

    $menungguPersetujuan = $servis->status_perbaikan === 'menunggu_persetujuan';
    $dibatalkan = $servis->status_perbaikan === 'dibatalkan';

    $statusPembayaranLabel = [
        'belum_dibayar' => 'Belum Dibayar',
        'sebagian' => 'Dibayar Sebagian',
        'lunas' => 'Lunas',
    ];

    $statusPembayaranClass = [
        'belum_dibayar' => 'secondary',
        'sebagian' => 'warning',
        'lunas' => 'success',
    ];

    $bolehBayar = in_array($servis->status_perbaikan, [
        'proses',
        'selesai',
        'diambil',
        'dibatalkan',
    ], true) && $sisaTagihan > 0;

$barangTersediaUntukJs = $barang->map(function ($item) {
    return [
        'id' => $item->id,
        'nama' => $item->nama_barang,
        'kode' => $item->kode_barang,
        'stok' => (int) $item->stok,
        'harga' => (float) $item->harga_jual,
        'satuan' => $item->satuan->nama_satuan ?? '',

        'hadiah' => $item->hadiah->map(function ($hadiah) {
            return [
                'id' => (int) $hadiah->id,
                'nama' => $hadiah->nama_hadiah,
                'jumlah_per_unit' => (int) $hadiah->pivot->jumlah_hadiah,
                'stok' => (int) $hadiah->stok,
            ];
        })->values()->all(),
    ];
})->values();

    $detailAwalUntukJs = $servis->detailServis->map(function ($detail) {
        return [
            'barang_id' => $detail->barang_id,
            'jumlah' => (int) $detail->jumlah,
        ];
    })->values();
@endphp

<style>
    .servis-detail {
        color: #26332D;
    }

    .servis-detail .page-title {
        color: #172D27;
        font-weight: 700;
    }

    .servis-detail .panel {
        background: #fff;
        border: 1px solid #DCE2DD;
        border-radius: 12px;
    }

    .servis-detail .section-title {
        color: #28483A;
        font-weight: 700;
    }

    .servis-detail .label {
        color: #78817B;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .servis-detail .value {
        font-weight: 600;
    }

    .servis-detail .btn-hsn {
        background: #28483A;
        border-color: #28483A;
        color: #fff;
    }

    .servis-detail .btn-hsn:hover {
        background: #172D27;
        color: #fff;
    }

    .servis-detail .summary {
        background: #F4F5F1;
        border: 1px solid #DCE2DD;
        border-radius: 10px;
    }

    .servis-detail .baris-sparepart {
        background: #fff;
        border: 1px solid #DCE2DD;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
    }

    .servis-detail .subtotal-barang {
        min-height: 38px;
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border: 1px solid #DCE2DD;
        border-radius: 0.375rem;
        background: #F4F5F1;
    }
</style>

<div class="container-fluid servis-detail">

    <div class="mb-4">
        <a href="{{ route($routePrefix . '.servis.index') }}"
            class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke transaksi servis
        </a>

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mt-2">
            <div>
                <h4 class="page-title mb-1">Detail Transaksi Servis</h4>
                <div class="text-muted small">{{ $servis->nomor_nota }}</div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge fs-6 text-bg-{{ $statusClass[$servis->status_perbaikan] ?? 'secondary' }}">
                    {{ $statusLabel[$servis->status_perbaikan] ?? $servis->status_perbaikan }}
                </span>

                <a href="{{ route($routePrefix . '.servis.nota', $servis->id) }}"
                    target="_blank"
                    class="btn btn-outline-success btn-sm">
                    <i class="bi bi-printer me-1"></i>
                    Cetak Nota
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Terdapat kesalahan:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFORMASI TRANSAKSI --}}
    <div class="panel p-4 mb-3">
        <div class="section-title mb-3">
            <i class="bi bi-receipt me-2"></i>Informasi Transaksi
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="label">Nomor Nota</div>
                <div class="value">{{ $servis->nomor_nota }}</div>
            </div>

            <div class="col-md-4">
                <div class="label">Tanggal Servis</div>
                <div class="value">{{ $servis->tanggal_servis->format('d/m/Y') }}</div>
            </div>

            <div class="col-md-4">
                <div class="label">Petugas Penerima</div>
                <div class="value">{{ $servis->pengguna->name ?? '-' }}</div>
            </div>

            <div class="col-md-4">
                <div class="label">Nama Pelanggan</div>
                <div class="value">{{ $servis->pelanggan->nama_pelanggan ?? '-' }}</div>
            </div>

            <div class="col-md-4">
                <div class="label">Nomor Telepon</div>
                <div class="value">{{ $servis->pelanggan->no_telepon ?? '-' }}</div>
            </div>

            <div class="col-md-4">
                <div class="label">Teknisi</div>
                <div class="value">{{ $servis->nama_teknisi ?: '-' }}</div>
            </div>
        </div>
    </div>

    {{-- INFORMASI KELUHAN --}}
    <div class="panel p-4 mb-3">
        <div class="section-title mb-3">
            <i class="bi bi-clipboard2-pulse me-2"></i>Informasi Perbaikan
        </div>

        <div class="mb-3">
            <div class="label">Keluhan Pelanggan</div>
            <div>{{ $servis->keluhan }}</div>
        </div>

        <div class="mb-3">
            <div class="label">Hasil Pemeriksaan</div>
            <div>{{ $servis->hasil_pemeriksaan ?: 'Belum ada hasil pemeriksaan.' }}</div>
        </div>

        <div>
            <div class="label">Keterangan Tambahan</div>
            <div>{{ $servis->keterangan ?: 'Belum ada keterangan tambahan.' }}</div>
        </div>
    </div>

    {{-- FORM PEMERIKSAAN DAN SPAREPART --}}
    @if($menungguPersetujuan)
        <div class="panel p-4 mb-3">

            <div class="section-title mb-2">
                <i class="bi bi-pencil-square me-2"></i>Lengkapi Data Servis
            </div>

            <p class="text-muted small mb-3">
                Teknisi dapat mengisi hasil pemeriksaan, biaya jasa, dan sparepart setelah memeriksa perangkat.
                Data masih dapat diperbaiki selama pelanggan belum menyetujui servis.
            </p>

            <form method="POST"
                action="{{ route($routePrefix . '.servis.update-data', $servis->id) }}"
                id="formDataServis">

                @csrf
                @method('PATCH')

                <input type="hidden" name="detail_servis_dikirim" value="1">

                <div class="mb-3">
                    <label for="hasil_pemeriksaan" class="form-label fw-semibold">
                        Hasil Pemeriksaan
                    </label>

                    <textarea name="hasil_pemeriksaan"
                        id="hasil_pemeriksaan"
                        rows="3"
                        class="form-control"
                        placeholder="Masukkan hasil pemeriksaan perangkat">{{ old('hasil_pemeriksaan', $servis->hasil_pemeriksaan) }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="biaya_jasa" class="form-label fw-semibold">
                        Biaya Jasa (Rp)
                    </label>

                    <input type="number"
                        name="biaya_jasa"
                        id="biaya_jasa"
                        class="form-control"
                        min="0"
                        step="1"
                        value="{{ old('biaya_jasa', $servis->biaya_jasa) }}"
                        placeholder="Masukkan biaya jasa jika sudah diketahui">

                    <small class="text-muted">
                        Biaya jasa dapat diisi setelah teknisi mengetahui tingkat kerusakan perangkat.
                    </small>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <div class="section-title">
                            <i class="bi bi-box-seam me-2"></i>Sparepart yang Dibutuhkan
                        </div>
                        <div class="small text-muted">
                            Pilih sparepart yang perlu diganti beserta jumlahnya.
                            Stok belum dikurangi pada tahap pemeriksaan.
                        </div>
                    </div>

                    <button type="button"
                        class="btn btn-outline-success btn-sm"
                        id="tambahSparepart">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Sparepart
                    </button>
                </div>

                <div id="daftarSparepart"></div>

<div id="informasiHadiahServis"
     class="alert alert-light border mt-3 mb-0"
     aria-live="polite">

    <div class="fw-semibold mb-1">
        <i class="bi bi-gift me-2"></i>
        Informasi Hadiah
    </div>

    <div id="isiInformasiHadiahServis" class="small text-muted">
        Pilih sparepart untuk memeriksa kelayakan hadiah pelanggan.
    </div>

</div>


                <div class="summary p-3 mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Sparepart</span>
                        <strong id="totalSparepart">Rp 0</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Biaya Jasa</span>
                        <strong id="ringkasanJasa">Rp 0</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Total Biaya Servis</span>
                        <strong class="fs-5" style="color:#28483A" id="totalServis">
                            Rp 0
                        </strong>
                    </div>
                </div>

                <div class="mt-3">
                    <label for="keterangan" class="form-label fw-semibold">
                        Keterangan Tambahan
                    </label>

                    <textarea name="keterangan"
                        id="keterangan"
                        rows="3"
                        class="form-control"
                        placeholder="Masukkan keterangan tambahan jika ada">{{ old('keterangan', $servis->keterangan) }}</textarea>
                </div>

                <button type="submit" class="btn btn-hsn mt-3">
                    <i class="bi bi-save me-1"></i>Simpan Data Pemeriksaan
                </button>
            </form>
        </div>
    @endif

    {{-- DAFTAR SPAREPART --}}
    <div class="panel mb-3">
        <div class="p-4 border-bottom">
            <div class="section-title">Daftar Sparepart Servis</div>
            <div class="small text-muted">
                Daftar sparepart yang tercatat pada transaksi ini.
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Sparepart</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Persetujuan</th>
                        <th class="pe-4">Penggunaan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($servis->detailServis as $detail)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">
                                    {{ $detail->barang->nama_barang ?? '-' }}
                                </div>
                                <small class="text-muted">
                                    {{ $detail->barang->kode_barang ?? '' }}
                                </small>
                            </td>

                            <td>
                                {{ $detail->jumlah }}
                                {{ $detail->barang->satuan->nama_satuan ?? '' }}
                            </td>

                            <td>
                                Rp {{ number_format((float) $detail->harga_sparepart, 0, ',', '.') }}
                            </td>

                            <td class="fw-semibold">
                                Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                            </td>

                            <td>
                                @if($detail->status_persetujuan === 'disetujui')
                                    <span class="badge text-bg-success">Disetujui</span>
                                @elseif($detail->status_persetujuan === 'ditolak')
                                    <span class="badge text-bg-danger">Ditolak</span>
                                @else
                                    <span class="badge text-bg-warning">Menunggu</span>
                                @endif
                            </td>

                            <td class="pe-4">
                                @if($detail->status_penggunaan === 'digunakan')
                                    <span class="badge text-bg-primary">Digunakan</span>
                                @elseif($detail->status_penggunaan === 'tidak_digunakan')
                                    <span class="badge text-bg-secondary">Tidak Digunakan</span>
                                @else
                                    <span class="badge text-bg-light border text-dark">Diminta</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada sparepart yang dicatat. Lengkapi setelah teknisi memeriksa perangkat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-top">
            <div class="row justify-content-end">
                <div class="col-md-5 col-lg-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Biaya Jasa</span>
                        <span>Rp {{ number_format((float) $servis->biaya_jasa, 0, ',', '.') }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Sparepart</span>
                        @if($dibatalkan)
                            <span class="text-muted">Tidak ditagihkan</span>
                        @else
                            <span>Rp {{ number_format($totalSparepart, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <strong>Total Biaya</strong>
                        <strong class="fs-5" style="color:#28483A">
                            Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- INFORMASI PEMBAYARAN --}}
    <div class="panel p-4 mb-3">
        <div class="section-title mb-3">
            <i class="bi bi-wallet2 me-2"></i>Informasi Pembayaran
        </div>

        @if($dibatalkan)
            <div class="alert alert-warning">
                Servis dibatalkan sebelum pengerjaan dimulai. Pelanggan hanya dikenakan biaya jasa sebesar
                <strong>Rp {{ number_format((float) $servis->biaya_jasa, 0, ',', '.') }}</strong>.
                Sparepart tidak ditagihkan dan stok tidak dikurangi.
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="label">Total Biaya</div>
                <div class="value fs-5">
                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="label">Sudah Dibayar</div>
                <div class="value fs-5 text-success">
                    Rp {{ number_format($nominalDibayar, 0, ',', '.') }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="label">Sisa Tagihan</div>
                <div class="value fs-5 text-danger">
                    Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="mb-3">
            <span class="text-muted me-2">Status Pembayaran:</span>
            <span class="badge text-bg-{{ $statusPembayaranClass[$servis->status_pembayaran] ?? 'secondary' }}">
                {{ $statusPembayaranLabel[$servis->status_pembayaran] ?? $servis->status_pembayaran }}
            </span>
        </div>

        @if($bolehBayar)
            <hr>

            <h6 class="section-title mb-3">Catat Pembayaran</h6>

            @if($dibatalkan)
                <p class="text-muted small">
                    Pembayaran ini digunakan untuk melunasi biaya jasa pembatalan servis.
                </p>
            @endif

            <form method="POST"
                action="{{ route($routePrefix . '.servis.pembayaran', $servis->id) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nominal Pembayaran</label>

                        <input type="number"
                            name="nominal"
                            class="form-control"
                            min="0.01"
                            max="{{ $sisaTagihan }}"
                            step="0.01"
                            value="{{ old('nominal') }}"
                            placeholder="Masukkan nominal"
                            required>

                        @error('nominal')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <small class="text-muted">
                            Maksimal Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Metode Pembayaran</label>

                        <select name="metode_pembayaran" class="form-select" required>
                            <option value="">Pilih Metode</option>
                            <option value="Tunai" @selected(old('metode_pembayaran') === 'Tunai')>Tunai</option>
                            <option value="Transfer" @selected(old('metode_pembayaran') === 'Transfer')>Transfer</option>
                            <option value="QRIS" @selected(old('metode_pembayaran') === 'QRIS')>QRIS</option>
                        </select>

                        @error('metode_pembayaran')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Keterangan (Opsional)</label>

                        <input type="text"
                            name="keterangan"
                            class="form-control"
                            value="{{ old('keterangan') }}"
                            placeholder="Contoh: Pembayaran pertama">

                        @error('keterangan')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-hsn mt-3">
                    <i class="bi bi-check-circle me-1"></i>
                    Simpan Pembayaran
                </button>
            </form>

        @elseif($menungguPersetujuan)
            <div class="alert alert-warning mb-0">
                Pembayaran dapat dicatat setelah pelanggan menyetujui atau membatalkan servis.
            </div>

        @elseif($sisaTagihan <= 0)
            <div class="alert alert-success mb-0">
                Tagihan servis sudah lunas.
            </div>
        @endif
    </div>

    {{-- RIWAYAT PEMBAYARAN --}}
    <div class="panel mb-3">
        <div class="p-4 border-bottom">
            <div class="section-title">
                <i class="bi bi-clock-history me-2"></i>Riwayat Pembayaran
            </div>
            <div class="small text-muted">
                Daftar pembayaran yang pernah dicatat untuk transaksi ini.
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Petugas</th>
                        <th class="pe-4">Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($servis->pendapatan->sortBy('created_at') as $pembayaran)
                        <tr>
                            <td class="ps-4">
                                {{ $pembayaran->tanggal_pendapatan->format('d/m/Y') }}
                            </td>

                            <td class="fw-semibold text-success">
                                Rp {{ number_format((float) $pembayaran->nominal, 0, ',', '.') }}
                            </td>

                            <td>{{ $pembayaran->metode_pembayaran ?? '-' }}</td>
                            <td>{{ $pembayaran->pengguna->name ?? '-' }}</td>
                            <td class="pe-4">{{ $pembayaran->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada pembayaran yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PERUBAHAN STATUS --}}
    <div class="panel p-4 mb-3">
        <div class="section-title mb-2">Perbarui Status Servis</div>

        @if($menungguPersetujuan)
            <div class="alert alert-warning small">
                Pastikan hasil pemeriksaan, biaya jasa, dan sparepart sudah dilengkapi serta diinformasikan kepada pelanggan.
                Jika pelanggan menyetujui, pilih <strong>Mulai Proses</strong>.
                Stok sparepart akan langsung dikurangi. Jika pelanggan menolak, pilih
                <strong>Batalkan Servis</strong>. Biaya jasa tetap ditagihkan, tetapi stok sparepart tidak dikurangi.
            </div>

            <div class="d-flex flex-wrap gap-2">
                <form method="POST"
                    action="{{ route($routePrefix . '.servis.update-status', $servis->id) }}"
                    onsubmit="return confirm('Mulai proses servis dan kurangi stok sparepart sesuai daftar?');">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status_perbaikan" value="proses">

                    <button type="submit" class="btn btn-hsn">
                        <i class="bi bi-play-fill me-1"></i>Mulai Proses
                    </button>
                </form>

                <form method="POST"
                    action="{{ route($routePrefix . '.servis.update-status', $servis->id) }}"
                    onsubmit="return confirm('Batalkan servis ini? Pelanggan tetap dikenakan biaya jasa dan sparepart tidak akan ditagihkan.');">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status_perbaikan" value="dibatalkan">

                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i>Batalkan Servis
                    </button>
                </form>
            </div>

        @elseif($servis->status_perbaikan === 'proses')
            <p class="text-muted small">
                Servis sedang dikerjakan. Setelah perbaikan selesai, perbarui statusnya.
            </p>

            <form method="POST"
                action="{{ route($routePrefix . '.servis.update-status', $servis->id) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status_perbaikan" value="selesai">

                <button type="submit" class="btn btn-hsn">
                    <i class="bi bi-check-circle me-1"></i>Tandai Selesai
                </button>
            </form>

        @elseif($servis->status_perbaikan === 'selesai')
            @if($servis->status_pembayaran === 'lunas' && $nominalDibayar >= $totalBiaya)
                <p class="text-muted small">
                    Pembayaran sudah lunas. Perangkat dapat diserahkan kepada pelanggan.
                </p>

                <form method="POST"
                    action="{{ route($routePrefix . '.servis.update-status', $servis->id) }}">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status_perbaikan" value="diambil">

                    <button type="submit" class="btn btn-hsn">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        Tandai Sudah Diambil
                    </button>
                </form>
            @else
                <div class="alert alert-warning mb-0">
                    Perangkat belum dapat diserahkan karena pembayaran belum lunas.
                    Sisa tagihan:
                    <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
                </div>
            @endif

        @elseif($dibatalkan)
            <div class="text-danger small">
                Transaksi servis telah dibatalkan. Sparepart tidak ditagihkan dan stok tidak dikurangi.
                Tagihan yang tersisa hanya biaya jasa.
            </div>

        @elseif($servis->status_perbaikan === 'diambil')
            <div class="text-success small">
                Perangkat sudah diserahkan kepada pelanggan. Transaksi servis telah selesai.
            </div>

        @else
            <div class="text-muted small">
                Tidak ada perubahan status yang tersedia.
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-start mb-4">
        <a href="{{ route($routePrefix . '.servis.index') }}"
            class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Transaksi Servis
        </a>
    </div>

</div>

@if($menungguPersetujuan)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barangTersedia = @json($barangTersediaUntukJs);
            const detailAwal = @json($detailAwalUntukJs);

            const daftar = document.getElementById('daftarSparepart');
            const tombolTambah = document.getElementById('tambahSparepart');
            const biayaJasa = document.getElementById('biaya_jasa');
            const informasiHadiah = document.getElementById(
    'informasiHadiahServis'
);

            let nomorBaris = 0;

            const rupiah = angka => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(angka || 0);


function perbaruiInformasiHadiah() {
    const daftarHakHadiah = new Map();

    let adaBarangDipilih = false;

    daftar.querySelectorAll('.baris-sparepart').forEach(baris => {
        const select = baris.querySelector('.pilih-barang');

        const jumlah = Number(
            baris.querySelector('.jumlah-barang').value || 0
        );

        const barang = barangTersedia.find(
            item => String(item.id) === select.value
        );

        if (!barang || jumlah <= 0) {
            return;
        }

        adaBarangDipilih = true;

        (barang.hadiah || []).forEach(hadiah => {
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

    const isi = document.getElementById(
        'isiInformasiHadiahServis'
    );

    isi.replaceChildren();

    if (!adaBarangDipilih) {
        informasiHadiah.className =
            'alert alert-light border mt-3 mb-0';

        isi.textContent =
            'Pilih sparepart untuk memeriksa kelayakan hadiah pelanggan.';

        return;
    }

    if (daftarHakHadiah.size === 0) {
        informasiHadiah.className =
            'alert alert-light border mt-3 mb-0';

        isi.textContent =
            'Sparepart yang dipilih belum memiliki ketentuan hadiah.';

        return;
    }

    let stokTidakCukup = false;

    daftarHakHadiah.forEach(hadiah => {
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

    const daftarHadiah = document.createElement('ul');

    daftarHadiah.className = 'mb-0 ps-4';

    daftarHakHadiah.forEach(hadiah => {
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

        daftarHadiah.appendChild(item);
    });

    isi.appendChild(daftarHadiah);
}


            function hitungTotal() {
                let totalSparepart = 0;

                daftar.querySelectorAll('.baris-sparepart').forEach(baris => {
                    const select = baris.querySelector('.pilih-barang');
                    const jumlah = Number(
                        baris.querySelector('.jumlah-barang').value || 0
                    );

                    const barang = barangTersedia.find(
                        item => String(item.id) === select.value
                    );

                    const subtotal = barang ? barang.harga * jumlah : 0;

                    baris.querySelector('.subtotal-barang').textContent =
                        rupiah(subtotal);

                    totalSparepart += subtotal;
                });

                const jasa = Number(biayaJasa.value || 0);

                document.getElementById('totalSparepart').textContent =
                    rupiah(totalSparepart);

                document.getElementById('ringkasanJasa').textContent =
                    rupiah(jasa);

                document.getElementById('totalServis').textContent =
                    rupiah(totalSparepart + jasa);
                    perbaruiInformasiHadiah();
            }

            function tambahBaris(detail = null) {
                const index = nomorBaris++;

                const baris = document.createElement('div');
                baris.className = 'baris-sparepart';

                const row = document.createElement('div');
                row.className = 'row g-3 align-items-end';

                const kolomBarang = document.createElement('div');
                kolomBarang.className = 'col-12 col-lg-6';

                const labelBarang = document.createElement('label');
                labelBarang.className = 'form-label fw-semibold';
                labelBarang.textContent = 'Sparepart';

                const select = document.createElement('select');
                select.className = 'form-select pilih-barang';
                select.name = `detail_servis[${index}][barang_id]`;
                select.required = true;

                const opsiAwal = document.createElement('option');
                opsiAwal.value = '';
                opsiAwal.textContent = 'Pilih sparepart';
                select.appendChild(opsiAwal);

                barangTersedia.forEach(item => {
                    const opsi = document.createElement('option');
                    opsi.value = item.id;
                    opsi.textContent = `${item.kode} — ${item.nama} (Stok: ${item.stok})`;
                    select.appendChild(opsi);
                });

                if (detail) {
                    select.value = String(detail.barang_id);
                }

                kolomBarang.append(labelBarang, select);

                const kolomJumlah = document.createElement('div');
                kolomJumlah.className = 'col-6 col-lg-2';

                const labelJumlah = document.createElement('label');
                labelJumlah.className = 'form-label fw-semibold';
                labelJumlah.textContent = 'Jumlah';

                const inputJumlah = document.createElement('input');
                inputJumlah.type = 'number';
                inputJumlah.min = '1';
                inputJumlah.step = '1';
                inputJumlah.value = detail ? detail.jumlah : 1;
                inputJumlah.required = true;
                inputJumlah.name = `detail_servis[${index}][jumlah]`;
                inputJumlah.className = 'form-control jumlah-barang';

                kolomJumlah.append(labelJumlah, inputJumlah);

                const kolomSubtotal = document.createElement('div');
                kolomSubtotal.className = 'col-6 col-lg-2';

                const labelSubtotal = document.createElement('label');
                labelSubtotal.className = 'form-label fw-semibold';
                labelSubtotal.textContent = 'Subtotal';

                const subtotal = document.createElement('div');
                subtotal.className = 'fw-semibold subtotal-barang';
                subtotal.textContent = rupiah(0);

                kolomSubtotal.append(labelSubtotal, subtotal);

                const kolomHapus = document.createElement('div');
                kolomHapus.className = 'col-12 col-lg-2';

                const tombolHapus = document.createElement('button');
                tombolHapus.type = 'button';
                tombolHapus.className = 'btn btn-outline-danger btn-sm w-100';
                tombolHapus.textContent = 'Hapus';

                tombolHapus.addEventListener('click', function () {
                    baris.remove();
                    hitungTotal();
                });

                kolomHapus.appendChild(tombolHapus);

                row.append(
                    kolomBarang,
                    kolomJumlah,
                    kolomSubtotal,
                    kolomHapus
                );

                const info = document.createElement('div');
                info.className = 'small text-muted mt-2 info-barang';

                function perbaruiInfo() {
                    const barangDipilih = barangTersedia.find(
                        item => String(item.id) === select.value
                    );

                    info.textContent = barangDipilih
                        ? `Harga: ${rupiah(barangDipilih.harga)} | Stok tersedia: ${barangDipilih.stok} ${barangDipilih.satuan}`
                        : 'Pilih sparepart untuk melihat harga dan stok.';
                }

                select.addEventListener('change', function () {
                    perbaruiInfo();
                    hitungTotal();
                });

                inputJumlah.addEventListener('input', hitungTotal);

                perbaruiInfo();

                baris.append(row, info);
                daftar.appendChild(baris);

                hitungTotal();
            }

            tombolTambah.addEventListener('click', function () {
                tambahBaris();
            });

            biayaJasa.addEventListener('input', hitungTotal);

            detailAwal.forEach(detail => {
                tambahBaris(detail);
            });

            hitungTotal();
        });
    </script>
@endif

@endsection
