<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GenerateAkunPegawai extends Command
{
    protected $signature = 'pegawai:generate-akun';
    protected $description = 'Membuatkan akun login otomatis untuk semua pegawai yang belum memiliki user_id';

    public function handle()
    {
        $pegawais = Pegawai::whereNull('user_id')->get();
        $count = 0;

        foreach ($pegawais as $p) {
            $user = User::create([
                'name' => $p->nama,
                'email' => $p->nip,
                'password' => Hash::make('123456'),
                'role_id' => 3,
            ]);

            $p->update(['user_id' => $user->id]);
            $count++;
        }

        $this->info("Berhasil membuatkan akun login untuk $count pegawai! Password default: 123456");
    }
}