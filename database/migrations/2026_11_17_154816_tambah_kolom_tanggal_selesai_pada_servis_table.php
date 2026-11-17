<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('servis', 'tanggal_selesai')) {
            Schema::table('servis', function (Blueprint $table) {
                $table->date('tanggal_selesai')
                    ->nullable()
                    ->after('tanggal_servis');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('servis', 'tanggal_selesai')) {
            Schema::table('servis', function (Blueprint $table) {
                $table->dropColumn('tanggal_selesai');
            });
        }
    }
};
