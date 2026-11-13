<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPerubahanBarang extends Model
{
    protected $table = 'riwayat_perubahan_barang';

    protected $fillable = [
        'barang_id',
        'pengguna_id',
        'jenis_perubahan',
        'data_sebelum',
        'data_sesudah',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'data_sebelum' => 'array',
            'data_sesudah' => 'array',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}