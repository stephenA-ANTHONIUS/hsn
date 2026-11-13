<?php

namespace App\Http\Controllers;

use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SatuanBarangController extends Controller
{
    /**
     * Menentukan route berdasarkan peran pengguna.
     */
    private function indexRouteName(): string
    {
        return Auth::user()->role === 'admin'
            ? 'admin.satuan-barang.index'
            : 'kepala-toko.satuan-barang.index';
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $satuan = SatuanBarang::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama_satuan', 'like', "%{$search}%")
                        ->orWhere('singkatan', 'like', "%{$search}%");
                });
            })
            ->withCount('barang')
            ->orderBy('nama_satuan')
            ->paginate(10)
            ->withQueryString();

        return view('satuan-barang.index', compact('satuan', 'search'));
    }

    public function create()
    {
        return view('satuan-barang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan' => [
                'required',
                'string',
                'max:50',
                'unique:satuan_barang,nama_satuan',
            ],
            'singkatan' => [
                'required',
                'string',
                'max:20',
            ],
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.max' => 'Nama satuan maksimal 50 karakter.',
            'nama_satuan.unique' => 'Nama satuan tersebut sudah terdaftar.',
            'singkatan.required' => 'Singkatan satuan wajib diisi.',
            'singkatan.max' => 'Singkatan maksimal 20 karakter.',
        ]);

        SatuanBarang::create($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Satuan barang berhasil ditambahkan.');
    }

    public function edit(SatuanBarang $satuanBarang)
    {
        return view('satuan-barang.edit', compact('satuanBarang'));
    }

    public function update(Request $request, SatuanBarang $satuanBarang)
    {
        $validated = $request->validate([
            'nama_satuan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('satuan_barang', 'nama_satuan')
                    ->ignore($satuanBarang->id),
            ],
            'singkatan' => [
                'required',
                'string',
                'max:20',
            ],
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.max' => 'Nama satuan maksimal 50 karakter.',
            'nama_satuan.unique' => 'Nama satuan tersebut sudah digunakan.',
            'singkatan.required' => 'Singkatan satuan wajib diisi.',
            'singkatan.max' => 'Singkatan maksimal 20 karakter.',
        ]);

        $satuanBarang->update($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Satuan barang berhasil diperbarui.');
    }

    public function destroy(SatuanBarang $satuanBarang)
    {
        if ($satuanBarang->barang()->exists()) {
            return redirect()
                ->route($this->indexRouteName())
                ->with(
                    'error',
                    'Satuan tidak dapat dihapus karena masih digunakan oleh data barang.'
                );
        }

        $satuanBarang->delete();

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Satuan barang berhasil dihapus.');
    }
}
