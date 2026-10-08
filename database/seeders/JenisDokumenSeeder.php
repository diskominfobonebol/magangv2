<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisDokumen;

class JenisDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. DOKUMEN KATEGORI KENPA (9 DOKUMEN)
        // ==========================================
        $doc1 = JenisDokumen::find(1);
        if ($doc1 && $doc1->kategori === 'kenpa') {
            $doc1->update(['nama_dokumen' => 'SK Kenpa terakhir', 'is_wajib' => true]);
        }

        $doc2 = JenisDokumen::find(2);
        if ($doc2 && $doc2->kategori === 'kenpa') {
            $doc2->update(['nama_dokumen' => 'Surat pengantar dari dinas lama', 'is_wajib' => true]);
        }

        $doc3 = JenisDokumen::find(3);
        if ($doc3 && $doc3->kategori === 'kenpa') {
            $doc3->update(['nama_dokumen' => 'SKP 2 tahun terakhir', 'is_wajib' => true]);
        }

        $doc4 = JenisDokumen::find(4);
        if ($doc4 && $doc4->kategori === 'kenpa') {
            $doc4->update(['nama_dokumen' => 'Ijazah terakhir', 'is_wajib' => true]);
        }

        $kenpaDocs = [
            'Ijazah terakhir',
            'SK Kenpa terakhir',
            'Surat pengantar dari dinas lama',
            'Kartu pegawai',
            'SKP 2 tahun terakhir',
            'STTPL pertama kali',
            'SK mutasi OPD lama',
            'SK pindah antar provinsi',
            'SKP pindah instansi'
        ];

        foreach ($kenpaDocs as $nama) {
            JenisDokumen::firstOrCreate(
                ['kategori' => 'kenpa', 'nama_dokumen' => $nama],
                ['is_wajib' => true]
            );
        }

        // ==========================================
        // 2. DOKUMEN KATEGORI BERKALA (7 DOKUMEN)
        // ==========================================
        $doc5 = JenisDokumen::find(5);
        if ($doc5 && $doc5->kategori === 'berkala') {
            $doc5->update(['nama_dokumen' => 'SK Berkala terakhir', 'is_wajib' => true]);
        }

        $doc6 = JenisDokumen::find(6);
        if ($doc6 && $doc6->kategori === 'berkala') {
            $doc6->update(['nama_dokumen' => 'SK Kenpa terakhir', 'is_wajib' => true]);
        }

        $doc7 = JenisDokumen::find(7);
        if ($doc7 && $doc7->kategori === 'berkala') {
            $doc7->update(['nama_dokumen' => 'SK 1 tahun terakhir', 'is_wajib' => true]);
        }

        $berkalaDocs = [
            'SK CPNS',
            'SK PNS',
            'SK Berkala terakhir',
            'Daftar gaji',
            'Surat pengantar',
            'SK 1 tahun terakhir',
            'SK Kenpa terakhir'
        ];

        foreach ($berkalaDocs as $nama) {
            JenisDokumen::firstOrCreate(
                ['kategori' => 'berkala', 'nama_dokumen' => $nama],
                ['is_wajib' => true]
            );
        }
    }
}
