<?php

use App\Models\KategoriDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('kategori_dokumens')) {
            KategoriDokumen::updateOrCreate(
                ['nama_kategori' => 'Dokumen Lainnya'],
                ['urutan' => 11]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kategori_dokumens')) {
            KategoriDokumen::where('nama_kategori', 'Dokumen Lainnya')->delete();
        }
    }
};
