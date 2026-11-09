<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom nomor_surat_normalized jika belum ada
        Schema::table('berita_acara', function (Blueprint $table) {
            if (!Schema::hasColumn('berita_acara', 'nomor_surat_normalized')) {
                $table->string('nomor_surat_normalized', 150)->nullable()->after('nomor_surat');
            }
        });

        // 2. Isi data nomor_surat_normalized untuk data lama (trim, spasi ganda dirapikan, uppercase)
        $rows = DB::table('berita_acara')
            ->whereNotNull('nomor_surat')
            ->where('nomor_surat', '!=', '')
            ->select('id', 'nomor_surat')
            ->get();

        foreach ($rows as $row) {
            $normalized = strtoupper(preg_replace('/\s+/', ' ', trim($row->nomor_surat)));
            DB::table('berita_acara')
                ->where('id', $row->id)
                ->update(['nomor_surat_normalized' => $normalized]);
        }

        // 3. Cek apakah ada nomor ganda pada data lama
        $duplicates = DB::table('berita_acara')
            ->select('nomor_surat_normalized', DB::raw('GROUP_CONCAT(id) as ids'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('nomor_surat_normalized')
            ->where('nomor_surat_normalized', '!=', '')
            ->groupBy('nomor_surat_normalized')
            ->having('total', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            echo "\n======================================================================\n";
            echo "[PERINGATAN MIGRASI BERITA ACARA: DITEMUKAN NOMOR GANDA PADA DATA LAMA]\n";
            echo "======================================================================\n";
            echo "Data lama TIDAK dihapus otomatis sesuai SOP keamanan data.\n";
            echo "Daftar nomor Berita Acara yang terdeteksi ganda:\n";
            foreach ($duplicates as $dup) {
                echo " - Nomor: '{$dup->nomor_surat_normalized}'\n";
                echo "   Ditemukan pada Berita Acara ID: [{$dup->ids}] (total {$dup->total} berkas)\n";
            }
            echo "\nUnique index database belum dipasang agar migrasi tidak gagal (error 23000).\n";
            echo "Silakan rapikan nomor surat pada ID di atas secara manual, lalu migrasi ulang.\n";
            echo "======================================================================\n\n";
        } else {
            // Pasang Unique Index jika data sudah bersih / tidak ada duplikasi
            Schema::table('berita_acara', function (Blueprint $table) {
                // Pastikan index belum ada sebelum membuat
                $indexExists = collect(DB::select("SHOW INDEXES FROM berita_acara WHERE Key_name = 'berita_acara_nomor_surat_normalized_unique'"))->isNotEmpty();
                if (!$indexExists) {
                    $table->unique('nomor_surat_normalized', 'berita_acara_nomor_surat_normalized_unique');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berita_acara', function (Blueprint $table) {
            if (Schema::hasColumn('berita_acara', 'nomor_surat_normalized')) {
                // Drop unique index jika ada
                $indexExists = collect(DB::select("SHOW INDEXES FROM berita_acara WHERE Key_name = 'berita_acara_nomor_surat_normalized_unique'"))->isNotEmpty();
                if ($indexExists) {
                    $table->dropUnique('berita_acara_nomor_surat_normalized_unique');
                }
                $table->dropColumn('nomor_surat_normalized');
            }
        });
    }
};

