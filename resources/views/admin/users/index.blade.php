@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="userManagement()">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#172554] tracking-tight">Manajemen User & Hak Akses</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun pengguna, hak akses peran (Admin, Kasubag, Pegawai, Bendahara, Mahasiswa), status aktif, dan reset password.</p>
        </div>
        <button @click="showAddModal = true" class="btn-pill-primary px-5 py-2.5 text-xs font-bold shadow-lg flex items-center space-x-2 cursor-pointer transition transform hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>Tambah User Baru</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl text-xs sm:text-sm flex items-center space-x-2 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-xs sm:text-sm flex items-center space-x-2 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-xs sm:text-sm space-y-1 shadow-sm">
            <div class="flex items-center space-x-2 font-bold">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                <span>Terdapat Kesalahan Input:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Ringkasan Statistik Pengguna (5 Roles + Total) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total -->
        <div class="p-4 rounded-2xl bg-white/90 border border-blue-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Total Pengguna</span>
            <div class="flex items-baseline justify-between mt-1">
                <p class="text-2xl font-black text-[#172554]">{{ $roleCounts['total'] ?? 0 }}</p>
                <div class="w-7 h-7 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <!-- Admin Master -->
        <div class="p-4 rounded-2xl bg-purple-50/80 border border-purple-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-purple-700">Admin Master</span>
            <div class="flex items-baseline justify-between mt-1">
                <p class="text-2xl font-black text-purple-800">{{ $roleCounts['admin'] ?? 0 }}</p>
                <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
        </div>

        <!-- Kasubag -->
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-700">Admin Kasubag</span>
            <div class="flex items-baseline justify-between mt-1">
                <p class="text-2xl font-black text-amber-800">{{ $roleCounts['admin_kasubag'] ?? 0 }}</p>
                <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>
        </div>

        <!-- Bendahara Barang -->
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">Bendahara</span>
            <div class="flex items-baseline justify-between mt-1">
                <p class="text-2xl font-black text-emerald-800">{{ $roleCounts['bendahara_barang'] ?? 0 }}</p>
                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>

        <!-- Pegawai -->
        <div class="p-4 rounded-2xl bg-cyan-50/80 border border-cyan-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-cyan-700">Pegawai</span>
            <div class="flex items-baseline justify-between mt-1">
                <p class="text-2xl font-black text-cyan-800">{{ $roleCounts['pegawai'] ?? 0 }}</p>
                <div class="w-7 h-7 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
            </div>
        </div>

        <!-- Mahasiswa -->
        <div class="p-4 rounded-2xl bg-pink-50/80 border border-pink-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-pink-700">Mahasiswa</span>
            <div class="flex items-baseline justify-between mt-1">
                <p class="text-2xl font-black text-[#EC4899]">{{ $roleCounts['mahasiswa'] ?? 0 }}</p>
                <div class="w-7 h-7 rounded-full bg-pink-100 text-[#EC4899] flex items-center justify-center text-xs">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Utama: Filter Toolbar & Tabel -->
    <div class="bg-card-gradient rounded-3xl p-6 space-y-4 border border-blue-200/50 shadow-xl shadow-blue-900/5">
        <!-- Toolbar Filter & Search -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-blue-100/80 pb-4">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, email, NIP, instansi..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-full text-xs font-semibold text-[#172554] focus:ring-2 focus:ring-[#3B82F6] focus:outline-none">
                </div>

                <select name="role" class="text-xs border border-slate-300 rounded-full px-3 py-2 bg-white font-semibold text-[#172554] focus:ring-2 focus:ring-[#3B82F6]">
                    <option value="all" {{ ($filterRole ?? 'all') === 'all' ? 'selected' : '' }}>Semua Role</option>
                    <option value="1" {{ ($filterRole ?? '') === '1' || ($filterRole ?? '') === 'admin' ? 'selected' : '' }}>Admin Master</option>
                    <option value="2" {{ ($filterRole ?? '') === '2' || ($filterRole ?? '') === 'admin_kasubag' ? 'selected' : '' }}>Admin Kasubag</option>
                    <option value="4" {{ ($filterRole ?? '') === '4' || ($filterRole ?? '') === 'bendahara_barang' ? 'selected' : '' }}>Bendahara Barang</option>
                    <option value="3" {{ ($filterRole ?? '') === '3' || ($filterRole ?? '') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    <option value="5" {{ ($filterRole ?? '') === '5' || ($filterRole ?? '') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Magang</option>
                </select>

                <select name="status" class="text-xs border border-slate-300 rounded-full px-3 py-2 bg-white font-semibold text-[#172554] focus:ring-2 focus:ring-[#3B82F6]">
                    <option value="all" {{ ($filterStatus ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="1" {{ ($filterStatus ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ ($filterStatus ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="bg-[#3B82F6] hover:bg-blue-700 text-white text-xs px-4 py-2 rounded-full font-bold flex items-center space-x-1.5 shadow-sm transition">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>

                @if($search || ($filterRole && $filterRole !== 'all') || ($filterStatus && $filterStatus !== 'all'))
                    <a href="{{ route('admin.users') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-3 py-2 hover:underline flex items-center space-x-1">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <div class="text-xs text-slate-500 font-medium">
                Menampilkan <strong class="text-[#172554]">{{ $users->total() }}</strong> pengguna
            </div>
        </div>
        <!-- Tabel Daftar Pengguna -->
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white/90">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="bg-slate-100 text-[#172554] uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-center">No</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Email / NIP Login</th>
                        <th class="px-4 py-3">Instansi / Bidang</th>
                        <th class="px-4 py-3 text-center">Peran (Role)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Terdaftar</th>
                        <th class="px-4 py-3 text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($users as $index => $user)
                    @php
                        $roleId = $user->role_id;
                        $roleBadge = match($roleId) {
                            1 => 'bg-purple-100 text-purple-800 border border-purple-300 font-black',
                            2 => 'bg-amber-100 text-amber-800 border border-amber-300 font-bold',
                            3 => 'bg-cyan-100 text-cyan-800 border border-cyan-300 font-bold',
                            4 => 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold',
                            5 => 'bg-pink-100 text-[#EC4899] border border-pink-300 font-bold',
                            default => 'bg-slate-100 text-slate-700'
                        };
                        $roleLabel = $user->role->nama ?? match($roleId) {
                            1 => 'Admin Master',
                            2 => 'Admin Kasubag',
                            3 => 'Pegawai',
                            4 => 'Bendahara Barang',
                            5 => 'Mahasiswa Magang',
                            default => 'User ' . $roleId
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ !$user->is_active ? 'bg-slate-50/50 opacity-70' : '' }}">
                        <td class="px-4 py-3 text-center font-medium">{{ $users->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-[#3B82F6] font-black flex items-center justify-center text-xs uppercase shadow-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[#172554] text-xs">{{ $user->name }}</p>
                                    @if($user->id === Auth::id())
                                        <span class="text-[10px] text-[#3B82F6] font-extrabold">(Akun Anda)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-700 font-medium text-xs">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $user->instansi_bidang ?: 'Diskominfo Bonebol' }}</td>
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
                            <div class="inline-flex items-center space-x-1.5">
                                <!-- Tombol Edit User -->
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($user) }})" 
                                        title="Edit Data User" 
                                        class="w-7 h-7 rounded-full bg-blue-50 text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white transition flex items-center justify-center cursor-pointer">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>

                                <!-- Tombol Reset Password -->
                                <button type="button" 
                                        @click="openResetModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')" 
                                        title="Reset Password" 
                                        class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center cursor-pointer">
                                    <i class="fa-solid fa-key text-xs"></i>
                                </button>

                                @if($user->id !== Auth::id())
                                    <!-- Tombol Toggle Status -->
                                    <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun user ini?')">
                                        @csrf
                                        <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}" class="w-7 h-7 rounded-full {{ $user->is_active ? 'bg-slate-100 text-slate-600 hover:bg-slate-700 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white' }} transition flex items-center justify-center cursor-pointer">
                                            <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus User -->
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN akun {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Pengguna" class="w-7 h-7 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition flex items-center justify-center cursor-pointer">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada data pengguna yang sesuai filter.</td>
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

    <!-- MODAL 1: TAMBAH USER BARU -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-card-gradient rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-blue-200/80" @click.away="showAddModal = false">
            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </div>
                    <span>Tambah Pengguna Baru</span>
                </h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso, S.Kom" class="form-input">
                </div>

                <div>
                    <label class="form-label">Email / NIP Login <span class="text-rose-500">*</span></label>
                    <input type="text" name="identity" required placeholder="NIP (198...) atau Email..." class="form-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Peran (Role) <span class="text-rose-500">*</span></label>
                        <select name="role_id" required class="form-input">
                            <option value="1">Admin Master</option>
                            <option value="2">Admin Kasubag</option>
                            <option value="4">Bendahara Barang</option>
                            <option value="3" selected>Pegawai</option>
                            <option value="5">Mahasiswa Magang</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Instansi / Bidang</label>
                        <input type="text" name="instansi_bidang" placeholder="Diskominfo Bonebol" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">Password Awal <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="form-input">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-full text-xs font-bold text-slate-500 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                        Simpan User Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT DATA USER -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-card-gradient rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-blue-200/80" @click.away="showEditModal = false">
            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center">
                        <i class="fa-solid fa-user-pen text-xs"></i>
                    </div>
                    <span>Edit Data Pengguna</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/users') }}/' + editItem.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editItem.name" required class="form-input">
                </div>

                <div>
                    <label class="form-label">Email / NIP Login <span class="text-rose-500">*</span></label>
                    <input type="text" name="email" x-model="editItem.email" required class="form-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Peran (Role) <span class="text-rose-500">*</span></label>
                        <select name="role_id" x-model="editItem.role_id" required class="form-input">
                            <option value="1">Admin Master</option>
                            <option value="2">Admin Kasubag</option>
                            <option value="4">Bendahara Barang</option>
                            <option value="3">Pegawai</option>
                            <option value="5">Mahasiswa Magang</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Instansi / Bidang</label>
                        <input type="text" name="instansi_bidang" x-model="editItem.instansi_bidang" placeholder="Diskominfo Bonebol" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">Kata Sandi Baru <span class="text-xs text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" class="form-input">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-full text-xs font-bold text-slate-500 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Reset Password -->
    <div x-show="showResetModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeResetModal()">
        
        <!-- Backdrop -->
        <div x-show="showResetModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="closeResetModal()"></div>

        <!-- Modal Dialog -->
        <div x-show="showResetModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-card-gradient rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-blue-200/60 space-y-6 z-10">
            
            <div class="flex items-start justify-between border-b border-blue-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy">Konfirmasi Reset Password</h3>
                        <p class="text-xs text-slate-500 font-medium">Ganti password akun pengguna</p>
                    </div>
                </div>
                <button type="button" 
                        @click="closeResetModal()" 
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="'/admin/users/' + targetUser.id + '/reset-password'" method="POST" class="space-y-4">
                @csrf
                <div class="bg-amber-50/60 p-4 rounded-2xl border border-amber-200/60 text-xs text-amber-900 leading-relaxed">
                    Reset password untuk <strong class="font-bold text-navy" x-text="targetUser.name"></strong> (<span class="font-mono text-slate-600" x-text="targetUser.email"></span>)? Password lama akan digantikan dengan password baru.
                </div>

                <div>
                    <label class="form-label text-xs">Password Baru (Opsional)</label>
                    <input type="text" name="custom_password" placeholder="Kosongkan untuk generate otomatis (8 karakter)..." class="form-input text-xs font-mono">
                    <p class="text-[11px] text-slate-400 mt-1 font-medium">Jika dikosongkan, sistem akan membuat password acak 8 karakter kombinasi huruf dan angka secara otomatis.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-blue-100">
                    <button type="button" 
                            @click="closeResetModal()" 
                            class="btn-pill-secondary px-4 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="btn-pill-primary px-5 py-2 text-xs font-bold shadow-md cursor-pointer !bg-gradient-to-r !from-amber-500 !to-orange-500 hover:!from-amber-600 hover:!to-orange-600">
                        Ya, Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hasil Reset Password (Tampil Sekali Saja) -->
    @if(session('reset_success'))
    <div x-data="{ showResult: true, copied: false }" 
         x-show="showResult" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="showResult = false">
        
        <!-- Backdrop -->
        <div x-show="showResult" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="showResult = false"></div>

        <!-- Modal Dialog -->
        <div x-show="showResult" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-card-gradient rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-emerald-200 space-y-6 z-10">
            
            <div class="flex items-start justify-between border-b border-emerald-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy">Password Berhasil Direset!</h3>
                        <p class="text-xs text-slate-500 font-medium">Informasi kredensial login baru</p>
                    </div>
                </div>
                <button type="button" 
                        @click="showResult = false" 
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="bg-white/80 p-4 rounded-2xl border border-blue-100/80 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-bold uppercase tracking-wider">Nama Pengguna</span>
                        <span class="font-bold text-navy">{{ session('reset_success.name') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-bold uppercase tracking-wider">Email / NIP Login</span>
                        <span class="font-mono font-bold text-primary">{{ session('reset_success.email') }}</span>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-pink-50 p-4 rounded-2xl border border-blue-200/60 space-y-2">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Password Baru:</span>
                    <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-xl border border-blue-200/80">
                        <span class="font-mono text-base font-extrabold text-navy tracking-wider select-all">{{ session('reset_success.password') }}</span>
                        <button type="button" 
                                @click="navigator.clipboard.writeText('{{ session('reset_success.password') }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                class="btn-pill-primary px-3 py-1.5 text-xs font-bold gap-1 shadow-sm cursor-pointer">
                            <template x-if="!copied">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    Salin
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1 text-white font-bold">
                                    <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Tersalin!
                                </span>
                            </template>
                        </button>
                    </div>
                </div>

                <div class="p-3.5 bg-amber-50/80 rounded-2xl border border-amber-200/60 text-[11px] text-amber-900 leading-relaxed space-y-1">
                    <p class="font-bold flex items-center gap-1 text-amber-800">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Penting: Password ini hanya tampil satu kali!
                    </p>
                    <p class="text-slate-600">Setelah dialog ini ditutup, password tidak dapat dilihat kembali demi alasan keamanan. Segera salin dan informasikan kepada pengguna yang bersangkutan.</p>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-emerald-100">
                <button type="button" 
                        @click="showResult = false" 
                        class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md cursor-pointer">
                    Saya Sudah Menyimpan Password
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        function userManagement() {
            return {
                showAddModal: false,
                showEditModal: false,
                showResetModal: false,
                editItem: { id: null, name: '', email: '', role_id: 3, instansi_bidang: '' },
                targetUser: { id: null, name: '', email: '' },
                openEditModal(user) {
                    this.editItem = {
                        id: user.id,
                        name: user.name,
                        email: user.email,
                        role_id: user.role_id,
                        instansi_bidang: user.instansi_bidang || ''
                    };
                    this.showEditModal = true;
                },
                openResetModal(id, name, email) {
                    this.targetUser = { id: id, name: name, email: email };
                    this.showResetModal = true;
                },
                closeResetModal() {
                    this.showResetModal = false;
                    this.targetUser = { id: null, name: '', email: '' };
                }
            };
        }
    </script>
</div>
@endsection