@extends('layouts.app')

@section('title', 'Buat Surat Telaah')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="suratTelaahWizard()">

    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.keluar') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Buat Surat Telaah</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Form -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Surat Keluar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Surat Telaah Baru</h2>
                <p class="text-xs text-slate-500 font-semibold" x-text="step === 1 ? 'Langkah 1 dari 2 • Informasi Surat Telaah' : 'Langkah 2 dari 2 • Personel & Uraian Telaahan'"></p>
            </div>
        </div>
        <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Alert Error Validasi Server -->
    @if (isset($errors) && $errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold space-y-1 shadow-sm">
        <div class="flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>Terdapat beberapa kesalahan pengisian form:</span>
        </div>
        <ul class="list-disc list-inside pl-4 font-normal">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Stepper 2 Langkah (tanpa langkah SPPD, dikendalikan punya_langkah_sppd = false) -->
    <div class="py-2 flex items-center justify-center">
        <!-- Step 1 -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md transition-all"
                 :class="step === 1 ? 'ring-4 ring-blue-100 bg-primary' : 'bg-primary'">
                <template x-if="step === 1"><span>1</span></template>
                <template x-if="step > 1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </template>
            </div>
            <span class="ml-2 text-xs font-bold" :class="step === 1 ? 'text-primary' : 'text-slate-500'">Informasi Surat</span>
        </div>

        <!-- Divider -->
        <div class="w-16 border-t-2 mx-3 transition-colors" :class="step >= 2 ? 'border-primary' : 'border-slate-200'"></div>

        <!-- Step 2 -->
        <div class="flex items-center" :class="step < 2 ? 'opacity-60' : ''">
            <div class="flex items-center justify-center w-7 h-7 rounded-full font-bold text-xs transition-all"
                 :class="step === 2 ? 'text-white bg-primary ring-4 ring-blue-100 shadow-md' : 'bg-slate-200 text-slate-500'">
                2
            </div>
            <span class="ml-2 text-xs font-semibold" :class="step === 2 ? 'text-primary font-bold' : 'text-slate-500'">Personel & Uraian</span>
        </div>
    </div>

    <!-- Form Container -->
    <form id="form-telaah" action="{{ route('surat.telaah.store') }}" method="POST" class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        @csrf

        <input type="hidden" name="spt_id" :value="selectedSpt ? selectedSpt.id : ''">
        <input type="hidden" name="mode_nomor" :value="modeNomor">

        <!-- ============================================================= -->
        <!-- LANGKAH 1: INFORMASI SURAT TELAAH                             -->
        <!-- ============================================================= -->
        <div x-show="step === 1" class="space-y-6">
            <!-- Field 1: Tanggal Telaah -->
            <div>
                <label class="form-label text-xs">Tanggal Telaah <span class="text-rose-500">*</span></label>
                <input type="date" 
                       name="tanggal_telaah" 
                       x-model="tanggalTelaah" 
                       @input="errorTanggal = false; evaluateBackdate()"
                       @change="evaluateBackdate()" 
                       class="form-input text-xs font-semibold"
                       :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
                <p x-show="errorTanggal" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tanggal Telaah wajib diisi sebelum melanjutkan.
                </p>
                @if($errors->has('tanggal_telaah'))
                    <p class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $errors->first('tanggal_telaah') }}
                    </p>
                @endif
                <!-- Notice Backdate (sama persis dengan SPT) -->
                <div x-show="isBackdate" x-cloak class="mt-2 p-3 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900 font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Tanggal surat sebelum hari ini (backdate), nomor harus diisi manual.</span>
                </div>
            </div>

            <!-- Field 2: Kartu Jenis Surat Terkunci "Surat Telaah" -->
            <div>
                <label class="form-label text-xs">Jenis Surat</label>
                <div class="p-4 rounded-2xl border border-blue-200 bg-blue-50/40 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-primary flex items-center justify-center font-bold text-lg">
                            📝
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-navy">Surat Telaah (Telaahan Staf)</span>
                                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Kode: 555/KEP/Telaah.staff</span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                Telaahan staf kedinasan. Mode nomor default manual dengan opsi otomatis berurut melanjutkan nomor lama.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field 3: Nomor Surat Telaah (Manual Default, Otomatis Opsi) -->
            <div>
                <label class="form-label text-xs">Nomor Surat Telaah <span class="text-rose-500">*</span></label>
                
                <!-- Toggle Tab Pilihan (Manual Lebih Dulu) -->
                <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                    <button type="button" @click="modeNomor = 'manual'" 
                            class="pb-2 flex items-center gap-1.5 transition-colors cursor-pointer"
                            :class="modeNomor === 'manual' ? 'text-primary border-b-2 border-primary' : 'text-slate-400 hover:text-slate-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Isi Manual (Prioritas)
                    </button>
                    <button type="button" 
                            @click="if(!isBackdate) { modeNomor = 'otomatis'; errorNomorManual = false; }" 
                            :disabled="isBackdate"
                            class="pb-2 flex items-center gap-1.5 transition-colors"
                            :class="isBackdate ? 'text-slate-300 cursor-not-allowed' : (modeNomor === 'otomatis' ? 'text-primary border-b-2 border-primary cursor-pointer' : 'text-slate-400 hover:text-slate-600 cursor-pointer')"
                            :title="isBackdate ? 'Opsi otomatis dinonaktifkan untuk tanggal mundur (backdate)' : 'Gunakan nomor otomatis berurut'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Otomatis
                        <span x-show="isBackdate" class="text-[9px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-700">Terkunci</span>
                    </button>
                </div>

                <!-- Input Mode Manual (Default) -->
                <div x-show="modeNomor === 'manual'">
                    <div class="relative">
                        <input type="text" 
                               name="nomor_manual" 
                               x-model="nomorManual" 
                               @input.debounce.400ms="errorNomorManual = false; checkDuplicateNomor()"
                               :placeholder="'Contoh: 555/KEP/Telaah.staff/01/' + formatBulanRomawi() + '/' + formatTahun()" 
                               class="form-input text-xs font-mono font-semibold"
                               :class="{ '!border-rose-400 !bg-rose-50/20': errorNomorManual || isDuplicate }">
                        <div x-show="checkingDupe" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>
                    
                    <p x-show="errorNomorManual" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Nomor Surat Telaah manual wajib diisi jika memilih mode manual.
                    </p>

                    <!-- Alert Duplikat Real-time -->
                    <div x-show="isDuplicate" x-cloak class="mt-2 p-3 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-800 font-semibold flex items-start gap-2 shadow-sm">
                        <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <span x-text="duplicateMessage"></span>
                            <template x-if="duplicateData && duplicateData.url">
                                <a :href="duplicateData.url" target="_blank" class="ml-1 underline text-primary font-bold">Lihat Dokumen &rarr;</a>
                            </template>
                        </div>
                    </div>

                    <p class="mt-2 text-xs text-slate-400 font-medium">
                        Nomor manual diterima apa adanya dan dicek keunikannya lintas seluruh arsip surat keluar.
                    </p>
                </div>

                <!-- Preview Mode Otomatis -->
                <div x-show="modeNomor === 'otomatis'" style="display: none;" class="bg-white/80 border border-blue-200/60 rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Preview Format Nomor Surat Telaah Otomatis:</p>
                    <template x-if="tanggalTelaah">
                        <div>
                            <div class="text-sm font-extrabold text-primary font-mono mb-1">
                                555/KEP/Telaah.staff/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                            </div>
                            <p class="text-xs text-slate-400 font-mono">
                                Penomoran otomatis melanjutkan urutan arsip lama sepanjang tahun tanpa reset bulanan.
                            </p>
                        </div>
                    </template>
                    <template x-if="!tanggalTelaah">
                        <div class="text-xs text-slate-500 italic py-1">
                            Pilih tanggal surat terlebih dahulu untuk menampilkan nomor otomatis.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Field 4: Rujukan SPT Induk (Searchable Dropdown) -->
            <div class="border border-blue-200/80 rounded-2xl p-4 bg-blue-50/40 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[10px] font-bold">&bull;</span>
                        <div>
                            <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Rujukan Surat Perintah Tugas (SPT)</h4>
                            <p class="text-[11px] text-slate-500">Pilih SPT untuk mengisi tujuan, uraian, dan personil secara otomatis pada langkah berikutnya.</p>
                        </div>
                    </div>
                    <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Direkomendasikan</span>
                </div>

                <div class="relative" @click.outside="showSptDropdown = false">
                    <input type="text" 
                           x-model="sptSearch" 
                           @focus="showSptDropdown = true" 
                           @click="showSptDropdown = true" 
                           @input="showSptDropdown = true" 
                           placeholder="Ketik nomor SPT atau uraian tugas untuk mencari rujukan..." 
                           class="form-input !bg-white text-xs font-mono font-semibold">
                    
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </div>

                    <!-- Dropdown List SPT -->
                    <div x-show="showSptDropdown && filteredSptList().length > 0" 
                         style="display:none;" 
                         class="absolute z-30 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                        <ul class="py-1 divide-y divide-slate-100">
                            <template x-for="spt in filteredSptList()" :key="'spt-ref-' + spt.id">
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

                <!-- SPT Terpilih Card Preview -->
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

            <!-- Tombol Navigasi Langkah 1 -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                    Batal & Exit
                </a>
                <button type="button" @click="goToStep2()" class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25 cursor-pointer">
                    <span>Lanjut ke Personel & Uraian &rarr;</span>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LANGKAH 2: PERSONEL & URAIAN TELAAHAN                         -->
        <!-- ============================================================= -->
        <div x-show="step === 2" style="display:none;" class="space-y-6">
            <!-- Field 1: Tujuan Tugas / Instansi -->
            <div>
                <label class="form-label text-xs">Tujuan Tugas / Instansi <span class="text-rose-500">*</span></label>
                <input type="text" 
                       name="tujuan" 
                       x-model="tujuan" 
                       @input="errorTujuan = false"
                       required 
                       placeholder="Contoh: Kantor Dinas Kominfo Provinsi Gorontalo, Gorontalo" 
                       class="form-input text-xs font-medium"
                       :class="{ '!border-rose-400 !bg-rose-50/20': errorTujuan }">
                <p x-show="errorTujuan" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tujuan Tugas / Instansi wajib diisi.
                </p>
            </div>

            <!-- Field 2: Uraian / Maksud Telaahan Staf -->
            <div>
                <label class="form-label text-xs">Uraian / Maksud Telaahan Staf <span class="text-rose-500">*</span></label>
                <textarea name="uraian" 
                          x-model="uraian" 
                          @input="errorUraian = false"
                          rows="3" 
                          required 
                          placeholder="Tuliskan pokok telaahan staf / maksud uraian penugasan..." 
                          class="form-input text-xs font-medium !h-auto"
                          :class="{ '!border-rose-400 !bg-rose-50/20': errorUraian }"></textarea>
                <p x-show="errorUraian" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Uraian / Maksud Telaahan Staf wajib diisi.
                </p>
            </div>

            <!-- Alert Validasi Personil Client-side -->
            <div x-show="errorPegawai" x-cloak class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Pilih minimal 1 personil yang ditugaskan.</span>
                </div>
                <button type="button" @click="errorPegawai = false" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Field 3: Personel yang Ditugaskan (Komponen Pemilih Pegawai SPT) -->
            <x-personil-selector label="Personil yang Ditugaskan" subtitle="Personil otomatis terisi dari rujukan SPT atau dapat dipilih mandiri." />

            <!-- Field 4: Keterangan Tambahan -->
            <div>
                <label class="form-label text-xs">Keterangan Tambahan (Opsional, Maks 500 Karakter)</label>
                <textarea name="keterangan" 
                          x-model="keterangan" 
                          rows="2" 
                          maxlength="500" 
                          placeholder="Catatan tambahan bila diperlukan..." 
                          class="form-input text-xs !h-auto"></textarea>
            </div>

            <!-- Field 5: Link Google Drive -->
            <div>
                <label for="link_google_drive" class="form-label text-xs">Link Google Drive <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <input type="url" 
                           id="link_google_drive" 
                           name="link_google_drive" 
                           x-model="linkGoogleDrive" 
                           placeholder="https://drive.google.com/file/d/.../view?usp=sharing" 
                           class="form-input !pl-10 font-mono text-xs">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Tautan ke scan atau foto surat fisik yang disimpan di Google Drive (opsional).</span>
            </div>

            <!-- Tombol Navigasi Langkah 2 -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button" @click="step = 1" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold cursor-pointer">
                        &larr; Kembali ke Informasi Surat
                    </button>
                    <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-4 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Batal
                    </a>
                </div>
                <button type="button" @click="submitTelaah($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25">
                    <span x-text="submitting ? 'Memproses...' : 'Terbitkan & Simpan Surat Telaah'"></span>
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function suratTelaahWizard() {
    return {
        step: 1,
        spts: @json($spts),
        allPegawais: @json($pegawais),
        selectedSpt: null,
        sptSearch: '',
        showSptDropdown: false,
        modeNomor: '{{ old('mode_nomor', 'manual') }}',
        nomorUrut: '{{ $nomorUrut ?? '01' }}',
        nomorManual: '{{ old('nomor_manual', '') }}',
        tanggalTelaah: '{{ old('tanggal_telaah', '') }}',
        uraian: '{{ addslashes(old('uraian', '')) }}',
        tujuan: '{{ addslashes(old('tujuan', '')) }}',
        keterangan: '{{ addslashes(old('keterangan', '')) }}',
        linkGoogleDrive: '{{ addslashes(old('link_google_drive', '')) }}',
        selectedPegawaiIds: @json(array_values(array_map('intval', (array)old('pegawai_id', [])))),
        parentSptPegawaiIds: [],
        
        searchPegawai: '',
        showPegawaiDropdown: false,
        isBackdate: false,
        checkingDupe: false,
        isDuplicate: false,
        duplicateMessage: '',
        duplicateData: null,
        
        errorTanggal: false,
        errorNomorManual: false,
        errorTujuan: false,
        errorUraian: false,
        errorPegawai: false,
        submitting: false,

        showP3kModal: false,
        p3kLoading: false,
        p3kError: '',
        p3kForm: { nama: '', nip: '', jabatan: '', pangkat_golongan: '', kategori_pegawai: 'P3K' },

        init() {
            if (this.tanggalTelaah) {
                this.evaluateBackdate();
            }
            if (this.nomorManual) {
                this.checkDuplicateNomor();
            }
            const preSptId = '{{ old('spt_id', request('spt_id', '')) }}';
            if (preSptId && this.spts) {
                const found = this.spts.find(s => String(s.id) === String(preSptId));
                if (found) {
                    this.selectSpt(found);
                }
            }
        },

        filteredSptList() {
            if (!this.spts) return [];
            const q = this.sptSearch ? this.sptSearch.toLowerCase().trim() : '';
            if (!q) return this.spts.slice(0, 20);
            return this.spts.filter(s => 
                (s.nomor_surat && s.nomor_surat.toLowerCase().includes(q)) ||
                (s.uraian && s.uraian.toLowerCase().includes(q)) ||
                (s.tujuan && s.tujuan.toLowerCase().includes(q)) ||
                (s.tgl_surat && s.tgl_surat.includes(q))
            ).slice(0, 20);
        },

        selectSpt(spt) {
            this.selectedSpt = spt;
            this.sptSearch = spt.nomor_surat;
            this.showSptDropdown = false;
            if (spt.tujuan && !this.tujuan) {
                this.tujuan = spt.tujuan;
            }
            if ((spt.uraian || spt.perihal) && !this.uraian) {
                this.uraian = spt.uraian || spt.perihal;
            }
            if (spt.pegawais && Array.isArray(spt.pegawais) && spt.pegawais.length > 0) {
                spt.pegawais.forEach(p => {
                    if (!this.allPegawais.some(ap => Number(ap.id) === Number(p.id))) {
                        this.allPegawais.push({
                            id: Number(p.id),
                            nama: p.nama,
                            nip: p.nip || '',
                            jabatan: p.jabatan || '',
                            kategori_pegawai: p.kategori_pegawai || 'ASN',
                            pangkat_golongan: p.pangkat_golongan || ''
                        });
                    }
                });
                this.parentSptPegawaiIds = spt.pegawais.map(p => Number(p.id));
                if (!this.selectedPegawaiIds || this.selectedPegawaiIds.length === 0) {
                    this.selectedPegawaiIds = spt.pegawais.map(p => Number(p.id));
                }
            }
        },

        clearSpt() {
            this.selectedSpt = null;
            this.parentSptPegawaiIds = [];
            this.sptSearch = '';
            this.showSptDropdown = false;
        },

        formatBulanRomawi() {
            if (!this.tanggalTelaah) return 'MM';
            const parts = this.tanggalTelaah.split('-');
            if (parts.length < 2) return 'MM';
            const m = parseInt(parts[1], 10);
            const r = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            return r[m-1] || 'MM';
        },

        formatTahun() {
            if (!this.tanggalTelaah) return 'YYYY';
            return this.tanggalTelaah.split('-')[0] || 'YYYY';
        },

        evaluateBackdate() {
            if (!this.tanggalTelaah || this.tanggalTelaah.trim() === '') {
                this.isBackdate = false;
                return;
            }
            fetch(`/surat/api/check-backdate-kategori?kategori=Surat%20Telaah&tgl_surat=${encodeURIComponent(this.tanggalTelaah)}`)
                .then(res => res.json())
                .then(data => {
                    if (data) {
                        const wasBackdate = this.isBackdate;
                        this.isBackdate = Boolean(data.is_backdate);
                        if (this.isBackdate) {
                            this.modeNomor = 'manual';
                        } else {
                            if (data.next_seq) {
                                this.nomorUrut = data.next_seq;
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
                this.duplicateMessage = '';
                return;
            }
            this.checkingDupe = true;
            fetch(`/surat/api/check-nomor-duplikat?nomor=${encodeURIComponent(clean)}`)
                .then(res => res.json())
                .then(data => {
                    this.checkingDupe = false;
                    this.isDuplicate = Boolean(data.is_duplicate);
                    if (data.is_duplicate) {
                        this.duplicateMessage = data.message;
                        this.duplicateData = data.existing;
                    }
                })
                .catch(() => {
                    this.checkingDupe = false;
                });
        },

        filteredPegawais() {
            if (!this.allPegawais) return [];
            const q = this.searchPegawai ? this.searchPegawai.toLowerCase().trim() : '';
            if (!q) {
                return this.allPegawais.slice(0, 15);
            }
            const searchWords = q.split(/\s+/).filter(Boolean);
            return this.allPegawais.filter(p => {
                if (!p || !p.id) return false;
                const nama = String(p.nama || '').toLowerCase();
                const nip = String(p.nip || '').toLowerCase();
                const jabatan = String(p.jabatan || '').toLowerCase();
                const kat = String(p.kategori_pegawai || 'ASN').toLowerCase();
                const fullText = `${nama} ${nip} ${jabatan} ${kat}`;
                return searchWords.every(word => fullText.includes(word));
            }).slice(0, 15);
        },

        getSelectedPegawaiObjects() {
            if (!this.selectedPegawaiIds || !Array.isArray(this.selectedPegawaiIds)) return [];
            return this.selectedPegawaiIds.map(id => {
                const num = Number(typeof id === 'object' && id !== null ? id.id : id);
                return this.allPegawais.find(p => Number(p.id) === num);
            }).filter(Boolean);
        },

        isOutsideSpt(id) {
            const sptIds = (this.parentSptPegawaiIds && this.parentSptPegawaiIds.length > 0)
                ? this.parentSptPegawaiIds
                : (this.selectedSpt && this.selectedSpt.pegawais ? this.selectedSpt.pegawais.map(p => Number(p.id)) : []);
            if (!sptIds || sptIds.length === 0) return false;
            return !sptIds.some(pid => Number(pid) === Number(id));
        },

        addPegawai(target) {
            const p = (typeof target === 'object' && target !== null) ? target : this.allPegawais.find(x => Number(x.id) === Number(target));
            const id = p ? Number(p.id) : Number(target);
            if (!id) return;
            if (p && !this.allPegawais.some(x => Number(x.id) === Number(p.id))) {
                this.allPegawais.push(p);
            }
            if (!this.selectedPegawaiIds.some(pid => Number(pid) === id)) {
                this.selectedPegawaiIds.push(id);
                this.errorPegawai = false;
            }
            this.searchPegawai = '';
            this.showPegawaiDropdown = false;
        },

        removePegawai(id) {
            const numId = Number(id);
            this.selectedPegawaiIds = this.selectedPegawaiIds.filter(pid => Number(pid) !== numId);
        },

        getPegawai(id) {
            const numId = Number(id);
            return this.allPegawais.find(p => Number(p.id) === numId);
        },

        openModalP3k() {
            this.p3kForm = { nama: '', nip: '', jabatan: '', pangkat_golongan: '', kategori_pegawai: 'P3K' };
            this.p3kError = '';
            this.showP3kModal = true;
        },

        closeModalP3k() {
            this.showP3kModal = false;
        },

        async submitP3k() {
            if (!this.p3kForm.nama.trim()) {
                this.p3kError = 'Nama lengkap wajib diisi.';
                return;
            }
            this.p3kLoading = true;
            this.p3kError = '';

            try {
                const response = await fetch('{{ route('surat.api.storePegawaiP3k') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.p3kForm)
                });

                const res = await response.json();
                if (res.success && res.data) {
                    this.allPegawais.push(res.data);
                    this.addPegawai(res.data.id);
                    this.closeModalP3k();
                } else {
                    this.p3kError = res.message || 'Gagal menambahkan data pegawai.';
                }
            } catch (err) {
                this.p3kError = 'Terjadi kesalahan jaringan.';
            } finally {
                this.p3kLoading = false;
            }
        },

        goToStep2() {
            this.errorTanggal = !this.tanggalTelaah || !this.tanggalTelaah.trim();
            if (this.modeNomor === 'manual') {
                this.errorNomorManual = !this.nomorManual || !this.nomorManual.trim();
            } else {
                this.errorNomorManual = false;
            }

            if (this.errorTanggal || this.errorNomorManual) {
                return false;
            }

            if (this.modeNomor === 'manual' && this.isDuplicate) {
                alert('Nomor surat telaah sudah terdaftar di database. Silakan gunakan nomor lain.');
                return false;
            }

            this.step = 2;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        submitTelaah(e) {
            if (e) e.preventDefault();
            this.errorTujuan = !this.tujuan.trim();
            this.errorUraian = !this.uraian.trim();
            this.errorPegawai = this.selectedPegawaiIds.length === 0;

            if (this.errorTujuan || this.errorUraian || this.errorPegawai) {
                return false;
            }

            if (this.modeNomor === 'manual' && this.isDuplicate) {
                alert('Nomor surat sudah terdaftar di database. Silakan gunakan nomor lain.');
                return false;
            }

            this.submitting = true;
            document.getElementById('form-telaah').submit();
        }
    };
}
</script>
@endsection
