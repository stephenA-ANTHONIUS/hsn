<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailServis extends Model
{
    protected $table = 'detail_servis';

    protected $fillable = [
        'servis_id',
        'barang_id',
        'jumlah',
        'harga_sparepart',
        'subtotal',
        'status_persetujuan',
        'status_penggunaan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_sparepart' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
