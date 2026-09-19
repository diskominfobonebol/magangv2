<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Mahasiswa Magang - SIMPATIK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --color-navy: #172554;
            --color-blue: #3B82F6;
            --color-pink: #EC4899;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .app-background {
            background: linear-gradient(135deg, #EFF6FF 0%, #F0FDF4 50%, #FDF2F8 100%) !important;
            background-attachment: fixed !important;
        }
        .bg-card-gradient {
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%) !important;
        }
        .form-input {
            width: 100% !important;
            border-radius: 9999px !important;
            border: 1px solid #CBD5E1 !important;
            padding: 0.75rem 1.25rem !important;
            font-size: 0.875rem !important;
            background-color: #FFFFFF !important;
            transition: all 0.2s ease-in-out !important;
            outline: none !important;
        }
        .form-input:focus {
            border-color: #10B981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15) !important;
        }
        .form-label {
            display: block !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #334155 !important;
            margin-bottom: 0.35rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }
        .btn-pill-primary {
            border-radius: 9999px !important;
            background: linear-gradient(135deg, #059669 0%, #10B981 100%) !important;
            color: #FFFFFF !important;
            font-weight: 700 !important;
            transition: all 0.2s ease-in-out !important;
            border: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
        }
        .btn-pill-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px 0 rgba(16, 185, 129, 0.4) !important;
            background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
        }
        .btn-signin-mahasiswa {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            padding: 0.875rem 1.5rem !important;
            font-size: 0.875rem !important;
            font-weight: 800 !important;
            color: #ffffff !important;
            background: linear-gradient(135deg, #BE185D 0%, #EC4899 100%) !important;
            border-radius: 9999px !important;
            box-shadow: 0 10px 25px -3px rgba(190, 24, 93, 0.4), 0 4px 6px -2px rgba(190, 24, 93, 0.2) !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.25s ease-in-out !important;
            text-decoration: none !important;
        }
        .btn-signin-mahasiswa:hover {
            background: linear-gradient(135deg, #9D174D 0%, #DB2777 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 14px 28px -4px rgba(190, 24, 93, 0.5) !important;
        }
        .text-navy {
            color: var(--color-navy) !important;
        }
    </style>
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full" x-data="{ showPassword: false }">
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

        <!-- Badge Khusus Mahasiswa Magang -->
        <div class="flex items-center justify-center mb-6">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-pink-50 text-[#BE185D] border border-pink-200 text-xs font-bold shadow-xs">
                <i class="fa-solid fa-graduation-cap text-[#EC4899]"></i> Portal Khusus Mahasiswa Magang
            </span>
        </div>

        <!-- Card Form Login Mahasiswa -->
        <div class="bg-card-gradient p-8 sm:p-10 rounded-3xl shadow-xl shadow-pink-900/5 border border-pink-200/50 backdrop-blur-sm">
            <div class="mb-5 pb-3 border-b border-pink-100 text-left">
                <h2 class="text-base font-extrabold text-navy">Masuk Portal Mahasiswa Magang</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Layanan mandiri pendaftaran &amp; unduh surat balasan magang</p>
            </div>
            @if(session('error'))
                <div class="mb-6 bg-rose-50 text-rose-600 p-4 rounded-xl text-sm border border-rose-200 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl text-sm border border-emerald-200 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-base shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-rose-50 text-rose-600 p-4 rounded-xl text-sm border border-rose-200">
                    <ul class="list-disc pl-4 space-y-1 font-medium text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="form-label">Email Mahasiswa</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="username" required class="form-input" placeholder="mahasiswa@email.com" value="{{ old('email') }}">
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="mt-1 relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" autocomplete="current-password" required class="form-input pr-10" placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#EC4899] transition">
                            <i class="fa-solid text-xs" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end text-xs">
                    <a href="{{ route('password.request') }}" class="text-[#BE185D] hover:text-pink-800 font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-key text-[10px]"></i> Lupa Password?
                    </a>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-signin-mahasiswa shadow-lg" style="background: linear-gradient(135deg, #BE185D 0%, #EC4899 100%) !important; color: #ffffff !important; min-height: 48px;">
                        <span>Sign In Mahasiswa</span>
                        <i class="fa-solid fa-arrow-right ml-2 text-white"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-200/60 text-center text-xs text-slate-600 font-medium space-y-3">
                <div>
                    Belum memiliki akun magang? 
                    <a href="{{ route('register') }}" class="text-[#EC4899] hover:underline font-extrabold">Daftar Akun Baru</a>
                </div>
                <div class="pt-1 border-t border-slate-100">
                    <a href="{{ route('landing') }}" class="text-slate-500 hover:text-navy transition inline-flex items-center gap-1.5 font-semibold">
                        <i class="fa-solid fa-arrow-left text-[11px]"></i>
                        <span>Kembali ke Halaman Utama</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500 font-medium">
            &copy; {{ date('Y') }} SIMPATIK &mdash; Dinas Kominfo Bone Bolango.
        </div>
    </div>
</body>
</html>
