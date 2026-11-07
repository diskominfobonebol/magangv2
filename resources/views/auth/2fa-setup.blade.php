<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktivasi 2FA - Sinosip</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 sm:p-6" x-data="twoFactorSetup()">
    <div class="max-w-lg w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <span class="font-extrabold text-3xl tracking-tight text-navy">Sinosip</span>
            </div>
            <h2 class="mt-3 text-2xl font-extrabold text-navy">Aktivasi Autentikasi 2FA</h2>
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

            <!-- Petunjuk Penggunaan HP & Laptop -->
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 text-xs text-slate-700 leading-relaxed space-y-2">
                <div class="font-bold text-navy flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Petunjuk Penggunaan:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-[11px] text-slate-600">
                    <li><strong>HP:</strong> Unduh aplikasi <em>Google Authenticator</em> dari Play Store / App Store, lalu pindai QR code di bawah.</li>
                    <li><strong>Laptop:</strong> Pasang ekstensi authenticator di browser (mis. <em>Authenticator</em> pada Chrome/Edge), lalu tambahkan akun dengan scan QR di layar atau masukkan <em>Secret Key</em> manual di bawah.</li>
                    <li>Akun terdaftar sebagai: <strong class="font-mono text-primary">Sinosip ({{ $holder }})</strong></li>
                </ul>
            </div>

            <!-- QR Code Section -->
            <div class="flex flex-col items-center justify-center p-5 bg-white rounded-2xl border border-blue-200/60 shadow-sm">
                <!-- Wrapper Gambar QR Code dengan kontras tinggi & quiet zone standar -->
                <div class="p-3 bg-white rounded-2xl border-2 border-slate-100 shadow-xs flex items-center justify-center">
                    @if(!empty($qrCodeDataUri))
                        <img src="{{ $qrCodeDataUri }}" 
                             alt="QR Code TOTP Sinosip" 
                             class="w-56 h-56 sm:w-60 sm:h-60 max-w-full aspect-square object-contain block select-none"
                             loading="eager">
                    @else
                        <div class="w-56 h-56 sm:w-60 sm:h-60 flex items-center justify-center">
                            {!! $qrCodeSvg !!}
                        </div>
                    @endif
                </div>

                <!-- Petunjuk Scan Google Authenticator -->
                <div class="mt-3.5 text-center space-y-1">
                    <div class="inline-flex items-center gap-1.5 text-xs font-bold text-navy">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <span>Pindai melalui Google Authenticator</span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Buka aplikasi Google Authenticator di HP &rarr; ketuk <strong>(+)</strong> &rarr; pilih <strong>Scan a QR code</strong> / <strong>Pindai kode QR</strong>
                    </p>
                </div>

                @if(!empty($qrCodeUrl))
                    <!-- Opsi Tambahan bagi Pengguna Browser / Laptop (1-Klik) -->
                    <div class="mt-3 pt-3 border-t border-slate-100 w-full flex items-center justify-center">
                        <a href="{{ $qrCodeUrl }}" 
                           class="text-[11px] font-semibold text-primary hover:text-blue-700 hover:underline inline-flex items-center gap-1"
                           title="Klik untuk membuka langsung di ekstensi Authenticator laptop/browser">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <span>Atau klik untuk buka langsung di ekstensi browser</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Manual Secret Key (For Laptop / Manual Input) -->
            <div class="space-y-1.5">
                <label class="form-label text-xs font-bold text-slate-700 block">
                    Secret Key Manual (Pengguna Laptop / Tanpa Kamera):
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" 
                           readonly 
                           value="{{ $secret }}" 
                           id="secretKeyInput"
                           class="form-input text-xs font-mono font-bold tracking-widest text-navy bg-slate-50 select-all cursor-text">
                    <button type="button" 
                            @click="copySecret()"
                            class="btn-pill-secondary !px-4 !py-2 !text-xs font-bold shrink-0 inline-flex items-center gap-1.5 cursor-pointer"
                            :class="copied ? '!border-emerald-300 !text-emerald-700 !bg-emerald-50' : ''">
                        <template x-if="!copied">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                Salin
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Tersalin!
                            </span>
                        </template>
                    </button>
                </div>
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

            <!-- Form Konfirmasi Kode 6 Digit -->
            <form action="{{ route('2fa.setup.confirm') }}" method="POST" class="space-y-4 pt-2 border-t border-blue-100">
                @csrf
                <div>
                    <label for="code" class="form-label text-xs font-bold text-slate-700 block mb-1">
                        Masukkan 6-Digit Kode dari Aplikasi Authenticator: <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="code" 
                               name="code" 
                               type="text" 
                               inputmode="numeric" 
                               pattern="[0-9]*"
                               maxlength="6"
                               required 
                               autocomplete="one-time-code"
                               autofocus
                               class="form-input text-center text-xl font-mono font-extrabold tracking-widest text-navy" 
                               placeholder="000000">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Kode 6 digit berganti setiap 30 detik pada aplikasi Anda.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                        <span>Konfirmasi & Aktifkan 2FA</span>
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

    <script>
        function twoFactorSetup() {
            return {
                copied: false,
                copySecret() {
                    const secretInput = document.getElementById('secretKeyInput');
                    if (secretInput) {
                        navigator.clipboard.writeText(secretInput.value).then(() => {
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2500);
                        });
                    }
                }
            };
        }
    </script>
</body>
</html>
