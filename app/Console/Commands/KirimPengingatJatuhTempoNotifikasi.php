<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KenpaBerkala;
use App\Services\NotificationService;
use Carbon\Carbon;

class KirimPengingatJatuhTempoNotifikasi extends Command
{
    /**
     * Nama dan signature perintah konsol.
     */
    protected $signature = 'notifikasi:kirim-pengingat-jatuh-tempo {--dry-run : Menjalankan simulasi pengecekan tanpa membuat notifikasi di database}';

    /**
     * Deskripsi perintah.
     */
    protected $description = 'Kirim notifikasi in-app pengingat H-30 sebelum jatuh tempo Kenaikan Pangkat/KGB ke Admin Kasubag dan Pegawai bersangkutan';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        // Mengikuti acuan KirimPengingatPangkat (yang terjadwal di console): target persis 1 bulan / 30 hari ke depan
        $targetTanggal = Carbon::now()->addDays(30)->toDateString();

        $this->info("=== PENGINGAT JATUH TEMPO H-30 (IN-APP) ===");
        $this->info("Tanggal Target Jatuh Tempo: {$targetTanggal}");
        if ($isDryRun) {
            $this->warn("[MODE DRY-RUN] Tidak ada data yang akan disimpan ke database.");
        }

        // Ambil data kenpa_berkalas yang jatuh tempo tepat pada tanggal target (khusus ASN, status Aktif)
        $dataPengajuan = KenpaBerkala::with(['pegawai.user'])
            ->whereHas('pegawai', function ($q) {
                $q->where('kategori_pegawai', 'ASN');
            })
            ->whereDate('tgl_jatuh_tempo', $targetTanggal)
            ->where('status', 'Aktif')
            ->get();

        $totalPegawaiJatuhTempo = $dataPengajuan->count();
        $this->line("Ditemukan {$totalPegawaiJatuhTempo} pegawai ASN yang memenuhi kriteria H-30.");

        $totalKasubagNotified = 0;
        $totalPegawaiNotified = 0;

        foreach ($dataPengajuan as $kenpa) {
            $pegawai = $kenpa->pegawai;
            $nama = $pegawai?->nama ?? 'Tanpa Nama';
            $nip = $pegawai?->nip ?? '-';
            $jenis = $kenpa->jenis ?? 'Kenaikan Pangkat/KGB';

            $this->line("- Pegawai: {$nama} (NIP: {$nip}), Jenis: {$jenis}, Jatuh Tempo: {$kenpa->tgl_jatuh_tempo}");

            $res = NotificationService::notifyPengingatJatuhTempoH30($kenpa, $isDryRun);
            $totalKasubagNotified += $res['kasubag'];
            $totalPegawaiNotified += $res['pegawai'];
        }

        $this->info("--- Ringkasan Eksekusi ---");
        $this->info("Total Pegawai Masuk Jendela H-30: {$totalPegawaiJatuhTempo}");
        $this->info("Notifikasi Kasubag " . ($isDryRun ? "Akan Dikirim: " : "Berhasil Dikirim: ") . $totalKasubagNotified);
        $this->info("Notifikasi Pegawai " . ($isDryRun ? "Akan Dikirim: " : "Berhasil Dikirim: ") . $totalPegawaiNotified);

        return Command::SUCCESS;
    }
}

