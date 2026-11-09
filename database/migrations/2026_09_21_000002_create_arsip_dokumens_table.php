<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbarui enum role pada tabel users agar role kasubag dan pegawai terdaftar resmi
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'admin_kasubag', 'kasubag', 'bendahara_barang', 'pegawai') NOT NULL DEFAULT 'pegawai'");

        // 2. Buat tabel arsip_dokumens
        if (!Schema::hasTable('arsip_dokumens')) {
            Schema::create('arsip_dokumens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('kategori', 100); // Kenaikan Pangkat, Gaji Berkala, SK Jabatan, dll.
                $table->string('jenis_dokumen', 150); // SK Pangkat Terakhir, Ijazah, dll.
                $table->string('tmt_tahun', 50)->nullable(); // TMT / Tahun berlaku dokumen
                $table->string('nomor_dokumen', 150)->nullable();
                $table->text('keterangan')->nullable();
                $table->string('file_path'); // Lokasi penyimpanan file di storage
                $table->string('file_name'); // Nama asli file saat diupload
                $table->unsignedBigInteger('file_size')->nullable(); // Ukuran file dalam bytes
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_dokumens');
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'admin_kasubag', 'bendahara_barang', 'pegawai') NOT NULL DEFAULT 'pegawai'");
    }
};
