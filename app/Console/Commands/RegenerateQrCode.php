<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\AsetPeralatanMesin;

class RegenerateQrCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:regenerate {--ip= : Alamat IP lokal jaringan (opsional, otomatis dideteksi jika kosong)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui IP lokal di .env, bersihkan config cache, dan sinkronisasi URL QR Code aset';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->option('ip');

        // Deteksi IP lokal jika parameter tidak diberikan
        if (empty($ip)) {
            $detectedIp = gethostbyname(gethostname());
            if ($detectedIp && $detectedIp !== '127.0.0.1') {
                $ip = $detectedIp;
            } else {
                $ip = '192.168.1.6'; // fallback default
            }
        }

        $newUrl = "http://{$ip}:8000";
        $this->info("=== SINKRONISASI IP & QR CODE SINOSIP ===");
        $this->info("IP Terdeteksi : {$ip}");
        $this->info("Target APP_URL: {$newUrl}");

        // 1. Update file .env
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (preg_match('/^APP_URL=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_URL=.*$/m', "APP_URL={$newUrl}", $envContent);
            } else {
                $envContent .= "\nAPP_URL={$newUrl}";
            }
            file_put_contents($envPath, $envContent);
            $this->info("[✓] File .env berhasil diperbarui dengan APP_URL={$newUrl}");
        } else {
            $this->warn("[!] File .env tidak ditemukan di: {$envPath}");
        }

        // 2. Clear config cache
        Artisan::call('config:clear');
        $this->info("[✓] Cache konfigurasi berhasil dibersihkan (php artisan config:clear).");

        // 3. Verifikasi URL QR Code Aset
        $totalAset = AsetPeralatanMesin::count();
        $sampleAset = AsetPeralatanMesin::first();

        $this->info("[✓] Total data aset terdaftar: {$totalAset}");
        if ($sampleAset) {
            $sampleUrl = rtrim($newUrl, '/') . '/aset/' . urlencode($sampleAset->no_reg_pemda);
            $this->line("    Contoh URL QR Code ({$sampleAset->no_reg_pemda}):");
            $this->line("    <fg=cyan>{$sampleUrl}</>");
        }

        $this->newLine();
        $this->info("QR Code aset sekarang siap discan dari HP / perangkat mobile di jaringan Wi-Fi yang sama.");
        $this->line("Pastikan server berjalan dengan: <fg=yellow>php artisan serve --host=0.0.0.0 --port=8000</>");

        return Command::SUCCESS;
    }
}
