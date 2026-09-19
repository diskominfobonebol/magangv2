@extends('layouts.app')

@section('title', 'Buat Surat Keputusan (SK)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="suratSkCreate()">

    <!-- Header Form -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('surat.sk') }}" class="text-slate-400 hover:text-navy text-xs font-bold transition-colors">&larr; Kembali ke Arsip SK</a>
            </div>
            <h2 class="text-2xl font-extrabold text-navy mt-1">Buat Surat Keputusan (SK) Baru</h2>
            <p class="text-slate-500 text-xs mt-0.5">Catat penerbitan Surat Keputusan baru dan lampirkan berkas dokumen fisik (PDF/Dokumen).</p>
        </div>
    </div>

    <!-- Alert Error Validasi -->
    @if ($errors->any())
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
    <form action="{{ route('surat.sk.store') }}" method="POST" enctype="multipart/form-data" class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        @csrf

        <input type="hidden" name="mode_nomor" :value="modeNomor">

        <!-- SEKSI 1: IDENTITAS SURAT SK -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">1</span>
                <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Identitas Surat Keputusan</h4>
            </div>

            <!-- Field Tanggal SK Ditetapkan -->
            <div>
                <label class="form-label text-xs">Tanggal SK Ditetapkan <span class="text-rose-500">*</span></label>
                <input type="date" 
                       name="tanggal_sk" 
                       x-model="tanggalSk" 
                       required 
                       class="form-input text-xs font-semibold">
                <p class="text-[11px] text-slate-400 mt-1">Bulan dan tahun penomoran surat otomatis menyesuaikan dengan tanggal SK.</p>
            </div>

            <!-- Field Nomor Surat SK dengan Toggle Otomatis / Manual -->
            <div>
                <label class="form-label text-xs">Nomor Surat SK <span class="text-rose-500">*</span></label>
                
                <!-- Toggle Tab Pilihan -->
                <div class="flex items-center gap-6 border-b border-blue-200/50 mb-3 text-xs font-bold">
                    <button type="button" @click="modeNomor = 'otomatis'" 
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
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Preview Format Nomor Surat SK:</p>
                    <div class="text-sm font-extrabold text-primary font-mono mb-1">
                        555/KEP/Kominfo.BB/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                    </div>
                    <p class="text-xs text-slate-400 font-mono">
                        Format resmi: 555/KEP/Kominfo.BB/{nomor_urut}/{bulan_romawi}/{tahun}
                    </p>
                </div>

                <!-- Input Mode Manual -->
                <div x-show="modeNomor === 'manual'" style="display: none;">
                    <input type="text" 
                           name="nomor_manual" 
                           x-model="nomorManual" 
                           :placeholder="'Contoh: 555/KEP/Kominfo.BB/01/' + formatBulanRomawi() + '/' + formatTahun()" 
                           class="form-input text-xs font-mono font-semibold">
                    <p class="mt-2 text-xs text-pink-accent font-semibold">
                        Pastikan nomor manual tidak duplikat dengan nomor yang sudah terdaftar di database.
                    </p>
                </div>
            </div>

            <div>
                <label class="form-label text-xs">Hal / Tentang Surat Keputusan <span class="text-rose-500">*</span></label>
                <textarea name="tentang" 
                          rows="3" 
                          required 
                          placeholder="Tuliskan pokok penetapan atau judul perihal Surat Keputusan..." 
                          class="form-input text-xs font-medium !h-auto">{{ old('tentang') }}</textarea>
            </div>

            <div>
                <label class="form-label text-xs">Keterangan Tambahan (Opsional, Maks 500 Karakter)</label>
                <textarea name="keterangan" 
                          rows="2" 
                          maxlength="500" 
                          placeholder="Catatan tambahan, rujukan dasar hukum, atau informasi lain..." 
                          class="form-input text-xs !h-auto">{{ old('keterangan') }}</textarea>
            </div>
        </div>

        <!-- SEKSI 2: UPLOAD DOKUMEN FISIK (PDF) -->
        <div class="space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">2</span>
                <div>
                    <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Lampiran Berkas Fisik (Opsional)</h4>
                    <p class="text-[11px] text-slate-500">Upload scan dokumen fisik SK untuk kemudahan arsip & unduhan.</p>
                </div>
            </div>

            <div class="border-2 border-dashed border-blue-200 rounded-2xl p-6 bg-blue-50/20 text-center hover:bg-blue-50/40 transition-colors">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-primary flex items-center justify-center text-xl mb-2">
                        📎
                    </div>
                    <label class="cursor-pointer">
                        <span class="btn-pill-secondary px-4 py-2 text-xs font-bold inline-block">Pilih Berkas Lampiran</span>
                        <input type="file" name="file_sk" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="handleFile($event)">
                    </label>
                    <p class="text-xs text-slate-500 mt-2" x-text="fileName ? 'File Terpilih: ' + fileName : 'Format PDF, DOC, DOCX, JPG, PNG (Maks 10 MB)'"></p>
                </div>
            </div>
        </div>

        <!-- Footer Tombol Aksi -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('surat.sk') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                Batal
            </a>
            <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/25 cursor-pointer">
                <span>Terbitkan & Simpan Surat SK</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
        </div>
    </form>

</div>

<script>
function suratSkCreate() {
    return {
        modeNomor: '{{ old('mode_nomor', 'otomatis') }}',
        nomorUrut: '{{ $nomorUrut ?? '01' }}',
        nomorManual: '{{ old('nomor_manual', '') }}',
        tanggalSk: '{{ old('tanggal_sk', date('Y-m-d')) }}',
        fileName: '',

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

        handleFile(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.fileName = files[0].name;
            } else {
                this.fileName = '';
            }
        }
    }
}
</script>
@endsection
