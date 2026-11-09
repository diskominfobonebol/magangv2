<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Serah Terima - {{ $nomor_surat }}</title>
    <style>
        @page {
            size: 215.9mm 330mm;
            margin: 1.5cm 2cm 1.5cm 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #172554;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }

        .title-section {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        .doc-title {
            margin: 0;
            font-size: 12.5pt;
            font-weight: bold;
            color: #172554;
            text-decoration: underline;
            letter-spacing: 0.5px;
            font-family: 'Times New Roman', Times, serif;
        }
        .doc-number {
            margin: 3px 0 0 0;
            font-size: 11pt;
            font-weight: bold;
            color: #172554;
            font-family: 'Times New Roman', Times, serif;
        }

        .opening-p {
            text-align: justify;
            margin-bottom: 8px;
            line-height: 1.2;
            font-size: 11pt;
        }

        .table-pihak {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .table-pihak td {
            vertical-align: top;
            padding: 1px 0;
            font-size: 11pt;
            line-height: 1.15;
        }

        .clause-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .clause-table td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 11pt;
            line-height: 1.2;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            page-break-inside: avoid;
        }
        .sign-table td {
            text-align: center;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.2;
        }
        .sign-space {
            height: 48px;
        }
        .underline {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- 1. KOP SURAT RESMI -->
    @include('components.kop-surat', ['isPdf' => true, 'fontFamily' => "'Times New Roman', Times, serif"])

    <!-- 2. JUDUL DOKUMEN & NOMOR -->
    <div class="title-section">
        <h3 class="doc-title uppercase">BERITA ACARA SERAH TERIMA</h3>
        <p class="doc-number">Nomor : {{ $nomor_surat }}</p>
    </div>

    <!-- 3. PARAGRAF PEMBUKA -->
    <p class="opening-p">
        Pada hari <strong>{{ $hari }}</strong> Tanggal <strong>{{ $tanggal_terbilang }}</strong> Bulan <strong>{{ $bulan }}</strong> Tahun <strong>{{ $tahun_terbilang }}</strong>, Saya yang bertanda tangan di bawah ini:
    </p>

    <!-- 4. DATA PIHAK PERTAMA & PIHAK KEDUA -->
    <table class="table-pihak">
        <!-- PIHAK PERTAMA -->
        <tr>
            <td style="width: 4%;"><strong>1.</strong></td>
            <td style="width: 20%;"><strong>Nama</strong></td>
            <td style="width: 3%;">:</td>
            <td style="width: 73%;"><strong>{{ $pihak_pertama['nama'] }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $pihak_pertama['nip'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pihak_pertama['jabatan'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="italic" style="color: #334155; padding-bottom: 5px;">Disebut Sebagai</td>
            <td style="padding-bottom: 5px;">:</td>
            <td style="padding-bottom: 5px;"><strong>PIHAK PERTAMA</strong></td>
        </tr>

        <!-- PIHAK KEDUA -->
        <tr>
            <td><strong>2.</strong></td>
            <td><strong>Nama</strong></td>
            <td>:</td>
            <td><strong>{{ $pihak_kedua['nama'] }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $pihak_kedua['nip'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pihak_kedua['jabatan'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="italic" style="color: #334155; padding-bottom: 5px;">Disebut Sebagai</td>
            <td style="padding-bottom: 5px;">:</td>
            <td style="padding-bottom: 5px;"><strong>PIHAK KEDUA</strong></td>
        </tr>
    </table>

    <!-- 5. PARAGRAF PERNYATAAN PENYERAHAN -->
    <p class="opening-p" style="margin-top: 4px; margin-bottom: 6px;">
        Dengan ini menyerahkan <strong>{{ $barang_penyerahan }}</strong> untuk keperluan pekerjaan, dengan ketentuan sebagai berikut:
    </p>

    <!-- 6. KLAUSUL PERTAMA S.D. KETUJUH (Sejajar dengan Titik Dua Data Pihak) -->
    <table class="clause-table">
        @foreach($klausul as $label => $isi)
        <tr>
            <td style="width: 24%;"><strong>{{ $label }}</strong></td>
            <td style="width: 3%;">:</td>
            <td style="width: 73%; text-align: justify;">{{ $isi }}</td>
        </tr>
        @endforeach
    </table>

    <!-- 7. PARAGRAF PENUTUP -->
    <p class="opening-p" style="margin-top: 6px; margin-bottom: 8px;">
        {{ $paragraf_penutup }}
    </p>

    <!-- 8. TEMPAT & TANGGAL (Rata Tengah) -->
    <div class="text-center" style="margin-top: 10px; margin-bottom: 8px; font-size: 11pt;">
        {{ $tempat }}, {{ $tanggal_formatted }}
    </div>

    <!-- 9. BLOK TANDA TANGAN -->
    <table class="sign-table" style="margin-top: 0;">
        <tr>
            <td style="padding-top: 4px;">
                Yang Menyerahkan,<br>
                <strong>PIHAK PERTAMA</strong>
            </td>
            <td style="padding-top: 4px;">
                Yang Menerima,<br>
                <strong>PIHAK KEDUA</strong>
            </td>
        </tr>
        <tr>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
        </tr>
        <tr>
            <td>
                <strong class="underline">{{ $pihak_pertama['nama'] }}</strong><br>
                NIP. {{ $pihak_pertama['nip'] }}
            </td>
            <td>
                <strong class="underline">{{ $pihak_kedua['nama'] }}</strong><br>
                NIP. {{ $pihak_kedua['nip'] }}
            </td>
        </tr>
    </table>

    <!-- 9. MENGETAHUI (Rata Tengah di Bawah) -->
    <table class="sign-table" style="margin-top: 15px;">
        <tr>
            <td style="width: 25%;"></td>
            <td style="width: 50%;">
                Mengetahui,<br>
                <strong>{{ $mengetahui['jabatan'] }}</strong>
            </td>
            <td style="width: 25%;"></td>
        </tr>
        <tr>
            <td colspan="3" class="sign-space"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <strong class="underline">{{ $mengetahui['nama'] }}</strong><br>
                NIP. {{ $mengetahui['nip'] }}
            </td>
            <td></td>
        </tr>
    </table>

</body>
</html>
