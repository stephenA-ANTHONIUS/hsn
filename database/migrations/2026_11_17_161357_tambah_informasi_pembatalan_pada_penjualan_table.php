<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->timestamp('tanggal_pembatalan')
                ->nullable()
                ->after('keterangan');

            $table->foreignId('dibatalkan_oleh')
                ->nullable()
                ->after('tanggal_pembatalan')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('alasan_pembatalan')
                ->nullable()
                ->after('dibatalkan_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropForeign(['dibatalkan_oleh']);

            $table->dropColumn([
                'tanggal_pembatalan',
                'dibatalkan_oleh',
                'alasan_pembatalan',
            ]);
        });
    }
};