<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendapatan extends Model
{
    protected $table = 'pendapatan';

    protected $fillable = [
        'pengguna_id',
        'penjualan_id',
        'servis_id',
        'tanggal_pendapatan',
        'kategori_pendapatan',
        'nominal',
        'metode_pembayaran',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pendapatan' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }
}
