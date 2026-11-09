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
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aset_id');
            $table->string('jenis', 100); // Berita Acara Serah Terima (BAST) atau Berita Acara Penerimaan Barang
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->string('file_path');
            $table->string('file_nama_asli')->nullable();
            $table->unsignedBigInteger('file_ukuran')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('aset_id')->references('id')->on('aset_peralatan_mesin')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};
