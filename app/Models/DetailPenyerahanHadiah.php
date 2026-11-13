<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenyerahanHadiah extends Model
{
    protected $table = 'detail_penyerahan_hadiah';

    protected $fillable = [
        'penyerahan_hadiah_id',
        'hadiah_id',
        'jumlah',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function penyerahanHadiah(): BelongsTo
    {
        return $this->belongsTo(
            PenyerahanHadiah::class,
            'penyerahan_hadiah_id'
        );
    }

    public function hadiah(): BelongsTo
    {
        return $this->belongsTo(Hadiah::class, 'hadiah_id');
    }
}
