
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_stok_hadiah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hadiah_id')
                ->constrained('hadiah')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('jenis_transaksi', [
                'masuk',
                'keluar',
                'penyesuaian'
            ]);

            $table->unsignedInteger('jumlah');
            $table->date('tanggal_transaksi');

            $table->string('referensi_tabel', 100)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_stok_hadiah');
    }
};
