@props(['active' => null, 'subActive' => null])

@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $user = Auth::user();
    $userRole = $user?->role;
    $userNama = $user?->nama ?? 'Pengguna';
    $roleLabel = $user?->role_label ?? 'Pengguna';

    // Deteksi query tab jika ada
    $tabParam = request()->query('tab');
    if (!$tabParam && (request()->has('filter_bulan') || request()->has('filter_tahun'))) {
        $tabParam = 'laporan';
    }

    // Tentukan menu utama aktif
    $activeMenu = $active ?? (
        str_starts_with($currentRoute, 'admin.aset') ? 'aset' : (
            str_starts_with($currentRoute, 'admin.arsip') ? 'arsip' : (
                str_starts_with($currentRoute, 'admin.user') ? 'user' : ''
            )
        )
    );

    // Tentukan sub-item aktif untuk Manajemen Aset
    $activeSub = $subActive;
    if (!$activeSub && $activeMenu === 'aset') {
        if ($currentRoute === 'admin.aset.berita_acara' || $tabParam === 'berita-acara') {
            $activeSub = 'berita-acara';
        } elseif ($currentRoute === 'admin.aset.laporan' || $tabParam === 'laporan') {
            $activeSub = 'laporan';
        } else {
            $activeSub = 'master';
        }
    }
@endphp

<!-- Status Awal Dropdown Manajemen Aset: Mencegah Flash/Kedip Sebelum Alpine Selesai Memuat -->
<script>
    (function() {
        var isOpen = sessionStorage.getItem('sidebar.manajemenAset') === 'true';
        if (isOpen) {
            document.write('<style>#sidebar-sub-aset[x-cloak] { display: block !important; } #sidebar-aset-chevron { transform: rotate(90deg); }</style>');
        }
    })();
</script>

<!-- Sidebar Navigation Terpadu Admin Panel -->
<aside x-data="{
        openAset: (sessionStorage.getItem('sidebar.manajemenAset') === 'true'),
        toggleAset() {
            this.openAset = !this.openAset;
            sessionStorage.setItem('sidebar.manajemenAset', this.openAset ? 'true' : 'false');
        }
    }" 
    class="w-64 bg-sidebar-gradient text-white flex flex-col justify-between p-4 hidden md:flex shrink-0 border-r border-white/10 shadow-sm">
    <div>
        <!-- Header Sidebar (Acuan Utama - Dinamis Sesuai Role User) -->
        <div class="flex items-center space-x-3 px-2 py-3 border-b border-white/15">
            <div>
                <h2 class="font-black text-base leading-tight text-white">{{ $roleLabel }}</h2>
                <span class="text-xs text-[#D1E7DD] font-semibold">Diskominfo Bonebolango</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="mt-6 space-y-2">
            @if(in_array($userRole, ['admin', 'bendahara_barang']))
            <!-- Dropdown Menu: Manajemen Aset (Default Tertutup, Terbuka saat diklik) -->
            <div class="space-y-1">
                <button type="button" 
                        @click="toggleAset()"
                        class="w-full flex items-center justify-between px-4 py-3 {{ $activeMenu === 'aset' ? 'bg-white/15 text-white font-bold shadow-sm border border-white/35' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }} rounded-full transition whitespace-nowrap group focus:outline-none cursor-pointer">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center shrink-0 {{ $activeMenu === 'aset' ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                        <span class="tracking-wide">Manajemen Aset</span>
                    </div>
                    <i id="sidebar-aset-chevron"
                       class="fa-solid fa-chevron-right text-[11px] transition-transform duration-300 ease-in-out ml-2 shrink-0 {{ $activeMenu === 'aset' ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"
                       :class="openAset ? 'rotate-90' : 'rotate-0'"></i>
                </button>

                <!-- Sub-Item Dropdown: Indentasi lebih dalam, ikon lebih kecil, transisi halus -->
                <div id="sidebar-sub-aset"
                     x-show="openAset" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="space-y-1 pt-1 pb-1"
                     x-cloak>
                    <!-- 1. Master Data Aset -->
                    <a href="{{ route('admin.aset', ['tab' => 'master']) }}" 
                       class="flex items-center space-x-3 pl-8 pr-4 py-2.5 text-xs rounded-full transition whitespace-nowrap {{ ($activeMenu === 'aset' && $activeSub === 'master') ? 'bg-white/20 text-white font-bold border border-white/35 shadow-xs' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }}">
                        <i class="fa-solid fa-database w-4 text-center shrink-0 text-[11px] {{ ($activeMenu === 'aset' && $activeSub === 'master') ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                        <span class="tracking-wide">Master Data Aset</span>
                    </a>

                    <!-- 2. Berita Acara (posisi di tengah) -->
                    <a href="{{ route('admin.aset.berita_acara') }}" 
                       class="flex items-center space-x-3 pl-8 pr-4 py-2.5 text-xs rounded-full transition whitespace-nowrap {{ ($activeMenu === 'aset' && $activeSub === 'berita-acara') ? 'bg-white/20 text-white font-bold border border-white/35 shadow-xs' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }}">
                        <i class="fa-solid fa-file-signature w-4 text-center shrink-0 text-[11px] {{ ($activeMenu === 'aset' && $activeSub === 'berita-acara') ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                        <span class="tracking-wide">Berita Acara</span>
                    </a>

                    <!-- 3. Laporan Rekap Bulanan -->
                    <a href="{{ route('admin.aset.laporan') }}" 
                       class="flex items-center space-x-3 pl-8 pr-4 py-2.5 text-xs rounded-full transition whitespace-nowrap {{ ($activeMenu === 'aset' && $activeSub === 'laporan') ? 'bg-white/20 text-white font-bold border border-white/35 shadow-xs' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }}">
                        <i class="fa-solid fa-chart-pie w-4 text-center shrink-0 text-[11px] {{ ($activeMenu === 'aset' && $activeSub === 'laporan') ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                        <span class="tracking-wide">Laporan Rekap Bulanan</span>
                    </a>
                </div>
            </div>
            @endif

            @if($userRole === 'pegawai')
            <a href="{{ route('pegawai.dokumen') }}" 
               class="flex items-center space-x-3 px-4 py-3 {{ $activeMenu === 'arsip' ? 'bg-white/15 text-white font-bold shadow-sm border border-white/35' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }} rounded-full transition whitespace-nowrap group">
                <i class="fa-solid fa-folder-tree w-5 text-center shrink-0 {{ $activeMenu === 'arsip' ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                <span class="tracking-wide">Arsip Dokumen</span>
            </a>
            @endif

            @if(in_array($userRole, ['admin', 'admin_kasubag', 'kasubag']))
            <a href="{{ route('admin.arsip.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 {{ $activeMenu === 'arsip' ? 'bg-white/15 text-white font-bold shadow-sm border border-white/35' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }} rounded-full transition whitespace-nowrap group">
                <i class="fa-solid fa-folder-tree w-5 text-center shrink-0 {{ $activeMenu === 'arsip' ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                <span class="tracking-wide">Arsip Dokumen</span>
            </a>
            @endif

            @if($userRole === 'admin')
            <a href="{{ route('admin.user') }}" 
               class="flex items-center space-x-3 px-4 py-3 {{ $activeMenu === 'user' ? 'bg-white/15 text-white font-bold shadow-sm border border-white/35' : 'text-[#D1E7DD] hover:bg-white/10 hover:text-white font-medium' }} rounded-full transition whitespace-nowrap group">
                <i class="fa-solid fa-users w-5 text-center shrink-0 {{ $activeMenu === 'user' ? 'text-white' : 'text-emerald-200 group-hover:text-white' }}"></i>
                <span class="tracking-wide">Manajemen User</span>
            </a>
            @endif
        </nav>
    </div>

    <!-- Profil Pengguna & Logout -->
    <div class="border-t border-white/15 pt-4">
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white text-[#024d43] rounded-full flex items-center justify-center font-black shadow uppercase shrink-0">
                    {{ substr($userNama, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate max-w-[110px]">{{ $userNama }}</p>
                    <p class="text-xs text-[#D1E7DD] font-medium">
                        {{ $roleLabel }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-1 shrink-0">
                <a href="{{ route('account.security') }}" title="Keamanan Akun & 2FA" class="text-emerald-200 hover:text-white p-2 transition">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0" onsubmit="sessionStorage.removeItem('sidebar.manajemenAset');">
                    @csrf
                    <button type="submit" title="Logout" class="text-emerald-200 hover:text-rose-300 p-2 transition">
                        <i class="fa-solid fa-right-from-bracket text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
