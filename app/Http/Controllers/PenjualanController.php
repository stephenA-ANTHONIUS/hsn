<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Pendapatan;
use App\Models\Penjualan;
use App\Models\RiwayatPerubahanPenjualan;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenjualanController extends Controller
{
    private function routePrefix(): string
    {
        return Auth::user()->role === 'admin'
            ? 'admin'
            : 'kepala-toko';
    }

    /**
     * Memastikan transaksi hanya dapat diedit pada hari yang sama.
     */
    private function pastikanBisaDiedit(Penjualan $penjualan): void
    {
        if (!$penjualan->tanggal_penjualan->isToday()) {
            abort(403, 'Transaksi hanya dapat diedit pada tanggal transaksi dibuat.');
        }
    }

    /**
     * Mengambil data transaksi sebelum atau sesudah perubahan.
     */
    private function ambilSnapshot(Penjualan $penjualan): array
    {
       $penjualan->load([
    'detailPenjualan.barang',
    'pelanggan',
]);

        return [
            'nomor_nota' => $penjualan->nomor_nota,
            'pelanggan_id' => $penjualan->pelanggan_id,
            'nama_pelanggan' => $penjualan->pelanggan->nama_pelanggan ?? 'Pelanggan Umum',
            'tanggal_penjualan' => $penjualan->tanggal_penjualan->format('Y-m-d'),
            'total_penjualan' => (float) $penjualan->total_penjualan,
            'nominal_dibayar' => (float) $penjualan->nominal_dibayar,
            'metode_pembayaran' => $penjualan->metode_pembayaran,
            'status_pembayaran' => $penjualan->status_pembayaran,
            'keterangan' => $penjualan->keterangan,
            'detail' => $penjualan->detailPenjualan
                ->map(function ($detail) {
                    return [
                        'barang_id' => $detail->barang_id,
                        'nama_barang' => $detail->barang->nama_barang ?? '-',
                        'jumlah' => (int) $detail->jumlah,
                        'harga_jual' => (float) $detail->harga_jual,
                        'subtotal' => (float) $detail->subtotal,
                    ];
                })
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Menampilkan daftar transaksi penjualan.
     */
    public function index(Request $request)
    {
        $penjualan = Penjualan::with([
            'pelanggan',
            'pengguna',
        ])
            ->withCount('detailPenjualan')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_nota', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', function ($p) use ($search) {
                            $p->where('nama_pelanggan', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->tanggal, function ($query, $tanggal) {
                $query->whereDate('tanggal_penjualan', $tanggal);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status_pembayaran', $status);
            })
            ->latest('tanggal_penjualan')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('penjualan.index', compact('penjualan'));
    }

    /**
     * Menampilkan formulir penjualan baru.
     */
    public function create()
    {
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();

        $barang = Barang::with([
            'hadiah' => function ($query) {
                $query->where('hadiah.status', 'aktif');
            },
        ])
            ->where('status', 'aktif')
            ->where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        // Menyiapkan aturan hadiah untuk pemeriksaan otomatis di halaman transaksi.
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

        return view('penjualan.create', compact(
            'pelanggan',
            'barang',
            'hadiahBarangUntukJs'
        ));
    }


    /**
     * Menyimpan transaksi penjualan dengan pembayaran lunas.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelanggan_id' => [
                'nullable',
                'exists:pelanggan,id',
            ],
            'tanggal_penjualan' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'metode_pembayaran' => [
                'required',
                'string',
                Rule::in(['Tunai', 'Transfer', 'QRIS']),
            ],
            'keterangan' => [
                'nullable',
                'string',
            ],
            'detail' => [
                'required',
                'array',
                'min:1',
            ],
            'detail.*.barang_id' => [
                'required',
                'distinct',
                'exists:barang,id',
            ],
            'detail.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $daftarBarang = collect($validated['detail'])
                ->sortBy('barang_id');

            $barangTerkunci = [];

            foreach ($daftarBarang as $item) {
                $barang = Barang::whereKey($item['barang_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$barang || $barang->status !== 'aktif') {
                    throw ValidationException::withMessages([
                        'detail' => 'Salah satu barang sudah tidak aktif.',
                    ]);
                }

                if ((int) $barang->stok < (int) $item['jumlah']) {
                    throw ValidationException::withMessages([
                        'detail' => 'Stok barang ' . $barang->nama_barang
                            . ' tidak mencukupi. Stok tersedia: '
                            . $barang->stok,
                    ]);
                }

                $barangTerkunci[$barang->id] = $barang;
            }

            $total = 0;

            foreach ($validated['detail'] as $item) {
                $barang = $barangTerkunci[$item['barang_id']];

                $total += (int) $item['jumlah']
                    * (float) $barang->harga_jual;
            }

            $nomorNota = 'PJ-'
                . now()->format('Ymd')
                . '-'
                . Str::upper(Str::random(6));

            while (Penjualan::where('nomor_nota', $nomorNota)->exists()) {
                $nomorNota = 'PJ-'
                    . now()->format('Ymd')
                    . '-'
                    . Str::upper(Str::random(6));
            }

            $transaksi = Penjualan::create([
                'pelanggan_id' => $validated['pelanggan_id'] ?? null,
                'pengguna_id' => Auth::id(),
                'nomor_nota' => $nomorNota,
                'tanggal_penjualan' => $validated['tanggal_penjualan'],
                'total_penjualan' => $total,
                'nominal_dibayar' => $total,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'lunas',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            foreach ($validated['detail'] as $item) {
                $barang = $barangTerkunci[$item['barang_id']];
                $jumlah = (int) $item['jumlah'];
                $hargaJual = (float) $barang->harga_jual;

                $transaksi->detailPenjualan()->create([
                    'barang_id' => $barang->id,
                    'jumlah' => $jumlah,
                    'harga_jual' => $hargaJual,
                    'subtotal' => $jumlah * $hargaJual,
                ]);

                $barang->decrement('stok', $jumlah);

                RiwayatStok::create([
                    'barang_id' => $barang->id,
                    'pengguna_id' => Auth::id(),
                    'jenis_transaksi' => 'penjualan',
                    'jenis_perubahan' => 'keluar',
                    'jumlah' => $jumlah,
                    'referensi_tabel' => 'penjualan',
                    'referensi_id' => $transaksi->id,
                    'keterangan' => 'Penjualan dengan nota '
                        . $transaksi->nomor_nota,
                ]);
            }

            Pendapatan::create([
                'pengguna_id' => Auth::id(),
                'penjualan_id' => $transaksi->id,
                'tanggal_pendapatan' => $validated['tanggal_penjualan'],
                'kategori_pendapatan' => 'penjualan',
                'nominal' => $total,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan' => 'Pembayaran lunas penjualan nota '
                    . $transaksi->nomor_nota,
            ]);
        });

        return redirect()
            ->route($this->routePrefix() . '.penjualan.index')
            ->with('success', 'Transaksi penjualan berhasil disimpan. Pembayaran lunas dan stok barang telah diperbarui.');
    }

    /**
     * Menampilkan formulir edit transaksi.
     */
    public function edit(Penjualan $penjualan)
    {
        $this->pastikanBisaDiedit($penjualan);

        $penjualan->load([
            'detailPenjualan.barang',
            'penyerahanHadiah',
        ]);

        $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();

        $barangLamaIds = $penjualan->detailPenjualan
            ->pluck('barang_id');

        $barang = Barang::where(function ($query) use ($barangLamaIds) {
            $query->where('status', 'aktif')
                ->orWhereIn('id', $barangLamaIds);
        })
            ->orderBy('nama_barang')
            ->get();

        $adaPenyerahanHadiah = $penjualan->penyerahanHadiah->isNotEmpty();

        return view('penjualan.edit', compact(
            'penjualan',
            'pelanggan',
            'barang',
            'adaPenyerahanHadiah'
        ));
    }

    /**
     * Memperbarui transaksi penjualan.
     */
    public function update(Request $request, Penjualan $penjualan)
    {
        $validated = $request->validate([
            'pelanggan_id' => [
                'nullable',
                'exists:pelanggan,id',
            ],
            'metode_pembayaran' => [
                'required',
                'string',
                Rule::in(['Tunai', 'Transfer', 'QRIS']),
            ],
            'keterangan' => [
                'nullable',
                'string',
            ],
            'detail' => [
                'required',
                'array',
                'min:1',
            ],
            'detail.*.barang_id' => [
                'required',
                'distinct',
                'exists:barang,id',
            ],
            'detail.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($validated, $penjualan) {
            $transaksi = Penjualan::whereKey($penjualan->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->pastikanBisaDiedit($transaksi);

            $detailLama = $transaksi->detailPenjualan()
                ->get()
                ->keyBy('barang_id');

            $jumlahBaru = collect($validated['detail'])
                ->keyBy('barang_id');

            $detailBerubah = [];

            foreach ($detailLama as $barangId => $detail) {
                $detailBaru = $jumlahBaru->get($barangId);

                if (!$detailBaru || (int) $detailBaru['jumlah'] !== (int) $detail->jumlah) {
                    $detailBerubah[] = true;
                }
            }

            foreach ($jumlahBaru as $barangId => $detail) {
                if (!$detailLama->has($barangId)) {
                    $detailBerubah[] = true;
                }
            }

            $pelangganBerubah = (int) ($validated['pelanggan_id'] ?? 0)
                !== (int) ($transaksi->pelanggan_id ?? 0);

            // Jika hadiah sudah diserahkan, barang dan pelanggan tidak boleh diubah.
            if (
                $transaksi->penyerahanHadiah()->exists()
                && (!empty($detailBerubah) || $pelangganBerubah)
            ) {
                throw ValidationException::withMessages([
                    'detail' => 'Barang atau pelanggan tidak dapat diubah karena hadiah dari transaksi ini sudah diserahkan.',
                ]);
            }

            $snapshotSebelum = $this->ambilSnapshot($transaksi);

            // Mengunci seluruh barang lama dan barang baru berdasarkan urutan ID.
            $semuaBarangId = $detailLama->keys()
                ->merge($jumlahBaru->keys())
                ->unique()
                ->sort()
                ->values();

            $barangTerkunci = Barang::whereIn('id', $semuaBarangId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalBaru = 0;
            $detailUntukDisimpan = [];

            foreach ($semuaBarangId as $barangId) {
                $barang = $barangTerkunci->get($barangId);

                if (!$barang) {
                    throw ValidationException::withMessages([
                        'detail' => 'Data barang tidak ditemukan.',
                    ]);
                }

                $detailLamaItem = $detailLama->get($barangId);
                $detailBaruItem = $jumlahBaru->get($barangId);

                $stokLamaTerjual = $detailLamaItem
                    ? (int) $detailLamaItem->jumlah
                    : 0;

                $stokBaruTerjual = $detailBaruItem
                    ? (int) $detailBaruItem['jumlah']
                    : 0;

                $selisih = $stokBaruTerjual - $stokLamaTerjual;

                if ($selisih > 0) {
                    if ($barang->status !== 'aktif') {
                        throw ValidationException::withMessages([
                            'detail' => 'Barang ' . $barang->nama_barang
                                . ' sudah tidak aktif.',
                        ]);
                    }

                    if ((int) $barang->stok < $selisih) {
                        throw ValidationException::withMessages([
                            'detail' => 'Stok tambahan barang '
                                . $barang->nama_barang
                                . ' tidak mencukupi. Stok tersedia: '
                                . $barang->stok,
                        ]);
                    }

                    $barang->decrement('stok', $selisih);

                    RiwayatStok::create([
                        'barang_id' => $barang->id,
                        'pengguna_id' => Auth::id(),
                        'jenis_transaksi' => 'penjualan',
                        'jenis_perubahan' => 'keluar',
                        'jumlah' => $selisih,
                        'referensi_tabel' => 'penjualan',
                        'referensi_id' => $transaksi->id,
                        'keterangan' => 'Penambahan jumlah barang akibat edit nota '
                            . $transaksi->nomor_nota,
                    ]);
                } elseif ($selisih < 0) {
                    $jumlahMasuk = abs($selisih);

                    $barang->increment('stok', $jumlahMasuk);

                    RiwayatStok::create([
                        'barang_id' => $barang->id,
                        'pengguna_id' => Auth::id(),
                        'jenis_transaksi' => 'penjualan',
                        'jenis_perubahan' => 'masuk',
                        'jumlah' => $jumlahMasuk,
                        'referensi_tabel' => 'penjualan',
                        'referensi_id' => $transaksi->id,
                        'keterangan' => 'Pengembalian stok akibat edit nota '
                            . $transaksi->nomor_nota,
                    ]);
                }

                if ($detailBaruItem) {
                    // Barang yang sudah ada mempertahankan harga pada transaksi lama.
                    // Barang baru memakai harga jual yang berlaku saat ditambahkan.
                    $hargaJual = $detailLamaItem
                        ? (float) $detailLamaItem->harga_jual
                        : (float) $barang->harga_jual;

                    $subtotal = $stokBaruTerjual * $hargaJual;

                    $totalBaru += $subtotal;

                    $detailUntukDisimpan[] = [
                        'barang_id' => $barang->id,
                        'jumlah' => $stokBaruTerjual,
                        'harga_jual' => $hargaJual,
                        'subtotal' => $subtotal,
                    ];
                }
            }

            // Memperbarui transaksi utama.
            $transaksi->update([
                'pelanggan_id' => $validated['pelanggan_id'] ?? null,
                'total_penjualan' => $totalBaru,
                'nominal_dibayar' => $totalBaru,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'lunas',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Memperbarui seluruh detail transaksi.
            $transaksi->detailPenjualan()->delete();

            foreach ($detailUntukDisimpan as $detail) {
                $transaksi->detailPenjualan()->create($detail);
            }

            // Memperbarui pendapatan transaksi.
            $pendapatan = Pendapatan::where('penjualan_id', $transaksi->id)
                ->lockForUpdate()
                ->first();

            $dataPendapatan = [
                'tanggal_pendapatan' => $transaksi->tanggal_penjualan,
                'kategori_pendapatan' => 'penjualan',
                'nominal' => $totalBaru,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan' => 'Pembayaran lunas penjualan nota '
                    . $transaksi->nomor_nota,
            ];

            if ($pendapatan) {
                $pendapatan->update($dataPendapatan);
            } else {
                Pendapatan::create(array_merge($dataPendapatan, [
                    'pengguna_id' => Auth::id(),
                    'penjualan_id' => $transaksi->id,
                ]));
            }

            // Menyimpan snapshot sesudah perubahan.
            $transaksi->refresh();
            $transaksi->load('detailPenjualan.barang');

            $snapshotSesudah = $this->ambilSnapshot($transaksi);

            RiwayatPerubahanPenjualan::create([
                'penjualan_id' => $transaksi->id,
                'pengguna_id' => Auth::id(),
                'data_sebelum' => $snapshotSebelum,
                'data_sesudah' => $snapshotSesudah,
                'keterangan' => 'Perubahan transaksi penjualan nota '
                    . $transaksi->nomor_nota,
            ]);
        });

        return redirect()
            ->route($this->routePrefix() . '.penjualan.index')
            ->with('success', 'Transaksi berhasil diperbarui. Stok, pendapatan, dan riwayat perubahan telah disesuaikan.');
    }

    /**
     * Menampilkan riwayat perubahan transaksi.
     */
    public function riwayat(Penjualan $penjualan)
    {
        $riwayat = $penjualan->riwayatPerubahan()
            ->with('pengguna')
            ->latest()
            ->paginate(10);

        return view('penjualan.riwayat', compact(
            'penjualan',
            'riwayat'
        ));
    }

    /**
     * Menampilkan nota penjualan.
     */
    public function nota(Penjualan $penjualan)
    {
        $penjualan->load([
            'pelanggan',
            'pengguna',
            'detailPenjualan.barang.satuan',
        ]);

        return view('penjualan.nota', compact('penjualan'));
    }
}
