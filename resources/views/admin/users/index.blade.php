@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="userManagement()">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="text-2xl font-bold text-navy">Manajemen User & Pegawai</h1>
            <p class="text-sm text-slate-500">Kelola akun login, NIP, email, dan hak akses pengguna aplikasi.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Tambah User / Pegawai -->
    <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50">
        <h2 class="text-lg font-bold text-navy mb-4">Tambah User Baru</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Nama lengkap..." class="form-input">
            </div>
            <div>
                <label class="form-label">Email / NIP</label>
                <input type="text" name="identity" required placeholder="NIP atau Email..." class="form-input">
            </div>
            <div>
                <label class="form-label">Password Default</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="form-input">
            </div>
            <div>
                <label class="form-label">Role / Hak Akses</label>
                <select name="role_id" class="form-input">
                    <option value="1">Admin Master</option>
                    <option value="2">Kasubag / Operator</option>
                    <option value="3">Pegawai</option>
                    <option value="4">Bendahara Barang</option>
                    <option value="5">Mahasiswa</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end pt-2">
                <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                    Simpan User Baru
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar User -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white/70 border-b border-blue-200/40 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-4 px-6">Nama</th>
                    <th class="py-4 px-6">Email / NIP Login</th>
                    <th class="py-4 px-6">Role</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blue-100/40 text-sm text-slate-700">
                @foreach($users as $user)
                <tr class="hover:bg-white/40 transition-colors">
                    <td class="py-4 px-6 font-bold text-navy">{{ $user->name }}</td>
                    <td class="py-4 px-6 font-medium text-slate-600">{{ $user->email }}</td>
                    <td class="py-4 px-6">
                        <span class="px-3 py-1 text-xs font-bold rounded-full 
                            {{ $user->role_id == 1 ? 'badge-pink' : ($user->role_id == 2 ? 'badge-blue' : ($user->role_id == 4 ? 'bg-amber-100 text-amber-800' : ($user->role_id == 5 ? 'bg-purple-100 text-purple-800' : 'badge-navy'))) }}">
                            {{ $user->role->nama ?? 'Role ' . $user->role_id }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" 
                                    @click="openResetModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                    class="btn-pill-secondary !px-3 !py-1 !text-xs !border-amber-200 text-amber-600 hover:!bg-amber-50 font-bold inline-flex items-center gap-1 cursor-pointer"
                                    title="Reset Password">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                Reset Password
                            </button>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?')" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-pill-secondary !px-3 !py-1 !text-xs !border-red-200 text-red-600 hover:!bg-red-50 font-bold inline-flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
                showResetModal: false,
                targetUser: { id: null, name: '', email: '' },
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