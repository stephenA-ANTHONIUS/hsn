<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Pemasok;
use App\Models\RiwayatPerubahanBarang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BarangMasukController extends Controller
{
    /**
     * Menentukan prefix route berdasarkan role pengguna.
     */
    private function routePrefix(): string
    {
        return Auth::user()->role === 'admin'
            ? 'admin'
            : 'kepala-toko';
    }

    /**
 * Mengambil snapshot lengkap transaksi barang masuk.
 */
private function snapshotBarangMasuk(BarangMasuk $barangMasuk): array
{
    $barangMasuk->loadMissing([
        'pemasok',
        'detail.barang',
    ]);

    return [
        'barang_masuk_id' => $barangMasuk->id,
        'nomor_nota' => $barangMasuk->nomor_nota,
        'tanggal_masuk' => $barangMasuk->tanggal_masuk->format('Y-m-d'),
        'pemasok' => $barangMasuk->pemasok->nama_pemasok ?? '-',
        'keterangan' => $barangMasuk->keterangan,

        'detail' => $barangMasuk->detail->map(function ($detail) {
            return [
                'barang_id' => $detail->barang_id,
                'kode_barang' => $detail->barang->kode_barang ?? '-',
                'nama_barang' => $detail->barang->nama_barang ?? '-',
                'jumlah' => (int) $detail->jumlah,
                'harga_modal' => $detail->harga_modal,
                'harga_jual' => $detail->harga_jual,
            ];
        })->values()->toArray(),
    ];
}

/**
 * Mencatat perubahan transaksi barang masuk.
 */
private function catatRiwayatBarangMasuk(
    string $jenisPerubahan,
    ?array $dataSebelum,
    ?array $dataSesudah,
    string $keterangan
): void {
    RiwayatPerubahanBarang::create([
        'barang_id' => null,
        'pengguna_id' => Auth::id(),
        'jenis_perubahan' => $jenisPerubahan,
        'data_sebelum' => $dataSebelum,
        'data_sesudah' => $dataSesudah,
        'keterangan' => $keterangan,
    ]);
}

    /**
     * Memeriksa apakah transaksi masih boleh diedit.
     * Transaksi bertanggal sebelum hari ini dikunci.
     */
    private function pastikanTransaksiBisaDiedit(BarangMasuk $barangMasuk): void
    {
        $tanggalTransaksi = Carbon::parse($barangMasuk->tanggal_masuk)
            ->toDateString();

        $tanggalHariIni = now()->toDateString();

        if ($tanggalTransaksi < $tanggalHariIni) {
            throw ValidationException::withMessages([
                'tanggal_masuk' => 'Transaksi barang masuk yang sudah lewat tidak dapat diubah.',
            ]);
        }
    }

    /**
     * Menampilkan daftar transaksi barang masuk.
     */
    public function index(Request $request)
    {
        $barangMasuk = BarangMasuk::with(['pengguna', 'pemasok'])
            ->withCount('detail')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_nota', 'like', "%{$search}%")
                        ->orWhereHas('pemasok', function ($p) use ($search) {
                            $p->where('nama_pemasok', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->tanggal, function ($query, $tanggal) {
                $query->whereDate('tanggal_masuk', $tanggal);
            })
            ->latest('tanggal_masuk')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('barang-masuk.index', compact('barangMasuk'));
    }

    /**
     * Menampilkan formulir barang masuk.
     */
    public function create()
    {
        $barang = Barang::where('status', 'aktif')
            ->orderBy('nama_barang')
            ->get();

        return view('barang-masuk.create', compact('barang'));
    }

    /**
     * Menyimpan transaksi barang masuk dan memperbarui stok.
     */
    public function store(Request $request)
    {
        $isKepalaToko = Auth::user()->role === 'kepala_toko';

        $rules = [
            'nomor_nota' => [
                'required',
                'string',
                'max:100',
                'unique:barang_masuk,nomor_nota',
            ],
            'tanggal_masuk' => [
                'required',
                'date',
                'after_or_equal:today',
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
            'detail.*.harga_jual' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];

        // Harga modal hanya dapat diinput oleh Kepala Toko.
        if ($isKepalaToko) {
            $rules['detail.*.harga_modal'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $isKepalaToko) {

            // Menggunakan satu pemasok tetap dari Jakarta.
            $pemasok = Pemasok::firstOrCreate([
                'nama_pemasok' => 'Pusat Jakarta',
            ]);

            // Membuat transaksi utama barang masuk.
            $transaksi = BarangMasuk::create([
                'pemasok_id' => $pemasok->id,
                'pengguna_id' => Auth::id(),
                'nomor_nota' => $validated['nomor_nota'],
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Menyimpan detail barang masuk.
            foreach ($validated['detail'] as $item) {

                $barang = Barang::whereKey($item['barang_id'])
                    ->lockForUpdate()
                    ->first();

                // Memastikan barang masih aktif.
                if (!$barang || $barang->status !== 'aktif') {
                    throw ValidationException::withMessages([
                        'detail' => 'Salah satu barang sudah nonaktif. Silakan periksa kembali pilihan barang.',
                    ]);
                }

                $jumlah = (int) $item['jumlah'];

                // Menyimpan detail transaksi barang masuk sebagai riwayat harga saat transaksi.
                $transaksi->detail()->create([
                    'barang_id' => $barang->id,
                    'jumlah' => $jumlah,
                    'harga_modal' => $isKepalaToko
                        ? ($item['harga_modal'] ?? null)
                        : null,
                    'harga_jual' => $item['harga_jual'],
                ]);

                // Memperbarui harga jual utama barang agar langsung digunakan pada penjualan berikutnya.
                $barang->update([
                    'harga_jual' => $item['harga_jual'],
                ]);

                // Menambahkan jumlah stok barang.
                $barang->increment('stok', $jumlah);

                // Mencatat perubahan stok.
                RiwayatStok::create([
                    'barang_id' => $barang->id,
                    'pengguna_id' => Auth::id(),
                    'jenis_transaksi' => 'barang_masuk',
                    'jenis_perubahan' => 'masuk',
                    'jumlah' => $jumlah,
                    'referensi_tabel' => 'barang_masuk',
                    'referensi_id' => $transaksi->id,
                    'keterangan' => 'Barang masuk dengan nota ' . $transaksi->nomor_nota,
                ]);
            }
            // Mencatat transaksi barang masuk ke riwayat perubahan.
$transaksi->load([
    'pemasok',
    'detail.barang',
]);

$this->catatRiwayatBarangMasuk(
    'barang_masuk_ditambahkan',
    null,
    $this->snapshotBarangMasuk($transaksi),
    'Transaksi barang masuk dengan nota '
        . $transaksi->nomor_nota
        . ' berhasil ditambahkan.'
);
        });

        return redirect()
            ->route($this->routePrefix() . '.barang-masuk.index')
            ->with('success', 'Transaksi barang masuk berhasil disimpan dan stok telah diperbarui.');
    }

    /**
     * Menampilkan detail transaksi barang masuk.
     */
    public function show(BarangMasuk $barangMasuk)
    {
        $barangMasuk->load([
            'pemasok',
            'pengguna',
            'detail.barang.satuan',
            'detail.barang.kategori',
        ]);

        return view('barang-masuk.show', compact('barangMasuk'));
    }

    /**
     * Menampilkan formulir edit barang masuk.
     */
    public function edit(BarangMasuk $barangMasuk)
    {
        try {
            $this->pastikanTransaksiBisaDiedit($barangMasuk);
        } catch (ValidationException $e) {
            return redirect()
                ->route($this->routePrefix() . '.barang-masuk.index')
                ->with('error', $e->getMessage());
        }

        $barangMasuk->load('detail');

        $barang = Barang::orderBy('nama_barang')->get();

        return view('barang-masuk.edit', compact('barangMasuk', 'barang'));
    }

    /**
     * Memperbarui transaksi barang masuk dan menyesuaikan stok.
     */
    public function update(Request $request, BarangMasuk $barangMasuk)
    {
        $this->pastikanTransaksiBisaDiedit($barangMasuk);

        $isKepalaToko = Auth::user()->role === 'kepala_toko';

        $rules = [
            'nomor_nota' => [
                'required',
                'string',
                'max:100',
                Rule::unique('barang_masuk', 'nomor_nota')
                    ->ignore($barangMasuk->id),
            ],
            'tanggal_masuk' => [
                'required',
                'date',
                'after_or_equal:today',
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
            'detail.*.harga_jual' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];

        // Harga modal hanya dapat diubah oleh Kepala Toko.
        if ($isKepalaToko) {
            $rules['detail.*.harga_modal'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $barangMasuk, $isKepalaToko) {

            // Mengunci transaksi utama selama proses perubahan.
            $barangMasuk = BarangMasuk::whereKey($barangMasuk->id)
                ->lockForUpdate()
                ->firstOrFail();

// Memeriksa kembali tanggal transaksi.
$this->pastikanTransaksiBisaDiedit($barangMasuk);

// Memuat seluruh data transaksi sebelum perubahan.
$barangMasuk->load([
    'pemasok',
    'detail.barang',
]);

// Mengambil snapshot sebelum perubahan.
$dataSebelum = $this->snapshotBarangMasuk($barangMasuk);

// Mengambil detail transaksi sebelumnya.
$detailLama = $barangMasuk->detail()->get();

            // Menyimpan harga modal lama berdasarkan barang.
            $hargaModalLama = $detailLama
                ->groupBy('barang_id')
                ->map(function ($items) {
                    return $items->pluck('harga_modal')->values()->all();
                });

            // Menghitung jumlah barang sebelum diedit.
            $stokLama = $detailLama
                ->groupBy('barang_id')
                ->map(fn ($items) => $items->sum('jumlah'));

            // Menghitung jumlah barang setelah diedit.
            $stokBaru = collect($validated['detail'])
                ->groupBy('barang_id')
                ->map(fn ($items) => $items->sum('jumlah'));

            // Menggabungkan seluruh ID barang lama dan baru.
            $semuaBarangId = $stokLama->keys()
                ->merge($stokBaru->keys())
                ->unique();

            foreach ($semuaBarangId as $barangId) {

                $barang = Barang::whereKey($barangId)
                    ->lockForUpdate()
                    ->first();

                if (!$barang) {
                    throw ValidationException::withMessages([
                        'detail' => 'Data barang tidak ditemukan.',
                    ]);
                }

                // Memastikan barang baru yang dipilih masih aktif.
                if (
                    ($stokLama[$barangId] ?? 0) == 0 &&
                    $barang->status !== 'aktif'
                ) {
                    throw ValidationException::withMessages([
                        'detail' => 'Barang baru yang dipilih sudah tidak aktif.',
                    ]);
                }

                $jumlahLama = (int) ($stokLama[$barangId] ?? 0);
                $jumlahBaru = (int) ($stokBaru[$barangId] ?? 0);

                $selisih = $jumlahBaru - $jumlahLama;

                if ($selisih === 0) {
                    continue;
                }

                $stokSekarang = (int) $barang->stok;
                $stokSetelahEdit = $stokSekarang + $selisih;

                // Mencegah stok menjadi minus.
                if ($stokSetelahEdit < 0) {
                    throw ValidationException::withMessages([
                        'detail' => 'Jumlah barang tidak dapat dikurangi karena sebagian stok sudah digunakan pada transaksi lain.',
                    ]);
                }

                // Memperbarui stok barang.
                $barang->update([
                    'stok' => $stokSetelahEdit,
                ]);

                // Mencatat perubahan stok.
                RiwayatStok::create([
                    'barang_id' => $barang->id,
                    'pengguna_id' => Auth::id(),
                    'jenis_transaksi' => 'penyesuaian',
                    'jenis_perubahan' => $selisih > 0 ? 'masuk' : 'keluar',
                    'jumlah' => abs($selisih),
                    'referensi_tabel' => 'barang_masuk',
                    'referensi_id' => $barangMasuk->id,
                    'keterangan' => 'Penyesuaian stok karena edit nota barang masuk '
                        . $barangMasuk->nomor_nota
                        . '. Jumlah sebelumnya: ' . $jumlahLama
                        . ', jumlah setelah edit: ' . $jumlahBaru,
                ]);
            }

            // Memperbarui informasi utama transaksi.
            $barangMasuk->update([
                'nomor_nota' => $validated['nomor_nota'],
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Menghapus detail transaksi lama.
            $barangMasuk->detail()->delete();

            // Menyimpan detail transaksi terbaru.
            $indeksHargaModal = [];

            foreach ($validated['detail'] as $item) {

                $barangId = $item['barang_id'];

                if ($isKepalaToko) {
                    $hargaModal = $item['harga_modal'] ?? null;
                } else {
                    // Admin mempertahankan harga modal lama.
                    $daftarHargaLama = $hargaModalLama[$barangId] ?? [];

                    $indeks = $indeksHargaModal[$barangId] ?? 0;

                    $hargaModal = $daftarHargaLama[$indeks] ?? null;

                    $indeksHargaModal[$barangId] = $indeks + 1;
                }

                $barangMasuk->detail()->create([
                    'barang_id' => $barangId,
                    'jumlah' => $item['jumlah'],
                    'harga_modal' => $hargaModal,
                    'harga_jual' => $item['harga_jual'],
                ]);

                // Harga jual terbaru dari transaksi barang masuk menjadi harga jual utama barang.
                Barang::whereKey($barangId)->update([
                    'harga_jual' => $item['harga_jual'],
                ]);
            }
            // Memuat ulang transaksi dengan detail terbaru.
$barangMasuk->refresh();

$barangMasuk->load([
    'pemasok',
    'detail.barang',
]);

// Mengambil snapshot sesudah perubahan.
$dataSesudah = $this->snapshotBarangMasuk($barangMasuk);

// Catat jika terdapat perubahan informasi transaksi.
if ($dataSebelum !== $dataSesudah) {
    $this->catatRiwayatBarangMasuk(
        'barang_masuk_diperbarui',
        $dataSebelum,
        $dataSesudah,
        'Transaksi barang masuk dengan nota '
            . $barangMasuk->nomor_nota
            . ' diperbarui.'
    );
}
        });

        return redirect()
            ->route($this->routePrefix() . '.barang-masuk.index')
            ->with('success', 'Transaksi barang masuk berhasil diperbarui dan perubahan stok telah dicatat.');
    }
}
