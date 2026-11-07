<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;

class TestDriveConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drive:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uji coba dan verifikasi koneksi Google Drive API menggunakan Service Account';

    /**
     * Execute the console command.
     */
    public function handle(GoogleDriveService $driveService)
    {
        $this->info('===========================================================');
        $this->info('    SINOSIP - UJI KONEKSI GOOGLE DRIVE SERVICE ACCOUNT     ');
        $this->info('===========================================================');
        $this->line('');

        $this->comment('Sedang memverifikasi file credential dan autentikasi ke Google Drive API...');

        $result = $driveService->testConnection();

        $this->line('');
        $this->line("• Path Credential : " . ($result['credentials_path'] ?? '-'));
        $this->line("• Root Folder ID  : " . ($result['root_folder_id'] ?? '-'));
        $this->line('');

        if ($result['success']) {
            $this->info('✅ STATUS: ' . $result['message']);
            $this->line("• Jumlah item terdeteksi: " . ($result['sample_files_count'] ?? 0));
            
            if (!empty($result['sample_files'])) {
                $this->table(['ID File/Folder', 'Nama', 'MIME Type'], $result['sample_files']);
            }

            $this->line('');
            $this->info('🎉 Konfigurasi Google Drive API siap digunakan untuk pengunggahan surat!');
            return self::SUCCESS;
        } else {
            $this->error('❌ STATUS: ' . $result['message']);
            $this->line('');
            $this->warn('Petunjuk Setup:');
            $this->line('1. Letakkan file JSON Service Account di path di atas (contoh: storage/app/google-drive-credentials.json).');
            $this->line('2. Pastikan Google Drive API telah diaktifkan di Google Cloud Console.');
            $this->line('3. Jika menggunakan folder bersama (Shared Folder), bagikan folder tersebut ke email Service Account (editor).');
            $this->line('');
            return self::FAILURE;
        }
    }
}
