<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Program Magang Mahasiswa - Dinas Kominfo Bone Bolango | SIMPATIK</title>
    <meta name="description" content="Informasi resmi program magang mahasiswa dan siswa kejuruan di Dinas Komunikasi dan Informatika Kabupaten Bone Bolango. Syarat berkas fisik wajib dan pendaftaran.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            background: linear-gradient(135deg, #EFF6FF 0%, #FDF2F8 50%, #EFF6FF 100%) !important;
            background-attachment: fixed !important;
        }
        .text-navy-main {
            color: #172554 !important;
        }
        .text-desc-main {
            color: #334155 !important;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 1px solid rgba(203, 213, 225, 0.8) !important;
        }
        .doc-card-blue {
            background: #FFFFFF !important;
            border: 2px solid #BFDBFE !important;
            box-shadow: 0 4px 16px -2px rgba(37, 99, 235, 0.08) !important;
        }
        .doc-card-pink {
            background: #FFFFFF !important;
            border: 2px solid #FBCFE8 !important;
            box-shadow: 0 4px 16px -2px rgba(236, 72, 153, 0.08) !important;
        }
        .btn-magang-primary {
            background: linear-gradient(135deg, #BE185D 0%, #EC4899 100%) !important;
            color: #FFFFFF !important;
            box-shadow: 0 8px 20px -4px rgba(236, 72, 153, 0.45) !important;
            transition: all 0.25s ease-in-out !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            font-weight: 800 !important;
        }
        .btn-magang-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 12px 25px -4px rgba(236, 72, 153, 0.55) !important;
            background: linear-gradient(135deg, #9D174D 0%, #DB2777 100%) !important;
        }
        .btn-magang-secondary {
            background: #FFFFFF !important;
            color: #172554 !important;
            border: 1px solid #CBD5E1 !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05) !important;
            transition: all 0.25s ease-in-out !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            font-weight: 800 !important;
        }
        .btn-magang-secondary:hover {
            background: #F8FAFC !important;
            border-color: #94A3B8 !important;
            transform: translateY(-2px) !important;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-pink-600 selection:text-white">

    <!-- Top Navbar -->
    <header class="sticky top-0 z-40 glass-header shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <!-- Dual Logos Pemda & Kominfo -->
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Logo Pemda Bone Bolango" class="h-10 sm:h-11 w-auto object-contain drop-shadow-sm transition transform group-hover:scale-105">
                    <div class="h-6 w-px bg-slate-200"></div>
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Logo Diskominfo Bone Bolango" class="h-9 sm:h-10 w-auto object-contain drop-shadow-sm transition transform group-hover:scale-105">
                </div>
                <div class="h-9 w-px bg-slate-200 hidden sm:block"></div>
                <div>
                    <span class="font-black text-xl sm:text-2xl tracking-tight text-navy-main block leading-none">SIMPATIK</span>
                    <span class="text-[10px] font-bold text-[#BE185D] uppercase tracking-widest block mt-1">Portal Magang Mahasiswa</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-600 hover:text-navy-main transition px-3.5 py-2 rounded-full hover:bg-slate-100">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Halaman Utama</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="py-12 sm:py-18 text-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full bg-pink-100 border border-pink-300 text-[#9D174D] text-xs font-extrabold mb-6 shadow-sm">
                    <i class="fa-solid fa-graduation-cap text-base"></i>
                    <span>Program Praktik Kerja Lapangan & Magang Mahasiswa</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-black text-navy-main tracking-tight leading-tight sm:leading-tight mb-6">
                    Bangun Pengalaman Kerja Nyata di <span class="text-[#EC4899]">Dinas Kominfo Bone Bolango</span>
                </h1>

                <p class="text-base sm:text-lg text-desc-main font-semibold max-w-2xl mx-auto mb-10 leading-relaxed">
                    Kesempatan bagi mahasiswa perguruan tinggi dan siswa SMK untuk mengaplikasikan keahlian dalam bidang Teknologi Informasi, Jaringan Komputer, Komunikasi Publik, dan E-Government di lingkungan Pemda Bone Bolango.
                </p>

                <!-- Dual Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto">
                    <!-- Tombol 1: Ke Form Login Mahasiswa (Dedicated Page) -->
                    <a href="{{ route('login.mahasiswa') }}" class="btn-magang-primary w-full sm:w-auto px-8 py-3.5 rounded-full text-xs space-x-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Login Mahasiswa</span>
                    </a>
                    <!-- Tombol 2: Daftar Akun Baru -->
                    <a href="{{ route('register') }}" class="btn-magang-secondary w-full sm:w-auto px-7 py-3.5 rounded-full text-xs space-x-2">
                        <i class="fa-solid fa-user-plus text-[#2563EB]"></i>
                        <span>Daftar Akun Baru</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- SEKSI WAJIB: DAFTAR BERKAS YANG HARUS DISERAHKAN KE KANTOR -->
        <section class="py-14 sm:py-18 bg-white/85 border-y border-slate-200">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-black tracking-widest text-[#BE185D] uppercase">Persyaratan Administrasi</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-navy-main tracking-tight mt-1.5">Daftar Berkas Fisik yang Harus Diserahkan</h2>
                    <p class="text-sm text-desc-main mt-2 font-medium">Calon peserta magang diwajibkan menyerahkan berkas dokumen fisik resmi berikut secara langsung ke Kantor Dinas Kominfo Bone Bolango:</p>
                </div>

                <!-- 2 Dokumen Utama Berbentuk Card Bernomor dengan Kontras Tinggi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Dokumen 1: Surat Observasi dari Jurusan -->
                    <div class="doc-card-blue rounded-3xl p-7 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="w-10 h-10 rounded-2xl bg-[#2563EB] text-white font-black text-base flex items-center justify-center shadow-sm">
                                    01
                                </span>
                                <div>
                                    <span class="text-[11px] font-bold text-[#1E40AF] uppercase tracking-wider block">Tahap Observasi Awal</span>
                                    <h3 class="text-lg font-black text-navy-main leading-snug">Surat Observasi dari Jurusan</h3>
                                </div>
                            </div>
                            <p class="text-xs text-desc-main leading-relaxed font-semibold mb-5">
                                Surat permohonan resmi yang diterbitkan oleh Ketua Jurusan atau Program Studi di perguruan tinggi / sekolah asal.
                            </p>
                            <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200 text-xs text-[#1E3A8A] font-semibold space-y-2">
                                <div class="flex items-start gap-2">
                                    <i class="fa-solid fa-clock text-blue-600 mt-0.5"></i>
                                    <span><strong>Waktu Penyerahan:</strong> Diserahkan saat mahasiswa ingin melakukan observasi awal dan pengenalan lingkungan dinas sebelum masa magang dimulai.</span>
                                </div>
                                <div class="flex items-start gap-2 pt-1 border-t border-blue-200/60">
                                    <i class="fa-solid fa-location-dot text-blue-600 mt-0.5"></i>
                                    <span><strong>Lokasi:</strong> Bagian Tata Usaha / Kepegawaian Diskominfo Bone Bolango.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen 2: Surat Rekomendasi dari Fakultas -->
                    <div class="doc-card-pink rounded-3xl p-7 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="w-10 h-10 rounded-2xl bg-[#EC4899] text-white font-black text-base flex items-center justify-center shadow-sm">
                                    02
                                </span>
                                <div>
                                    <span class="text-[11px] font-bold text-[#BE185D] uppercase tracking-wider block">Tahap Pendaftaran Resmi</span>
                                    <h3 class="text-lg font-black text-navy-main leading-snug">Surat Rekomendasi dari Fakultas</h3>
                                </div>
                            </div>
                            <p class="text-xs text-desc-main leading-relaxed font-semibold mb-5">
                                Surat rekomendasi atau surat pengantar resmi yang ditandatangani oleh Dekan atau Wakil Dekan Bidang Kemahasiswaan / Akademik.
                            </p>
                            <div class="bg-pink-50 rounded-2xl p-4 border border-pink-200 text-xs text-[#9D174D] font-semibold space-y-2">
                                <div class="flex items-start gap-2">
                                    <i class="fa-solid fa-clock text-[#EC4899] mt-0.5"></i>
                                    <span><strong>Waktu Penyerahan:</strong> Diserahkan sebagai syarat utama penerbitan persetujuan magang resmi dari Kepala Dinas.</span>
                                </div>
                                <div class="flex items-start gap-2 pt-1 border-t border-pink-200/60">
                                    <i class="fa-solid fa-file-circle-check text-[#EC4899] mt-0.5"></i>
                                    <span><strong>Kelengkapan:</strong> Disertai proposal kegiatan magang (jika ada) dan daftar nama peserta.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bidang / Divisi Magang di Diskominfo -->
        <section class="py-16 sm:py-20 lg:pb-24 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-black tracking-widest text-[#2563EB] uppercase">Peluang Penempatan</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-navy-main tracking-tight mt-1.5">Bidang Kerja di Dinas Kominfo</h2>
                    <p class="text-sm text-desc-main mt-2 font-medium">Peserta magang dapat ditempatkan pada salah satu bidang teknis berikut:</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-[#2563EB] flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-laptop-code"></i>
                        </div>
                        <h4 class="font-black text-navy-main text-base">Aplikasi & Informatika (APTIKA)</h4>
                        <p class="text-xs text-desc-main leading-relaxed font-medium">Pengembangan aplikasi pemerintah, pengelolaan basis data, dan pemeliharaan portal digital dinas.</p>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#059669] flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-network-wired"></i>
                        </div>
                        <h4 class="font-black text-navy-main text-base">Infrastruktur Jaringan (E-Gov)</h4>
                        <p class="text-xs text-desc-main leading-relaxed font-medium">Pengelolaan jaringan fiber optik, bandwidth kantor OPD, server lokal, dan keamanan siber dinas.</p>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-pink-100 text-[#EC4899] flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <h4 class="font-black text-navy-main text-base">Komunikasi Publik (IKP)</h4>
                        <p class="text-xs text-desc-main leading-relaxed font-medium">Peliputan berita pemerintah daerah, desain konten media sosial, videografi, dan diseminasi informasi publik.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 text-xs text-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-1">
            <p class="font-bold text-white">SIMPATIK &mdash; Portal Magang Dinas Kominfo Bone Bolango</p>
            <p>&copy; {{ date('Y') }} Pemerintah Kabupaten Bone Bolango. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
