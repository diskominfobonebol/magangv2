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
        Schema::create('dokumen_pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kenpa_berkala_id')->constrained('kenpa_berkalas');
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumens');
            $table->string('file_path');
            $table->enum('status_verifikasi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pegawais');
    }
};
