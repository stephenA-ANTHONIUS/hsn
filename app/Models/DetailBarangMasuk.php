<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBarangMasuk extends Model
{
    protected $table = 'detail_barang_masuk';

    protected $fillable = [
        'barang_masuk_id',
        'barang_id',
        'jumlah',
        'harga_modal',
        'harga_jual',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_modal' => 'decimal:2',
            'harga_jual' => 'decimal:2',
        ];
    }

    public function barangMasuk(): BelongsTo
    {
        return $this->belongsTo(BarangMasuk::class, 'barang_masuk_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
