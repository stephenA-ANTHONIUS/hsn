<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelangganController extends Controller
{
    /**
     * Menentukan route berdasarkan peran pengguna.
     */
    private function indexRouteName(): string
    {
        return Auth::user()->role === 'admin'
            ? 'admin.pelanggan.index'
            : 'kepala-toko.pelanggan.index';
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $pelanggan = Pelanggan::query()
            ->withCount([
                'penjualan',
                'servis',
                'penyerahanHadiah',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pelanggan', 'like', "%{$search}%")
                        ->orWhere('no_telepon', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggan.index', compact('pelanggan', 'search'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 150 karakter.',
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
        ]);

        Pelanggan::create($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 150 karakter.',
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
        ]);

        $pelanggan->update($validated);

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $sudahDigunakan =
            $pelanggan->penjualan()->exists()
            || $pelanggan->servis()->exists()
            || $pelanggan->penyerahanHadiah()->exists();

        if ($sudahDigunakan) {
            return redirect()
                ->route($this->indexRouteName())
                ->with(
                    'error',
                    'Pelanggan tidak dapat dihapus karena sudah memiliki riwayat transaksi.'
                );
        }

        $pelanggan->delete();

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
