
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')
                ->nullable()
                ->constrained('pelanggan')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('nomor_nota', 100)->unique();
            $table->date('tanggal_penjualan');
            $table->decimal('total_penjualan', 15, 2)->default(0);
            $table->string('metode_pembayaran', 30)->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
