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
        Schema::create('pendaftaran_magang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nim', 50)->nullable();
            $table->string('universitas', 150);
            $table->string('jurusan', 100);
            $table->string('no_hp', 30)->nullable();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->string('surat_pengantar')->nullable();
            $table->string('status', 50)->default('Menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_magang');
    }
};
