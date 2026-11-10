
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyerahan_hadiah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')
                ->nullable()
                ->constrained('pelanggan')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('nomor_nota_pembelian', 100)->nullable();
            $table->date('tanggal_penyerahan');

            $table->string('foto_bukti', 255)->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyerahan_hadiah');
    }
};
