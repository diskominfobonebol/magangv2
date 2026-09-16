<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - SIMPATIK & Sinosip</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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

    <!-- Alpine.js & Chart.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        @media print {
            .no-print, aside, header {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="app-background antialiased flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-sidebar-gradient text-white flex flex-col hidden md:flex h-full z-20 shrink-0 no-print">
        <div class="h-20 flex items-center px-4 border-b border-white/10 gap-3">
            <div class="flex items-center gap-2 px-2.5 py-1.5 bg-[#0f172a] rounded-2xl shrink-0 shadow-md border border-slate-700/80">
                <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Pemda Bone Bolango" class="h-8 w-auto object-contain" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.8)) drop-shadow(0 0 1px rgba(255,255,255,0.35));">
                <div class="h-5 w-px bg-slate-700"></div>
                <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Diskominfo" class="h-7 w-auto object-contain" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.8)) drop-shadow(0 0 1px rgba(255,255,255,0.45));">
            </div>
            <div class="overflow-hidden">
                <span class="font-extrabold text-sm tracking-tight text-white block leading-tight truncate">SIMPATIK &bull; Sinosip</span>
                <span class="text-[9px] text-blue-200/80 font-bold uppercase tracking-wider block mt-0.5 truncate">Diskominfo Bone Bolango</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            {{-- Menu Admin & Kasubag (Role 1 & 2) --}}
            @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                <a href="/surat" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->is('surat*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-envelope-open-text w-5 text-center text-sm {{ request()->is('surat*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Surat Menyurat
                </a>
                <a href="{{ route('kenaikan-pangkat.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('kenaikan-pangkat.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-award w-5 text-center text-sm {{ request()->routeIs('kenaikan-pangkat.*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Kenaikan Pangkat & KGB
                </a>
            @endif

            {{-- Menu Manajemen Aset (Role 1 & 4) --}}
            @if(auth()->check() && (auth()->user()->role_id == 1 || auth()->user()->role_id == 4))
                <a href="{{ route('admin.aset') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.aset*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-center text-sm {{ request()->routeIs('admin.aset*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Manajemen Aset
                </a>
            @endif

            {{-- Menu Kelola Magang (Role 1) --}}
            @if(auth()->check() && auth()->user()->role_id == 1)
                <a href="{{ route('admin.magang') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.magang*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-user-graduate w-5 text-center text-sm {{ request()->routeIs('admin.magang*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Kelola Magang
                </a>
            @endif

            {{-- Menu Pegawai Biasa (Role 3) --}}
            @if(auth()->check() && auth()->user()->role_id == 3)
                <a href="{{ route('dashboard.pegawai') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard.pegawai*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-user-gear w-5 text-center text-sm {{ request()->routeIs('dashboard.pegawai*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Profil & Data Saya
                </a>
            @endif

            {{-- Menu Mahasiswa Magang (Role 5) --}}
            @if(auth()->check() && auth()->user()->role_id == 5)
                <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('mahasiswa.dashboard*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-file-pen w-5 text-center text-sm {{ request()->routeIs('mahasiswa.dashboard*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Portal Magang
                </a>
            @endif

            {{-- Menu Khusus Admin Master (Pengaturan WA & Manajemen User) --}}
            @if(auth()->check() && auth()->user()->role_id == 1)
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-brands fa-whatsapp w-5 text-center text-sm {{ request()->routeIs('admin.settings.*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Pengaturan WhatsApp
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center text-sm {{ request()->routeIs('admin.users.*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Manajemen User
                </a>
            @endif
        </div>

        <div class="p-4 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-2.5 w-full text-left text-slate-300 hover:text-pink-300 hover:bg-white/10 rounded-xl font-medium transition-all">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center text-sm"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <header class="h-20 bg-header-gradient flex items-center justify-between px-8 z-10 shrink-0 no-print">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-black text-navy tracking-tight">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-5">
                <!-- Date Pill -->
                <div class="hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/80 border border-blue-200/60 shadow-sm text-xs font-semibold text-slate-600">
                    <i class="fa-regular fa-calendar-check text-primary"></i>
                    <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="h-8 w-px bg-slate-200 hidden lg:block"></div>
                <!-- User Profile -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-navy leading-tight">{{ Auth::user()->name ?? Auth::user()->nama ?? 'Pengguna' }}</p>
                        <span class="inline-block text-[10px] font-bold text-primary bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/50 mt-0.5">
                            {{ Auth::user()->role->nama ?? 'Pengguna' }}
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-extrabold text-sm shadow-md shadow-blue-500/20 flex items-center justify-center ring-2 ring-white overflow-hidden">
                        {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->nama ?? 'U', 0, 2)) }}
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
