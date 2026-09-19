<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Nomor Surat SPT & SPPD - Dinas Kominfo Bone Bolango</title>
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
        
        /* Kop Surat Resmi Symmetrical Rata Tengah (Single Logo Pemda di Kiri) */
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
        
        /* Personel list */
        ul.personel-list {
            margin: 0;
            padding-left: 14px;
        }
        ul.personel-list li {
            margin-bottom: 2px;
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

    <!-- KOP SURAT RESMI PEMERINTAH DAERAH KABUPATEN BONE BOLANGO (SIMETRIS RATA TENGAH) -->
    @include('components.kop-surat-pdf')

    <!-- JUDUL LAPORAN -->
    <div class="judul-laporan">
        <h3>LAPORAN REKAPITULASI PENERBITAN NOMOR SURAT</h3>
        <p class="periode">{{ $periodeText ?? 'Rekapitulasi Nomor Surat Keseluruhan' }}</p>
        <p class="subjudul">Arsip Penerbitan Surat Perintah Tugas (SPT) dan Surat Perintah Perjalanan Dinas (SPPD)</p>
    </div>

    <!-- TABEL REKAPITULASI KESELURUHAN (LANDSCAPE & PROPORSI KOLOM RAPI) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3.5%; text-align: center;">No</th>
                <th style="width: 8.5%; text-align: center;">Tanggal</th>
                <th style="width: 16%;">Nomor Surat SPT</th>
                <th style="width: 16%;">Nomor Surat SPPD</th>
                <th style="width: 22%;">Perihal / Uraian Tugas</th>
                <th style="width: 11%;">Tujuan</th>
                <th style="width: 13%;">Pegawai yang Ditugaskan</th>
                <th style="width: 10%; text-align: center;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($surats ?? [] as $index => $item)
            @php
                $isSppdPdf = ($item->jenis_surat_id == 1 || str_starts_with($item->nomor_surat, '090/'));
                
                $directSppds = [];
                if ($item->has_sppd && $item->pegawais) {
                    foreach ($item->pegawais as $p) {
                        if (!empty($p->pivot->nomor_sppd) && $p->pivot->nomor_sppd !== '-') {
                            $directSppds[] = [
                                'nomor' => $p->pivot->nomor_sppd,
                                'nama' => $p->nama,
                                'is_child' => false,
                            ];
                        }
                    }
                }

                $childSppds = [];
                if ($item->children && $item->children->count() > 0) {
                    foreach ($item->children as $child) {
                        if ($child->pegawais && $child->pegawais->count() > 0) {
                            foreach ($child->pegawais as $cp) {
                                $cNomor = $cp->pivot->nomor_sppd ?: $child->nomor_surat;
                                if (!empty($cNomor) && $cNomor !== '-') {
                                    $childSppds[] = [
                                        'nomor' => $cNomor,
                                        'nama' => $cp->nama,
                                        'is_child' => true,
                                    ];
                                }
                            }
                        } else {
                            $childSppds[] = [
                                'nomor' => $child->nomor_surat,
                                'nama' => '-',
                                'is_child' => true,
                            ];
                        }
                    }
                }
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($item->tgl_surat)->translatedFormat('d/m/Y') }}</td>
                <td style="font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #1e3a8a;">
                    @if($isSppdPdf)
                        @if($item->parent)
                            {{ $item->parent->nomor_surat }} <span style="font-size: 7pt; color: #1e40af; font-weight: normal;">(Induk)</span>
                        @elseif(!empty($item->spt_induk_manual))
                            {{ $item->spt_induk_manual }} <span style="font-size: 7pt; color: #b45309; font-weight: normal;">(Manual)</span>
                        @else
                            <span style="color: #666; font-weight: normal;">-</span>
                        @endif
                    @else
                        {{ $item->nomor_surat }}
                        @if($item->children && $item->children->count() > 0)
                            <div style="font-size: 7pt; color: #1e40af; font-weight: normal;">[Induk - {{ $item->children->count() }} SPPD Susulan]</div>
                        @endif
                    @endif
                </td>
                <td style="font-family: 'Courier New', Courier, monospace; font-weight: bold;">
                    @if(count($directSppds) > 0 || count($childSppds) > 0)
                        <ul class="personel-list" style="list-style-type: none; padding-left: 0; margin: 0;">
                            @foreach($directSppds as $sppd)
                                <li style="color: #9d174d; margin-bottom: 2px;">
                                    &bull; {{ $sppd['nomor'] }}
                                </li>
                            @endforeach
                            @foreach($childSppds as $sppd)
                                <li style="color: #9d174d; margin-bottom: 2px;">
                                    &bull; {{ $sppd['nomor'] }} <span style="font-size: 6.5pt; color: #4338ca; font-weight: normal;">(Susulan)</span>
                                </li>
                            @endforeach
                        </ul>
                    @elseif($item->has_sppd)
                        <span style="color: #b45309; font-size: 7.5pt; font-style: italic;">Data Tidak Lengkap</span>
                    @else
                        <span style="color: #666; font-weight: normal; text-align: center; display: block;">-</span>
                    @endif
                </td>
                <td>
                    {{ $item->uraian ?: '-' }}
                </td>
                <td>{{ $item->tujuan }}</td>
                <td>
                    @if($item->pegawais && $item->pegawais->count() > 0)
                        <div>
                            @if($item->children && $item->children->count() > 0)
                                <div style="font-size: 7pt; font-weight: bold; color: #475569; text-transform: uppercase;">Personel SPT:</div>
                            @endif
                            <ul class="personel-list">
                                @foreach($item->pegawais as $pegawai)
                                    <li>{{ $pegawai->nama }} <span style="font-size: 7.5pt; color: #555;">(NIP. {{ $pegawai->nip }})</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <span style="color: #888; font-style: italic;">-</span>
                    @endif

                    @if($item->children && $item->children->count() > 0)
                        @foreach($item->children as $child)
                            @if($child->pegawais && $child->pegawais->count() > 0)
                                <div style="margin-top: 4px; padding-top: 3px; border-top: 1px dashed #cbd5e1;">
                                    <div style="font-size: 7pt; font-weight: bold; color: #9d174d; text-transform: uppercase;">
                                        Personel SPPD Susulan ({{ $child->nomor_surat }}):
                                    </div>
                                    <ul class="personel-list">
                                        @foreach($child->pegawais as $cp)
                                            <li>{{ $cp->nama }} <span style="font-size: 7.5pt; color: #555;">(NIP. {{ $cp->nip }})</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </td>
                <td style="text-align: {{ !empty($item->keterangan) ? 'left' : 'center' }};">
                    {{ $item->keterangan ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; font-style: italic; color: #777;">Belum ada data rekapitulasi nomor surat.</td>
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
