<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemasokController extends Controller
{
    /**
     * Menentukan route berdasarkan peran pengguna.
     */
    private function indexRouteName(): string
    {
        return Auth::user()->role === 'admin'
            ? 'admin.pemasok.index'
            : 'kepala-toko.pemasok.index';
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $pemasok = Pemasok::query()
            ->withCount('barangMasuk')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pemasok', 'like', "%{$search}%")
                        ->orWhere('nama_kontak', 'like', "%{$search}%")
                        ->orWhere('no_telepon', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_pemasok')
            ->paginate(10)
            ->withQueryString();

        return view('pemasok.index', compact('pemasok', 'search'));
    }

    public function create()
    {
        return view('pemasok.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemasok' => ['required', 'string', 'max:150'],
            'nama_kontak' => ['nullable', 'string', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama_pemasok.required' => 'Nama pemasok wajib diisi.',
            'nama_pemasok.max' => 'Nama pemasok maksimal 150 karakter.',
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
        ]);

        Pemasok::create($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data pemasok berhasil ditambahkan.');
    }

    public function edit(Pemasok $pemasok)
    {
        return view('pemasok.edit', compact('pemasok'));
    }

    public function update(Request $request, Pemasok $pemasok)
    {
        $validated = $request->validate([
            'nama_pemasok' => ['required', 'string', 'max:150'],
            'nama_kontak' => ['nullable', 'string', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama_pemasok.required' => 'Nama pemasok wajib diisi.',
            'nama_pemasok.max' => 'Nama pemasok maksimal 150 karakter.',
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $pemasok->update($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data pemasok berhasil diperbarui.');
    }

    public function destroy(Pemasok $pemasok)
    {
        if ($pemasok->barangMasuk()->exists()) {
            return redirect()
                ->route($this->indexRouteName())
                ->with(
                    'error',
                    'Pemasok tidak dapat dihapus karena sudah digunakan dalam transaksi barang masuk.'
                );
        }

        $pemasok->delete();

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data pemasok berhasil dihapus.');
    }
}
