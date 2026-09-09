<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;

class PegawaiSeeder extends Seeder
{
    public function run()
    {
        $pegawai = [
            // Pimpinan Tertinggi (Kepala Dinas)
            ['nip' => '196502121990031004', 'nama' => 'Drs. H. Syamsuddin, M.Si', 'pangkat_golongan' => 'Pembina Utama Madya / IV/d', 'jabatan' => 'Kepala Dinas Komunikasi dan Informatika', 'no_wa' => '081234567800'],
            
            // Sekretariat & Pimpinan Bidang / Subkor
            ['nip' => '197501011998031001', 'nama' => 'Drs. H. Mansur, M.Si', 'pangkat_golongan' => 'Pembina Utama Muda / IV/c', 'jabatan' => 'Sekretaris Dinas', 'no_wa' => '081234567890'],
            ['nip' => '198205122005012002', 'nama' => 'Sri Wahyuni, S.AP', 'pangkat_golongan' => 'Pembina / IV/a', 'jabatan' => 'Kasubag Kepegawaian', 'no_wa' => '081234567891'],
            ['nip' => '198503152010011003', 'nama' => 'Rahmat Hidayat, SE', 'pangkat_golongan' => 'Penata Tk. I / III/d', 'jabatan' => 'Kasubag Keuangan', 'no_wa' => '081234567892'],
            
            // Staff Kepegawaian (3 orang)
            ['nip' => '199004102015022001', 'nama' => 'Dewi Lestari, S.Kom', 'pangkat_golongan' => 'Penata / III/c', 'jabatan' => 'Staff Pengelola Kepegawaian', 'no_wa' => '081234567893'],
            ['nip' => '199208222018011004', 'nama' => 'Fadli Pratama, A.Md', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Administrasi Kepegawaian', 'no_wa' => '081234567894'],
            ['nip' => '199512052020121005', 'nama' => 'Siti Aminah, S.Sos', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Layanan Kepegawaian', 'no_wa' => '081234567895'],

            // Staff Keuangan (4 orang)
            ['nip' => '198807142011012003', 'nama' => 'Nurul Hidayah, SE', 'pangkat_golongan' => 'Penata / III/c', 'jabatan' => 'Staff Verifikasi Keuangan', 'no_wa' => '081234567896'],
            ['nip' => '199102282015021002', 'nama' => 'Arief Rahman, Ak', 'pangkat_golongan' => 'Penata Muda Tk. I / III/b', 'jabatan' => 'Staff Pembuat Daftar Gaji', 'no_wa' => '081234567897'],
            ['nip' => '199306192019031006', 'nama' => 'Irfan Maulana, SE', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Akuntansi & Pelaporan', 'no_wa' => '081234567898'],
            ['nip' => '199609092022032002', 'nama' => 'Putri Wulandari, A.Md', 'pangkat_golongan' => 'Pengatur / II/c', 'jabatan' => 'Staff Administrasi Keuangan', 'no_wa' => '081234567899'],

            // Bidang Informatika & 4 Staffnya (Termasuk Admin Master di dalamnya)
            ['nip' => '197911202005011004', 'nama' => 'Dr. Ir. H. Zulkifli, M.T', 'pangkat_golongan' => 'Pembina Tk. I / IV/b', 'jabatan' => 'Kepala Bidang Informatika', 'no_wa' => '081234567800'],
            ['nip' => '198905052012121001', 'nama' => 'Mohammad Rizki, S.Kom', 'pangkat_golongan' => 'Penata Tk. I / III/d', 'jabatan' => 'Pranata Komputer Ahli Muda (Admin Master)', 'no_wa' => '081234567801'],
            ['nip' => '199410122019032001', 'nama' => 'Fitriani Ningsih, S.T', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Infrastruktur & Jaringan', 'no_wa' => '081234567802'],
            ['nip' => '199701152022031003', 'nama' => 'Reza Pahlevi, S.Kom', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Pengembang Aplikasi', 'no_wa' => '081234567803'],
            ['nip' => '199804202022032004', 'nama' => 'Amalia Zahra, S.Tr.Kom', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Layanan e-Government', 'no_wa' => '081234567804'],

            // Subkor Pengawasan TIK & Saspras + 3 Staff
            ['nip' => '198104102006041002', 'nama' => 'Ir. Hendra Gunawan, M.T', 'pangkat_golongan' => 'Pembina / IV/a', 'jabatan' => 'Subkor Pengawasan TIK & Saspras / Penguji Perangkat Telekomunikasi', 'no_wa' => '081234567810'],
            ['nip' => '199111052015021003', 'nama' => 'Dedy Pratama, S.T', 'pangkat_golongan' => 'Penata Muda Tk. I / III/b', 'jabatan' => 'Staff Pengujian Perangkat Telekomunikasi', 'no_wa' => '081234567811'],
            ['nip' => '199403222018012002', 'nama' => 'Rina Melati, S.Kom', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Pengawasan TIK', 'no_wa' => '081234567812'],
            ['nip' => '199607182020121004', 'nama' => 'Agung Prasetyo, A.Md', 'pangkat_golongan' => 'Pengatur Tk. I / II/d', 'jabatan' => 'Staff Sarana & Prasarana TIK', 'no_wa' => '081234567813'],

            // Bidang Komunikasi + 2 Staff
            ['nip' => '197709052002121001', 'nama' => 'Drs. H. M. Alwi, M.I.Kom', 'pangkat_golongan' => 'Pembina Tk. I / IV/b', 'jabatan' => 'Kepala Bidang Komunikasi', 'no_wa' => '081234567820'],
            ['nip' => '199012012015022002', 'nama' => 'Nurfadilah, S.I.Kom', 'pangkat_golongan' => 'Penata Tk. I / III/d', 'jabatan' => 'Staff Kemitraan & Komunikasi Publik', 'no_wa' => '081234567821'],
            ['nip' => '199505142019031005', 'nama' => 'Yusuf Bahtiar, S.Sos', 'pangkat_golongan' => 'Penata Muda / III/a', 'jabatan' => 'Staff Layanan Informasi & Media', 'no_wa' => '081234567822'],
        ];

        foreach ($pegawai as $data) {
            Pegawai::create($data);
        }
    }
}