<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sinosip</title>
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
<body class="app-background min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-6">
            <a href="/" class="inline-block group">
                <div class="flex items-center justify-center gap-3 mb-3">
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda Bone Bolango" class="h-12 w-auto object-contain drop-shadow-sm transition transform group-hover:scale-105">
                    <div class="h-8 w-px bg-slate-300"></div>
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Diskominfo Bone Bolango" class="h-11 w-auto object-contain drop-shadow-sm transition transform group-hover:scale-105">
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight">
                    SIMPATIK <span class="text-primary font-bold">&bull; Sinosip</span>
                </h1>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Dinas Kominfo Kabupaten Bone Bolango</p>
            </a>
        </div>

        <!-- Badge Khusus Pegawai & ASN -->
        <div class="flex items-center justify-center mb-6">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-[#172554] border border-blue-200 text-xs font-bold shadow-xs">
                <i class="fa-solid fa-user-shield text-[#3B82F6]"></i> Portal Khusus Pegawai &amp; ASN
            </span>
        </div>

        <!-- Card -->
        <div class="bg-card-gradient p-8 sm:p-10 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 backdrop-blur-sm">
            <div class="mb-5 pb-3 border-b border-blue-100 text-left">
                <h2 class="text-base font-extrabold text-navy">Masuk Portal Pegawai &amp; ASN</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Akses Surat SPT/SPPD, Kenaikan Pangkat, dan Aset Daerah</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 text-red-600 p-4 rounded-xl text-sm border border-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

           <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="form-label">Email / NIP</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="text" autocomplete="username" required class="form-input" placeholder="Masukkan Email atau NIP" value="{{ old('email') }}">
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input" style="padding-right: 2.5rem !important;" placeholder="••••••••">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                            <!-- Eye Closed / Slashed (Default) -->
                            <svg id="eyeSlashIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                            <!-- Eye Open (When visible) -->
                            <svg id="eyeIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg cursor-pointer">
                        Sign In &rarr;
                    </button>
                </div>
            </form>
            
            <div class="mt-4 pt-3 border-t border-blue-100 text-center text-xs">
                <button type="button" id="btnLupaPassword" class="text-slate-400 hover:text-navy font-semibold inline-flex items-center gap-1 transition cursor-pointer">
                    <i class="fa-solid fa-key text-[10px]"></i>
                    Lupa Password?
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-blue-100/60 text-center">
                <a href="{{ route('landing') }}" class="text-xs text-slate-500 hover:text-navy font-semibold inline-flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Halaman Utama</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Lupa Password Pegawai -->
    <div id="modalLupaPassword" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(15,23,42,0.55); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-7 text-center border border-blue-200/60">
            <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h3 class="text-base font-black text-navy mb-2">Lupa Password Akun Pegawai</h3>
            <p class="text-xs text-slate-600 font-medium leading-relaxed mb-4">
                Reset password untuk akun <strong>Pegawai / ASN</strong> tidak dapat dilakukan secara mandiri.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center mb-5">
                <p class="text-xs font-bold text-navy leading-relaxed">
                    Silakan hubungi Admin Master untuk reset password Anda.
                </p>
            </div>
            <button type="button" id="btnTutupModal" class="w-full py-2.5 rounded-full bg-navy text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                Mengerti, Tutup
            </button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');
            const eyeIcon = document.getElementById('eyeIcon');

            if (toggleBtn && passwordInput && eyeSlashIcon && eyeIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    eyeSlashIcon.classList.toggle('hidden', isPassword);
                    eyeIcon.classList.toggle('hidden', !isPassword);
                });
            }

            // Modal Lupa Password
            const btnLupa = document.getElementById('btnLupaPassword');
            const modal = document.getElementById('modalLupaPassword');
            const btnTutup = document.getElementById('btnTutupModal');

            if (btnLupa && modal) {
                btnLupa.addEventListener('click', function () {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            }
            if (btnTutup && modal) {
                btnTutup.addEventListener('click', function () {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                });
            }
        });
    </script>
</body>
</html>
