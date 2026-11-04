<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BersihkanNotifikasiLama extends Command
{
    /**
     * Nama dan signature perintah konsol.
     */
    protected $signature = 'notifikasi:bersihkan';

    /**
     * Deskripsi perintah.
     */
    protected $description = 'Bersihkan notifikasi lawas: sudah dibaca > 90 hari dan belum dibaca > 180 hari';

    public function handle(): int
    {
        $batasDibaca = Carbon::now()->subDays(90);
        $batasBelumDibaca = Carbon::now()->subDays(180);

        // Hapus yang sudah dibaca > 90 hari
        $deletedRead = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $batasDibaca)
            ->delete();

        // Hapus yang belum dibaca > 180 hari
        $deletedUnread = DB::table('notifications')
            ->whereNull('read_at')
            ->where('created_at', '<', $batasBelumDibaca)
            ->delete();

        $this->info("Pembersihan notifikasi selesai:");
        $this->line("- Notifikasi sudah dibaca (> 90 hari) terhapus: {$deletedRead}");
        $this->line("- Notifikasi belum dibaca (> 180 hari) terhapus: {$deletedUnread}");

        return Command::SUCCESS;
    }
}

