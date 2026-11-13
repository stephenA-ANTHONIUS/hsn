<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\RiwayatPerubahanBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    /**
     * Memeriksa apakah pengguna adalah Kepala Toko.
     */
    private function isKepalaToko(): bool
    {
        return Auth::user()->role === 'kepala_toko';
    }

    /**
     * Menentukan route berdasarkan role pengguna.
     */
    private function indexRouteName(): string
    {
        return $this->isKepalaToko()
            ? 'kepala-toko.barang.index'
            : 'admin.barang.index';
    }
    /**
 * Mengambil snapshot informasi barang.
 * Stok tidak disertakan karena sudah memiliki Riwayat Stok sendiri.
 */
private function snapshotBarang(Barang $barang): array
{
    return [
        'kode_barang' => $barang->kode_barang,
        'nama_barang' => $barang->nama_barang,
        'kategori_barang_id' => (int) $barang->kategori_barang_id,
        'satuan_barang_id' => (int) $barang->satuan_barang_id,
        'harga_modal' => $barang->harga_modal,
        'harga_jual' => $barang->harga_jual,
        'stok_minimum' => (int) $barang->stok_minimum,
        'deskripsi' => $barang->deskripsi,
        'status' => $barang->status,
    ];
}

/**
 * Menyimpan riwayat perubahan barang.
 */
private function catatRiwayatBarang(
    Barang $barang,
    string $jenisPerubahan,
    ?array $dataSebelum,
    ?array $dataSesudah,
    ?string $keterangan = null
): void {
    RiwayatPerubahanBarang::create([
        'barang_id' => $barang->id,
        'pengguna_id' => Auth::id(),
        'jenis_perubahan' => $jenisPerubahan,
        'data_sebelum' => $dataSebelum,
        'data_sesudah' => $dataSesudah,
        'keterangan' => $keterangan,
    ]);
}

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $isKepalaToko = $this->isKepalaToko();

        $barangQuery = Barang::with(['kategori', 'satuan']);

        // Admin tidak mengambil kolom harga_modal dari database.
        if (!$isKepalaToko) {
            $barangQuery->select([
                'id',
                'kategori_barang_id',
                'satuan_barang_id',
                'kode_barang',
                'nama_barang',
                'harga_jual',
                'stok',
                'stok_minimum',
                'deskripsi',
                'status',
                'created_at',
                'updated_at',
            ]);
        }

        $barang = $barangQuery
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            })
            ->when($request->kategori, function ($query, $kategori) {
                $query->where('kategori_barang_id', $kategori);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategori = KategoriBarang::orderBy('nama_kategori')->get();

        return view('barang.index', compact(
            'barang',
            'kategori',
            'search',
            'isKepalaToko'
        ));
    }

    public function create()
    {
        $kategori = KategoriBarang::orderBy('nama_kategori')->get();
        $satuan = SatuanBarang::orderBy('nama_satuan')->get();
        $isKepalaToko = $this->isKepalaToko();

        return view('barang.create', compact(
            'kategori',
            'satuan',
            'isKepalaToko'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_barang_id' => [
                'required',
                'exists:kategori_barang,id',
            ],
            'satuan_barang_id' => [
                'required',
                'exists:satuan_barang,id',
            ],
            'kode_barang' => [
                'required',
                'string',
                'max:50',
                'unique:barang,kode_barang',
            ],
            'nama_barang' => [
                'required',
                'string',
                'max:150',
            ],
            'harga_jual' => [
                'required',
                'numeric',
                'min:0',
            ],
            'stok_minimum' => [
                'required',
                'integer',
                'min:0',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in(['aktif', 'nonaktif']),
            ],
        ], [
            'kategori_barang_id.required' => 'Kategori barang wajib dipilih.',
            'kategori_barang_id.exists' => 'Kategori barang tidak ditemukan.',
            'satuan_barang_id.required' => 'Satuan barang wajib dipilih.',
            'satuan_barang_id.exists' => 'Satuan barang tidak ditemukan.',
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.unique' => 'Kode barang sudah digunakan.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
        ]);

        // Harga modal hanya boleh diisi oleh Kepala Toko.
        if ($this->isKepalaToko()) {
            $hargaModal = $request->validate([
                'harga_modal' => ['required', 'numeric', 'min:0'],
            ], [
                'harga_modal.required' => 'Harga modal wajib diisi.',
                'harga_modal.numeric' => 'Harga modal harus berupa angka.',
                'harga_modal.min' => 'Harga modal tidak boleh negatif.',
            ]);

            $validated['harga_modal'] = $hargaModal['harga_modal'];
        } else {
            $validated['harga_modal'] = 0;
        }

        // Stok awal selalu nol.
        // Penambahan stok harus melalui transaksi barang masuk.
        $validated['stok'] = 0;

        DB::transaction(function () use ($validated) {
    $barang = Barang::create($validated);

    $this->catatRiwayatBarang(
        $barang,
        'ditambahkan',
        null,
        $this->snapshotBarang($barang),
        'Penambahan data barang baru.'
    );
});

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data barang berhasil ditambahkan. Silakan catat stok awal melalui menu Barang Masuk.');
    }

    public function edit(Barang $barang)
    {
        $kategori = KategoriBarang::orderBy('nama_kategori')->get();
        $satuan = SatuanBarang::orderBy('nama_satuan')->get();
        $isKepalaToko = $this->isKepalaToko();

        // Jangan kirim nilai harga modal kepada Admin.
        if (!$isKepalaToko) {
            $barang->setAttribute('harga_modal', null);
        }

        return view('barang.edit', compact(
            'barang',
            'kategori',
            'satuan',
            'isKepalaToko'
        ));
    }

    public function update(Request $request, Barang $barang)
{
    $validated = $request->validate([
        'kategori_barang_id' => [
            'required',
            'exists:kategori_barang,id',
        ],
        'satuan_barang_id' => [
            'required',
            'exists:satuan_barang,id',
        ],
        'kode_barang' => [
            'required',
            'string',
            'max:50',
            Rule::unique('barang', 'kode_barang')
                ->ignore($barang->id),
        ],
        'nama_barang' => [
            'required',
            'string',
            'max:150',
        ],
        'harga_jual' => [
            'required',
            'numeric',
            'min:0',
        ],
        'stok_minimum' => [
            'required',
            'integer',
            'min:0',
        ],
        'deskripsi' => [
            'nullable',
            'string',
        ],
        'status' => [
            'required',
            Rule::in(['aktif', 'nonaktif']),
        ],
    ], [
        'kategori_barang_id.required' => 'Kategori barang wajib dipilih.',
        'kategori_barang_id.exists' => 'Kategori barang tidak ditemukan.',
        'satuan_barang_id.required' => 'Satuan barang wajib dipilih.',
        'satuan_barang_id.exists' => 'Satuan barang tidak ditemukan.',
        'kode_barang.required' => 'Kode barang wajib diisi.',
        'kode_barang.unique' => 'Kode barang sudah digunakan.',
        'nama_barang.required' => 'Nama barang wajib diisi.',
        'harga_jual.required' => 'Harga jual wajib diisi.',
        'stok_minimum.required' => 'Stok minimum wajib diisi.',
    ]);

    // Harga modal hanya dapat diperbarui Kepala Toko.
    if ($this->isKepalaToko()) {
        $hargaModal = $request->validate([
            'harga_modal' => ['required', 'numeric', 'min:0'],
        ], [
            'harga_modal.required' => 'Harga modal wajib diisi.',
            'harga_modal.numeric' => 'Harga modal harus berupa angka.',
            'harga_modal.min' => 'Harga modal tidak boleh negatif.',
        ]);

        $validated['harga_modal'] = $hargaModal['harga_modal'];
    }

    DB::transaction(function () use ($validated, $barang) {
        $barang = Barang::whereKey($barang->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Mengambil data sebelum perubahan.
        $dataSebelum = $this->snapshotBarang($barang);

        // Memperbarui informasi barang.
        $barang->update($validated);

        // Mengambil data setelah perubahan.
        $dataSesudah = $this->snapshotBarang($barang);

        // Catat hanya jika memang ada perubahan.
        if ($dataSebelum !== $dataSesudah) {
            $this->catatRiwayatBarang(
                $barang,
                'diperbarui',
                $dataSebelum,
                $dataSesudah,
                'Perubahan informasi barang.'
            );
        }
    });

    return redirect()
        ->route($this->indexRouteName())
        ->with('success', 'Data barang berhasil diperbarui.');
}
    public function destroy(Barang $barang)
    {
        $sudahDigunakan =
            $barang->detailBarangMasuk()->exists()
            || $barang->detailPenjualan()->exists()
            || $barang->detailServis()->exists()
            || $barang->riwayatStok()->exists()
            || $barang->hadiah()->exists();

        if ($sudahDigunakan) {
            return redirect()
                ->route($this->indexRouteName())
                ->with(
                    'error',
                    'Barang tidak dapat dihapus karena sudah digunakan dalam transaksi atau pengaturan hadiah.'
                );
        }

        $barang->delete();

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data barang berhasil dihapus.');
    }

    public function nonaktifkan(Barang $barang)
{
    DB::transaction(function () use ($barang) {
        $barang = Barang::whereKey($barang->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($barang->status === 'nonaktif') {
            return;
        }

        $dataSebelum = $this->snapshotBarang($barang);

        $barang->update([
            'status' => 'nonaktif',
        ]);

        $this->catatRiwayatBarang(
            $barang,
            'dinonaktifkan',
            $dataSebelum,
            $this->snapshotBarang($barang),
            'Barang dinonaktifkan.'
        );
    });

    return redirect()
        ->route($this->indexRouteName())
        ->with('success', 'Barang berhasil dinonaktifkan.');
}

public function aktifkan(Barang $barang)
{
    DB::transaction(function () use ($barang) {
        $barang = Barang::whereKey($barang->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($barang->status === 'aktif') {
            return;
        }

        $dataSebelum = $this->snapshotBarang($barang);

        $barang->update([
            'status' => 'aktif',
        ]);

        $this->catatRiwayatBarang(
            $barang,
            'diaktifkan',
            $dataSebelum,
            $this->snapshotBarang($barang),
            'Barang diaktifkan kembali.'
        );
    });

    return redirect()
        ->route($this->indexRouteName())
        ->with('success', 'Barang berhasil diaktifkan kembali.');
}
}
