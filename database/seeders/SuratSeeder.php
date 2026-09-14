<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surat;
use App\Models\Pegawai;
use App\Models\JenisSurat;
use App\Models\User;

class SuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan data Jenis Surat terdaftar
        JenisSurat::updateOrCreate(
            ['id' => 1],
            ['kode' => '090', 'nama_jenis' => 'SPPD']
        );

        JenisSurat::updateOrCreate(
            ['id' => 2],
            ['kode' => '555', 'nama_jenis' => 'SPT']
        );

        // Ambil User ID admin pembuat surat
        $adminUser = User::where('role_id', 1)->first() ?? User::first();
        $kasubagUser = User::where('role_id', 2)->first() ?? $adminUser;

        $adminId = $adminUser ? $adminUser->id : 1;
        $kasubagId = $kasubagUser ? $kasubagUser->id : $adminId;

        // Ambil daftar Pegawai berdasarkan NIP untuk relasi pivot surat_pegawai
        $pegawais = Pegawai::all()->keyBy('nip');

        // 2. Definisi 10 Data Dummy Surat Realistis Diskominfo Bone Bolango
        $dummySurats = [
            [
                'nomor_surat' => '555/001/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-01',
                'perihal' => 'Koordinasi Integrasi Jaringan Intra Pemerintah Daerah',
                'tujuan' => 'Dinas Kominfo dan Statistik Provinsi Gorontalo',
                'uraian' => 'Melaksanakan koordinasi teknis mengenai integrasi jaringan fiber optic dan interkoneksi data center Pemerintah Kabupaten Bone Bolango dengan Pemerintah Provinsi Gorontalo.',
                'keterangan' => 'Perjalanan dinas dilaksanakan selama 1 (satu) hari kerja.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197911202005011004', 'tugas' => 'Penanggung Jawab Tim', 'nomor_sppd' => '090/001/IX/2026'],
                    ['nip' => '198905052012121001', 'tugas' => 'Petugas Teknis Jaringan', 'nomor_sppd' => '090/002/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/002/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-02',
                'perihal' => 'Peliputan dan Publikasi Kunjungan Kerja Bupati Bone Bolango',
                'tujuan' => 'Kecamatan Suwawa Timur, Bone Bolango',
                'uraian' => 'Melakukan peliputan media, live streaming, dan dokumentasi kegiatan penyaluran bantuan sosial bersama Bupati Bone Bolango.',
                'keterangan' => 'Laporan dokumentasi dan siaran pers dipublikasikan di portal resmi daerah.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197709052002121001', 'tugas' => 'Koordinator Peliputan', 'nomor_sppd' => '090/003/IX/2026'],
                    ['nip' => '199505142019031005', 'tugas' => 'Dokumentasi & Fotografer', 'nomor_sppd' => '090/004/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/003/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => false,
                'tgl_surat' => '2026-09-03',
                'perihal' => 'Pemeliharaan dan Troubleshooting Server Data Center Diskominfo',
                'tujuan' => 'Ruang Server Data Center Diskominfo Bone Bolango',
                'uraian' => 'Melakukan perawatan rutin perangkat server rackmount, backup basis data sistem informasi daerah, dan pengecekan UPS data center.',
                'keterangan' => 'Tugas internal berkala tanpa perjalanan dinas luar.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199410122019032001', 'tugas' => 'Administrator Jaringan', 'nomor_sppd' => null],
                    ['nip' => '199701152022031003', 'tugas' => 'Database Administrator', 'nomor_sppd' => null],
                ]
            ],
            [
                'nomor_surat' => '555/004/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-04',
                'perihal' => 'Inspeksi dan Monitoring Menara Telekomunikasi (BTS)',
                'tujuan' => 'Kecamatan Bonepantai dan Bulawa, Bone Bolango',
                'uraian' => 'Melakukan pengawasan dan pengendalian menara BTS seluler terkait kepatuhan izin, kondisi fisik menara, dan kualitas pancaran sinyal seluler di wilayah pesisir.',
                'keterangan' => 'Dinas dilaksanakan menggunakan kendaraan dinas operasional.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '198104102006041002', 'tugas' => 'Penguji Telekomunikasi', 'nomor_sppd' => '090/005/IX/2026'],
                    ['nip' => '199111052015021003', 'tugas' => 'Teknisi Lapangan', 'nomor_sppd' => '090/006/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/005/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-05',
                'perihal' => 'Sosialisasi dan Pendampingan Aplikasi SPBE ke OPD Kecamatan',
                'tujuan' => 'Kantor Camat Kabila dan Kantor Camat Tilongkabila',
                'uraian' => 'Memberikan bimbingan teknis penggunaan aplikasi e-Office dan tanda tangan elektronik (TTE) bagi ASN di tingkat kecamatan.',
                'keterangan' => 'Bimtek diikuti oleh operator persuratan masing-masing kecamatan.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199804202022032004', 'tugas' => 'Narasumber Teknis SPBE', 'nomor_sppd' => '090/007/IX/2026'],
                    ['nip' => '199403222018012002', 'tugas' => 'Fasilitator & Pendamping', 'nomor_sppd' => '090/008/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/006/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-06',
                'perihal' => 'Konsultasi Pengelolaan Sistem Informasi Kepegawaian ke BKN Regional XI',
                'tujuan' => 'Kantor Regional XI BKN Manado, Sulawesi Utara',
                'uraian' => 'Konsultasi dan sinkronisasi data kenaikan pangkat serta periodisasi gaji berkala ASN Dinas Kominfo ke sistem SIASN BKN.',
                'keterangan' => 'Perjalanan dinas luar daerah selama 3 (tiga) hari kerja.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '198205122005012002', 'tugas' => 'Pejabat Pengelola Kepegawaian', 'nomor_sppd' => '090/009/IX/2026'],
                    ['nip' => '199004102015022001', 'tugas' => 'Operator Kepegawaian', 'nomor_sppd' => '090/010/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/007/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => false,
                'tgl_surat' => '2026-09-07',
                'perihal' => 'Verifikasi Berkas Kenaikan Gaji Berkala Pegawai Diskominfo',
                'tujuan' => 'Subbagian Kepegawaian & Umum Diskominfo Bone Bolango',
                'uraian' => 'Melakukan verifikasi berkas SK terakhir dan penilaian kinerja ASN yang memasuki masa jatuh tempo KGB periode Oktober 2026.',
                'keterangan' => 'Internal kantor Diskominfo Bone Bolango.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '199208222018011004', 'tugas' => 'Verifikator Berkas', 'nomor_sppd' => null],
                    ['nip' => '199512052020121005', 'tugas' => 'Penyusun Daftar Nominatif', 'nomor_sppd' => null],
                ]
            ],
            [
                'nomor_surat' => '555/008/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-08',
                'perihal' => 'Rekonsiliasi Laporan Keuangan dan Aset Triwulan III',
                'tujuan' => 'Badan Keuangan dan Pendapatan Daerah (BKPD) Bone Bolango',
                'uraian' => 'Melaksanakan rekonsiliasi data belanja modal, belanja operasional TIK, serta pencatatan inventaris barang milik daerah (BMD).',
                'keterangan' => 'Membawa buku inventaris dan bukti fisik pengadaan.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '198503152010011003', 'tugas' => 'Penanggung Jawab Keuangan', 'nomor_sppd' => '090/011/IX/2026'],
                    ['nip' => '198807142011012003', 'tugas' => 'Petugas Verifikasi Keuangan', 'nomor_sppd' => '090/012/IX/2026'],
                    ['nip' => '199306192019031006', 'tugas' => 'Petugas Pembukuan', 'nomor_sppd' => '090/013/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/009/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-09',
                'perihal' => 'Pemasangan Akses Internet Publik (Wi-Fi Gratis) di Kawasan Wisata',
                'tujuan' => 'Kawasan Wisata Danau Perintis, Kecamatan Suwawa',
                'uraian' => 'Instalasi router outdoor, konfigurasi bandwidth management, dan pengujian koneksi internet publik gratis untuk pengunjung wisata.',
                'keterangan' => 'Program prioritas perluasan akses digital desa wisata.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199607182020121004', 'tugas' => 'Instalatur Jaringan', 'nomor_sppd' => '090/014/IX/2026'],
                    ['nip' => '198905052012121001', 'tugas' => 'Supervisor Teknis', 'nomor_sppd' => '090/015/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/010/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-10',
                'perihal' => 'Kemitraan Media Massa dan Diseminasi Informasi Pembangunan Daerah',
                'tujuan' => 'Kantor PWI (Persatuan Wartawan Indonesia) Cabang Gorontalo',
                'uraian' => 'Koordinasi kemitraan publikasi program unggulan daerah Kabupaten Bone Bolango bersama perwakilan media cetak, elektronik, dan daring.',
                'keterangan' => 'Pertemuan koordinasi triwulanan bersama insan pers.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197709052002121001', 'tugas' => 'Koordinator Kemitraan', 'nomor_sppd' => '090/016/IX/2026'],
                    ['nip' => '199012012015022002', 'tugas' => 'Humas & Notulensi', 'nomor_sppd' => '090/017/IX/2026'],
                ]
            ],
        ];

        // 3. Masukkan data ke tabel surats dan surat_pegawai
        foreach ($dummySurats as $item) {
            $personil = $item['personil'];
            unset($item['personil']);
            $item['status'] = 'Terbit';

            $surat = Surat::updateOrCreate(
                ['nomor_surat' => $item['nomor_surat']],
                $item
            );

            // Relasikan personil ke tabel pivot surat_pegawai
            $syncData = [];
            foreach ($personil as $p) {
                $pegawaiModel = $pegawais->get($p['nip']) ?? Pegawai::where('nip', $p['nip'])->first();
                if ($pegawaiModel) {
                    $syncData[$pegawaiModel->id] = [
                        'keterangan_tugas' => $p['tugas'],
                        'nomor_sppd' => $p['nomor_sppd'],
                    ];
                }
            }

            if (!empty($syncData)) {
                $surat->pegawais()->sync($syncData);
            }
        }
    }
}
