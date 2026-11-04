@extends('layouts.app')

@section('title', 'Buat Nomor SPPD Baru - Langkah 3 (Nomor SPPD)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="createSppdStep3()">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.keluar') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <a href="{{ route('surat.sppd.create') }}" class="hover:text-primary transition-colors font-semibold">Buat SPPD</a>
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
            <a href="{{ route('surat.sppd.create.step2') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Langkah 2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Nomor SPPD Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 3 dari 3 &bull; Konfirmasi & Penerbitan Nomor SPPD per Pegawai</p>
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
        <!-- Step 1 (Completed) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm bg-pink-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Informasi Nomor Surat</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-pink-500 mx-2"></div>
        
        <!-- Step 2 (Completed) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm bg-pink-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Personel & Uraian</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-pink-500 mx-2"></div>

        <!-- Step 3 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-pink-100" style="background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%);">
                3
            </div>
            <span class="ml-2 text-xs font-bold text-pink-600">Nomor SPPD</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
        <form action="{{ route('surat.sppd.store') }}" method="POST" class="p-6 md:p-8" id="form-sppd-step3" @submit="submitTerbitkan($event)">
            @csrf

            <!-- Ringkasan Singkat Dokumen -->
            <div class="p-4 bg-white/70 border border-blue-200/60 rounded-2xl mb-6 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium">SPT Induk Terhubung:</span>
                        <p class="font-mono font-bold text-navy mt-0.5">{{ $parentSptNomor ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Tanggal SPPD:</span>
                        <p class="font-semibold text-navy mt-0.5">{{ \Carbon\Carbon::parse(session('s_sppd_tgl_surat', date('Y-m-d')))->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Tujuan Perjalanan Dinas:</span>
                        <p class="font-semibold text-navy mt-0.5">{{ session('s_sppd_tujuan', '-') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Uraian / Maksud:</span>
                        <p class="font-semibold text-slate-700 mt-0.5 line-clamp-2">{{ session('s_sppd_uraian', '-') }}</p>
                    </div>
                </div>
            </div>

            <!-- Bagian: Penomoran SPPD per Pegawai -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Penomoran SPPD Per Pegawai</h4>
                        <p class="text-[11px] text-slate-500">Masing-masing personil yang ditugaskan memperoleh lembar dan nomor SPPD tersendiri.</p>
                    </div>
                    <span class="badge-blue text-[10px] px-2.5 py-0.5 rounded-full font-bold">
                        {{ $selectedPegawais->count() }} Personil Ditugaskan
                    </span>
                </div>

                <!-- Notice Backdate Jika Terkunci Manual -->
                <div x-show="isBackdate" x-cloak class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl text-xs text-amber-900 font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Tanggal surat sebelum hari ini (backdate), nomor harus diisi manual per pegawai.</span>
                </div>

                <!-- Daftar Pegawai & Nomor SPPD -->
                <div class="space-y-3">
                    @foreach($selectedPegawais as $index => $pegawai)
                    <div class="p-4 bg-white border border-blue-200/60 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <input type="hidden" name="pegawai_id[]" value="{{ $pegawai->id }}">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h5 class="text-xs font-bold text-navy">{{ $pegawai->nama }}</h5>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5 font-medium">
                                    <span>NIP: {{ $pegawai->nip ?: '-' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $pegawai->jabatan }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Area Input / Preview Nomor SPPD -->
                        <div class="md:w-80">
                            <template x-if="modeNomor === 'otomatis' && !isBackdate">
                                <div>
                                    <span class="text-[10px] text-slate-400 font-semibold block mb-0.5">Nomor SPPD Otomatis:</span>
                                    <span class="font-mono font-bold text-xs text-pink-600 bg-pink-50 border border-pink-200 px-3 py-1.5 rounded-xl block">
                                        {{ $sppdPreviews[$pegawai->id] ?? '-' }}
                                    </span>
                                </div>
                            </template>

                            <template x-if="modeNomor === 'manual' || isBackdate">
                                <div>
                                    <span class="text-[10px] text-slate-500 font-semibold block mb-0.5">Nomor SPPD Manual <span class="text-rose-500">*</span></span>
                                    <input type="text" 
                                           name="nomor_sppd[{{ $pegawai->id }}]" 
                                           x-model="nomorSppdManual['{{ $pegawai->id }}']" 
                                           @input.debounce.400ms="checkDuplicatePegawaiNomor('{{ $pegawai->id }}')" 
                                           required 
                                           placeholder="Ketik nomor SPPD pegawai..." 
                                           class="form-input text-xs font-mono font-semibold !py-1.5"
                                           :class="{ '!border-rose-400 !bg-rose-50/20': dupeErrors['{{ $pegawai->id }}'] }">
                                    <p x-show="dupeErrors['{{ $pegawai->id }}']" x-text="dupeErrors['{{ $pegawai->id }}']" x-cloak class="text-[11px] text-rose-500 font-bold mt-1"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Bagian: Link Google Drive (Opsional) -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Berkas Lampiran Fisik (Opsional)</h4>
                
                <div>
                    <label class="form-label text-xs">Link Google Drive</label>
                    <input type="url" 
                           name="link_google_drive" 
                           placeholder="https://drive.google.com/file/d/.../view?usp=sharing" 
                           class="form-input text-xs font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Tautan ke scan atau foto surat fisik yang disimpan di Google Drive (opsional).</span>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.sppd.create.step2') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold inline-block text-center">
                        &larr; Kembali
                    </a>
                    <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-4 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Batal & Exit
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="simpanDraft($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Draft'"></span>
                    </button>
                    <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-primary !bg-pink-600 hover:!bg-pink-700 !shadow-pink-500/25 px-6 py-2.5 text-xs font-bold flex items-center gap-2 shadow-lg">
                        <span x-text="submitting ? 'Menerbitkan...' : 'Terbitkan & Simpan SPPD'"></span>
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function createSppdStep3() {
    return {
        modeNomor: '{{ session('s_sppd_mode_nomor', 'otomatis') }}',
        isBackdate: {{ ($eval['is_backdate'] ?? false) ? 'true' : 'false' }},
        nomorSppdManual: {},
        dupeErrors: {},
        submitting: false,

        init() {
            @foreach($selectedPegawais as $pegawai)
                this.nomorSppdManual['{{ $pegawai->id }}'] = '{{ $loop->first ? session('s_sppd_nomor_surat', '') : '' }}';
            @endforeach
        },

        checkDuplicatePegawaiNomor(pegawaiId) {
            const clean = (this.nomorSppdManual[pegawaiId] || '').trim();
            if (!clean) {
                delete this.dupeErrors[pegawaiId];
                return;
            }

            fetch(`/surat/api/check-nomor-duplikat?nomor=${encodeURIComponent(clean)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.is_duplicate) {
                        this.dupeErrors[pegawaiId] = 'Nomor sudah terdaftar (' + (data.existing?.kategori || 'Surat lain') + ')';
                    } else {
                        delete this.dupeErrors[pegawaiId];
                    }
                })
                .catch(() => {});
        },

        submitTerbitkan(e) {
            if (this.submitting) {
                if (e) e.preventDefault();
                return false;
            }

            if (this.modeNomor === 'manual' || this.isBackdate) {
                for (let pId in this.dupeErrors) {
                    if (this.dupeErrors[pId]) {
                        alert('Terdapat nomor manual yang sudah digunakan atau duplikat. Silakan periksa kembali.');
                        if (e) e.preventDefault();
                        return false;
                    }
                }
            }

            this.submitting = true;
        },

        simpanDraft(e) {
            if (e) e.preventDefault();
            this.submitting = true;
            const form = document.getElementById('form-sppd-step3');
            form.action = '{{ route('surat.sppd.draft') }}';
            form.submit();
        }
    };
}
</script>
@endsection
