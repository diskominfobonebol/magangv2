<?php

namespace Database\Seeders;

use App\Models\ArsipDokumen;
use App\Models\JenisDokumen;
use App\Models\KategoriDokumen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ArsipDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $pegawaiAhmad = User::where('email', 'pegawai@kominfo.go.id')->first();
        $kasubag = User::where('email', 'kasubag@kominfo.go.id')->first();

        $uploaderId = $kasubag ? $kasubag->id : null;

        // Pastikan folder arsip_dokumen ada
        if (!Storage::disk('public')->exists('arsip_dokumen')) {
            Storage::disk('public')->makeDirectory('arsip_dokumen');
        }

        // Siapkan dummy file PDF jika belum ada
        $samplePdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000098 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n180\n%%EOF";

        $sampleFiles = [
            'arsip_dokumen/sample-sk-pangkat.pdf',
            'arsip_dokumen/sample-sk-berkala.pdf',
            'arsip_dokumen/sample-ijazah.pdf',
        ];

        foreach ($sampleFiles as $f) {
            if (!Storage::disk('public')->exists($f)) {
                Storage::disk('public')->put($f, $samplePdfContent);
            }
        }

        // Cari master kategori dan jenis dokumen
        $katPangkat = KategoriDokumen::where('nama_kategori', 'Dokumen Pengangkatan & Kepegawaian Dasar')->first();
        $jenisPangkat = JenisDokumen::where('nama_jenis', 'SK Kenaikan Pangkat')->first();
        $jenisKGB = JenisDokumen::where('nama_jenis', 'Kenaikan Gaji Berkala (KGB)')->first();

        $katPendidikan = KategoriDokumen::where('nama_kategori', 'Pendidikan, Pelatihan, & Pengembangan Kompetensi')->first();
        $jenisIjazah = JenisDokumen::where('nama_jenis', 'Ijazah & Transkrip Nilai')->first();

        if ($pegawaiAhmad) {
            ArsipDokumen::updateOrCreate(
                [
                    'user_id' => $pegawaiAhmad->id,
                    'nomor_dokumen' => '823.3/BKPSDM-BB/SK-KP/042/2024',
                ],
                [
                    'kategori_dokumen_id' => $katPangkat?->id,
                    'jenis_dokumen_id' => $jenisPangkat?->id,
                    'kategori' => $katPangkat?->nama_kategori ?? 'Dokumen Pengangkatan & Kepegawaian Dasar',
                    'jenis_dokumen' => $jenisPangkat?->nama_jenis ?? 'SK Kenaikan Pangkat',
                    'tmt_tahun' => '2024',
                    'keterangan' => 'SK Kenaikan Pangkat Penata Muda Tingkat I Golongan III/b terhitung mulai tanggal 01 April 2024.',
                    'file_path' => 'arsip_dokumen/sample-sk-pangkat.pdf',
                    'file_name' => 'SK_Kenaikan_Pangkat_Ahmad_2024.pdf',
                    'file_size' => 148500,
                    'uploaded_by' => $uploaderId,
                    'last_action_by' => $uploaderId,
                    'last_action_role' => 'Kasubag',
                    'last_action_type' => 'upload',
                ]
            );

            ArsipDokumen::updateOrCreate(
                [
                    'user_id' => $pegawaiAhmad->id,
                    'nomor_dokumen' => '822.2/DISKOMINFO/KGB/018/2023',
                ],
                [
                    'kategori_dokumen_id' => $katPangkat?->id,
                    'jenis_dokumen_id' => $jenisKGB?->id,
                    'kategori' => $katPangkat?->nama_kategori ?? 'Dokumen Pengangkatan & Kepegawaian Dasar',
                    'jenis_dokumen' => $jenisKGB?->nama_jenis ?? 'Kenaikan Gaji Berkala (KGB)',
                    'tmt_tahun' => '2023',
                    'keterangan' => 'Kenaikan Gaji Berkala periode 2 tahunan masa kerja 6 tahun.',
                    'file_path' => 'arsip_dokumen/sample-sk-berkala.pdf',
                    'file_name' => 'SK_KGB_Ahmad_2023.pdf',
                    'file_size' => 112300,
                    'uploaded_by' => $uploaderId,
                    'last_action_by' => $uploaderId,
                    'last_action_role' => 'Kasubag',
                    'last_action_type' => 'upload',
                ]
            );

            ArsipDokumen::updateOrCreate(
                [
                    'user_id' => $pegawaiAhmad->id,
                    'nomor_dokumen' => 'UNG-TI/S1/2018/8892',
                ],
                [
                    'kategori_dokumen_id' => $katPendidikan?->id,
                    'jenis_dokumen_id' => $jenisIjazah?->id,
                    'kategori' => $katPendidikan?->nama_kategori ?? 'Pendidikan, Pelatihan, & Pengembangan Kompetensi',
                    'jenis_dokumen' => $jenisIjazah?->nama_jenis ?? 'Ijazah & Transkrip Nilai',
                    'tmt_tahun' => '2018',
                    'keterangan' => 'Salinan legalisir Ijazah dan Transkrip Akademik Sarjana Komputer.',
                    'file_path' => 'arsip_dokumen/sample-ijazah.pdf',
                    'file_name' => 'Ijazah_S1_Ahmad_2018.pdf',
                    'file_size' => 324100,
                    'uploaded_by' => $uploaderId,
                    'last_action_by' => $uploaderId,
                    'last_action_role' => 'Kasubag',
                    'last_action_type' => 'upload',
                ]
            );
        }
    }
}
