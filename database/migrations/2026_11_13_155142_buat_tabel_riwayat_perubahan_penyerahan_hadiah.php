<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_perubahan_penyerahan_hadiah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penyerahan_hadiah_id')
                ->constrained('penyerahan_hadiah')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Dibuat atau diubah.
            $table->string('aksi', 20);

            // Kondisi sebelum dan sesudah perubahan.
            $table->json('data_sebelum')->nullable();
            $table->json('data_sesudah')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index([
                'penyerahan_hadiah_id',
                'created_at'
            ]);

            $table->index([
                'pengguna_id',
                'created_at'
            ]);

            $table->index('aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_penyerahan_hadiah');
    }
};
