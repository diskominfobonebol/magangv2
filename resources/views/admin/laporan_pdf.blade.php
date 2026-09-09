<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap SIMPATIK - Diskominfo</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #172554;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #172554;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            color: #172554;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 13px;
            color: #3B82F6;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #64748b;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-info table {
            width: 100%;
        }
        .rekap-cards {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .rekap-cards td {
            width: 20%;
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
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-baik { background-color: #dcfce7; color: #166534; }
        .badge-rusak-ringan { background-color: #fef9c3; color: #854d0e; }
        .badge-rusak-berat { background-color: #fee2e2; color: #991b1b; }
        .badge-hilang { background-color: #f1f5f9; color: #334155; }
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

    @include('components.kop-surat', ['isPdf' => true])

    <div style="text-align: center; margin-bottom: 12px;">
        <h3 style="margin: 0; font-size: 13px; color: #172554; text-transform: uppercase; font-weight: bold;">LAPORAN REKAPITULASI KONDISI BARANG & PERALATAN MESIN</h3>
    </div>

    <div class="meta-info" style="text-align: right; margin-bottom: 10px;">
        <span style="font-size: 9.5px; color: #475569;"><strong>Tanggal Cetak:</strong> {{ date('d F Y') }}</span>
    </div>

    <!-- Ringkasan Statistik Rekap -->
    <table class="rekap-cards">
        <tr>
            <td>
                <div class="card-title">Kondisi Baik</div>
                <div class="card-value" style="color: #166534;">{{ $rekap['Baik'] }}</div>
            </td>
            <td>
                <div class="card-title">Rusak Ringan</div>
                <div class="card-value" style="color: #854d0e;">{{ $rekap['Rusak Ringan'] }}</div>
            </td>
            <td>
                <div class="card-title">Rusak Berat</div>
                <div class="card-value" style="color: #991b1b;">{{ $rekap['Rusak Berat'] }}</div>
            </td>
            <td>
                <div class="card-title">Hilang</div>
                <div class="card-value" style="color: #334155;">{{ $rekap['Hilang'] }}</div>
            </td>
            <td>
                <div class="card-title">Total Aset</div>
                <div class="card-value" style="color: #3B82F6;">{{ $rekap['Total'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Detail Data Aset -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 16%;">No. Reg Pemda</th>
                <th style="width: 18%;">Jenis Barang</th>
                <th style="width: 18%;">Merek / Tipe</th>
                <th style="width: 18%;">Penanggung Jawab</th>
                <th style="width: 10%;">Tahun</th>
                <th style="width: 16%;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aset as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $item->no_reg_pemda }}</strong></td>
                <td>{{ $item->jenis_barang }}</td>
                <td>{{ $item->merek_tipe }}</td>
                <td>{{ $item->penanggung_jawab ?: 'Belum Ditentukan' }}</td>
                <td style="text-align: center;">{{ $item->tahun ?: '-' }}</td>
                <td>
                    @if($item->kondisi === 'Baik')
                        <span class="badge badge-baik">Baik</span>
                    @elseif($item->kondisi === 'Rusak Ringan')
                        <span class="badge badge-rusak-ringan">Rusak Ringan</span>
                    @elseif($item->kondisi === 'Rusak Berat')
                        <span class="badge badge-rusak-berat">Rusak Berat</span>
                    @else
                        <span class="badge badge-hilang">{{ $item->kondisi }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 15px; color: #94a3b8;">Tidak ada data aset pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%;">
                    <p>Suwawa, {{ date('d F Y') }}</p>
                    <p style="margin-bottom: 50px;"><strong>Pengelola Barang / Aset</strong></p>
                    <p><u>_______________________</u></p>
                    <p>NIP. .....................................</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
