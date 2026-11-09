<?php

namespace App\Services;

use Illuminate\Support\Collection;

class LaporanAsetService
{
    /**
     * Hitung rekap kuantitas dan persentase kondisi aset.
     * Menggunakan Largest Remainder Method (Hare-Niemeyer) untuk menjamin total persentase tepat 100,0%.
     *
     * @param Collection $asetPeriode Seluruh data aset pada periode aktif (tanpa filter kondisi)
     * @return array [
     *    'counts'      => ['Baik' => 10, ...],
     *    'percentages' => ['Baik' => 50.0, ...],
     *    'formatted'   => ['Baik' => '50,0%', ...],
     *    'total'       => 20,
     *    'is_empty'    => false,
     * ]
     */
    public function calculateRekap(Collection $asetPeriode): array
    {
        $keys = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'];
        $total = $asetPeriode->count();

        $counts = [
            'Baik'         => $asetPeriode->where('kondisi', 'Baik')->count(),
            'Rusak Ringan' => $asetPeriode->where('kondisi', 'Rusak Ringan')->count(),
            'Rusak Berat'  => $asetPeriode->where('kondisi', 'Rusak Berat')->count(),
            'Hilang'       => $asetPeriode->where('kondisi', 'Hilang')->count(),
        ];

        if ($total === 0) {
            $percentages = array_fill_keys($keys, 0.0);
            $formatted   = array_fill_keys($keys, '0,0%');

            return [
                'counts'      => $counts,
                'percentages' => $percentages,
                'formatted'   => $formatted,
                'total'       => 0,
                'is_empty'    => true,
            ];
        }

        // Largest Remainder Method untuk pembulatan 1 desimal (basis 1000 per mil)
        $targetTotalTenths = 1000; // 100.0% = 1000 tenths
        $integerTenths = [];
        $remainders = [];

        foreach ($keys as $k) {
            $exactTenths = ($counts[$k] / $total) * 1000;
            $intPart = (int)floor($exactTenths);
            $integerTenths[$k] = $intPart;
            $remainders[$k]    = $exactTenths - $intPart;
        }

        $allocatedTenths = array_sum($integerTenths);
        $deficit = $targetTotalTenths - $allocatedTenths;

        // Urutkan sisa desimal terbesar ke terkecil
        arsort($remainders);

        foreach (array_keys($remainders) as $k) {
            if ($deficit <= 0) {
                break;
            }
            $integerTenths[$k]++;
            $deficit--;
        }

        $percentages = [];
        $formatted   = [];
        foreach ($keys as $k) {
            $val = $integerTenths[$k] / 10;
            $percentages[$k] = $val;
            $formatted[$k]   = number_format($val, 1, ',', '.') . '%';
        }

        return [
            'counts'      => $counts,
            'percentages' => $percentages,
            'formatted'   => $formatted,
            'total'       => $total,
            'is_empty'    => false,
        ];
    }

    /**
     * Render grafik Donat Kondisi Aset sebagai gambar PNG resolusi tinggi via GD
     * Mengembalikan Data URI (data:image/png;base64,...) yang 100% kompatibel dengan DomPDF.
     * Tidak membutuhkan JavaScript atau koneksi internet eksternal.
     */
    public function renderDonutChartPng(array $rekap, int $width = 460, int $height = 240): string
    {
        $counts      = $rekap['counts'] ?? [];
        $percentages = $rekap['percentages'] ?? [];
        $total       = $rekap['total'] ?? 0;
        $isEmpty     = $rekap['is_empty'] ?? ($total === 0);

        // Skala 2x (supersampling) untuk hasil anti-aliasing yang sangat tajam di PDF
        $scale = 2;
        $w = $width * $scale;
        $h = $height * $scale;

        $img = imagecreatetruecolor($w, $h);

        // Background putih bersih
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        // Definisi Geometri Donat
        $centerX = (int)($w * 0.32);
        $centerY = (int)($h * 0.50);
        $outerDiameter = (int)($h * 0.78);
        $innerDiameter = (int)($outerDiameter * 0.58);

        $colorConfig = config('aset.kondisi_colors', []);

        if ($isEmpty) {
            // Tampilan jika tidak ada data
            $gray = imagecolorallocate($img, 226, 232, 240);
            imagefilledellipse($img, $centerX, $centerY, $outerDiameter, $outerDiameter, $gray);
            imagefilledellipse($img, $centerX, $centerY, $innerDiameter, $innerDiameter, $white);

            // Teks "Tidak ada data"
            $textDark = imagecolorallocate($img, 100, 116, 139);
            $this->drawCenteredText($img, 'Tidak ada data', $centerX, $centerY, 4 * $scale, $textDark);
        } else {
            // Gambar irisan donat
            $startAngle = -90; // Mulai dari jam 12 (atas)

            $sliceKeys = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'];
            foreach ($sliceKeys as $k) {
                $count = $counts[$k] ?? 0;
                if ($count <= 0) {
                    continue;
                }

                $sweep = ($count / $total) * 360;
                $endAngle = $startAngle + $sweep;

                $hex = $colorConfig[$k]['color'] ?? '#3B82F6';
                $rgb = $this->hexToRgb($hex);
                $gdColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);

                // GD imagefilledarc membutuhkan sudut 0-360
                $arcStart = (int)round($this->normalizeAngle($startAngle));
                $arcEnd   = (int)round($this->normalizeAngle($endAngle));

                if ($arcStart === $arcEnd) {
                    // Kasus 100% lingkaran penuh satu kondisi
                    imagefilledellipse($img, $centerX, $centerY, $outerDiameter, $outerDiameter, $gdColor);
                } else {
                    imagefilledarc(
                        $img,
                        $centerX,
                        $centerY,
                        $outerDiameter,
                        $outerDiameter,
                        $arcStart,
                        $arcEnd,
                        $gdColor,
                        IMG_ARC_PIE
                    );
                }

                $startAngle = $endAngle;
            }

            // Lubang donat (lingkaran dalam putih)
            imagefilledellipse($img, $centerX, $centerY, $innerDiameter, $innerDiameter, $white);

            // Teks tengah: Total Aset
            $textNavy = imagecolorallocate($img, 23, 37, 84);
            $textMuted = imagecolorallocate($img, 100, 116, 139);

            $this->drawCenteredText($img, 'TOTAL', $centerX, $centerY - (12 * $scale), 3 * $scale, $textMuted);
            $this->drawCenteredText($img, (string)$total, $centerX, $centerY + (10 * $scale), 5 * $scale, $textNavy);
        }

        // Gambar Legenda di Sisi Kanan Donat
        $legendX = (int)($w * 0.58);
        $legendStartY = (int)($h * 0.20);
        $legendSpacing = (int)($h * 0.16);

        $legendItems = [
            'Baik'         => 'Baik',
            'Rusak Ringan' => 'Rusak Ringan',
            'Rusak Berat'  => 'Rusak Berat',
            'Hilang'       => 'Belum Ditemukan Keberadaannya',
        ];

        $boxSize = 10 * $scale;
        $idx = 0;
        foreach ($legendItems as $key => $labelPdf) {
            $y = $legendStartY + ($idx * $legendSpacing);

            $hex = $colorConfig[$key]['color'] ?? '#64748B';
            $rgb = $this->hexToRgb($hex);
            $boxColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);

            // Kotak indikator warna
            imagefilledrectangle($img, $legendX, $y, $legendX + $boxSize, $y + $boxSize, $boxColor);

            // Teks legenda & persentase
            $textColor = imagecolorallocate($img, 23, 37, 84);
            $pctStr = ($rekap['formatted'][$key] ?? '0,0%');
            $line = "{$labelPdf} ({$pctStr})";

            imagestring($img, 3 * $scale, $legendX + $boxSize + (8 * $scale), $y - (1 * $scale), $line, $textColor);

            $idx++;
        }

        // Downscale kembali ke ukuran target ($width x $height) untuk hasil super halus
        $finalImg = imagecreatetruecolor($width, $height);
        $finalWhite = imagecolorallocate($finalImg, 255, 255, 255);
        imagefilledrectangle($finalImg, 0, 0, $width, $height, $finalWhite);

        imagecopyresampled($finalImg, $img, 0, 0, 0, 0, $width, $height, $w, $h);

        ob_start();
        imagepng($finalImg);
        $pngData = ob_get_clean();

        imagedestroy($img);
        imagedestroy($finalImg);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    /**
     * Konversi Hex color ke array RGB [R, G, B]
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Normalisasi sudut ke rentang 0 - 360 derajat
     */
    protected function normalizeAngle(float $angle): float
    {
        $mod = fmod($angle, 360);
        return $mod < 0 ? $mod + 360 : $mod;
    }

    /**
     * Gambar teks rata tengah horizontal dan vertikal sederhana via GD font built-in
     */
    protected function drawCenteredText($img, string $text, int $cx, int $cy, int $font, int $color): void
    {
        $font = min(5, max(1, $font));
        $fw = imagefontwidth($font);
        $fh = imagefontheight($font);

        $x = (int)($cx - (strlen($text) * $fw / 2));
        $y = (int)($cy - ($fh / 2));

        imagestring($img, $font, $x, $y, $text, $color);
    }
}
