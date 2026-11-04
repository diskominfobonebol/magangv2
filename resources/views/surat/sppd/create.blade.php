@extends('layouts.app')

@section('title', 'Buat Nomor SPPD Baru - Langkah 1')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="createSppdStep1()">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.keluar') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Buat SPPD</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Surat Keluar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Nomor SPPD Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 1 dari 3 &bull; Informasi Nomor Surat Perjalanan Dinas</p>
            </div>
        </div>
        <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
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

    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold shadow-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Stepper (3 Langkah bergaya SPT) -->
    <div class="py-2 flex items-center justify-center">
        <!-- Step 1 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-pink-100" style="background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%);">
                1
            </div>
            <span class="ml-2 text-xs font-bold text-pink-600">Informasi Nomor Surat</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-slate-200 mx-2"></div>
        <!-- Step 2 (Inactive) -->
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">
                2
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-500">Personel & Uraian</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-slate-200 mx-2"></div>
        <!-- Step 3 (Inactive) -->
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">
                3
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-500">Nomor SPPD</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
        <form action="{{ route('surat.sppd.create.step2') }}" method="POST" class="p-6 md:p-8" id="form-sppd" @submit.prevent="submitLanjut($event)">
            @csrf

            <input type="hidden" name="jenis_surat_id" value="1">
            <input type="hidden" name="parent_id" :value="sptMode === 'pilih' ? (selectedSpt ? selectedSpt.id : '') : ''">
            <input type="hidden" name="spt_induk_manual" :value="sptMode === 'manual' ? sptManual : ''">
            <input type="hidden" name="mode_nomor" :value="modeNomor">
            <input type="hidden" name="nomor_surat_manual" :value="nomorManual">
            <input type="hidden" name="tujuan" :value="tujuan">
            <input type="hidden" name="uraian" :value="uraian">

            <div class="space-y-6">
                <!-- Field 1: TANGGAL SURAT -->
                <div>
                    <label class="form-label text-xs">Tanggal Nomor SPPD <span class="text-rose-500">*</span></label>
                    <input type="date" 
                           name="tgl_surat" 
                           x-model="tanggal" 
                           @change="evaluateBackdate()" 
                           @input="errorTanggal = false; evaluateBackdate()" 
                           class="form-input text-xs font-semibold" 
                           :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
                    <p x-show="errorTanggal" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tanggal Nomor SPPD wajib diisi sebelum menyimpan draft atau melanjutkan.
                    </p>
                    @if(session('error_tgl_surat') || $errors->has('tgl_surat'))
                        <p class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('error_tgl_surat') ?? $errors->first('tgl_surat') }}
                        </p>
                    @endif
                </div>

                <!-- Field 2: JENIS SURAT (SPPD Terkunci Kode 090) -->
                <div>
                    <label class="form-label text-xs">Jenis Surat</label>
                    <div class="p-4 rounded-2xl border border-pink-200 bg-pink-50/40 flex items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-lg">
                                ⚡
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-navy">Surat Perjalanan Dinas (SPPD)</span>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-pink-100 text-pink-700 border border-pink-200">Kode: 090</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    Surat tugas perjalanan kedinasan yang wajib merujuk ke dokumen Surat Perintah Tugas (SPT) Induk.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Field 3: SPT INDUK (WAJIB: TAB PILIH DATABASE / MANUAL) -->
                <div class="border border-blue-200/80 rounded-2xl p-4 bg-blue-50/30 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-[10px] font-bold">&bull;</span>
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase tracking-wider">SPT Induk Rujukan <span class="text-rose-500">*</span></h4>
                                <p class="text-[11px] text-slate-500">Pilih dokumen SPT dari database atau ketik nomor SPT secara manual bila SPT belum tercatat di sistem.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Mode SPT Induk -->
                    <div class="flex items-center gap-4 text-xs font-bold">
                        <button type="button" @click="sptMode = 'pilih'; errorSpt = false"
                                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5"
                                :class="sptMode === 'pilih' ? 'bg-primary text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7z"></path></svg>
                            Pilih dari Database (Rekomendasi)
                        </button>
                        <button type="button" @click="sptMode = 'manual'; errorSpt = false"
                                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5"
                                :class="sptMode === 'manual' ? 'bg-primary text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Ketik Nomor Manual
                        </button>
                    </div>

                    <!-- Tab A: Pilih dari Database -->
                    <div x-show="sptMode === 'pilih'" class="space-y-2">
                        <div class="relative" @click.outside="showSptDropdown = false">
                            <input type="text" 
                                   x-model="sptSearch" 
                                   @focus="showSptDropdown = true" 
                                   @click="showSptDropdown = true" 
                                   @input="showSptDropdown = true; errorSpt = false" 
                                   placeholder="Cari nomor SPT, uraian tugas, atau tujuan..." 
                                   class="form-input !bg-white text-xs font-mono font-semibold"
                                   :class="{ '!border-rose-400 !bg-rose-50/20': errorSpt }">
                            
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <!-- Dropdown List -->
                            <div x-show="showSptDropdown && filteredSptList().length > 0" 
                                 style="display:none;" 
                                 class="absolute z-30 w-full mt-1 bg-white border border-blue-200 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                <ul class="py-1 divide-y divide-slate-100">
                                    <template x-for="spt in filteredSptList()" :key="'spt-db-' + spt.id">
                                        <li @mousedown.prevent="selectSpt(spt)" class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors flex items-center justify-between group">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-primary text-xs" x-text="spt.nomor_surat"></span>
                                                    <span class="text-[10px] text-slate-400" x-text="'• ' + spt.tgl_surat"></span>
                                                </div>
                                                <p class="text-[11px] text-slate-600 truncate max-w-lg mt-0.5" x-text="spt.uraian || spt.perihal || 'Surat Perintah Tugas'"></p>
                                                <span class="text-[10px] text-slate-400" x-text="'Tujuan: ' + (spt.tujuan || '-')"></span>
                                            </div>
                                            <span class="text-[10px] font-bold text-primary bg-blue-100 px-2 py-0.5 rounded-lg shrink-0 ml-2">Pilih &crarr;</span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- Card SPT Terpilih -->
                        <div x-show="selectedSpt" x-cloak class="bg-gradient-to-r from-emerald-50 to-blue-50 border border-emerald-200 rounded-xl p-3 flex items-start justify-between text-xs shadow-sm">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-navy text-xs" x-text="selectedSpt?.nomor_surat"></span>
                                    <span class="badge-green text-[9px] px-1.5 py-0.2 rounded-full font-bold">Terhubung</span>
                                    <span class="text-[10px] text-slate-500 font-medium" x-text="'Tanggal: ' + selectedSpt?.tgl_surat"></span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1 font-medium" x-text="selectedSpt?.uraian || selectedSpt?.perihal || 'Surat Perintah Tugas'"></p>
                                <p class="text-[10px] text-slate-500 mt-0.5" x-text="'Tujuan: ' + (selectedSpt?.tujuan || '-')"></p>
                            </div>
                            <button type="button" @click="clearSpt()" class="text-xs text-rose-500 font-bold hover:underline cursor-pointer ml-3">
                                Ganti / Lepas
                            </button>
                        </div>
                    </div>

                    <!-- Tab B: Ketik Manual -->
                    <div x-show="sptMode === 'manual'" style="display:none;" class="space-y-2">
                        <input type="text" 
                               x-model="sptManual" 
                               @input="errorSpt = false"
                               placeholder="Contoh: 555/KOMINFO-BB/SPT-DD/001/X/2026" 
                               class="form-input text-xs font-mono font-semibold"
                               :class="{ '!border-rose-400 !bg-rose-50/20': errorSpt }">
                        <p class="text-[11px] text-slate-400">
                            Gunakan opsi ini jika dokumen SPT diterbitkan secara offline atau belum diarsipkan ke dalam sistem.
                        </p>
                    </div>

                    <p x-show="errorSpt" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.
                    </p>
                </div>

                <!-- Field 4: NOMOR SURAT SPPD (Otomatis Default / Manual Opsi) -->
                <div>
                    <label class="form-label text-xs">Nomor Surat SPPD</label>
                    
                    <!-- Tab Pilihan -->
                    <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                        <button type="button" 
                                @click="!isBackdate && (modeNomor = 'otomatis')" 
                                :disabled="isBackdate"
                                class="pb-2 flex items-center gap-1.5 transition-colors"
                                :class="isBackdate ? 'text-slate-300 cursor-not-allowed opacity-60' : (modeNomor === 'otomatis' ? 'text-pink-600 border-b-2 border-pink-600 cursor-pointer' : 'text-slate-400 hover:text-slate-600 cursor-pointer')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Otomatis (Default)
                            <span x-show="isBackdate" x-cloak class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded-full font-bold">Terkunci</span>
                        </button>
                        <button type="button" @click="modeNomor = 'manual'" 
                                class="pb-2 flex items-center gap-1.5 transition-colors cursor-pointer"
                                :class="modeNomor === 'manual' ? 'text-pink-600 border-b-2 border-pink-600' : 'text-slate-400 hover:text-slate-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Isi Manual
                        </button>
                    </div>

                    <!-- Dynamic Content Area -->
                    <div class="mt-1 space-y-2.5">
                        <!-- Notice Banner Backdate -->
                        <div x-show="isBackdate" x-cloak class="p-3.5 bg-amber-50/90 border border-amber-300/80 rounded-2xl text-xs text-amber-900 font-semibold flex items-start gap-2.5 shadow-sm">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div class="flex-1">
                                <span>Tanggal surat sebelum hari ini (backdate), nomor harus diisi manual.</span>
                            </div>
                        </div>

                        <!-- Otomatis Preview -->
                        <div x-show="modeNomor === 'otomatis' && !isBackdate" class="bg-white/80 border border-blue-200/50 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Preview Format Nomor SPPD:</p>
                                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Otomatis Berurut</span>
                            </div>
                            
                            <template x-if="tanggal">
                                <div>
                                    <div class="text-sm font-extrabold text-pink-600 mb-1 font-mono">
                                        090/KOMINFO-BB/SPPD/<span x-text="detectJenisPenugasan()"></span>/<span x-text="nomorUrutSppd"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                                    </div>
                                    <p class="text-xs text-slate-400 font-mono">
                                        Penomoran berlanjut sepanjang tahun tanpa reset per bulan. Nomor spesifik per pegawai akan diatur pada Langkah 3.
                                    </p>
                                </div>
                            </template>
                            <template x-if="!tanggal">
                                <div class="text-xs text-slate-500 italic py-1">
                                    Pilih tanggal surat terlebih dahulu untuk menampilkan nomor otomatis.
                                </div>
                            </template>
                        </div>

                        <!-- Manual Input -->
                        <div x-show="modeNomor === 'manual'" style="display: none;" class="space-y-2">
                            <div class="relative">
                                <input type="text" 
                                       x-model="nomorManual" 
                                       @input.debounce.400ms="checkDuplicateNomor()" 
                                       :placeholder="'Contoh: 090/KOMINFO-BB/SPPD/' + detectJenisPenugasan() + '/001/' + formatBulanRomawi() + '/' + formatTahun()" 
                                       class="form-input font-mono font-semibold text-xs" 
                                       :class="{ '!border-rose-400 !bg-rose-50/30': isDuplicate }">
                                <div x-show="checkingDupe" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                            </div>
                            
                            <!-- Duplicate Warning -->
                            <div x-show="isDuplicate" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-rose-800">
                                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span>Nomor Surat Sudah Digunakan</span>
                                </div>
                                <p>
                                    Nomor <strong class="font-mono" x-text="nomorManual"></strong> sudah digunakan pada <strong x-text="duplicateInfo.kategori"></strong> tanggal <span x-text="duplicateInfo.tgl_surat"></span>.
                                    <template x-if="duplicateInfo.link">
                                        <a :href="duplicateInfo.link" target="_blank" class="text-primary font-bold underline ml-1">Lihat Surat &rarr;</a>
                                    </template>
                                </p>
                            </div>

                            <p class="text-xs text-slate-500">
                                Nomor manual wajib unik di seluruh kategori surat keluar.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Batal & Exit
                </a>
                <div class="flex items-center gap-3">
                    <!-- Tombol Simpan Draft -->
                    <button type="button" @click="simpanDraft($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Draft'"></span>
                    </button>
                    
                    <!-- Tombol Lanjut ke Step 2 -->
                    <button type="button" @click="submitLanjut($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-primary !bg-pink-600 hover:!bg-pink-700 !shadow-pink-500/25 px-6 py-2.5 text-xs font-bold gap-2 shadow-md flex items-center">
                        <span x-text="submitting ? 'Memproses...' : 'Lanjut &rarr;'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function createSppdStep1() {
    return {
        tanggal: '{{ old('tgl_surat', session('s_sppd_tgl_surat', '')) }}',
        spts: @json($spts),
        sptMode: '{{ session('s_sppd_spt_induk_manual') ? 'manual' : 'pilih' }}',
        sptSearch: '',
        showSptDropdown: false,
        selectedSpt: null,
        sptManual: '{{ addslashes(old('spt_induk_manual', session('s_sppd_spt_induk_manual', ''))) }}',
        tujuan: '{{ addslashes(old('tujuan', session('s_sppd_tujuan', ''))) }}',
        uraian: '{{ addslashes(old('uraian', session('s_sppd_uraian', ''))) }}',
        
        modeNomor: '{{ old('mode_nomor', session('s_sppd_mode_nomor', 'otomatis')) }}',
        nomorUrutSppd: '{{ $nomorUrutSppd ?? '001' }}',
        nomorManual: '{{ old('nomor_surat_manual', session('s_sppd_nomor_surat', '')) }}',
        
        isBackdate: {{ ($eval['is_backdate'] ?? false) ? 'true' : 'false' }},
        checkingDupe: false,
        isDuplicate: false,
        duplicateInfo: { kategori: '', tgl_surat: '', link: '' },
        errorTanggal: false,
        errorSpt: false,
        submitting: false,

        init() {
            const savedParentId = '{{ session('s_sppd_parent_id') }}';
            if (savedParentId) {
                const found = this.spts.find(s => s.id == savedParentId);
                if (found) {
                    this.selectSpt(found);
                }
            }
            if (this.tanggal) {
                this.evaluateBackdate();
            }
            if (this.nomorManual) {
                this.checkDuplicateNomor();
            }
        },

        filteredSptList() {
            if (!this.spts) return [];
            const q = this.sptSearch ? this.sptSearch.toLowerCase().trim() : '';
            if (!q) return this.spts.slice(0, 30);
            return this.spts.filter(s => 
                (s.nomor_surat && s.nomor_surat.toLowerCase().includes(q)) ||
                (s.uraian && s.uraian.toLowerCase().includes(q)) ||
                (s.tujuan && s.tujuan.toLowerCase().includes(q)) ||
                (s.tgl_surat && s.tgl_surat.includes(q))
            ).slice(0, 30);
        },

        selectSpt(spt) {
            this.selectedSpt = spt;
            this.sptSearch = spt.nomor_surat;
            this.showSptDropdown = false;
            this.errorSpt = false;
            if (spt.tujuan && !this.tujuan) {
                this.tujuan = spt.tujuan;
            }
            if (spt.uraian && !this.uraian) {
                this.uraian = spt.uraian;
            }
        },

        clearSpt() {
            this.selectedSpt = null;
            this.sptSearch = '';
            this.showSptDropdown = false;
        },

        formatBulanRomawi() {
            if (!this.tanggal) return 'MM';
            const parts = this.tanggal.split('-');
            if (parts.length < 2) return 'MM';
            const m = parseInt(parts[1], 10);
            const r = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            return r[m-1] || 'MM';
        },

        formatTahun() {
            if (!this.tanggal) return 'YYYY';
            return this.tanggal.split('-')[0] || 'YYYY';
        },

        detectJenisPenugasan() {
            const text = (this.tujuan || '').toLowerCase();
            const luarDaerahKeywords = ['jakarta', 'makassar', 'surabaya', 'bandung', 'palu', 'manado', 'kemen', 'kementerian'];
            for (let kw of luarDaerahKeywords) {
                if (text.includes(kw)) return 'DL';
            }
            return 'DD';
        },

        evaluateBackdate() {
            if (!this.tanggal || this.tanggal.trim() === '') {
                this.isBackdate = false;
                return;
            }
            fetch(`/surat/api/check-backdate-kategori?kategori=SPPD&tgl_surat=${encodeURIComponent(this.tanggal)}`)
                .then(res => res.json())
                .then(data => {
                    if (data) {
                        const wasBackdate = this.isBackdate;
                        this.isBackdate = Boolean(data.is_backdate);
                        if (this.isBackdate) {
                            this.modeNomor = 'manual';
                        } else {
                            if (wasBackdate && this.modeNomor === 'manual') {
                                this.modeNomor = 'otomatis';
                            }
                            if (data.next_seq) {
                                this.nomorUrutSppd = data.next_seq;
                            }
                        }
                    }
                })
                .catch(() => {});
        },

        checkDuplicateNomor() {
            const clean = (this.nomorManual || '').trim();
            if (!clean) {
                this.isDuplicate = false;
                return;
            }
            this.checkingDupe = true;
            fetch(`/surat/api/check-nomor-duplikat?nomor=${encodeURIComponent(clean)}`)
                .then(res => res.json())
                .then(data => {
                    this.checkingDupe = false;
                    this.isDuplicate = Boolean(data.is_duplicate);
                    if (data.is_duplicate && data.existing) {
                        this.duplicateInfo = {
                            kategori: data.existing.kategori || 'Surat Lain',
                            tgl_surat: data.existing.tgl_surat || '-',
                            link: data.existing.url || ''
                        };
                    }
                })
                .catch(() => {
                    this.checkingDupe = false;
                });
        },

        submitLanjut(e) {
            if (e) e.preventDefault();
            this.errorTanggal = !this.tanggal || !this.tanggal.trim();
            
            if (this.sptMode === 'pilih') {
                this.errorSpt = !this.selectedSpt;
            } else {
                this.errorSpt = !this.sptManual.trim();
            }

            if (this.errorTanggal || this.errorSpt) {
                this.submitting = false;
                return false;
            }

            if (this.modeNomor === 'manual') {
                if (!this.nomorManual || !this.nomorManual.trim()) {
                    alert('Nomor surat SPPD manual wajib diisi jika memilih mode manual.');
                    this.submitting = false;
                    return false;
                }
                if (this.isDuplicate) {
                    alert('Nomor surat sudah terdaftar di database. Silakan gunakan nomor lain.');
                    this.submitting = false;
                    return false;
                }
            }

            this.submitting = true;
            document.getElementById('form-sppd').submit();
        },

        simpanDraft(e) {
            if (e) e.preventDefault();
            this.errorTanggal = !this.tanggal || !this.tanggal.trim();
            if (this.errorTanggal) {
                this.submitting = false;
                return false;
            }

            if (this.modeNomor === 'manual') {
                if (!this.nomorManual || !this.nomorManual.trim()) {
                    alert('Nomor surat SPPD manual wajib diisi jika memilih mode manual.');
                    this.submitting = false;
                    return false;
                }
                if (this.isDuplicate) {
                    alert('Nomor surat sudah terdaftar di database. Silakan gunakan nomor lain.');
                    this.submitting = false;
                    return false;
                }
            }

            this.submitting = true;
            const form = document.getElementById('form-sppd');
            form.action = '{{ route('surat.sppd.draft') }}';
            form.submit();
        }
    };
}
</script>
@endsection
