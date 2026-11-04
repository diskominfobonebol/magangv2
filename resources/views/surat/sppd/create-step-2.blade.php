@extends('layouts.app')

@section('title', 'Buat Nomor SPPD Baru - Langkah 2')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="createSppdStep2()">
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
                    <span class="text-slate-400 font-medium">Langkah 2</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.sppd.create') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Langkah 1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Nomor SPPD Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 2 dari 3 &bull; Personel & Uraian Perjalanan Dinas</p>
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

    @if (isset($errors) && $errors->any())
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
        <!-- Step 2 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-pink-100" style="background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%);">
                2
            </div>
            <span class="ml-2 text-xs font-bold text-pink-600">Personel & Uraian</span>
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
        <form action="{{ route('surat.sppd.create.step3') }}" method="POST" class="p-6 md:p-8" id="form-sppd-step2" @submit.prevent="submitLanjut($event)">
            @csrf

            <div class="space-y-6">
                <!-- Field 1: TUJUAN / INSTANSI -->
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
                        Tujuan perjalanan dinas wajib diisi.
                    </p>
                </div>

                <!-- Field 2: URAIAN / MAKSUD PERJALANAN DINAS -->
                <div>
                    <label class="form-label text-xs">Uraian / Maksud Perjalanan Dinas <span class="text-rose-500">*</span></label>
                    <textarea name="uraian" 
                              x-model="uraian" 
                              @input="errorUraian = false"
                              rows="3" 
                              required 
                              placeholder="Tuliskan maksud perjalanan dinas secara lengkap..." 
                              class="form-input text-xs font-medium !h-auto"
                              :class="{ '!border-rose-400 !bg-rose-50/20': errorUraian }"></textarea>
                    <p x-show="errorUraian" x-cloak class="text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Uraian maksud perjalanan dinas wajib diisi.
                    </p>
                </div>

                <!-- Alert Validasi Personil Client-side -->
                <div x-show="errorPegawai" x-cloak class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Pilih minimal 1 personil yang ditugaskan dalam perjalanan dinas.</span>
                    </div>
                    <button type="button" @click="errorPegawai = false" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Field 3: PERSONEL YANG DITUGASKAN -->
                <x-personil-selector label="Pegawai yang Ditugaskan" subtitle="Personil dapat disinkronkan otomatis dari SPT Induk atau dipilih sesuai kebutuhan penugasan." />

                <!-- Field 4: KETERANGAN TAMBAHAN (OPSIONAL, MAKS 150 KARAKTER) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label text-xs mb-0">Keterangan Tambahan (Opsional)</label>
                        <span class="text-xs font-semibold" :class="(keterangan || '').length > 140 ? 'text-amber-600 font-bold' : 'text-slate-400'">
                            <span x-text="(keterangan || '').length"></span>/150 karakter
                        </span>
                    </div>
                    <textarea name="keterangan" 
                              rows="2" 
                              maxlength="150" 
                              x-model="keterangan" 
                              placeholder="Catatan tambahan bila diperlukan (maksimal 150 karakter)..." 
                              class="form-input text-xs !h-auto"></textarea>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.sppd.create') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold inline-block text-center">
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
                    <button type="button" @click="submitLanjut($event)" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'" class="btn-pill-primary !bg-pink-600 hover:!bg-pink-700 !shadow-pink-500/25 px-6 py-2.5 text-xs font-bold gap-2 shadow-md">
                        <span x-text="submitting ? 'Memproses...' : 'Lanjut &rarr;'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $defaultSelectedIds = array_values(array_map('intval', (array)old('pegawai_id', session('s_sppd_pegawai_id', ($parentSpt && $parentSpt->pegawais ? $parentSpt->pegawais->pluck('id')->all() : [])))));
    $parentSptIds = $parentSpt && $parentSpt->pegawais ? $parentSpt->pegawais->pluck('id')->map(fn($id) => (int)$id)->values()->all() : [];
@endphp

<script>
function createSppdStep2() {
    return {
        allPegawais: @json($pegawais),
        parentSpt: @json($parentSpt),
        parentSptPegawaiIds: @json($parentSptIds),
        tujuan: '{{ addslashes(old('tujuan', session('s_sppd_tujuan', ($parentSpt ? $parentSpt->tujuan : '')))) }}',
        uraian: '{{ addslashes(old('uraian', session('s_sppd_uraian', ($parentSpt ? ($parentSpt->uraian ?: $parentSpt->perihal) : '')))) }}',
        keterangan: '{{ addslashes(old('keterangan', session('s_sppd_keterangan', ''))) }}',
        selectedPegawaiIds: @json($defaultSelectedIds),
        
        searchPegawai: '',
        showPegawaiDropdown: false,
        errorTujuan: false,
        errorUraian: false,
        errorPegawai: false,
        submitting: false,

        showP3kModal: false,
        p3kLoading: false,
        p3kError: '',
        p3kForm: {
            nama: '',
            nip: '',
            jabatan: '',
            pangkat_golongan: '',
            kategori_pegawai: 'P3K'
        },

        init() {
            if (this.parentSpt && this.parentSpt.pegawais && Array.isArray(this.parentSpt.pegawais)) {
                this.parentSpt.pegawais.forEach(p => {
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
            }
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
            if (!this.allPegawais || !this.selectedPegawaiIds) return [];
            return this.selectedPegawaiIds.map(id => {
                const num = Number(typeof id === 'object' && id !== null ? id.id : id);
                return this.allPegawais.find(p => Number(p.id) === num);
            }).filter(Boolean);
        },

        isOutsideSpt(id) {
            if (!this.parentSptPegawaiIds || this.parentSptPegawaiIds.length === 0) return false;
            return !this.parentSptPegawaiIds.some(pid => Number(pid) === Number(id));
        },

        isPegawaiSelected(id) {
            if (!this.selectedPegawaiIds) return false;
            return this.selectedPegawaiIds.some(pid => Number(pid) === Number(id));
        },

        addPegawai(p) {
            const id = (typeof p === 'object' && p !== null) ? Number(p.id) : Number(p);
            if (!id) return;
            if (typeof p === 'object' && p !== null && !this.allPegawais.some(x => Number(x.id) === id)) {
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
            this.selectedPegawaiIds = this.selectedPegawaiIds.filter(pid => Number(pid) !== Number(id));
        },

        getPegawai(id) {
            return this.allPegawais.find(p => Number(p.id) === Number(id));
        },

        openModalP3k() {
            this.p3kForm = {
                nama: '',
                nip: '',
                jabatan: '',
                pangkat_golongan: '',
                kategori_pegawai: 'P3K'
            };
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

        submitLanjut(e) {
            if (e) e.preventDefault();
            this.errorTujuan = !this.tujuan.trim();
            this.errorUraian = !this.uraian.trim();
            this.errorPegawai = this.selectedPegawaiIds.length === 0;

            if (this.errorTujuan || this.errorUraian || this.errorPegawai) {
                return false;
            }

            this.submitting = true;
            document.getElementById('form-sppd-step2').submit();
        },

        simpanDraft(e) {
            if (e) e.preventDefault();
            this.submitting = true;
            const form = document.getElementById('form-sppd-step2');
            form.action = '{{ route('surat.sppd.draft') }}';
            form.submit();
        }
    };
}
</script>
@endsection
