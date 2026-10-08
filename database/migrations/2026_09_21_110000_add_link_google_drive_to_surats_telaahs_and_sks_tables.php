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
        Schema::table('surats', function (Blueprint $table) {
            if (!Schema::hasColumn('surats', 'link_google_drive')) {
                $table->text('link_google_drive')->nullable()->after('keterangan');
            }
        });

        Schema::table('surat_telaahs', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_telaahs', 'link_google_drive')) {
                $table->text('link_google_drive')->nullable()->after('keterangan');
            }
        });

        Schema::table('surat_sks', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_sks', 'link_google_drive')) {
                $table->text('link_google_drive')->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (Schema::hasColumn('surats', 'link_google_drive')) {
                $table->dropColumn('link_google_drive');
            }
        });

        Schema::table('surat_telaahs', function (Blueprint $table) {
            if (Schema::hasColumn('surat_telaahs', 'link_google_drive')) {
                $table->dropColumn('link_google_drive');
            }
        });

        Schema::table('surat_sks', function (Blueprint $table) {
            if (Schema::hasColumn('surat_sks', 'link_google_drive')) {
                $table->dropColumn('link_google_drive');
            }
        });
    }
};
