<!-- KOP SURAT RESMI PEMERINTAH DAERAH KABUPATEN BONE BOLANGO (SIMETRIS RATA TENGAH) -->
<table class="kop-table">
    <tr>
        <!-- Logo Kiri: Logo Pemda Bone Bolango -->
        <td class="kop-logo-kiri">
            @if(!empty($logoBoneBase64))
                <img src="data:image/png;base64,{{ $logoBoneBase64 }}" alt="Logo Bone Bolango">
            @elseif(file_exists(public_path('images/logo-pemda-transparent.png')))
                <img src="{{ public_path('images/logo-pemda-transparent.png') }}" alt="Logo Bone Bolango">
            @elseif(file_exists(public_path('images/bonebolango.png')))
                <img src="{{ public_path('images/bonebolango.png') }}" alt="Logo Bone Bolango">
            @endif
        </td>
        
        <!-- Teks Kop Surat di Tengah -->
        <td class="kop-text">
            <h2>PEMERINTAH KABUPATEN BONE BOLANGO</h2>
            <h1>DINAS KOMUNIKASI DAN INFORMATIKA</h1>
            <p class="kop-alamat">Jl. Prof. Dr. Ing. B.J. Habibie, Kec. Suwawa, Kabupaten Bone Bolango - 96184</p>
            <p style="margin: 2px 0 0 0; font-size: 10px; color: #334155; font-weight: 500; line-height: 1.3; font-family: Arial, sans-serif;">
                E-mail: <a href="mailto:kominfo@bonebolangokab.go.id" style="color: #2563eb; text-decoration: none;">kominfo@bonebolangokab.go.id</a> | Website: <a href="mailto:kominfo@bonebolangokab.go.id" style="color: #2563eb; text-decoration: none;">kominfo@bonebolangokab.go.id</a>
            </p>
        </td>

        <!-- Kolom Penyeimbang Simetris Kanan (Tanpa Logo) -->
        <td class="kop-spacer-kanan"></td>
    </tr>
</table>
