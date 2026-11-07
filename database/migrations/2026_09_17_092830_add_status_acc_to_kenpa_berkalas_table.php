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
        Schema::table('kenpa_berkalas', function (Blueprint $table) {
            $table->string('jenis', 50)->change();
            $table->string('status', 50)->default('Aktif')->change();

            if (!Schema::hasColumn('kenpa_berkalas', 'progres_berkas')) {
                $table->integer('progres_berkas')->default(0)->after('status');
            }
            if (!Schema::hasColumn('kenpa_berkalas', 'status_acc')) {
                $table->string('status_acc', 50)->default('Menunggu')->after('status');
            }
            if (!Schema::hasColumn('kenpa_berkalas', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('status_acc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kenpa_berkalas', function (Blueprint $table) {
            if (Schema::hasColumn('kenpa_berkalas', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
            if (Schema::hasColumn('kenpa_berkalas', 'status_acc')) {
                $table->dropColumn('status_acc');
            }
            if (Schema::hasColumn('kenpa_berkalas', 'progres_berkas')) {
                $table->dropColumn('progres_berkas');
            }
        });
    }
};
