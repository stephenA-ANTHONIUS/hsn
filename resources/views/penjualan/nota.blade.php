<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota {{ $penjualan->nomor_nota }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f5f1;
            color: #26332d;
            font-family: Arial, sans-serif;
        }

        .nota {
            max-width: 800px;
            margin: 30px auto;
            padding: 35px;
            background: white;
            border-radius: 10px;
        }

        .judul-toko {
            color: #172d27;
            font-weight: bold;
        }

        .garis {
            border-top: 2px solid #28483a;
            margin: 20px 0;
        }

        .total {
            color: #28483a;
            font-size: 20px;
            font-weight: bold;
        }

        .status-batal {
            border: 2px solid #dc3545;
            color: #dc3545;
            padding: 12px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .informasi-pembatalan {
            background: #fff1f1;
            border: 1px solid #f1b5ba;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
        }

        .informasi-pembatalan strong {
            color: #b02a37;
        }

        .peringatan-batal {
            color: #b02a37;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        @media print {
            body {
                background: white;
            }

            .nota {
                margin: 0;
                padding: 10px;
                max-width: none;
                border-radius: 0;
            }

            .tombol-cetak {
                display: none !important;
            }
        }
    </style>
</head>

<body>

<div class="nota">

    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="judul-toko mb-1">HSN Service Perfection</h3>
            <div>Palembang</div>
            <div>Nota Penjualan Barang dan Sparepart</div>
        </div>

        <div class="text-end">
            <h5 class="fw-bold">NOTA PENJUALAN</h5>
            <div>{{ $penjualan->nomor_nota }}</div>
            <div>
                {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="garis"></div>

    {{-- STATUS PEMBATALAN --}}
    @if($penjualan->tanggal_pembatalan)
        <div class="status-batal">
            TRANSAKSI DIBATALKAN
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <strong>Pelanggan</strong>
            <div>
                {{ $penjualan->pelanggan->nama_pelanggan ?? 'Pelanggan Umum' }}
            </div>

            @if($penjualan->pelanggan)
                <div>
                    {{ $penjualan->pelanggan->no_telepon ?? '-' }}
                </div>

                <div>
                    {{ $penjualan->pelanggan->alamat ?? '-' }}
                </div>
            @endif
        </div>

        <div class="col-md-6 text-md-end">
            <strong>Petugas</strong>
            <div>{{ $penjualan->pengguna->name ?? '-' }}</div>
        </div>
    </div>

    {{-- DETAIL BARANG --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($penjualan->detailPenjualan as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $detail->barang->nama_barang ?? '-' }}
                        </td>
                        <td>{{ $detail->jumlah }}</td>
                        <td>
                            Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                        </td>
                        <td>
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- RINGKASAN PEMBAYARAN --}}
    <div class="row justify-content-end">
        <div class="col-md-6">

            <div class="d-flex justify-content-between mb-2">
                <span>Total Penjualan</span>
                <strong>
                    Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}
                </strong>
            </div>

            @if($penjualan->tanggal_pembatalan)

                <div class="d-flex justify-content-between mb-2">
                    <span>Nominal Pembayaran Tercatat</span>
                    <strong>
                        Rp {{ number_format($penjualan->nominal_dibayar, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Metode Pembayaran</span>
                    <strong>{{ $penjualan->metode_pembayaran ?? '-' }}</strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span>Status Transaksi</span>
                    <strong class="text-danger">Dibatalkan</strong>
                </div>

            @else

                <div class="d-flex justify-content-between mb-2">
                    <span>Sudah Dibayar</span>
                    <strong>
                        Rp {{ number_format($penjualan->nominal_dibayar, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Sisa Pembayaran</span>
                    <strong>
                        Rp {{ number_format(
                            $penjualan->total_penjualan - $penjualan->nominal_dibayar,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Metode Pembayaran</span>
                    <strong>{{ $penjualan->metode_pembayaran ?? '-' }}</strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span>Status</span>
                    <strong>
                        @if($penjualan->status_pembayaran === 'lunas')
                            Lunas
                        @elseif($penjualan->status_pembayaran === 'sebagian')
                            Sebagian
                        @else
                            Belum Dibayar
                        @endif
                    </strong>
                </div>

            @endif

        </div>
    </div>

    {{-- INFORMASI PEMBATALAN --}}
    @if($penjualan->tanggal_pembatalan)
        <div class="informasi-pembatalan">
            <div class="fw-bold text-danger mb-3">
                INFORMASI PEMBATALAN
            </div>

            <div class="mb-2">
                <strong>Tanggal Pembatalan:</strong>
                <div>
                    {{ $penjualan->tanggal_pembatalan->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="mb-2">
                <strong>Dibatalkan Oleh:</strong>
                <div>
                    {{ $penjualan->penggunaPembatalan->name ?? '-' }}
                </div>
            </div>

            <div>
                <strong>Alasan Pembatalan:</strong>
                <div>
                    {{ $penjualan->alasan_pembatalan ?? '-' }}
                </div>
            </div>
        </div>
    @endif

    {{-- KETERANGAN --}}
    @if($penjualan->keterangan)
        <div class="mt-4">
            <strong>Keterangan:</strong>
            <div>{{ $penjualan->keterangan }}</div>
        </div>
    @endif

    <div class="garis"></div>

    @if($penjualan->tanggal_pembatalan)
        <div class="peringatan-batal">
            NOTA INI TELAH DIBATALKAN
            <div class="small fw-normal mt-2">
                Dokumen ini hanya merupakan catatan historis transaksi
                dan bukan bukti transaksi penjualan yang masih berlaku.
            </div>
        </div>
    @else
        <div class="text-center">
            Terima kasih telah berbelanja di HSN Service Perfection.
            <br>
            Simpan nota ini sebagai bukti transaksi.
        </div>
    @endif

    <div class="text-center mt-4 tombol-cetak">
        <button onclick="window.print()" class="btn btn-success">
            Cetak Nota
        </button>
    </div>

</div>

</body>
</html>
