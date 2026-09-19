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
            if (!Schema::hasColumn('surats', 'jenis_penugasan')) {
                $table->string('jenis_penugasan', 20)->default('DD')->after('tujuan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (Schema::hasColumn('surats', 'jenis_penugasan')) {
                $table->dropColumn('jenis_penugasan');
            }
        });
    }
};
