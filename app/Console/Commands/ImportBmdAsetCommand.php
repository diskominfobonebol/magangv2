<?php

namespace App\Console\Commands;

use App\Models\AsetPeralatanMesin;
use App\Models\BeritaAcara;
use App\Services\AsetImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportBmdAsetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aset:import-bmd
                            {file? : Path ke berkas CSV (default: database/seeders/data/aset_bmd_kominfo.csv)}
                            {--dry-run : Jalankan simulasi validasi CSV tanpa mengubah database}
                            {--replace : Izinkan penghapusan seluruh data aset lama sebelum mengimpor}
                            {--force : Lewati konfirmasi interaktif di terminal saat memakai --replace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengimpor 52 data aset BMD riil Kominfo Bone Bolango dari berkas CSV ke tabel aset_peralatan_mesin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('=== IMPORT DATA ASET BMD KOMINFO BONE BOLANGO ===');

        // 1. Tentukan path berkas CSV
        $filePath = $this->argument('file') ?: database_path('seeders/data/aset_bmd_kominfo.csv');

        if (!file_exists($filePath)) {
            $this->error("Berkas CSV tidak ditemukan pada path: {$filePath}");
            return Command::FAILURE;
        }

        $this->line("Berkas sumber: <comment>{$filePath}</comment>");

        // 2. Baca dan validasi berkas CSV
        $parsed = $this->parseCsv($filePath);
        if ($parsed === null) {
            return Command::FAILURE;
        }

        [$rows, $conditionCounts] = $parsed;
        $totalRows = count($rows);

        $this->line("Jumlah baris valid terbaca: <info>{$totalRows}</info>");
        $this->table(
            ['Kondisi', 'Jumlah Terbaca', 'Target Standar', 'Status'],
            [
                ['Baik', $conditionCounts['Baik'] ?? 0, 26, ($conditionCounts['Baik'] ?? 0) === 26 ? 'SESUAI (OK)' : 'PERHATIAN'],
                ['Rusak Berat', $conditionCounts['Rusak Berat'] ?? 0, 16, ($conditionCounts['Rusak Berat'] ?? 0) === 16 ? 'SESUAI (OK)' : 'PERHATIAN'],
                ['Rusak Ringan', $conditionCounts['Rusak Ringan'] ?? 0, 7, ($conditionCounts['Rusak Ringan'] ?? 0) === 7 ? 'SESUAI (OK)' : 'PERHATIAN'],
                ['Hilang', $conditionCounts['Hilang'] ?? 0, 3, ($conditionCounts['Hilang'] ?? 0) === 3 ? 'SESUAI (OK)' : 'PERHATIAN'],
                ['TOTAL', $totalRows, 52, $totalRows === 52 ? 'SESUAI (OK)' : 'PERHATIAN'],
            ]
        );

        // 3. Cek data lama di database
        $existingAsetCount = AsetPeralatanMesin::count();
        $existingBaCount = BeritaAcara::count();

        // 4. Mode DRY-RUN (Simulasi)
        if ($this->option('dry-run')) {
            $this->warn("\n[MODE DRY-RUN AKTIF]");
            $this->line("- Database tidak diubah sama sekali.");
            $this->line("- Data aset lama saat ini di database: <comment>{$existingAsetCount} unit</comment>");
            $this->line("- Data Berita Acara terkait saat ini: <comment>{$existingBaCount} dokumen</comment>");
            $this->line("- Rencana insert data baru: <info>{$totalRows} unit aset BMD riil</info>");
            $this->info("\n[DRY-RUN BERHASIL] Validasi CSV sempurna. Jalankan tanpa --dry-run dengan --replace untuk eksekusi nyata.");
            return Command::SUCCESS;
        }

        // 5. Cek izin replace jika database sudah berisi data
        if ($existingAsetCount > 0 && !$this->option('replace')) {
            $this->error("\nPERINGATAN: Tabel 'aset_peralatan_mesin' sudah berisi {$existingAsetCount} data!");
            $this->line("Untuk keamanan dari ketidaksengajaan terhapusnya data, jalankan dengan opsi:");
            $this->comment("  php artisan aset:import-bmd --replace");
            $this->line("Atau jalankan simulasi validasi dulu dengan:");
            $this->comment("  php artisan aset:import-bmd --dry-run");
            return Command::FAILURE;
        }

        // 6. Konfirmasi interaktif jika replace dan tidak ada flag --force
        if ($existingAsetCount > 0 && !$this->option('force')) {
            $this->warn("\nPERINGATAN BESAR:");
            $this->line("Tindakan ini akan <fg=red;options=bold>MENGHAPUS SELURUH {$existingAsetCount} DATA ASET LAMA</> dan <fg=red;options=bold>{$existingBaCount} BERITA ACARA TERKAIT</> (beserta file fisiknya di storage).");
            $this->line("Data kemudian akan diganti sepenuhnya dengan {$totalRows} aset BMD baru.");

            $confirm = $this->ask("Ketik 'YA' (huruf besar) untuk mengonfirmasi dan melanjutkan:");
            if (trim($confirm) !== 'YA') {
                $this->warn("Operasi dibatalkan. Tidak ada data yang dihapus.");
                return Command::SUCCESS;
            }
        }

        // 7. Eksekusi database transaction
        $deletedBaFilesCount = 0;
        $deletedAsetPhotosCount = 0;

        try {
            DB::transaction(function () use (
                $rows,
                &$deletedBaFilesCount,
                &$deletedAsetPhotosCount,
                $existingAsetCount,
                $existingBaCount
            ) {
                // A. Hapus file fisik Berita Acara lama jika ada
                if ($existingBaCount > 0) {
                    $oldBas = BeritaAcara::all();
                    foreach ($oldBas as $ba) {
                        if (!empty($ba->file_path) && Storage::disk('public')->exists($ba->file_path)) {
                            Storage::disk('public')->delete($ba->file_path);
                            $deletedBaFilesCount++;
                        }
                    }
                    BeritaAcara::query()->delete();
                }

                // B. Hapus foto fisik aset lama jika ada
                if ($existingAsetCount > 0) {
                    $imageService = app(AsetImageService::class);
                    $oldAsets = AsetPeralatanMesin::all();
                    foreach ($oldAsets as $aset) {
                        if (!empty($aset->foto)) {
                            $imageService->delete($aset->foto);
                            $deletedAsetPhotosCount++;
                        }
                    }
                    AsetPeralatanMesin::query()->delete();
                }

                // C. Insert 52 aset riil
                $now = now();
                foreach ($rows as $item) {
                    AsetPeralatanMesin::create([
                        'no_reg_pemda'           => $item['no_reg_pemda'],
                        'no_reg_kominfo'         => $item['no_reg_kominfo'],
                        'penanggung_jawab'       => $item['penanggung_jawab'],
                        'jenis_barang'           => $item['jenis_barang'],
                        'merek_tipe'             => $item['merek_tipe'],
                        'tahun'                  => $item['tahun'],
                        'harga_perolehan'        => $item['harga_perolehan'],
                        'no_rangka_seri'         => $item['no_rangka_seri'],
                        'no_mesin'               => $item['no_mesin'],
                        'no_polisi'              => $item['no_polisi'],
                        'no_bpkb'                => $item['no_bpkb'],
                        'kondisi'                => $item['kondisi'],
                        'persentase_kondisi'     => $item['persentase_kondisi'],
                        'keterangan_lokasi_unit' => $item['keterangan_lokasi_unit'],
                        'no_sk_bast'             => null,
                        'foto'                   => null,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            $this->error("\nTerjadi kegagalan saat import: " . $e->getMessage());
            $this->warn("Seluruh operasi telah di-rollback. Database tetap dalam kondisi semula.");
            return Command::FAILURE;
        }

        // 8. Output ringkasan akhir
        $newAsetCount = AsetPeralatanMesin::count();
        $this->newLine();
        $this->info("=== IMPORT BERHASIL DILAKSANAKAN ===");
        $this->line("- Jumlah aset lama dihapus: <comment>{$existingAsetCount} unit</comment>");
        $this->line("- Jumlah berita acara lama dihapus: <comment>{$existingBaCount} dokumen</comment> ({$deletedBaFilesCount} berkas fisik dibersihkan)");
        $this->line("- Jumlah foto lama aset dibersihkan: <comment>{$deletedAsetPhotosCount} foto</comment>");
        $this->line("- Jumlah aset baru berhasil diimpor: <info>{$newAsetCount} unit</info>");

        $this->newLine();
        $this->line("Rincian kondisi aset saat ini di database:");
        $breakdown = AsetPeralatanMesin::select('kondisi', DB::raw('count(*) as total'))
            ->groupBy('kondisi')
            ->pluck('total', 'kondisi')
            ->toArray();

        $this->table(
            ['Kondisi', 'Jumlah Aktual di DB', 'Target', 'Status'],
            [
                ['Baik', $breakdown['Baik'] ?? 0, 26, ($breakdown['Baik'] ?? 0) === 26 ? 'VERIFIKASI TEPAT' : 'SELISIH'],
                ['Rusak Berat', $breakdown['Rusak Berat'] ?? 0, 16, ($breakdown['Rusak Berat'] ?? 0) === 16 ? 'VERIFIKASI TEPAT' : 'SELISIH'],
                ['Rusak Ringan', $breakdown['Rusak Ringan'] ?? 0, 7, ($breakdown['Rusak Ringan'] ?? 0) === 7 ? 'VERIFIKASI TEPAT' : 'SELISIH'],
                ['Hilang', $breakdown['Hilang'] ?? 0, 3, ($breakdown['Hilang'] ?? 0) === 3 ? 'VERIFIKASI TEPAT' : 'SELISIH'],
                ['TOTAL', array_sum($breakdown), 52, array_sum($breakdown) === 52 ? 'VERIFIKASI TEPAT' : 'SELISIH'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Membaca dan memetakan CSV ke struktur array yang siap divalidasi dan di-insert
     */
    protected function parseCsv(string $filePath): ?array
    {
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            $this->error("Gagal membuka berkas CSV.");
            return null;
        }

        // Baca baris header pertama
        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error("Berkas CSV kosong atau tidak memiliki header.");
            fclose($handle);
            return null;
        }

        // Hapus UTF-8 BOM jika ada di awal kolom pertama
        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        $header = array_map('trim', $header);

        $kondisiConfig = config('aset.kondisi_standar', [
            'Baik'         => ['default' => 100],
            'Rusak Ringan' => ['default' => 65],
            'Rusak Berat'  => ['default' => 25],
            'Hilang'       => ['default' => 0],
        ]);

        $rows = [];
        $conditionCounts = [
            'Baik'         => 0,
            'Rusak Berat'  => 0,
            'Rusak Ringan' => 0,
            'Hilang'       => 0,
        ];

        $lineNumber = 1;
        $errors = [];

        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;

            // Lewati baris kosong
            if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            // Gabungkan baris data dengan header
            if (count($header) !== count($data)) {
                $errors[] = "Baris {$lineNumber}: Jumlah kolom (" . count($data) . ") tidak cocok dengan header (" . count($header) . ").";
                continue;
            }

            $rowRaw = array_combine($header, array_map('trim', $data));

            // 1. no_reg_pemda (Wajib)
            $noRegPemda = $rowRaw['no_reg_pemda'] ?? '';
            if ($noRegPemda === '' || $noRegPemda === '-') {
                $errors[] = "Baris {$lineNumber}: no_reg_pemda wajib diisi.";
            }

            // 2. no_reg_kominfo (Nullable)
            $noRegKominfo = $this->cleanNullable($rowRaw['no_reg_kominfo'] ?? null);

            // 3. penanggung_jawab (Nullable)
            $penanggungJawab = $this->cleanNullable($rowRaw['penanggung_jawab'] ?? null);

            // 4. jenis_barang (Wajib)
            $jenisBarang = $rowRaw['jenis_barang'] ?? '';
            if ($jenisBarang === '' || $jenisBarang === '-') {
                $errors[] = "Baris {$lineNumber}: jenis_barang wajib diisi.";
            }

            // 5. merek_tipe (Nullable)
            $merekTipe = $this->cleanNullable($rowRaw['merek_tipe'] ?? null);

            // 6. tahun (Nullable integer)
            $tahunRaw = $this->cleanNullable($rowRaw['tahun'] ?? null);
            $tahun = null;
            if ($tahunRaw !== null && is_numeric($tahunRaw)) {
                $tahun = (int)$tahunRaw;
            }

            // 7. harga_perolehan (Numeric default 0)
            $hargaRaw = preg_replace('/[^\d]/', '', $rowRaw['harga_perolehan'] ?? '0');
            $hargaPerolehan = !empty($hargaRaw) ? (float)$hargaRaw : 0;

            // 8. Spesifikasi teknis (Nullable)
            $noRangkaSeri = $this->cleanNullable($rowRaw['nomor_rangka_seri'] ?? null);
            $noMesin = $this->cleanNullable($rowRaw['nomor_mesin'] ?? null);
            $noPolisi = $this->cleanNullable($rowRaw['nomor_polisi'] ?? null);
            $noBpkb = $this->cleanNullable($rowRaw['nomor_bpkb'] ?? null);

            // 9. kondisi & persentase_kondisi
            $kondisi = $rowRaw['kondisi'] ?? '';
            $validKondisi = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'];
            if (!in_array($kondisi, $validKondisi, true)) {
                $errors[] = "Baris {$lineNumber}: Nilai kondisi '{$kondisi}' tidak valid. Harus salah satu dari: " . implode(', ', $validKondisi);
                continue;
            }

            $persentaseDefault = $kondisiConfig[$kondisi]['default'] ?? match ($kondisi) {
                'Baik'         => 100,
                'Rusak Ringan' => 65,
                'Rusak Berat'  => 25,
                'Hilang'       => 0,
                default        => 100,
            };

            // 10. keterangan_lokasi_unit (Nullable)
            $keteranganUnit = $this->cleanNullable($rowRaw['keterangan_unit_lokasi'] ?? null);

            $rows[] = [
                'no_reg_pemda'           => $noRegPemda,
                'no_reg_kominfo'         => $noRegKominfo,
                'penanggung_jawab'       => $penanggungJawab,
                'jenis_barang'           => $jenisBarang,
                'merek_tipe'             => $merekTipe,
                'tahun'                  => $tahun,
                'harga_perolehan'        => $hargaPerolehan,
                'no_rangka_seri'         => $noRangkaSeri,
                'no_mesin'               => $noMesin,
                'no_polisi'              => $noPolisi,
                'no_bpkb'                => $noBpkb,
                'kondisi'                => $kondisi,
                'persentase_kondisi'     => $persentaseDefault,
                'keterangan_lokasi_unit' => $keteranganUnit,
            ];

            if (isset($conditionCounts[$kondisi])) {
                $conditionCounts[$kondisi]++;
            }
        }

        fclose($handle);

        if (!empty($errors)) {
            $this->error("Ditemukan " . count($errors) . " kesalahan pada berkas CSV:");
            foreach (array_slice($errors, 0, 10) as $err) {
                $this->line(" - <fg=red>{$err}</>");
            }
            if (count($errors) > 10) {
                $this->line(" ... dan " . (count($errors) - 10) . " error lainnya.");
            }
            return null;
        }

        return [$rows, $conditionCounts];
    }

    /**
     * Membersihkan nilai nullable dari string kosong, tanda minus strip, atau whitespace
     */
    protected function cleanNullable(?string $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $val = trim($val);
        if ($val === '' || $val === '-' || strtolower($val) === 'null') {
            return null;
        }
        return $val;
    }
}
