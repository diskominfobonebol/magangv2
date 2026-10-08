<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Master Jenis Surat
        if (\App\Models\JenisSurat::count() === 0) {
            \App\Models\JenisSurat::insert([
                ['kode' => '090', 'nama_jenis' => 'SPPD', 'created_at' => now(), 'updated_at' => now()],
                ['kode' => '555', 'nama_jenis' => 'SPT', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            PegawaiSeeder::class,
            JenisDokumenSeeder::class,
            SuratSeeder::class,
            AsetSeeder::class,
        ]);
    }
}
