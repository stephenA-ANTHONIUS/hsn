@extends('layouts.app')

@section('content')

<style>
    .servis-form {
        color: #26332D;
    }

    .servis-form .page-title {
        color: #172D27;
        font-weight: 700;
    }

    .servis-form .panel {
        background: #fff;
        border: 1px solid #DCE2DD;
        border-radius: 12px;
    }

    .servis-form .section-title {
        color: #28483A;
        font-weight: 700;
        font-size: 15px;
    }

    .servis-form .form-control:focus {
        border-color: #28483A;
        box-shadow: 0 0 0 .2rem rgba(40,72,58,.12);
    }

    .servis-form .btn-hsn {
        background: #28483A;
        color: #fff;
        border: 1px solid #28483A;
    }

    .servis-form .btn-hsn:hover {
        background: #172D27;
        color: #fff;
    }

    .servis-form .pilihan-pelanggan {
        background: #F4F5F1;
        border: 1px solid #DCE2DD;
        border-radius: 10px;
    }
</style>

<div class="container-fluid servis-form">

    <div class="mb-4">
        <a href="{{ route($routePrefix . '.servis.index') }}"
            class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke transaksi servis
        </a>

        <h4 class="page-title mt-2 mb-1">Tambah Transaksi Servis</h4>

        <div class="text-muted small">
            Masukkan data pelanggan dan keluhan awal. Data pemeriksaan serta sparepart dapat dilengkapi setelah transaksi dibuat.
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Periksa kembali data berikut:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route($routePrefix . '.servis.store') }}">
        @csrf

        <div class="panel p-4 mb-3">

            <div class="section-title mb-3">
                <i class="bi bi-person-lines-fill me-2"></i>
                Informasi Pelanggan
            </div>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Pelanggan</label>

                    <input type="text"
                        name="nama_pelanggan"
                        class="form-control"
                        value="{{ old('nama_pelanggan') }}"
                        maxlength="150"
                        placeholder="Masukkan nama pelanggan"
                        required>

                    <div class="form-text">
                        Isi nama pelanggan sesuai identitas yang diberikan.
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nomor HP</label>

                    <input type="tel"
                        name="no_telepon"
                        class="form-control"
                        value="{{ old('no_telepon') }}"
                        maxlength="20"
                        placeholder="Contoh: 081234567890"
                        required>

                    <div class="form-text">
                        Nomor HP digunakan untuk mencocokkan data pelanggan yang sudah terdaftar.
                    </div>
                </div>

                @if($errors->has('aksi_pelanggan'))
                    <div class="col-12">
                        <div class="pilihan-pelanggan p-3">
                            <div class="fw-semibold mb-2">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                Nomor HP sudah terdaftar
                            </div>

                            <p class="small text-muted mb-3">
                                Nomor HP yang dimasukkan sudah digunakan oleh pelanggan dengan nama berbeda.
                                Tentukan tindakan yang sesuai sebelum menyimpan transaksi.
                            </p>

                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                    type="radio"
                                    name="aksi_pelanggan"
                                    id="perbaruiNama"
                                    value="perbarui_nama"
                                    @checked(old('aksi_pelanggan') === 'perbarui_nama')>

                                <label class="form-check-label" for="perbaruiNama">
                                    <strong>Perbarui nama pelanggan lama</strong>
                                    <div class="small text-muted">
                                        Digunakan jika pelanggan yang sama mengganti atau memperbaiki nama.
                                    </div>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input"
                                    type="radio"
                                    name="aksi_pelanggan"
                                    id="buatPelangganBaru"
                                    value="buat_baru"
                                    @checked(old('aksi_pelanggan') === 'buat_baru')>

                                <label class="form-check-label" for="buatPelangganBaru">
                                    <strong>Buat profil pelanggan baru</strong>
                                    <div class="small text-muted">
                                        Digunakan jika nomor HP dipakai bersama oleh orang yang berbeda.
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Servis</label>

                    <input type="date"
                        name="tanggal_servis"
                        class="form-control"
                        max="{{ now()->toDateString() }}"
                        value="{{ old('tanggal_servis', now()->toDateString()) }}"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Teknisi</label>

                    <input type="text"
                        name="nama_teknisi"
                        class="form-control"
                        value="{{ old('nama_teknisi') }}"
                        maxlength="150"
                        placeholder="Masukkan nama teknisi jika sudah diketahui">

                    <div class="form-text">
                        Nama teknisi dapat dikosongkan dan dilengkapi kemudian.
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Keluhan Pelanggan</label>

                    <textarea name="keluhan"
                        class="form-control"
                        rows="4"
                        required
                        placeholder="Jelaskan keluhan atau kerusakan perangkat...">{{ old('keluhan') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Keterangan Tambahan</label>

                    <textarea name="keterangan"
                        class="form-control"
                        rows="3"
                        placeholder="Keterangan tambahan jika diperlukan...">{{ old('keterangan') }}</textarea>
                </div>

            </div>
        </div>

        <div class="panel p-4 mb-4">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-info-circle fs-4" style="color:#28483A;"></i>
                <div>
                    <div class="fw-semibold mb-1">Informasi proses servis</div>
                    <div class="text-muted small">
                        Setelah transaksi dibuat, teknisi dapat memeriksa perangkat dan melengkapi hasil pemeriksaan,
                        biaya jasa, serta sparepart yang dibutuhkan melalui halaman detail transaksi.
                        Stok sparepart belum dikurangi pada tahap ini.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">

            <a href="{{ route($routePrefix . '.servis.index') }}"
                class="btn btn-outline-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-hsn">
                <i class="bi bi-save me-1"></i>
                Simpan Transaksi Servis
            </button>

        </div>

    </form>
</div>

@endsection
