
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menghubungkan penyerahan hadiah dengan penjualan
        // Kolom hanya dibuat jika belum tersedia
        if (!Schema::hasColumn('penyerahan_hadiah', 'penjualan_id')) {
            Schema::table('penyerahan_hadiah', function (Blueprint $table) {
                $table->foreignId('penjualan_id')
                    ->nullable()
                    ->after('pelanggan_id')
                    ->constrained('penjualan')
                    ->restrictOnDelete();
            });
        }

        // Menghapus kolom nota pembelian lama jika masih tersedia
        if (Schema::hasColumn('penyerahan_hadiah', 'nomor_nota_pembelian')) {
            Schema::table('penyerahan_hadiah', function (Blueprint $table) {
                $table->dropColumn('nomor_nota_pembelian');
            });
        }

        // Menambahkan status pembayaran pada penjualan
        if (!Schema::hasColumn('penjualan', 'status_pembayaran')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->enum('status_pembayaran', [
                    'belum_dibayar',
                    'sebagian',
                    'lunas'
                ])->default('belum_dibayar')
                  ->after('metode_pembayaran');
            });
        }

        // Menambahkan status pembayaran pada servis
        if (!Schema::hasColumn('servis', 'status_pembayaran')) {
            Schema::table('servis', function (Blueprint $table) {
                $table->enum('status_pembayaran', [
                    'belum_dibayar',
                    'sebagian',
                    'lunas'
                ])->default('belum_dibayar')
                  ->after('total_biaya');
            });
        }

        // Tidak perlu menghapus UNIQUE pada pendapatan.
        // Hasil pemeriksaan database menunjukkan index tersebut
        // memang tidak tersedia.
    }

    public function down(): void
    {
        // Menghapus status pembayaran servis
        if (Schema::hasColumn('servis', 'status_pembayaran')) {
            Schema::table('servis', function (Blueprint $table) {
                $table->dropColumn('status_pembayaran');
            });
        }

        // Menghapus status pembayaran penjualan
        if (Schema::hasColumn('penjualan', 'status_pembayaran')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->dropColumn('status_pembayaran');
            });
        }

        // Mengembalikan struktur penyerahan hadiah sebelumnya
        if (Schema::hasColumn('penyerahan_hadiah', 'penjualan_id')) {
            Schema::table('penyerahan_hadiah', function (Blueprint $table) {
                $table->dropConstrainedForeignId('penjualan_id');
            });
        }

        if (!Schema::hasColumn('penyerahan_hadiah', 'nomor_nota_pembelian')) {
            Schema::table('penyerahan_hadiah', function (Blueprint $table) {
                $table->string('nomor_nota_pembelian', 100)->nullable();
            });
        }
    }
};
