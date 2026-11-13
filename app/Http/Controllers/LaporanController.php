<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Hadiah;
use App\Models\Penjualan;
use App\Models\PenyerahanHadiah;
use App\Models\RiwayatStok;
use App\Models\Servis;
use App\Models\TransaksiStokHadiah;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Halaman utama menu laporan.
     */
    public function index()
    {
        return view('laporan.index');
    }

    /**
     * Memvalidasi filter periode laporan.
     */
    private function validasiPeriode(Request $request): array
    {
        return $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
        ]);
    }

    /**
     * Menerapkan filter tanggal pada query.
     */
    private function terapkanPeriode($query, string $kolom, array $periode)
    {
        if (!empty($periode['tanggal_mulai'])) {
            $query->whereDate(
                $kolom,
                '>=',
                $periode['tanggal_mulai']
            );
        }

        if (!empty($periode['tanggal_akhir'])) {
            $query->whereDate(
                $kolom,
                '<=',
                $periode['tanggal_akhir']
            );
        }

        return $query;
    }

    /**
     * Format nominal rupiah.
     */
    private function rupiah($nominal): string
    {
        return 'Rp ' . number_format(
            (float) $nominal,
            0,
            ',',
            '.'
        );
    }

    /**
     * Laporan barang masuk.
     */
    public function barangMasuk(Request $request)
    {
        $periode = $this->validasiPeriode($request);

        $query = BarangMasuk::with([
            'pemasok',
            'pengguna',
            'detail.barang',
        ]);

        $this->terapkanPeriode($query, 'tanggal_masuk', $periode);

        $transaksi = $query
            ->latest('tanggal_masuk')
            ->latest('id')
            ->get();

        $bolehLihatHargaModal = request()->routeIs('kepala-toko.*');

        $kolom = [
            'tanggal' => 'Tanggal',
            'nomor_nota' => 'Nomor Nota',
            'pemasok' => 'Pemasok',
            'kode_barang' => 'Kode Barang',
            'nama_barang' => 'Nama Barang',
            'jumlah' => 'Jumlah',
            'harga_jual' => 'Harga Jual',
        ];

        if ($bolehLihatHargaModal) {
            $kolom['harga_modal'] = 'Harga Modal';
        }

        $kolom['petugas'] = 'Dicatat Oleh';

        $baris = [];
        $totalJumlah = 0;

        foreach ($transaksi as $item) {
            foreach ($item->detail as $detail) {
                $totalJumlah += (int) $detail->jumlah;

                $data = [
                    'tanggal' => $item->tanggal_masuk->format('d-m-Y'),
                    'nomor_nota' => $item->nomor_nota,
                    'pemasok' => $item->pemasok?->nama_pemasok ?? '-',
                    'kode_barang' => $detail->barang?->kode_barang ?? '-',
                    'nama_barang' => $detail->barang?->nama_barang ?? '-',
                    'jumlah' => number_format($detail->jumlah, 0, ',', '.'),
                    'harga_jual' => $this->rupiah($detail->harga_jual),
                    'petugas' => $item->pengguna?->name ?? '-',
                ];

                if ($bolehLihatHargaModal) {
                    $data['harga_modal'] = $this->rupiah($detail->harga_modal ?? 0);
                }

                $baris[] = $data;
            }
        }

        return view('laporan.tabel', [
            'jenis' => 'barang-masuk',
            'judul' => 'Laporan Barang Masuk',
            'deskripsi' => 'Riwayat penerimaan barang dan sparepart berdasarkan faktur atau nota pemasok.',
            'kolom' => $kolom,
            'baris' => $baris,
            'periode' => $periode,
            'ringkasan' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'nilai' => number_format($transaksi->count(), 0, ',', '.'),
                ],
                [
                    'label' => 'Total Barang Masuk',
                    'nilai' => number_format($totalJumlah, 0, ',', '.') . ' unit',
                ],
            ],
        ]);
    }

    /**
     * Laporan barang keluar.
     * Menggabungkan mutasi barang dan mutasi hadiah.
     */
    public function barangKeluar(Request $request)
    {
        $periode = $this->validasiPeriode($request);

        $queryBarang = RiwayatStok::with([
            'barang',
            'pengguna',
        ])
            ->where('jenis_perubahan', 'keluar');

        $this->terapkanPeriode($queryBarang, 'created_at', $periode);

        $mutasiBarang = $queryBarang->get();

        $baris = [];
        $totalBarangKeluar = 0;

        foreach ($mutasiBarang as $item) {
            $tanggal = $item->created_at;

            $jenis = match ($item->jenis_transaksi) {
                'penjualan' => 'Penjualan langsung',
                'servis' => 'Penggunaan sparepart untuk servis',
                'penyesuaian' => 'Penyesuaian stok',
                'barang_masuk' => 'Barang masuk',
                default => ucfirst($item->jenis_transaksi),
            };

            $totalBarangKeluar += (int) $item->jumlah;

            $baris[] = [
                '_sort' => $tanggal?->format('Y-m-d H:i:s') ?? '',
                'tanggal' => $tanggal?->format('d-m-Y H:i') ?? '-',
                'kategori' => 'Barang/Sparepart',
                'kode' => $item->barang?->kode_barang ?? '-',
                'nama' => $item->barang?->nama_barang ?? '-',
                'jenis_transaksi' => $jenis,
                'jumlah' => number_format($item->jumlah, 0, ',', '.'),
                'petugas' => $item->pengguna?->name ?? '-',
                'referensi' => $item->referensi_tabel
                    ? $item->referensi_tabel . ' #' . $item->referensi_id
                    : '-',
                'keterangan' => $item->keterangan ?: '-',
            ];
        }

        $queryHadiah = TransaksiStokHadiah::with([
            'hadiah',
            'pengguna',
        ])
            ->where('jenis_transaksi', 'keluar');

        $this->terapkanPeriode($queryHadiah, 'tanggal_transaksi', $periode);

        $mutasiHadiah = $queryHadiah->get();

        $totalHadiahKeluar = 0;

        foreach ($mutasiHadiah as $item) {
            $tanggal = $item->tanggal_transaksi;

            $totalHadiahKeluar += (int) $item->jumlah;

            $jenis = $item->referensi_tabel === 'penyerahan_hadiah'
                ? 'Penyerahan hadiah'
                : 'Pengeluaran hadiah';

            $baris[] = [
                '_sort' => $item->created_at?->format('Y-m-d H:i:s')
                    ?? $tanggal?->format('Y-m-d'),
                'tanggal' => $tanggal?->format('d-m-Y') ?? '-',
                'kategori' => 'Hadiah',
                'kode' => $item->hadiah?->kode_hadiah ?? '-',
                'nama' => $item->hadiah?->nama_hadiah ?? '-',
                'jenis_transaksi' => $jenis,
                'jumlah' => number_format($item->jumlah, 0, ',', '.'),
                'petugas' => $item->pengguna?->name ?? '-',
                'referensi' => $item->referensi_tabel
                    ? $item->referensi_tabel . ' #' . $item->referensi_id
                    : '-',
                'keterangan' => $item->keterangan ?: '-',
            ];
        }

        $baris = collect($baris)
            ->sortByDesc('_sort')
            ->map(function ($item) {
                unset($item['_sort']);
                return $item;
            })
            ->values()
            ->all();

        return view('laporan.tabel', [
            'jenis' => 'barang-keluar',
            'judul' => 'Laporan Barang Keluar',
            'deskripsi' => 'Riwayat pengeluaran barang, sparepart untuk servis, serta penyerahan hadiah.',
            'kolom' => [
                'tanggal' => 'Tanggal',
                'kategori' => 'Kategori',
                'kode' => 'Kode',
                'nama' => 'Nama Barang/Hadiah',
                'jenis_transaksi' => 'Jenis Transaksi',
                'jumlah' => 'Jumlah Keluar',
                'petugas' => 'Dicatat Oleh',
                'referensi' => 'Referensi',
                'keterangan' => 'Keterangan',
            ],
            'baris' => $baris,
            'periode' => $periode,
            'ringkasan' => [
                [
                    'label' => 'Total Barang/Sparepart Keluar',
                    'nilai' => number_format($totalBarangKeluar, 0, ',', '.') . ' unit',
                ],
                [
                    'label' => 'Total Hadiah Keluar',
                    'nilai' => number_format($totalHadiahKeluar, 0, ',', '.') . ' unit',
                ],
            ],
        ]);
    }

    /**
     * Laporan riwayat transaksi.
     * Menggabungkan penjualan, servis, dan penyerahan hadiah.
     */
    public function riwayatTransaksi(Request $request)
    {
        $periode = $this->validasiPeriode($request);

        $baris = [];

        // Penjualan langsung.
        $queryPenjualan = Penjualan::with([
            'pelanggan',
            'pengguna',
            'detailPenjualan.barang',
        ]);

        $this->terapkanPeriode(
            $queryPenjualan,
            'tanggal_penjualan',
            $periode
        );

        $penjualan = $queryPenjualan->get();

        foreach ($penjualan as $item) {
            $rincian = $item->detailPenjualan
                ->map(function ($detail) {
                    return ($detail->barang?->nama_barang ?? 'Barang')
                        . ' (' . $detail->jumlah . ' unit)';
                })
                ->implode(', ');

            $baris[] = [
                '_sort' => $item->tanggal_penjualan->format('Y-m-d')
                    . ' ' . ($item->created_at?->format('H:i:s') ?? ''),
                'tanggal' => $item->tanggal_penjualan->format('d-m-Y'),
                'jenis' => 'Penjualan langsung',
                'nomor_nota' => $item->nomor_nota,
                'pelanggan' => $item->pelanggan?->nama_pelanggan
                    ?? 'Pelanggan umum',
                'rincian' => $rincian ?: '-',
                'nilai' => $this->rupiah($item->total_penjualan),
                'petugas' => $item->pengguna?->name ?? '-',
            ];
        }

        // Transaksi servis.
        $queryServis = Servis::with([
            'pelanggan',
            'pengguna',
            'detailServis.barang',
        ]);

        $this->terapkanPeriode(
            $queryServis,
            'tanggal_servis',
            $periode
        );

        $servis = $queryServis->get();

        foreach ($servis as $item) {
            $rincian = $item->detailServis
                ->map(function ($detail) {
                    $status = str_replace(
                        '_',
                        ' ',
                        $detail->status_penggunaan
                    );

                    return ($detail->barang?->nama_barang ?? 'Sparepart')
                        . ' (' . $detail->jumlah . ' unit, ' . $status . ')';
                })
                ->implode(', ');

            $baris[] = [
                '_sort' => $item->tanggal_servis->format('Y-m-d')
                    . ' ' . ($item->created_at?->format('H:i:s') ?? ''),
                'tanggal' => $item->tanggal_servis->format('d-m-Y'),
                'jenis' => 'Servis - ' . ucfirst(str_replace('_', ' ', $item->status_perbaikan)),
                'nomor_nota' => $item->nomor_nota,
                'pelanggan' => $item->pelanggan?->nama_pelanggan
                    ?? 'Pelanggan umum',
                'rincian' => $rincian ?: 'Jasa servis',
                'nilai' => $this->rupiah($item->total_biaya),
                'petugas' => $item->pengguna?->name ?? '-',
            ];
        }

        // Penyerahan hadiah.
        $queryHadiah = PenyerahanHadiah::with([
            'pelanggan',
            'penjualan',
            'pengguna',
            'detailPenyerahanHadiah.hadiah',
        ]);

        $this->terapkanPeriode(
            $queryHadiah,
            'tanggal_penyerahan',
            $periode
        );

        $penyerahan = $queryHadiah->get();

        foreach ($penyerahan as $item) {
            $rincian = $item->detailPenyerahanHadiah
                ->map(function ($detail) {
                    return ($detail->hadiah?->nama_hadiah ?? 'Hadiah')
                        . ' (' . $detail->jumlah . ' buah)';
                })
                ->implode(', ');

            $baris[] = [
                '_sort' => $item->tanggal_penyerahan->format('Y-m-d')
                    . ' ' . ($item->created_at?->format('H:i:s') ?? ''),
                'tanggal' => $item->tanggal_penyerahan->format('d-m-Y'),
                'jenis' => 'Penyerahan hadiah',
                'nomor_nota' => $item->penjualan?->nomor_nota ?? '-',
                'pelanggan' => $item->pelanggan?->nama_pelanggan
                    ?? 'Pelanggan umum',
                'rincian' => $rincian ?: '-',
                'nilai' => '-',
                'petugas' => $item->pengguna?->name ?? '-',
            ];
        }

        $baris = collect($baris)
            ->sortByDesc('_sort')
            ->map(function ($item) {
                unset($item['_sort']);
                return $item;
            })
            ->values()
            ->all();

        return view('laporan.tabel', [
            'jenis' => 'riwayat-transaksi',
            'judul' => 'Laporan Riwayat Transaksi',
            'deskripsi' => 'Rekapitulasi penjualan langsung, transaksi servis, dan penyerahan hadiah.',
            'kolom' => [
                'tanggal' => 'Tanggal',
                'jenis' => 'Jenis Transaksi',
                'nomor_nota' => 'Nomor Nota',
                'pelanggan' => 'Pelanggan',
                'rincian' => 'Rincian Transaksi',
                'nilai' => 'Nilai Transaksi',
                'petugas' => 'Petugas',
            ],
            'baris' => $baris,
            'periode' => $periode,
            'ringkasan' => [
                [
                    'label' => 'Penjualan Langsung',
                    'nilai' => number_format($penjualan->count(), 0, ',', '.') . ' transaksi',
                ],
                [
                    'label' => 'Transaksi Servis',
                    'nilai' => number_format($servis->count(), 0, ',', '.') . ' transaksi',
                ],
                [
                    'label' => 'Penyerahan Hadiah',
                    'nilai' => number_format($penyerahan->count(), 0, ',', '.') . ' transaksi',
                ],
            ],
        ]);
    }

    /**
     * Laporan stok terkini.
     */
    public function stok()
    {
        $barang = Barang::with([
            'kategori',
            'satuan',
        ])
            ->orderBy('nama_barang')
            ->get();

        $hadiah = Hadiah::orderBy('nama_hadiah')
            ->get();

        // Harga modal hanya diberikan kepada kepala toko.
        $bolehLihatHargaModal = request()->routeIs('kepala-toko.*');

        return view('laporan.stok', compact(
            'barang',
            'hadiah',
            'bolehLihatHargaModal'
        ));
    }

    /**
     * Laporan penjualan kotor harian.
     * Penjualan langsung + total biaya servis.
     */
    public function penjualanKotor(Request $request)
    {
        $periode = $this->validasiPeriode($request);

        $queryPenjualan = Penjualan::query();

        $this->terapkanPeriode(
            $queryPenjualan,
            'tanggal_penjualan',
            $periode
        );

        $penjualanHarian = $queryPenjualan
            ->selectRaw('tanggal_penjualan AS tanggal, SUM(total_penjualan) AS total')
            ->groupBy('tanggal_penjualan')
            ->get();

        $queryServis = Servis::query();

        $this->terapkanPeriode(
            $queryServis,
            'tanggal_servis',
            $periode
        );

        $servisHarian = $queryServis
            ->selectRaw('tanggal_servis AS tanggal, SUM(total_biaya) AS total')
            ->groupBy('tanggal_servis')
            ->get();

        $rekap = [];

        $totalPenjualan = 0;
        $totalServis = 0;

        foreach ($penjualanHarian as $item) {
            $tanggal = $item->tanggal;

            $rekap[$tanggal] = [
                'tanggal' => $tanggal,
                'penjualan' => (float) $item->total,
                'servis' => 0,
            ];

            $totalPenjualan += (float) $item->total;
        }

        foreach ($servisHarian as $item) {
            $tanggal = $item->tanggal;

            if (!isset($rekap[$tanggal])) {
                $rekap[$tanggal] = [
                    'tanggal' => $tanggal,
                    'penjualan' => 0,
                    'servis' => 0,
                ];
            }

            $rekap[$tanggal]['servis'] = (float) $item->total;

            $totalServis += (float) $item->total;
        }

        $baris = collect(array_values($rekap))
            ->sortByDesc('tanggal')
            ->map(function ($item) {
                return [
                    'tanggal' => \Carbon\Carbon::parse($item['tanggal'])
                        ->format('d-m-Y'),
                    'penjualan' => $this->rupiah($item['penjualan']),
                    'servis' => $this->rupiah($item['servis']),
                    'total' => $this->rupiah(
                        $item['penjualan'] + $item['servis']
                    ),
                ];
            })
            ->values()
            ->all();

        return view('laporan.penjualan-kotor', [
            'baris' => $baris,
            'periode' => $periode,
            'totalPenjualan' => $totalPenjualan,
            'totalServis' => $totalServis,
            'totalKeseluruhan' => $totalPenjualan + $totalServis,
        ]);
    }
}
