<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Kata Sandi Wajib - KOMTARA</title>
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
        .form-input {
            width: 100% !important;
            height: 2.75rem !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #172554 !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s ease-in-out !important;
        }
        .form-input:focus {
            outline: none !important;
            border-color: #146C43 !important;
            box-shadow: 0 0 0 3px rgba(20, 108, 67, 0.2) !important;
        }
        .form-label {
            display: block !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #172554 !important;
            margin-bottom: 0.375rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.025em !important;
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
<body class="app-background min-h-screen flex flex-col antialiased">

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
        <div class="max-w-md w-full" x-data="{ showOld: false, showNew: false, showConfirm: false }">
            <!-- Header Area Form Ganti Password -->
            <div class="text-center mb-6">
                <!-- Badge Keterangan Sistem Sesuai Landing Page -->
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/95 border border-white/80 text-amber-900 text-xs font-black shadow-md">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span><i class="fa-solid fa-shield-halved mr-1 text-amber-600"></i> Keamanan Login Pertama</span>
                </div>

                <h2 class="mt-4 text-2xl sm:text-3xl font-black text-white drop-shadow-sm">Ganti Kata Sandi Wajib</h2>
                <p class="mt-1 text-xs sm:text-sm text-emerald-100 font-medium drop-shadow-xs">
                    Halo <strong class="text-white underline">{{ $user->nama }}</strong>, demi keamanan data instansi, Anda wajib mengganti kata sandi awal sebelum dapat menggunakan sistem.
                </p>
            </div>

            <!-- Card Form Ganti Password -->
            <div class="bg-card-gradient p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-200 backdrop-blur-sm">
                @if(session('info'))
                    <div class="mb-5 bg-[#D1E7DD] text-[#0F5132] p-3.5 rounded-xl text-xs border border-[#A3CFBB] flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-info text-sm shrink-0 mt-0.5 text-[#198754]"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

            @if(session('error'))
                <div class="mb-5 bg-red-50 text-red-600 p-3.5 rounded-xl text-xs border border-red-200 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-sm shrink-0 mt-0.5"></i>
                    <span>{{ session('error') }}</span>
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

            <form action="{{ route('password.change.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Password Lama -->
                <div>
                    <label for="old_password" class="form-label">Kata Sandi Lama (Default)</label>
                    <div class="relative">
                        <input :type="showOld ? 'text' : 'password'" id="old_password" name="old_password" required class="form-input pr-10" placeholder="Masukkan kata sandi lama / default">
                        <button type="button" @click="showOld = !showOld" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#198754] transition">
                            <i class="fa-solid text-xs" :class="showOld ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Password Baru -->
                <div>
                    <label for="password" class="form-label">Kata Sandi Baru</label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" id="password" name="password" required class="form-input pr-10" placeholder="Minimal 8 karakter (huruf & angka)">
                        <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#198754] transition">
                            <i class="fa-solid text-xs" :class="showNew ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 font-medium">
                        <i class="fa-solid fa-circle-check text-slate-400 mr-1"></i> Minimal 8 karakter, wajib kombinasi huruf dan angka.
                    </p>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label for="password_confirmation" class="form-label">Ulangi Kata Sandi Baru</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required class="form-input pr-10" placeholder="Ketik ulang kata sandi baru">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#198754] transition">
                            <i class="fa-solid text-xs" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg">
                        Simpan Kata Sandi & Lanjut &rarr;
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-200 text-center">
                <form action="{{ route('logout') }}" method="POST" class="inline" onsubmit="sessionStorage.removeItem('sidebar.manajemenAset');">
                    @csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-red-600 font-semibold transition inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Batalkan dan Keluar (Logout)</span>
                    </button>
                </form>
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
