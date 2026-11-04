<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Sinosip Kominfo Bone Bolango</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body class="app-background antialiased flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-sidebar-gradient text-white flex flex-col hidden md:flex h-full z-20">
        <div class="h-20 flex items-center px-6 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg text-white mr-3 shadow-md shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
            <div>
                <span class="font-bold text-xl tracking-tight text-white block leading-tight">Sinosip</span>
                <span class="text-[10px] text-blue-200/80 font-medium">Kominfo Bone Bolango</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            {{-- Menu Khusus Admin & Kasubag (Role 1 & 2) --}}
            @if(auth()->check() && (auth()->user()->role_id == 1 || auth()->user()->role_id == 2))
                @php
                    $isSuratActive = request()->is('surat*');
                    $isSuratKeluarActive = request()->routeIs('surat.keluar') || request()->routeIs('surat.index') || request()->routeIs('surat.create*') || request()->routeIs('surat.show*') || request()->routeIs('surat.edit*') || request()->routeIs('surat.rekap*') || (request()->is('surat*') && !request()->is('surat/masuk*') && !request()->is('surat/telaah*') && !request()->is('surat/sk*'));
                    $isSuratMasukActive = request()->routeIs('surat.masuk') || request()->routeIs('surat-masuk.*') || request()->is('surat/masuk*');
                    $isSuratTelaahActive = request()->routeIs('surat.telaah*') || request()->is('surat/telaah*');
                    $isSuratSkActive = request()->routeIs('surat.sk*') || request()->is('surat/sk*');
                @endphp

                <!-- Dropdown Kelola Surat -->
                <div x-data="{ openSurat: {{ $isSuratActive ? 'true' : 'false' }} }" class="space-y-1">
                    <button type="button" 
                            @click="openSurat = !openSurat" 
                            class="flex items-center justify-between w-full px-4 py-3 rounded-xl transition-all {{ $isSuratActive ? 'sidebar-link-active' : 'sidebar-link' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ $isSuratActive ? 'text-blue-300' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium text-sm">Kelola Surat</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" 
                             :class="openSurat ? 'rotate-180 text-white' : 'text-slate-400'" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Sub-menu Surat -->
                    <div x-show="openSurat" 
                         x-collapse
                         class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-white/20 ml-5 my-1">
                        
                        <!-- a. Surat Keluar (SPT & SPPD) -->
                        <a href="{{ route('surat.keluar') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ $isSuratKeluarActive ? 'bg-white/15 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isSuratKeluarActive ? 'bg-cyan-400 ring-2 ring-cyan-400/40' : 'bg-slate-400' }}"></span>
                            <span>Surat Keluar</span>
                        </a>

                        <!-- b. Surat Masuk -->
                        <a href="{{ route('surat.masuk') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ $isSuratMasukActive ? 'bg-white/15 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isSuratMasukActive ? 'bg-pink-400 ring-2 ring-pink-400/40' : 'bg-slate-400' }}"></span>
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
                <button type="submit" class="flex items-center gap-3 px-4 py-3 w-full text-left text-slate-300 hover:text-pink-300 hover:bg-white/10 rounded-xl font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <header class="h-20 bg-header-gradient flex items-center justify-between px-8 z-10">
            <h1 class="text-xl font-bold text-navy">@yield('title', 'Dashboard')</h1>
            
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Notification Bell Component -->
                <div class="relative" x-data="headerNotifications()" @click.outside="open = false">
                    <button type="button" 
                            @click="toggleDropdown()" 
                            class="relative p-2.5 rounded-2xl text-slate-500 hover:text-navy hover:bg-white/60 transition-colors cursor-pointer focus:outline-none"
                            title="Notifikasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <!-- Badge Unread -->
                        <span x-show="unreadCount > 0" 
                              x-cloak 
                              class="absolute top-1.5 right-1.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-rose-500 rounded-full ring-2 ring-white shadow-sm"
                              x-text="unreadCount > 99 ? '99+' : unreadCount">
                        </span>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-blue-100 py-0 z-50 overflow-hidden divide-y divide-slate-100">
                        
                        <!-- Header Dropdown -->
                        <div class="p-3.5 bg-slate-50/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-navy">Notifikasi</span>
                                <span x-show="unreadCount > 0" 
                                      class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700" 
                                      x-text="unreadCount + ' Baru'"></span>
                            </div>
                            <template x-if="unreadCount > 0">
                                <button type="button" 
                                        @click="markAllRead()" 
                                        class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                                    Tandai semua dibaca
                                </button>
                            </template>
                        </div>

                        <!-- List Konten Dropdown -->
                        <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-100">
                            <template x-if="loading">
                                <div class="py-8 text-center text-slate-400">
                                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-blue-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span class="text-xs">Memuat notifikasi...</span>
                                </div>
                            </template>

                            <template x-if="!loading && notifications.length === 0">
                                <div class="py-10 px-4 text-center">
                                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600">Belum ada notifikasi</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Semua pemberitahuan sistem akan tampil di sini</p>
                                </div>
                            </template>

                            <template x-if="!loading && notifications.length > 0">
                                <div>
                                    <template x-for="item in notifications" :key="item.id">
                                        <div @click="markAsRead(item)" 
                                             class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition-colors cursor-pointer relative group"
                                             :class="!item.is_read ? 'bg-blue-50/40' : ''">
                                            
                                            <!-- Dot & Icon -->
                                            <div class="mt-1 flex-shrink-0">
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                                                     :class="!item.is_read ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="flex-1 min-w-0 pr-6">
                                                <div class="flex items-center gap-1.5">
                                                    <p class="text-xs font-bold leading-tight" 
                                                       :class="!item.is_read ? 'text-navy' : 'text-slate-700'"
                                                       x-text="item.title"></p>
                                                    <span x-show="!item.is_read" class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                                </div>
                                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-snug" x-text="item.message"></p>
                                                <span class="text-[10px] text-slate-400 mt-1.5 block font-medium" x-text="item.created_at_human"></span>
                                            </div>

                                            <!-- Tombol Hapus -->
                                            <button type="button" 
                                                    @click="deleteItem(item, $event)" 
                                                    class="absolute right-3 top-3 opacity-0 group-hover:opacity-100 text-slate-300 hover:text-rose-500 p-1 rounded-md transition-all"
                                                    title="Hapus notifikasi">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Footer Dropdown -->
                        <a href="{{ route('notifications.index') }}" 
                           class="block py-2.5 text-center text-xs font-semibold text-blue-600 hover:text-blue-800 bg-slate-50/70 hover:bg-slate-100 transition-colors">
                            Lihat Semua Notifikasi &rarr;
                        </a>
                    </div>
                </div>

                <!-- Profile Dropdown (Header Menu) -->
                <div class="relative" x-data="{ openProfile: false }" @click.outside="openProfile = false">
                <button type="button" 
                        @click="openProfile = !openProfile" 
                        class="flex items-center gap-3 p-1.5 rounded-2xl hover:bg-white/60 transition-colors cursor-pointer focus:outline-none">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-navy leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ Auth::user()->role->nama ?? 'Pegawai' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-200 border-2 border-white shadow flex items-center justify-center overflow-hidden ring-2 ring-blue-100">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=DBEAFE&color=1E40AF" alt="Avatar">
                    </div>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 hidden sm:block" 
                         :class="openProfile ? 'rotate-180' : ''" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="openProfile" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-64 bg-card-gradient rounded-2xl shadow-xl border border-blue-200/60 py-2 z-50 divide-y divide-blue-100/60">
                    
                    <div class="px-4 py-3">
                        <p class="text-xs font-bold text-navy truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] font-mono text-slate-500 truncate">{{ Auth::user()->email }}</p>
                        <div class="mt-1.5">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full 
                                {{ Auth::user()->role_id == 1 ? 'badge-pink' : (Auth::user()->role_id == 2 ? 'badge-blue' : 'badge-navy') }}">
                                {{ Auth::user()->role->nama ?? 'Pegawai' }}
                            </span>
                        </div>
                    </div>

                    <div class="py-1">
                        <a href="{{ route('account.security') }}" 
                           class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:text-navy hover:bg-white/60 transition-colors">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>Keamanan Akun & 2FA</span>
                        </a>
                    </div>

                    <div class="py-1">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </div>
    </main>

    <script>
    function headerNotifications() {
        return {
            open: false,
            unreadCount: {{ Auth::check() ? Auth::user()->unreadNotifications()->count() : 0 }},
            notifications: [],
            loading: false,
            pollTimer: null,

            init() {
                this.startPolling();
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') {
                        this.fetchUnreadCount();
                        this.startPolling();
                    } else {
                        this.stopPolling();
                    }
                });
            },

            startPolling() {
                this.stopPolling();
                this.pollTimer = setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        this.fetchUnreadCount();
                    }
                }, 60000); // 60 detik polling ringan
            },

            stopPolling() {
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;
                }
            },

            fetchUnreadCount() {
                fetch('{{ route('notifications.unread-count') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (typeof data.unread_count !== 'undefined') {
                        this.unreadCount = data.unread_count;
                    }
                })
                .catch(() => {});
            },

            toggleDropdown() {
                this.open = !this.open;
                if (this.open) {
                    this.fetchRecent();
                }
            },

            fetchRecent() {
                this.loading = true;
                fetch('{{ route('notifications.recent') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.notifications = data.notifications || [];
                    if (typeof data.unread_count !== 'undefined') {
                        this.unreadCount = data.unread_count;
                    }
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                });
            },

            markAsRead(item) {
                if (!item.is_read) {
                    fetch('{{ url('/notifikasi') }}/' + item.id + '/read', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        item.is_read = true;
                        if (typeof data.unread_count !== 'undefined') {
                            this.unreadCount = data.unread_count;
                        } else {
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                    })
                    .catch(() => {});
                }
                if (item.url && item.url !== '#') {
                    window.location.href = item.url;
                }
            },

            markAllRead() {
                fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(() => {
                    this.unreadCount = 0;
                    this.notifications.forEach(n => n.is_read = true);
                })
                .catch(() => {});
            },

            deleteItem(item, event) {
                event.stopPropagation();
                fetch('{{ url('/notifikasi') }}/' + item.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.notifications = this.notifications.filter(n => n.id !== item.id);
                    if (typeof data.unread_count !== 'undefined') {
                        this.unreadCount = data.unread_count;
                    }
                })
                .catch(() => {});
            }
        };
    }
    </script>
</body>
</html>
