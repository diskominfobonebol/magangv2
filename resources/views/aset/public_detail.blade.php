<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOMTARA - Detail Aset ({{ $aset->no_reg_pemda }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">
    <style>
        :root {
            --color-primary: #198754;
            --color-green-light: #D1E7DD;
            --color-white: #FFFFFF;
            --color-pink: #EC4899;
            --color-pink-light: #F9A8D4;
            --color-navy: #172554;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
    </style>
</head>
<body class="app-background font-sans antialiased text-slate-800 py-8 px-4 sm:px-6">

    <div class="max-w-xl mx-auto">
        <!-- Header Brand -->
        <div class="text-center mb-6">
            <div class="flex items-center justify-center space-x-3 mb-3">
                <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda" class="h-12 w-auto object-contain drop-shadow-md">
                <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Kominfo" class="h-12 w-auto object-contain drop-shadow-md">
            </div>
            <h1 class="text-2xl font-black text-white drop-shadow-sm tracking-tight">Detail Informasi Aset</h1>
            <p class="text-sm text-emerald-100 font-medium mt-1">KOMTARA &bull; Dinas Komunikasi dan Informatika Bone Bolango</p>
        </div>

        <!-- Card Main Info -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <!-- Top Status Badge Banner -->
            <div class="bg-[#172554] text-white px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="text-[#D1E7DD] uppercase tracking-wider font-semibold">Pemda:</span>
                        <span class="font-bold tracking-wide text-white font-mono">{{ $aset->no_reg_pemda ?: '-' }}</span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="text-emerald-300 uppercase tracking-wider font-semibold">KOMINFO:</span>
                        <span class="font-bold tracking-wide text-emerald-400 font-mono">{{ $aset->no_reg_kominfo ?: '-' }}</span>
                    </div>
                </div>
                <div>
                    @php
                        $badgeColor = match($aset->kondisi ?? 'Baik') {
                            'Baik' => 'bg-emerald-500 text-white',
                            'Rusak Ringan' => 'bg-amber-500 text-white',
                            'Rusak Berat', 'Hilang' => 'bg-rose-500 text-white',
                            default => 'bg-slate-500 text-white'
                        };
                    @endphp
                    <span class="{{ $badgeColor }} text-xs px-3 py-1.5 rounded-full font-bold shadow-sm inline-flex items-center space-x-1">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Kondisi: {{ $aset->kondisi ?? 'Baik' }}</span>
                    </span>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <!-- 1. Penanggung Jawab -->
                <div class="flex items-start space-x-4 p-4 bg-[#D1E7DD]/40 rounded-2xl border border-[#A3CFBB]/70">
                    <div class="w-10 h-10 rounded-xl bg-[#198754] text-white flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-user-shield text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 font-medium block">Penanggung Jawab</span>
                        <h3 class="text-base font-bold text-[#172554]">{{ $aset->penanggung_jawab ?? 'Belum Ditentukan' }}</h3>
                    </div>
                </div>

                <!-- 2. Jenis & Merek -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center space-x-2 text-[#198754] mb-1">
                            <i class="fa-solid fa-box text-sm"></i>
                            <span class="text-xs font-semibold text-slate-500 uppercase">Jenis Aset</span>
                        </div>
                        <p class="font-bold text-[#172554]">{{ $aset->jenis_barang }}</p>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center space-x-2 text-[#EC4899] mb-1">
                            <i class="fa-solid fa-tag text-sm"></i>
                            <span class="text-xs font-semibold text-slate-500 uppercase">Merek / Tipe</span>
                        </div>
                        <p class="font-bold text-[#172554]">{{ $aset->merek_tipe }}</p>
                    </div>
                </div>

                <!-- 3. Spesifikasi Aset -->
                <div class="border-t border-slate-100 pt-4">
                    <h4 class="text-sm font-bold text-[#172554] mb-3 flex items-center space-x-2">
                        <i class="fa-solid fa-sliders text-[#198754]"></i>
                        <span>Spesifikasi & Detail Teknis</span>
                    </h4>

                    <div class="bg-slate-50 rounded-2xl p-4 divide-y divide-slate-200/60 text-sm">
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">Tahun Pengadaan</span>
                            <span class="font-bold text-[#172554]">{{ $aset->tahun ?? '-' }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">Kondisi Barang</span>
                            <span class="font-bold {{ $aset->kondisi === 'Baik' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $aset->kondisi }}
                            </span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">No. Rangka / Seri</span>
                            <span class="font-semibold text-slate-800">{{ $aset->no_rangka_seri ?: '-' }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">No. Mesin</span>
                            <span class="font-semibold text-slate-800">{{ $aset->no_mesin ?: '-' }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">No. Polisi</span>
                            <span class="font-semibold text-slate-800">{{ $aset->no_polisi ?: '-' }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">No. BPKB</span>
                            <span class="font-semibold text-slate-800">{{ $aset->no_bpkb ?: '-' }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">Lokasi Unit</span>
                            <span class="font-semibold text-slate-800">{{ $aset->keterangan_lokasi_unit ?: '-' }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">Harga Perolehan</span>
                            <span class="font-bold text-[#198754]">Rp {{ number_format($aset->harga_perolehan ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @if($aset->no_sk_bast)
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">No. SK BAST</span>
                            <span class="font-semibold text-slate-800">{{ $aset->no_sk_bast }}</span>
                        </div>
                        @endif
                        @if($aset->tgl_mulai_pinjam || $aset->tgl_selesai_pinjam)
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500 font-medium">Masa Pinjam Pakai</span>
                            <span class="font-semibold text-purple-700">
                                @if($aset->tgl_mulai_pinjam && $aset->tgl_selesai_pinjam)
                                    {{ \Carbon\Carbon::parse($aset->tgl_mulai_pinjam)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($aset->tgl_selesai_pinjam)->translatedFormat('d M Y') }}
                                @elseif($aset->tgl_mulai_pinjam)
                                    Mulai {{ \Carbon\Carbon::parse($aset->tgl_mulai_pinjam)->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 4. Riwayat Berita Acara Aset -->
                <div class="border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-[#172554] flex items-center space-x-2">
                            <i class="fa-solid fa-file-contract text-[#198754]"></i>
                            <span>Riwayat Berita Acara</span>
                        </h4>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-[#D1E7DD] text-[#0F5132] border border-[#A3CFBB]">
                            {{ $aset->beritaAcaras->count() }} Berkas
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($aset->beritaAcaras as $ba)
                        <div class="p-3.5 bg-slate-50 hover:bg-[#D1E7DD]/30 rounded-2xl border border-slate-200/80 transition space-y-2">
                            <div class="flex items-start justify-between gap-2 flex-wrap">
                                <div>
                                    <div class="flex items-center space-x-1.5 flex-wrap gap-1 mb-1">
                                        @if($ba->sumber === 'generate')
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#D1E7DD] text-[#0F5132] border border-[#A3CFBB]">
                                                <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                                                <span>BAST Resmi</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                                                <span>Scan Fisik</span>
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <i class="fa-regular fa-calendar-days text-[#198754]"></i>
                                            {{ \Carbon\Carbon::parse($ba->tanggal)->translatedFormat('d F Y') }}
                                        </span>
                                    </div>
                                    <h5 class="text-xs font-bold text-[#172554]">
                                        {{ $ba->nomor_surat ?: $ba->jenis }}
                                    </h5>
                                    @if($ba->sumber === 'generate' && ($ba->pihak_pertama_nama || $ba->pihak_kedua_nama))
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Penyerah: <strong>{{ $ba->pihak_pertama_nama ?: '-' }}</strong> &bull; Penerima: <strong>{{ $ba->pihak_kedua_nama ?: '-' }}</strong>
                                        </p>
                                    @elseif($ba->keterangan)
                                        <p class="text-[11px] text-slate-500 mt-0.5 italic">
                                            {{ $ba->keterangan }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-1.5 self-end sm:self-center shrink-0">
                                    <a href="{{ route('berita_acara.preview', $ba->id) }}" target="_blank" class="px-2.5 py-1 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-700 text-[10px] font-bold inline-flex items-center space-x-1 transition shadow-2xs">
                                        <i class="fa-solid fa-eye"></i>
                                        <span>Lihat</span>
                                    </a>
                                    <a href="{{ route('berita_acara.download_pdf', $ba->id) }}" class="px-2.5 py-1 rounded-full bg-rose-100 hover:bg-rose-200 text-rose-800 text-[10px] font-bold inline-flex items-center space-x-1 transition shadow-2xs" title="Download PDF">
                                        <i class="fa-solid fa-file-pdf text-rose-600"></i>
                                        <span>PDF</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-400">
                            Belum ada riwayat Berita Acara yang diterbitkan atau diunggah untuk aset ini.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">QR Code Sistem Pemantauan Aset Diskominfo</p>
            </div>
        </div>

    </div>

</body>
</html>
