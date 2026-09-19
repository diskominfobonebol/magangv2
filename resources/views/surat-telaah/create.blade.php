@extends('layouts.app')

@section('title', 'Buat Surat Telaah')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="suratTelaahCreate()">

    <!-- Header Form -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('surat.telaah') }}" class="text-slate-400 hover:text-navy text-xs font-bold transition-colors">&larr; Kembali ke Arsip Telaah</a>
            </div>
            <h2 class="text-2xl font-extrabold text-navy mt-1">Buat Surat Telaah Baru</h2>
            <p class="text-slate-500 text-xs mt-0.5">Pilih rujukan SPT Induk untuk mengisi data telaahan secara otomatis.</p>
        </div>
    </div>

    <!-- Alert Error Validasi -->
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

    <!-- Form Body -->
    <form id="form-telaah" action="{{ route('surat.telaah.store') }}" method="POST" @submit.prevent="submitTelaah($event)" class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        @csrf

        <input type="hidden" name="spt_id" :value="selectedSpt ? selectedSpt.id : ''">
        <input type="hidden" name="mode_nomor" :value="modeNomor">

        <!-- SEKSI 1: PILIH SPT INDUK -->
        <div class="border border-blue-200/80 rounded-2xl p-4 bg-blue-50/40 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">1</span>
                    <div>
                        <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Rujukan Surat Perintah Tugas (SPT)</h4>
                        <p class="text-[11px] text-slate-500">Pilih SPT untuk mengisi tujuan, uraian, dan personil secara otomatis.</p>
                    </div>
                </div>
                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Direkomendasikan</span>
            </div>

            <!-- Searchable Combobox SPT -->
            <div class="relative" @click.outside="showSptDropdown = false">
                <div class="relative">
                    <input type="text" 
                           x-model="sptSearch" 
                           @focus="showSptDropdown = true" 
                           @click="showSptDropdown = true" 
                           @input="showSptDropdown = true" 
                           placeholder="Ketik nomor SPT atau uraian tugas untuk mencari rujukan..." 
                           class="form-input !bg-white !pr-16 text-xs font-mono font-semibold">
                    
                    <button type="button" 
                            x-show="sptSearch" 
                            @click="sptSearch = ''; showSptDropdown = true" 
                            class="absolute inset-y-0 right-8 pr-1 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer"
                            title="Hapus pencarian">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
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
                                        <span class="font-mono font-bold text-primary group-hover:underline text-xs" x-text="spt.nomor_surat"></span>
                                        <span class="text-[10px] text-slate-400" x-text="'• ' + spt.tgl_surat"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 truncate max-w-lg mt-0.5" x-text="spt.uraian || spt.perihal || 'Surat Perintah Tugas'"></p>
                                    <span class="text-[10px] text-slate-400" x-text="'Tujuan: ' + (spt.tujuan || '-')"></span>
                                </div>
                                <span class="text-[10px] font-bold text-primary bg-blue-100 px-2.5 py-1 rounded-lg shrink-0 ml-2">Pilih &crarr;</span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div x-show="showSptDropdown && sptSearch && filteredSptList().length === 0" 
                     style="display:none;" 
                     class="absolute z-30 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                    <p class="text-xs text-slate-500 italic">SPT dengan kata kunci "<span class="font-bold text-navy" x-text="sptSearch"></span>" tidak ditemukan.</p>
                </div>
            </div>

            <!-- SPT Terpilih Card Preview -->
            <template x-if="selectedSpt">
                <div class="bg-gradient-to-r from-emerald-50 to-blue-50 border border-emerald-200 rounded-xl p-3 flex items-start justify-between text-xs shadow-sm">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-navy text-xs" x-text="selectedSpt.nomor_surat"></span>
                            <span class="badge-green text-[9px] px-1.5 py-0.2 rounded-full font-bold">Terhubung</span>
                            <span class="text-[10px] text-slate-500 font-medium" x-text="'Tanggal: ' + selectedSpt.tgl_surat"></span>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-1 font-medium" x-text="selectedSpt.uraian || selectedSpt.perihal || 'Surat Perintah Tugas'"></p>
                        
                        <!-- SPPD Preview jika ada -->
                        <div x-show="selectedSpt.pegawais && selectedSpt.pegawais.some(p => p.pivot && p.pivot.nomor_sppd)" class="mt-2 flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">SPPD Terkait:</span>
                            <template x-for="p in selectedSpt.pegawais.filter(p => p.pivot && p.pivot.nomor_sppd)" :key="'prev-sppd-' + p.id">
                                <span class="badge-pink font-mono text-[10px] px-2 py-0.5 rounded-md font-bold" x-text="p.pivot.nomor_sppd"></span>
                            </template>
                        </div>
                    </div>
                    <button type="button" @click="clearSpt()" class="text-xs text-rose-500 font-bold hover:underline cursor-pointer ml-3">
                        Ganti / Lepas
                    </button>
                </div>
            </template>
        </div>

        <!-- SEKSI 2: IDENTITAS SURAT TELAAH (NOMOR & TANGGAL) -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">2</span>
                <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Identitas Surat Telaah</h4>
            </div>

            <!-- Field Tanggal Telaah -->
            <div>
                <label class="form-label text-xs">Tanggal Telaah <span class="text-rose-500">*</span></label>
                <input type="date" 
                       name="tanggal_telaah" 
                       x-model="tanggalTelaah" 
                       @input="errorTanggal = false"
                       required 
                       class="form-input text-xs font-semibold"
                       :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
                <p x-show="errorTanggal" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tanggal Telaah wajib diisi.
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Bulan dan tahun penomoran surat otomatis menyesuaikan dengan tanggal telaah.</p>
            </div>

            <!-- Field Nomor Surat Telaah dengan Toggle Otomatis / Manual -->
            <div>
                <label class="form-label text-xs">Nomor Surat Telaah <span class="text-rose-500">*</span></label>
                
                <!-- Toggle Tab Pilihan -->
                <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                    <button type="button" @click="modeNomor = 'otomatis'; errorNomorManual = false" 
                            class="pb-2 flex items-center gap-1.5 transition-colors cursor-pointer"
                            :class="modeNomor === 'otomatis' ? 'text-primary border-b-2 border-primary' : 'text-slate-400 hover:text-slate-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Otomatis (Rekomendasi)
                    </button>
                    <button type="button" @click="modeNomor = 'manual'" 
                            class="pb-2 flex items-center gap-1.5 transition-colors cursor-pointer"
                            :class="modeNomor === 'manual' ? 'text-primary border-b-2 border-primary' : 'text-slate-400 hover:text-slate-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Isi Manual
                    </button>
                </div>

                <!-- Preview Mode Otomatis -->
                <div x-show="modeNomor === 'otomatis'" class="bg-white/80 border border-blue-200/60 rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Preview Format Nomor Surat Telaah:</p>
                    <div class="text-sm font-extrabold text-primary font-mono mb-1">
                        555/KEP/Telaah.staff/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                    </div>
                    <p class="text-xs text-slate-400 font-mono">
                        Format resmi: 555/KEP/Telaah.staff/{nomor_urut}/{bulan_romawi}/{tahun}
                    </p>
                </div>

                <!-- Input Mode Manual -->
                <div x-show="modeNomor === 'manual'" style="display: none;">
                    <input type="text" 
                           name="nomor_manual" 
                           x-model="nomorManual" 
                           @input="errorNomorManual = false"
                           :placeholder="'Contoh: 555/KEP/Telaah.staff/01/' + formatBulanRomawi() + '/' + formatTahun()" 
                           class="form-input text-xs font-mono font-semibold"
                           :class="{ '!border-rose-400 !bg-rose-50/20': errorNomorManual }">
                    <p x-show="errorNomorManual" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Nomor Surat Telaah manual wajib diisi jika memilih mode manual.
                    </p>
                    <p class="mt-2 text-xs text-pink-accent font-semibold">
                        Pastikan nomor manual tidak duplikat dengan nomor yang sudah terdaftar di database.
                    </p>
                </div>
            </div>

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

            <div>
                <label class="form-label text-xs">Keterangan Tambahan (Opsional, Maks 500 Karakter)</label>
                <textarea name="keterangan" 
                          x-model="keterangan" 
                          rows="2" 
                          maxlength="500" 
                          placeholder="Catatan tambahan bila diperlukan..." 
                          class="form-input text-xs !h-auto"></textarea>
            </div>
        </div>

        <!-- Alert Validasi Personil Client-side -->
        <div x-show="errorPegawai" x-cloak class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span x-text="errorPegawaiMessage || 'Pilih minimal 1 personil yang ditugaskan.'"></span>
            </div>
            <button type="button" @click="errorPegawai = false" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- SEKSI 3: PERSONEL YANG DITUGASKAN -->
        <x-personil-selector badgeNumber="3" label="Personil yang Ditugaskan" subtitle="Personil otomatis terisi dari SPT, namun dapat ditambahkan atau disesuaikan." />

        <!-- Footer Tombol Aksi -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('surat.telaah') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                Batal
            </a>
            <button type="button" @click="submitTelaah($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25">
                <span x-text="submitting ? 'Memproses...' : 'Terbitkan & Simpan Surat Telaah'"></span>
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </button>
        </div>
    </form>

</div>

<script>
function suratTelaahCreate() {
    return {
        spts: @json($spts),
        allPegawais: @json($pegawais),
        selectedSpt: null,
        sptSearch: '',
        showSptDropdown: false,
        modeNomor: '{{ old('mode_nomor', 'otomatis') }}',
        nomorUrut: '{{ $nomorUrut ?? '01' }}',
        nomorManual: '{{ old('nomor_manual', '') }}',
        tanggalTelaah: '{{ old('tanggal_telaah', date('Y-m-d')) }}',
        uraian: '{{ addslashes(old('uraian', '')) }}',
        tujuan: '{{ addslashes(old('tujuan', '')) }}',
        keterangan: '{{ addslashes(old('keterangan', '')) }}',
        selectedPegawaiIds: @json(old('pegawai_id', [])),
        autoFilledPegawaiIds: [],
        manualPegawaiIds: @json(old('pegawai_id', [])),
        searchPegawai: '',
        showPegawaiDropdown: false,
        showP3kModal: false,
        p3kLoading: false,
        p3kError: '',
        p3kForm: {
            nama: '',
            nip: '',
            jabatan: ''
        },

        errorTanggal: false,
        errorNomorManual: false,
        errorTujuan: false,
        errorUraian: false,
        errorPegawai: false,
        errorPegawaiMessage: '',
        submitting: false,

        init() {
            const oldSptId = '{{ old('spt_id', '') }}';
            if (oldSptId) {
                const found = this.spts.find(s => String(s.id) === String(oldSptId));
                if (found) {
                    this.selectedSpt = found;
                    this.sptSearch = found.nomor_surat;
                    this.autoFilledPegawaiIds = (found.pegawais || []).map(p => Number(p.id));
                }
            }
        },

        submitTelaah(e) {
            // Reset state error
            this.errorTanggal = false;
            this.errorNomorManual = false;
            this.errorTujuan = false;
            this.errorUraian = false;
            this.errorPegawai = false;
            this.errorPegawaiMessage = '';

            let hasError = false;

            if (!this.tanggalTelaah || !this.tanggalTelaah.trim()) {
                this.errorTanggal = true;
                hasError = true;
            }

            if (this.modeNomor === 'manual' && (!this.nomorManual || !this.nomorManual.trim())) {
                this.errorNomorManual = true;
                hasError = true;
            }

            if (!this.tujuan || !this.tujuan.trim()) {
                this.errorTujuan = true;
                hasError = true;
            }

            if (!this.uraian || !this.uraian.trim()) {
                this.errorUraian = true;
                hasError = true;
            }

            if (!this.selectedPegawaiIds || this.selectedPegawaiIds.length === 0) {
                this.errorPegawai = true;
                this.errorPegawaiMessage = 'Pilih minimal 1 personil yang ditugaskan.';
                hasError = true;
            }

            if (hasError) {
                const firstError = document.querySelector('.border-rose-400, [x-show="errorPegawai"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            this.submitting = true;
            const form = document.getElementById('form-telaah');
            if (form) {
                form.submit();
            }
        },

        formatBulanRomawi() {
            if (!this.tanggalTelaah) return 'MM';
            const parts = this.tanggalTelaah.split('-');
            if (parts.length < 2) return 'MM';
            const month = parseInt(parts[1], 10);
            const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            return romawi[month - 1] || 'MM';
        },

        formatTahun() {
            if (!this.tanggalTelaah) return 'YYYY';
            return this.tanggalTelaah.split('-')[0] || 'YYYY';
        },

        filteredSptList() {
            if (!this.sptSearch || !this.sptSearch.trim()) {
                return this.spts;
            }
            const q = this.sptSearch.toLowerCase().trim();
            return this.spts.filter(s => {
                return (s.nomor_surat && s.nomor_surat.toLowerCase().includes(q)) ||
                       (s.uraian && s.uraian.toLowerCase().includes(q)) ||
                       (s.perihal && s.perihal.toLowerCase().includes(q)) ||
                       (s.tujuan && s.tujuan.toLowerCase().includes(q));
            });
        },

        selectSpt(spt) {
            this.selectedSpt = spt;
            this.sptSearch = spt.nomor_surat;
            this.showSptDropdown = false;

            // Auto-fill field bila belum diisi atau user ingin mengikuti data induk
            if (!this.uraian || this.uraian.trim() === '') {
                this.uraian = spt.uraian || spt.perihal || '';
            }
            if (!this.tujuan || this.tujuan.trim() === '') {
                this.tujuan = spt.tujuan || '';
            }

            // 1. Ambil daftar personil baru dari SPT yang dipilih
            const newSptPegawaiIds = (spt.pegawais || []).map(p => Number(p.id));

            // 2. Pertahankan personil manual (yang bukan bagian dari auto-fill SPT sebelumnya)
            const currentManualIds = this.selectedPegawaiIds
                .map(Number)
                .filter(id => !this.autoFilledPegawaiIds.includes(id));

            // 3. Update state auto-fill dan manual
            this.autoFilledPegawaiIds = [...newSptPegawaiIds];
            this.manualPegawaiIds = [...currentManualIds];

            // 4. Gabungkan personil SPT baru + personil manual dengan deduplikasi (Dedupe by ID)
            const combined = [...this.autoFilledPegawaiIds, ...this.manualPegawaiIds];
            this.selectedPegawaiIds = [...new Set(combined.map(Number))];
            if (this.selectedPegawaiIds.length > 0) {
                this.errorPegawai = false;
                this.errorPegawaiMessage = '';
            }
        },

        clearSpt() {
            this.selectedSpt = null;
            this.sptSearch = '';
            // Pertahankan seluruh personil terpilih saat ini sebagai personil manual agar tidak hilang
            this.manualPegawaiIds = [...this.selectedPegawaiIds.map(Number)];
            this.autoFilledPegawaiIds = [];
        },

        getPegawaiRank(p) {
            if (!p) return 999;
            const jab = String(p.jabatan || '').toLowerCase();
            if (jab.includes('kepala dinas')) return 1;
            if (jab.includes('sekretaris')) return 2;
            if (jab.includes('kepala bidang informatika') || jab.includes('kabid informatika')) return 3;
            if (jab.includes('kepala bidang komunikasi') || jab.includes('kabid komunikasi')) return 4;
            if (jab.includes('kepala bidang') || jab.includes('kabid')) return 5;
            if (jab.includes('kasubag kepegawaian') || jab.includes('kasubbag kepegawaian')) return 6;
            if (jab.includes('kasubag keuangan') || jab.includes('kasubbag keuangan')) return 7;
            if (jab.includes('kasubag') || jab.includes('kasubbag') || jab.includes('kepala sub bagian')) return 8;
            if (jab.includes('subkor') || jab.includes('sub koordinator')) return 9;
            return 10;
        },

        filteredPegawais() {
            const rawSearch = (this.searchPegawai || '').toLowerCase().trim();
            let results = [];
            if (!rawSearch) {
                results = this.allPegawais.filter(p => {
                    if (!p || !p.id) return false;
                    if ([1, 2, 3].includes(Number(p.id))) return false;
                    return !this.selectedPegawaiIds.some(id => Number(id) === Number(p.id));
                });
            } else {
                const searchWords = rawSearch.split(/\s+/).filter(Boolean);

                const aliasMap = {
                    'kepala dinas': 'kadis kadin kepala dinas',
                    'sekretaris dinas': 'sekdis sek sekretaris dinas sekretaris',
                    'sekretaris': 'sekdis sek sekretaris',
                    'kepala bidang': 'kabid kepala bidang',
                    'kasubag': 'kasubag kasubbag subbag kepala sub bagian kepala subbag',
                    'kasubbag': 'kasubag kasubbag subbag kepala sub bagian kepala subbag',
                    'subkor': 'sub koordinator subkor',
                    'pranata komputer': 'prakom pranata komputer',
                };

                results = this.allPegawais.filter(p => {
                    if (!p || !p.id) return false;
                    if ([1, 2, 3].includes(Number(p.id))) return false;
                    const notSelected = !this.selectedPegawaiIds.some(id => Number(id) === Number(p.id));
                    if (!notSelected) return false;

                    const nama = String(p.nama || '').toLowerCase();
                    const nip = String(p.nip || '').toLowerCase();
                    const jabatan = String(p.jabatan || '').toLowerCase();
                    const kat = String(p.kategori_pegawai || 'ASN').toLowerCase();

                    let jabatanExpanded = jabatan;
                    for (const [key, aliases] of Object.entries(aliasMap)) {
                        if (jabatan.includes(key)) {
                            jabatanExpanded += ' ' + aliases;
                        }
                    }

                    const fullText = `${nama} ${nip} ${jabatan} ${jabatanExpanded} ${kat}`;

                    return searchWords.every(word => fullText.includes(word));
                });
            }

            return results.sort((a, b) => {
                const rankA = this.getPegawaiRank(a);
                const rankB = this.getPegawaiRank(b);
                if (rankA !== rankB) return rankA - rankB;
                return (a.id || 0) - (b.id || 0);
            });
        },

        getSelectedPegawaiObjects() {
            return this.selectedPegawaiIds.map(id => this.allPegawais.find(p => Number(p.id) === Number(id))).filter(Boolean);
        },

        addPegawai(p) {
            if (!p || !p.id) return;
            const numId = Number(p.id);
            if (!this.selectedPegawaiIds.some(id => Number(id) === numId)) {
                this.selectedPegawaiIds.push(numId);
            }
            if (!this.manualPegawaiIds.includes(numId)) {
                this.manualPegawaiIds.push(numId);
            }
            this.errorPegawai = false;
            this.errorPegawaiMessage = '';
            this.searchPegawai = '';
            this.showPegawaiDropdown = false;
        },

        removePegawai(id) {
            const numId = Number(id);
            this.selectedPegawaiIds = this.selectedPegawaiIds.filter(pid => Number(pid) !== numId);
            this.autoFilledPegawaiIds = this.autoFilledPegawaiIds.filter(pid => Number(pid) !== numId);
            this.manualPegawaiIds = this.manualPegawaiIds.filter(pid => Number(pid) !== numId);
        },

        openModalP3k(initialName = '') {
            this.p3kError = '';
            this.p3kForm = {
                nama: typeof initialName === 'string' ? initialName : '',
                nip: '',
                jabatan: ''
            };
            this.showP3kModal = true;
            this.showPegawaiDropdown = false;
        },

        closeModalP3k() {
            this.showP3kModal = false;
            this.p3kError = '';
        },

        async submitP3k() {
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

                const data = await response.json();

                if (!response.ok || !data.success) {
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        this.p3kError = data.errors[firstKey][0];
                    } else {
                        this.p3kError = data.message || 'Gagal menyimpan data pegawai P3K.';
                    }
                    this.p3kLoading = false;
                    return;
                }

                const newPegawai = data.pegawai;
                
                this.allPegawais.push(newPegawai);
                this.addPegawai(newPegawai);
                this.closeModalP3k();
            } catch (err) {
                this.p3kError = 'Terjadi kesalahan: ' + err.message;
            } finally {
                this.p3kLoading = false;
            }
        }
    }
}
</script>
@endsection
