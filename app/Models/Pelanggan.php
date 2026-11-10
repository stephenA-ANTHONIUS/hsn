<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'nama_pelanggan',
        'no_telepon',
        'alamat',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'pelanggan_id');
    }

    public function servis(): HasMany
    {
        return $this->hasMany(Servis::class, 'pelanggan_id');
    }

    public function penyerahanHadiah(): HasMany
    {
        return $this->hasMany(PenyerahanHadiah::class, 'pelanggan_id');
    }
}
