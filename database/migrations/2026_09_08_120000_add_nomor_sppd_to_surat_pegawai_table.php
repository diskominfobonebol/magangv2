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
        if (!Schema::hasColumn('surat_pegawai', 'nomor_sppd')) {
            Schema::table('surat_pegawai', function (Blueprint $table) {
                $table->string('nomor_sppd')->nullable()->after('keterangan_tugas');
            });
        }

        // Backfill nomor SPPD untuk data lama yang memiliki has_sppd = 1
        if (Schema::hasTable('surats') && Schema::hasColumn('surats', 'has_sppd')) {
            $surats = DB::table('surats')->where('has_sppd', 1)->orderBy('id')->get();
            $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            
            $sppdCounter = 1;
            // Cari counter SPPD tertinggi yang pernah ada
            $existingStandaloneSppd = DB::table('surats')->where('nomor_surat', 'like', '090/%')->get();
            foreach ($existingStandaloneSppd as $s) {
                $parts = explode('/', $s->nomor_surat);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $num = (int)$parts[1];
                    if ($num >= $sppdCounter) {
                        $sppdCounter = $num + 1;
                    }
                }
            }

            foreach ($surats as $surat) {
                $d = new \DateTime($surat->tgl_surat ?? now());
                $bln = $romawiBulan[$d->format('n') - 1];
                $thn = $d->format('Y');

                $pivotRows = DB::table('surat_pegawai')->where('surat_id', $surat->id)->orderBy('id')->get();
                foreach ($pivotRows as $pivot) {
                    $noUrut = str_pad($sppdCounter, 3, '0', STR_PAD_LEFT);
                    $nomorSppd = "090/{$noUrut}/{$bln}/{$thn}";
                    
                    DB::table('surat_pegawai')
                        ->where('id', $pivot->id)
                        ->update(['nomor_sppd' => $nomorSppd]);

                    $sppdCounter++;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('surat_pegawai', 'nomor_sppd')) {
            Schema::table('surat_pegawai', function (Blueprint $table) {
                $table->dropColumn('nomor_sppd');
            });
        }
    }
};
