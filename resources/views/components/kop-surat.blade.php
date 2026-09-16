@props(['isPdf' => false])

@php
    // Persiapkan data image base64 untuk Logo Pemda Bone Bolango
    $pemdaPath = file_exists(public_path('images/logo-pemda-transparent.png'))
        ? public_path('images/logo-pemda-transparent.png')
        : (file_exists(public_path('images/bonebolango.png'))
            ? public_path('images/bonebolango.png')
            : public_path('images/logo-pemda.png'));
    $pemdaExists = file_exists($pemdaPath);
    $pemdaSrc = null;
    if ($pemdaExists) {
        $pemdaRel = str_replace([public_path() . DIRECTORY_SEPARATOR, public_path() . '/', '\\'], ['', '', '/'], $pemdaPath);
        $pemdaSrc = $isPdf 
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($pemdaPath))
            : asset($pemdaRel);
    }

    // Persiapkan data image base64 untuk Logo Kominfo Bone Bolango
    $kominfoPath = file_exists(public_path('images/logo-kominfo-transparent.png'))
        ? public_path('images/logo-kominfo-transparent.png')
        : (file_exists(public_path('images/logo-kominfo.png'))
            ? public_path('images/logo-kominfo.png')
            : public_path('images/logo-kominfo.jpg'));
    $kominfoExists = file_exists($kominfoPath);
    $kominfoSrc = null;
    if ($kominfoExists) {
        $isJpg = str_ends_with(strtolower($kominfoPath), '.jpg') || str_ends_with(strtolower($kominfoPath), '.jpeg');
        $kominfoMime = $isJpg ? 'image/jpeg' : 'image/png';
        $kominfoRel = str_replace([public_path() . DIRECTORY_SEPARATOR, public_path() . '/', '\\'], ['', '', '/'], $kominfoPath);
        $kominfoSrc = $isPdf 
            ? 'data:' . $kominfoMime . ';base64,' . base64_encode(file_get_contents($kominfoPath))
            : asset($kominfoRel);
    }
@endphp

<div style="width: 100%; border-bottom: 3px double #172554; padding-bottom: 12px; margin-bottom: 20px;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
        <tr>
            <!-- Kolom Logo Pemda Bone Bolango di Sisi Kiri -->
            <td style="width: 14%; text-align: center; vertical-align: middle; border: none; padding: 0 4px 0 0;">
                @if($pemdaSrc)
                    <img src="{{ $pemdaSrc }}" height="65" style="height: 65px; width: auto; max-height: 65px; vertical-align: middle; display: inline-block;" alt="Logo Pemda Bone Bolango">
                @endif
            </td>

            <!-- Kolom Teks Kop Surat di Tengah -->
            <td style="width: 72%; text-align: center; vertical-align: middle; border: none; padding: 0 6px;">
                <h2 style="margin: 0; font-size: 15px; font-weight: 800; color: #172554; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.25; font-family: Arial, sans-serif;">
                    PEMERINTAH KABUPATEN BONE BOLANGO
                </h2>
                <h3 style="margin: 3px 0; font-size: 13px; font-weight: 800; color: #172554; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.25; font-family: Arial, sans-serif;">
                    DINAS KOMUNIKASI & INFORMATIKA
                </h3>
                <p style="margin: 0; font-size: 9.5px; color: #334155; font-weight: 500; line-height: 1.35; font-family: Arial, sans-serif;">
                    Jl. Prof. DR. Ing. H. BJ. Habibie, Kec. Suwawa, Kabupaten Bone Bolango – 96184
                </p>
                <p style="margin: 2px 0 0 0; font-size: 9.5px; color: #334155; font-weight: 500; line-height: 1.35; font-family: Arial, sans-serif;">
                    E-mail: <a href="mailto:kominfo@bonebolangokab.go.id" style="color: #2563eb; text-decoration: none;">kominfo@bonebolangokab.go.id</a> | Website: <a href="mailto:kominfo@bonebolangokab.go.id" style="color: #2563eb; text-decoration: none;">kominfo@bonebolangokab.go.id</a>
                </p>
            </td>

            <!-- Kolom Penyeimbang Simetris di Sisi Kanan (Tanpa Logo Kominfo) -->
            <td style="width: 14%; text-align: center; vertical-align: middle; border: none; padding: 0 0 0 4px;">
            </td>
        </tr>
    </table>
</div>
