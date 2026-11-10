
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penyerahan_hadiah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penyerahan_hadiah_id')
                ->constrained('penyerahan_hadiah')
                ->restrictOnDelete();

            $table->foreignId('hadiah_id')
                ->constrained('hadiah')
                ->restrictOnDelete();

            $table->unsignedInteger('jumlah');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penyerahan_hadiah');
    }
};
