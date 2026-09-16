<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPATIK - Dinas Kominfo Bone Bolango</title>
    <meta name="description" content="Satu portal pelayanan terpadu untuk tata kelola pendataan aset serta pendaftaran dan kegiatan mahasiswa magang di Dinas Komunikasi dan Informatika Kabupaten Bone Bolango.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind = {
            theme: {
                extend: {
                    colors: {
                        navy: '#172554',
                        primary: '#2563EB',
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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            background-color: #FFFFFF;
        }
        .header-logo-pemda {
            height: 44px;
            width: auto;
            max-width: 120px;
        }
        .header-logo-kominfo {
            height: 40px;
            width: auto;
            max-width: 120px;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col justify-between selection:bg-blue-600 selection:text-white bg-white relative overflow-x-hidden">

    <!-- Header Sesuai Gambar Referensi -->
    <header class="w-full bg-white border-b border-slate-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 h-20 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 sm:gap-4 group">
                <!-- Dual Logos Pemda & Kominfo -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda Bone Bolango" class="header-logo-pemda h-10 sm:h-11 w-auto object-contain transition transform group-hover:scale-105">
                    <div class="h-7 w-px bg-slate-200"></div>
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Diskominfo Bone Bolango" class="header-logo-kominfo h-9 sm:h-10 w-auto object-contain transition transform group-hover:scale-105">
                </div>
                <div class="border-l border-slate-200 pl-3 sm:pl-3.5">
                    <span class="font-black text-xl sm:text-2xl tracking-tight text-[#172554] block leading-none">SIMPATIK</span>
                    <span class="text-[9.5px] sm:text-[10px] font-bold text-slate-500 uppercase tracking-wider block mt-1">DISKOMINFO BONE BOLANGO</span>
                </div>
            </a>
        </div>
    </header>

    <!-- Main Hero Section -->
    <main class="flex-1 flex flex-col justify-center items-center py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            
            <!-- 1. Badge Kecil -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#EFF6FF] border border-[#BFDBFE]/80 text-[#1E40AF] text-xs font-semibold mb-8 shadow-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB] inline-block"></span>
                <span>Sistem Informasi Terpadu &bull; Dinas Kominfo Bone Bolango</span>
            </div>

            <!-- 2. Judul Utama -->
            <h1 class="text-3xl sm:text-5xl lg:text-[54px] font-black text-[#1E293B] leading-[1.18] tracking-tight mb-6">
                Digitalisasi Pengelolaan <span class="text-[#2563EB]">Aset Daerah</span>,<br class="hidden sm:inline">
                Arsip &amp; Kenaikan Pangkat &amp; <span class="text-[#E11D74]">Program Magang</span>
            </h1>

            <!-- 3. Paragraf Deskripsi -->
            <p class="text-sm sm:text-base text-slate-600 max-w-3xl mx-auto leading-relaxed font-normal mb-12">
                Satu portal pelayanan terpadu untuk tata kelola pendataan aset dinas serta pendaftaran dan kegiatan mahasiswa magang di Dinas Komunikasi dan Informatika Kabupaten Bone Bolango. Dilengkapi kemudahan pengelolaan arsip surat menyurat (SPT &amp; SPPD) dan notifikasi otomatis via WhatsApp untuk Gaji Berkala dan Kenaikan Pangkat pegawai.
            </p>

            <!-- 4. Dua Tombol Aksi -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-7 mb-16">
                <!-- Tombol 1: Login Pegawai (Gradasi Biru) -->
                <a href="{{ route('login.pegawai') }}" class="w-60 h-16 px-6 rounded-full text-white inline-flex items-center justify-between transition-all duration-300 transform hover:-translate-y-1 hover:shadow-2xl shadow-xl shadow-blue-500/35 group" style="background: linear-gradient(135deg, #1D4ED8 0%, #2563EB 55%, #3B82F6 100%);">
                    <i class="fa-solid fa-user text-lg text-white group-hover:scale-110 transition-transform"></i>
                    <div class="text-center">
                        <span class="block text-sm font-extrabold leading-tight">Login</span>
                        <span class="block text-sm font-extrabold leading-tight">Pegawai</span>
                    </div>
                    <i class="fa-solid fa-arrow-right text-sm text-white group-hover:translate-x-1 transition-transform"></i>
                </a>

                <!-- Tombol 2: Portal Magang (Gradasi Pink / Magenta) -->
                <a href="{{ route('landing.magang') }}" class="w-60 h-16 px-6 rounded-full text-white inline-flex items-center justify-between transition-all duration-300 transform hover:-translate-y-1 hover:shadow-2xl shadow-xl shadow-pink-500/35 group" style="background: linear-gradient(135deg, #BE185D 0%, #DB2777 55%, #E11D74 100%);">
                    <i class="fa-solid fa-graduation-cap text-lg text-white group-hover:scale-110 transition-transform"></i>
                    <div class="text-center">
                        <span class="block text-sm font-extrabold leading-tight">Portal</span>
                        <span class="block text-sm font-extrabold leading-tight">Magang</span>
                    </div>
                    <i class="fa-solid fa-arrow-right text-sm text-white group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <!-- 5. Garis Pembatas & 4 Poin Centang -->
            <div class="w-full max-w-4xl mx-auto border-t border-slate-100 pt-8">
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 sm:gap-x-7 text-xs sm:text-[13px] font-medium text-slate-700">
                    <!-- Poin 1: Inventarisasi Aset Dinas (Centang Biru) -->
                    <div class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-[#2563EB] text-base"></i>
                        <span>Inventarisasi Aset Dinas</span>
                    </div>
                    <!-- Poin 2: Arsip Surat (Centang Navy/Gelap) -->
                    <div class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-[#1E293B] text-base"></i>
                        <span>Arsip Surat (SPT &amp; SPPD)</span>
                    </div>
                    <!-- Poin 3: Notifikasi WhatsApp (Centang Hijau) -->
                    <div class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-[#10B981] text-base"></i>
                        <span>Notifikasi WhatsApp Pegawai</span>
                    </div>
                    <!-- Poin 4: Alur Magang (Centang Pink) -->
                    <div class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-[#E11D74] text-base"></i>
                        <span>Alur Magang Transparan</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer Minimalis -->
    <footer class="w-full py-4 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} Dinas Komunikasi dan Informatika Kabupaten Bone Bolango</p>
    </footer>

</body>
</html>