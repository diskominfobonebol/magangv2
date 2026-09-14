<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KenpaBerkala;
use App\Http\Controllers\KenaikanPangkatController;
use Carbon\Carbon;

class KirimPengingatPangkat extends Command
{
    protected $signature = 'pangkat:kirim-pengingat';
    protected $description = 'Kirim pengingat WhatsApp H-1 bulan sebelum jatuh tempo Kenaikan Pangkat/KGB';

    public function handle()
    {
        // Tanggal persis 1 bulan ke depan dari hari ini (current date: Sept 2026)
        $targetTanggal = Carbon::now()->addMonth()->format('Y-m-d');

        // Ambil data kenpa_berkalas yang jatuh temponya tepat 1 bulan ke depan (khusus ASN)
        $dataPengajuan = KenpaBerkala::with('pegawai')
            ->whereHas('pegawai', function($q) {
                $q->where('kategori_pegawai', 'ASN');
            })
            ->whereDate('tgl_jatuh_tempo', $targetTanggal)
            ->where('status', 'Aktif')
            ->get();

        $controller = new KenaikanPangkatController();
        $jumlahKirim = 0;

        foreach ($dataPengajuan as $item) {
            $pegawai = $item->pegawai;

            if ($pegawai && $pegawai->no_wa) {
                $nama = $pegawai->nama;
                $tglJatuhTempo = $item->tgl_jatuh_tempo;
                $jenis = $item->jenis;

                $pesan = "Halo *{$nama}*, ini adalah pengingat otomatis dari sistem Sinosip. Masa *{$jenis}* Anda akan jatuh tempo pada tanggal *{$tglJatuhTempo}* (1 bulan lagi). Mohon segera persiapkan dan lengkapi berkas yang diperlukan.";

                // Panggil method kirimWhatsApp yang sudah ada di controller Anda menggunakan Reflection/akses publik jika perlu, 
                // atau kita buat method kirimWhatsApp menjadi public agar bisa dipanggil dari luar.
                
                // *Catatan Penting*: Karena kirimWhatsApp di controller Anda bersifat private, 
                // ubah dulu modifier-nya dari `private function kirimWhatsApp` menjadi `public function kirimWhatsApp`.
                
                $controller->kirimWhatsApp($pegawai->no_wa, $pesan);
                $jumlahKirim++;
                
                $this->info("Pengingat berhasil dikirim ke: {$nama} ({$pegawai->no_wa})");
            }
        }

        $this->info("Selesai. Total {$jumlahKirim} pesan pengingat berhasil diproses.");
    }
}