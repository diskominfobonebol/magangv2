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
        Schema::table('dokumen_pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('dokumen_pegawais', 'keterangan_admin')) {
                $table->text('keterangan_admin')->nullable()->after('status_verifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('dokumen_pegawais', 'keterangan_admin')) {
                $table->dropColumn('keterangan_admin');
            }
        });
    }
};
