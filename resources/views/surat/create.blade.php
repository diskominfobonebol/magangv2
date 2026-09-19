@extends('layouts.app')

@section('title', 'Buat Surat Perintah Tugas (SPT) Baru')

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
                    <span class="text-slate-400 font-medium">Buat SPT</span>
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
                <h2 class="text-2xl font-bold text-navy">Buat Nomor SPT Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 1 dari 3 &bull; Informasi Nomor Surat Perintah Tugas</p>
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
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" 
         x-data="createSuratForm()">
        
        <form action="{{ route('surat.create.step2') }}" method="POST" class="p-6 md:p-8" id="form-utama" @submit.prevent="submitLanjut($event)">
            @csrf
            
            <div class="space-y-6">
                <!-- Field 1: TANGGAL SURAT -->
                <div>
                    <label class="form-label">Tanggal Nomor Surat <span class="text-rose-500">*</span></label>
                    <input type="date" name="tgl_surat" x-model="tanggal" @change="evaluateBackdate()" @input="errorTanggal = false; evaluateBackdate()" class="form-input" :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
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

                <!-- Field 2: JENIS SURAT (SPT Tetap / Non-Dropdown) -->
                <div>
                    <label class="form-label">Jenis Surat</label>
                    <div class="p-4 rounded-2xl border border-primary/40 bg-blue-50/30 flex items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-primary flex items-center justify-center font-bold text-lg">
                                📄
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-navy">Surat Perintah Tugas (SPT)</span>
                                    <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Kode: 555</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    Surat tugas kedinasan. Opsi pembuatan SPPD terkait dapat disertakan pada Langkah 3.
                                </p>
                            </div>
                        </div>
                        <input type="hidden" name="jenis_surat_id" value="2">
                    </div>
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
                    <div class="mt-1 space-y-2.5">
                        <!-- Notice Banner Backdate -->
                        <div x-show="modeNomor === 'otomatis' && isBackdate && backdateNotice" x-cloak class="p-3.5 bg-amber-50/90 border border-amber-300/80 rounded-2xl text-xs text-amber-900 font-semibold flex items-start gap-2.5 shadow-sm">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div class="flex-1">
                                <span x-text="backdateNotice"></span>
                            </div>
                        </div>

                        <!-- Otomatis Preview -->
                        <div x-show="modeNomor === 'otomatis'" class="bg-white/80 border border-blue-200/50 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Preview nomor surat:</p>
                                <span x-show="isBackdate" x-cloak class="badge-amber text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-200">Disisipkan (Backdate)</span>
                            </div>
                            
                            <div>
                                <div class="text-sm font-extrabold text-primary mb-1 font-mono">
                                    555/KOMINFO-BB/SPT-DD/<span x-text="nomorUrutSpt"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                                </div>
                                <p class="text-xs text-slate-400 font-mono">
                                    Format: 555/KOMINFO-BB/SPT-DD/<span x-text="nomorUrutSpt"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Manual Input -->
                        <div x-show="modeNomor === 'manual'" style="display: none;">
                            <input type="text" x-model="nomorManual" :placeholder="'Contoh: 555/KOMINFO-BB/SPT-DD/001a/IX/' + formatTahun()" class="form-input font-mono">
                            <p class="mt-2 text-xs text-pink-accent font-semibold">
                                Pastikan nomor manual sesuai format resmi (mendukung akhiran huruf jika disisipkan) dan tidak duplikat dengan arsip sebelumnya.
                            </p>
                        </div>

                        <input type="hidden" name="mode_nomor" :value="modeNomor">
                        <input type="hidden" name="nomor_surat_manual" :value="nomorManual">
                        <input type="hidden" name="nomor_surat" :value="modeNomor === 'manual' ? nomorManual : ('555/KOMINFO-BB/SPT-DD/' + nomorUrutSpt + '/' + formatBulanRomawi() + '/' + formatTahun())">
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
                    <button type="button" @click="simpanDraft($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Draft'"></span>
                    </button>
                    
                    <!-- Tombol Lanjut (Milik form utama) -->
                    <button type="button" @click="submitLanjut($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md flex items-center">
                        <span x-text="submitting ? 'Memproses...' : 'Lanjut &rarr;'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function createSuratForm() {
        return {
            tanggal: @json(old('tgl_surat', session('s_tgl_surat', ''))),
            jenisSuratId: '2',
            modeNomor: 'otomatis',
            nomorManual: @json(old('nomor_surat', '')),
            nomorUrutSpt: @json($eval['next_seq'] ?? $nomorUrutSpt ?? $nomorUrut ?? '001'),
            isBackdate: @json((isset($eval['is_backdate']) && $eval['is_backdate']) ? true : false),
            backdateNotice: @json($eval['notice'] ?? ''),
            predecessorNomor: @json($eval['predecessor_nomor'] ?? ''),
            seriesSummary: @json($seriesSummary ?? null),
            errorTanggal: false,
            submitting: false,

            getLetterSuffix(index) {
                let result = '';
                let n = index;
                while (n >= 0) {
                    result = String.fromCharCode(97 + (n % 26)) + result;
                    n = Math.floor(n / 26) - 1;
                }
                return result;
            },
            formatBulanRomawi() {
                if (!this.tanggal) return 'MM';
                const parts = this.tanggal.split('-');
                if (parts.length < 2) return 'MM';
                const month = parseInt(parts[1], 10);
                const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                return romawi[month - 1] || 'MM';
            },
            formatTahun() {
                if (!this.tanggal) return 'YYYY';
                return this.tanggal.split('-')[0] || 'YYYY';
            },
            evaluateBackdate() {
                if (!this.tanggal || this.tanggal.trim() === '') {
                    this.isBackdate = false;
                    this.backdateNotice = '';
                    this.nomorUrutSpt = '...';
                    return;
                }
                const targetDate = this.tanggal;
                const targetYear = parseInt(targetDate.split('-')[0], 10);
                const bln = this.formatBulanRomawi();
                const thn = this.formatTahun();

                // 1. Pengecekan instan di client side jika summary tersedia
                if (this.seriesSummary && this.seriesSummary.spt) {
                    const sptData = this.seriesSummary.spt;
                    const items = (sptData.items || []).filter(it => it.tgl_surat.startsWith(String(targetYear)) || it.nomor_surat.endsWith('/' + targetYear));
                    
                    let maxBaseSeq = 0;
                    let highestSurat = null;
                    if (items.length > 0) {
                        maxBaseSeq = Math.max(...items.map(it => it.base_seq));
                        const candidates = items.filter(it => it.base_seq === maxBaseSeq).sort((a, b) => b.tgl_surat.localeCompare(a.tgl_surat));
                        highestSurat = candidates[0];
                    }

                    const highestDate = highestSurat ? highestSurat.tgl_surat : null;
                    const isBack = (highestSurat !== null && highestDate !== null && targetDate < highestDate);

                    if (isBack) {
                        this.isBackdate = true;
                        const predecessors = items.filter(it => it.tgl_surat <= targetDate).sort((a, b) => {
                            if (a.tgl_surat !== b.tgl_surat) return b.tgl_surat.localeCompare(a.tgl_surat);
                            if (a.base_seq !== b.base_seq) return b.base_seq - a.base_seq;
                            return (b.letter || '').localeCompare(a.letter || '');
                        });

                        let pred = predecessors.length > 0 ? predecessors[0] : (items.slice().sort((a, b) => a.base_seq - b.base_seq)[0]);
                        const baseSeq = pred ? pred.base_seq : 1;
                        const paddedBase = String(baseSeq).padStart(3, '0');
                        const predNomor = pred ? pred.nomor_surat : ('555/KOMINFO-BB/SPT-DD/' + paddedBase + '/' + bln + '/' + thn);

                        const usedLetters = items.filter(it => it.base_seq === baseSeq).map(it => it.letter).filter(Boolean);
                        let letterIdx = 0;
                        while (usedLetters.includes(this.getLetterSuffix(letterIdx))) {
                            letterIdx++;
                        }
                        const letter = this.getLetterSuffix(letterIdx);
                        this.nomorUrutSpt = paddedBase + letter;
                        const previewNomor = `555/KOMINFO-BB/SPT-DD/${paddedBase}${letter}/${bln}/${thn}`;
                        this.backdateNotice = `Terdeteksi tanggal mundur (backdate) — nomor akan disisipkan sebagai anak dari nomor ${predNomor}, menjadi ${previewNomor}.`;
                    } else {
                        this.isBackdate = false;
                        this.backdateNotice = '';
                        this.nomorUrutSpt = String(maxBaseSeq + 1).padStart(3, '0');
                    }
                }

                // 2. Sinkronisasi dengan backend API untuk verifikasi real-time
                fetch(`/surat/api/check-backdate?series=SPT&tgl_surat=${targetDate}&jenis_penugasan=DD`)
                    .then(res => res.json())
                    .then(data => {
                        if (data) {
                            this.isBackdate = Boolean(data.is_backdate);
                            this.nomorUrutSpt = data.next_seq;
                            this.backdateNotice = data.notice || '';
                            this.predecessorNomor = data.predecessor_nomor || '';
                        }
                    })
                    .catch(() => {});
            },
            simpanDraft(e) {
                this.errorTanggal = !this.tanggal || this.tanggal.trim() === '';
                if (this.errorTanggal) {
                    if (e) e.preventDefault();
                    this.submitting = false;
                    return false;
                }
                if (this.submitting) {
                    if (e) e.preventDefault();
                    return false;
                }
                this.submitting = true;
                const form = document.getElementById('form-utama');
                form.action = '{{ route('surat.draft') }}';
                form.submit();
            },
            submitLanjut(e) {
                this.errorTanggal = !this.tanggal || this.tanggal.trim() === '';
                if (this.errorTanggal) {
                    if (e) e.preventDefault();
                    this.submitting = false;
                    return false;
                }
                if (this.submitting) {
                    if (e) e.preventDefault();
                    return false;
                }
                this.submitting = true;
                const form = document.getElementById('form-utama');
                form.action = '{{ route('surat.create.step2') }}';
                form.submit();
            },
            init() {
                if (this.tanggal) {
                    this.evaluateBackdate();
                }
            }
        };
    }
</script>
@endsection