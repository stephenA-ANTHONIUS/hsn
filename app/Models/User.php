<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Memeriksa apakah pengguna merupakan Kepala Toko
    public function isKepalaToko(): bool
    {
        return $this->role === 'kepala_toko';
    }

    // Memeriksa apakah pengguna merupakan Admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Relasi transaksi barang masuk
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'pengguna_id');
    }

    // Relasi riwayat stok barang
    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatStok::class, 'pengguna_id');
    }

    // Relasi penjualan
    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'pengguna_id');
    }

    // Relasi servis
    public function servis(): HasMany
    {
        return $this->hasMany(Servis::class, 'pengguna_id');
    }

    // Relasi transaksi stok hadiah
    public function transaksiStokHadiah(): HasMany
    {
        return $this->hasMany(TransaksiStokHadiah::class, 'pengguna_id');
    }

    // Relasi penyerahan hadiah
    public function penyerahanHadiah(): HasMany
    {
        return $this->hasMany(PenyerahanHadiah::class, 'pengguna_id');
    }

    // Relasi pendapatan
    public function pendapatan(): HasMany
    {
        return $this->hasMany(Pendapatan::class, 'pengguna_id');
    }
}
