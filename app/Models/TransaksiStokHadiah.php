<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiStokHadiah extends Model
{
    protected $table = 'transaksi_stok_hadiah';

    protected $fillable = [
        'hadiah_id',
        'pengguna_id',
        'jenis_transaksi',
        'jumlah',
        'tanggal_transaksi',
        'referensi_tabel',
        'referensi_id',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'tanggal_transaksi' => 'date',
            'referensi_id' => 'integer',
        ];
    }

    public function hadiah(): BelongsTo
    {
        return $this->belongsTo(Hadiah::class, 'hadiah_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
