<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinosip - Sistem Informasi Notifikasi Kenaikan Pangkat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind = {
            theme: {
                extend: {
                    colors: {
                        navy: '#172554',
                        primary: '#3B82F6',
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
</head>
<body class="app-background font-sans antialiased text-navy">
    <div class="min-h-screen flex flex-col justify-between">
        
        <!-- Navbar Atas -->
        <header class="max-w-7xl mx-auto w-full px-6 py-6 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 text-white rounded-2xl flex items-center justify-center font-bold text-xl shadow-lg shadow-blue-500/25" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <div>
                    <span class="text-2xl font-extrabold text-navy tracking-tight">Sinosip</span>
                    <span class="block text-[10px] font-semibold text-slate-500 -mt-1">Dinas Kominfo Bone Bolango</span>
                </div>
            </div>
        </header>

        <!-- Hero Section Utama -->
        <main class="max-w-4xl mx-auto px-6 text-center py-12 space-y-8">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold badge-gradient shadow-sm">
                <span>📍 Dinas Kominfo Bone Bolango</span>
            </div>

            <h1 class="text-4xl md:text-6xl font-extrabold text-navy tracking-tight leading-tight">
                Sistem Arsip & <br>
                <span class="text-primary">Notifikasi Kenaikan Pangkat</span>
            </h1>

            <p class="text-base md:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Sinosip memudahkan pengelolaan arsip surat menyurat (SPT & SPPD) dan memberikan notifikasi otomatis via WhatsApp untuk Gaji Berkala dan Kenaikan Pangkat pegawai.
            </p>

            <div class="pt-2">
                <!-- Tombol Mulai Sekarang Mengarah ke Halaman Login Sistem -->
                <a href="{{ route('login') }}" class="btn-pill-gradient px-8 py-3.5 text-base shadow-lg shadow-blue-500/30">
                    Mulai Sekarang &rarr;
                </a>
            </div>
        </main>

        <!-- Footer Sederhana -->
        <footer class="max-w-7xl mx-auto w-full px-6 py-6 text-center text-xs text-slate-500 border-t border-blue-200/40">
           <p>&copy; {{ date('Y') }} Sinosip &mdash; Dinas Kominfo Bone Bolango. All rights reserved.</p>
           <p class="mt-1 text-xs text-slate-400">Sistem Informasi Notifikasi Kenaikan Pangkat/Berkala dan Arsip Surat Menyurat (Sinosip)</p>
        </footer>

    </div>
</body>
</html>