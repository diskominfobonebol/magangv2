@extends('layouts.app')

@section('title', 'Buat Surat Keluar Lainnya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="suratLainnyaWizard()">

    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.keluar') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Buat Surat Keluar Lainnya</span>
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
                <h2 class="text-2xl font-bold text-navy">Buat Surat Keluar Lainnya</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 1 dari 1 • Formulir Surat Keluar Biasa / Lainnya</p>
            </div>
        </div>
        <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Stepper 1 Langkah (bergaya SPT) -->
    <div class="py-2 flex items-center justify-center">
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100 bg-primary">
                1
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Informasi & Uraian Surat</span>
        </div>
    </div>

    <!-- Alert Notifikasi Error -->
    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    @if ($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold shadow-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form Container -->
    <form action="{{ route('surat.lainnya.store') }}" method="POST" class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6" id="form-lainnya" @submit="submitForm($event)">
        @csrf

        <!-- Field 1: Tanggal Surat -->
        <div>
            <label class="form-label text-xs">Tanggal Surat <span class="text-rose-500">*</span></label>
            <input type="date" 
                   name="tgl_surat" 
                   x-model="tanggalSurat" 
                   @input="errorTanggal = false; evaluateBackdate()"
                   @change="evaluateBackdate()" 
                   class="form-input text-xs font-semibold"
                   :class="{ '!border-rose-400 !bg-rose-50/20': errorTanggal }">
            <p x-show="errorTanggal" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Tanggal Surat wajib diisi sebelum menyimpan.
            </p>
            @if($errors->has('tgl_surat'))
                <p class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $errors->first('tgl_surat') }}
                </p>
            @endif
            
            <!-- Notice Backdate -->
            <div x-show="isBackdate" x-cloak class="mt-2 p-3 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Tanggal surat sebelum hari ini (backdate), nomor harus diisi manual.</span>
            </div>
        </div>

        <!-- Field 2: Kartu Jenis Surat Terkunci "Lainnya" -->
        <div>
            <label class="form-label text-xs">Jenis Surat</label>
            <div class="p-4 rounded-2xl border border-blue-200 bg-blue-50/40 flex items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-primary flex items-center justify-center font-bold text-lg">
                        ✉️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-navy">Surat Keluar Lainnya</span>
                            <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-0.5 rounded-full font-bold">Kode Belum Diatur (Hanya Manual)</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Kategori surat keluar biasa / umum. Mode otomatis dinonaktifkan sampai kode kategori diisi pada database jenis surat.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Field 3: Nomor Surat (Manual Default, Otomatis Nonaktif) -->
        <div>
            <label class="form-label text-xs">Nomor Surat Keluar <span class="text-rose-500">*</span></label>
            
            <!-- Toggle Tab Pilihan (Manual Terpilih, Otomatis Nonaktif) -->
            <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                <button type="button" 
                        class="pb-2 flex items-center gap-1.5 text-primary border-b-2 border-primary cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Isi Manual (Satu-satunya Opsi)
                </button>
                <button type="button" 
                        disabled
                        class="pb-2 flex items-center gap-1.5 text-slate-300 cursor-not-allowed"
                        title="Kode kategori Lainnya belum diatur di database, mode otomatis dinonaktifkan.">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Otomatis
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-400">Kode Kosong</span>
                </button>
            </div>

            <div class="relative">
                <input type="text" 
                       name="nomor_surat" 
                       x-model="nomorSurat" 
                       @input.debounce.400ms="checkDuplicateNomor()" 
                       required 
                       placeholder="Ketik nomor surat resmi (contoh: 005/KOMINFO-BB/UND/01/XI/2026)" 
                       class="form-input text-xs font-mono font-bold"
                       :class="{ '!border-rose-400 !bg-rose-50/20': isDuplicate }">
                <div x-show="checkingDupe" class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>

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

            <p class="text-[11px] text-slate-400 mt-1">
                * Nomor surat dicek keunikannya secara real-time lintas seluruh arsip surat keluar.
            </p>
        </div>

        <!-- Field 4: Uraian / Perihal -->
        <div>
            <label class="form-label text-xs">Uraian / Perihal Surat <span class="text-rose-500">*</span></label>
            <textarea name="uraian" 
                      rows="3" 
                      required 
                      placeholder="Tuliskan pokok isi surat, perihal undangan, atau uraian maksud surat keluar..." 
                      class="form-input text-xs font-medium !h-auto">{{ old('uraian') }}</textarea>
        </div>

        <!-- Field 5: Keterangan (Opsional, Maks 150 Karakter) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="form-label text-xs mb-0">Keterangan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                <span class="text-[10px] text-slate-400">Maks. 150 karakter</span>
            </div>
            <input type="text" 
                   name="keterangan" 
                   maxlength="150" 
                   value="{{ old('keterangan') }}" 
                   placeholder="Catatan tambahan, ditujukan kepada, atau informasi ringkas..." 
                   class="form-input text-xs font-medium">
        </div>

        <!-- Field 6: Lampiran Berkas / Google Drive -->
        <div class="space-y-4 pt-2 border-t border-slate-100">
            <div>
                <label class="form-label text-xs">Link Dokumen Google Drive <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                <input type="url" 
                       name="link_google_drive" 
                       value="{{ old('link_google_drive') }}" 
                       placeholder="https://drive.google.com/file/d/..." 
                       class="form-input text-xs font-medium">
            </div>
        </div>

        <!-- Footer Tombol Aksi -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                Batal & Exit
            </a>
            <button type="submit" 
                    :disabled="submitting || isDuplicate" 
                    :class="(submitting || isDuplicate) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" 
                    class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25">
                <span x-text="submitting ? 'Menyimpan...' : 'Terbitkan & Simpan Surat'"></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
        </div>
    </form>
</div>

<script>
function suratLainnyaWizard() {
    return {
        tanggalSurat: @json(old('tgl_surat', '')),
        nomorSurat: @json(old('nomor_surat', '')),
        isBackdate: false,
        isDuplicate: false,
        duplicateMessage: '',
        duplicateData: null,
        checkingDupe: false,
        errorTanggal: false,
        submitting: false,

        init() {
            if (this.tanggalSurat) {
                this.evaluateBackdate();
            }
            if (this.nomorSurat) {
                this.checkDuplicateNomor();
            }
        },

        async evaluateBackdate() {
            if (!this.tanggalSurat || !this.tanggalSurat.trim()) {
                this.isBackdate = false;
                return;
            }
            try {
                const res = await fetch(`{{ route('surat.api.checkBackdateKategori') }}?kategori=Lainnya&tgl_surat=${encodeURIComponent(this.tanggalSurat)}`);
                const data = await res.json();
                this.isBackdate = !!data.is_backdate;
            } catch (e) {
                console.error(e);
            }
        },

        async checkDuplicateNomor() {
            const num = (this.nomorSurat || '').trim();
            if (!num || num.length < 3) {
                this.isDuplicate = false;
                this.duplicateMessage = '';
                this.duplicateData = null;
                return;
            }

            this.checkingDupe = true;
            try {
                const res = await fetch(`{{ route('surat.api.checkNomorDuplikat') }}?nomor=${encodeURIComponent(num)}`);
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

        submitForm(e) {
            this.errorTanggal = !this.tanggalSurat || !this.tanggalSurat.trim();
            if (this.errorTanggal) {
                if (e) e.preventDefault();
                this.submitting = false;
                return false;
            }
            if (!this.nomorSurat || !this.nomorSurat.trim()) {
                if (e) e.preventDefault();
                this.submitting = false;
                return false;
            }
            if (this.isDuplicate) {
                if (e) e.preventDefault();
                alert('Nomor surat sudah digunakan. Silakan gunakan nomor surat lain.');
                this.submitting = false;
                return false;
            }
            if (this.submitting) {
                if (e) e.preventDefault();
                return false;
            }
            this.submitting = true;
        }
    }
}
</script>
@endsection
