<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jika tabel sudah terbentuk sebelumnya,
        // cukup periksa dan tambahkan relasi barang_id.
        if (Schema::hasTable('detail_servis')) {
            $foreignKey = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'detail_servis'
                AND COLUMN_NAME = 'barang_id'
                AND REFERENCED_TABLE_NAME = 'barang'
            ");

            if ((int) $foreignKey->total === 0) {
                Schema::table('detail_servis', function (Blueprint $table) {
                    $table->foreign('barang_id')
                        ->references('id')
                        ->on('barang')
                        ->restrictOnDelete();
                });
            }

            return;
        }

        // Jika tabel belum ada, buat tabel seperti biasa.
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