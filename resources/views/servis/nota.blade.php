<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Servis - {{ $servis->nomor_nota }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #26332D;
            background: #f4f5f1;
            margin: 0;
            padding: 25px;
        }

        .nota {
            max-width: 800px;
            margin: auto;
            padding: 35px;
            background: white;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #28483A;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }

        .header h2 {
            color: #172D27;
            margin: 0 0 6px;
        }

        .header p {
            margin: 4px 0;
            font-size: 13px;
        }

        h4 {
            color: #28483A;
            margin: 22px 0 10px;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            font-size: 13px;
        }

        .label {
            color: #78817B;
            margin-bottom: 4px;
        }

        .value {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 10px 8px;
            border: 1px solid #DCE2DD;
            text-align: left;
        }

        th {
            background: #F4F5F1;
        }

        .text-right {
            text-align: right;
        }

        .total {
            width: 320px;
            margin-left: auto;
            margin-top: 15px;
            font-size: 14px;
        }

        .total div {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }

        .grand-total {
            border-top: 1px solid #aaa;
            margin-top: 5px;
            padding-top: 12px !important;
            font-weight: bold;
            font-size: 17px;
            color: #28483A;
        }

        .catatan {
            margin-top: 20px;
            font-size: 13px;
            white-space: pre-line;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #78817B;
        }

        .aksi {
            max-width: 800px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .aksi button {
            padding: 9px 16px;
            border: 0;
            border-radius: 5px;
            background: #28483A;
            color: white;
            cursor: pointer;
        }

        .aksi a {
            padding: 9px 16px;
            border-radius: 5px;
            border: 1px solid #aaa;
            color: #26332D;
            text-decoration: none;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .nota {
                border: none;
                padding: 15px;
                max-width: 100%;
            }

            .aksi {
                display: none;
            }

            @page {
                size: A4;
                margin: 12mm;
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .nota {
                padding: 18px;
            }

            .info {
                grid-template-columns: 1fr;
            }

            .total {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="aksi">
        <a href="{{ route($routePrefix . '.servis.show', $servis->id) }}">
            Kembali
        </a>

        <button onclick="window.print()">
            Cetak Nota
        </button>
    </div>

    <div class="nota">

        <div class="header">
            <h2>HSN SERVICE PERFECTION</h2>
            <p>Nota Transaksi Servis</p>
            <p>{{ $servis->nomor_nota }}</p>
        </div>

        <h4>Informasi Servis</h4>

        <div class="info">
            <div class="info-item">
                <div class="label">Nomor Nota</div>
                <div class="value">{{ $servis->nomor_nota }}</div>
            </div>

            <div class="info-item">
                <div class="label">Tanggal Servis</div>
                <div class="value">
                    {{ $servis->tanggal_servis->format('d/m/Y') }}
                </div>
            </div>

            <div class="info-item">
                <div class="label">Nama Pelanggan</div>
                <div class="value">
                    {{ $servis->pelanggan->nama_pelanggan ?? 'Pelanggan umum' }}
                </div>
            </div>

            <div class="info-item">
                <div class="label">Nomor Telepon</div>
                <div class="value">
                    {{ $servis->pelanggan->no_telepon ?? '-' }}
                </div>
            </div>

            <div class="info-item">
                <div class="label">Teknisi</div>
                <div class="value">{{ $servis->nama_teknisi ?: '-' }}</div>
            </div>

            <div class="info-item">
                <div class="label">Petugas Penerima</div>
                <div class="value">{{ $servis->pengguna->name ?? '-' }}</div>
            </div>

            <div class="info-item">
                <div class="label">Status Servis</div>
                <div class="value">
                    {{ ucwords(str_replace('_', ' ', $servis->status_perbaikan)) }}
                </div>
            </div>

            <div class="info-item">
                <div class="label">Status Pembayaran</div>
                <div class="value">
                    @if($servis->status_pembayaran === 'lunas')
                        Lunas
                    @elseif($servis->status_pembayaran === 'sebagian')
                        Dibayar Sebagian
                    @else
                        Belum Dibayar
                    @endif
                </div>
            </div>
        </div>

        <h4>Informasi Perbaikan</h4>

        <div class="info-item">
            <div class="label">Keluhan Pelanggan</div>
            <div>{{ $servis->keluhan }}</div>
        </div>

        <div class="info-item" style="margin-top: 12px;">
            <div class="label">Hasil Pemeriksaan</div>
            <div>{{ $servis->hasil_pemeriksaan ?: '-' }}</div>
        </div>

        <h4>Detail Sparepart</h4>

        <table>
            <thead>
                <tr>
                    <th>Nama Sparepart</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($servis->detailServis as $detail)
                    <tr>
                        <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                        <td>
                            {{ $detail->jumlah }}
                            {{ $detail->barang->satuan->nama_satuan ?? '' }}
                        </td>
                        <td>
                            Rp {{ number_format((float) $detail->harga_sparepart, 0, ',', '.') }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;">
                            Tidak ada sparepart.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="total">
            <div>
                <span>Biaya Jasa</span>
                <span>Rp {{ number_format((float) $servis->biaya_jasa, 0, ',', '.') }}</span>
            </div>

            <div>
                <span>Total Sparepart</span>
                <span>
                    Rp {{ number_format((float) $servis->detailServis->sum('subtotal'), 0, ',', '.') }}
                </span>
            </div>

            <div class="grand-total">
                <span>Total Biaya</span>
                <span>Rp {{ number_format((float) $servis->total_biaya, 0, ',', '.') }}</span>
            </div>

            <div>
                <span>Sudah Dibayar</span>
                <span>Rp {{ number_format((float) $servis->nominal_dibayar, 0, ',', '.') }}</span>
            </div>

            <div>
                <span>Sisa Tagihan</span>
                <span>
                    Rp {{ number_format(max(0, (float) $servis->total_biaya - (float) $servis->nominal_dibayar), 0, ',', '.') }}
                </span>
            </div>
        </div>

        <h4>Riwayat Pembayaran</h4>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th>Nominal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($servis->pendapatan->sortBy('created_at') as $pembayaran)
                    <tr>
                        <td>{{ $pembayaran->tanggal_pendapatan->format('d/m/Y') }}</td>
                        <td>{{ $pembayaran->metode_pembayaran ?? '-' }}</td>
                        <td>
                            Rp {{ number_format((float) $pembayaran->nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center;">
                            Belum ada pembayaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($servis->keterangan)
            <div class="catatan">
                <strong>Keterangan:</strong>
                {{ $servis->keterangan }}
            </div>
        @endif

        <div class="footer">
            Terima kasih telah menggunakan layanan HSN Service Perfection.
        </div>

    </div>

</body>
</html>
