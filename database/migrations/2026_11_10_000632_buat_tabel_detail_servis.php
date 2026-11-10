
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_servis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('servis_id')
                ->constrained('servis')
                ->restrictOnDelete();

            $table->foreignId('barang_id')
                ->constrained('barang')
                ->restrictOnDelete();

            $table->unsignedInteger('jumlah');

            $table->decimal('harga_sparepart', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->enum('status_persetujuan', [
                'menunggu',
                'disetujui',
                'ditolak'
            ])->default('menunggu');

            $table->enum('status_penggunaan', [
                'diminta',
                'digunakan',
                'tidak_digunakan'
            ])->default('diminta');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_servis');
    }
};
