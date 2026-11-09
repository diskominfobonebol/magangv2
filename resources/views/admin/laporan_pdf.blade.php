<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Kondisi Barang KOMTARA - Diskominfo</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 10mm 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #172554;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-title {
            text-align: center;
            margin: 10px 0 12px 0;
        }
        .header-title h3 {
            margin: 0;
            font-size: 13px;
            color: #172554;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .meta-info {
            margin-bottom: 10px;
            font-size: 9px;
            color: #475569;
        }
        .meta-info table {
            width: 100%;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table th {
            background-color: #172554;
            color: #ffffff;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #172554;
            text-align: left;
            letter-spacing: 0.3px;
        }
        .data-table td {
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            font-size: 8.8px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 7.8px;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
        }
        .badge-baik { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-rusak-ringan { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge-rusak-berat { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-hilang { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }

        /* Signature block */
        .footer, .signature-block {
            margin-top: 20px;
            width: 100%;
            page-break-inside: avoid;
        }
        .footer table {
            width: 100%;
        }
        .footer td {
            font-size: 9px;
        }
    </style>
</head>
<body>

    @include('components.kop-surat', ['isPdf' => true])

    <div class="header-title">
        <h3>LAPORAN REKAPITULASI KONDISI BARANG & PERALATAN MESIN</h3>
    </div>

    <div class="meta-info">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: left;">
                    @php
                        $bulanList = [
                            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        $namaBulan = ($filterBulan && $filterBulan !== 'all' && isset($bulanList[$filterBulan])) ? $bulanList[$filterBulan] : 'Semua Bulan';
                        $tahunStr = ($filterTahun && $filterTahun !== 'all') ? $filterTahun : 'Semua Tahun';
                    @endphp
                    <strong>Periode:</strong> {{ $namaBulan }} {{ $tahunStr }}
                </td>
                <td style="text-align: right;">
                    <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. Tabel Detail Data Aset (Mengikuti Filter Aktif) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">No</th>
                <th style="width: 14%;">No. Reg Pemda</th>
                <th style="width: 16%;">Jenis Barang</th>
                <th style="width: 18%;">Merek / Tipe</th>
                <th style="width: 16%;">Penanggung Jawab</th>
                <th style="width: 5%; text-align: center;">Tahun</th>
                <th style="width: 17%; text-align: center;">Kondisi</th>
                <th style="width: 11%; text-align: center;">Gambar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aset as $index => $item)
            @php
                $pct = $item->persentase_kondisi ?? match($item->kondisi) {
                    'Baik' => 100,
                    'Rusak Ringan' => 65,
                    'Rusak Berat' => 25,
                    'Hilang' => 0,
                    default => 100,
                };
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $item->no_reg_pemda ?: '-' }}</strong></td>
                <td>{{ $item->jenis_barang }}</td>
                <td>{{ $item->merek_tipe ?: '-' }}</td>
                <td>{{ $item->penanggung_jawab ?: 'Belum Ditentukan' }}</td>
                <td style="text-align: center;">{{ $item->tahun ?: '-' }}</td>
                <td style="text-align: center;">
                    @if($item->kondisi === 'Baik')
                        <span class="badge badge-baik">Baik ({{ $pct }}%)</span>
                    @elseif($item->kondisi === 'Rusak Ringan')
                        <span class="badge badge-rusak-ringan">Rusak Ringan ({{ $pct }}%)</span>
                    @elseif($item->kondisi === 'Rusak Berat')
                        <span class="badge badge-rusak-berat">Rusak Berat ({{ $pct }}%)</span>
                    @elseif($item->kondisi === 'Hilang')
                        <span class="badge badge-hilang" style="white-space: nowrap;">Belum Ditemukan Keberadaannya (0%)</span>
                    @else
                        <span class="badge badge-hilang">{{ $item->kondisi }} ({{ $pct }}%)</span>
                    @endif
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    @if($item->thumb_data_uri)
                        @php
                            $dim = $item->thumb_dimensions ?? ['width' => 90, 'height' => 70];
                        @endphp
                        <img src="{{ $item->thumb_data_uri }}" width="{{ $dim['width'] }}" height="{{ $dim['height'] }}" alt="Foto" style="display: block; margin: 0 auto; border-radius: 4px; border: 1px solid #cbd5e1;">
                    @else
                        <span style="color: #94a3b8; font-weight: bold; font-size: 11px;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 15px; color: #94a3b8;">
                    Tidak ada data aset pada kriteria filter ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. Blok Tanda Tangan Resmi -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 65%;"></td>
                <td style="width: 35%; text-align: center;">
                    <p style="margin: 0;">Suwawa, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 4px 0 50px 0;"><strong>Pengelola Barang / Aset</strong></p>
                    <p style="margin: 0;"><u>_______________________</u></p>
                    <p style="margin: 3px 0 0 0;">NIP. .....................................</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
