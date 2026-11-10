<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenyerahanHadiah extends Model
{
    protected $table = 'penyerahan_hadiah';

    protected $fillable = [
        'pelanggan_id',
        'penjualan_id',
        'pengguna_id',
        'tanggal_penyerahan',
        'foto_bukti',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penyerahan' => 'date',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
    public function detailPenyerahanHadiah(): HasMany
    {
        return $this->hasMany(
            DetailPenyerahanHadiah::class,
            'penyerahan_hadiah_id'
        );
    }
    }
