<?php

namespace Database\Seeders;

use App\Models\KategoriDokumen;
use App\Models\JenisDokumen;
use Illuminate\Database\Seeder;

class MasterKategoriDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kategori' => 'Identitas & Data Keluarga',
                'urutan' => 1,
                'jenis' => [
                    'Kartu Pegawai (Karpeg)',
                    'Kartu Isteri (Karis)',
                    'Kartu Suami (Karsu)',
                    'Kartu Keluarga (KK)',
                    'Akta Kelahiran Anak',
                    'Akta Perkawinan/Akta Nikah',
                    'Laporan Perkawinan Pertama',
                    'Akta Perceraian',
                    'Akta Kematian',
                    'Daftar Keluarga',
                    'Daftar Riwayat Hidup',
                ],
            ],
            [
                'kategori' => 'Dokumen Pengangkatan & Kepegawaian Dasar',
                'urutan' => 2,
                'jenis' => [
                    'SK CPNS',
                    'SK PNS',
                    'Surat Tanda Lulus Prajabatan',
                    'SK Penyesuaian Masa Kerja (PMK)',
                    'Kenaikan Gaji Berkala (KGB)',
                    'SK Kenaikan Pangkat',
                    'Nota Usul Kenaikan Pangkat',
                ],
            ],
            [
                'kategori' => 'Karir, Jabatan, & Evaluasi Kinerja',
                'urutan' => 3,
                'jenis' => [
                    'SK Jabatan (Fungsional/Struktural + Pelantikan, SPMT)',
                    'PAK Jabatan Fungsional',
                    'Sertifikat Kompetensi pada Jabatan Fungsional',
                    'Daftar Riwayat Jabatan',
                    'SKP (Sasaran Kinerja Pegawai)',
                    'Dokumen Anjab (Analisis Jabatan)',
                ],
            ],
            [
                'kategori' => 'Pendidikan, Pelatihan, & Pengembangan Kompetensi',
                'urutan' => 4,
                'jenis' => [
                    'Ijazah & Transkrip Nilai',
                    'Surat Tanda Lulus Ujian Dinas',
                    'Surat Tanda Lulus Kenaikan Pangkat Penyesuaian Ijazah',
                    'Usul Pencantuman Gelar Akademik',
                    'Sertifikat Pendidik',
                    'Diklat PIM 2/PKN',
                    'Diklat PIM 3/PKA',
                    'Diklat PIM 4/PKP',
                    'Biodata Calon Peserta Ujian Dinas/Penyesuaian Ijazah',
                    'Rekomendasi Ujian Dinas/Penyesuaian Ijazah',
                ],
            ],
            [
                'kategori' => 'Pengurusan Izin & Tugas Belajar',
                'urutan' => 5,
                'jenis' => [
                    'SK Izin Belajar',
                    'SK Tugas Belajar & Surat Keterangan Tugas Belajar',
                    'Permohonan Tugas Belajar & Rekomendasi Tugas Belajar',
                    'Surat Keterangan Lulus Seleksi diterima sebagai Mahasiswa',
                    'Surat Pernyataan Membiayai Sendiri Kuliah',
                    'Surat Pernyataan Tidak Menuntut Kenaikan Pangkat dan Penyesuaian Ijazah',
                    'Surat Keterangan Perkuliahan Tidak Mengganggu Tupoksi',
                    'Akreditasi Kampus/Izin Penyelenggaraan Studi (BAN-PT)',
                    'Tampilan Layar Pangkalan Data Forlap Dikti',
                    'MOU Universitas',
                ],
            ],
            [
                'kategori' => 'Mutasi & Pindah Instansi',
                'urutan' => 6,
                'jenis' => [
                    'Persetujuan Pindah Instansi (PPK Asal dan Tujuan)',
                    'SK Mutasi Pindah',
                    'Usul Pindah Instansi',
                ],
            ],
            [
                'kategori' => 'Pengurusan Cuti & Kesehatan',
                'urutan' => 7,
                'jenis' => [
                    'Permohonan Cuti',
                    'Rekomendasi Cuti',
                    'Surat Cuti',
                    'Surat Keterangan Dokter',
                    'Surat Keterangan Dokter (Tim Penguji Kesehatan)',
                ],
            ],
            [
                'kategori' => 'Pemberhentian & Pensiun',
                'urutan' => 8,
                'jenis' => [
                    'Surat Permohonan Berhenti APS ttd YBS',
                    'Surat Permohonan APS ttd PPK',
                    'Surat Permohonan Berhenti APS ttd PPK',
                    'Usul Pemberhentian Uzur',
                    'SK Pembebasan',
                    'Daftar Penerima Calon Pensiun',
                ],
            ],
            [
                'kategori' => 'Pemeriksaan, Disiplin, & Etika',
                'urutan' => 9,
                'jenis' => [
                    'Surat Keterangan Tidak Pernah Dijatuhi Hukuman Disiplin',
                    'Surat Keterangan Tidak Pernah Dijatuhi Hukuman Pidana',
                    'Surat Keterangan Tidak Sedang Menjalani Hukuman Disiplin Tingkat Sedang/Berat',
                    'Surat Keterangan Tidak Pernah Melanggar Kode Etik ASN Tingkat Sedang/Berat',
                    'Surat Keterangan Tidak Sedang Menjalani Pemberhentian Sementara',
                    'Surat Keterangan Temuan Inspektorat',
                ],
            ],
            [
                'kategori' => 'Penghargaan & Administrasi Umum',
                'urutan' => 10,
                'jenis' => [
                    'Satya Lencana 10 Tahun',
                    'Satya Lencana 20 Tahun',
                    'Satya Lencana 30 Tahun',
                    'Surat Pengantar',
                ],
            ],
            [
                'kategori' => 'Dokumen Lainnya',
                'urutan' => 11,
                'jenis' => [],
            ],
        ];

        foreach ($data as $item) {
            $kategori = KategoriDokumen::updateOrCreate(
                ['nama_kategori' => $item['kategori']],
                ['urutan' => $item['urutan']]
            );

            foreach ($item['jenis'] as $idx => $jenis) {
                JenisDokumen::updateOrCreate(
                    [
                        'kategori_dokumen_id' => $kategori->id,
                        'nama_jenis' => $jenis,
                    ],
                    [
                        'urutan' => $idx + 1,
                    ]
                );
            }
        }
    }
}
