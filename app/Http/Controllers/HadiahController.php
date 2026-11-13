<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Hadiah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HadiahController extends Controller
{
    private function routePrefix(): string
    {
        return request()->routeIs('admin.*')
            ? 'admin'
            : 'kepala-toko';
    }

    public function index(Request $request)
    {
        $hadiah = Hadiah::withCount('barang')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_hadiah', 'like', "%{$search}%")
                      ->orWhere('nama_hadiah', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hadiah.index', compact('hadiah'));
    }

    public function create()
    {
        $barang = Barang::orderBy('nama_barang')->get();

        return view('hadiah.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_hadiah' => [
                'required',
                'string',
                'max:50',
                'unique:hadiah,kode_hadiah',
            ],
            'nama_hadiah' => [
                'required',
                'string',
                'max:150',
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
            'barang_hadiah' => [
                'nullable',
                'array',
            ],
            'barang_hadiah.*.barang_id' => [
                'required',
                'integer',
                'distinct',
                'exists:barang,id',
            ],
            'barang_hadiah.*.jumlah_hadiah' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $hadiah = Hadiah::create([
                'kode_hadiah' => $validated['kode_hadiah'],
                'nama_hadiah' => $validated['nama_hadiah'],
                'stok' => 0,
                'stok_minimum' => $validated['stok_minimum'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'status' => $validated['status'],
            ]);

            $hadiah->barang()->sync(
                $this->formatBarangHadiah(
                    $validated['barang_hadiah'] ?? []
                )
            );
        });

        return redirect()
            ->route($this->routePrefix() . '.hadiah.index')
            ->with('success', 'Data hadiah berhasil ditambahkan.');
    }

    public function edit(Hadiah $hadiah)
    {
        $hadiah->load('barang');

        $barang = Barang::orderBy('nama_barang')->get();

        return view('hadiah.edit', compact('hadiah', 'barang'));
    }

    public function update(Request $request, Hadiah $hadiah)
    {
        $validated = $request->validate([
            'kode_hadiah' => [
                'required',
                'string',
                'max:50',
                Rule::unique('hadiah', 'kode_hadiah')
                    ->ignore($hadiah->id),
            ],
            'nama_hadiah' => [
                'required',
                'string',
                'max:150',
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
            'barang_hadiah' => [
                'nullable',
                'array',
            ],
            'barang_hadiah.*.barang_id' => [
                'required',
                'integer',
                'distinct',
                'exists:barang,id',
            ],
            'barang_hadiah.*.jumlah_hadiah' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($validated, $hadiah) {
            $hadiah->update([
                'kode_hadiah' => $validated['kode_hadiah'],
                'nama_hadiah' => $validated['nama_hadiah'],
                'stok_minimum' => $validated['stok_minimum'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'status' => $validated['status'],
            ]);

            $hadiah->barang()->sync(
                $this->formatBarangHadiah(
                    $validated['barang_hadiah'] ?? []
                )
            );
        });

        return redirect()
            ->route($this->routePrefix() . '.hadiah.index')
            ->with('success', 'Data hadiah berhasil diperbarui.');
    }

    private function formatBarangHadiah(array $data): array
    {
        $hasil = [];

        foreach ($data as $item) {
            $hasil[$item['barang_id']] = [
                'jumlah_hadiah' => $item['jumlah_hadiah'],
            ];
        }

        return $hasil;
    }
}
