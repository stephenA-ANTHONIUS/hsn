<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $namaTabel = 'riwayat_perubahan_penyerahan_hadiah';

        $indexes = [
            'idx_rph_penyerahan_created' => [
                'penyerahan_hadiah_id',
                'created_at',
            ],
            'idx_rph_pengguna_created' => [
                'pengguna_id',
                'created_at',
            ],
            'idx_rph_aksi' => [
                'aksi',
            ],
        ];

        // Jika tabel sudah terbentuk sebelumnya,
        // cukup tambahkan indeks yang belum tersedia.
        if (Schema::hasTable($namaTabel)) {
            foreach ($indexes as $namaIndex => $kolom) {
                $indexAda = DB::selectOne(
                    'SELECT COUNT(*) AS total
                     FROM information_schema.statistics
                     WHERE table_schema = DATABASE()
                     AND table_name = ?
                     AND index_name = ?',
                    [$namaTabel, $namaIndex]
                );

                if ((int) $indexAda->total === 0) {
                    Schema::table($namaTabel, function (Blueprint $table) use ($kolom, $namaIndex) {
                        $table->index($kolom, $namaIndex);
                    });
                }
            }

            return;
        }

        // Jika tabel belum ada, buat tabel seperti biasa.
        Schema::create($namaTabel, function (Blueprint $table) {
            $table->id();

            $table->foreignId('penyerahan_hadiah_id')
                ->constrained('penyerahan_hadiah')
                ->restrictOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('aksi', 20);

            $table->json('data_sebelum')->nullable();
            $table->json('data_sesudah')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(
                ['penyerahan_hadiah_id', 'created_at'],
                'idx_rph_penyerahan_created'
            );

            $table->index(
                ['pengguna_id', 'created_at'],
                'idx_rph_pengguna_created'
            );

            $table->index('aksi', 'idx_rph_aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_penyerahan_hadiah');
    }
};
