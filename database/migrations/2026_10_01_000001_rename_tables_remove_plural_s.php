<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('kategori_dokumens')) {
            Schema::rename('kategori_dokumens', 'kategori_dokumen');
        }

        if (Schema::hasTable('jenis_dokumens')) {
            Schema::rename('jenis_dokumens', 'jenis_dokumen');
        }

        if (Schema::hasTable('arsip_dokumens')) {
            Schema::rename('arsip_dokumens', 'arsip_dokumen');
        }

        if (Schema::hasTable('berita_acaras')) {
            Schema::rename('berita_acaras', 'berita_acara');
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('berita_acara')) {
            Schema::rename('berita_acara', 'berita_acaras');
        }

        if (Schema::hasTable('arsip_dokumen')) {
            Schema::rename('arsip_dokumen', 'arsip_dokumens');
        }

        if (Schema::hasTable('jenis_dokumen')) {
            Schema::rename('jenis_dokumen', 'jenis_dokumens');
        }

        if (Schema::hasTable('kategori_dokumen')) {
            Schema::rename('kategori_dokumen', 'kategori_dokumens');
        }

        Schema::enableForeignKeyConstraints();
    }
};
