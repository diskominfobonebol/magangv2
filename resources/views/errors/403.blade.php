<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak | Sinosip</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="app-background min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full text-center">
        <!-- Logo -->
        <div class="mb-6">
            <div class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-blue-500/30" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">S</div>
                <span class="font-extrabold text-3xl tracking-tight text-navy">Sinosip</span>
            </div>
        </div>

        <!-- Card 403 -->
        <div class="bg-card-gradient p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
            <div class="w-20 h-20 rounded-3xl bg-rose-50 border border-rose-200 flex items-center justify-center mx-auto text-rose-600 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>

            <div>
                <span class="inline-block px-3 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-full uppercase tracking-wider mb-2">
                    403 &bull; Akses Dibatasi
                </span>
                <h2 class="text-xl font-bold text-navy">Hak Akses Tidak Mencukupi</h2>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk menambah, mengubah, atau menghapus data pada halaman/modul ini. Operasi mutasi data hanya dapat dilakukan oleh Kasubag.' }}
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold shadow-sm">
                    &larr; Kembali
                </a>
                <a href="{{ route('dashboard') }}" class="btn-pill-primary px-5 py-2.5 text-xs font-bold shadow-md">
                    Ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
