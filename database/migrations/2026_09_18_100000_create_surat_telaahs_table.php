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
        Schema::create('surat_telaahs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_telaah')->unique();
            $table->date('tanggal_telaah');
            $table->foreignId('spt_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->foreignId('sppd_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->text('uraian');
            $table->string('tujuan');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_telaahs');
    }
};
