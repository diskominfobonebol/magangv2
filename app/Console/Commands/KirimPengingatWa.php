<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KenpaBerkala;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class KirimPengingatWa extends Command
{
    protected $signature = 'whatsapp:kirim-pengingat';
    protected $description = 'Kirim pengingat WhatsApp otomatis H-30 hari sebelum jatuh tempo kenaikan pangkat/berkala';

    public function handle()
    {
        // Cari data yang tanggal jatuh temponya tepat 30 hari lagi dari hari ini
        $targetTanggal = Carbon::now()->addDays(30)->toDateString();

        $kenpas = KenpaBerkala::with('pegawai')
            ->whereHas('pegawai', function($q) {
                $q->where('kategori_pegawai', 'ASN');
            })
            ->whereDate('tgl_jatuh_tempo', $targetTanggal)
            ->where('status', 'Aktif')
            ->get();

        foreach ($kenpas as $kenpa) {
            $pegawai = $kenpa->pegawai;

            if ($pegawai && $pegawai->no_wa) {
                $pesan = "Halo Yth. {$pegawai->nama},\n\n" .
                         "Pemberitahuan sistem Sinosip: Pengajuan *{$kenpa->jenis}* Anda akan jatuh tempo pada tanggal " . Carbon::parse($kenpa->tgl_jatuh_tempo)->translatedFormat('d F Y') . " (30 hari lagi).\n\n" .
                         "Mohon segera login ke aplikasi Sinosip dan lengkapi berkas persyaratan Anda.\n\n" .
                         "Terima kasih.";

                // Panggil fungsi kirim
                $this->kirimPesanFonnte($pegawai->no_wa, $pesan);
            }
        }

        $this->info('Pengingat WhatsApp berhasil diproses.');
    }

    private function kirimPesanFonnte($tujuan, $pesan)
    {
        // Ambil token secara dinamis dari database (diatur oleh Admin Master)
        $token = \App\Models\Setting::where('key', 'wa_token')->value('value');
        
        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $tujuan,
                'message' => $pesan,
            ]);
        } catch (\Exception $e) {
            // Log error jika diperlukan
        }
    }
}