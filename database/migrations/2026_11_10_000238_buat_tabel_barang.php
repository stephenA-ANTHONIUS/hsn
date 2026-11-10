
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kategori_barang_id')
                ->constrained('kategori_barang')
                ->restrictOnDelete();

            $table->foreignId('satuan_barang_id')
                ->constrained('satuan_barang')
                ->restrictOnDelete();

            $table->string('kode_barang', 50)->unique();
            $table->string('nama_barang', 150);

            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);

            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);

            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])
                ->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
