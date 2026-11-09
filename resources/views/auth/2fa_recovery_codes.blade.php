<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Cadangan Pemulihan (Recovery Codes) - KOMTARA</title>
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
        body, body.app-background, .app-background {
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
        .btn-pill-primary:hover:not(:disabled) {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px 0 rgba(20, 108, 67, 0.45) !important;
            background: #0F5132 !important;
        }
        .btn-pill-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
        }
        .text-navy {
            color: var(--color-navy) !important;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
    </style>
</head>
<body class="app-background min-h-screen flex flex-col antialiased" 
      x-data="{ 
          codes: {{ Js::from($recoveryCodes) }},
          copied: false,
          acknowledged: false,
          copyAll() {
              const text = 'KOMTARA RECOVERY CODES - ' + '{{ $user->nama }}' + '\n\n' + this.codes.join('\n');
              navigator.clipboard.writeText(text);
              this.copied = true;
              setTimeout(() => this.copied = false, 2500);
          },
          downloadTxt() {
              const text = 'KOMTARA RECOVERY CODES\nUser: {{ $user->nama }} ({{ $user->email }})\nTanggal: ' + new Date().toLocaleString() + '\n\nKode Pemulihan Sekali Pakai:\n' + this.codes.map((c, i) => (i+1) + '. ' + c).join('\n') + '\n\nSimpan dokumen ini dengan aman dan rahasia.';
              const blob = new Blob([text], { type: 'text/plain' });
              const url = URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url;
              a.download = 'komtara-recovery-codes-{{ $user->nip ?: Str::slug($user->nama) }}.txt';
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              URL.revokeObjectURL(url);
          }
      }">

    <!-- Top Navbar Identik Landing Page -->
    <header class="sticky top-0 z-40 glass-header shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-20 flex items-center">
            <a href="{{ route('landing') }}" class="flex items-center space-x-2.5 group">
                <!-- Logo ukuran proporsional (w-10 h-10 sm:w-11 sm:h-11) -->
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda Bone Bolango" class="w-10 h-10 sm:w-11 sm:h-11 object-contain mix-blend-multiply transition transform group-hover:scale-105">
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Diskominfo Bone Bolango" class="w-10 h-10 sm:w-11 sm:h-11 object-contain mix-blend-multiply transition transform group-hover:scale-105">
                </div>
                <div class="border-l border-slate-300 pl-2.5">
                    <span class="font-black text-xl sm:text-2xl tracking-tight text-[#172554] block leading-none">KOMTARA</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mt-0.5">Diskominfo Bone Bolango</span>
                </div>
            </a>
        </div>
    </header>

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 py-8 sm:py-12">
        <div class="max-w-lg w-full">
            <!-- Header Area Form Recovery Codes -->
            <div class="text-center mb-6">
                <!-- Badge Keterangan Sistem Sesuai Landing Page -->
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/95 border border-white/80 text-amber-900 text-xs font-black shadow-md">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span><i class="fa-solid fa-key mr-1 text-amber-600"></i> Simpan Kode Cadangan</span>
                </div>

                <h2 class="mt-4 text-2xl sm:text-3xl font-black text-white drop-shadow-sm">Kode Cadangan Sekali Pakai</h2>
                <p class="mt-1 text-xs sm:text-sm text-emerald-100 font-medium drop-shadow-xs">
                    Gunakan kode ini jika Anda kehilangan akses ke HP atau ekstensi authenticator Anda.
                </p>
            </div>

            <!-- Main Card Recovery Codes -->
            <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl border border-slate-200 backdrop-blur-sm">
                <!-- Peringatan Keamanan -->
                <div class="mb-5 bg-amber-50 text-amber-800 p-4 rounded-2xl text-xs border border-amber-200 flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-base shrink-0 text-amber-600 mt-0.5"></i>
                    <div class="leading-relaxed">
                        <strong>PENTING:</strong> Simpan kode ini sekarang! Masing-masing kode hanya dapat dipakai <strong>1 kali</strong>. Kode cadangan ini <strong>tidak akan ditampilkan lagi</strong> setelah Anda meninggalkan halaman ini.
                    </div>
                </div>

                <!-- Grid 8 Recovery Codes -->
                <div class="grid grid-cols-2 gap-2.5 bg-slate-900/95 p-4 sm:p-5 rounded-2xl border border-slate-800 shadow-inner mb-5">
                    @foreach($recoveryCodes as $index => $code)
                        <div class="flex items-center justify-between bg-slate-800/80 px-3 py-2 rounded-xl border border-slate-700/60 font-mono text-sm font-bold text-amber-300 tracking-wider">
                            <span class="text-[10px] text-slate-500 select-none mr-2">#{{ $index + 1 }}</span>
                            <span>{{ $code }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Tombol Aksi: Salin Semua & Download File TXT -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-6">
                    <button type="button" @click="copyAll()" 
                        class="w-full py-2.5 px-4 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2"
                        :class="copied ? 'bg-emerald-600 text-white' : 'bg-[#D1E7DD] text-[#0F5132] hover:bg-[#A3CFBB] border border-[#A3CFBB]'">
                        <i class="fa-solid" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                        <span x-text="copied ? 'Semua Kode Disalin!' : 'Salin Semua Kode'"></span>
                    </button>

                    <button type="button" @click="downloadTxt()" 
                        class="w-full py-2.5 px-4 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh File .TXT</span>
                    </button>
                </div>

                <!-- Form Persetujuan & Lanjut ke Dashboard -->
                <form action="{{ route('2fa.recovery_codes.ack') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="bg-white/80 p-3.5 rounded-2xl border border-slate-200">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="acknowledged" value="1" x-model="acknowledged" required
                                class="mt-0.5 w-4 h-4 text-[#198754] border-slate-300 rounded focus:ring-[#198754] accent-[#198754]">
                            <span class="text-xs font-semibold text-slate-700 leading-snug">
                                Saya sudah mencatat atau mengunduh kode cadangan ini di tempat yang aman.
                            </span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" :disabled="!acknowledged" class="btn-pill-primary w-full py-3 px-4 text-sm font-bold shadow-lg">
                            Selesai & Masuk ke Sistem &rarr;
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
