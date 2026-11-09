<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOMTARA: Platform Pengelolaan Aset dan Administrasi Terpadu Diskominfo Bone Bolango</title>
    <meta name="description" content="KOMTARA: Platform operasional internal terpadu khusus lingkungan Dinas Kominfo Bone Bolango guna mewujudkan tertib administrasi dan tata kelola digital.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-navy: #172554;
            --color-primary: #198754;
            --color-primary-dark: #0F5132;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #FFFFFF;
            background: #024d43e6 !important;
            min-height: 100vh;
        }
        .text-navy-main {
            color: #172554 !important;
        }
        .text-desc-main {
            color: #F8FAFC !important;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        /* Tombol Tunggal: Login Pegawai */
        .btn-pegawai-cta {
            background: #FFFFFF !important;
            color: #0A3622 !important;
            box-shadow: 0 10px 25px -4px rgba(10, 54, 34, 0.4) !important;
            transition: all 0.25s ease-in-out !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            font-weight: 800 !important;
        }
        .btn-pegawai-cta:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 14px 28px -4px rgba(10, 54, 34, 0.5) !important;
            background: #F8FAFC !important;
            color: #0F5132 !important;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-[#198754] selection:text-white">

    <!-- Top Navbar -->
    <header class="sticky top-0 z-40 glass-header shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-20 flex items-center">
            <a href="{{ route('landing') }}" class="flex items-center space-x-2.5 group">
                <!-- Logo ukuran proporsional (w-10 h-10 sm:w-11 sm:h-11) -->
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda Bone Bolango" class="w-10 h-10 sm:w-11 sm:h-11 object-contain mix-blend-multiply transition transform group-hover:scale-105">
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Diskominfo Bone Bolango" class="w-10 h-10 sm:w-11 sm:h-11 object-contain mix-blend-multiply transition transform group-hover:scale-105">
                </div>
                <div class="border-l border-slate-300 pl-2.5">
                    <span class="font-black text-xl sm:text-2xl tracking-tight text-[#172554] block leading-none">KOMTARA</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mt-0.5">Diskominfo Bone Bolango</span>
                </div>
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 flex flex-col justify-center py-16 sm:py-24 lg:py-28">
        <section class="text-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <!-- Badge Atas -->
                <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full bg-white/95 border border-white/80 text-[#0A3622] text-xs font-black mb-6 shadow-md">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#198754] animate-pulse"></span>
                    <span class="text-[#0A3622]">KOMTARA &bull; Dinas Kominfo Bone Bolango</span>
                </div>

                <!-- Headline Utama -->
                <h1 class="text-3xl sm:text-5xl lg:text-[52px] font-black text-white drop-shadow-sm tracking-tight leading-[1.18] mb-6">
                    KOMTARA: Platform Pengelolaan Aset<br class="hidden sm:inline">
                    dan <span class="text-[#FEF08A]">Administrasi Terpadu</span> Diskominfo Bone Bolango
                </h1>

                <!-- Deskripsi -->
                <p class="text-base sm:text-lg text-emerald-50 font-medium max-w-3xl mx-auto mb-10 leading-relaxed drop-shadow-xs">
                    Platform operasional internal terpadu khusus lingkungan Dinas Kominfo Bone Bolango guna mewujudkan tertib administrasi dan tata kelola digital. Mengintegrasikan pemantauan fisik aset peralatan kerja milik dinas, legalitas serah terima barang antar-pegawai, serta tata kelola berkas kepegawaian internal yang akuntabel, transparan, dan terpusat.
                </p>

                <!-- TOMBOL TUNGGAL: LOGIN -->
                <div class="flex items-center justify-center max-w-xs mx-auto">
                    <a href="{{ route('login') }}" class="btn-pegawai-cta w-full px-8 py-4 rounded-full text-sm space-x-3">
                        <i class="fa-solid fa-user-shield text-base"></i>
                        <span>Login</span>
                        <i class="fa-solid fa-arrow-right text-xs ml-1 opacity-80"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Bar Penuh Solid Hijau Tua -->
    <footer class="w-full bg-[#0A3622] text-white py-5 sm:py-6 text-center border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-1.5">
            <p class="text-base font-semibold text-white tracking-wide">KOMTARA &mdash; Dinas Komunikasi dan Informatika</p>
            <p class="text-sm font-normal text-emerald-100">&copy; {{ date('Y') }} Pemerintah Kabupaten Bone Bolango. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
