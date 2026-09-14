<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_pegawai', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_pegawai', 'nomor_sppd')) {
                $table->string('nomor_sppd')->nullable()->after('keterangan_tugas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pegawai', function (Blueprint $table) {
            if (Schema::hasColumn('surat_pegawai', 'nomor_sppd')) {
                $table->dropColumn('nomor_sppd');
            }
        });
    }
};
