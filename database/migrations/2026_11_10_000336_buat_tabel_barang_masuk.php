
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pemasok_id')
                ->nullable()
                ->constrained('pemasok')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('nomor_nota', 100)->unique();
            $table->date('tanggal_masuk');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};
