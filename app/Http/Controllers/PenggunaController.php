<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    // Menampilkan daftar akun Admin
    public function index(Request $request)
    {
        $query = User::where('role', 'admin');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'aktif'
            );
        }

        $pengguna = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pengguna.index', compact('pengguna'));
    }

    // Menampilkan form tambah Admin
    public function create()
    {
        return view('pengguna.create');
    }

    // Menyimpan akun Admin baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'admin',
            'is_active' => true,
        ]);

        return redirect()
            ->route('kepala-toko.pengguna.index')
            ->with('success', 'Akun Admin berhasil ditambahkan.');
    }

    // Menampilkan form edit Admin
    public function edit(User $pengguna)
    {
        $this->pastikanAdmin($pengguna);

        return view('pengguna.edit', compact('pengguna'));
    }

    // Memperbarui data Admin
    public function update(Request $request, User $pengguna)
    {
        $this->pastikanAdmin($pengguna);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($pengguna->id),
            ],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'name.required' => 'Nama Admin wajib diisi.',
            'email.required' => 'Email Admin wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $pengguna->name = $validated['name'];
        $pengguna->email = $validated['email'];

        if (!empty($validated['password'])) {
            $pengguna->password = $validated['password'];
        }

        $pengguna->save();

        return redirect()
            ->route('kepala-toko.pengguna.index')
            ->with('success', 'Data Admin berhasil diperbarui.');
    }

    // Menonaktifkan akun Admin
    public function nonaktifkan(User $pengguna)
    {
        $this->pastikanAdmin($pengguna);

        $pengguna->is_active = false;
        $pengguna->save();

        return redirect()
            ->route('kepala-toko.pengguna.index')
            ->with('success', 'Akun Admin berhasil dinonaktifkan.');
    }

    // Mengaktifkan kembali akun Admin
    public function aktifkan(User $pengguna)
    {
        $this->pastikanAdmin($pengguna);

        $pengguna->is_active = true;
        $pengguna->save();

        return redirect()
            ->route('kepala-toko.pengguna.index')
            ->with('success', 'Akun Admin berhasil diaktifkan kembali.');
    }

    // Memastikan akun yang dikelola benar-benar Admin
    private function pastikanAdmin(User $pengguna): void
    {
        abort_if($pengguna->role !== 'admin', 404);
    }
}
