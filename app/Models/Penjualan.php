<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'pelanggan_id',
        'pengguna_id',
        'nomor_nota',
        'tanggal_penjualan',
        'total_penjualan',
        'metode_pembayaran',
        'status_pembayaran',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penjualan' => 'date',
            'total_penjualan' => 'decimal:2',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    public function pendapatan(): HasMany
    {
        return $this->hasMany(Pendapatan::class, 'penjualan_id');
    }
}
