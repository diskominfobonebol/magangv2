<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Surat</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; padding: 2cm; margin: 0; background: #fff; }
        .kop { border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; }
        .kop .logo { width: 80px; height: 80px; border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; }
        .kop .text-kop { flex: 1; text-align: center; }
        .kop h1 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .kop h2 { font-size: 16pt; margin: 2px 0; text-transform: uppercase; font-weight: bold; }
        .kop p { font-size: 10pt; margin: 0; }
        .judul-surat { text-align: center; margin-bottom: 20px; }
        .judul-surat h3 { font-size: 14pt; margin: 0; text-transform: uppercase; text-decoration: underline; font-weight: bold; }
        .judul-surat p { font-size: 12pt; margin: 2px 0; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt; }
        .info-grid div { width: 48%; }
        .uraian { text-align: justify; text-indent: 1cm; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11pt; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { font-weight: bold; }
        .ttd { width: 250px; float: right; text-align: center; margin-top: 30px; font-size: 11pt; }
        .ttd .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 80px; margin-bottom: 2px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; text-align: center;">
        Jika dialog print tidak muncul otomatis, tekan Ctrl+P atau <button onclick="window.print()">Klik di sini untuk mencetak</button>.
    </div>

    <!-- KOP SURAT MENGGUNAKAN TABEL AGAR TIDAK TURUN KE BAWAH -->
    <table style="width: 100%; border-collapse: collapse; border-bottom: 3px solid #000; margin-bottom: 20px; padding-bottom: 10px; border:none;">
        <tr>
            <!-- Kolom Logo di Kiri (Logo Pemda Bone Bolango) -->
            <td style="width: 15%; vertical-align: middle; text-align: center; border: none; padding-bottom: 10px;">
                @if(file_exists(public_path('images/bonebolango.png')))
                    <img src="{{ ($isPdf ?? false) ? public_path('images/bonebolango.png') : asset('images/bonebolango.png') }}" alt="Logo Bone Bolango" style="width: 65px; height: auto;">
                @endif
            </td>
            
            <!-- Kolom Teks Instansi di Tengah -->
            <td style="width: 70%; vertical-align: middle; text-align: center; border: none; padding-bottom: 10px;">
                <h1 style="font-size: 13pt; margin: 0; text-transform: uppercase; font-weight: bold;">Pemerintah Kabupaten Bone Bolango</h1>
                <h2 style="font-size: 14pt; margin: 2px 0; text-transform: uppercase; font-weight: bold; white-space: nowrap;">Dinas Komunikasi dan Informatika</h2>
                <p style="font-size: 9.5pt; margin: 2px 0 0 0; white-space: nowrap;">Jl. Prof. Dr. Ing. B.J. Habibie, Kompleks Perkantoran Bone Bolango, Kode Pos 96582</p>
                <p style="font-size: 9.5pt; margin: 0; white-space: nowrap;">Laman: www.bonebolangokab.go.id | Email: kominfo@bonebolangokab.go.id</p>
            </td>
            
            <!-- Kolom Logo di Kanan (Logo Kominfo) -->
            <td style="width: 15%; vertical-align: middle; text-align: center; border: none; padding-bottom: 10px;">
                @if(file_exists(public_path('images/logo-kominfo.png')))
                    <img src="{{ ($isPdf ?? false) ? public_path('images/logo-kominfo.png') : asset('images/logo-kominfo.png') }}" alt="Logo Kominfo" style="width: 75px; height: auto;">
                @endif
            </td>
        </tr>
    </table>

    <div class="judul-surat">
        <h3>Surat Perjalanan Tugas</h3>
        <p>Nomor: {{ $surat->nomor_surat ?? '-' }}</p>
    </div>

    <div class="info-grid">
        <div>
            <strong>Tanggal Surat:</strong><br>
            {{ \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d F Y') }}
        </div>
        <div>
            <strong>Tujuan:</strong><br>
            {{ $surat->tujuan ?? '-' }}
        </div>
    </div>

    <p class="uraian">
        {{ $surat->uraian ?? '-' }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Jabatan</th>
            </tr>
        </thead>
        <tbody>
            @if($surat->pegawais && $surat->pegawais->count() > 0)
                @foreach($surat->pegawais as $index => $pegawai)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $pegawai->nama }}</td>
                    <td>{{ $pegawai->nip }}</td>
                    <td>{{ $pegawai->jabatan }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada personel yang ditugaskan.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="margin-bottom: 20px;">
        <strong>Keterangan Tambahan:</strong><br>
        {{ $surat->keterangan ?? '-' }}
    </div>

    <div class="ttd">
        Ditetapkan di Gorontalo<br>
        pada tanggal {{ \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d F Y') }}<br>
        <div class="nama-ttd">Drs. H. Syamsuddin, M.Si</div>
        NIP. 196502121990031004
    </div>
    <div style="clear: both;"></div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>