@extends('layouts.app')

@section('title', 'Buat Nomor Surat Baru')

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
                <h2 class="text-2xl font-bold text-navy">Buat Nomor Surat Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 1 dari 3</p>
            </div>
        </div>
        <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Alert Notifikasi Error -->
    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    <!-- Stepper -->
    <div class="py-2 flex items-center justify-center">
        <!-- Step 1 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                1
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Informasi Nomor Surat</span>
        </div>
        <!-- Divider -->
        <div class="w-16 border-t-2 border-blue-200 mx-3"></div>
        <!-- Step 2 (Inactive) -->
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">
                2
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-500">Pegawai & Uraian</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" 
     x-data="{ 
         tanggal: '{{ old('tgl_surat', session('s_tgl_surat', '')) }}', 
         jenisSuratId: '{{ old('jenis_surat_id', session('s_jenis_surat_id', '2')) }}', 
         modeNomor: 'otomatis',
         nomorManual: '{{ old('nomor_surat', '') }}',
         nomorUrutSpt: '{{ $nomorUrutSpt ?? $nomorUrut ?? '001' }}',
         nomorUrutSppd: '{{ $nomorUrutSppd ?? '001' }}',
         errorTanggal: false,
         errorJenis: false,
         get nomorUrut() {
             return this.jenisSuratId === '1' ? this.nomorUrutSppd : this.nomorUrutSpt;
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
         },
         simpanDraft(e) {
             this.errorTanggal = !this.tanggal || this.tanggal.trim() === '';
             this.errorJenis = !this.jenisSuratId || this.jenisSuratId === '';
             if (this.errorTanggal || this.errorJenis) {
                 e.preventDefault();
                 return false;
             }
             const form = document.getElementById('form-utama');
             form.action = '{{ route('surat.draft') }}';
             form.submit();
         },
         submitLanjut(e) {
             this.errorTanggal = !this.tanggal || this.tanggal.trim() === '';
             this.errorJenis = !this.jenisSuratId || this.jenisSuratId === '';
             if (this.errorTanggal || this.errorJenis) {
                 e.preventDefault();
                 return false;
             }
             const form = document.getElementById('form-utama');
             form.action = '{{ route('surat.create.step2') }}';
         }
     }">
        
        <form action="{{ route('surat.create.step2') }}" method="POST" class="p-6 md:p-8" id="form-utama">
            @csrf
            
            <div class="space-y-6">
                <!-- Field 1: TANGGAL SURAT -->
                <div>
                    <label class="form-label">Tanggal Nomor Surat <span class="text-rose-500">*</span></label>
                    <input type="date" name="tgl_surat" x-model="tanggal" @input="errorTanggal = false" class="form-input" :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
                    <p x-show="errorTanggal" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tanggal Nomor Surat wajib diisi sebelum menyimpan draft atau melanjutkan.
                    </p>
                    @if(session('error_tgl_surat'))
                        <p class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('error_tgl_surat') }}
                        </p>
                    @endif
                </div>

                <!-- Field 2: JENIS SURAT -->
                <div>
                    <label class="form-label">Jenis Surat <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($jenisSurats as $jenis)
                            <label class="relative flex items-center p-4 rounded-2xl border cursor-pointer transition-all bg-white"
                                   :class="jenisSuratId == {{ $jenis->id }} ? 'border-primary shadow-sm bg-blue-50/20' : (errorJenis ? 'border-rose-300' : 'border-slate-200 hover:border-slate-300')">
                                <input type="radio" name="jenis_surat_id" value="{{ $jenis->id }}" x-model="jenisSuratId" @change="errorJenis = false" class="text-primary focus:ring-primary h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-navy">{{ $jenis->nama_jenis }}</span>
                                    <span class="block text-xs text-slate-500 font-medium">{{ $jenis->deskripsi }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p x-show="errorJenis" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Jenis Surat wajib dipilih sebelum menyimpan draft atau melanjutkan.
                    </p>
                    @if(session('error_jenis_surat'))
                        <p class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('error_jenis_surat') }}
                        </p>
                    @endif
                </div>

                <!-- Field 3: NOMOR SURAT -->
                <div>
                    <label class="form-label">Nomor Surat</label>
                    
                    <!-- Tab Pilihan -->
                    <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                        <button type="button" @click="modeNomor = 'otomatis'" 
                                class="pb-2 flex items-center gap-1.5 transition-colors cursor-pointer"
                                :class="modeNomor === 'otomatis' ? 'text-primary border-b-2 border-primary' : 'text-slate-400 hover:text-slate-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Otomatis
                        </button>
                        <button type="button" @click="modeNomor = 'manual'" 
                                class="pb-2 flex items-center gap-1.5 transition-colors cursor-pointer"
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
                        </div>

                        <!-- Manual Input -->
                        <div x-show="modeNomor === 'manual'" style="display: none;">
                            <input type="text" x-model="nomorManual" placeholder="Contoh: 555/123/Kominfo/IX/2026" class="form-input font-mono">
                            <p class="mt-2 text-xs text-pink-accent font-semibold">
                                Pastikan nomor manual tidak duplikat dengan arsip sebelumnya.
                            </p>
                        </div>

                        <input type="hidden" name="nomor_surat" :value="modeNomor === 'manual' ? nomorManual : (kodeSurat() + '/' + nomorUrut + '/' + formatBulanRomawi() + '/' + formatTahun())">
                    </div>
                </div>

               <!-- Field 4: TUJUAN SURAT -->
                <div>
                    <label class="form-label">Tujuan Surat</label>
                   <input type="text" name="tujuan" value="{{ old('tujuan', session('s_tujuan', '')) }}" placeholder="Contoh: Kementerian Dalam Negeri, Jakarta" class="form-input">
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Batal & Exit
                </a>
                <div class="flex items-center gap-3">
                    <!-- Tombol Simpan Draft -->
                    <button type="button" @click="simpanDraft($event)" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold cursor-pointer">
                        Simpan Draft
                    </button>
                    
                    <!-- Tombol Lanjut (Milik form utama) -->
                    <button type="submit" @click="submitLanjut($event)" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md cursor-pointer">
                        Lanjut &rarr;
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection