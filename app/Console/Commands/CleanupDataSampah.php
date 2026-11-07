<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Surat;

class CleanupDataSampah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surat:cleanup-data-sampah 
                            {--dry-run : Menampilkan preview data sampah tanpa menghapus (default)}
                            {--delete : Menghapus data sampah secara permanen dari database}
                            {--force : Melewati konfirmasi interaktif saat menggunakan flag --delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit dan pembersihan data surat sampah/tidak valid/testing dari database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDelete = $this->option('delete');
        $isDryRun = $this->option('dry-run') || !$isDelete;

        $modeText = $isDelete ? 'MODE EKSEKUSI PENGHAPUSAN PERMANEN (--delete)' : 'MODE SIMULASI / AUDIT PREVIEW ONLY (--dry-run)';
        $this->info("==========================================================================");
        $this->info("             AUDIT & PEMBERSIHAN DATA SURAT SAMPAH / TIDAK VALID          ");
        $this->info("  Status: {$modeText}");
        $this->info("==========================================================================\n");

        $garbageSurats = collect();

        // 1. Ambil seluruh data surats untuk di-scan
        $allSurats = Surat::with(['pegawais', 'children'])->get();

        foreach ($allSurats as $surat) {
            $reasons = [];

            $isNomorKosong = empty($surat->nomor_surat) || trim($surat->nomor_surat) === '';
            $isTglKosong = empty($surat->tgl_surat);
            $hasNoPegawai = $surat->pegawais->isEmpty();
            $hasNoChildren = $surat->children->isEmpty();
            $isDraft = ($surat->status === 'Draft');
            $isUraianKosong = empty($surat->uraian) || trim($surat->uraian) === '';

            // Kategori 1: Nomor Surat NULL / Kosong
            if ($isNomorKosong) {
                $reasons[] = 'Nomor surat NULL/Kosong';
            }

            // Kategori 2: Tanggal Surat NULL
            if ($isTglKosong) {
                $reasons[] = 'Tanggal surat NULL';
            }

            // Kategori 3: Draft gagal/terbengkalai tanpa uraian & tanpa personel
            if ($isDraft && $isUraianKosong && $hasNoPegawai) {
                $reasons[] = 'Draft kosong tanpa uraian & tanpa pegawai';
            }

            // Kategori 4: SPPD tanpa SPT induk, tanpa manual SPT, dan tanpa pegawai
            $isSppd = ($surat->jenis_surat_id == 1 || str_starts_with((string)$surat->nomor_surat, '090/'));
            if ($isSppd && empty($surat->parent_id) && empty($surat->spt_induk_manual) && $hasNoPegawai) {
                $reasons[] = 'SPPD yatim (tanpa SPT induk & tanpa pegawai)';
            }

            // Kategori 5: Test data dengan format tidak wajar atau placeholder
            if (!$isNomorKosong && (str_contains(strtolower($surat->nomor_surat), 'test') || str_contains(strtolower($surat->nomor_surat), 'dummy'))) {
                $reasons[] = 'Format nomor terindikasi data dummy/testing';
            }

            if (!empty($reasons)) {
                $garbageSurats->push([
                    'id' => $surat->id,
                    'nomor_surat' => $surat->nomor_surat ?: '(NULL)',
                    'tgl_surat' => $surat->tgl_surat ?: '(NULL)',
                    'jenis' => ($surat->jenis_surat_id == 1 ? 'SPPD' : 'SPT'),
                    'status' => $surat->status ?: '-',
                    'pegawai_count' => $surat->pegawais->count(),
                    'anak_count' => $surat->children->count(),
                    'reason' => implode(', ', $reasons),
                ]);
            }
        }

        // 2. Scan Orphan Pivot di surat_pegawai
        $orphanPivots = DB::table('surat_pegawai')
            ->leftJoin('surats', 'surat_pegawai.surat_id', '=', 'surats.id')
            ->leftJoin('pegawais', 'surat_pegawai.pegawai_id', '=', 'pegawais.id')
            ->whereNull('surats.id')
            ->orWhereNull('pegawais.id')
            ->select('surat_pegawai.*')
            ->get();

        // Tampilkan Tabel Hasil Audit Data Sampah di surats
        if ($garbageSurats->isEmpty()) {
            $this->info("✅ [TABEL SURATS] Tidak ditemukan row data surat sampah / tidak valid.");
        } else {
            $this->warn("⚠️  [TABEL SURATS] Ditemukan {$garbageSurats->count()} row data surat yang terindikasi sampah / tidak valid:\n");
            $rows = [];
            foreach ($garbageSurats as $g) {
                $rows[] = [
                    "#{$g['id']}",
                    $g['nomor_surat'],
                    $g['tgl_surat'],
                    $g['jenis'],
                    $g['status'],
                    "{$g['pegawai_count']} orang",
                    $g['reason'],
                ];
            }
            $this->table(
                ['ID', 'Nomor Surat', 'Tgl Surat', 'Jenis', 'Status', 'Personel', 'Alasan Terindikasi Sampah'],
                $rows
            );
        }

        // Tampilkan Tabel Orphan Pivot di surat_pegawai
        $this->newLine();
        if ($orphanPivots->isEmpty()) {
            $this->info("✅ [TABEL SURAT_PEGAWAI] Tidak ditemukan baris relasi orphan (semua surat_id & pegawai_id valid).");
        } else {
            $this->warn("⚠️  [TABEL SURAT_PEGAWAI] Ditemukan {$orphanPivots->count()} baris pivot orphan:");
            $pRows = [];
            foreach ($orphanPivots as $p) {
                $pRows[] = [
                    "#{$p->id}",
                    "Surat ID #{$p->surat_id}",
                    "Pegawai ID #{$p->pegawai_id}",
                    $p->nomor_sppd ?: '-',
                ];
            }
            $this->table(['Pivot ID', 'Surat ID', 'Pegawai ID', 'Nomor SPPD'], $pRows);
        }

        $this->newLine();
        $this->line("--------------------------------------------------------------------------");
        $this->line("Ringkasan Audit:");
        $this->line(" • Total Surat Sampah Teridentifikasi : " . $garbageSurats->count());
        $this->line(" • Total Pivot Orphan Teridentifikasi : " . $orphanPivots->count());
        $this->line("--------------------------------------------------------------------------");

        if ($garbageSurats->isEmpty() && $orphanPivots->isEmpty()) {
            $this->info("\n🎉 Database bersih! Tidak ada tindakan pembersihan yang diperlukan.\n");
            return 0;
        }

        if ($isDryRun) {
            $this->warn("\nℹ️  Ini adalah simulasi audit (--dry-run). Tidak ada data di database yang dihapus.");
            $this->line("Untuk menghapus data sampah di atas secara permanen dari database, jalankan:");
            $this->info("👉 php artisan surat:cleanup-data-sampah --delete\n");
            return 0;
        }

        // Konfirmasi sebelum menghapus
        if (!$this->option('force')) {
            $totalCount = $garbageSurats->count() + $orphanPivots->count();
            if (!$this->confirm("Apakah Anda yakin ingin MENGHAPUS {$totalCount} data sampah di atas secara PERMANEN dari database?")) {
                $this->comment("Operasi pembersihan dibatalkan oleh pengguna.");
                return 0;
            }
        }

        // Eksekusi Penghapusan Permanen dalam Transaction
        $this->newLine();
        $this->info("🗑️  Memulai proses pembersihan data sampah dari database...");

        DB::beginTransaction();
        try {
            $deletedSurats = 0;
            $deletedPivots = 0;

            // Hapus orphan pivot
            if ($orphanPivots->isNotEmpty()) {
                $orphanIds = $orphanPivots->pluck('id')->toArray();
                $deletedPivots = DB::table('surat_pegawai')->whereIn('id', $orphanIds)->delete();
            }

            // Hapus surat sampah beserta relasi pivotnya
            if ($garbageSurats->isNotEmpty()) {
                $suratIds = $garbageSurats->pluck('id')->toArray();
                DB::table('surat_pegawai')->whereIn('surat_id', $suratIds)->delete();
                $deletedSurats = DB::table('surats')->whereIn('id', $suratIds)->delete();
            }

            DB::commit();

            $this->info("✅ PEMBERSIHAN SELESAI DENGAN SUKSES!");
            $this->info(" • Berhasil menghapus {$deletedSurats} row surat sampah.");
            $this->info(" • Berhasil menghapus {$deletedPivots} baris pivot orphan.\n");

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ Terjadi kesalahan saat menghapus data sampah: {$e->getMessage()}. Transaksi di-rollback!\n");
            return 1;
        }

        return 0;
    }
}
