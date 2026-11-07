<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Surat;
use App\Models\SuratMasuk;
use App\Models\SuratTelaah;
use App\Models\SuratSk;
use App\Models\KenpaBerkala;
use App\Models\DokumenPegawai;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserPurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:purge 
                            {--keep= : Daftar email atau NIP yang dikecualikan/dipertahankan (pisahkan dengan koma)} 
                            {--dry-run : Simulasi dan hanya tampilkan daftar akun serta data terkait tanpa menghapus apa pun} 
                            {--force : Paksa penghapusan tanpa konfirmasi interaktif (hanya jika dry-run tidak aktif)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan akun lama non-jabatan dengan aman beserta data relasinya (gunakan --dry-run untuk simulasi)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $keepList = array_filter(array_map('trim', explode(',', (string) $this->option('keep'))));

        $this->info("======================================================================");
        $this->info("           SINOSIP - ANALISIS & PEMBERSIHAN AKUN PENGGUNA             ");
        $this->info("======================================================================");
        $this->line("Mode: " . ($isDryRun ? "<fg=yellow;options=bold>DRY-RUN (SIMULASI - Tidak ada data yang dihapus)</>" : "<fg=red;options=bold>EKSEKUSI NYATA</>"));
        
        if (!empty($keepList)) {
            $this->line("Akun dipertahankan (--keep): <fg=cyan>" . implode(', ', $keepList) . "</>");
        }

        // 1. Ambil semua user kecuali Admin Master (role_id 1) dan Akun Jabatan Kasubag (role_id 2)
        // Perhatian: Akun jabatan tidak boleh ikut terhapus.
        $protectedRoleIds = [1, 2];
        $usersQuery = User::with(['role', 'pegawai'])->whereNotIn('role_id', $protectedRoleIds);

        $candidates = $usersQuery->get()->filter(function ($user) use ($keepList) {
            $email = strtolower($user->email);
            $nip = $user->pegawai ? $user->pegawai->nip : '';

            foreach ($keepList as $keep) {
                $keepLower = strtolower($keep);
                if ($email === $keepLower || $nip === $keepLower) {
                    return false;
                }
            }
            return true;
        });

        if ($candidates->isEmpty()) {
            $this->info("Tidak ada akun pengguna yang memenuhi kriteria pembersihan.");
            return Command::SUCCESS;
        }

        $tableRows = [];
        $totalFiles = 0;
        $fileList = [];

        foreach ($candidates as $user) {
            $pegawai = $user->pegawai;
            $pegawaiId = $pegawai ? $pegawai->id : null;

            // Hitung data relasi langsung dari user
            $suratCreated = Surat::where('created_by', $user->id)->count();
            $suratUpdated = Surat::where('updated_by', $user->id)->count();
            $suratMasukCreated = SuratMasuk::where('created_by', $user->id)->count();
            $telaahCreated = SuratTelaah::where('created_by', $user->id)->count();
            $skCreated = SuratSk::where('created_by', $user->id)->count();

            // Hitung data relasi dari pegawai
            $sptAssigned = $pegawaiId ? DB::table('surat_pegawai')->where('pegawai_id', $pegawaiId)->count() : 0;
            $telaahAssigned = $pegawaiId ? DB::table('pegawai_surat_telaah')->where('pegawai_id', $pegawaiId)->count() : 0;
            $kenpaCount = $pegawaiId ? KenpaBerkala::where('pegawai_id', $pegawaiId)->count() : 0;
            
            $dokumenCount = 0;
            $userFiles = [];
            if ($pegawaiId) {
                $kenpaIds = KenpaBerkala::where('pegawai_id', $pegawaiId)->pluck('id');
                $dokumens = DokumenPegawai::whereIn('kenpa_berkala_id', $kenpaIds)->get();
                $dokumenCount = $dokumens->count();
                foreach ($dokumens as $doc) {
                    if ($doc->file_path) {
                        $userFiles[] = $doc->file_path;
                        $fileList[] = $doc->file_path;
                    }
                }
            }
            $notifCount = $pegawaiId ? NotificationLog::where('pegawai_id', $pegawaiId)->count() : 0;
            $totalFiles += count($userFiles);

            $tableRows[] = [
                'ID' => $user->id,
                'Nama' => $user->name,
                'Email / NIP' => $user->email . ($pegawai && $pegawai->nip !== $user->email ? " (NIP: {$pegawai->nip})" : ""),
                'Role' => $user->role->nama ?? 'Pegawai',
                'Surat Dibuat' => ($suratCreated + $suratMasukCreated + $telaahCreated + $skCreated),
                'Tugas SPT/SPPD' => $sptAssigned,
                'Telaah' => $telaahAssigned,
                'Kenpa/Berkala' => $kenpaCount,
                'Dokumen' => $dokumenCount . " (" . count($userFiles) . " file)",
                'Notifikasi WA' => $notifCount,
            ];
        }

        $this->table(
            ['User ID', 'Nama Lengkap', 'Email / NIP', 'Role', 'Surat Dibuat', 'Tugas SPT/SPPD', 'Telaah', 'Kenpa/Berkala', 'Dokumen Upload', 'Log WA'],
            $tableRows
        );

        $this->line("");
        $this->info("Ringkasan Analisis:");
        $this->line("- Jumlah akun calon pembersihan : <fg=yellow;options=bold>" . count($candidates) . " akun</>");
        $this->line("- Jumlah file dokumen di storage: <fg=yellow;options=bold>" . $totalFiles . " file</>");
        $this->line("");

        $this->comment("Urutan Hapus yang Aman (Foreign Key & File Hierarchy):");
        $this->line(" 1. Hapus file fisik dokumen dari storage (total {$totalFiles} file)");
        $this->line(" 2. Hapus dokumen_pegawais terkait kenpa_berkalas");
        $this->line(" 3. Hapus notification_logs terkait pegawai");
        $this->line(" 4. Hapus pivot surat_pegawai & pegawai_surat_telaah");
        $this->line(" 5. Hapus kenpa_berkalas pegawai");
        $this->line(" 6. Lepas foreign key creator surat (surats, surat_masuk, telaah, sk) agar integritas arsip tetap utuh");
        $this->line(" 7. Hapus rekaman pegawais");
        $this->line(" 8. Hapus rekaman users");
        $this->line("");

        if ($isDryRun) {
            $this->warn("[DRY-RUN] Simulasi selesai. Tidak ada baris basis data atau file penyimpanan yang diubah atau dihapus.");
            $this->warn("Silakan tinjau daftar di atas dan konfirmasi akun mana yang ingin dipertahankan dengan opsi --keep=...");
            $this->warn("PENTING: Selalu buat BACKUP DATABASE sebelum menjalankan pembersihan sebenarnya!");
            return Command::SUCCESS;
        }

        // Pengamanan ketat: Jika bukan dry-run, minta konfirmasi ganda
        $this->error("PERINGATAN: Anda sedang menjalankan pembersihan NYATA. Data dan file akan DIHAPUS PERMANEN!");
        if (!$this->option('force')) {
            $confirm = $this->confirm("Apakah Anda yakin telah membuat backup database dan ingin menghapus " . count($candidates) . " akun di atas?", false);
            if (!$confirm) {
                $this->warn("Operasi dibatalkan oleh pengguna.");
                return Command::SUCCESS;
            }
        }

        // Eksekusi penghapusan dalam DB Transaction
        DB::beginTransaction();
        try {
            foreach ($candidates as $user) {
                $pegawai = $user->pegawai;
                if ($pegawai) {
                    $kenpaIds = KenpaBerkala::where('pegawai_id', $pegawai->id)->pluck('id');
                    $dokumens = DokumenPegawai::whereIn('kenpa_berkala_id', $kenpaIds)->get();
                    foreach ($dokumens as $doc) {
                        if ($doc->file_path && Storage::exists($doc->file_path)) {
                            Storage::delete($doc->file_path);
                        }
                    }
                    DokumenPegawai::whereIn('kenpa_berkala_id', $kenpaIds)->delete();
                    NotificationLog::where('pegawai_id', $pegawai->id)->delete();
                    DB::table('surat_pegawai')->where('pegawai_id', $pegawai->id)->delete();
                    DB::table('pegawai_surat_telaah')->where('pegawai_id', $pegawai->id)->delete();
                    KenpaBerkala::where('pegawai_id', $pegawai->id)->delete();
                    $pegawai->delete();
                }

                // Untuk surat yang dibuat oleh user ini, reassign creator ke Admin Master (id 1) agar arsip tidak yatim
                Surat::where('created_by', $user->id)->update(['created_by' => 1]);
                Surat::where('updated_by', $user->id)->update(['updated_by' => 1]);
                SuratMasuk::where('created_by', $user->id)->update(['created_by' => 1]);
                SuratMasuk::where('updated_by', $user->id)->update(['updated_by' => 1]);
                SuratTelaah::where('created_by', $user->id)->update(['created_by' => 1]);
                SuratTelaah::where('updated_by', $user->id)->update(['updated_by' => 1]);
                SuratSk::where('created_by', $user->id)->update(['created_by' => 1]);
                SuratSk::where('updated_by', $user->id)->update(['updated_by' => 1]);

                $user->delete();
            }

            DB::commit();
            $this->info("Pembersihan akun berhasil diselesaikan.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal melakukan pembersihan: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
