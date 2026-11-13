<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servis extends Model
{
    protected $table = 'servis';

    protected $fillable = [
        'pelanggan_id',
        'pengguna_id',
        'nomor_nota',
        'tanggal_servis',
        'nama_teknisi',
        'keluhan',
        'hasil_pemeriksaan',
        'status_perbaikan',
        'biaya_jasa',
        'total_biaya',
        'nominal_dibayar',
        'status_pembayaran',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_servis' => 'date',
            'biaya_jasa' => 'decimal:2',
            'total_biaya' => 'decimal:2',
            'nominal_dibayar' => 'decimal:2',
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

    public function detailServis(): HasMany
    {
        return $this->hasMany(DetailServis::class, 'servis_id');
    }

    public function pendapatan(): HasMany
    {
        return $this->hasMany(Pendapatan::class, 'servis_id');
    }
}
