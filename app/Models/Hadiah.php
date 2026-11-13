<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hadiah extends Model
{
    protected $table = 'hadiah';

    protected $fillable = [
        'kode_hadiah',
        'nama_hadiah',
        'stok',
        'stok_minimum',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'stok_minimum' => 'integer',
        ];
    }

    public function transaksiStokHadiah(): HasMany
    {
        return $this->hasMany(
            TransaksiStokHadiah::class,
            'hadiah_id'
        );
    }

    public function detailPenyerahanHadiah(): HasMany
    {
        return $this->hasMany(
            DetailPenyerahanHadiah::class,
            'hadiah_id'
        );
    }

    // Relasi hadiah dengan barang
    public function barang(): BelongsToMany
    {
        return $this->belongsToMany(
            Barang::class,
            'barang_hadiah',
            'hadiah_id',
            'barang_id'
        )->withPivot('jumlah_hadiah')
         ->withTimestamps();
    }
}
