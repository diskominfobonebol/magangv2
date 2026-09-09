@extends('layouts.app')

@section('title', 'Buat Surat Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.index') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Buat Surat</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.index') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Menu Surat">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Surat Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 1 dari 3</p>
            </div>
        </div>
        <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Stepper -->
    <div class="py-2 flex items-center justify-center">
        <!-- Step 1 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                1
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Informasi Surat</span>
        </div>
        <!-- Divider -->
        <div class="w-16 border-t-2 border-blue-200 mx-3"></div>
        <!-- Step 2 (Inactive) -->
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">
                2
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-500">Personel & Uraian</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" 
     x-data="{ 
         tanggal: '{{ session('s_tgl_surat', '') }}', 
         jenisSuratId: '{{ session('s_jenis_surat_id', '2') }}', 
         modeNomor: 'otomatis',
         get nomorUrut() {
             return this.jenisSuratId === '1' ? '001' : '{{ $nomorUrut ?? '001' }}';
         },
         kodeSurat() {
             if (this.jenisSuratId === '1') return '090';
             if (this.jenisSuratId === '2') return '555';
             return 'XXX';
         },
         namaJenisSurat() {
             if (this.jenisSuratId === '1') return 'SPPD';
             if (this.jenisSuratId === '2') return 'SPT';
             return '';
         },
         formatBulanRomawi() {
             if (!this.tanggal) return 'MM';
             const month = parseInt(this.tanggal.split('-')[1], 10);
             const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
             return romawi[month - 1] || 'MM';
         },
         formatTahun() {
             if (!this.tanggal) return 'YYYY';
             return this.tanggal.split('-')[0] || 'YYYY';
         }
     }">
        
        <form action="{{ route('surat.create.step2') }}" method="POST" class="p-6 md:p-8">
            @csrf
            
            <div class="space-y-6">
                <!-- Field 1: TANGGAL SURAT -->
                <div>
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tgl_surat" x-model="tanggal" class="form-input" required>
                </div>

                <!-- Field 2: JENIS SURAT -->
                <div>
                    <label class="form-label">Jenis Surat</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($jenisSurats as $jenis)
                            <label class="relative flex items-center p-4 rounded-2xl border cursor-pointer transition-all bg-white"
                                   :class="jenisSuratId == {{ $jenis->id }} ? 'border-primary shadow-sm bg-blue-50/20' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="jenis_surat_id" value="{{ $jenis->id }}" x-model="jenisSuratId" class="text-primary focus:ring-primary h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-navy">{{ $jenis->nama_jenis }}</span>
                                    <span class="block text-xs text-slate-500 font-medium">{{ $jenis->deskripsi }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Field 3: NOMOR SURAT -->
                <div>
                    <label class="form-label">Nomor Surat</label>
                    
                    <!-- Tab Pilihan -->
                    <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                        <button type="button" @click="modeNomor = 'otomatis'" 
                                class="pb-2 flex items-center gap-1.5 transition-colors"
                                :class="modeNomor === 'otomatis' ? 'text-primary border-b-2 border-primary' : 'text-slate-400 hover:text-slate-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Otomatis
                        </button>
                        <button type="button" @click="modeNomor = 'manual'" 
                                class="pb-2 flex items-center gap-1.5 transition-colors"
                                :class="modeNomor === 'manual' ? 'text-primary border-b-2 border-primary' : 'text-slate-400 hover:text-slate-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Isi Manual
                        </button>
                    </div>

                    <!-- Dynamic Content Area -->
                    <div class="mt-1">
                       <!-- Otomatis Preview -->
                        <div x-show="modeNomor === 'otomatis'" class="bg-white/80 border border-blue-200/50 rounded-2xl p-4">
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Preview nomor surat:</p>
                            <div class="text-sm font-extrabold text-primary mb-1 font-mono">
                                <span x-text="kodeSurat()"></span>/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                            </div>
                            <p class="text-xs text-slate-400 font-mono">
                                Format: <span x-text="kodeSurat()"></span>/URUT/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                            </p>
                            <input type="hidden" name="nomor_surat" :value="kodeSurat() + '/' + nomorUrut + '/' + formatBulanRomawi() + '/' + formatTahun()">
                        </div>

                        <!-- Manual Input -->
                        <div x-show="modeNomor === 'manual'" style="display: none;">
                            <input type="text" name="nomor_surat" placeholder="Contoh: 555/123/Kominfo/IX/2026" class="form-input font-mono">
                            <p class="mt-2 text-xs text-pink-accent font-semibold">
                                Pastikan nomor manual tidak duplikat dengan arsip sebelumnya.
                            </p>
                        </div>
                    </div>
                </div>

               <!-- Field 4: TUJUAN SURAT -->
                <div>
                    <label class="form-label">Tujuan Surat</label>
                   <input type="text" name="tujuan" value="{{ session('s_tujuan', '') }}" placeholder="Contoh: Kementerian Dalam Negeri, Jakarta" class="form-input" required>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Batal & Exit
                </a>
                <div class="flex items-center gap-3">
                    <!-- Tombol Simpan Draft (Men-trigger form tersembunyi di bawah) -->
                    <button type="button" onclick="document.getElementById('form-simpan-draft').submit();" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold cursor-pointer">
                        Simpan Draft
                    </button>
                    
                    <!-- Tombol Lanjut (Milik form utama) -->
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md cursor-pointer">
                        Lanjut &rarr;
                    </button>
                </div>
            </div>
        </form>

        <!-- Form Tersembunyi Khusus untuk Simpan Draft -->
        <form id="form-simpan-draft" action="{{ route('surat.draft') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>
@endsection