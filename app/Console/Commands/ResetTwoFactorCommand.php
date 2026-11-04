<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetTwoFactorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-2fa {identity : Email atau NIP pengguna yang ingin direset 2FA-nya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset data Autentikasi Dua Faktor (2FA TOTP & Recovery Codes) pengguna via CLI/server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identity = trim($this->argument('identity'));

        $user = User::where('email', $identity)
            ->orWhere('email', $identity . '@sinosip.local')
            ->orWhereHas('pegawai', function ($q) use ($identity) {
                $q->where('nip', $identity);
            })
            ->first();

        if (!$user) {
            $this->error("Pengguna dengan email atau NIP '{$identity}' tidak ditemukan.");
            return Command::FAILURE;
        }

        $user->resetTwoFactor();

        $this->info("======================================================================");
        $this->info("                 RESET 2FA BERHASIL DISELESAIKAN                      ");
        $this->info("======================================================================");
        $this->line("Pengguna : <fg=cyan>{$user->name}</>");
        $this->line("Identitas: <fg=cyan>{$user->email}</>");
        $this->line("Role     : <fg=cyan>" . ($user->role->nama ?? 'Role ' . $user->role_id) . "</>");
        $this->line("Status   : <fg=green;options=bold>2FA telah direset.</>");
        $this->comment("Pengguna wajib melakukan setup 2FA ulang saat login berikutnya.");

        return Command::SUCCESS;
    }
}
