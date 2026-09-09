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
            $table->enum('jenis', ['kenpa', 'berkala']);
            $table->date('tgl_terakhir');
            $table->date('tgl_jatuh_tempo');
            $table->enum('status', ['berjalan', 'selesai', 'diperbarui']);
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
