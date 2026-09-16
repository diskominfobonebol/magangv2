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

        // 2. Definisi 15 Data Dummy Surat Realistis Diskominfo Bone Bolango
        $dummySurats = [
            [
                'nomor_surat' => '555/001/DISKOMINFO-BB/SPT/VIII/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-08-05',
                'perihal' => 'Koordinasi Integrasi Jaringan Intra Pemerintah Daerah dan Interkoneksi Data Center',
                'tujuan' => 'Dinas Kominfo dan Statistik Provinsi Gorontalo',
                'uraian' => 'Melaksanakan koordinasi teknis mengenai integrasi jaringan fiber optic dan interkoneksi data center Pemerintah Kabupaten Bone Bolango dengan Pemerintah Provinsi Gorontalo.',
                'keterangan' => 'Perjalanan dinas dilaksanakan selama 1 (satu) hari kerja.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197911202005011004', 'tugas' => 'Penanggung Jawab Tim', 'nomor_sppd' => '090/001/VIII/2026'],
                    ['nip' => '198905052012121001', 'tugas' => 'Petugas Teknis Jaringan', 'nomor_sppd' => '090/002/VIII/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/002/DISKOMINFO-BB/SPT/VIII/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-08-11',
                'perihal' => 'Peliputan dan Publikasi Media Kunjungan Kerja Bupati Bone Bolango',
                'tujuan' => 'Kecamatan Suwawa Timur, Bone Bolango',
                'uraian' => 'Melakukan peliputan media, live streaming, dan dokumentasi kegiatan penyaluran bantuan sosial kemasyarakatan bersama Bupati Bone Bolango.',
                'keterangan' => 'Laporan dokumentasi dan siaran pers dipublikasikan di portal resmi daerah.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197709052002121001', 'tugas' => 'Koordinator Peliputan', 'nomor_sppd' => '090/003/VIII/2026'],
                    ['nip' => '199505142019031005', 'tugas' => 'Fotografer & Jurnalis Media', 'nomor_sppd' => '090/004/VIII/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/003/DISKOMINFO-BB/SPT/VIII/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => false,
                'tgl_surat' => '2026-08-18',
                'perihal' => 'Pemeliharaan Rutin Server dan Troubleshooting Data Center Diskominfo',
                'tujuan' => 'Ruang Server Data Center Diskominfo Bone Bolango',
                'uraian' => 'Melakukan patching sistem operasi server, perawatan fisik rack server, backup berkala basis data e-Government, dan pengecekan cadangan daya UPS.',
                'keterangan' => 'Tugas internal pemeliharaan sistem berkala tanpa perjalanan luar kantor.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199410122019032001', 'tugas' => 'Administrator Sistem & Jaringan', 'nomor_sppd' => null],
                    ['nip' => '199701152022031003', 'tugas' => 'Database Administrator', 'nomor_sppd' => null],
                ]
            ],
            [
                'nomor_surat' => '555/004/DISKOMINFO-BB/SPT/VIII/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-08-25',
                'perihal' => 'Inspeksi Teknis Menara Telekomunikasi (BTS) dan Monitoring Sinyal Pesisir',
                'tujuan' => 'Kecamatan Bonepantai dan Kecamatan Bulawa',
                'uraian' => 'Melakukan pengawasan dan pengendalian menara BTS seluler terkait kelaikan konstruksi, kepatuhan retribusi izin, dan kualitas sinyal seluler di wilayah pesisir Bone Bolango.',
                'keterangan' => 'Dinas lapangan menggunakan kendaraan operasional dinas.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '198104102006041002', 'tugas' => 'Penguji Telekomunikasi', 'nomor_sppd' => '090/005/VIII/2026'],
                    ['nip' => '199111052015021003', 'tugas' => 'Teknisi Pengukuran Frekuensi', 'nomor_sppd' => '090/006/VIII/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/005/DISKOMINFO-BB/SPT/VIII/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => false,
                'tgl_surat' => '2026-08-29',
                'perihal' => 'Sosialisasi dan Pembentukan Tim Tanggap Insiden Keamanan Siber (CSIRT)',
                'tujuan' => 'Aula Bappeda-Litbang Kabupaten Bone Bolango',
                'uraian' => 'Memberikan edukasi penanganan insiden siber, proteksi data pribadi ASN, dan standar keamanan informasi bagi pengelola sistem informasi seluruh OPD.',
                'keterangan' => 'Kegiatan workshop berlangsung 1 hari kerja diikuti oleh 35 OPD.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '198905052012121001', 'tugas' => 'Narasumber Keamanan Siber', 'nomor_sppd' => null],
                    ['nip' => '199004102015022001', 'tugas' => 'Fasilitator & Notulensi', 'nomor_sppd' => null],
                ]
            ],
            [
                'nomor_surat' => '555/006/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-02',
                'perihal' => 'Pendampingan Teknis dan Evaluasi Kematangan SPBE Tingkat Kecamatan',
                'tujuan' => 'Kantor Camat Kabila dan Kantor Camat Tilongkabila',
                'uraian' => 'Melakukan inventarisasi aplikasi pelayanan masyarakat, evaluasi tata kelola SPBE kecamatan, dan percepatan migrasi email kedinasan.',
                'keterangan' => 'Pemeriksaan kelaikan infrastruktur perangkat TI kecamatan.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '199804202022032004', 'tugas' => 'Analis e-Government', 'nomor_sppd' => '090/007/IX/2026'],
                    ['nip' => '199208222018011004', 'tugas' => 'Asisten Teknis Lapangan', 'nomor_sppd' => '090/008/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/007/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-04',
                'perihal' => 'Pemasangan Access Point dan Penguatan Jaringan WiFi Publik Desa Wisata',
                'tujuan' => 'Desa Lombongo, Kecamatan Suwawa Tengah',
                'uraian' => 'Melakukan instalasi access point outdoor, penarikan kabel fiber optik, dan konfigurasi bandwidth limiter untuk hotspot publik gratis bagi wisatawan.',
                'keterangan' => 'Program prioritas perluasan akses digital desa wisata unggulan daerah.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199607182020121004', 'tugas' => 'Instalatur Jaringan', 'nomor_sppd' => '090/009/IX/2026'],
                    ['nip' => '199403222018012002', 'tugas' => 'Analis Jaringan Nirkabel', 'nomor_sppd' => '090/010/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/008/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-06',
                'perihal' => 'Rapat Kemitraan Media Massa dan Diseminasi Informasi Pembangunan Daerah',
                'tujuan' => 'Kantor PWI (Persatuan Wartawan Indonesia) Cabang Gorontalo',
                'uraian' => 'Koordinasi kemitraan publikasi program unggulan daerah Kabupaten Bone Bolango bersama perwakilan media cetak, elektronik, dan daring.',
                'keterangan' => 'Pertemuan sinergi kemitraan media massa triwulan III.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197709052002121001', 'tugas' => 'Koordinator Kemitraan Pers', 'nomor_sppd' => '090/011/IX/2026'],
                    ['nip' => '199012012015022002', 'tugas' => 'Humas & Notulensi', 'nomor_sppd' => '090/012/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/009/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => false,
                'tgl_surat' => '2026-09-08',
                'perihal' => 'Workshop Literasi Digital bagi UMKM dan Komunitas Pemuda Kreatif',
                'tujuan' => 'Gedung Pemuda dan Olahraga Kabupaten Bone Bolango',
                'uraian' => 'Pelatihan pembuatan konten promosi digital, adopsi QRIS payment, dan strategi pemasaran online produk UMKM lokal Bone Bolango.',
                'keterangan' => 'Kolaborasi dengan Dinas Perindagkop Bone Bolango.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199512052020121005', 'tugas' => 'Moderator Pelatihan', 'nomor_sppd' => null],
                    ['nip' => '199505142019031005', 'tugas' => 'Instruktur Konten Visual', 'nomor_sppd' => null],
                ]
            ],
            [
                'nomor_surat' => '555/010/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => false,
                'tgl_surat' => '2026-09-10',
                'perihal' => 'Bimbingan Teknis Penerapan Tanda Tangan Elektronik (TTE) Tersertifikasi BSrE BSSN',
                'tujuan' => 'Ruang Pola Kantor Bupati Bone Bolango',
                'uraian' => 'Bimbingan teknis aktivasi sertifikat elektronik pejabat eselon III dan IV serta integrasi modul TTE pada sistem administrasi persuratan dinas.',
                'keterangan' => 'Penerapan amanat Perpres SPBE untuk digitalisasi persuratan resmi.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '198905052012121001', 'tugas' => 'Instruktur Teknis TTE', 'nomor_sppd' => null],
                    ['nip' => '199701152022031003', 'tugas' => 'Petugas Verifikasi Sertifikat', 'nomor_sppd' => null],
                ]
            ],
            [
                'nomor_surat' => '555/011/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-12',
                'perihal' => 'Audit Keamanan Infrastruktur TI dan Penilaian Kerentanan Jaringan OPD',
                'tujuan' => 'Dinas Kesehatan dan Dinas Kependudukan & Pencatatan Sipil',
                'uraian' => 'Melakukan vulnerability scanning, audit pengkabelan LAN, dan pengujian keandalan firewall lokal penyimpanan data kependudukan dan kesehatan.',
                'keterangan' => 'Laporan audit kepatuhan ISO 27001 sektor pemerintahan.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197911202005011004', 'tugas' => 'Ketua Tim Audit TI', 'nomor_sppd' => '090/013/IX/2026'],
                    ['nip' => '199410122019032001', 'tugas' => 'Auditor Jaringan & Server', 'nomor_sppd' => '090/014/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/012/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-13',
                'perihal' => 'Monitoring dan Pengukuran Bandwidth Internet Kantor Organisasi Perangkat Daerah',
                'tujuan' => 'Kompleks Perkantoran Pemda Bone Bolango, Suwawa',
                'uraian' => 'Melakukan monitoring throughput, latency, dan quality of service (QoS) jalur transmisi fiber optik backbone OPD menjelang implementasi sistem terpadu.',
                'keterangan' => 'Pengujian beban jaringan pada jam sibuk kedinasan.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '199607182020121004', 'tugas' => 'Penguji Kualitas Jaringan', 'nomor_sppd' => '090/015/IX/2026'],
                    ['nip' => '199208222018011004', 'tugas' => 'Teknisi Pendukung Lapangan', 'nomor_sppd' => '090/016/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/013/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-14',
                'perihal' => 'Penyusunan Arsitektur dan Masterplan Smart City Kabupaten Bone Bolango 2026-2030',
                'tujuan' => 'Kantor Bappeda Provinsi Gorontalo',
                'uraian' => 'Melakukan sinkronisasi masterplan smart city Kabupaten Bone Bolango dengan peta rencana arsitektur SPBE Provinsi Gorontalo.',
                'keterangan' => 'Rapat koordinasi teknis tim penyusun masterplan digital.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '197501011998031001', 'tugas' => 'Pengarah Smart City', 'nomor_sppd' => '090/017/IX/2026'],
                    ['nip' => '199804202022032004', 'tugas' => 'Penyusun Dokumen Arsitektur', 'nomor_sppd' => '090/018/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/014/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-15',
                'perihal' => 'Rakor Kominfo se-Provinsi Gorontalo terkait Penanganan Blankspot Daerah Terpencil',
                'tujuan' => 'Hotel Grand Q Kota Gorontalo',
                'uraian' => 'Menghadiri rakor pemetaan wilayah blankspot seluler dan penyusunan proposal program pembangunan BTS USO BAKTI Kominfo RI tahun 2027.',
                'keterangan' => 'Rapat kerja gabungan Dinas Kominfo se-wilayah Provinsi Gorontalo.',
                'created_by' => $kasubagId,
                'personil' => [
                    ['nip' => '196502121990031004', 'tugas' => 'Kepala Dinas / Peserta Utama', 'nomor_sppd' => '090/019/IX/2026'],
                    ['nip' => '198104102006041002', 'tugas' => 'Pendamping Teknis Telekomunikasi', 'nomor_sppd' => '090/020/IX/2026'],
                ]
            ],
            [
                'nomor_surat' => '555/015/DISKOMINFO-BB/SPT/IX/2026',
                'jenis_surat_id' => 2,
                'has_sppd' => true,
                'tgl_surat' => '2026-09-16',
                'perihal' => 'Peliputan dan Siaran Langsung (Live Streaming) Rapat Paripurna Istimewa DPRD Bone Bolango',
                'tujuan' => 'Gedung DPRD Kabupaten Bone Bolango',
                'uraian' => 'Menyelenggarakan penyiaran live streaming YouTube Bone Bolango TV, peliputan foto, serta siaran pers media atas agenda paripurna dewan.',
                'keterangan' => 'Publikasi resmi transparansi agenda pemerintahan daerah.',
                'created_by' => $adminId,
                'personil' => [
                    ['nip' => '199012012015022002', 'tugas' => 'Produser Siaran Langsung', 'nomor_sppd' => '090/021/IX/2026'],
                    ['nip' => '199505142019031005', 'tugas' => 'Kameramen & Editor Berita', 'nomor_sppd' => '090/022/IX/2026'],
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
