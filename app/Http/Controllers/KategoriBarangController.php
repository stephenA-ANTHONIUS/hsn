<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KategoriBarangController extends Controller
{
    /**
     * Menentukan route berdasarkan peran pengguna.
     */
    private function indexRouteName(): string
    {
        return Auth::user()->role === 'admin'
            ? 'admin.kategori-barang.index'
            : 'kepala-toko.kategori-barang.index';
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $kategori = KategoriBarang::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_kategori', 'like', "%{$search}%");
            })
            ->withCount('barang')
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return view('kategori-barang.index', compact('kategori', 'search'));
    }

    public function create()
    {
        return view('kategori-barang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                'unique:kategori_barang,nama_kategori',
            ],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
            'nama_kategori.unique' => 'Nama kategori tersebut sudah terdaftar.',
        ]);

        KategoriBarang::create($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Kategori barang berhasil ditambahkan.');
    }

    public function edit(KategoriBarang $kategoriBarang)
    {
        return view('kategori-barang.edit', compact('kategoriBarang'));
    }

    public function update(Request $request, KategoriBarang $kategoriBarang)
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_barang', 'nama_kategori')
                    ->ignore($kategoriBarang->id),
            ],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
            'nama_kategori.unique' => 'Nama kategori tersebut sudah digunakan.',
        ]);

        $kategoriBarang->update($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Kategori barang berhasil diperbarui.');
    }

    public function destroy(KategoriBarang $kategoriBarang)
    {
        if ($kategoriBarang->barang()->exists()) {
            return redirect()
                ->route($this->indexRouteName())
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh data barang.'
                );
        }

        $kategoriBarang->delete();

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Kategori barang berhasil dihapus.');
    }
}
