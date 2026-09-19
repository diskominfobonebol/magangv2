@extends('layouts.app')

@section('title', 'Buat Surat Baru - Langkah 2')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="personelForm()">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.create') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Langkah 1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Surat Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 2 dari 3</p>
            </div>
        </div>
        <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Peringatan Validasi Error -->
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
        <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-xs font-semibold">
            <ul class="list-disc pl-4 space-y-1">
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
            <span class="ml-2 text-xs font-semibold text-slate-400">Informasi Nomor Surat</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-primary mx-2"></div>
        <!-- Step 2 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                2
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Personel & Uraian</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-slate-200 mx-2"></div>
        <!-- Step 3 (Inactive) -->
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">3</div>
            <span class="ml-2 text-xs font-semibold text-slate-500">{{ (session('s_jenis_surat_id') == 1) ? 'SPT Induk & Nomor SPPD' : 'Nomor SPPD' }}</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
        <form action="{{ route('surat.create.step3') }}" method="POST" class="p-6 md:p-8" id="form-utama" @submit.prevent="submitLanjut($event)">
            @csrf
            <div class="space-y-8">
                
                <!-- Field 1: URAIAN SURAT -->
                <div>
                    <label class="form-label">Uraian Nomor Surat</label>
                    <textarea name="uraian" rows="3" placeholder="Tuliskan uraian atau maksud surat secara bebas..." class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ session('s_uraian') }}</textarea>
                </div>

                <!-- Alert Validasi Personil Client-side -->
                <div x-show="errorPegawai" x-cloak class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span x-text="errorPegawaiMessage || 'Pilih minimal 1 pegawai untuk melanjutkan.'"></span>
                    </div>
                    <button type="button" @click="errorPegawai = false" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Field 2: DAFTAR PERSONEL -->
                <x-personil-selector label="Daftar Pegawai" subtitle="Pilih personil yang ditugaskan dalam pelaksanaan Surat Perintah Tugas." emptyMessage="Belum ada personel yang dipilih. Silakan cari dan pilih minimal 1 personel di atas." />

                <!-- Field 3: KETERANGAN TAMBAHAN -->
                <div x-data="{ ket: {{ json_encode(session('s_keterangan', '')) }}, maxKet: 150 }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="form-label !mb-0">Keterangan Tambahan</label>
                        <span class="text-xs font-semibold" :class="(ket || '').length > 140 ? 'text-amber-600 font-bold' : 'text-slate-400'">
                            <span x-text="(ket || '').length"></span>/<span x-text="maxKet"></span> karakter
                        </span>
                    </div>
                    <textarea name="keterangan" rows="2" maxlength="150" x-model="ket" placeholder="Keterangan tambahan (opsional, maks 150 karakter)..." class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90"></textarea>
                    @error('keterangan')
                        <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.create') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold inline-block text-center">
                        &larr; Kembali
                    </a>
                    <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Batal & Exit
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="simpanDraft($event)" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold cursor-pointer">
                        Simpan Draft
                    </button>
                    <button type="button" @click="submitLanjut($event)" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md cursor-pointer">
                        Lanjut &rarr;
                    </button>
                </div>
            </div>  
        </form>
    </div>
</div>

<script>
    function personelForm() {
        return {
            allPegawais: [
                @foreach($pegawais as $p)
                { id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', nip: '{{ addslashes($p->nip) }}', jabatan: '{{ addslashes($p->jabatan) }}', kategori_pegawai: '{{ addslashes($p->kategori_pegawai ?? "ASN") }}' },
                @endforeach
            ],
            selectedPegawaiIds: @json(session('s_pegawai_id', old('pegawai_id', []))),
            searchPegawai: '',
            showPegawaiDropdown: false,
            showP3kModal: false,
            p3kLoading: false,
            p3kError: '',
            errorPegawai: false,
            errorPegawaiMessage: '',
            p3kForm: {
                nama: '',
                nip: '',
                jabatan: ''
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
                if (!this.selectedPegawaiIds.some(id => Number(id) === Number(p.id))) {
                    this.selectedPegawaiIds.push(p.id);
                }
                this.errorPegawai = false;
                this.errorPegawaiMessage = '';
                this.searchPegawai = '';
                this.showPegawaiDropdown = false;
            },

            removePegawai(id) {
                this.selectedPegawaiIds = this.selectedPegawaiIds.filter(pid => Number(pid) !== Number(id));
            },

            submitLanjut(e) {
                if (!this.selectedPegawaiIds || this.selectedPegawaiIds.length === 0) {
                    this.errorPegawai = true;
                    this.errorPegawaiMessage = 'Pilih minimal 1 pegawai untuk melanjutkan.';
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    return false;
                }
                this.errorPegawai = false;
                this.errorPegawaiMessage = '';
                const form = document.getElementById('form-utama');
                form.action = '{{ route('surat.create.step3') }}';
                form.submit();
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
            },

            simpanDraft(e) {
                e.preventDefault();
                const form = document.getElementById('form-utama');
                form.action = '{{ route('surat.draft') }}';
                form.submit();
            }
        }
    }
</script>
@endsection