<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_perubahan_barang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->nullable()
                ->constrained('barang')
                ->nullOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('jenis_perubahan', 30);

            $table->json('data_sebelum')->nullable();
            $table->json('data_sesudah')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['barang_id', 'created_at']);
            $table->index(['pengguna_id', 'created_at']);
            $table->index('jenis_perubahan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_barang');
    }
};
