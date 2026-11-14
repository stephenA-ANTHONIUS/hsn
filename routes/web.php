<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\SatuanBarangController;
use App\Http\Controllers\HadiahController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PenyerahanHadiahController;
use App\Http\Controllers\RiwayatPerubahanBarangController;
use App\Http\Controllers\ServisController;
use App\Http\Controllers\TransaksiStokHadiahController;

// =====================================================
// LOGIN
// =====================================================

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

// =====================================================
// LOGOUT
// =====================================================

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =====================================================
// PROFIL AKUN
// =====================================================

Route::middleware(['auth', 'auth.session'])->group(function () {

    // Melihat profil akun sendiri.
    Route::get('/akun-saya', function () {
        return view('profile.show', [
            'user' => auth()->user(),
        ]);
    })->name('profile.show');

    // Mengubah nama dan email sendiri: Kepala Toko saja.
    Route::put('/akun-saya', function (Request $request) {

        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Nama dan email akun berhasil diperbarui.');

    })->middleware('role:kepala_toko')
      ->name('profile.update');

    // Mengubah password sendiri: Kepala Toko saja.
    Route::put('/akun-saya/password', function (Request $request) {

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Password akun berhasil diperbarui.');

    })->middleware('role:kepala_toko')
      ->name('profile.password.update');

});

// =====================================================
// DASHBOARD DAN FITUR KEPALA TOKO
// =====================================================

Route::middleware(['auth', 'auth.session', 'role:kepala_toko'])
    ->prefix('kepala-toko')
    ->name('kepala-toko.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('dashboards.kepala-toko');
        })->name('dashboard');

        // Laporan
        Route::prefix('laporan')
            ->name('laporan.')
            ->controller(LaporanController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/barang-masuk', 'barangMasuk')->name('barang-masuk');
                Route::get('/barang-keluar', 'barangKeluar')->name('barang-keluar');
                Route::get('/riwayat-transaksi', 'riwayatTransaksi')->name('riwayat-transaksi');
                Route::get('/stok', 'stok')->name('stok');
                Route::get('/penjualan-kotor', 'penjualanKotor')->name('penjualan-kotor');
            });

        // Kategori barang
        Route::resource('kategori-barang', KategoriBarangController::class)
            ->except(['show'])
            ->parameters([
                'kategori-barang' => 'kategoriBarang',
            ]);

        // Satuan barang
        Route::resource('satuan-barang', SatuanBarangController::class)
            ->except(['show'])
            ->parameters([
                'satuan-barang' => 'satuanBarang',
            ]);

        // Riwayat perubahan barang
        Route::get('/barang/riwayat-perubahan', [
            RiwayatPerubahanBarangController::class,
            'index'
        ])->name('barang.riwayat');

        // Pemasok
        Route::resource('pemasok', PemasokController::class)
            ->except(['show']);

        // Pelanggan
        Route::resource('pelanggan', PelangganController::class)
            ->except(['show']);

        // Data barang
        Route::resource('barang', BarangController::class)
            ->except(['show', 'destroy']);

        Route::patch('/barang/{barang}/nonaktifkan', [BarangController::class, 'nonaktifkan'])
            ->name('barang.nonaktifkan');

        Route::patch('/barang/{barang}/aktifkan', [BarangController::class, 'aktifkan'])
            ->name('barang.aktifkan');

        // Barang masuk
        Route::get('/barang-masuk', [BarangMasukController::class, 'index'])
            ->name('barang-masuk.index');

        Route::get('/barang-masuk/create', [BarangMasukController::class, 'create'])
            ->name('barang-masuk.create');

        Route::post('/barang-masuk', [BarangMasukController::class, 'store'])
            ->name('barang-masuk.store');

        Route::get('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'show'])
            ->name('barang-masuk.show');

        Route::get('/barang-masuk/{barangMasuk}/edit', [BarangMasukController::class, 'edit'])
            ->name('barang-masuk.edit');

        Route::put('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'update'])
            ->name('barang-masuk.update');

        // Pengguna
        Route::resource('pengguna', PenggunaController::class)
            ->except(['show', 'destroy']);

        Route::patch('/pengguna/{pengguna}/nonaktifkan', [PenggunaController::class, 'nonaktifkan'])
            ->name('pengguna.nonaktifkan');

        Route::patch('/pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])
            ->name('pengguna.aktifkan');

        // Data hadiah
        Route::resource('hadiah', HadiahController::class)
            ->except(['show', 'destroy']);

        // Stok hadiah
        Route::prefix('hadiah/stok')
            ->name('hadiah.stok.')
            ->controller(TransaksiStokHadiahController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
            });

        // Penyerahan hadiah
        Route::prefix('penyerahan-hadiah')
            ->name('penyerahan-hadiah.')
            ->controller(PenyerahanHadiahController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{penyerahanHadiah}/riwayat', 'riwayat')->name('riwayat');
                Route::get('/{penyerahanHadiah}/edit', 'edit')->name('edit');
                Route::put('/{penyerahanHadiah}', 'update')->name('update');
            });

        // Penjualan
        Route::prefix('penjualan')
            ->name('penjualan.')
            ->controller(PenjualanController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{penjualan}/edit', 'edit')->name('edit');
                Route::put('/{penjualan}', 'update')->name('update');
                Route::get('/{penjualan}/riwayat', 'riwayat')->name('riwayat');
                Route::get('/{penjualan}/nota', 'nota')->name('nota');
            });

        // Servis
        Route::prefix('servis')
            ->name('servis.')
            ->controller(ServisController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');

                Route::get('/{servis}/nota', 'nota')->name('nota');
                Route::get('/{servis}', 'show')->name('show');

                Route::patch('/{servis}/status', 'updateStatus')
                    ->name('update-status');

                Route::post('/{servis}/pembayaran', 'storePembayaran')
                    ->name('pembayaran');
            });

        Route::patch('/servis/{servis}/data', [ServisController::class, 'updateData'])
            ->name('servis.update-data');
    });

// =====================================================
// DASHBOARD DAN FITUR ADMIN
// =====================================================

Route::middleware(['auth', 'auth.session', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('dashboards.admin');
        })->name('dashboard');

        // Laporan
        Route::prefix('laporan')
            ->name('laporan.')
            ->controller(LaporanController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/barang-masuk', 'barangMasuk')->name('barang-masuk');
                Route::get('/barang-keluar', 'barangKeluar')->name('barang-keluar');
                Route::get('/riwayat-transaksi', 'riwayatTransaksi')->name('riwayat-transaksi');
                Route::get('/stok', 'stok')->name('stok');
                Route::get('/penjualan-kotor', 'penjualanKotor')->name('penjualan-kotor');
            });

        // Barang masuk
        Route::get('/barang-masuk', [BarangMasukController::class, 'index'])
            ->name('barang-masuk.index');

        Route::get('/barang-masuk/create', [BarangMasukController::class, 'create'])
            ->name('barang-masuk.create');

        Route::post('/barang-masuk', [BarangMasukController::class, 'store'])
            ->name('barang-masuk.store');

        Route::get('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'show'])
            ->name('barang-masuk.show');

        Route::get('/barang-masuk/{barangMasuk}/edit', [BarangMasukController::class, 'edit'])
            ->name('barang-masuk.edit');

        Route::put('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'update'])
            ->name('barang-masuk.update');

        // Kategori barang
        Route::resource('kategori-barang', KategoriBarangController::class)
            ->except(['show'])
            ->parameters([
                'kategori-barang' => 'kategoriBarang',
            ]);

        // Satuan barang
        Route::resource('satuan-barang', SatuanBarangController::class)
            ->except(['show'])
            ->parameters([
                'satuan-barang' => 'satuanBarang',
            ]);

        // Riwayat perubahan barang
        Route::get('/barang/riwayat-perubahan', [
            RiwayatPerubahanBarangController::class,
            'index'
        ])->name('barang.riwayat');

        // Pemasok
        Route::resource('pemasok', PemasokController::class)
            ->except(['show']);

        // Pelanggan
        Route::resource('pelanggan', PelangganController::class)
            ->except(['show']);

        // Data barang
        Route::resource('barang', BarangController::class)
            ->except(['show', 'destroy']);

        Route::patch('/barang/{barang}/nonaktifkan', [BarangController::class, 'nonaktifkan'])
            ->name('barang.nonaktifkan');

        Route::patch('/barang/{barang}/aktifkan', [BarangController::class, 'aktifkan'])
            ->name('barang.aktifkan');

        // Data hadiah
        Route::resource('hadiah', HadiahController::class)
            ->except(['show', 'destroy']);

        // Stok hadiah
        Route::prefix('hadiah/stok')
            ->name('hadiah.stok.')
            ->controller(TransaksiStokHadiahController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
            });

        // Penyerahan hadiah
        Route::prefix('penyerahan-hadiah')
            ->name('penyerahan-hadiah.')
            ->controller(PenyerahanHadiahController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{penyerahanHadiah}/riwayat', 'riwayat')->name('riwayat');
                Route::get('/{penyerahanHadiah}/edit', 'edit')->name('edit');
                Route::put('/{penyerahanHadiah}', 'update')->name('update');
            });

        // Penjualan
        Route::prefix('penjualan')
            ->name('penjualan.')
            ->controller(PenjualanController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{penjualan}/edit', 'edit')->name('edit');
                Route::put('/{penjualan}', 'update')->name('update');
                Route::get('/{penjualan}/riwayat', 'riwayat')->name('riwayat');
                Route::get('/{penjualan}/nota', 'nota')->name('nota');
            });

        // Servis
        Route::prefix('servis')
            ->name('servis.')
            ->controller(ServisController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');

                Route::get('/{servis}/nota', 'nota')->name('nota');
                Route::get('/{servis}', 'show')->name('show');

                Route::patch('/{servis}/status', 'updateStatus')
                    ->name('update-status');

                Route::post('/{servis}/pembayaran', 'storePembayaran')
                    ->name('pembayaran');
            });

        Route::patch('/servis/{servis}/data', [ServisController::class, 'updateData'])
            ->name('servis.update-data');
    });
