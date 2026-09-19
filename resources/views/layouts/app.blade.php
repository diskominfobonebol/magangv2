<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sinosip') - Sistem Informasi Notifikasi Kenaikan Pangkat & Persuratan</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        @media print {
            .no-print, aside, header, .btn, button {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="app-background antialiased flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-sidebar-gradient text-white flex flex-col hidden md:flex h-full z-20 shrink-0 no-print">
        <div class="h-20 flex items-center px-6 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg text-white mr-3 shadow-md shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
            <div>
                <span class="font-extrabold text-lg tracking-wider text-white block leading-tight">SINOSIP</span>
                <span class="text-[10px] text-slate-400 font-medium tracking-widest block uppercase">Diskominfo Bonebol</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            {{-- Menu Khusus Admin & Kasubag (Role 1 & 2) --}}
            @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                @php
                    $isSuratActive = request()->is('surat*');
                    $isSuratKeluarActive = request()->routeIs('surat.keluar') || request()->routeIs('surat.index') || request()->routeIs('surat.create*') || request()->routeIs('surat.show*') || request()->routeIs('surat.edit*') || request()->routeIs('surat.rekap*') || (request()->is('surat*') && !request()->is('surat/masuk*') && !request()->is('surat/telaah*') && !request()->is('surat/sk*'));
                    $isSuratMasukActive = request()->routeIs('surat.masuk') || request()->routeIs('surat-masuk.*') || request()->is('surat/masuk*');
                    $isSuratTelaahActive = request()->routeIs('surat.telaah*') || request()->is('surat/telaah*');
                    $isSuratSkActive = request()->routeIs('surat.sk*') || request()->is('surat/sk*');
                @endphp

                <!-- Menu Collapsible: Surat Menyurat -->
                <div x-data="{ openSurat: {{ $isSuratActive ? 'true' : 'false' }} }" class="space-y-1">
                    <button type="button" 
                            @click="openSurat = !openSurat" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all cursor-pointer {{ $isSuratActive ? 'sidebar-link-active' : 'sidebar-link' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ $isSuratActive ? 'text-blue-300' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <span class="font-medium text-sm">Surat Menyurat</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 transition-transform duration-200" 
                             :class="openSurat ? 'rotate-180' : ''" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Sub Menu Items (4 Sub Menu) -->
                    <div x-show="openSurat" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-white/15 ml-5 mt-1">
                        
                        <!-- a. Surat Keluar (SPT & SPPD) -->
                        <a href="{{ route('surat.keluar') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ $isSuratKeluarActive ? 'bg-white/15 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isSuratKeluarActive ? 'bg-pink-400 ring-2 ring-pink-400/40' : 'bg-slate-400' }}"></span>
                            <span>Surat Keluar (SPT & SPPD)</span>
                        </a>

                        <!-- b. Surat Masuk -->
                        <a href="{{ route('surat.masuk') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ $isSuratMasukActive ? 'bg-white/15 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isSuratMasukActive ? 'bg-blue-400 ring-2 ring-blue-400/40' : 'bg-slate-400' }}"></span>
                            <span>Surat Masuk</span>
                        </a>

                        <!-- c. Surat Telaah -->
                        <a href="{{ route('surat.telaah') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ $isSuratTelaahActive ? 'bg-white/15 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isSuratTelaahActive ? 'bg-amber-400 ring-2 ring-amber-400/40' : 'bg-slate-400' }}"></span>
                            <span>Surat Telaah</span>
                        </a>

                        <!-- d. Surat SK -->
                        <a href="{{ route('surat.sk') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ $isSuratSkActive ? 'bg-white/15 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isSuratSkActive ? 'bg-emerald-400 ring-2 ring-emerald-400/40' : 'bg-slate-400' }}"></span>
                            <span>Surat SK</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('kenaikan-pangkat.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('kenaikan-pangkat.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('kenaikan-pangkat.*') ? 'text-blue-300' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Kenaikan Pangkat & Gaji Berkala
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

            {{-- Menu Khusus Pegawai Biasa (Role 3) --}}
            @if(auth()->check() && auth()->user()->role_id == 3)
                <a href="{{ route('dashboard.pegawai') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard.pegawai') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard.pegawai') ? 'text-blue-300' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profil & Data Saya
                </a>
            @endif

            {{-- Menu Khusus Mahasiswa Magang (Role 5) --}}
            @if(auth()->check() && auth()->user()->role_id == 5)
                <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('mahasiswa.dashboard*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <i class="fa-solid fa-graduation-cap w-5 text-center text-sm {{ request()->routeIs('mahasiswa.dashboard*') ? 'text-blue-300' : 'text-slate-400' }}"></i>
                    Portal Magang
                </a>
            @endif

            {{-- Menu Khusus Admin Master --}}
            @if(auth()->check() && (auth()->user()->role_id == 1 || auth()->user()->role === 'master'))
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.settings.*') ? 'text-blue-300' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Pengaturan WhatsApp
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-blue-300' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Manajemen User
                </a>
            @endif
        </div>

        <div class="p-4 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-3 w-full text-left text-slate-300 hover:text-pink-300 hover:bg-white/10 rounded-xl font-medium transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <header class="h-20 bg-header-gradient flex items-center justify-between px-8 z-10 shrink-0 no-print">
            <h1 class="text-xl font-bold text-navy">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-navy">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-500">{{ Auth::user()->role->nama ?? 'Pegawai' }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-200 border-2 border-white shadow flex items-center justify-center overflow-hidden ring-2 ring-blue-100">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=DBEAFE&color=1E40AF" alt="Avatar">
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
