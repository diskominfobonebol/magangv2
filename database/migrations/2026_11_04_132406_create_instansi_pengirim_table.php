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
        if (!Schema::hasTable('instansi_pengirim')) {
            Schema::create('instansi_pengirim', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('nama_normal')->unique();
                $table->timestamps();
            });
        }

        // Backfill idempotent dari data surat_masuk (jika ada data lama)
        if (Schema::hasTable('surat_masuk')) {
            $distinctAsal = DB::table('surat_masuk')
                ->select('asal_surat')
                ->whereNotNull('asal_surat')
                ->where('asal_surat', '!=', '')
                ->distinct()
                ->pluck('asal_surat');

            foreach ($distinctAsal as $asal) {
                $trimmed = trim(preg_replace('/\s+/', ' ', $asal));
                $normal = mb_strtolower($trimmed, 'UTF-8');
                if (!empty($normal)) {
                    DB::table('instansi_pengirim')->insertOrIgnore([
                        'nama' => $trimmed,
                        'nama_normal' => $normal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instansi_pengirim');
    }
};
