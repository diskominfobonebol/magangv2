<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Surat;

class MigrateNomorSppd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surat:migrate-nomor-sppd 
                            {--dry-run : Menjalankan simulasi migrasi tanpa menyimpan ke database (default)}
                            {--apply : Menerapkan perubahan nomor SPPD secara permanen ke database}
                            {--force : Melewati konfirmasi interaktif saat menggunakan flag --apply}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi format penomoran SPPD legacy ke skema resmi berbasis suffix huruf berurutan dari SPT induk';

    /**
     * Convert integer index to letter suffix (0->a, 25->z, 26->aa, etc.)
     */
    protected function getLetterSuffix(int $index): string
    {
        return Surat::getLetterSuffix($index);
    }

    /**
     * Extract sequence number segment from parent SPT number (e.g. 555/KOMINFO-BB/SPT-DD/003/IX/2026 -> 003)
     */
    protected function extractSptSequence(?string $sptNomor): string
    {
        return Surat::extractSptSequence($sptNomor);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isApply = $this->option('apply');
        $isDryRun = $this->option('dry-run') || !$isApply;

        $modeText = $isApply ? 'MODE EKSEKUSI (PERMANEN / --apply)' : 'MODE SIMULASI (DRY RUN / PREVIEW ONLY)';
        $this->info("==========================================================================");
        $this->info("       MIGRASI FORMAT PENOMORAN SPPD KE SKEMA RESMI KOMINFO BONE BOLANGO   ");
        $this->info("  Format Baru: 090/KOMINFO-BB/SPPD/DD/{no_urut_SPT_induk}{huruf}/{bln}/{thn}");
        $this->info("  Status: {$modeText}");
        $this->info("==========================================================================\n");

        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        // 1. Ambil seluruh SPT induk (surat dengan parent_id null yang berjenis SPT atau kode 555)
        $spts = Surat::with(['children' => function($q) {
                $q->with('pegawais')->orderBy('created_at')->orderBy('id');
            }, 'pegawais'])
            ->whereNull('parent_id')
            ->where(function($q) {
                $q->where('jenis_surat_id', 2)
                  ->orWhereNull('jenis_surat_id')
                  ->orWhere('nomor_surat', 'like', '555/%');
            })
            ->orderBy('id')
            ->get();

        $migrationPlans = [];
        $incompleteData = [];
        $totalChangesCount = 0;

        foreach ($spts as $spt) {
            $sptUrut = $this->extractSptSequence($spt->nomor_surat);
            $planForSpt = [
                'spt_id' => $spt->id,
                'spt_nomor' => $spt->nomor_surat,
                'spt_urut' => $sptUrut,
                'spt_tgl' => $spt->tgl_surat,
                'changes' => []
            ];

            $letterIndex = 0;

            // Proses dokumen SPPD anak (parent_id = $spt->id)
            foreach ($spt->children as $child) {
                // Evaluasi kelengkapan data
                $isNomorKosong = empty($child->nomor_surat) || trim($child->nomor_surat) === '';
                $isPegawaiKosong = $child->pegawais->isEmpty();

                if ($isNomorKosong || $isPegawaiKosong) {
                    $reason = [];
                    if ($isNomorKosong) $reason[] = 'Nomor surat SPPD kosong/null';
                    if ($isPegawaiKosong) $reason[] = 'Tidak ada pegawai yang ditugaskan';

                    $incompleteData[] = [
                        'id' => $child->id,
                        'parent_id' => $spt->id,
                        'parent_nomor' => $spt->nomor_surat,
                        'nomor_surat' => $child->nomor_surat ?: '(KOSONG)',
                        'tgl_surat' => $child->tgl_surat,
                        'uraian' => $child->uraian ?: '-',
                        'pegawai_count' => $child->pegawais->count(),
                        'reason' => implode(' & ', $reason)
                    ];
                    continue; // Skip data tidak lengkap
                }

                $d = new \DateTime($child->tgl_surat ?: date('Y-m-d'));
                $bln = $romawiBulan[$d->format('n') - 1];
                $thn = $d->format('Y');
                $jp = $child->jenis_penugasan ?: ($spt->jenis_penugasan ?: 'DD');

                $docLetter = $this->getLetterSuffix($letterIndex);
                $newDocNomor = Surat::formatNomorSppd($sptUrut, $docLetter, $child->tgl_surat, $jp);

                $planForSpt['changes'][] = [
                    'entity' => 'surats',
                    'id' => $child->id,
                    'type' => 'Dokumen SPPD Anak',
                    'desc' => "SPPD #{$child->id} (Tgl: {$child->tgl_surat})",
                    'old_nomor' => $child->nomor_surat,
                    'new_nomor' => $newDocNomor,
                    'action' => 'update_surat',
                ];
                $totalChangesCount++;

                // Update nomor SPPD untuk setiap pegawai pada child ini
                foreach ($child->pegawais as $pIdx => $p) {
                    $pLetter = $this->getLetterSuffix($letterIndex);
                    $newPNomor = Surat::formatNomorSppd($sptUrut, $pLetter, $child->tgl_surat, $jp);

                    $planForSpt['changes'][] = [
                        'entity' => 'surat_pegawai',
                        'id' => $p->pivot->id,
                        'surat_id' => $child->id,
                        'pegawai_id' => $p->id,
                        'type' => 'Pivot Personel',
                        'desc' => "Pegawai: {$p->nama} (SPPD #{$child->id})",
                        'old_nomor' => $p->pivot->nomor_sppd ?: '-',
                        'new_nomor' => $newPNomor,
                        'action' => 'update_pivot',
                    ];
                    $totalChangesCount++;

                    $letterIndex++;
                }
            }

            // Proses SPPD yang melekat langsung di SPT induk (has_sppd = 1)
            if ($spt->has_sppd && $spt->pegawais && $spt->pegawais->isNotEmpty()) {
                $d = new \DateTime($spt->tgl_surat ?: date('Y-m-d'));
                $bln = $romawiBulan[$d->format('n') - 1];
                $thn = $d->format('Y');
                $jp = $spt->jenis_penugasan ?: 'DD';

                foreach ($spt->pegawais as $p) {
                    if (!empty($p->pivot->nomor_sppd) && $p->pivot->nomor_sppd !== '-') {
                        $pLetter = $this->getLetterSuffix($letterIndex);
                        $newPNomor = Surat::formatNomorSppd($sptUrut, $pLetter, $spt->tgl_surat, $jp);

                        $planForSpt['changes'][] = [
                            'entity' => 'surat_pegawai',
                            'id' => $p->pivot->id,
                            'surat_id' => $spt->id,
                            'pegawai_id' => $p->id,
                            'type' => 'Pivot Personel (Direct SPT)',
                            'desc' => "Pegawai: {$p->nama} (SPT #{$spt->id})",
                            'old_nomor' => $p->pivot->nomor_sppd,
                            'new_nomor' => $newPNomor,
                            'action' => 'update_pivot',
                        ];
                        $totalChangesCount++;

                        $letterIndex++;
                    }
                }
            }

            if (!empty($planForSpt['changes'])) {
                $migrationPlans[] = $planForSpt;
            }
        }

        // Tampilkan Tabel Preview Rencana Perubahan
        $this->info("📋 RENCANA MIGRASI NOMOR SPPD:\n");
        $tableRows = [];
        foreach ($migrationPlans as $plan) {
            foreach ($plan['changes'] as $c) {
                $tableRows[] = [
                    $plan['spt_nomor'],
                    $c['type'],
                    $c['desc'],
                    $c['old_nomor'],
                    '➔ ' . $c['new_nomor'],
                ];
            }
        }

        if (empty($tableRows)) {
            $this->comment("Tidak ada data SPPD legacy yang memenuhi kriteria untuk dimigrasi.");
        } else {
            $this->table(
                ['SPT Induk', 'Tipe Entitas', 'Keterangan', 'Nomor Lama', 'Nomor Baru'],
                $tableRows
            );
        }

        // Tampilkan Laporan Data Tidak Lengkap (Di-skip)
        $this->newLine();
        if (!empty($incompleteData)) {
            $this->warn("⚠️  LAPORAN DATA TIDAK LENGKAP (DILEWATI DARI MIGRASI):");
            $this->line("   Data berikut di-skip dari proses migrasi dan perlu dicek manual oleh admin:\n");
            
            $incRows = [];
            foreach ($incompleteData as $inc) {
                $incRows[] = [
                    "#{$inc['id']}",
                    $inc['parent_nomor'],
                    $inc['nomor_surat'],
                    $inc['tgl_surat'],
                    $inc['uraian'],
                    "{$inc['pegawai_count']} orang",
                    $inc['reason'],
                ];
            }
            $this->table(
                ['ID', 'SPT Induk', 'Nomor SPPD', 'Tgl Surat', 'Uraian', 'Personel', 'Alasan Di-skip'],
                $incRows
            );
        } else {
            $this->info("✅ Tidak ditemukan data SPPD anak yang tidak lengkap.");
        }

        $this->newLine();
        $this->line("--------------------------------------------------------------------------");
        $this->line("Ringkasan:");
        $this->line(" • Total SPT Induk diproses   : " . count($migrationPlans));
        $this->line(" • Total Nomor SPPD berubah   : {$totalChangesCount}");
        $this->line(" • Total Data Incomplete Skip : " . count($incompleteData));
        $this->line("--------------------------------------------------------------------------");

        // Jika Dry-run, berhenti di sini
        if ($isDryRun) {
            $this->warn("\nℹ️  Ini adalah simulasi (--dry-run). Tidak ada data di database yang diubah.");
            $this->line("Untuk mengeksekusi dan menyimpan perubahan permanen ke database, jalankan:");
            $this->info("👉 php artisan surat:migrate-nomor-sppd --apply\n");
            return 0;
        }

        // Konfirmasi sebelum Apply
        if (!$this->option('force')) {
            if (!$this->confirm("Apakah Anda yakin ingin menerapkan {$totalChangesCount} perubahan nomor SPPD di atas ke database secara permanen?")) {
                $this->comment("Operasi migrasi dibatalkan oleh pengguna.");
                return 0;
            }
        }

        // Eksekusi Migrasi dengan DB Transaction per SPT
        $this->newLine();
        $this->info("🚀 Memulai proses migrasi ke database...");

        $logEntries = [];
        $logTimestamp = now()->format('Y-m-d H:i:s');
        $logEntries[] = "=== SPPD NUMBERING MIGRATION LOG ===";
        $logEntries[] = "Executed at: {$logTimestamp}";
        $logEntries[] = "Total changes: {$totalChangesCount}";
        $logEntries[] = "------------------------------------\n";

        $successSptCount = 0;

        foreach ($migrationPlans as $plan) {
            DB::beginTransaction();
            try {
                foreach ($plan['changes'] as $c) {
                    if ($c['action'] === 'update_surat') {
                        DB::table('surats')
                            ->where('id', $c['id'])
                            ->update([
                                'nomor_surat' => $c['new_nomor'],
                                'updated_at' => now(),
                            ]);
                    } elseif ($c['action'] === 'update_pivot') {
                        DB::table('surat_pegawai')
                            ->where('id', $c['id'])
                            ->update([
                                'nomor_sppd' => $c['new_nomor'],
                                'updated_at' => now(),
                            ]);
                    }

                    $logEntries[] = sprintf(
                        "[%s] SPT: %s | Entity: %s #%d | %s | Old: %s -> New: %s",
                        $logTimestamp,
                        $plan['spt_nomor'],
                        $c['entity'],
                        $c['id'],
                        $c['desc'],
                        $c['old_nomor'],
                        $c['new_nomor']
                    );
                }

                DB::commit();
                $successSptCount++;
                $this->line(" ✅ SPT {$plan['spt_nomor']}: berhasil dimigrasi (" . count($plan['changes']) . " nomor diperbarui).");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error(" ❌ SPT {$plan['spt_nomor']}: GAGAL dimigrasi ({$e->getMessage()}). Transaksi di-rollback!");
                $logEntries[] = sprintf(
                    "[%s] ERROR migrating SPT %s: %s",
                    $logTimestamp,
                    $plan['spt_nomor'],
                    $e->getMessage()
                );
            }
        }

        // Simpan File Log Terpisah
        $logFilename = 'sppd_migration_' . date('Ymd_His') . '.log';
        $logPath = storage_path('logs/' . $logFilename);
        File::ensureDirectoryExists(storage_path('logs'));
        File::put($logPath, implode("\n", $logEntries));

        $this->newLine();
        $this->info("🎉 MIGRASI SELESAI!");
        $this->info(" • Berhasil menerapkan migrasi pada {$successSptCount} SPT Induk.");
        $this->info(" • File audit log tersimpan di: {$logPath}\n");

        return 0;
    }
}
