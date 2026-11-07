<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Pemulihan 2FA - Sinosip</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 sm:p-6" x-data="recoveryCodesHandler()">
    <div class="max-w-xl w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <span class="font-extrabold text-3xl tracking-tight text-navy">Sinosip</span>
            </div>
            <h2 class="mt-3 text-2xl font-extrabold text-navy">Simpan Kode Pemulihan Anda</h2>
            <p class="mt-1 text-xs text-slate-500 font-medium">Cadangan Darurat Jika Anda Kehilangan Perangkat 2FA</p>
        </div>

        <!-- Main Card -->
        <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 backdrop-blur-sm space-y-6">
            
            <!-- Warning Alert -->
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3.5 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Penting: Kode Ini Hanya Tampil Sekali!</h3>
                    <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                        Jika Anda kehilangan HP atau ekstensi authenticator terhapus, Anda <strong>hanya dapat masuk</strong> menggunakan salah satu kode di bawah ini. Setiap kode berlaku <strong>hanya satu kali pakai</strong>.
                    </p>
                </div>
            </div>

            <!-- Recovery Codes Grid -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600">8 Kode Pemulihan Sekali Pakai:</span>
                    <div class="flex items-center gap-2">
                        <!-- Tombol Salin Semua -->
                        <button type="button" 
                                @click="copyAll()"
                                class="btn-pill-secondary !px-3 !py-1 !text-xs font-bold inline-flex items-center gap-1 cursor-pointer"
                                :class="copied ? '!border-emerald-300 !text-emerald-700 !bg-emerald-50' : ''">
                            <template x-if="!copied">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    Salin Semua
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Tersalin!
                                </span>
                            </template>
                        </button>
                        
                        <!-- Tombol Unduh File -->
                        <button type="button" 
                                @click="downloadTxt()"
                                class="btn-pill-secondary !px-3 !py-1 !text-xs font-bold inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh File .TXT
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 p-4 bg-white/90 rounded-2xl border border-blue-200/80 shadow-inner">
                    @foreach($codes as $code)
                        <div class="p-2.5 bg-slate-50 border border-slate-200/60 rounded-xl text-center font-mono font-extrabold text-sm tracking-wider text-navy select-all">
                            {{ $code }}
                        </div>
                    @endforeach
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

            <!-- Form Konfirmasi Simpan Kode -->
            <form action="{{ route('2fa.recovery-codes.acknowledge') }}" method="POST" class="space-y-4 pt-3 border-t border-blue-100">
                @csrf
                <div class="p-3.5 bg-blue-50/60 rounded-2xl border border-blue-200/60">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" 
                               name="acknowledged" 
                               value="1" 
                               x-model="hasAcknowledged"
                               required 
                               class="mt-0.5 w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary cursor-pointer">
                        <span class="text-xs text-slate-700 leading-relaxed font-semibold">
                            Saya mengonfirmasi bahwa saya sudah menyalin atau mengunduh 8 kode pemulihan di atas dan menyimpannya di tempat yang aman.
                        </span>
                    </label>
                </div>

                <div class="pt-1">
                    <button type="submit" 
                            :disabled="!hasAcknowledged"
                            :class="!hasAcknowledged ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                            class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg flex items-center justify-center gap-2">
                        <span>Lanjutkan Masuk ke Aplikasi</span>
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
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function recoveryCodesHandler() {
            const rawCodes = @json($codes);
            return {
                copied: false,
                hasAcknowledged: false,
                copyAll() {
                    const text = "SINOSIP - 2FA RECOVERY CODES\n============================\n" + rawCodes.join("\n") + "\n\nSimpan kode ini di tempat aman. Masing-masing hanya bisa dipakai satu kali.";
                    navigator.clipboard.writeText(text).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    });
                },
                downloadTxt() {
                    const content = "SINOSIP - KODE PEMULIHAN 2FA\n=================================\nPengguna : {{ auth()->user()->name }}\nEmail/NIP: {{ auth()->user()->email }}\nTanggal  : " + new Date().toLocaleString() + "\n\nDaftar Kode Pemulihan Sekali Pakai:\n" + rawCodes.map((c, i) => (i+1) + ". " + c).join("\n") + "\n\nCatatan Penting:\n- Setiap kode hanya berlaku 1 kali pakai.\n- Gunakan jika kehilangan akses authenticator di HP/laptop.";
                    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'sinosip-2fa-recovery-codes.txt';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            };
        }
    </script>
</body>
</html>
