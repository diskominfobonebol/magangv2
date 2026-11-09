@props(['isPdf' => false, 'fontFamily' => 'Arial, sans-serif'])

<div style="width: 100%; border-bottom: 3px double #172554; padding-bottom: 4px; margin-bottom: 14px;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
        <tr>
            <td style="width: 15%; text-align: center; vertical-align: middle; border: none; padding: 0 10px 0 0;">
                @if($isPdf && file_exists(public_path('images/logo-pemda.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo-pemda.png'))) }}" style="height: 70px; width: auto; max-width: 100%;" alt="Logo Pemda Bone Bolango">
                @else
                    <img src="{{ asset('images/logo-pemda.png') }}" style="height: 70px; width: auto; max-width: 100%;" alt="Logo Pemda Bone Bolango">
                @endif
            </td>
            <td style="width: 85%; text-align: center; vertical-align: middle; border: none; padding: 0;">
                <h2 style="margin: 0; font-size: 16px; font-weight: 800; color: #172554; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; font-family: {{ $fontFamily }};">
                    PEMERINTAH KABUPATEN BONE BOLANGO
                </h2>
                <h3 style="margin: 4px 0; font-size: 14px; font-weight: 800; color: #172554; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; font-family: {{ $fontFamily }};">
                    DINAS KOMUNIKASI & INFORMATIKA
                </h3>
                <p style="margin: 0; font-size: 10px; color: #334155; font-weight: 500; line-height: 1.3; font-family: {{ $fontFamily }};">
                    Jl. Prof. DR. Ing. H. BJ. Habibie, Kec. Suwawa, Kabupaten Bone Bolango – 96184
                </p>
                <p style="margin: 2px 0 0 0; font-size: 10px; color: #334155; font-weight: 500; line-height: 1.3; font-family: {{ $fontFamily }};">
                    E-mail: <a href="mailto:kominfo@bonebolangokab.go.id" style="color: #2563eb; text-decoration: none;">kominfo@bonebolangokab.go.id</a> | Website: <a href="https://kominfo.bonebolangokab.go.id" target="_blank" style="color: #2563eb; text-decoration: none;">kominfo.bonebolangokab.go.id</a>
                </p>
            </td>
        </tr>
    </table>
</div>
