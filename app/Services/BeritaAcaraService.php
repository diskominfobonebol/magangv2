<?php

namespace App\Services;

use App\Models\BeritaAcara;
use App\Models\AsetPeralatanMesin;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class BeritaAcaraService
{
    /**
     * Konversi bilangan bulat (1 - 9999) ke teks terbilang bahasa Indonesia (Title Case)
     */
    public static function terbilang(int $angka): string
    {
        $satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return $satuan[$angka];
        } elseif ($angka < 20) {
            return self::terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $hasil = self::terbilang((int)($angka / 10)) . ' Puluh';
            if ($angka % 10 > 0) {
                $hasil .= ' ' . self::terbilang($angka % 10);
            }
            return $hasil;
        } elseif ($angka < 200) {
            return 'Seratus' . ($angka - 100 > 0 ? ' ' . self::terbilang($angka - 100) : '');
        } elseif ($angka < 1000) {
            $hasil = self::terbilang((int)($angka / 100)) . ' Ratus';
            if ($angka % 100 > 0) {
                $hasil .= ' ' . self::terbilang($angka % 100);
            }
            return $hasil;
        } elseif ($angka < 2000) {
            return 'Seribu' . ($angka - 1000 > 0 ? ' ' . self::terbilang($angka - 1000) : '');
        } elseif ($angka < 10000) {
            $hasil = self::terbilang((int)($angka / 1000)) . ' Ribu';
            if ($angka % 1000 > 0) {
                $hasil .= ' ' . self::terbilang($angka % 1000);
            }
            return $hasil;
        }

        return (string)$angka;
    }

    /**
     * Dapatkan nama hari dalam bahasa Indonesia dari string tanggal (Y-m-d)
     */
    public static function namaHari(string $dateString): string
    {
        $days = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $eng = date('l', strtotime($dateString));
        return $days[$eng] ?? $eng;
    }

    /**
     * Dapatkan nama bulan dalam bahasa Indonesia dari string tanggal (Y-m-d)
     */
    public static function namaBulan(string $dateString): string
    {
        $months = [
            1  => 'Januari',   2  => 'Februari', 3  => 'Maret',
            4  => 'April',     5  => 'Mei',      6  => 'Juni',
            7  => 'Juli',      8  => 'Agustus',  9  => 'September',
            10 => 'Oktober',   11 => 'November', 12 => 'Desember',
        ];
        $m = (int)date('n', strtotime($dateString));
        return $months[$m] ?? date('F', strtotime($dateString));
    }

    /**
     * Klausul baku standar BAST Dinas Komunikasi dan Informatika
     */
    public static function getKlausulStandar(?AsetPeralatanMesin $aset = null): array
    {
        $merekTipe = ($aset && !empty($aset->merek_tipe)) ? $aset->merek_tipe : '-';

        return [
            'Pertama' => 'Barang tersebut di atas dipergunakan untuk kelancaran pelaksanaan tugas kedinasan / operasional dinas.',
            'Kedua'   => 'PIHAK KEDUA berkewajiban merawat, memelihara, dan menjaga keutuhan serta keamanan barang tersebut dengan sebaik-baiknya.',
            'Ketiga'  => 'Segala biaya pemeliharaan, operasional, dan perbaikan ringan selama masa penggunaan menjadi tanggung jawab PIHAK KEDUA.',
            'Keempat' => 'Barang tersebut tidak dibenarkan untuk dipindahtangankan, dipinjamkan, atau dialihkan kepada pihak lain tanpa persetujuan tertulis dari Pimpinan/Kepala Dinas.',
            'Kelima'  => 'Apabila terjadi kerusakan berat atau kehilangan akibat kelalaian dalam penggunaan, maka PIHAK KEDUA bersedia bertanggung jawab sesuai dengan peraturan perundang-undangan yang berlaku.',
            'Keenam'  => 'Barang tersebut sewaktu-waktu dapat ditarik kembali oleh Dinas Komunikasi dan Informatika Kabupaten Bone Bolango apabila dinilai perlu untuk kepentingan kedinasan atau mutasi jabatan.',
            'Ketujuh' => 'Spesifikasi Unit dimaksud adalah berikut: Merek/Type: ' . $merekTipe . '.',
        ];
    }

    /**
     * Persiapkan data terstruktur untuk dokumen template BAST
     */
    public static function prepareData(BeritaAcara $ba): array
    {
        $aset = $ba->aset;
        $tglStr = $ba->tanggal ? $ba->tanggal->format('Y-m-d') : date('Y-m-d');
        $dayNum = (int)date('j', strtotime($tglStr));
        $yearNum = (int)date('Y', strtotime($tglStr));

        // Format nama barang singkat untuk kalimat penyerahan
        $prefix = '1 Unit';
        if (!empty($ba->jumlah_barang)) {
            if (preg_match('/^(\d+)\s*(?:\([^)]+\)\s*)?(unit|buah|set|paket)?/i', trim($ba->jumlah_barang), $qtyMatches)) {
                $prefix = $qtyMatches[1] . ' ' . (!empty($qtyMatches[2]) ? ucfirst(strtolower($qtyMatches[2])) : 'Unit');
            }
        }

        if ($aset) {
            $merekWords = preg_split('/\s+/', trim($aset->merek_tipe ?? ''));
            $firstMerekWord = $merekWords[0] ?? '';
            $namaBarangSingkat = $aset->jenis_barang ?? 'Barang';
            if (!empty($firstMerekWord) && stripos($namaBarangSingkat, $firstMerekWord) === false) {
                $namaBarangSingkat .= ' ' . $firstMerekWord;
            }
            $barangPenyerahan = $prefix . ' ' . $namaBarangSingkat;
        } elseif (!empty($ba->jumlah_barang)) {
            $barangPenyerahan = str_ireplace('1 (satu) unit', '1 Unit', $ba->jumlah_barang);
        } else {
            $barangPenyerahan = '1 Unit Barang';
        }

        return [
            'ba'                 => $ba,
            'aset'               => $aset,
            'nomor_surat'        => $ba->nomor_surat ?: '555/KOMINFO-BB/BEN-BAR/BAP/' . date('Y'),
            'hari'               => self::namaHari($tglStr),
            'tanggal_angka'      => $dayNum,
            'tanggal_terbilang'  => self::terbilang($dayNum),
            'bulan'              => self::namaBulan($tglStr),
            'tahun_angka'        => $yearNum,
            'tahun_terbilang'    => self::terbilang($yearNum),
            'tanggal_formatted'  => date('d', strtotime($tglStr)) . ' ' . self::namaBulan($tglStr) . ' ' . $yearNum,
            'tempat'             => $ba->tempat_tanda_tangan ?: 'Suwawa',
            'pihak_pertama'      => [
                'nama'    => $ba->pihak_pertama_nama ?: '-',
                'nip'     => $ba->pihak_pertama_nip ?: '-',
                'jabatan' => $ba->pihak_pertama_jabatan ?: 'Pengurus Barang / Pengelola Aset',
            ],
            'pihak_kedua'        => [
                'nama'    => $ba->pihak_kedua_nama ?: ($aset->penanggung_jawab ?? '-'),
                'nip'     => $ba->pihak_kedua_nip ?: '-',
                'jabatan' => $ba->pihak_kedua_jabatan ?: 'Penerima / Pemakai Barang',
            ],
            'mengetahui'         => [
                'nama'    => $ba->mengetahui_nama ?: '-',
                'nip'     => $ba->mengetahui_nip ?: '-',
                'jabatan' => $ba->mengetahui_jabatan ?: 'Plt. Kepala Dinas',
            ],
            'jumlah_barang'      => $ba->jumlah_barang ?: ($aset ? '1 (Satu) Unit ' . $aset->jenis_barang : '1 Unit Barang'),
            'barang_penyerahan'  => $barangPenyerahan,
            'kondisi'            => $aset->kondisi ?? 'Baik',
            'keperluan'          => $ba->keperluan ?: 'pekerjaan',
            'klausul'            => self::getKlausulStandar($aset),
            'paragraf_penutup'   => 'Demikian berita acara ini dibuat untuk digunakan seperlunya.',
        ];
    }

    /**
     * Generate file PDF Berita Acara dan simpan ke public storage
     */
    public function generatePdf(BeritaAcara $ba): string
    {
        $data = self::prepareData($ba);

        // Pastikan folder berita_acara ada
        Storage::disk('public')->makeDirectory('berita_acara');

        $fileName = 'berita_acara/BAST_' . date('Ymd_His') . '_' . $ba->id . '.pdf';
        $fullPath = Storage::disk('public')->path($fileName);

        $pdf = Pdf::loadView('admin.berita_acara_pdf', $data)
            ->setPaper([0, 0, 612.00, 935.43], 'portrait') // Ukuran Kertas F4 / Folio (21.59 x 33.0 cm)
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        $pdf->save($fullPath);

        return $fileName;
    }
}
