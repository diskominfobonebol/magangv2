<?php

namespace Database\Seeders;

use App\Models\AsetPeralatanMesin;
use Illuminate\Database\Seeder;

class AsetPeralatanMesinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 20 data dummy Aset Peralatan & Mesin — kondisi campuran:
     *   8 Baik | 5 Rusak Ringan | 4 Rusak Berat | 3 Hilang
     */
    public function run(): void
    {
        $assets = [

            // === BAIK (8) === Standar 80%–100% (default 100%)
            ['no_reg_pemda'=>'REG-2026-001','no_reg_kominfo'=>'KOMINFO-2026-0101','penanggung_jawab'=>'Rizal Pratama, S.Kom','jenis_barang'=>'Laptop','merek_tipe'=>'Lenovo ThinkPad X1 Carbon Gen 11','tahun'=>2026,'harga_perolehan'=>32000000,'no_rangka_seri'=>'SN-LNV-X1-2026-00191','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>100,'keterangan_lokasi_unit'=>'Bidang Pengelolaan E-Government, Lantai 2','no_sk_bast'=>'SK/TI/001/DISKOMINFO/2026'],
            ['no_reg_pemda'=>'REG-2026-002','no_reg_kominfo'=>'KOMINFO-2026-0102','penanggung_jawab'=>'Fikram Nur, M.T.','jenis_barang'=>'Router','merek_tipe'=>'MikroTik CCR2216-1G-12XS-2XQ','tahun'=>2025,'harga_perolehan'=>42000000,'no_rangka_seri'=>'MT-CCR2216-2025-99012','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>95,'keterangan_lokasi_unit'=>'Ruang Server NOC, Gedung Diskominfo','no_sk_bast'=>'SK/NET/002/DISKOMINFO/2025'],
            ['no_reg_pemda'=>'REG-2026-003','no_reg_kominfo'=>null,'penanggung_jawab'=>'Drs. H. Mulyadi R.','jenis_barang'=>'Mobil Dinas','merek_tipe'=>'Toyota Innova Zenix 2.0 V CVT','tahun'=>2025,'harga_perolehan'=>495000000,'no_rangka_seri'=>'MHFXA31E7P2098234','no_mesin'=>'M20A-FKS-99123','no_polisi'=>'DM 3723 AB','no_bpkb'=>'BPKB-G-9919231','kondisi'=>'Baik','persentase_kondisi'=>90,'keterangan_lokasi_unit'=>'Sekretariat Diskominfo, Garasi Dinas','no_sk_bast'=>'BAST/KND/003/DISKOMINFO/2025'],
            ['no_reg_pemda'=>'REG-2026-004','no_reg_kominfo'=>'KOMINFO-2026-0104','penanggung_jawab'=>'Siti Rahmawati, S.AP','jenis_barang'=>'Proyektor','merek_tipe'=>'Epson EB-FH52 4000 Lumens','tahun'=>2024,'harga_perolehan'=>18500000,'no_rangka_seri'=>'EPS-FH52-2024-00912','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>85,'keterangan_lokasi_unit'=>'Ruang Rapat Utama, Lantai 1 Diskominfo','no_sk_bast'=>'BAST/AV/004/DISKOMINFO/2024'],
            ['no_reg_pemda'=>'REG-2026-005','no_reg_kominfo'=>null,'penanggung_jawab'=>'Ir. Hendra Wijaya','jenis_barang'=>'Server','merek_tipe'=>'Dell PowerEdge R750 2U Rack Server','tahun'=>2024,'harga_perolehan'=>175000000,'no_rangka_seri'=>'DELL-R750-2024-110045','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>100,'keterangan_lokasi_unit'=>'Data Center Utama Diskominfo Bone Bolango','no_sk_bast'=>'SK/SRV/005/DISKOMINFO/2024'],
            ['no_reg_pemda'=>'REG-2026-006','no_reg_kominfo'=>'KOMINFO-2026-0106','penanggung_jawab'=>'Nurlaila Isa, A.Md','jenis_barang'=>'Printer','merek_tipe'=>'HP LaserJet Pro M404dn','tahun'=>2024,'harga_perolehan'=>6800000,'no_rangka_seri'=>'HP-M404DN-2024-44123','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>92,'keterangan_lokasi_unit'=>'Subbagian Umum & Kepegawaian, Lantai 1','no_sk_bast'=>'BAST/PRN/006/DISKOMINFO/2024'],
            ['no_reg_pemda'=>'REG-2026-007','no_reg_kominfo'=>null,'penanggung_jawab'=>'Bayu Krisna, S.I.Kom','jenis_barang'=>'Kamera','merek_tipe'=>'Canon EOS R6 Mark II Mirrorless','tahun'=>2025,'harga_perolehan'=>41500000,'no_rangka_seri'=>'CANON-R6M2-2025-550291','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>88,'keterangan_lokasi_unit'=>'Bidang Informasi & Komunikasi Publik (IKP)','no_sk_bast'=>'BAST/IKP/007/DISKOMINFO/2025'],
            ['no_reg_pemda'=>'REG-2026-008','no_reg_kominfo'=>'KOMINFO-2026-0108','penanggung_jawab'=>'Rizal Pratama, S.Kom','jenis_barang'=>'AC Split','merek_tipe'=>'Daikin Inverter FTKF35EVM 1.5 PK','tahun'=>2025,'harga_perolehan'=>9500000,'no_rangka_seri'=>'DKN-FTKF35-2025-00821','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Baik','persentase_kondisi'=>98,'keterangan_lokasi_unit'=>'Ruang Command Center, Lantai 3 Diskominfo','no_sk_bast'=>'BAST/AHU/008/DISKOMINFO/2025'],

            // === RUSAK RINGAN (5) === Standar 50%–79% (default 65%)
            ['no_reg_pemda'=>'REG-2026-009','no_reg_kominfo'=>'KOMINFO-2026-0109','penanggung_jawab'=>'Aditya Firmansyah','jenis_barang'=>'Laptop','merek_tipe'=>'Acer Aspire 5 A515-56G Intel Core i5','tahun'=>2022,'harga_perolehan'=>12500000,'no_rangka_seri'=>'ACR-A515-2022-88204','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Ringan','persentase_kondisi'=>70,'keterangan_lokasi_unit'=>'Subbagian Tata Usaha (layar retak, masih berfungsi)','no_sk_bast'=>'BAST/TI/009/DISKOMINFO/2022'],
            ['no_reg_pemda'=>'REG-2026-010','no_reg_kominfo'=>null,'penanggung_jawab'=>'Siti Rahmawati, S.AP','jenis_barang'=>'Printer','merek_tipe'=>'Canon PIXMA G2010 Ink Tank','tahun'=>2021,'harga_perolehan'=>3200000,'no_rangka_seri'=>'CNPX-G2010-2021-10294','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Ringan','persentase_kondisi'=>65,'keterangan_lokasi_unit'=>'Ruang Rapat Lantai 1 (paper-jam intermiten)','no_sk_bast'=>'BAST/PRN/010/DISKOMINFO/2021'],
            ['no_reg_pemda'=>'REG-2026-011','no_reg_kominfo'=>'KOMINFO-2026-0111','penanggung_jawab'=>'Ir. Hendra Wijaya','jenis_barang'=>'Switch Manageable','merek_tipe'=>'Cisco Catalyst 2960-X 24-Port','tahun'=>2020,'harga_perolehan'=>28000000,'no_rangka_seri'=>'CSC-C2960X-2020-77130','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Ringan','persentase_kondisi'=>60,'keterangan_lokasi_unit'=>'Ruang Server NOC (2 port mati, sisanya normal)','no_sk_bast'=>'SK/NET/011/DISKOMINFO/2020'],
            ['no_reg_pemda'=>'REG-2026-012','no_reg_kominfo'=>null,'penanggung_jawab'=>'Aditya Firmansyah','jenis_barang'=>'Mesin Penghancur Kertas','merek_tipe'=>'Ideal 2604 Cross Cut Heavy-Duty','tahun'=>2021,'harga_perolehan'=>18900000,'no_rangka_seri'=>'IDL-2604-2021-00392','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Ringan','persentase_kondisi'=>75,'keterangan_lokasi_unit'=>'Subbagian Tata Usaha & Kearsipan (pisau tumpul)','no_sk_bast'=>'BAST/MK/012/DISKOMINFO/2021'],
            ['no_reg_pemda'=>'REG-2026-013','no_reg_kominfo'=>'KOMINFO-2026-0113','penanggung_jawab'=>'Nurlaila Isa, A.Md','jenis_barang'=>'UPS','merek_tipe'=>'APC Back-UPS Pro 1500VA BR1500G','tahun'=>2022,'harga_perolehan'=>7500000,'no_rangka_seri'=>'APC-BR1500-2022-33091','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Ringan','persentase_kondisi'=>55,'keterangan_lokasi_unit'=>'Ruang Kepala Bidang (baterai melemah)','no_sk_bast'=>'BAST/EL/013/DISKOMINFO/2022'],

            // === RUSAK BERAT (4) === Standar 1%–49% (default 25%)
            ['no_reg_pemda'=>'REG-2026-014','no_reg_kominfo'=>null,'penanggung_jawab'=>'Rizal Pratama, S.Kom','jenis_barang'=>'Laptop','merek_tipe'=>'Dell Latitude 5480 Intel Core i7','tahun'=>2019,'harga_perolehan'=>18000000,'no_rangka_seri'=>'DELL-L5480-2019-50182','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Berat','persentase_kondisi'=>20,'keterangan_lokasi_unit'=>'Gudang Penyimpanan Aset Tidak Layak Pakai','no_sk_bast'=>'BAST/TI/014/DISKOMINFO/2019'],
            ['no_reg_pemda'=>'REG-2026-015','no_reg_kominfo'=>'KOMINFO-2026-0115','penanggung_jawab'=>'Drs. H. Mulyadi R.','jenis_barang'=>'Mobil Dinas','merek_tipe'=>'Daihatsu Gran Max Pick Up 1.5 D','tahun'=>2017,'harga_perolehan'=>145000000,'no_rangka_seri'=>'MHKV5EA1J7K119283','no_mesin'=>'DE-VR-882901','no_polisi'=>'DM 8800 ZZ','no_bpkb'=>'BPKB-D-007123','kondisi'=>'Rusak Berat','persentase_kondisi'=>35,'keterangan_lokasi_unit'=>'Bengkel Dinas Kabupaten (perbaikan besar)','no_sk_bast'=>'BAST/KND/015/DISKOMINFO/2017'],
            ['no_reg_pemda'=>'REG-2026-016','no_reg_kominfo'=>null,'penanggung_jawab'=>'Fikram Nur, M.T.','jenis_barang'=>'Server','merek_tipe'=>'HP ProLiant DL180 Gen 9 (End-of-Life)','tahun'=>2018,'harga_perolehan'=>98000000,'no_rangka_seri'=>'HP-DL180G9-2018-28819','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Berat','persentase_kondisi'=>15,'keterangan_lokasi_unit'=>'Gudang Penyimpanan Aset Tidak Layak Pakai','no_sk_bast'=>'SK/SRV/016/DISKOMINFO/2018'],
            ['no_reg_pemda'=>'REG-2026-017','no_reg_kominfo'=>'KOMINFO-2026-0117','penanggung_jawab'=>'Siti Rahmawati, S.AP','jenis_barang'=>'Proyektor','merek_tipe'=>'BenQ MX503 XGA DLP Projector','tahun'=>2016,'harga_perolehan'=>9800000,'no_rangka_seri'=>'BNQ-MX503-2016-10192','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Rusak Berat','persentase_kondisi'=>40,'keterangan_lokasi_unit'=>'Gudang Penyimpanan Aset Tidak Layak Pakai','no_sk_bast'=>'BAST/AV/017/DISKOMINFO/2016'],

            // === HILANG (3) === Standar 0% (terkunci 0)
            ['no_reg_pemda'=>'REG-2026-018','no_reg_kominfo'=>null,'penanggung_jawab'=>'Bayu Krisna, S.I.Kom','jenis_barang'=>'Modem','merek_tipe'=>'Huawei B525s-23a 4G LTE Router','tahun'=>2022,'harga_perolehan'=>3800000,'no_rangka_seri'=>'HW-B525-2022-55012','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Hilang','persentase_kondisi'=>0,'keterangan_lokasi_unit'=>'Tidak diketahui (hilang saat peminjaman luar kantor 2023)','no_sk_bast'=>'BAST/NET/018/DISKOMINFO/2022'],
            ['no_reg_pemda'=>'REG-2026-019','no_reg_kominfo'=>'KOMINFO-2026-0119','penanggung_jawab'=>'Aditya Firmansyah','jenis_barang'=>'Laptop','merek_tipe'=>'Asus VivoBook S14 S430FA Intel i5','tahun'=>2021,'harga_perolehan'=>10500000,'no_rangka_seri'=>'ASUS-S430FA-2021-77293','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Hilang','persentase_kondisi'=>0,'keterangan_lokasi_unit'=>'Tidak diketahui (dilaporkan hilang Maret 2024)','no_sk_bast'=>'BAST/TI/019/DISKOMINFO/2021'],
            ['no_reg_pemda'=>'REG-2026-020','no_reg_kominfo'=>null,'penanggung_jawab'=>'Nurlaila Isa, A.Md','jenis_barang'=>'Sound System','merek_tipe'=>'Bose S1 Pro System Portable Speaker','tahun'=>2023,'harga_perolehan'=>13800000,'no_rangka_seri'=>'BOSE-S1P-2023-91204','no_mesin'=>null,'no_polisi'=>null,'no_bpkb'=>null,'kondisi'=>'Hilang','persentase_kondisi'=>0,'keterangan_lokasi_unit'=>'Tidak diketahui (dalam pelacakan Tim Pengelola Aset)','no_sk_bast'=>'SK/SND/020/DISKOMINFO/2023'],
        ];

        $inserted = 0;
        $skipped  = 0;

        foreach ($assets as $assetData) {
            $existing = AsetPeralatanMesin::where('no_reg_pemda', $assetData['no_reg_pemda'])->first();
            if ($existing) {
                $existing->update(['persentase_kondisi' => $assetData['persentase_kondisi']]);
                $skipped++;
                continue;
            }
            AsetPeralatanMesin::create($assetData);
            $inserted++;
        }

        $this->command->info("");
        $this->command->info("  ✓ Seeder selesai: {$inserted} data dimasukkan, {$skipped} dilewati (sudah ada).");
        $this->command->table(
            ['Kondisi', 'Jumlah'],
            [
                ['Baik',         8],
                ['Rusak Ringan', 5],
                ['Rusak Berat',  4],
                ['Hilang',       3],
                ['Total',       20],
            ]
        );
    }
}