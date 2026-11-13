<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPerubahanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RiwayatPerubahanBarangController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jenis_perubahan' => [
                'nullable',
               Rule::in([
    'ditambahkan',
    'diperbarui',
    'dinonaktifkan',
    'diaktifkan',
    'barang_masuk_ditambahkan',
    'barang_masuk_diperbarui',
]),
            ],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
        ]);

        $isKepalaToko = Auth::user()->role === 'kepala_toko';

        $riwayat = RiwayatPerubahanBarang::with([
            'barang:id,kode_barang,nama_barang',
            'pengguna:id,name',
        ])
            ->when($request->filled('search'), function ($query) use ($validated) {
                $search = $validated['search'];

                $query->where(function ($q) use ($search) {
                    $q->whereHas('barang', function ($barang) use ($search) {
                        $barang->where('kode_barang', 'like', "%{$search}%")
                            ->orWhere('nama_barang', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pengguna', function ($pengguna) use ($search) {
                        $pengguna->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhere('data_sebelum', 'like', "%{$search}%")
                    ->orWhere('data_sesudah', 'like', "%{$search}%");
                });
            })
            ->when(
                !empty($validated['jenis_perubahan']),
                fn ($query) => $query->where(
                    'jenis_perubahan',
                    $validated['jenis_perubahan']
                )
            )
            ->when(
                !empty($validated['tanggal_mulai']),
                fn ($query) => $query->whereDate(
                    'created_at',
                    '>=',
                    $validated['tanggal_mulai']
                )
            )
            ->when(
                !empty($validated['tanggal_akhir']),
                fn ($query) => $query->whereDate(
                    'created_at',
                    '<=',
                    $validated['tanggal_akhir']
                )
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

// Harga modal hanya boleh diketahui Kepala Toko.
if (!$isKepalaToko) {

    $hapusHargaModal = function (&$data) use (&$hapusHargaModal) {
        if (!is_array($data)) {
            return;
        }

        unset($data['harga_modal']);

        foreach ($data as &$nilai) {
            if (is_array($nilai)) {
                $hapusHargaModal($nilai);
            }
        }

        unset($nilai);
    };

    $riwayat->getCollection()->transform(function ($item) use ($hapusHargaModal) {

        $sebelum = $item->data_sebelum ?? [];
        $sesudah = $item->data_sesudah ?? [];

        $hapusHargaModal($sebelum);
        $hapusHargaModal($sesudah);

        $item->data_sebelum = $sebelum;
        $item->data_sesudah = $sesudah;

        return $item;
    });
}

        $routePrefix = $isKepalaToko
            ? 'kepala-toko'
            : 'admin';

        return view('barang.riwayat', compact(
            'riwayat',
            'isKepalaToko',
            'routePrefix'
        ));
    }
}
