<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wajib Ganti Password - Sinosip</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <span class="font-extrabold text-3xl tracking-tight text-navy">Sinosip</span>
            </div>
            <h2 class="mt-3 text-2xl font-extrabold text-navy">Wajib Ganti Password</h2>
            <p class="mt-1 text-xs text-slate-500 font-medium">Dinas Kominfo Kabupaten Bone Bolango</p>
        </div>

        <!-- Main Card -->
        <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 backdrop-blur-sm space-y-6">
            
            <!-- Security Alert Notice -->
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3.5 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Aktivasi Keamanan Akun</h3>
                    <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                        Demi keamanan akun Anda, silakan <strong>ganti password default</strong> Anda sebelum melanjutkan ke sistem aplikasi.
                    </p>
                </div>
            </div>

            <!-- Logged-in User Info Badge -->
            <div class="bg-white/60 border border-blue-100 rounded-2xl p-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm ring-2 ring-blue-200">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-navy leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full 
                    {{ auth()->user()->role_id == 1 ? 'badge-pink' : (auth()->user()->role_id == 2 ? 'badge-blue' : 'badge-navy') }}">
                    {{ auth()->user()->role->nama ?? 'Pegawai' }}
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

            <!-- Form Reset Password Wajib -->
            <form action="{{ route('password.force_reset.update') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Password Baru -->
                <div>
                    <label for="password" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               minlength="8"
                               autocomplete="new-password"
                               class="form-input text-sm" 
                               style="padding-right: 2.75rem !important;" 
                               placeholder="Minimal 8 karakter...">
                        <button type="button" 
                                id="togglePassword1" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" 
                                aria-label="Toggle password visibility">
                            <svg id="eyeSlash1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                            <svg id="eye1" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Gunakan minimal 8 karakter (kombinasi huruf & angka).</p>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label for="password_confirmation" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Konfirmasi Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               type="password" 
                               required 
                               minlength="8"
                               autocomplete="new-password"
                               class="form-input text-sm" 
                               style="padding-right: 2.75rem !important;" 
                               placeholder="Ulangi password baru...">
                        <button type="button" 
                                id="togglePassword2" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" 
                                aria-label="Toggle password confirmation visibility">
                            <svg id="eyeSlash2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                            <svg id="eye2" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                        <span>Simpan Password & Masuk</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
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

    <!-- Toggle Password Visibility JS -->
    <script>
        function setupToggle(btnId, inputId, eyeSlashId, eyeId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const eyeSlash = document.getElementById(eyeSlashId);
            const eye = document.getElementById(eyeId);

            if (btn && input && eyeSlash && eye) {
                btn.addEventListener('click', function () {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    eyeSlash.classList.toggle('hidden', isPassword);
                    eye.classList.toggle('hidden', !isPassword);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            setupToggle('togglePassword1', 'password', 'eyeSlash1', 'eye1');
            setupToggle('togglePassword2', 'password_confirmation', 'eyeSlash2', 'eye2');
        });
    </script>
</body>
</html>
