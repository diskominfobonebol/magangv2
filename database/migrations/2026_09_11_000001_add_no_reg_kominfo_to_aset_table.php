<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Jika kolom id belum ada, jadikan id sebagai Primary Key baru dan lepas status primary key dari no_reg_pemda
        if (!Schema::hasColumn('aset_peralatan_mesin', 'id')) {
            DB::statement('ALTER TABLE aset_peralatan_mesin DROP PRIMARY KEY, ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)');
        }

        // 2. Ubah kolom no_reg_pemda menjadi nullable agar bisa disimpan kosong saat pengisian manual
        DB::statement('ALTER TABLE aset_peralatan_mesin MODIFY COLUMN no_reg_pemda VARCHAR(100) NULL');

        // 3. Tambahkan kolom no_reg_kominfo tepat setelah no_reg_pemda
        if (!Schema::hasColumn('aset_peralatan_mesin', 'no_reg_kominfo')) {
            DB::statement('ALTER TABLE aset_peralatan_mesin ADD COLUMN no_reg_kominfo VARCHAR(100) NULL AFTER no_reg_pemda');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('aset_peralatan_mesin', 'no_reg_kominfo')) {
            Schema::table('aset_peralatan_mesin', function (Blueprint $table) {
                $table->dropColumn('no_reg_kominfo');
            });
        }
    }
};
