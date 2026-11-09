<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('aset_peralatan_mesin', 'persentase_kondisi')) {
            Schema::table('aset_peralatan_mesin', function (Blueprint $table) {
                $table->unsignedTinyInteger('persentase_kondisi')->default(100)->after('kondisi');
            });
        }

        // Isi default persentase kondisi untuk data lama sesuai standarnya
        DB::table('aset_peralatan_mesin')->where('kondisi', 'Baik')->update(['persentase_kondisi' => 100]);
        DB::table('aset_peralatan_mesin')->where('kondisi', 'Rusak Ringan')->update(['persentase_kondisi' => 65]);
        DB::table('aset_peralatan_mesin')->where('kondisi', 'Rusak Berat')->update(['persentase_kondisi' => 25]);
        DB::table('aset_peralatan_mesin')->where('kondisi', 'Hilang')->update(['persentase_kondisi' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('aset_peralatan_mesin', 'persentase_kondisi')) {
            Schema::table('aset_peralatan_mesin', function (Blueprint $table) {
                $table->dropColumn('persentase_kondisi');
            });
        }
    }
};

