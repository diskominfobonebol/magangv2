<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FlagDefaultPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pegawai:flag-default-password 
                            {--dry-run : Simulasi pemeriksaan tanpa mengubah data di database} 
                            {--password= : Password default spesifik yang ingin diuji}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menandai akun pegawai yang masih menggunakan kata sandi default agar wajib mengganti kata sandi saat login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = (bool)$this->option('dry-run');
        $customPassword = $this->option('password');

        $defaultPasswords = [];
        if ($customPassword) {
            $defaultPasswords[] = $customPassword;
        } else {
            $configured = config('auth.default_pegawai_password', 'Kominfo#2026');
            $defaultPasswords[] = $configured;
            if ($configured !== 'password123') {
                $defaultPasswords[] = 'password123';
            }
        }

        if ($isDryRun) {
            $this->warn('=== MODE DRY-RUN: Pemeriksaan simulasi (tidak mengubah database) ===');
        } else {
            $this->info('=== MENJALANKAN PENANDAAN AKUN PEGAWAI DENGAN KATA SANDI DEFAULT ===');
        }

        $this->line('Menguji kecocokan dengan password default: ' . implode(', ', array_map(fn($p) => '"' . $p . '"', $defaultPasswords)));

        $pegawaiUsers = User::where('role', 'pegawai')->get();
        $this->info("Total akun pegawai yang diperiksa: {$pegawaiUsers->count()} akun.");

        $flaggedUsers = [];

        foreach ($pegawaiUsers as $user) {
            $matchedPassword = null;
            foreach ($defaultPasswords as $defPass) {
                if (Hash::check($defPass, $user->password)) {
                    $matchedPassword = $defPass;
                    break;
                }
            }

            if ($matchedPassword !== null) {
                $statusSebelum = $user->must_change_password ? 'Sudah Flagged' : 'Belum Flagged';
                $flaggedUsers[] = [
                    'id'             => $user->id,
                    'nama'           => $user->nama,
                    'nip'            => $user->nip ?: '-',
                    'urutan'         => $user->urutan ?? '-',
                    'status_sebelum' => $statusSebelum,
                    'user_model'     => $user,
                ];
            }
        }

        $count = count($flaggedUsers);
        $this->newLine();

        if ($count === 0) {
            $this->info('Tidak ditemukan akun pegawai yang menggunakan kata sandi default.');
            return 0;
        }

        $this->warn("Ditemukan {$count} akun pegawai yang terkonfirmasi masih menggunakan kata sandi default:");
        $tableData = [];
        foreach ($flaggedUsers as $f) {
            $tableData[] = [
                'ID' => $f['id'],
                'Urutan' => $f['urutan'],
                'Nama' => $f['nama'],
                'NIP' => $f['nip'],
                'Status Sebelumnya' => $f['status_sebelum'],
                'Status Baru' => 'Wajib Ganti Password (must_change_password = true)',
            ];
        }
        $this->table(['ID', 'Urutan', 'Nama', 'NIP', 'Status Sebelumnya', 'Status Baru'], $tableData);

        if (!$isDryRun) {
            $updated = 0;
            foreach ($flaggedUsers as $f) {
                $user = $f['user_model'];
                $user->must_change_password = true;
                $user->save();
                $updated++;
            }
            $this->newLine();
            $this->info("Berhasil memperbarui {$updated} akun pegawai! Seluruh akun tersebut wajib mengganti kata sandi saat login berikutnya.");
        } else {
            $this->newLine();
            $this->warn("Dry-run selesai. Jalankan tanpa opsi --dry-run untuk menerapkan tanda ke database.");
        }

        return 0;
    }
}

