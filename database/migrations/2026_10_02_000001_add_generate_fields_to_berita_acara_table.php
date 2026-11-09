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
        Schema::table('berita_acara', function (Blueprint $table) {
            $table->string('sumber', 20)->default('upload')->after('jenis'); // 'upload' atau 'generate'
            $table->string('nomor_surat', 150)->nullable()->after('sumber');
            $table->string('file_word_path', 255)->nullable()->after('file_path');
            $table->string('pihak_pertama_nama', 150)->nullable()->after('keterangan');
            $table->string('pihak_pertama_nip', 50)->nullable()->after('pihak_pertama_nama');
            $table->string('pihak_pertama_jabatan', 150)->nullable()->after('pihak_pertama_nip');
            $table->string('pihak_kedua_nama', 150)->nullable()->after('pihak_pertama_jabatan');
            $table->string('pihak_kedua_nip', 50)->nullable()->after('pihak_kedua_nama');
            $table->string('pihak_kedua_jabatan', 150)->nullable()->after('pihak_kedua_nip');
            $table->string('mengetahui_nama', 150)->nullable()->after('pihak_kedua_jabatan');
            $table->string('mengetahui_nip', 50)->nullable()->after('mengetahui_nama');
            $table->string('mengetahui_jabatan', 150)->nullable()->after('mengetahui_nip');
            $table->string('jumlah_barang', 150)->nullable()->after('mengetahui_jabatan');
            $table->string('keperluan', 255)->nullable()->after('jumlah_barang');
            $table->string('tempat_tanda_tangan', 100)->nullable()->after('keperluan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berita_acara', function (Blueprint $table) {
            $table->dropColumn([
                'sumber',
                'nomor_surat',
                'file_word_path',
                'pihak_pertama_nama',
                'pihak_pertama_nip',
                'pihak_pertama_jabatan',
                'pihak_kedua_nama',
                'pihak_kedua_nip',
                'pihak_kedua_jabatan',
                'mengetahui_nama',
                'mengetahui_nip',
                'mengetahui_jabatan',
                'jumlah_barang',
                'keperluan',
                'tempat_tanda_tangan',
            ]);
        });
    }
};
