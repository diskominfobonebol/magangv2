<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi 2FA - Sinosip</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 sm:p-6" x-data="{ useRecoveryCode: false }">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <span class="font-extrabold text-3xl tracking-tight text-navy">Sinosip</span>
            </div>
            <h2 class="mt-3 text-2xl font-extrabold text-navy">Verifikasi Dua Faktor</h2>
            <p class="mt-1 text-xs text-slate-500 font-medium">Dinas Kominfo Kabupaten Bone Bolango</p>
        </div>

        <!-- Main Card -->
        <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 backdrop-blur-sm space-y-6">
            
            <!-- User Info Badge -->
            <div class="bg-white/60 border border-blue-100 rounded-2xl p-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm ring-2 ring-blue-200">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-navy leading-tight">{{ $user->name }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">{{ $user->email }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full 
                    {{ $user->role_id == 1 ? 'badge-pink' : ($user->role_id == 2 ? 'badge-blue' : 'badge-navy') }}">
                    {{ $user->role->nama ?? 'Pegawai' }}
                </span>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs font-semibold border border-red-200 space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl text-xs font-semibold border border-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Verifikasi Kode 6-Digit (Mode Default) -->
            <form x-show="!useRecoveryCode" action="{{ route('2fa.verify.check') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Kode 6-Digit dari Aplikasi Authenticator:
                    </label>
                    <div class="relative">
                        <input id="code" 
                               name="code" 
                               type="text" 
                               inputmode="numeric" 
                               pattern="[0-9]*"
                               maxlength="6"
                               :required="!useRecoveryCode"
                               autocomplete="one-time-code"
                               autofocus
                               class="form-input text-center text-2xl font-mono font-extrabold tracking-widest text-navy" 
                               placeholder="000000">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Buka Google Authenticator di HP atau ekstensi di browser.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                        <span>Verifikasi & Masuk</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>

                <div class="pt-2 text-center">
                    <button type="button" 
                            @click="useRecoveryCode = true" 
                            class="text-xs font-semibold text-primary hover:underline cursor-pointer inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        <span>Kehilangan akses authenticator? Gunakan Recovery Code</span>
                    </button>
                </div>
            </form>

            <!-- Form Verifikasi Recovery Code (Mode Darurat) -->
            <form x-show="useRecoveryCode" x-cloak action="{{ route('2fa.verify.check') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="recovery_code" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Masukkan Salah Satu Recovery Code:
                    </label>
                    <div class="relative">
                        <input id="recovery_code" 
                               name="recovery_code" 
                               type="text" 
                               :required="useRecoveryCode"
                               autocomplete="off"
                               class="form-input text-center text-base font-mono font-bold tracking-widest text-navy uppercase" 
                               placeholder="XXXX-XXXX">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Gunakan kode cadangan 8 karakter yang telah Anda simpan sebelumnya.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                        <span>Verifikasi Recovery Code</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>

                <div class="pt-2 text-center">
                    <button type="button" 
                            @click="useRecoveryCode = false" 
                            class="text-xs font-semibold text-slate-500 hover:text-navy cursor-pointer inline-flex items-center gap-1">
                        <span>&larr; Kembali ke Verifikasi Authenticator</span>
                    </button>
                </div>
            </form>

            <!-- Logout Link -->
            <div class="pt-2 border-t border-blue-100/60 text-center">
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition-colors inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Bukan akun Anda? <u>Keluar / Logout</u></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
