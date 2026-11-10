
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_barang_masuk', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_masuk_id')
                ->constrained('barang_masuk')
                ->restrictOnDelete();

            $table->foreignId('barang_id')
                ->constrained('barang')
                ->restrictOnDelete();

            $table->unsignedInteger('jumlah');
            $table->decimal('harga_modal', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_barang_masuk');
    }
};
