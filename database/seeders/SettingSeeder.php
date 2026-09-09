<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SystemSetting::insert([
            ['key' => 'app_name', 'value' => 'Sistem Arsip dan Notifikasi Bone Bolango', 'keterangan' => 'Nama Aplikasi'],
            ['key' => 'wa_api_url', 'value' => 'http://localhost:3000/send', 'keterangan' => 'URL API WhatsApp'],
            ['key' => 'wa_api_key', 'value' => 'secret123', 'keterangan' => 'API Key WhatsApp'],
            ['key' => 'format_surat_instansi', 'value' => 'BONE-BOLANGO', 'keterangan' => 'Format Nomenklatur Surat'],
        ]);
    }
}
