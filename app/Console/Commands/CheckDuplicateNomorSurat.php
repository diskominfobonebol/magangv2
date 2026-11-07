<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Surat;

class CheckDuplicateNomorSurat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surat:check-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mendeteksi data nomor surat & SPPD duplikat serta validasi kesesuaian bulan romawi di database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== MEMULAI PENGECEKAN DATA DUPLIKAT NOMOR SURAT & SPPD ===');

        // 1. Pengecekan Duplikat Nomor Surat pada Tabel surats
        $duplicatesSurat = DB::table('surats')
            ->select('nomor_surat', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nomor_surat')
            ->where('nomor_surat', '!=', '')
            ->groupBy('nomor_surat')
            ->having('total', '>', 1)
            ->get();

        if ($duplicatesSurat->isEmpty()) {
            $this->info('✅ [TABEL SURATS] Tidak ditemukan nomor surat yang duplikat.');
        } else {
            $this->error("⚠️ [TABEL SURATS] Ditemukan {$duplicatesSurat->count()} nomor surat duplikat:");
            foreach ($duplicatesSurat as $dup) {
                $this->line("   - Nomor: '{$dup->nomor_surat}' muncul {$dup->total} kali.");
                $rows = DB::table('surats')->where('nomor_surat', $dup->nomor_surat)->get(['id', 'tgl_surat', 'tujuan', 'status']);
                foreach ($rows as $row) {
                    $this->line("     * ID: {$row->id} | Tgl: {$row->tgl_surat} | Status: {$row->status} | Tujuan: {$row->tujuan}");
                }
            }
        }

        // 2. Pengecekan Duplikat Nomor SPPD pada Tabel surat_pegawai
        $duplicatesSppd = DB::table('surat_pegawai')
            ->select('nomor_sppd', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nomor_sppd')
            ->where('nomor_sppd', '!=', '')
            ->where('nomor_sppd', '!=', '-')
            ->groupBy('nomor_sppd')
            ->having('total', '>', 1)
            ->get();

        if ($duplicatesSppd->isEmpty()) {
            $this->info('✅ [TABEL SURAT_PEGAWAI] Tidak ditemukan nomor SPPD yang duplikat.');
        } else {
            $this->error("⚠️ [TABEL SURAT_PEGAWAI] Ditemukan {$duplicatesSppd->count()} nomor SPPD duplikat:");
            foreach ($duplicatesSppd as $dup) {
                $this->line("   - SPPD: '{$dup->nomor_sppd}' muncul {$dup->total} kali.");
            }
        }

        // 3. Pengecekan Ketidaksesuaian Bulan Romawi dengan Tanggal Surat
        $romawiMap = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        $allSurats = Surat::whereNotNull('tgl_surat')->whereNotNull('nomor_surat')->get();
        $mismatches = [];

        foreach ($allSurats as $surat) {
            $parts = explode('/', $surat->nomor_surat);
            if (count($parts) >= 4) {
                $recordedBulan = strtoupper(trim($parts[count($parts) - 2]));
                $actualMonthNum = (int) date('n', strtotime($surat->tgl_surat));
                $expectedBulan = $romawiMap[$actualMonthNum] ?? '';

                if ($recordedBulan !== $expectedBulan && in_array($recordedBulan, array_values($romawiMap))) {
                    $mismatches[] = [
                        'id' => $surat->id,
                        'nomor_surat' => $surat->nomor_surat,
                        'tgl_surat' => $surat->tgl_surat,
                        'recorded_bulan' => $recordedBulan,
                        'expected_bulan' => $expectedBulan,
                    ];
                }
            }
        }

        if (empty($mismatches)) {
            $this->info('✅ [VALIDASI BULAN ROMAWI] Semua format bulan romawi pada nomor surat sesuai dengan tanggal terbit.');
        } else {
            $this->warn("⚠️ [VALIDASI BULAN ROMAWI] Ditemukan " . count($mismatches) . " nomor surat dengan bulan romawi yang tidak sesuai dengan tanggal surat:");
            foreach ($mismatches as $m) {
                $this->line("   - ID #{$m['id']}: '{$m['nomor_surat']}' (Tgl: {$m['tgl_surat']}) -> Tercatat {$m['recorded_bulan']}, seharusnya {$m['expected_bulan']}");
            }
        }

        $this->info('=== PENGECEKAN SELESAI ===');
        return 0;
    }
}
