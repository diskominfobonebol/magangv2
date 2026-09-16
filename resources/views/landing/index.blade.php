<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPATIK - Sistem Informasi Terpadu Dinas Kominfo Bone Bolango</title>
    <meta name="description" content="Portal resmi Sistem Informasi Manajemen Aset, Pendaftaran Magang Mahasiswa, Arsip Surat SPT/SPPD, dan Notifikasi Kenaikan Pangkat Dinas Komunikasi dan Informatika Kabupaten Bone Bolango.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind = {
            theme: {
                extend: {
                    colors: {
                        navy: '#172554',
                        primary: '#2563EB',
                        'blue-light': '#93C5FD',
                        'pink-light': '#F9A8D4',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --color-navy: #172554;
            --color-blue: #2563EB;
            --color-pink: #DB2777;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            background-color: #FAFAFC;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col justify-between selection:bg-blue-600 selection:text-white bg-[#FAFAFC] relative overflow-x-hidden">

    <!-- Ambient Subtle Background Glow -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-b from-blue-100/60 via-indigo-50/30 to-transparent blur-3xl opacity-80 rounded-full"></div>
        <div class="absolute top-1/3 -left-32 w-80 h-80 bg-blue-100/40 blur-3xl rounded-full"></div>
        <div class="absolute top-1/2 -right-32 w-80 h-80 bg-pink-100/35 blur-3xl rounded-full"></div>
    </div>

    <!-- Top Navbar Bersih, Seimbang & Elegan -->
    <header class="w-full bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 h-20 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 sm:gap-4 group">
                <!-- Dual Logos Pemda & Kominfo dengan Optical Balance -->
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda Bone Bolango" class="h-10 sm:h-11 w-auto object-contain transition transform group-hover:scale-105">
                    <div class="h-6 w-px bg-slate-200"></div>
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Diskominfo Bone Bolango" class="h-9 sm:h-10 w-auto object-contain transition transform group-hover:scale-105">
                </div>
                <div class="border-l border-slate-200 pl-3 sm:pl-3.5">
                    <span class="font-black text-xl sm:text-2xl tracking-tight text-[#172554] block leading-none">SIMPATIK</span>
                    <span class="text-[9.5px] sm:text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block mt-1">DISKOMINFO BONE BOLANGO</span>
                </div>
            </a>

            <!-- Right Navigation Quick Access -->
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold text-white bg-[#2563EB] hover:bg-blue-700 shadow-md shadow-blue-500/20 transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Ke Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('landing.magang') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold text-slate-600 hover:text-[#DB2777] hover:bg-pink-50 transition">
                        <i class="fa-solid fa-graduation-cap text-[#DB2777]"></i>
                        <span>Portal Magang</span>
                    </a>
                    <a href="{{ route('login.pegawai') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100/80 border border-blue-200/80 shadow-xs transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Login Pegawai</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Hero Section -->
    <main class="flex-1 flex flex-col justify-center items-center py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            
            <!-- 1. Pill Badge Terpusat dengan Animasi Halus -->
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-blue-50/90 border border-blue-200/70 text-blue-700 text-xs font-bold mb-7 shadow-xs">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                </span>
                <span>Sistem Informasi Terpadu &bull; Dinas Kominfo Bone Bolango</span>
            </div>

            <!-- 2. Headline H1 Dua Baris dengan Tipografi Seimbang -->
            <h1 class="text-3xl sm:text-5xl lg:text-[50px] font-black text-[#0F172A] leading-[1.18] tracking-tight mb-5">
                Digitalisasi Pengelolaan <span class="text-[#2563EB]">Aset Daerah</span>,<br class="hidden sm:inline">
                Arsip, Kenaikan Pangkat &amp; <span class="text-[#DB2777]">Program Magang</span>
            </h1>

            <!-- 3. Paragraf Deskripsi Ringkas & Padat -->
            <p class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto leading-relaxed font-normal mb-10">
                Satu portal pelayanan terpadu untuk tata kelola pendataan aset dinas serta pendaftaran dan kegiatan mahasiswa magang di Dinas Komunikasi dan Informatika Kabupaten Bone Bolango. Dilengkapi pengelolaan arsip surat dinas (SPT &amp; SPPD) dan notifikasi otomatis WhatsApp untuk kenaikan pangkat pegawai.
            </p>

            <!-- 4. Dua Tombol Aksi Simetris & Rapi -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mb-14">
                <!-- Tombol 1: Login Pegawai (Biru) -->
                <a href="{{ route('login.pegawai') }}" class="w-full sm:w-64 inline-flex items-center justify-between px-6 py-3.5 rounded-full bg-[#2563EB] hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/35 transition-all transform hover:-translate-y-0.5 group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user-shield text-base text-white"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-[10.5px] font-semibold text-blue-100 uppercase tracking-wider leading-tight">Akses Khusus</span>
                            <span class="block text-sm font-extrabold text-white leading-tight">Login Pegawai</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs text-blue-200 group-hover:translate-x-1 transition-transform"></i>
                </a>

                <!-- Tombol 2: Portal Magang (Pink / Magenta) -->
                <a href="{{ route('landing.magang') }}" class="w-full sm:w-64 inline-flex items-center justify-between px-6 py-3.5 rounded-full bg-[#DB2777] hover:bg-[#BE185D] text-white font-bold shadow-lg shadow-pink-500/25 hover:shadow-xl hover:shadow-pink-500/35 transition-all transform hover:-translate-y-0.5 group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-graduation-cap text-base text-white"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-[10.5px] font-semibold text-pink-100 uppercase tracking-wider leading-tight">Mahasiswa / Siswa</span>
                            <span class="block text-sm font-extrabold text-white leading-tight">Portal Magang</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs text-pink-200 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <!-- 5. Garis Pembatas & 4 Badge Fitur Rapih -->
            <div class="w-full max-w-4xl mx-auto border-t border-slate-200/80 pt-8">
                <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-4 text-xs font-semibold text-slate-700">
                    <!-- Fitur 1: Aset -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white border border-slate-200/80 shadow-2xs hover:border-blue-200 hover:bg-blue-50/40 transition">
                        <i class="fa-solid fa-circle-check text-[#2563EB] text-sm"></i>
                        <span>Inventarisasi Aset Dinas</span>
                    </div>
                    <!-- Fitur 2: Surat -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white border border-slate-200/80 shadow-2xs hover:border-slate-300 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-circle-check text-[#1E293B] text-sm"></i>
                        <span>Arsip Surat (SPT &amp; SPPD)</span>
                    </div>
                    <!-- Fitur 3: Kenaikan Pangkat -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white border border-slate-200/80 shadow-2xs hover:border-emerald-200 hover:bg-emerald-50/40 transition">
                        <i class="fa-solid fa-circle-check text-[#10B981] text-sm"></i>
                        <span>Notifikasi WhatsApp Pegawai</span>
                    </div>
                    <!-- Fitur 4: Magang -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white border border-slate-200/80 shadow-2xs hover:border-pink-200 hover:bg-pink-50/40 transition">
                        <i class="fa-solid fa-circle-check text-[#DB2777] text-sm"></i>
                        <span>Alur Magang Transparan</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer Bersih & Elegan -->
    <footer class="w-full py-6 border-t border-slate-200/80 bg-white/50 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Pemerintah Kabupaten Bone Bolango &bull; Dinas Komunikasi dan Informatika</p>
    </footer>

</body>
</html>