<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Surat Masuk - Dinas Kominfo Bone Bolango</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
            size: A4 landscape;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        
        /* Kop Surat Resmi Symmetrical Rata Tengah */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }
        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-logo-kiri {
            width: 12%;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo-kiri img {
            width: 60px;
            height: auto;
            max-height: 72px;
            display: inline-block;
        }
        .kop-spacer-kanan {
            width: 12%;
        }
        .kop-text {
            width: 76%;
            text-align: center;
            vertical-align: middle;
        }
        .kop-text h2 {
            font-size: 11pt;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
            line-height: 1.25;
        }
        .kop-text h1 {
            font-size: 15pt;
            margin: 2px 0 3px 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
            line-height: 1.2;
        }
        .kop-text p.kop-alamat {
            font-size: 8.5pt;
            margin: 1px 0;
            line-height: 1.3;
            color: #111;
        }
        .kop-text p.kop-kontak {
            font-size: 8.5pt;
            margin: 1px 0;
            line-height: 1.3;
            color: #222;
        }
        .kop-text p.kop-kontak a {
            color: #1e3a8a;
            text-decoration: underline;
        }

        /* Judul Laporan */
        .judul-laporan {
            text-align: center;
            margin-bottom: 12px;
        }
        .judul-laporan h3 {
            font-size: 12pt;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.3px;
        }
        .judul-laporan p.periode {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 3px 0 2px 0;
        }
        .judul-laporan p.subjudul {
            font-size: 8.5pt;
            margin: 0;
            color: #444;
        }


        /* Table Styling */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8.5pt;
            table-layout: fixed;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 8pt;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }

        /* Tanda Tangan */
        .ttd-container {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-box {
            float: right;
            width: 280px;
            text-align: center;
            font-size: 9pt;
            line-height: 1.35;
        }
        .ttd-space {
            height: 55px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI (SHARED COMPONENT) -->
    @include('components.kop-surat-pdf')

    <!-- JUDUL LAPORAN -->
    <div class="judul-laporan">
        <h3>LAPORAN REKAPITULASI SURAT MASUK</h3>
        <p class="periode">{{ $periodeText ?? 'Rekapitulasi Surat Masuk Keseluruhan' }}</p>
        <p class="subjudul">Arsip Pencatatan Surat Masuk yang Diterima dari Instansi / Lembaga / Pihak Luar</p>
    </div>

    <!-- TABEL REKAPITULASI SURAT MASUK (LANDSCAPE & PROPORSI RAPI) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 9%; text-align: center;">Tanggal</th>
                <th style="width: 19%;">Nomor Surat</th>
                <th style="width: 22%;">Asal Surat / Instansi Pengirim</th>
                <th style="width: 26%;">Uraian / Perihal</th>
                <th style="width: 11%; text-align: center;">Keterangan</th>
                <th style="width: 9%; text-align: center;">Bukti Fisik</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suratMasuks ?? [] as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($item->tanggal_surat)->translatedFormat('d/m/Y') }}</td>
                <td style="font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #1e3a8a;">
                    {{ $item->nomor_surat }}
                </td>
                <td style="font-weight: bold; color: #172554;">
                    {{ $item->asal_surat }}
                </td>
                <td>
                    {{ $item->uraian }}
                </td>
                <td style="text-align: {{ !empty($item->keterangan) ? 'left' : 'center' }};">
                    {{ $item->keterangan ?? '-' }}
                </td>
                <td style="text-align: center; font-size: 8pt;">
                    @if(!empty($item->link_google_drive))
                        <span style="color: #047857; font-weight: bold;">Tersedia (Drive)</span>
                    @else
                        <span style="color: #64748b;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; font-style: italic; color: #777; padding: 15px;">Belum ada data rekapitulasi surat masuk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN PENGESAHAN -->
    <div class="ttd-container">
        <div class="ttd-box">
            Bone Bolango, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>Kepala Dinas Komunikasi dan Informatika<br>Kabupaten Bone Bolango</strong>
            <div class="ttd-space"></div>
            <div class="ttd-nama">Drs. H. Syamsuddin, M.Si</div>
            <div>NIP. 196502121990031004</div>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
