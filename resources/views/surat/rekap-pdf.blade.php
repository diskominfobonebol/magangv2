<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Nomor Surat {{ !empty($filterJenisLabel) ? '— ' . $filterJenisLabel . ' ' : '' }}- Dinas Kominfo Bone Bolango</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #172554;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .meta-info {
            margin-bottom: 10px;
            font-size: 10px;
        }
        .rekap-cards {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .rekap-cards td {
            padding: 8px;
            text-align: center;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .rekap-cards .card-title {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .rekap-cards .card-value {
            font-size: 14px;
            font-weight: bold;
            color: #172554;
            margin-top: 3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #172554;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #172554;
            text-align: left;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        ul.personel-list {
            margin: 0;
            padding-left: 12px;
        }
        ul.personel-list li {
            margin-bottom: 2px;
            font-size: 9px;
            line-height: 1.25;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer table {
            width: 100%;
        }
        .footer td {
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI PEMERINTAH DAERAH KABUPATEN BONE BOLANGO -->
    @include('components.kop-surat', ['isPdf' => true])

    <!-- JUDUL LAPORAN -->
    <div style="text-align: center; margin-bottom: 12px;">
        <h3 style="margin: 0; font-size: 13px; color: #172554; text-transform: uppercase; font-weight: bold;">LAPORAN REKAPITULASI PENERBITAN NOMOR SURAT{{ !empty($filterJenisLabel) ? ' — ' . $filterJenisLabel : '' }}</h3>
        <p style="margin: 3px 0 0 0; font-size: 9.5px; color: #64748b;">Arsip Penerbitan Surat Perintah Tugas (SPT) dan Surat Perintah Perjalanan Dinas (SPPD)</p>
    </div>

    <div class="meta-info" style="text-align: right; margin-bottom: 10px;">
        <span style="font-size: 9.5px; color: #475569;"><strong>Tanggal Cetak:</strong> {{ date('d F Y') }}</span>
    </div>

    <!-- RINGKASAN METRIK -->
    <table class="rekap-cards">
        <tr>
            <td style="width: 33.33%;">
                <div class="card-title">Total Nomor SPT</div>
                <div class="card-value" style="color: #172554;">{{ $totalSpt ?? 0 }} Dokumen</div>
            </td>
            <td style="width: 33.33%;">
                <div class="card-title">Total Nomor SPPD</div>
                <div class="card-value" style="color: #9d174d;">{{ $totalSppd ?? 0 }} Dokumen</div>
            </td>
            <td style="width: 33.33%;">
                <div class="card-title">Total Bulan Ini</div>
                <div class="card-value" style="color: #3B82F6;">{{ $totalBulanIni ?? 0 }} Dokumen</div>
            </td>
        </tr>
    </table>

    <!-- TABEL REKAPITULASI KESELURUHAN (KOLOM SPT & SPPD BERDAMPINGAN) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 10%; text-align: center;">Tanggal</th>
                <th style="width: 17%;">Nomor Surat SPT</th>
                <th style="width: 17%;">Nomor Surat SPPD</th>
                <th style="width: 20%;">Perihal / Uraian Tugas</th>
                <th style="width: 13%;">Tujuan</th>
                <th style="width: 14%;">Pegawai yang Ditugaskan</th>
                <th style="width: 5%; text-align: center;">Ket</th>
            </tr>
        </thead>
        <tbody>
            @forelse($surats ?? [] as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($item->tgl_surat)->translatedFormat('d/m/Y') }}</td>
                <td>
                    <strong style="font-family: 'Courier New', Courier, monospace; color: #1e3a8a; font-size: 9.5px;">{{ $item->nomor_surat }}</strong>
                </td>
                <td>
                    @if($item->has_sppd)
                        @php
                            $nomorSppd = str_starts_with($item->nomor_surat, '555') 
                                ? str_replace('555/', '090/', $item->nomor_surat) 
                                : preg_replace('/^[^\/]+/', '090', $item->nomor_surat);
                        @endphp
                        <span style="font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #9d174d; font-size: 9.5px;">{{ $nomorSppd }}</span>
                    @else
                        <span style="color: #94a3b8; text-align: center; display: block;">-</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $item->perihal }}</strong>
                    @if($item->uraian)
                        <br><span style="font-size: 8.5px; color: #64748b;">{{ $item->uraian }}</span>
                    @endif
                </td>
                <td>{{ $item->tujuan }}</td>
                <td>
                    @if($item->pegawais && $item->pegawais->count() > 0)
                        <ul class="personel-list">
                            @foreach($item->pegawais as $pegawai)
                                <li>{{ $pegawai->nama }} <span style="font-size: 8px; color: #64748b;">(NIP. {{ $pegawai->nip }})</span></li>
                            @endforeach
                        </ul>
                    @else
                        <span style="color: #94a3b8; font-style: italic;">-</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $item->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; font-style: italic; color: #94a3b8; padding: 15px;">Belum ada data rekapitulasi nomor surat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN PENGESAHAN -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%;">
                    <p>Suwawa, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin-bottom: 50px;"><strong>Kepala Dinas Komunikasi dan Informatika<br>Kabupaten Bone Bolango</strong></p>
                    <p><u><strong>Drs. H. Syamsuddin, M.Si</strong></u></p>
                    <p>NIP. 196502121990031004</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
