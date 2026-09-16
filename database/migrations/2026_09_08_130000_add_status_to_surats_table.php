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
            if (!Schema::hasColumn('surats', 'status')) {
                if (Schema::hasColumn('surats', 'has_sppd')) {
                    $table->string('status', 20)->default('Terbit')->after('has_sppd');
                } else {
                    $table->string('status', 20)->default('Terbit');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (Schema::hasColumn('surats', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
