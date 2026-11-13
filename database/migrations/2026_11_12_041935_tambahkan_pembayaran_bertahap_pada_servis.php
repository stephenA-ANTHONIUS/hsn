<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan nominal_dibayar hanya jika belum tersedia.
        if (!Schema::hasColumn('servis', 'nominal_dibayar')) {
            Schema::table('servis', function (Blueprint $table) {
                $table->decimal('nominal_dibayar', 15, 2)
                    ->default(0)
                    ->after('total_biaya');
            });
        }

        // Tambahkan status_pembayaran hanya jika belum tersedia.
        if (!Schema::hasColumn('servis', 'status_pembayaran')) {
            Schema::table('servis', function (Blueprint $table) {
                $table->enum('status_pembayaran', [
                    'belum_dibayar',
                    'cicilan',
                    'lunas',
                ])->default('belum_dibayar')
                    ->after('nominal_dibayar');
            });
        }

        // Hapus unique index servis_id hanya jika masih ada.
        $indexes = DB::select(
            "SHOW INDEX FROM pendapatan WHERE Column_name = 'servis_id'"
        );

        foreach ($indexes as $index) {
            if ((int) $index->Non_unique === 0) {
                Schema::table('pendapatan', function (Blueprint $table) use ($index) {
                    $table->dropUnique($index->Key_name);
                });
            }
        }
    }

    public function down(): void
    {
        // Migration ini hanya melakukan pemeriksaan dan penyesuaian.
        // Tidak menghapus kolom yang mungkin sudah digunakan.
    }
};
