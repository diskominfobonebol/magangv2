@extends('layouts.app')

@section('title', 'Buat Surat Baru - Langkah 3 (Nomor SPPD)')

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
                    <a href="{{ route('surat.create') }}" class="hover:text-primary transition-colors font-semibold">Buat SPT</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Langkah 3</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.create.step2') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Langkah 2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Nomor SPT Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 3 dari 3 &bull; Opsi Penerbitan SPPD Terkait</p>
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

    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold shadow-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Stepper -->
    <div class="py-2 flex items-center justify-center">
        <!-- Step 1 (Completed) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Informasi SPT</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-primary mx-2"></div>
        
        <!-- Step 2 (Completed) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Personel & Uraian</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-primary mx-2"></div>

        <!-- Step 3 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                3
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Nomor SPPD</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" 
         x-data="{ 
             pilihanSppd: 'ya', 
             submitting: false,

             simpanDraft(e) {
                 if (e) e.preventDefault();
                 if (this.submitting) return false;
                 this.submitting = true;
                 const form = document.getElementById('form-utama');
                 form.action = '{{ route('surat.draft') }}';
                 form.submit();
             },

             submitTerbitkan(e) {
                 if (this.submitting) {
                     if (e) e.preventDefault();
                     return false;
                 }
                 this.submitting = true;
             }
         }">
        <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8" id="form-utama" @submit="submitTerbitkan($event)" x-data="{ selectedFileName: '', selectedFileSize: '', isDragging: false }">
            @csrf
            
            <input type="hidden" name="tgl_surat" value="{{ session('s_tgl_surat', date('Y-m-d')) }}">
            <input type="hidden" name="tujuan" value="{{ session('s_tujuan', '') }}">
            <input type="hidden" name="jenis_surat_id" value="2">
            <input type="hidden" name="uraian" value="{{ session('s_uraian', '') }}">
            <input type="hidden" name="keterangan" value="{{ session('s_keterangan', '') }}">
            <input type="hidden" name="has_sppd" :value="pilihanSppd === 'ya' ? 1 : 0">
            
            @if(session('s_pegawai_id'))
                @foreach(session('s_pegawai_id') as $pid)
                    <input type="hidden" name="pegawai_id[]" value="{{ $pid }}">
                @endforeach
            @endif

            <div class="space-y-6">
                <!-- Section Pilihan Penerbitan SPPD -->
                <div class="border border-blue-200/60 rounded-3xl p-6 bg-white/60 space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-navy flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">✈️</span>
                            Penerbitan Surat Perintah Perjalanan Dinas (SPPD) Terkait
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Pilih apakah surat tugas ini langsung disertai penomoran SPPD untuk setiap personil:</p>
                    </div>

                    <!-- Dual Option Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Card 1: Dengan SPPD (Default) -->
                        <div @click="pilihanSppd = 'ya'" 
                             :class="pilihanSppd === 'ya' ? 'border-primary ring-2 ring-blue-500/40 bg-blue-50/50 shadow-md shadow-blue-500/10' : 'border-slate-200 bg-white hover:border-blue-300'"
                             class="rounded-2xl border p-4 cursor-pointer transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold"
                                         :class="pilihanSppd === 'ya' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-500'">
                                        ✓
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-navy">Buat SPPD Sekaligus</h4>
                                        <p class="text-[11px] text-slate-500">Nomor SPPD otomatis dibuat untuk setiap personel</p>
                                    </div>
                                </div>
                                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold" x-show="pilihanSppd === 'ya'">Dipilih</span>
                            </div>
                        </div>

                        <!-- Card 2: Tanpa SPPD -->
                        <div @click="pilihanSppd = 'tidak'" 
                             :class="pilihanSppd === 'tidak' ? 'border-primary ring-2 ring-blue-500/40 bg-blue-50/50 shadow-md shadow-blue-500/10' : 'border-slate-200 bg-white hover:border-blue-300'"
                             class="rounded-2xl border p-4 cursor-pointer transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold"
                                         :class="pilihanSppd === 'tidak' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-500'">
                                        ✕
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-navy">Hanya SPT (Tanpa SPPD)</h4>
                                        <p class="text-[11px] text-slate-500">SPPD dapat ditambahkan belakangan jika diperlukan</p>
                                    </div>
                                </div>
                                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold" x-show="pilihanSppd === 'tidak'">Dipilih</span>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Detail Opsi A: SPPD Aktif -->
                    <div x-show="pilihanSppd === 'ya'" x-transition class="bg-white/90 border border-blue-200/60 rounded-2xl p-4 space-y-3">
                        @if(isset($sppdEval) && $sppdEval['is_backdate'])
                        <div class="p-3 bg-amber-50/90 border border-amber-300/80 rounded-xl text-xs text-amber-900 font-semibold flex items-start gap-2 shadow-sm">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <span>{{ $sppdEval['notice'] }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-navy">Daftar Personel & Preview Nomor SPPD Otomatis:</span>
                            <span class="text-[11px] text-primary font-bold">{{ isset($selectedPegawais) ? $selectedPegawais->count() : 0 }} Personel</span>
                        </div>
                        
                        <ul class="space-y-2.5">
                            @if(isset($selectedPegawais) && $selectedPegawais->count() > 0)
                                @foreach($selectedPegawais as $pegawai)
                                <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-xl hover:bg-blue-50/40 transition-colors border border-blue-100/60 bg-slate-50/40">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-navy text-xs">{{ $pegawai->nama }}</span>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold {{ ($pegawai->kategori_pegawai ?? 'ASN') === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                                {{ $pegawai->kategori_pegawai ?? 'ASN' }}
                                            </span>
                                        </div>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">{{ ($pegawai->kategori_pegawai === 'P3K' ? 'No. Identitas: ' : 'NIP. ') . ($pegawai->nip ?: '-') }} &bull; {{ $pegawai->jabatan }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-400">No. SPPD:</span>
                                        <span class="badge-pink text-xs font-mono px-3 py-1 rounded-full font-bold">
                                            {{ $sppdPreviews[$pegawai->id] ?? '090/KOMINFO-BB/SPPD/DD/001a/' . ($bln ?? 'IX') . '/' . ($thn ?? date('Y')) }}
                                        </span>
                                        <input type="hidden" name="nomor_sppd[{{ $pegawai->id }}]" value="{{ $sppdPreviews[$pegawai->id] ?? '' }}">
                                    </div>
                                </li>
                                @endforeach
                            @else
                                <li class="text-xs text-rose-500 italic p-3 text-center bg-rose-50 rounded-xl">
                                    Peringatan: Belum ada personel yang dipilih dari Langkah 2. Silakan kembali ke Langkah 2 untuk memilih personel.
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Panel Detail Opsi B: Tanpa SPPD -->
                    <div x-show="pilihanSppd === 'tidak'" x-transition style="display: none;" class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center flex-shrink-0">
                            ℹ️
                        </div>
                        <p class="text-xs text-slate-600">
                            Surat Perintah Tugas (SPT) akan diterbitkan tanpa surat SPPD. Anda sewaktu-waktu dapat menambahkan SPPD susulan melalui halaman <strong>Detail Nomor Surat</strong>.
                        </p>
                    </div>
                </div>

                <!-- Section Upload Berkas Scan Surat (Google Drive Integration) -->
                <div class="border border-blue-200/60 rounded-3xl p-6 bg-white/60 space-y-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-navy flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">📁</span>
                                Unggah Berkas Scan Fisik (Opsional)
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">
                                File scan/foto dokumen surat yang sudah ditandatangani dan dicap basah (PDF / Gambar maks. 10MB).
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            Bisa Diunggah Nanti
                        </span>
                    </div>

                    <div class="border-2 border-dashed rounded-2xl p-5 text-center transition-all cursor-pointer relative bg-white/80"
                         :class="isDragging ? 'border-primary bg-blue-50/60 ring-2 ring-blue-400/30' : 'border-blue-200 hover:border-primary/60'"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; const f = $event.dataTransfer.files[0]; selectedFileName = f.name; selectedFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB'; }">
                        
                        <input type="file" 
                               name="file_surat" 
                               id="file_surat" 
                               x-ref="fileInput"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                               @change="if ($event.target.files.length > 0) { const f = $event.target.files[0]; selectedFileName = f.name; selectedFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB'; } else { selectedFileName = ''; selectedFileSize = ''; }">

                        <div x-show="!selectedFileName" class="space-y-2 pointer-events-none">
                            <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 text-primary flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-navy">Klik untuk memilih file atau seret file ke sini</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Mendukung format PDF, JPG, JPEG, atau PNG (Maksimal 10 MB)</p>
                            </div>
                        </div>

                        <div x-show="selectedFileName" x-cloak class="flex items-center justify-between p-3 bg-blue-50/80 border border-blue-200 rounded-xl pointer-events-auto">
                            <div class="flex items-center gap-3 text-left">
                                <span class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-xs">
                                    📄
                                </span>
                                <div>
                                    <h5 class="text-xs font-bold text-navy truncate max-w-xs md:max-w-md" x-text="selectedFileName"></h5>
                                    <p class="text-[10px] text-slate-500 font-semibold" x-text="selectedFileSize"></p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="$refs.fileInput.value = ''; selectedFileName = ''; selectedFileSize = '';" 
                                    class="text-xs font-bold text-rose-600 hover:text-rose-800 p-1.5 rounded-lg hover:bg-rose-50 transition-colors">
                                Hapus
                            </button>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 italic">
                        * File yang diunggah akan otomatis tersinkronisasi ke Google Drive instansi dan dapat diakses publik melalui tautan viewer yang aman.
                    </p>
                </div>
            </div>


            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.create.step2') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold inline-block text-center">
                        &larr; Kembali
                    </a>
                    <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Batal & Exit
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Tombol Simpan Draft -->
                    <button type="button" @click="simpanDraft($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Draft'"></span>
                    </button>
                    <!-- Tombol Terbitkan (Milik form utama) -->
                    <button type="submit" 
                            :disabled="submitting"
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                            class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="submitting ? 'Memproses...' : (pilihanSppd === 'ya' ? 'Terbitkan SPT & SPPD' : 'Terbitkan Surat SPT')"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection