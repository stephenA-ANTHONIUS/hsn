<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatuanBarang extends Model
{
    protected $table = 'satuan_barang';

    protected $fillable = [
        'nama_satuan',
        'singkatan',
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'satuan_barang_id');
    }
}
