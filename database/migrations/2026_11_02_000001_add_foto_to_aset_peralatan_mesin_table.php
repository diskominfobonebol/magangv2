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
        Schema::table('aset_peralatan_mesin', function (Blueprint $table) {
            if (!Schema::hasColumn('aset_peralatan_mesin', 'foto')) {
                $table->string('foto', 255)->nullable()->after('keterangan_lokasi_unit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aset_peralatan_mesin', function (Blueprint $table) {
            if (Schema::hasColumn('aset_peralatan_mesin', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }
};

