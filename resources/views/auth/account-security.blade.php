@extends('layouts.app')

@section('title', 'Keamanan Akun & 2FA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="accountSecurity()">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="text-2xl font-bold text-navy">Keamanan Akun & Autentikasi 2FA</h1>
            <p class="text-sm text-slate-500">Kelola status Autentikasi Dua Faktor (TOTP) dan kode pemulihan akun Anda.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Gagal memproses permintaan:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Status Kartu 2FA -->
    <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        <div class="flex items-center justify-between border-b border-blue-100 pb-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-navy">Autentikasi Dua Faktor (TOTP)</h2>
                    <p class="text-xs text-slate-500">Standar RFC 6238 via Google Authenticator atau ekstensi browser</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300 shadow-xs inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Aktif & Terlindungi</span>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white/80 p-4 rounded-2xl border border-blue-100 space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Waktu Aktivasi</span>
                <span class="text-sm font-bold text-navy">
                    {{ $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->format('d M Y, H:i') . ' WITA' : '-' }}
                </span>
            </div>
            <div class="bg-white/80 p-4 rounded-2xl border border-blue-100 space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Recovery Codes</span>
                <span class="text-sm font-bold {{ $recoveryCodesCount > 2 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $recoveryCodesCount }} kode sekali pakai tersisa
                </span>
            </div>
        </div>

        <!-- 2 Pilihan Aksi Keamanan -->
        <div class="pt-4 border-t border-blue-100 grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Aksi 1: Buat Ulang Recovery Codes -->
            <div class="bg-white/90 p-5 rounded-2xl border border-blue-200/60 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-1.5">
                    <div class="font-bold text-sm text-navy flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>Buat Ulang Recovery Codes</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Jika recovery code Anda hampir habis atau dicurigai diketahui orang lain, buat 8 kode baru. Kode lama otomatis hangus.
                    </p>
                </div>
                <button type="button" 
                        @click="showRegenModal = true"
                        class="btn-pill-secondary !px-4 !py-2.5 !text-xs font-bold text-navy hover:!bg-blue-50 inline-flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Generate Ulang Kode</span>
                </button>
            </div>

            <!-- Aksi 2: Pindahkan 2FA ke Perangkat Baru -->
            <div class="bg-white/90 p-5 rounded-2xl border border-blue-200/60 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-1.5">
                    <div class="font-bold text-sm text-navy flex items-center gap-2">
                        <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Pindahkan ke Perangkat Baru</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Jika Anda mengganti HP atau ingin memindahkan authenticator ke laptop, setup ulang 2FA dengan QR code baru.
                    </p>
                </div>
                <button type="button" 
                        @click="showReEnrollModal = true"
                        class="btn-pill-secondary !px-4 !py-2.5 !text-xs font-bold text-pink-600 !border-pink-200 hover:!bg-pink-50 inline-flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Pindahkan 2FA / Setup Ulang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Buat Ulang Recovery Codes -->
    <div x-show="showRegenModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="showRegenModal = false">
        
        <div x-show="showRegenModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="showRegenModal = false"></div>

        <div x-show="showRegenModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-card-gradient rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-blue-200/60 space-y-6 z-10">
            
            <div class="flex items-start justify-between border-b border-blue-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy">Buat Ulang Recovery Codes</h3>
                        <p class="text-xs text-slate-500 font-medium">Konfirmasi password akun Anda</p>
                    </div>
                </div>
                <button type="button" @click="showRegenModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('account.security.regenerate-recovery-codes') }}" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-600 leading-relaxed">
                    Demi keamanan, masukkan <strong>password akun Anda</strong> saat ini untuk membuat 8 recovery codes baru. Semua recovery code lama akan langsung hangus.
                </p>

                <div>
                    <label for="regen_password" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Password Anda: <span class="text-rose-500">*</span>
                    </label>
                    <input id="regen_password" 
                           name="password" 
                           type="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="••••••••" 
                           class="form-input text-sm">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-blue-100">
                    <button type="button" @click="showRegenModal = false" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="btn-pill-primary px-5 py-2 text-xs font-bold shadow-md cursor-pointer">
                        Buat 8 Kode Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Re-Enroll / Pindah Perangkat -->
    <div x-show="showReEnrollModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="showReEnrollModal = false">
        
        <div x-show="showReEnrollModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="showReEnrollModal = false"></div>

        <div x-show="showReEnrollModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-card-gradient rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-pink-200 space-y-6 z-10">
            
            <div class="flex items-start justify-between border-b border-pink-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center border border-pink-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy">Pindahkan 2FA / Setup Ulang</h3>
                        <p class="text-xs text-slate-500 font-medium">Konfirmasi password akun Anda</p>
                    </div>
                </div>
                <button type="button" @click="showReEnrollModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('account.security.re-enroll') }}" method="POST" class="space-y-4">
                @csrf
                <div class="p-3 bg-pink-50 rounded-2xl border border-pink-200 text-xs text-pink-900 leading-relaxed">
                    Setelah konfirmasi, Anda akan diarahkan ke halaman Setup 2FA untuk memindai QR code baru. Authenticator di perangkat lama tidak akan bisa digunakan lagi.
                </div>

                <div>
                    <label for="reenroll_password" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Password Anda: <span class="text-rose-500">*</span>
                    </label>
                    <input id="reenroll_password" 
                           name="password" 
                           type="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="••••••••" 
                           class="form-input text-sm">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-pink-100">
                    <button type="button" @click="showReEnrollModal = false" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="btn-pill-primary px-5 py-2 text-xs font-bold shadow-md cursor-pointer !bg-gradient-to-r !from-pink-500 !to-rose-500">
                        Setup Ulang 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function accountSecurity() {
        return {
            showRegenModal: false,
            showReEnrollModal: false,
        };
    }
</script>
@endsection
