
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_hadiah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('barang')
                ->restrictOnDelete();

            $table->foreignId('hadiah_id')
                ->constrained('hadiah')
                ->restrictOnDelete();

            // Jumlah hadiah untuk setiap unit barang yang dibeli
            $table->unsignedInteger('jumlah_hadiah')->default(1);

            $table->timestamps();

            // Mencegah pasangan barang dan hadiah tercatat dua kali
            $table->unique(['barang_id', 'hadiah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_hadiah');
    }
};
