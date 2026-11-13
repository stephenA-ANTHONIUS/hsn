<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kategori_barang_id',
        'satuan_barang_id',
        'kode_barang',
        'nama_barang',
        'harga_modal',
        'harga_jual',
        'stok',
        'stok_minimum',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_modal' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(SatuanBarang::class, 'satuan_barang_id');
    }

    public function detailBarangMasuk(): HasMany
    {
        return $this->hasMany(DetailBarangMasuk::class, 'barang_id');
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'barang_id');
    }

    public function detailServis(): HasMany
    {
        return $this->hasMany(DetailServis::class, 'barang_id');
    }

    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatStok::class, 'barang_id');
    }

    // Relasi barang dengan hadiah
    public function hadiah(): BelongsToMany
    {
        return $this->belongsToMany(
            Hadiah::class,
            'barang_hadiah',
            'barang_id',
            'hadiah_id'
        )->withPivot('jumlah_hadiah')
         ->withTimestamps();
    }
}
