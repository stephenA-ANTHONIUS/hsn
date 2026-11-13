<?php

namespace App\Http\Controllers;

use App\Models\Hadiah;
use App\Models\Penjualan;
use App\Models\PenyerahanHadiah;
use App\Models\RiwayatPerubahanPenyerahanHadiah;
use App\Models\TransaksiStokHadiah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PenyerahanHadiahController extends Controller
{
    private function routePrefix(): string
    {
        return request()->routeIs('admin.*')
            ? 'admin'
            : 'kepala-toko';
    }

    /**
     * Menampilkan riwayat penyerahan hadiah.
     */
    public function index(Request $request)
    {
        $penyerahan = PenyerahanHadiah::with([
            'pelanggan',
            'penjualan',
            'pengguna',
            'detailPenyerahanHadiah.hadiah',
        ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->whereHas('penjualan', function ($sub) use ($search) {
                        $sub->where('nomor_nota', 'like', "%{$search}%");
                    })->orWhereHas('pelanggan', function ($sub) use ($search) {
                        $sub->where('nama_pelanggan', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->filled('tanggal_mulai'), function ($query) use ($request) {
                $query->whereDate(
                    'tanggal_penyerahan',
                    '>=',
                    $request->tanggal_mulai
                );
            })
            ->when($request->filled('tanggal_akhir'), function ($query) use ($request) {
                $query->whereDate(
                    'tanggal_penyerahan',
                    '<=',
                    $request->tanggal_akhir
                );
            })
            ->latest('tanggal_penyerahan')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('penyerahan-hadiah.index', compact('penyerahan'));
    }

    /**
     * Form pencatatan penyerahan hadiah.
     * Hanya menampilkan nota penjualan hari ini.
     */
    public function create()
    {
        $penjualan = Penjualan::with([
            'pelanggan',
            'detailPenjualan.barang.hadiah',
        ])
            ->whereDate('tanggal_penjualan', today())
            ->whereDoesntHave('penyerahanHadiah')
            ->latest('tanggal_penjualan')
            ->get();

        $transaksiPenjualan = $this->formatTransaksiPenjualan($penjualan);

        $hadiah = Hadiah::where('status', 'aktif')
            ->orderBy('nama_hadiah')
            ->get(['id', 'kode_hadiah', 'nama_hadiah', 'stok']);

        return view('penyerahan-hadiah.create', compact(
            'transaksiPenjualan',
            'hadiah'
        ));
    }

    /**
     * Menyimpan penyerahan hadiah baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penjualan_id' => [
                'required',
                'integer',
                'exists:penjualan,id',
            ],
            'tanggal_penyerahan' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'foto_bukti' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'hadiah' => [
                'required',
                'array',
                'min:1',
            ],
            'hadiah.*.hadiah_id' => [
                'required',
                'integer',
                'distinct',
                'exists:hadiah,id',
            ],
            'hadiah.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
            'hadiah.*.keterangan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'foto_bukti.required' =>
                'Foto bukti penyerahan hadiah wajib diunggah.',
            'foto_bukti.image' =>
                'File bukti harus berupa gambar.',
            'foto_bukti.mimes' =>
                'Foto harus berformat JPG, JPEG, atau PNG.',
            'foto_bukti.max' =>
                'Ukuran foto maksimal 2 MB.',
            'hadiah.required' =>
                'Pilih minimal satu hadiah.',
            'hadiah.min' =>
                'Pilih minimal satu hadiah.',
            'hadiah.*.hadiah_id.distinct' =>
                'Hadiah yang sama tidak boleh dipilih lebih dari satu kali.',
        ]);

        $fotoPath = null;

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                &$fotoPath
            ) {
                $penjualan = Penjualan::with('detailPenjualan.barang.hadiah')
                    ->lockForUpdate()
                    ->findOrFail($validated['penjualan_id']);

                if (
                    PenyerahanHadiah::where(
                        'penjualan_id',
                        $penjualan->id
                    )->exists()
                ) {
                    throw ValidationException::withMessages([
                        'penjualan_id' =>
                            'Nota ini sudah memiliki catatan penyerahan hadiah.',
                    ]);
                }

                $hakHadiah = $this->hitungHakHadiah($penjualan);

                $penyerahan = PenyerahanHadiah::create([
                    'pelanggan_id' => $penjualan->pelanggan_id,
                    'penjualan_id' => $penjualan->id,
                    'pengguna_id' => $request->user()->id,
                    'tanggal_penyerahan' => $validated['tanggal_penyerahan'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                $idHadiah = collect($validated['hadiah'])
                    ->pluck('hadiah_id')
                    ->unique()
                    ->values();

                $dataHadiah = Hadiah::whereIn('id', $idHadiah)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Kurangi stok hadiah sesuai penyerahan awal.
                $this->simpanDetailDanKurangiStok(
                    $penyerahan,
                    $validated['hadiah'],
                    $hakHadiah,
                    $dataHadiah,
                    $validated['tanggal_penyerahan'],
                    $request->user()->id,
                    $penjualan
                );

                // Simpan foto bukti.
                $fotoPath = $request->file('foto_bukti')
                    ->store('bukti-penyerahan-hadiah', 'public');

                $penyerahan->update([
                    'foto_bukti' => $fotoPath,
                ]);

                /*
                 * CATATAN AUDIT:
                 * Pencatatan awal hanya dilakukan di store(),
                 * bukan di update().
                 */
                $penyerahan->load([
                    'pelanggan',
                    'penjualan',
                    'detailPenyerahanHadiah.hadiah',
                ]);

                RiwayatPerubahanPenyerahanHadiah::create([
                    'penyerahan_hadiah_id' => $penyerahan->id,
                    'pengguna_id' => $request->user()->id,
                    'aksi' => 'dibuat',
                    'data_sebelum' => null,
                    'data_sesudah' => $this->buatSnapshotPenyerahan($penyerahan),
                    'keterangan' => 'Pencatatan awal penyerahan hadiah.',
                ]);
            });
        } catch (Throwable $e) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }

            throw $e;
        }

        return redirect()
            ->route($this->routePrefix() . '.penyerahan-hadiah.index')
            ->with('success', 'Penyerahan hadiah berhasil dicatat.');
    }

    /**
     * Menampilkan formulir edit.
     */
    public function edit(PenyerahanHadiah $penyerahanHadiah)
    {
        $penyerahanHadiah->load([
            'pelanggan',
            'penjualan.pelanggan',
            'penjualan.detailPenjualan.barang.hadiah',
            'detailPenyerahanHadiah.hadiah',
        ]);

        $penjualan = $penyerahanHadiah->penjualan;

        $hakHadiah = $this->hitungHakHadiah($penjualan);

        $idHadiahLama = $penyerahanHadiah->detailPenyerahanHadiah
            ->pluck('hadiah_id');

        $hadiah = Hadiah::where(function ($query) use ($idHadiahLama) {
            $query->where('status', 'aktif')
                ->orWhereIn('id', $idHadiahLama);
        })
            ->orderBy('nama_hadiah')
            ->get();

        return view('penyerahan-hadiah.edit', compact(
            'penyerahanHadiah',
            'hakHadiah',
            'hadiah'
        ));
    }

    /**
     * Menampilkan riwayat perubahan penyerahan hadiah.
     */
    public function riwayat(PenyerahanHadiah $penyerahanHadiah)
    {
        $penyerahanHadiah->load([
            'pelanggan',
            'penjualan',
            'pengguna',
        ]);

        $riwayat = $penyerahanHadiah->riwayatPerubahan()
            ->with('pengguna')
            ->latest('created_at')
            ->latest('id')
            ->get();

        return view('penyerahan-hadiah.riwayat', compact(
            'penyerahanHadiah',
            'riwayat'
        ));
    }

    /**
     * Memperbarui penyerahan hadiah.
     *
     * Mutasi stok hanya mencatat SELISIH jumlah hadiah.
     * Tidak mengembalikan dan mengeluarkan ulang seluruh hadiah.
     */
    public function update(Request $request, PenyerahanHadiah $penyerahanHadiah)
    {
        $validated = $request->validate([
            'tanggal_penyerahan' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'foto_bukti' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'hadiah' => [
                'required',
                'array',
                'min:1',
            ],
            'hadiah.*.hadiah_id' => [
                'required',
                'integer',
                'distinct',
                'exists:hadiah,id',
            ],
            'hadiah.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
            'hadiah.*.keterangan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'foto_bukti.image' =>
                'File bukti harus berupa gambar.',
            'foto_bukti.mimes' =>
                'Foto harus berformat JPG, JPEG, atau PNG.',
            'foto_bukti.max' =>
                'Ukuran foto maksimal 2 MB.',
            'hadiah.required' =>
                'Pilih minimal satu hadiah.',
            'hadiah.min' =>
                'Pilih minimal satu hadiah.',
            'hadiah.*.hadiah_id.distinct' =>
                'Hadiah yang sama tidak boleh dipilih lebih dari satu kali.',
        ]);

        $penyerahanHadiah->load([
            'detailPenyerahanHadiah',
        ]);

        // Foto lama tetap digunakan apabila foto baru tidak diunggah.
        $adaFoto = $request->hasFile('foto_bukti')
            || !empty($penyerahanHadiah->foto_bukti);

        if (!$adaFoto) {
            throw ValidationException::withMessages([
                'foto_bukti' =>
                    'Foto bukti penyerahan hadiah wajib diunggah.',
            ]);
        }

        $fotoBaru = null;

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $penyerahanHadiah,
                &$fotoBaru
            ) {
                $penyerahan = PenyerahanHadiah::with([
                    'pelanggan',
                    'detailPenyerahanHadiah.hadiah',
                ])
                    ->lockForUpdate()
                    ->findOrFail($penyerahanHadiah->id);

                // Kunci transaksi penjualan.
                $penjualan = Penjualan::with(
                    'detailPenjualan.barang.hadiah'
                )
                    ->lockForUpdate()
                    ->findOrFail($penyerahan->penjualan_id);

                // Simpan snapshot sebelum perubahan.
                $dataSebelum = $this->buatSnapshotPenyerahan($penyerahan);

                // Hitung hak hadiah pelanggan.
                $hakHadiah = $this->hitungHakHadiah($penjualan);

                /*
                 * Hitung jumlah hadiah lama per jenis hadiah.
                 */
                $stokLama = $penyerahan->detailPenyerahanHadiah
                    ->groupBy('hadiah_id')
                    ->map(function ($items) {
                        return $items->sum('jumlah');
                    });

                /*
                 * Data hadiah baru dari formulir.
                 * Validasi distinct memastikan satu jenis hadiah hanya
                 * dikirim sekali.
                 */
                $itemsBaru = collect($validated['hadiah'])
                    ->keyBy('hadiah_id');

                /*
                 * Kumpulkan semua ID hadiah lama dan baru.
                 * Kunci berdasarkan urutan ID untuk mengurangi deadlock.
                 */
                $semuaIdHadiah = $stokLama->keys()
                    ->merge($itemsBaru->keys())
                    ->unique()
                    ->sort()
                    ->values();

                $dataHadiah = Hadiah::whereIn('id', $semuaIdHadiah)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                /*
                 * Validasi seluruh hadiah baru sebelum mutasi stok.
                 */
                foreach ($itemsBaru as $hadiahId => $item) {
                    $hadiah = $dataHadiah->get($hadiahId);

                    if (!$hadiah) {
                        throw ValidationException::withMessages([
                            'hadiah' => 'Data hadiah tidak ditemukan.',
                        ]);
                    }

                    if ($hadiah->status !== 'aktif') {
                        throw ValidationException::withMessages([
                            'hadiah' =>
                                "Hadiah {$hadiah->nama_hadiah} sudah nonaktif.",
                        ]);
                    }

                    $jumlah = (int) $item['jumlah'];
                    $hak = (int) ($hakHadiah[$hadiah->id] ?? 0);
                    $alasan = trim($item['keterangan'] ?? '');

                    if ($jumlah > $hak && $alasan === '') {
                        throw ValidationException::withMessages([
                            'hadiah' =>
                                "Jumlah {$hadiah->nama_hadiah} melebihi hak pelanggan. Isi alasan pada hadiah tersebut.",
                        ]);
                    }
                }

                /*
                 * CATAT MUTASI BERDASARKAN SELISIH.
                 *
                 * Contoh:
                 * Jumlah lama = 2
                 * Jumlah baru = 3
                 * Selisih = +1, hanya 1 yang keluar.
                 */
                $nomorNota = $penjualan->nomor_nota;
                $tanggalKoreksi = now()->toDateString();

                foreach ($semuaIdHadiah as $hadiahId) {
                    $hadiah = $dataHadiah->get($hadiahId);

                    if (!$hadiah) {
                        throw ValidationException::withMessages([
                            'hadiah' => 'Data hadiah tidak ditemukan.',
                        ]);
                    }

                    $jumlahLama = (int) ($stokLama->get($hadiahId) ?? 0);

                    $itemBaru = $itemsBaru->get($hadiahId);

                    $jumlahBaru = $itemBaru
                        ? (int) $itemBaru['jumlah']
                        : 0;

                    $selisih = $jumlahBaru - $jumlahLama;

                    // Tidak ada perubahan jumlah, tidak perlu mutasi stok.
                    if ($selisih === 0) {
                        continue;
                    }

                    if ($selisih > 0) {
                        /*
                         * Ada penambahan hadiah.
                         * Hanya stok tambahan yang dikurangi.
                         */
                        if ($hadiah->stok < $selisih) {
                            throw ValidationException::withMessages([
                                'hadiah' =>
                                    "Stok tambahan {$hadiah->nama_hadiah} tidak mencukupi. Stok tersedia: "
                                    . number_format($hadiah->stok, 0, ',', '.'),
                            ]);
                        }

                        $hadiah->stok -= $selisih;
                        $hadiah->save();

                        TransaksiStokHadiah::create([
                            'hadiah_id' => $hadiah->id,
                            'pengguna_id' => $request->user()->id,
                            'jenis_transaksi' => 'keluar',
                            'jumlah' => $selisih,
                            'tanggal_transaksi' => $tanggalKoreksi,
                            'referensi_tabel' => 'penyerahan_hadiah',
                            'referensi_id' => $penyerahan->id,
                            'keterangan' =>
                                'Penambahan jumlah hadiah akibat edit penyerahan nota '
                                . $nomorNota,
                        ]);
                    } else {
                        /*
                         * Ada pengurangan hadiah.
                         * Stok tambahan dikembalikan ke persediaan.
                         */
                        $jumlahDikembalikan = abs($selisih);

                        $hadiah->stok += $jumlahDikembalikan;
                        $hadiah->save();

                        TransaksiStokHadiah::create([
                            'hadiah_id' => $hadiah->id,
                            'pengguna_id' => $request->user()->id,
                            'jenis_transaksi' => 'masuk',
                            'jumlah' => $jumlahDikembalikan,
                            'tanggal_transaksi' => $tanggalKoreksi,
                            'referensi_tabel' => 'penyerahan_hadiah',
                            'referensi_id' => $penyerahan->id,
                            'keterangan' =>
                                'Pengurangan jumlah hadiah akibat edit penyerahan nota '
                                . $nomorNota,
                        ]);
                    }
                }

                /*
                 * Setelah seluruh validasi dan mutasi stok berhasil,
                 * perbarui detail penyerahan.
                 *
                 * Penghapusan dan pembuatan ulang detail tidak mengubah
                 * mutasi stok. Mutasi sudah dicatat berdasarkan selisih.
                 */
                $penyerahan->detailPenyerahanHadiah()->delete();

                foreach ($validated['hadiah'] as $item) {
                    $penyerahan->detailPenyerahanHadiah()->create([
                        'hadiah_id' => $item['hadiah_id'],
                        'jumlah' => (int) $item['jumlah'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }

                /*
                 * Perbarui informasi utama penyerahan.
                 */
                $penyerahan->update([
                    'tanggal_penyerahan' => $validated['tanggal_penyerahan'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                /*
                 * Simpan foto pengganti apabila diunggah.
                 * Foto lama sengaja tidak dihapus karena merupakan bukti audit.
                 */
                if ($request->hasFile('foto_bukti')) {
                    $fotoBaru = $request->file('foto_bukti')
                        ->store('bukti-penyerahan-hadiah', 'public');

                    $penyerahan->update([
                        'foto_bukti' => $fotoBaru,
                    ]);
                }

                /*
                 * Ambil snapshot sesudah perubahan.
                 */
                $penyerahan->refresh();

                $penyerahan->load([
                    'pelanggan',
                    'penjualan',
                    'detailPenyerahanHadiah.hadiah',
                ]);

                $dataSesudah = $this->buatSnapshotPenyerahan($penyerahan);

                /*
                 * Simpan satu riwayat perubahan.
                 * Tidak membuat catatan "dibuat" lagi.
                 */
                RiwayatPerubahanPenyerahanHadiah::create([
                    'penyerahan_hadiah_id' => $penyerahan->id,
                    'pengguna_id' => $request->user()->id,
                    'aksi' => 'diubah',
                    'data_sebelum' => $dataSebelum,
                    'data_sesudah' => $dataSesudah,
                    'keterangan' => 'Perubahan data penyerahan hadiah.',
                ]);
            });
        } catch (Throwable $e) {
            // Jika transaksi gagal, hapus foto baru yang belum terpakai.
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        // Jangan menghapus foto lama karena digunakan oleh riwayat audit.

        return redirect()
            ->route($this->routePrefix() . '.penyerahan-hadiah.index')
            ->with('success', 'Data penyerahan hadiah berhasil diperbarui. Mutasi stok hanya mencatat perubahan jumlah.');
    }

    /**
     * Mengambil snapshot lengkap data penyerahan hadiah.
     */
    private function buatSnapshotPenyerahan(
        PenyerahanHadiah $penyerahan
    ): array {
        $penyerahan->load([
            'pelanggan',
            'penjualan',
            'detailPenyerahanHadiah.hadiah',
        ]);

        return [
            'penyerahan_hadiah_id' => $penyerahan->id,
            'penjualan_id' => $penyerahan->penjualan_id,
            'nomor_nota' => $penyerahan->penjualan?->nomor_nota,
            'pelanggan' => $penyerahan->pelanggan?->nama_pelanggan
                ?? 'Pelanggan umum',
            'tanggal_penyerahan' => $penyerahan->tanggal_penyerahan
                ? $penyerahan->tanggal_penyerahan->format('Y-m-d')
                : null,
            'foto_bukti' => $penyerahan->foto_bukti,
            'keterangan' => $penyerahan->keterangan,

            'hadiah' => $penyerahan->detailPenyerahanHadiah
                ->map(function ($detail) {
                    return [
                        'detail_id' => $detail->id,
                        'hadiah_id' => $detail->hadiah_id,
                        'kode_hadiah' => $detail->hadiah?->kode_hadiah,
                        'nama_hadiah' => $detail->hadiah?->nama_hadiah
                            ?? 'Hadiah tidak ditemukan',
                        'jumlah' => (int) $detail->jumlah,
                        'keterangan' => $detail->keterangan,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * Menghitung hak hadiah berdasarkan barang yang dibeli.
     */
    private function hitungHakHadiah(Penjualan $penjualan): array
    {
        $penjualan->loadMissing('detailPenjualan.barang.hadiah');

        $hakHadiah = [];

        foreach ($penjualan->detailPenjualan as $detail) {
            foreach ($detail->barang->hadiah as $itemHadiah) {
                $jumlah = (int) $detail->jumlah
                    * (int) $itemHadiah->pivot->jumlah_hadiah;

                $hakHadiah[$itemHadiah->id] =
                    ($hakHadiah[$itemHadiah->id] ?? 0) + $jumlah;
            }
        }

        return $hakHadiah;
    }

    /**
     * Menyimpan detail hadiah dan mengurangi stok.
     * Digunakan untuk pencatatan penyerahan pertama kali.
     */
    private function simpanDetailDanKurangiStok(
        PenyerahanHadiah $penyerahan,
        array $items,
        array $hakHadiah,
        $dataHadiah,
        string $tanggal,
        int $penggunaId,
        Penjualan $penjualan
    ): void {
        foreach ($items as $item) {
            $hadiah = $dataHadiah->get($item['hadiah_id']);

            if (!$hadiah) {
                throw ValidationException::withMessages([
                    'hadiah' => 'Data hadiah tidak ditemukan.',
                ]);
            }

            if ($hadiah->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'hadiah' =>
                        "Hadiah {$hadiah->nama_hadiah} sudah nonaktif.",
                ]);
            }

            $jumlah = (int) $item['jumlah'];
            $hak = (int) ($hakHadiah[$hadiah->id] ?? 0);
            $alasanItem = trim($item['keterangan'] ?? '');

            if ($jumlah > $hak && $alasanItem === '') {
                throw ValidationException::withMessages([
                    'hadiah' =>
                        "Jumlah {$hadiah->nama_hadiah} melebihi hak pelanggan. Isi alasan pada hadiah tersebut.",
                ]);
            }

            if ($hadiah->stok < $jumlah) {
                throw ValidationException::withMessages([
                    'hadiah' =>
                        "Stok {$hadiah->nama_hadiah} tidak mencukupi. Stok tersedia: "
                        . number_format($hadiah->stok, 0, ',', '.'),
                ]);
            }

            $hadiah->stok -= $jumlah;
            $hadiah->save();

            $penyerahan->detailPenyerahanHadiah()->create([
                'hadiah_id' => $hadiah->id,
                'jumlah' => $jumlah,
                'keterangan' => $item['keterangan'] ?? null,
            ]);

            TransaksiStokHadiah::create([
                'hadiah_id' => $hadiah->id,
                'pengguna_id' => $penggunaId,
                'jenis_transaksi' => 'keluar',
                'jumlah' => $jumlah,
                'tanggal_transaksi' => $tanggal,
                'referensi_tabel' => 'penyerahan_hadiah',
                'referensi_id' => $penyerahan->id,
                'keterangan' => 'Penyerahan hadiah untuk nota '
                    . $penjualan->nomor_nota,
            ]);
        }
    }

    /**
     * Mengubah data penjualan menjadi format untuk JavaScript.
     */
    private function formatTransaksiPenjualan($penjualan)
    {
        return $penjualan->map(function ($item) {
            return [
                'id' => $item->id,
                'nomor_nota' => $item->nomor_nota,
                'tanggal' => $item->tanggal_penjualan->format('d-m-Y'),
                'pelanggan' => $item->pelanggan
                    ? $item->pelanggan->nama_pelanggan
                    : 'Pelanggan umum',
                'barang' => $item->detailPenjualan->map(function ($detail) {
                    return [
                        'nama' => $detail->barang->nama_barang,
                        'jumlah' => $detail->jumlah,
                        'hadiah' => $detail->barang->hadiah->map(function ($hadiah) {
                            return [
                                'id' => $hadiah->id,
                                'nama' => $hadiah->nama_hadiah,
                                'jumlah_per_unit' => $hadiah->pivot->jumlah_hadiah,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();
    }
}
