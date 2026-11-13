@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">Edit Barang</h4>
        <p class="text-muted mb-0">Perbarui informasi barang atau sparepart.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route($isKepalaToko ? 'kepala-toko.barang.update' : 'admin.barang.update', $barang->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Kode Barang <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="kode_barang"
                               class="form-control @error('kode_barang') is-invalid @enderror"
                               value="{{ old('kode_barang', $barang->kode_barang) }}"
                               required>

                        @error('kode_barang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Nama Barang <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nama_barang"
                               class="form-control @error('nama_barang') is-invalid @enderror"
                               value="{{ old('nama_barang', $barang->nama_barang) }}"
                               required>

                        @error('nama_barang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Kategori Barang <span class="text-danger">*</span>
                        </label>

                        <select name="kategori_barang_id"
                                class="form-select @error('kategori_barang_id') is-invalid @enderror"
                                required>

                            <option value="">Pilih Kategori</option>

                            @foreach($kategori as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('kategori_barang_id', $barang->kategori_barang_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kategori }}
                                </option>
                            @endforeach

                        </select>

                        @error('kategori_barang_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Satuan Barang <span class="text-danger">*</span>
                        </label>

                        <select name="satuan_barang_id"
                                class="form-select @error('satuan_barang_id') is-invalid @enderror"
                                required>

                            <option value="">Pilih Satuan</option>

                            @foreach($satuan as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('satuan_barang_id', $barang->satuan_barang_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_satuan }} ({{ $item->singkatan }})
                                </option>
                            @endforeach

                        </select>

                        @error('satuan_barang_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($isKepalaToko)
                        <div class="col-md-6">
                            <label class="form-label">
                                Harga Modal (Rp) <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   name="harga_modal"
                                   class="form-control @error('harga_modal') is-invalid @enderror"
                                   value="{{ old('harga_modal', $barang->harga_modal) }}"
                                   min="0"
                                   step="0.01"
                                   required>

                            @error('harga_modal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                Hanya dapat dilihat dan dikelola oleh Kepala Toko.
                            </small>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">
                            Harga Jual (Rp) <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="harga_jual"
                               class="form-control @error('harga_jual') is-invalid @enderror"
                               value="{{ old('harga_jual', $barang->harga_jual) }}"
                               min="0"
                               step="0.01"
                               required>

                        @error('harga_jual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Stok Saat Ini</label>

                        <input type="number"
                               class="form-control"
                               value="{{ $barang->stok }}"
                               disabled>

                        <small class="text-muted">
                            Stok hanya dapat berubah melalui transaksi Barang Masuk,
                            Penjualan, Servis, atau Penyerahan Hadiah.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Stok Minimum <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="stok_minimum"
                               class="form-control @error('stok_minimum') is-invalid @enderror"
                               value="{{ old('stok_minimum', $barang->stok_minimum) }}"
                               min="0"
                               required>

                        @error('stok_minimum')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Status Barang <span class="text-danger">*</span>
                        </label>

                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required>

                            <option value="aktif"
                                {{ old('status', $barang->status) == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="nonaktif"
                                {{ old('status', $barang->status) == 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>

                        </select>

                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>

                        <textarea name="deskripsi"
                                  rows="3"
                                  class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $barang->deskripsi) }}</textarea>

                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">

                    <a href="{{ route($isKepalaToko ? 'kepala-toko.barang.index' : 'admin.barang.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

@endsection
