<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStok extends Model
{
    protected $table = 'riwayat_stok';

    protected $fillable = [
        'barang_id',
        'pengguna_id',
        'jenis_transaksi',
        'jenis_perubahan',
        'jumlah',
        'referensi_tabel',
        'referensi_id',
        'keterangan',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}