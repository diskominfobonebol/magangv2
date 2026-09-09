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
        Schema::create('aset_peralatan_mesin', function (Blueprint $table) {
            $table->string('no_reg_pemda', 100)->primary();
            $table->string('penanggung_jawab', 100)->default('Belum Ditentukan');
            $table->string('jenis_barang', 100);
            $table->string('merek_tipe', 100)->nullable();
            $table->integer('tahun')->nullable();
            $table->unsignedBigInteger('harga_perolehan')->default(0);
            $table->string('no_rangka_seri', 100)->nullable();
            $table->string('no_mesin', 100)->nullable();
            $table->string('no_polisi', 50)->nullable();
            $table->string('no_bpkb', 50)->nullable();
            $table->string('kondisi', 50)->default('Baik');
            $table->string('keterangan_lokasi_unit', 150)->nullable();
            $table->string('no_sk_bast', 150)->nullable();
            $table->date('tgl_mulai_pinjam')->nullable();
            $table->date('tgl_selesai_pinjam')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_peralatan_mesin');
    }
};
