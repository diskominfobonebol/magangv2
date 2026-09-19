<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Mahasiswa Magang | SIMPATIK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
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
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
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
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important;
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
            box-shadow: 0 6px 20px 0 rgba(59, 130, 246, 0.45) !important;
        }
        .text-navy {
            color: #172554 !important;
        }
    </style>
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="inline-block group">
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
            <div class="inline-block mt-3 px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full uppercase tracking-wider">
                Portal Mahasiswa Magang
            </div>
            <h2 class="mt-3 text-2xl font-bold text-navy">Reset Kata Sandi Mandiri</h2>
            <p class="mt-1 text-sm text-slate-600 font-medium">Masukkan email terdaftar untuk menerima link reset kata sandi</p>
        </div>

        <!-- Card Form Forgot Password -->
        <div class="bg-card-gradient p-8 sm:p-10 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 backdrop-blur-sm">
            @if(session('status'))
                <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-2xl text-sm border border-emerald-200 flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-lg shrink-0 mt-0.5 text-emerald-600"></i>
                    <div>
                        <p class="font-bold">Tautan Terkirim!</p>
                        <p class="mt-0.5 text-xs text-emerald-600">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-rose-50 text-rose-600 p-4 rounded-2xl text-sm border border-rose-200">
                    <ul class="list-disc pl-4 space-y-1 font-medium text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="form-label">Email Mahasiswa</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="email" required class="form-input" placeholder="mahasiswa@email.com" value="{{ old('email') }}">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5 pl-3">
                        <i class="fa-solid fa-circle-info text-blue-500"></i> Pastikan email ini aktif dan terdaftar saat registrasi akun magang.
                    </p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Kirim Link Reset Password
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-blue-200/40 text-center text-xs text-slate-600 font-medium flex items-center justify-center gap-4">
                <a href="{{ route('login.mahasiswa') }}" class="text-[#3B82F6] hover:text-blue-800 font-bold transition">
                    &larr; Kembali ke Login Mahasiswa
                </a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('landing') }}" class="text-slate-500 hover:text-slate-800 font-medium transition">
                    Halaman Utama
                </a>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500 font-medium">
            &copy; {{ date('Y') }} SIMPATIK &mdash; Dinas Kominfo Bone Bolango.
        </div>
    </div>
</body>
</html>
