<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';

    protected $fillable = [
        'pemasok_id',
        'pengguna_id',
        'nomor_nota',
        'tanggal_masuk',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
        ];
    }

    public function pemasok(): BelongsTo
    {
        return $this->belongsTo(Pemasok::class, 'pemasok_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailBarangMasuk::class, 'barang_masuk_id');
    }
}