
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('barang')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('jenis_transaksi', [
                'barang_masuk',
                'penjualan',
                'servis',
                'penyesuaian'
            ]);

            $table->enum('jenis_perubahan', [
                'masuk',
                'keluar'
            ]);

            $table->unsignedInteger('jumlah');

            $table->string('referensi_tabel', 100)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_stok');
    }
};
