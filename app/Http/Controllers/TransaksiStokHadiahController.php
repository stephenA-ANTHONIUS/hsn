<?php

namespace App\Http\Controllers;

use App\Models\Hadiah;
use App\Models\TransaksiStokHadiah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiStokHadiahController extends Controller
{
    private function routePrefix(): string
    {
        return request()->routeIs('admin.*')
            ? 'admin'
            : 'kepala-toko';
    }

    /**
     * Menampilkan riwayat transaksi stok hadiah.
     */
    public function index(Request $request)
    {
        $transaksi = TransaksiStokHadiah::with([
                'hadiah',
                'pengguna',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->whereHas('hadiah', function ($q) use ($search) {
                    $q->where('kode_hadiah', 'like', "%{$search}%")
                      ->orWhere('nama_hadiah', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('jenis_transaksi'), function ($query) use ($request) {
                $query->where('jenis_transaksi', $request->jenis_transaksi);
            })
            ->when($request->filled('tanggal_mulai'), function ($query) use ($request) {
                $query->whereDate(
                    'tanggal_transaksi',
                    '>=',
                    $request->tanggal_mulai
                );
            })
            ->when($request->filled('tanggal_selesai'), function ($query) use ($request) {
                $query->whereDate(
                    'tanggal_transaksi',
                    '<=',
                    $request->tanggal_selesai
                );
            })
            ->latest('tanggal_transaksi')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('hadiah.stok.index', compact('transaksi'));
    }

    /**
     * Menampilkan formulir transaksi stok hadiah.
     */
    public function create()
    {
        $hadiah = Hadiah::where('status', 'aktif')
            ->orderBy('nama_hadiah')
            ->get();

        return view('hadiah.stok.create', compact('hadiah'));
    }

    /**
     * Menyimpan transaksi dan memperbarui stok hadiah.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hadiah_id' => [
                'required',
                'integer',
                'exists:hadiah,id',
            ],
            'jenis_transaksi' => [
                'required',
                'in:masuk,penyesuaian',
            ],
            'jumlah' => [
                'required',
                'integer',
                'min:0',
            ],
            'tanggal_transaksi' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'hadiah_id.required' => 'Silakan pilih hadiah.',
            'hadiah_id.exists' => 'Data hadiah tidak ditemukan.',
            'jenis_transaksi.required' => 'Jenis transaksi harus dipilih.',
            'jenis_transaksi.in' => 'Jenis transaksi tidak valid.',
            'jumlah.required' => 'Jumlah harus diisi.',
            'jumlah.min' => 'Jumlah tidak boleh kurang dari 0.',
            'tanggal_transaksi.required' => 'Tanggal transaksi harus diisi.',
            'tanggal_transaksi.before_or_equal' =>
                'Tanggal transaksi tidak boleh melebihi hari ini.',
        ]);

        if (
            $validated['jenis_transaksi'] !== 'penyesuaian'
            && $validated['jumlah'] < 1
        ) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah transaksi masuk atau keluar minimal 1.',
            ]);
        }

        DB::transaction(function () use ($validated, $request) {
            $hadiah = Hadiah::whereKey($validated['hadiah_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($hadiah->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'hadiah_id' => 'Hadiah tersebut sudah nonaktif dan tidak dapat digunakan.',
                ]);
            }

            $stokSebelumnya = (int) $hadiah->stok;
            $jumlah = (int) $validated['jumlah'];

            if ($validated['jenis_transaksi'] === 'masuk') {
    $stokBaru = $stokSebelumnya + $jumlah;
} else {
    // Penyesuaian menggunakan jumlah stok fisik terbaru.
    $stokBaru = $jumlah;
}

            $hadiah->update([
                'stok' => $stokBaru,
            ]);

            TransaksiStokHadiah::create([
                'hadiah_id' => $hadiah->id,
                'pengguna_id' => $request->user()->id,
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'jumlah' => $jumlah,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'referensi_tabel' => null,
                'referensi_id' => null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()
            ->route($this->routePrefix() . '.hadiah.stok.index')
            ->with('success', 'Transaksi stok hadiah berhasil disimpan.');
    }
}
