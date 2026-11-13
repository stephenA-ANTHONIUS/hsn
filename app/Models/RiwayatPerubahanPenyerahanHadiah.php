<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPerubahanPenyerahanHadiah extends Model
{
    protected $table = 'riwayat_perubahan_penyerahan_hadiah';

    protected $fillable = [
        'penyerahan_hadiah_id',
        'pengguna_id',
        'aksi',
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

    public function penyerahanHadiah(): BelongsTo
    {
        return $this->belongsTo(
            PenyerahanHadiah::class,
            'penyerahan_hadiah_id'
        );
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
