<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
