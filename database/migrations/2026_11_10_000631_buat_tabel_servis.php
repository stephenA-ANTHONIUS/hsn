
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')
                ->nullable()
                ->constrained('pelanggan')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('nomor_nota', 100)->unique();
            $table->date('tanggal_servis');
            $table->string('nama_teknisi', 150)->nullable();
            $table->text('keluhan');
            $table->text('hasil_pemeriksaan')->nullable();

            $table->enum('status_perbaikan', [
                'diterima',
                'pemeriksaan',
                'menunggu_persetujuan',
                'proses',
                'selesai',
                'diambil',
                'dibatalkan'
            ])->default('diterima');

            $table->decimal('biaya_jasa', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servis');
    }
};
