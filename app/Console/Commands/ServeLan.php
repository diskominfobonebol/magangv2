<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ServeLan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serve:lan {--port=8000 : Port server dijalankan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mendeteksi IP lokal WiFi/LAN, memperbarui APP_URL di .env, dan menjalankan server pada 0.0.0.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $port = $this->option('port') ?: '8000';
        $this->info('=====================================================');
        $this->info('  Mendeteksi IP Jaringan Lokal (WiFi / Ethernet)...  ');
        $this->info('=====================================================');

        $ip = $this->detectLocalIp();

        if ($ip) {
            $this->info("✓ IP Lokal Terdeteksi: {$ip}");
            $this->updateEnvAppUrl("http://{$ip}:{$port}");
        } else {
            $this->warn('! Tidak dapat mendeteksi IP lokal secara otomatis, menggunakan konfigurasi APP_URL saat ini.');
            $ip = '127.0.0.1';
        }

        $this->newLine();
        $this->line("  🌐 Akses Laptop/Komputer : <fg=cyan>http://127.0.0.1:{$port}</>");
        $this->line("  📱 Akses HP / Scan QR    : <fg=green;options=bold>http://{$ip}:{$port}</>");
        $this->newLine();
        $this->comment("Catatan: Pastikan HP dan Laptop terhubung ke WiFi/Hotspot yang SAMA.");
        $this->comment("Tekan CTRL+C untuk menghentikan server.");
        $this->newLine();

        // Jalankan server dengan host 0.0.0.0 agar bisa diakses dari jaringan luar (HP/Tablet)
        passthru("php artisan serve --host=0.0.0.0 --port={$port}");
    }

    /**
     * Mendeteksi IPv4 lokal aktif
     */
    protected function detectLocalIp(): ?string
    {
        // 1. Coba deteksi via ipconfig di Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('ipconfig');
            if ($output) {
                // Cari adapter yang memiliki Default Gateway (menandakan adapter yang sedang aktif terhubung)
                $lines = explode("\n", $output);
                $currentAdapter = '';
                $adapterIps = [];

                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (str_ends_with($trimmed, ':') && !str_starts_with($trimmed, ' ')) {
                        $currentAdapter = $trimmed;
                    }

                    if (preg_match('/IPv4 Address[.\s]+:\s*([0-9\.]+)/i', $line, $matches)) {
                        $detected = trim($matches[1]);
                        if ($detected !== '127.0.0.1') {
                            $adapterIps[$currentAdapter]['ip'] = $detected;
                        }
                    }

                    if (preg_match('/Default Gateway[.\s]+:\s*([0-9\.]+)/i', $line, $gwMatches)) {
                        if (!empty($adapterIps[$currentAdapter])) {
                            $adapterIps[$currentAdapter]['has_gw'] = true;
                        }
                    }
                }

                // Prioritaskan adapter yang memiliki default gateway (WiFi/LAN internet)
                foreach ($adapterIps as $adapter => $data) {
                    if (!empty($data['has_gw']) && !empty($data['ip'])) {
                        return $data['ip'];
                    }
                }

                // Fallback adapter pertama yang punya IP
                foreach ($adapterIps as $adapter => $data) {
                    if (!empty($data['ip'])) {
                        return $data['ip'];
                    }
                }
            }
        }

        // 2. Fallback via hostname PHP
        $hostIps = gethostbynamel(gethostname());
        if ($hostIps) {
            foreach ($hostIps as $hostIp) {
                if ($hostIp !== '127.0.0.1' && !str_starts_with($hostIp, '169.254.')) {
                    return $hostIp;
                }
            }
        }

        return null;
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
            $updated = preg_replace($pattern, "APP_URL={$newUrl}", $content);
            File::put($envPath, $updated);
            $this->info("✓ File .env berhasil diperbarui -> APP_URL={$newUrl}");
        }
    }
}
