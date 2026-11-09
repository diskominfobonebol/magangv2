<?php

namespace App\Console\Commands;

use App\Models\ArsipDokumen;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PurgePegawai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pegawai:purge 
                            {--keep=pegawai@kominfo.go.id : Email atau NIP akun pegawai yang dipertahankan}
                            {--nip=199004122019031003 : NIP untuk akun pegawai yang dipertahankan jika belum ada}
                            {--jabatan=Pranata Komputer Ahli Pertama : Jabatan akun yang dipertahankan}
                            {--golongan=III/b : Golongan akun yang dipertahankan}
                            {--dry-run : Menampilkan daftar akun & dokumen yang akan dihapus tanpa melakukan perubahan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus seluruh akun Pegawai lama kecuali 1 akun yang dipertahankan, beserta dokumen dan file storage fisiknya.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keepIdentifier = $this->option('keep') ?: 'pegawai@kominfo.go.id';
        $dryRun = (bool) $this->option('dry-run');

        $this->info("=============================================================");
        $this->info("   PEMBERSIHAN DATA AKUN PEGAWAI (SIMPATIK DISKOMINFO)");
        $this->info("=============================================================");
        $this->info("Akun Pegawai yang Dipertahankan : " . $keepIdentifier);
        $this->info("Mode Operasi                     : " . ($dryRun ? "[DRY RUN - SIMULASI ONLY]" : "[LIVE EXECUTION - PERMANENT]"));
        $this->newLine();

        // 1. Cari akun pegawai yang dipertahankan
        $keptUser = User::where('role', 'pegawai')
            ->where(function ($q) use ($keepIdentifier) {
                $q->where('email', $keepIdentifier)
                  ->orWhere('nip', $keepIdentifier);
            })->first();

        if (!$keptUser) {
            $this->error("Akun pegawai dengan identifier '{$keepIdentifier}' TIDAK DITEMUKAN di database!");
            return Command::FAILURE;
        }

        $this->info("Akun Pegawai Ditemukan untuk Dipertahankan:");
        $this->line(" - ID       : {$keptUser->id}");
        $this->line(" - Nama     : {$keptUser->nama}");
        $this->line(" - Email    : {$keptUser->email}");
        $this->line(" - Role     : {$keptUser->role}");
        $this->newLine();

        // 2. Ambil seluruh akun role pegawai KECUALI akun yang dipertahankan
        // Penting: Admin Master, Admin Kasubag, dan Bendahara Barang TIDAK BOLEH disentuh!
        $pegawaiToDelete = User::where('role', 'pegawai')
            ->where('id', '!=', $keptUser->id)
            ->get();

        $this->info("Total Akun Pegawai yang Akan Dihapus: " . $pegawaiToDelete->count());

        $deletedAccountsTable = [];
        $totalDocsCount = 0;
        $filesToDelete = [];

        foreach ($pegawaiToDelete as $p) {
            $docs = ArsipDokumen::where('user_id', $p->id)->get();
            $docCount = $docs->count();
            $totalDocsCount += $docCount;

            foreach ($docs as $d) {
                if ($d->file_path) {
                    $filesToDelete[] = $d->file_path;
                }
            }

            $deletedAccountsTable[] = [
                'ID' => $p->id,
                'Nama' => $p->nama,
                'Email' => $p->email,
                'NIP' => $p->nip ?? '-',
                'Total Dokumen' => $docCount,
            ];
        }

        if (!empty($deletedAccountsTable)) {
            $this->table(['ID', 'Nama', 'Email', 'NIP', 'Total Dokumen'], $deletedAccountsTable);
        } else {
            $this->comment("Tidak ada akun pegawai lain yang perlu dihapus.");
        }

        $this->info("Total Dokumen Terkait yang Akan Dihapus : {$totalDocsCount}");
        $this->info("Total File Fisik Storage Terkait       : " . count($filesToDelete));
        $this->newLine();

        if ($dryRun) {
            $this->warn(">>> HASIL DRY RUN: Tidak ada data yang dihapus dari database atau storage. <<<");
            $this->info("Jalankan tanpa flag --dry-run untuk mengeksekusi penghapusan permanen.");
            return Command::SUCCESS;
        }

        // 3. Eksekusi Penghapusan Sebenarnya
        DB::beginTransaction();
        try {
            $deletedAccountsCount = 0;
            $deletedDocsCount = 0;
            $deletedFilesCount = 0;

            foreach ($pegawaiToDelete as $pegawai) {
                $docs = ArsipDokumen::where('user_id', $pegawai->id)->get();

                foreach ($docs as $doc) {
                    // Hapus file fisik PDF dari disk public
                    if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                        Storage::disk('public')->delete($doc->file_path);
                        $deletedFilesCount++;
                    }

                    // Hapus baris dokumen
                    $doc->delete();
                    $deletedDocsCount++;
                }

                // Bersihkan sesi aktif dari tabel sessions jika ada
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('user_id', $pegawai->id)->delete();
                }

                // Bersihkan token reset password jika ada
                if (Schema::hasTable('password_reset_tokens')) {
                    DB::table('password_reset_tokens')->where('email', $pegawai->email)->delete();
                }

                // Hapus akun user
                $pegawai->delete();
                $deletedAccountsCount++;
            }

            // 4. Penyesuaian Akun yang Dipertahankan
            $nipInput = $this->option('nip') ?: '199004122019031003';
            $jabatanInput = $this->option('jabatan') ?: 'Pranata Komputer Ahli Pertama';
            $golonganInput = $this->option('golongan') ?: 'III/b';

            $keptUser->nip = $keptUser->nip ?: $nipInput;
            $keptUser->jabatan = $keptUser->jabatan ?: $jabatanInput;
            $keptUser->golongan = $keptUser->golongan ?: $golonganInput;
            $keptUser->must_change_password = true;
            $keptUser->two_factor_secret = null;
            $keptUser->two_factor_recovery_codes = null;
            $keptUser->two_factor_confirmed_at = null;
            $keptUser->save();

            // Set must_change_password = true HANYA untuk role pegawai, akun non-pegawai tetap false
            User::where('role', 'pegawai')->update(['must_change_password' => true]);
            User::where('role', '!=', 'pegawai')->update(['must_change_password' => false]);

            DB::commit();

            $this->info("=============================================================");
            $this->info("   HASIL PEMBERSIHAN DATA BERHASIL (SELESAI)");
            $this->info("=============================================================");
            $this->info("Jumlah Akun Pegawai yang Dihapus : {$deletedAccountsCount}");
            $this->info("Jumlah Dokumen yang Dihapus      : {$deletedDocsCount}");
            $this->info("Jumlah File PDF Fisik Dihapus    : {$deletedFilesCount}");
            $this->newLine();

            $this->info("Data Akun Pegawai yang Dipertahankan:");
            $this->table(
                ['Field', 'Nilai', 'Status'],
                [
                    ['Nama', $keptUser->nama, 'Lengkap'],
                    ['NIP', $keptUser->nip, 'Lengkap (Siap untuk login NIP)'],
                    ['Email', $keptUser->email, 'Lengkap'],
                    ['Jabatan', $keptUser->jabatan, 'Lengkap'],
                    ['Golongan', $keptUser->golongan, 'Lengkap'],
                    ['Role', $keptUser->role, 'Pegawai'],
                    ['Must Change Password', $keptUser->must_change_password ? 'Ya (True)' : 'Tidak', 'Wajib Ganti Password Aktif'],
                    ['2FA Confirmed At', $keptUser->two_factor_confirmed_at ? $keptUser->two_factor_confirmed_at : 'Belum (Reset)', 'Wajib Setup 2FA Baru'],
                    ['Total Dokumen Tersisa', ArsipDokumen::where('user_id', $keptUser->id)->count(), 'Utuh (Dokumen milik Ahmad tetap aman)'],
                ]
            );

            // Cek akun lain untuk memastikan tidak tersentuh
            $otherUsers = User::where('id', '!=', $keptUser->id)->get();
            $this->info("Daftar Akun Lain yang Utuh Tidak Tersentuh:");
            $otherTable = [];
            foreach ($otherUsers as $ou) {
                $otherTable[] = [
                    'ID' => $ou->id,
                    'Nama' => $ou->nama,
                    'Email' => $ou->email,
                    'Role' => $ou->role,
                    'Must Change Password' => $ou->must_change_password ? 'Ya' : 'Tidak',
                ];
            }
            $this->table(['ID', 'Nama', 'Email', 'Role', 'Must Change Password'], $otherTable);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan saat pembersihan data: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
