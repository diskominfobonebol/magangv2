<?php

namespace App\Console\Commands;

use App\Models\AsetPeralatanMesin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegenerateQr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:regenerate {--url= : URL publik baru (misal: https://xxxx.trycloudflare.com atau ngrok)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui URL QR Code (APP_URL) dan bersihkan cache konfigurasi untuk scan dari HP / Tunnel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newUrl = $this->option('url');

        $this->info('=====================================================');
        $this->info('    SINKRONISASI & REGENERASI QR CODE ASET SIMPATIK  ');
        $this->info('=====================================================');

        if ($newUrl) {
            $newUrl = rtrim($newUrl, '/');
            $this->updateEnvAppUrl($newUrl);
            $this->info("✓ APP_URL di .env berhasil diupdate ke: {$newUrl}");
        }

        // Bersihkan seluruh cache agar config URL terbaru langsung terbaca
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $currentAppUrl = config('app.url');
        $this->newLine();
        $this->info("✓ Konfigurasi APP_URL Aktif: {$currentAppUrl}");

        // Cek data sampel aset
        $sampleAsset = AsetPeralatanMesin::first();
        if ($sampleAsset) {
            $this->line("✓ Contoh URL QR Aset ({$sampleAsset->no_reg_pemda}):");
            $this->line("  🔗 <fg=cyan>{$sampleAsset->public_url}</>");
        }

        $this->newLine();
        $this->comment('Seluruh QR Code sekarang secara otomatis menggunakan URL di atas saat di-scan!');
        return 0;
    }

    /**
     * Memperbarui baris APP_URL pada file .env
     */
    protected function updateEnvAppUrl(string $newUrl): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);
        $pattern = '/^APP_URL=.*$/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "APP_URL={$newUrl}", $content);
        } else {
            $content .= "\nAPP_URL={$newUrl}";
        }

        File::put($envPath, $content);
    }
}
