<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keamanan Akun & 2FA - KOMTARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">

    <style>
        [x-cloak] { display: none !important; }
        :root {
            --color-primary: #198754;
            --color-navy: #172554;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .app-background {
            background: #024d43e6 !important;
            min-height: 100vh;
        }
        .bg-card-gradient {
            background: #FFFFFF !important;
            border: 1px solid rgba(203, 213, 225, 0.8) !important;
            border-radius: 1.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(23, 37, 84, 0.07), 0 4px 12px -2px rgba(23, 37, 84, 0.03);
        }
        .btn-pill-primary {
            background: #146C43 !important;
            color: #FFFFFF !important;
            border: none !important;
            border-radius: 9999px !important;
            font-weight: 700 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 14px 0 rgba(20, 108, 67, 0.35) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
        }
        .btn-pill-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px 0 rgba(20, 108, 67, 0.45) !important;
            background: #0F5132 !important;
        }
        .text-navy {
            color: var(--color-navy) !important;
        }
    </style>
</head>
<body class="app-background min-h-screen flex flex-col antialiased" x-data="{ showRegenModal: false, showResetDeviceModal: false }">
    <div class="flex-1 max-w-2xl w-full mx-auto p-4 sm:p-8 space-y-6">
        <!-- Navigation Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda" class="w-10 h-10 object-contain drop-shadow-md">
                <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Kominfo" class="w-10 h-10 object-contain drop-shadow-md">
                <div>
                    <h1 class="font-black text-xl text-white drop-shadow-sm">Keamanan Akun & 2FA</h1>
                    <p class="text-xs text-emerald-100 font-medium">KOMTARA &mdash; Dinas Kominfo Bone Bolango</p>
                </div>
            </div>

            @php
                $backUrl = route('landing');
                if ($user->role === 'pegawai') $backUrl = route('pegawai.dokumen');
                elseif (in_array($user->role, ['kasubag', 'admin_kasubag'])) $backUrl = route('admin.arsip.index');
                else $backUrl = route('admin.aset');
            @endphp
            <a href="{{ $backUrl }}" class="px-4 py-2 bg-white/90 hover:bg-white text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-xs transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                <span>Kembali ke Aplikasi</span>
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl text-xs border border-emerald-200 flex items-center gap-3 shadow-xs">
                <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-[#D1E7DD] text-[#0F5132] p-4 rounded-2xl text-xs border border-[#A3CFBB] flex items-center gap-3 shadow-xs">
                <i class="fa-solid fa-circle-info text-base text-[#198754]"></i>
                <span class="font-medium">{{ session('info') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs border border-red-200 shadow-xs">
                <ul class="list-disc pl-4 space-y-1 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Info Profil & Status 2FA -->
        <div class="bg-card-gradient p-6 rounded-3xl shadow-lg border border-slate-200 space-y-4">
            <div class="flex items-start sm:items-center justify-between gap-4 flex-col sm:flex-row pb-4 border-b border-slate-100">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#198754] to-[#0F5132] text-white flex items-center justify-center font-black text-xl shadow-md uppercase">
                        {{ substr($user->nama, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-navy">{{ $user->nama }}</h2>
                        <p class="text-xs text-slate-500 font-mono">{{ $user->nip ? 'NIP: ' . $user->nip : $user->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-check text-emerald-600"></i>
                        <span>2FA TOTP Aktif</span>
                    </span>
                </div>
            </div>

            <!-- Detail Keamanan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="bg-white/80 p-4 rounded-2xl border border-slate-200">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Aktivasi 2FA</span>
                    <span class="text-sm font-extrabold text-navy mt-0.5 block">
                        {{ $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->translatedFormat('d F Y, H:i') : '-' }}
                    </span>
                    <span class="text-[11px] text-slate-500 mt-1 block">Menggunakan Google Authenticator / Ekstensi Browser</span>
                </div>

                <div class="bg-white/80 p-4 rounded-2xl border border-slate-200">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Sisa Kode Cadangan (Recovery)</span>
                    <span class="text-sm font-extrabold text-navy mt-0.5 block">
                        {{ $recoveryCodesCount }} Kode Tersisa
                    </span>
                    <span class="text-[11px] text-slate-500 mt-1 block">Dapat dipakai saat kehilangan akses authenticator</span>
                </div>
            </div>
        </div>

        <!-- Card Manajemen 2FA & Recovery Codes -->
        <div class="bg-card-gradient p-6 rounded-3xl shadow-lg border border-slate-200 space-y-5">
            <h3 class="font-extrabold text-navy text-sm uppercase tracking-wider">Tindakan Keamanan Akun</h3>

            <!-- Opsi 1: Buat Ulang Kode Cadangan -->
            <div class="flex items-center justify-between p-4 bg-white/80 rounded-2xl border border-slate-200 gap-4 flex-col sm:flex-row">
                <div>
                    <h4 class="font-bold text-sm text-navy">Buat Ulang Kode Cadangan (Recovery Codes)</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Buat 8 kode cadangan baru jika kode sebelumnya habis atau Anda ingin memperbaruinya. Kode lama akan hangus.
                    </p>
                </div>
                <button type="button" @click="showRegenModal = true" class="px-4 py-2 bg-[#D1E7DD] text-[#0F5132] hover:bg-[#A3CFBB] border border-[#A3CFBB] text-xs font-bold rounded-xl transition shrink-0">
                    <i class="fa-solid fa-arrows-rotate mr-1 text-[#198754]"></i> Buat Ulang Kode
                </button>
            </div>

            <!-- Opsi 2: Pindahkan / Ganti Perangkat 2FA -->
            <div class="flex items-center justify-between p-4 bg-white/80 rounded-2xl border border-slate-200 gap-4 flex-col sm:flex-row">
                <div>
                    <h4 class="font-bold text-sm text-navy">Ganti Perangkat / Pasang Ulang 2FA</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pindahkan 2FA ke HP baru atau pasang ulang di browser laptop baru. Anda akan diarahkan ke scan QR code baru.
                    </p>
                </div>
                <button type="button" @click="showResetDeviceModal = true" class="px-4 py-2 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 text-xs font-bold rounded-xl transition shrink-0">
                    <i class="fa-solid fa-mobile-screen-button mr-1"></i> Pasang Ulang 2FA
                </button>
            </div>
        </div>

        <!-- Modal Konfirmasi Password: Buat Ulang Recovery Codes -->
        <div x-show="showRegenModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showRegenModal = false" class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200">
                <h3 class="font-extrabold text-navy text-base mb-1">Konfirmasi Kata Sandi</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Masukkan kata sandi akun Anda untuk membuat 8 kode cadangan pemulihan baru.
                </p>

                <form action="{{ route('account.security.regenerate_recovery') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-navy mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required autofocus class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#198754]" placeholder="••••••••">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showRegenModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-[#198754] hover:bg-[#146C43] text-white text-xs font-bold rounded-xl transition shadow-xs">
                            Buat Kode Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Konfirmasi Password: Reset Perangkat 2FA -->
        <div x-show="showResetDeviceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showResetDeviceModal = false" class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200">
                <h3 class="font-extrabold text-navy text-base mb-1">Pasang Ulang 2FA ke Perangkat Baru</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Konfirmasi kata sandi Anda. Anda akan diarahkan untuk melakukan scan QR code baru.
                </p>

                <form action="{{ route('account.security.reset_device') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-navy mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required autofocus class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#198754]" placeholder="••••••••">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showResetDeviceModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                            Lanjut ke Setup 2FA Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer Bar Penuh Solid Hijau Tua -->
    <footer class="w-full bg-[#0A3622] text-white py-5 sm:py-6 text-center border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-1.5">
            <p class="text-base font-semibold text-white tracking-wide">KOMTARA &mdash; Dinas Komunikasi dan Informatika</p>
            <p class="text-sm font-normal text-emerald-100">&copy; {{ date('Y') }} Pemerintah Kabupaten Bone Bolango. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
