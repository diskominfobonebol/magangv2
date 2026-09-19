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
        if (\App\Models\JenisDokumen::count() == 0) {
            \App\Models\JenisDokumen::insert([
                ['kategori' => 'kenpa', 'nama_dokumen' => 'SK Pangkat Terakhir', 'is_wajib' => true, 'created_at' => now(), 'updated_at' => now()],
                ['kategori' => 'kenpa', 'nama_dokumen' => 'SK Jabatan Terakhir', 'is_wajib' => true, 'created_at' => now(), 'updated_at' => now()],
                ['kategori' => 'kenpa', 'nama_dokumen' => 'Penilaian Prestasi Kerja (SKP)', 'is_wajib' => true, 'created_at' => now(), 'updated_at' => now()],
                ['kategori' => 'berkala', 'nama_dokumen' => 'SK Kenaikan Gaji Berkala Terakhir', 'is_wajib' => true, 'created_at' => now(), 'updated_at' => now()],
                ['kategori' => 'berkala', 'nama_dokumen' => 'SK Pangkat Terakhir', 'is_wajib' => true, 'created_at' => now(), 'updated_at' => now()],
                ['kategori' => 'berkala', 'nama_dokumen' => 'Penilaian SKP Terakhir', 'is_wajib' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            PegawaiSeeder::class,
            SuratSeeder::class,
            AsetSeeder::class,
        ]);
    }
}
