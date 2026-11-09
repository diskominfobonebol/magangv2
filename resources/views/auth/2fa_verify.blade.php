<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Dua Faktor (2FA) - KOMTARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">

    <style>
        [x-cloak] { display: none !important; }
        :root {
            --color-primary: #198754;
            --color-navy: #172554;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        body, body.app-background, .app-background {
            background: #024d43e6 !important;
            min-height: 100vh;
        }
        .bg-card-gradient {
            background: #FFFFFF !important;
            border: 1px solid rgba(203, 213, 225, 0.8) !important;
            border-radius: 1.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(23, 37, 84, 0.07), 0 4px 12px -2px rgba(23, 37, 84, 0.03);
        }
        .btn-pill-primary {
            background: #146C43 !important;
            color: #FFFFFF !important;
            border: none !important;
            border-radius: 9999px !important;
            font-weight: 700 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 14px 0 rgba(20, 108, 67, 0.35) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
        }
        .btn-pill-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px 0 rgba(20, 108, 67, 0.45) !important;
            background: #0F5132 !important;
        }
        .text-navy {
            color: var(--color-navy) !important;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
    </style>
</head>
<body class="app-background min-h-screen flex flex-col antialiased" x-data="{ useRecovery: false }">

    <!-- Top Navbar Identik Landing Page -->
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

    <div class="flex-1 flex items-center justify-center p-4 py-8 sm:py-12">
        <div class="max-w-md w-full">
            <!-- Header Area Form 2FA -->
            <div class="text-center mb-6">
                <!-- Badge Keterangan Sistem Sesuai Landing Page -->
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/95 border border-white/80 text-[#0A3622] text-xs font-black shadow-md">
                    <span class="w-2 h-2 rounded-full bg-[#198754] animate-pulse"></span>
                    <span><i class="fa-solid fa-shield-halved mr-1 text-[#198754]"></i> Verifikasi Dua Faktor (2FA)</span>
                </div>

                <h2 class="mt-4 text-2xl sm:text-3xl font-black text-white drop-shadow-sm" x-text="useRecovery ? 'Verifikasi Kode Cadangan' : 'Verifikasi Authenticator'">Verifikasi Authenticator</h2>
                <p class="mt-1 text-xs sm:text-sm text-emerald-100 font-medium drop-shadow-xs">
                    Masuk sebagai <strong class="text-white underline">{{ $user->nama }}</strong>
                </p>
            </div>

            <!-- Main Card Verification -->
            <div class="bg-card-gradient p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-200 backdrop-blur-sm">
                @if(session('info'))
                    <div class="mb-5 bg-[#D1E7DD] text-[#0F5132] p-3.5 rounded-xl text-xs border border-[#A3CFBB] flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-info text-sm shrink-0 mt-0.5 text-[#198754]"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

            @if(session('success'))
                <div class="mb-5 bg-emerald-50 text-emerald-700 p-3.5 rounded-xl text-xs border border-emerald-200 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-sm shrink-0 mt-0.5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-50 text-red-600 p-3.5 rounded-xl text-xs border border-red-200">
                    <ul class="list-disc pl-4 space-y-1 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('2fa.verify.check') }}" method="POST" class="space-y-5" onsubmit="sessionStorage.removeItem('sidebar.manajemenAset');">
                @csrf

                <!-- Mode 1: TOTP 6 Digit -->
                <div x-show="!useRecovery">
                    <label for="code_totp" class="block text-xs font-bold uppercase tracking-wider text-navy mb-2 text-center">
                        Kode 6 Digit Google Authenticator
                    </label>
                    <div class="relative">
                        <input type="text" id="code_totp" name="code" maxlength="6" inputmode="numeric" autocomplete="one-time-code" autofocus
                            class="w-full text-center text-3xl font-black font-mono tracking-[0.4em] py-3 px-4 bg-white border-2 border-[#A3CFBB] rounded-2xl text-navy focus:outline-none focus:ring-4 focus:ring-[#198754]/20 focus:border-[#198754] shadow-inner"
                            placeholder="••••••" :disabled="useRecovery">
                    </div>
                    <p class="text-[11px] text-slate-500 text-center mt-2 font-medium">
                        Buka aplikasi <strong>Google Authenticator</strong> di HP atau ekstensi di browser laptop Anda.
                    </p>
                </div>

                <!-- Mode 2: Recovery Code -->
                <div x-show="useRecovery" x-cloak>
                    <label for="code_recovery" class="block text-xs font-bold uppercase tracking-wider text-navy mb-2 text-center">
                        Masukkan 1 Kode Cadangan (Recovery Code)
                    </label>
                    <div class="relative">
                        <input type="text" id="code_recovery" name="code" autocomplete="off"
                            class="w-full text-center text-lg font-bold font-mono tracking-wider py-3 px-4 bg-white border-2 border-amber-400 rounded-2xl text-navy focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 shadow-inner"
                            placeholder="Contoh: A1B2-C3D4" :disabled="!useRecovery">
                    </div>
                    <p class="text-[11px] text-amber-700 text-center mt-2 font-medium">
                        Kode cadangan ini hanya dapat dipakai sekali dan akan langsung hangus setelah verifikasi berhasil.
                    </p>
                </div>

                <div>
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg">
                        Verifikasi & Lanjut &rarr;
                    </button>
                </div>

                <!-- Switcher antara TOTP dan Recovery Code -->
                <div class="text-center pt-2">
                    <button type="button" @click="useRecovery = !useRecovery" class="text-xs font-bold text-[#198754] hover:text-[#146C43] transition">
                        <span x-show="!useRecovery">Tidak bisa mengakses Authenticator? Pakai Recovery Code</span>
                        <span x-show="useRecovery">Kembali ke kode Google Authenticator 6 digit</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-200 text-center">
                <form action="{{ route('logout') }}" method="POST" class="inline" onsubmit="sessionStorage.removeItem('sidebar.manajemenAset');">
                    @csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-red-600 font-semibold transition inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Keluar dan Ganti Akun</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Footer Bar Penuh Solid Hijau Tua -->
    <footer class="w-full bg-[#0A3622] text-white py-5 sm:py-6 text-center border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-1.5">
            <p class="text-base font-semibold text-white tracking-wide">KOMTARA &mdash; Dinas Komunikasi dan Informatika</p>
            <p class="text-sm font-normal text-emerald-100">&copy; {{ date('Y') }} Pemerintah Kabupaten Bone Bolango. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
