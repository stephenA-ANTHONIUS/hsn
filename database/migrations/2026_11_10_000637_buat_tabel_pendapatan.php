
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('penjualan_id')
                ->nullable()
                ->constrained('penjualan')
                ->restrictOnDelete()
                ->unique();

            $table->foreignId('servis_id')
                ->nullable()
                ->constrained('servis')
                ->restrictOnDelete()
                ->unique();

            $table->date('tanggal_pendapatan');

            $table->enum('kategori_pendapatan', [
                'penjualan',
                'servis',
                'lainnya'
            ]);

            $table->decimal('nominal', 15, 2);

            $table->string('metode_pembayaran', 30)->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan');
    }
};
