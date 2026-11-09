<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - KOMTARA Diskominfo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Plus Jakarta Sans Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .bg-card-gradient {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(23, 37, 84, 0.05), 0 4px 12px -2px rgba(23, 37, 84, 0.02);
        }
        .bg-header-gradient {
            background: #FFFFFF !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        [x-cloak] { display: none !important; }
        body.overflow-hidden {
            overflow: hidden !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F1F5F9;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
</head>
<body class="app-background font-sans antialiased text-slate-800" 
    :class="{ 'overflow-hidden': showAddModal || showEditModal || showResetModal }"
    x-data="{ 
    showAddModal: false, 
    showEditModal: false, 
    showResetModal: false, 
    editItem: { id: null, nama: '', email: '', role: 'pegawai', instansi_bidang: '' }, 
    resetItem: { id: null, nama: '' } 
}">

    <div class="min-h-screen flex">
        <!-- Sidebar Navigation Terpadu Admin Panel -->
        @include('components.sidebar-admin', ['active' => 'user'])

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-x-hidden">
            <header class="bg-header-gradient px-6 py-4 flex items-center justify-between shadow-xs border-b border-slate-200">
                <div>
                    <h1 class="text-xl font-black text-[#172554] tracking-tight">Manajemen Pengguna Sistem</h1>
                    <p class="text-xs text-slate-500 font-medium">Kelola hak akses, perbarui akun, dan reset kata sandi seluruh pengguna KOMTARA.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('account.security') }}" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-[#172554] text-xs font-bold rounded-xl border border-slate-200 transition flex items-center gap-2 shadow-2xs">
                        <i class="fa-solid fa-shield-halved text-[#198754]"></i>
                        <span class="hidden sm:inline">Keamanan Akun & 2FA</span>
                    </a>
                    <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Pemda Bone Bolango" class="h-8 w-auto mix-blend-multiply">
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Kominfo Bone Bolango" class="h-8 w-auto mix-blend-multiply">
                </div>
            </header>

            <div class="p-6 space-y-6 flex-1">

                @if(session('success'))
                    <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-sm space-y-1 shadow-sm">
                        <div class="flex items-center space-x-2 font-bold">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                            <span>Terdapat Kesalahan Input:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs pl-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Card Daftar User -->
                <div class="bg-card-gradient rounded-3xl p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-[#172554] text-base">Daftar Pengguna Sistem</h3>
                            <p class="text-xs text-slate-500">Kelola akun dan hak akses seluruh pengguna KOMTARA.</p>
                        </div>

                        <!-- Button Tambah User -->
                        <button @click="showAddModal = true" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs px-5 py-2.5 rounded-full font-bold flex items-center space-x-2 shadow-md transition transform hover:-translate-y-0.5 self-start sm:self-auto">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Tambah User Baru</span>
                        </button>
                    </div>

                    <!-- Tabel Daftar User -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white/90">
                        <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                            <thead class="bg-slate-100 text-[#172554] uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3">Pengguna</th>
                                    <th class="px-4 py-3">Email & Instansi</th>
                                    <th class="px-4 py-3 text-center">Peran (Role)</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3">Terdaftar</th>
                                    <th class="px-4 py-3 text-center">Aksi Manajemen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($users as $index => $user)
                                @php
                                    $roleBadge = match($user->role) {
                                        'admin' => 'bg-purple-100 text-purple-800 border border-purple-300 font-black',
                                        'admin_kasubag' => 'bg-amber-100 text-amber-800 border border-amber-300 font-bold',
                                        'bendahara_barang' => 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold',
                                        'pegawai' => 'bg-cyan-100 text-cyan-800 border border-cyan-300 font-bold',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                    $roleLabel = $user->role_label;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition {{ !$user->is_active ? 'bg-slate-50/50 opacity-70' : '' }}">
                                    <td class="px-4 py-3 text-center font-medium">{{ $users->firstItem() + $index }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-[#D1E7DD] text-[#0F5132] font-black flex items-center justify-center text-xs uppercase shadow-xs">
                                                {{ substr($user->nama, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-[#172554] text-xs">{{ $user->nama }}</p>
                                                @if($user->id === Auth::id())
                                                    <span class="text-[10px] text-[#198754] font-extrabold">(Akun Anda)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-mono text-slate-700 font-medium text-xs">{{ $user->email }}</div>
                                        <div class="text-[10px] text-slate-400 font-semibold">{{ $user->instansi_bidang ?: 'Diskominfo Bonebol' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] {{ $roleBadge }}">
                                            {{ $roleLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i>Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                <i class="fa-solid fa-circle-xmark mr-1 text-rose-600"></i>Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 text-[11px]">{{ date('d M Y', strtotime($user->created_at)) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="inline-flex items-center space-x-1">
                                            <!-- Tombol Edit User -->
                                            <button type="button" @click="showEditModal = true; editItem = { id: {{ $user->id }}, nama: '{{ addslashes($user->nama) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}', instansi_bidang: '{{ addslashes($user->instansi_bidang ?? '') }}' }" title="Edit Data User" class="w-7 h-7 rounded-full bg-[#D1E7DD] text-[#0F5132] hover:bg-[#198754] hover:text-white transition flex items-center justify-center">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </button>

                                            <!-- Tombol Reset Password -->
                                            <button type="button" @click="showResetModal = true; resetItem = { id: {{ $user->id }}, nama: '{{ addslashes($user->nama) }}', role: '{{ $user->role }}' }" title="Reset Password" class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center">
                                                <i class="fa-solid fa-key text-xs"></i>
                                            </button>

                                            <!-- Tombol Reset 2FA -->
                                            <form action="{{ route('admin.user.reset_2fa', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin MERESET 2FA untuk akun \'{{ addslashes($user->nama) }}\'? Pengguna wajib melakukan setup 2FA baru saat login berikutnya.')">
                                                @csrf
                                                <button type="submit" title="Reset 2FA Pengguna" class="w-7 h-7 rounded-full bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white transition flex items-center justify-center">
                                                    <i class="fa-solid fa-shield-halved text-xs"></i>
                                                </button>
                                            </form>

                                            @if($user->id !== Auth::id())
                                                <!-- Tombol Nonaktifkan / Aktifkan -->
                                                <form action="{{ route('admin.user.toggle_status', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun user ini?')">
                                                    @csrf
                                                    <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}" class="w-7 h-7 rounded-full {{ $user->is_active ? 'bg-slate-100 text-slate-600 hover:bg-slate-700 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white' }} transition flex items-center justify-center">
                                                        <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                                    </button>
                                                </form>

                                                <!-- Tombol Hapus User -->
                                                <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN akun {{ addslashes($user->nama) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Hapus Pengguna" class="w-7 h-7 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition flex items-center justify-center">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada data pengguna yang terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Navigasi Pagination Laravel -->
                    <div class="pt-2">
                        {{ $users->links() }}
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- MODAL 1: TAMBAH USER BARU -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col border border-slate-200 overflow-hidden" @click.away="showAddModal = false">
            <!-- Header (Fixed) -->
            <div class="shrink-0 flex justify-between items-center px-6 py-4 border-b border-slate-100 bg-white">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </div>
                    <span>Tambah Pengguna Baru</span>
                </h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.user.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <!-- Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" required placeholder="Contoh: Budi Santoso, S.Kom" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="email@bonebolangokab.go.id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#172554] mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                            <select name="role" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                <option value="admin">Admin Master</option>
                                <option value="admin_kasubag">Admin Kasubag</option>
                                <option value="bendahara_barang">Bendahara Barang</option>
                                <option value="pegawai">Pegawai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#172554] mb-1">Instansi / Bidang</label>
                            <input type="text" name="instansi_bidang" placeholder="Diskominfo Bonebol" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Kata Sandi Awal <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                    </div>
                </div>

                <!-- Footer (Sticky) -->
                <div class="shrink-0 px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-2 rounded-b-3xl">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-200/80 transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs font-bold py-2.5 px-6 rounded-xl shadow-md transition">
                        <i class="fa-solid fa-save mr-1"></i> Simpan User Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT DATA USER -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col border border-slate-200 overflow-hidden" @click.away="showEditModal = false">
            <!-- Header (Fixed) -->
            <div class="shrink-0 flex justify-between items-center px-6 py-4 border-b border-slate-100 bg-white">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center">
                        <i class="fa-solid fa-user-pen text-xs"></i>
                    </div>
                    <span>Edit Data Pengguna</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/users') }}/' + editItem.id" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')

                <!-- Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" x-model="editItem.nama" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="editItem.email" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#172554] mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                            <select name="role" x-model="editItem.role" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                <option value="admin">Admin Master</option>
                                <option value="admin_kasubag">Admin Kasubag</option>
                                <option value="bendahara_barang">Bendahara Barang</option>
                                <option value="pegawai">Pegawai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#172554] mb-1">Instansi / Bidang</label>
                            <input type="text" name="instansi_bidang" x-model="editItem.instansi_bidang" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-[#198754] focus:outline-none">
                        </div>
                    </div>

                    <div class="bg-amber-50 p-3 rounded-xl border border-amber-200">
                        <label class="block text-xs font-bold text-amber-900 mb-1">Ganti Password (Opsional)</label>
                        <input type="password" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah password" class="w-full px-3.5 py-2 bg-white border border-amber-300 rounded-lg text-xs font-medium text-[#172554] focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Footer (Sticky) -->
                <div class="shrink-0 px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-2 rounded-b-3xl">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-200/80 transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs font-bold py-2.5 px-6 rounded-xl shadow-md transition">
                        <i class="fa-solid fa-save mr-1"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: RESET PASSWORD USER -->
    <div x-show="showResetModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-3xl border border-amber-200 shadow-2xl max-w-md w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col overflow-hidden" @click.away="showResetModal = false">
            <!-- Header (Fixed) -->
            <div class="shrink-0 flex justify-between items-center px-6 py-4 border-b border-slate-100 bg-white">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-key text-xs"></i>
                    </div>
                    <span>Reset Kata Sandi</span>
                </h3>
                <button @click="showResetModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/users') }}/' + resetItem.id + '/reset-password'" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf

                <!-- Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-3.5">
                    <p class="text-xs text-slate-600">
                        Atur kata sandi baru untuk pengguna: <strong class="text-[#172554]" x-text="resetItem.nama"></strong>
                    </p>

                    <div x-show="resetItem.role === 'pegawai'" class="p-3 bg-[#D1E7DD]/50 border border-[#A3CFBB] rounded-xl text-[11px] text-[#0F5132] flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-xs mt-0.5 shrink-0 text-[#0F5132]"></i>
                        <span>Akun Pegawai ini akan <strong>wajib mengganti kata sandi</strong> saat login pertama kali setelah reset.</span>
                    </div>

                    <div x-show="resetItem.role !== 'pegawai'" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-xs mt-0.5 shrink-0 text-slate-500"></i>
                        <span>Akun peran ini dapat langsung login dengan kata sandi baru tanpa dipaksa ganti kata sandi.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172554] mb-1">Ulangi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required placeholder="Ketik ulang kata sandi baru" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Footer (Sticky) -->
                <div class="shrink-0 px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-2 rounded-b-3xl">
                    <button type="button" @click="showResetModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-200/80 transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2.5 px-6 rounded-xl shadow-md transition">
                        <i class="fa-solid fa-key mr-1"></i> Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>