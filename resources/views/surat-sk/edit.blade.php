@extends('layouts.app')

@section('title', 'Edit Surat Keputusan (SK)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="suratSkEditWizard()">

    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.keluar') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <a href="{{ route('surat.sk.show', $sk->id) }}" class="hover:text-primary transition-colors font-semibold">Detail SK</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Edit Surat SK</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Form -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.sk.show', $sk->id) }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Edit Surat Keputusan (SK)</h2>
                <p class="text-xs text-slate-500 font-semibold" x-text="step === 1 ? 'Langkah 1 dari 2 • Informasi Surat Keputusan' : 'Langkah 2 dari 2 • Uraian & Lampiran Dokumen'"></p>
            </div>
        </div>
        <a href="{{ route('surat.sk.show', $sk->id) }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
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

    <!-- Stepper 2 Langkah (bergaya SPT) -->
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
            <span class="ml-2 text-xs font-semibold" :class="step === 2 ? 'text-primary font-bold' : 'text-slate-500'">Uraian & Lampiran</span>
        </div>
    </div>

    <!-- Form Body -->
    <form id="form-sk-edit" action="{{ route('surat.sk.update', $sk->id) }}" method="POST" class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        @csrf
        @method('PUT')

        <input type="hidden" name="mode_nomor" :value="modeNomor">

        <!-- ============================================================= -->
        <!-- LANGKAH 1: INFORMASI SURAT KEPUTUSAN                         -->
        <!-- ============================================================= -->
        <div x-show="step === 1" class="space-y-6">
            <!-- Field 1: Tanggal SK -->
            <div>
                <label class="form-label text-xs">Tanggal SK Ditetapkan <span class="text-rose-500">*</span></label>
                <input type="date" 
                       name="tanggal_sk" 
                       x-model="tanggalSk" 
                       @input="errorTanggal = false"
                       @change="evaluateBackdate()"
                       required 
                       class="form-input text-xs font-semibold"
                       :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
                <p x-show="errorTanggal" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tanggal SK wajib diisi sebelum melanjutkan.
                </p>
                <!-- Notice Backdate -->
                <div x-show="isBackdate" x-cloak class="mt-2 p-3 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900 font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Tanggal surat sebelum hari ini (backdate), nomor harus diisi manual.</span>
                </div>
            </div>

            <!-- Field 2: Kartu Jenis Surat Terkunci "Surat Keputusan (SK)" -->
            <div>
                <label class="form-label text-xs">Jenis Surat</label>
                <div class="p-4 rounded-2xl border border-blue-200 bg-blue-50/40 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-primary flex items-center justify-center font-bold text-lg">
                            📜
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-navy">Surat Keputusan (SK)</span>
                                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Kode: 555/KEP/Kominfo.BB</span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                Surat Keputusan Kepala Dinas. Mode nomor default manual dengan opsi otomatis berurut melanjutkan nomor lama.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field 3: Nomor Surat SK -->
            <div>
                <label class="form-label text-xs">Nomor Surat SK <span class="text-rose-500">*</span></label>
                
                <!-- Toggle Tab Pilihan -->
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
                               class="form-input text-xs font-mono font-semibold"
                               :class="{ '!border-rose-400 !bg-rose-50/20': errorNomorManual || isDuplicate }">
                        <div x-show="checkingDupe" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>
                    
                    <p x-show="errorNomorManual" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Nomor Surat SK manual wajib diisi jika memilih mode manual.
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
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Preview Format Nomor Surat SK Otomatis:</p>
                    <div class="text-sm font-extrabold text-primary font-mono mb-1">
                        555/KEP/Kominfo.BB/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                    </div>
                    <p class="text-xs text-slate-400 font-mono">
                        Penomoran otomatis melanjutkan urutan arsip lama sepanjang tahun tanpa reset bulanan.
                    </p>
                </div>
            </div>

            <!-- Tombol Navigasi Langkah 1 -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('surat.sk.show', $sk->id) }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                    Batal
                </a>
                <button type="button" @click="goToStep2()" class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25 cursor-pointer">
                    <span>Lanjut ke Uraian & Lampiran &rarr;</span>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LANGKAH 2: URAIAN & LAMPIRAN DOKUMEN                         -->
        <!-- ============================================================= -->
        <div x-show="step === 2" style="display:none;" class="space-y-6">
            <!-- Field 1: Tentang / Hal SK -->
            <div>
                <label class="form-label text-xs">Hal / Tentang Surat Keputusan <span class="text-rose-500">*</span></label>
                <textarea name="tentang" 
                          x-model="tentang"
                          @input="errorTentang = false"
                          rows="3" 
                          required 
                          class="form-input text-xs font-medium !h-auto"
                          :class="{ '!border-rose-400 !bg-rose-50/20': errorTentang }"></textarea>
                <p x-show="errorTentang" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Hal / Tentang SK wajib diisi.
                </p>
            </div>

            <!-- Field 2: Keterangan Tambahan -->
            <div>
                <label class="form-label text-xs">Keterangan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional, maks 500 karakter)</span></label>
                <textarea name="keterangan" 
                          rows="2" 
                          maxlength="500" 
                          class="form-input text-xs !h-auto">{{ old('keterangan', $sk->keterangan) }}</textarea>
            </div>

            <!-- Field 3: Link Google Drive -->
            <div>
                <label for="link_google_drive" class="form-label text-xs">Link Google Drive <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <input type="url" 
                           id="link_google_drive" 
                           name="link_google_drive" 
                           value="{{ old('link_google_drive', $sk->link_google_drive) }}" 
                           placeholder="https://drive.google.com/file/d/.../view?usp=sharing" 
                           class="form-input !pl-10 font-mono text-xs @error('link_google_drive') !border-rose-400 !ring-1 !ring-rose-400 @enderror">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Tautan ke scan atau berkas SK yang disimpan di Google Drive (opsional).</span>
                @error('link_google_drive')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Berkas Lampiran Fisik Sebelumnya (jika ada) -->
            @if($sk->file_sk)
            <div>
                <label class="form-label text-xs">Berkas Lampiran Fisik (Tersimpan Sebelumnya)</label>
                <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl text-xs flex items-center justify-between">
                    <span class="text-navy font-semibold flex items-center gap-1.5">
                        📎 Berkas: {{ basename($sk->file_sk) }}
                    </span>
                    <a href="{{ Storage::url($sk->file_sk) }}" target="_blank" class="text-primary hover:underline font-bold text-[11px]">
                        Lihat / Unduh Berkas &rarr;
                    </a>
                </div>
            </div>
            @endif

            <!-- Tombol Navigasi Langkah 2 -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button type="button" @click="step = 1" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span>&larr; Kembali ke Informasi Surat</span>
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" @click="submitForm()" class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25 cursor-pointer">
                        <span>Simpan Perubahan Surat SK</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>

<script>
function suratSkEditWizard() {
    return {
        step: 1,
        modeNomor: 'manual',
        nomorUrut: '{{ sprintf("%02d", \App\Models\SuratSk::getNextSequence(date("Y", strtotime($sk->tanggal_sk)))) }}',
        nomorManual: '{{ old('nomor_manual', $sk->nomor_sk) }}',
        tanggalSk: '{{ old('tanggal_sk', $sk->tanggal_sk ? $sk->tanggal_sk->format('Y-m-d') : date('Y-m-d')) }}',
        tentang: {!! json_encode(old('tentang', $sk->tentang)) !!},

        isBackdate: false,
        isDuplicate: false,
        duplicateMessage: '',
        duplicateData: null,
        checkingDupe: false,

        errorTanggal: false,
        errorNomorManual: false,
        errorTentang: false,

        init() {
            this.evaluateBackdate();
        },

        async evaluateBackdate() {
            if (!this.tanggalSk) return;
            try {
                const res = await fetch(`{{ route('surat.api.checkBackdateKategori') }}?kategori=Surat SK&tgl_surat=${this.tanggalSk}`);
                const data = await res.json();
                this.isBackdate = !!data.is_backdate;
                if (this.isBackdate) {
                    this.modeNomor = 'manual';
                }
            } catch (e) {
                console.error(e);
            }
        },

        async checkDuplicateNomor() {
            const num = (this.nomorManual || '').trim();
            if (!num || num === '{{ $sk->nomor_sk }}') {
                this.isDuplicate = false;
                this.duplicateMessage = '';
                this.duplicateData = null;
                return;
            }
            this.checkingDupe = true;
            try {
                const res = await fetch(`{{ route('surat.api.checkNomorDuplikat') }}?nomor=${encodeURIComponent(num)}&exclude_table=surat_sks&exclude_id={{ $sk->id }}`);
                const data = await res.json();
                this.isDuplicate = !!data.is_duplicate;
                this.duplicateMessage = data.message || '';
                this.duplicateData = data.existing || null;
            } catch (e) {
                console.error(e);
            } finally {
                this.checkingDupe = false;
            }
        },

        formatBulanRomawi() {
            if (!this.tanggalSk) return 'MM';
            const parts = this.tanggalSk.split('-');
            if (parts.length < 2) return 'MM';
            const month = parseInt(parts[1], 10);
            const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            return romawi[month - 1] || 'MM';
        },

        formatTahun() {
            if (!this.tanggalSk) return 'YYYY';
            return this.tanggalSk.split('-')[0] || 'YYYY';
        },



        goToStep2() {
            let valid = true;
            if (!this.tanggalSk) {
                this.errorTanggal = true;
                valid = false;
            }
            if (this.modeNomor === 'manual') {
                if (!this.nomorManual || !this.nomorManual.trim()) {
                    this.errorNomorManual = true;
                    valid = false;
                } else if (this.isDuplicate) {
                    valid = false;
                }
            }
            if (!valid) return;
            this.step = 2;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        submitForm() {
            if (!this.tentang || !this.tentang.trim()) {
                this.errorTentang = true;
                return;
            }
            if (this.modeNomor === 'manual' && this.isDuplicate) {
                this.step = 1;
                return;
            }
            document.getElementById('form-sk-edit').submit();
        }
    }
}
</script>
@endsection
