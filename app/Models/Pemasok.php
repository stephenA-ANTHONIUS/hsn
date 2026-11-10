<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemasok extends Model
{
    protected $table = 'pemasok';

    protected $fillable = [
        'nama_pemasok',
        'nama_kontak',
        'no_telepon',
        'email',
        'alamat',
    ];

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'pemasok_id');
    }
}
