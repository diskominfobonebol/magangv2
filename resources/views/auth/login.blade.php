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
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <span class="font-extrabold text-3xl tracking-tight text-navy">Sinosip</span>
            </a>
            <h2 class="mt-4 text-2xl font-bold text-navy">Masuk ke Akun Anda</h2>
            <p class="mt-1 text-sm text-slate-600 font-medium">Dinas Kominfo Bone Bolango</p>
        </div>

        <!-- Card -->
        <div class="bg-card-gradient p-8 sm:p-10 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 backdrop-blur-sm">
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
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input" placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg">
                        Sign In &rarr;
                    </button>
                </div>
            </form>
            
            <div class="mt-4 pt-4 border-t border-blue-100 text-center text-xs text-slate-600 font-medium">
                Mahasiswa baru? <a href="{{ route('register') }}" class="font-bold text-[#3B82F6] hover:underline">Daftar Akun Magang di sini</a>
            </div>
            
            <div class="mt-3 text-center text-xs text-slate-400 font-medium">
                Lupa password? Silakan hubungi Administrator.
            </div>
        </div>
    </div>
</body>
</html>
