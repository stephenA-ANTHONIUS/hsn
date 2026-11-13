<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Pendapatan;
use App\Models\RiwayatStok;
use App\Models\Servis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServisController extends Controller
{
    private function routePrefix(): string
    {
        return Auth::user()->role === 'admin' ? 'admin' : 'kepala-toko';
    }

    /**
     * Menyamakan format nomor HP.
     * 0812..., 62812..., dan +62812... dianggap sama.
     */
    private function normalisasiNomorHp(string $nomor): string
    {
        $angka = preg_replace('/\D+/', '', $nomor);

        if (str_starts_with($angka, '0')) {
            $angka = '62' . substr($angka, 1);
        } elseif (str_starts_with($angka, '8')) {
            $angka = '62' . $angka;
        }

        return $angka;
    }

    /**
     * Mencari atau membuat data pelanggan untuk transaksi servis.
     */
    private function cariAtauBuatPelanggan(array $validated): Pelanggan
    {
        $nama = trim($validated['nama_pelanggan']);
        $nomorHp = $this->normalisasiNomorHp($validated['no_telepon']);
        $aksi = $validated['aksi_pelanggan'] ?? null;

        $daftarPelanggan = Pelanggan::query()
            ->lockForUpdate()
            ->get();

        $pelangganDenganNomorSama = $daftarPelanggan
            ->filter(function ($pelanggan) use ($nomorHp) {
                return $this->normalisasiNomorHp(
                    $pelanggan->no_telepon ?? ''
                ) === $nomorHp;
            })
            ->values();

        $pelangganSama = $pelangganDenganNomorSama->first(function ($pelanggan) use ($nama) {
            return mb_strtolower(trim($pelanggan->nama_pelanggan))
                === mb_strtolower($nama);
        });

        if ($pelangganSama) {
            return $pelangganSama;
        }

        if ($pelangganDenganNomorSama->isNotEmpty()) {
            if ($aksi === 'perbarui_nama') {
                if ($pelangganDenganNomorSama->count() !== 1) {
                    throw ValidationException::withMessages([
                        'aksi_pelanggan' => 'Nomor HP ini sudah digunakan oleh beberapa profil pelanggan. Silakan pilih Buat Profil Pelanggan Baru.',
                    ]);
                }

                $pelanggan = $pelangganDenganNomorSama->first();

                $pelanggan->update([
                    'nama_pelanggan' => $nama,
                    'no_telepon' => '+' . $nomorHp,
                ]);

                return $pelanggan;
            }

            if ($aksi === 'buat_baru') {
                return Pelanggan::create([
                    'nama_pelanggan' => $nama,
                    'no_telepon' => '+' . $nomorHp,
                ]);
            }

            throw ValidationException::withMessages([
                'aksi_pelanggan' => 'Nomor HP tersebut sudah terdaftar dengan nama pelanggan yang berbeda. Pilih apakah ingin memperbarui nama pelanggan lama atau membuat profil pelanggan baru.',
            ]);
        }

        return Pelanggan::create([
            'nama_pelanggan' => $nama,
            'no_telepon' => '+' . $nomorHp,
        ]);
    }

    public function index(Request $request)
    {
        $query = Servis::with(['pelanggan', 'pengguna'])
            ->withCount('detailServis');

        if ($request->filled('cari')) {
            $cari = $request->cari;

            $query->where(function ($q) use ($cari) {
                $q->where('nomor_nota', 'like', "%{$cari}%")
                    ->orWhere('nama_teknisi', 'like', "%{$cari}%")
                    ->orWhereHas('pelanggan', function ($pelanggan) use ($cari) {
                        $pelanggan->where('nama_pelanggan', 'like', "%{$cari}%")
                            ->orWhere('no_telepon', 'like', "%{$cari}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status_perbaikan', $request->status);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_servis', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_servis', '<=', $request->tanggal_sampai);
        }

        $servis = $query->latest('tanggal_servis')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('servis.index', [
            'servis' => $servis,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function create()
    {
        return view('servis.create', [
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    /**
     * Membuat transaksi awal.
     * Sparepart, hasil pemeriksaan, dan biaya jasa dilengkapi setelah transaksi dibuat.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'no_telepon' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\s().-]+$/',
            ],
            'aksi_pelanggan' => [
                'nullable',
                Rule::in(['perbarui_nama', 'buat_baru']),
            ],
            'tanggal_servis' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'nama_teknisi' => ['nullable', 'string', 'max:150'],
            'keluhan' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $prefix = $this->routePrefix();

        $servis = DB::transaction(function () use ($validated) {
            $pelanggan = $this->cariAtauBuatPelanggan($validated);

            $nomorNota = 'SV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            while (Servis::where('nomor_nota', $nomorNota)->exists()) {
                $nomorNota = 'SV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
            }

            return Servis::create([
                'pelanggan_id' => $pelanggan->id,
                'pengguna_id' => Auth::id(),
                'nomor_nota' => $nomorNota,
                'tanggal_servis' => $validated['tanggal_servis'],
                'nama_teknisi' => $validated['nama_teknisi'] ?? null,
                'keluhan' => $validated['keluhan'],
                'hasil_pemeriksaan' => null,
                'status_perbaikan' => 'menunggu_persetujuan',
                'biaya_jasa' => 0,
                'total_biaya' => 0,
                'nominal_dibayar' => 0,
                'status_pembayaran' => 'belum_dibayar',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()
            ->route($prefix . '.servis.show', $servis->id)
            ->with('success', 'Transaksi servis berhasil dibuat. Silakan lengkapi hasil pemeriksaan, biaya jasa, dan sparepart setelah teknisi memeriksa perangkat.');
    }

    public function show(Servis $servis)
    {
        $servis->load([
            'pelanggan',
            'pengguna',
            'detailServis.barang.satuan',
            'pendapatan.pengguna',
        ]);

$barang = Barang::with([
    'satuan',
    'hadiah' => function ($query) {
        $query->where('hadiah.status', 'aktif');
    },
])
    ->where('status', 'aktif')
    ->orderBy('nama_barang')
    ->get();

        return view('servis.show', [
            'servis' => $servis,
            'barang' => $barang,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function nota(Servis $servis)
    {
        $servis->load([
            'pelanggan',
            'pengguna',
            'detailServis.barang.satuan',
            'pendapatan.pengguna',
        ]);

        return view('servis.nota', [
            'servis' => $servis,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    /**
     * Melengkapi hasil pemeriksaan, biaya jasa, keterangan,
     * dan daftar sparepart selama masih menunggu persetujuan.
     */
    public function updateData(Request $request, Servis $servis)
    {
        $validated = $request->validate([
            'hasil_pemeriksaan' => ['nullable', 'string'],
            'biaya_jasa' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'detail_servis' => ['nullable', 'array'],
            'detail_servis.*.barang_id' => [
                'required',
                'distinct',
                'exists:barang,id',
            ],
            'detail_servis.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($servis, $validated) {
            $transaksi = Servis::whereKey($servis->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaksi->status_perbaikan !== 'menunggu_persetujuan') {
                throw ValidationException::withMessages([
                    'hasil_pemeriksaan' => 'Hasil pemeriksaan, biaya jasa, dan sparepart hanya dapat diubah selama status Menunggu Persetujuan.',
                ]);
            }

            $detailInput = $validated['detail_servis'] ?? [];
            $totalSparepart = 0;
            $detailTersimpan = [];

            foreach ($detailInput as $detail) {
                $barang = Barang::whereKey($detail['barang_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($barang->status !== 'aktif') {
                    throw ValidationException::withMessages([
                        'detail_servis' => "Barang {$barang->nama_barang} sudah tidak aktif.",
                    ]);
                }

                $jumlah = (int) $detail['jumlah'];
                $harga = (float) $barang->harga_jual;
                $subtotal = $harga * $jumlah;

                $totalSparepart += $subtotal;

                $detailTersimpan[] = [
                    'barang_id' => $barang->id,
                    'jumlah' => $jumlah,
                    'harga_sparepart' => $harga,
                    'subtotal' => $subtotal,
                    'status_persetujuan' => 'menunggu',
                    'status_penggunaan' => 'diminta',
                ];
            }

            // Detail lama diganti dengan daftar sparepart terbaru.
            // Belum ada perubahan stok pada tahap ini.
            $transaksi->detailServis()->delete();

            foreach ($detailTersimpan as $detail) {
                $transaksi->detailServis()->create($detail);
            }

            $biayaJasa = (float) ($validated['biaya_jasa'] ?? 0);

            $transaksi->update([
                'hasil_pemeriksaan' => $validated['hasil_pemeriksaan'] ?? null,
                'biaya_jasa' => $biayaJasa,
                'keterangan' => $validated['keterangan'] ?? null,
                'total_biaya' => $totalSparepart + $biayaJasa,
            ]);
        });

        return redirect()
            ->route($this->routePrefix() . '.servis.show', $servis->id)
            ->with('success', 'Hasil pemeriksaan, biaya jasa, dan daftar sparepart berhasil diperbarui. Stok belum dikurangi.');
    }

    /**
     * Mencatat pembayaran servis, termasuk pembayaran bertahap.
     */
    public function storePembayaran(Request $request, Servis $servis)
    {
        $validated = $request->validate([
            'nominal' => ['required', 'numeric', 'gt:0'],
            'metode_pembayaran' => [
                'required',
                'string',
                Rule::in(['Tunai', 'Transfer', 'QRIS']),
            ],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $servis) {
            $transaksi = Servis::whereKey($servis->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($transaksi->status_perbaikan, [
                'proses',
                'selesai',
                'diambil',
                'dibatalkan',
            ], true)) {
                throw ValidationException::withMessages([
                    'nominal' => 'Pembayaran hanya dapat dicatat setelah pelanggan menyetujui servis atau ketika biaya jasa pembatalan sudah ditetapkan.',
                ]);
            }

            $totalBiaya = (float) $transaksi->total_biaya;
            $sudahDibayar = (float) $transaksi->nominal_dibayar;
            $sisaTagihan = round($totalBiaya - $sudahDibayar, 2);
            $nominalBayar = round((float) $validated['nominal'], 2);

            if ($sisaTagihan <= 0) {
                throw ValidationException::withMessages([
                    'nominal' => 'Tagihan servis ini sudah lunas.',
                ]);
            }

            if ($nominalBayar > $sisaTagihan) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal pembayaran melebihi sisa tagihan sebesar Rp'
                        . number_format($sisaTagihan, 0, ',', '.'),
                ]);
            }

            $totalDibayarBaru = round($sudahDibayar + $nominalBayar, 2);

            $statusPembayaran = $totalDibayarBaru >= $totalBiaya
                ? 'lunas'
                : ($totalDibayarBaru > 0 ? 'sebagian' : 'belum_dibayar');

            Pendapatan::create([
                'pengguna_id' => Auth::id(),
                'servis_id' => $transaksi->id,
                'tanggal_pendapatan' => now()->toDateString(),
                'kategori_pendapatan' => 'servis',
                'nominal' => $nominalBayar,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan' => $validated['keterangan']
                    ?? 'Pembayaran servis nota ' . $transaksi->nomor_nota,
            ]);

            $transaksi->update([
                'nominal_dibayar' => $totalDibayarBaru,
                'status_pembayaran' => $statusPembayaran,
            ]);
        });

        return redirect()
            ->route($this->routePrefix() . '.servis.show', $servis->id)
            ->with('success', 'Pembayaran servis berhasil dicatat.');
    }

    public function updateStatus(Request $request, Servis $servis)
    {
        $validated = $request->validate([
            'status_perbaikan' => [
                'required',
                'in:proses,selesai,diambil,dibatalkan',
            ],
        ]);

        $statusBaru = $validated['status_perbaikan'];

        DB::transaction(function () use ($servis, $statusBaru) {
            $servis = Servis::whereKey($servis->id)
                ->lockForUpdate()
                ->firstOrFail();

            $statusLama = $servis->status_perbaikan;

            if (
                $statusLama === 'menunggu_persetujuan'
                && $statusBaru === 'proses'
            ) {
                $detailServis = $servis->detailServis()
                    ->orderBy('barang_id')
                    ->get();

                foreach ($detailServis as $detail) {
                    $barang = Barang::whereKey($detail->barang_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($barang->status !== 'aktif') {
                        throw ValidationException::withMessages([
                            'status_perbaikan' => "Barang {$barang->nama_barang} sudah tidak aktif.",
                        ]);
                    }

                    if ($barang->stok < $detail->jumlah) {
                        throw ValidationException::withMessages([
                            'status_perbaikan' => "Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok}.",
                        ]);
                    }
                }

                foreach ($detailServis as $detail) {
                    $barang = Barang::whereKey($detail->barang_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $barang->decrement('stok', $detail->jumlah);

                    $detail->update([
                        'status_persetujuan' => 'disetujui',
                        'status_penggunaan' => 'digunakan',
                    ]);

                    RiwayatStok::create([
                        'barang_id' => $barang->id,
                        'pengguna_id' => Auth::id(),
                        'jenis_transaksi' => 'servis',
                        'jenis_perubahan' => 'keluar',
                        'jumlah' => $detail->jumlah,
                        'referensi_tabel' => 'servis',
                        'referensi_id' => $servis->id,
                        'keterangan' => 'Penggunaan sparepart untuk servis ' . $servis->nomor_nota,
                    ]);
                }

                $totalSparepart = (float) $servis->detailServis()
                    ->sum('subtotal');

                $servis->update([
                    'status_perbaikan' => 'proses',
                    'total_biaya' => $totalSparepart + (float) $servis->biaya_jasa,
                ]);

                return;
            }

            if (
                $statusLama === 'menunggu_persetujuan'
                && $statusBaru === 'dibatalkan'
            ) {
                $servis->detailServis()->update([
                    'status_persetujuan' => 'ditolak',
                    'status_penggunaan' => 'tidak_digunakan',
                ]);

                $servis->update([
                    'status_perbaikan' => 'dibatalkan',
                    'total_biaya' => (float) $servis->biaya_jasa,
                ]);

                return;
            }

            if ($statusLama === 'proses' && $statusBaru === 'selesai') {
                $servis->update([
                    'status_perbaikan' => 'selesai',
                ]);

                return;
            }

            if ($statusLama === 'selesai' && $statusBaru === 'diambil') {
                $totalBiaya = (float) $servis->total_biaya;
                $nominalDibayar = (float) $servis->nominal_dibayar;

                if (
                    $servis->status_pembayaran !== 'lunas'
                    || $nominalDibayar < $totalBiaya
                ) {
                    throw ValidationException::withMessages([
                        'status_perbaikan' => 'Perangkat belum dapat diserahkan karena pembayaran servis belum lunas.',
                    ]);
                }

                $servis->update([
                    'status_perbaikan' => 'diambil',
                ]);

                return;
            }

            throw ValidationException::withMessages([
                'status_perbaikan' => 'Perubahan status tersebut tidak diperbolehkan.',
            ]);
        });

        return redirect()
            ->route($this->routePrefix() . '.servis.show', $servis->id)
            ->with('success', 'Status servis berhasil diperbarui.');
    }
}
