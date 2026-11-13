<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_perubahan_penjualan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penjualan_id')
                ->constrained('penjualan')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->json('data_sebelum');
            $table->json('data_sesudah');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_penjualan');
    }
};
