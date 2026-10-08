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
        Schema::create('kenpa_berkalas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais');
            $table->string('jenis', 50)->default('Berkala');
            $table->date('tgl_terakhir');
            $table->date('tgl_jatuh_tempo');
            $table->string('status', 50)->default('Aktif');
            $table->integer('progres_berkas')->default(0);
            $table->string('status_acc', 50)->default('Menunggu');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kenpa_berkalas');
    }
};
