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
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Informasi Surat</span>
        </div>
        <div class="w-12 border-t-2 border-primary mx-2"></div>
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                2
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Pegawai & Uraian</span>
        </div>
        <div class="w-16 border-t-2 border-slate-200 mx-3"></div>
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">3</div>
            <span class="ml-2 text-xs font-semibold text-slate-500">Nomor SPPD</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
        <form action="{{ route('surat.create.step3') }}" method="POST" class="p-6 md:p-8" id="form-utama">
            @csrf
            <div class="space-y-8">
                
                <!-- Field 1: URAIAN SURAT -->
                <div>
                    <label class="form-label">Uraian Nomor Surat</label>
                    <textarea name="uraian" rows="3" placeholder="Tuliskan uraian atau maksud surat secara bebas..." class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ session('s_uraian') }}</textarea>
                </div>

                <!-- Field 2: DAFTAR PERSONEL -->
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <label class="form-label mb-0">Daftar Pegawai</label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openModalP3k()" class="btn-pill-secondary !px-3 !py-1 !text-xs gap-1.5 cursor-pointer !border-amber-300 text-amber-700 hover:!bg-amber-50">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                + Tambah Pegawai (P3K)
                            </button>
                            <button type="button" @click="addPersonel()" class="btn-pill-secondary !px-3 !py-1 !text-xs gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Tambah Personel
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in personelList" :key="item.id">
                            <div class="flex items-start gap-3">
                                <div class="mt-2 text-sm font-bold text-slate-400 w-5 text-right" x-text="(index + 1) + '.'"></div>
                                <div class="flex-1 relative">
                                    
                                    <!-- Search Input -->
                                    <div class="relative">
                                        <input type="text" 
                                               x-model="item.search" 
                                               @focus="item.showDropdown = true" 
                                               @click.away="item.showDropdown = false"
                                               :readonly="item.selected !== null"
                                               placeholder="Cari nama pegawai..." 
                                               class="form-input !bg-white"
                                               :class="{ 'border-b-0 rounded-b-none !bg-blue-50/40': item.selected !== null }">
                                        
                                        <!-- Remove row button -->
                                        <button type="button" x-show="item.selected === null && personelList.length > 1" @click="removePersonel(index)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-pink-accent cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>

                                    <!-- Dropdown Autocomplete: Ada Hasil -->
                                    <div x-show="item.showDropdown && item.selected === null && filteredPegawai(item.search, item).length > 0" 
                                         style="display:none;" 
                                         class="absolute z-20 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                        <ul class="py-1 divide-y divide-slate-100">
                                            <template x-for="pegawai in filteredPegawai(item.search, item)" :key="pegawai.id">
                                                <li @click="selectPegawai(item, pegawai)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors">
                                                    <div class="flex items-center justify-between">
                                                        <div class="text-sm text-navy font-bold" x-text="pegawai.nama"></div>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                                              :class="pegawai.kategori_pegawai === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                                              x-text="pegawai.kategori_pegawai || 'ASN'">
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-slate-500 mt-0.5"><span x-text="(pegawai.kategori_pegawai === 'P3K' ? 'No. Identitas P3K: ' : 'NIP. ') + (pegawai.nip || '-')"></span> &bull; <span x-text="pegawai.jabatan"></span></div>
                                                </li>
                                            </template>
                                            <li @click="openModalP3k(item, item.search)" class="px-4 py-2 bg-amber-50/60 hover:bg-amber-100/70 text-amber-800 cursor-pointer transition-colors flex items-center justify-between text-xs font-bold border-t border-amber-100">
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    Pegawai P3K belum terdaftar?
                                                </span>
                                                <span class="underline">+ Tambah P3K</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Dropdown Autocomplete: Tidak Ditemukan -->
                                    <div x-show="item.showDropdown && item.selected === null && item.search && item.search.trim().length > 0 && filteredPegawai(item.search, item).length === 0" 
                                         style="display:none;" 
                                         class="absolute z-20 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                                        <p class="text-xs text-slate-500 mb-2">Pegawai "<span class="font-bold text-navy" x-text="item.search"></span>" tidak ditemukan.</p>
                                        <button type="button" @click="openModalP3k(item, item.search)" class="btn-pill-primary !bg-gradient-to-r !from-amber-500 !to-orange-500 hover:!from-amber-600 hover:!to-orange-600 px-4 py-1.5 text-xs font-bold gap-1.5 shadow-sm inline-flex items-center">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                            + Tambah Pegawai Baru (P3K)
                                        </button>
                                    </div>

                                    <!-- Selected Panel -->
                                    <div x-show="item.selected !== null" style="display:none;" class="bg-blue-50/70 border border-t-0 border-blue-200 rounded-b-2xl p-3 relative">
                                        <div class="pr-8">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <p class="text-xs font-bold text-primary" x-text="item.selected?.jabatan"></p>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                      :class="item.selected?.kategori_pegawai === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                                      x-text="item.selected?.kategori_pegawai || 'ASN'">
                                                </span>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-500" x-text="(item.selected?.kategori_pegawai === 'P3K' ? 'Nomor Identitas P3K: ' : 'NIP. ') + item.selected?.nip"></p>
                                            <input type="hidden" name="pegawai_id[]" :value="item.selected?.id" :disabled="!item.selected">
                                        </div>
                                        <button type="button" @click="clearSelection(item)" class="absolute top-3 right-3 text-slate-400 hover:text-pink-accent rounded p-1 transition-colors cursor-pointer" title="Batal pilih">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>

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
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md cursor-pointer">
                        Lanjut &rarr;
                    </button>
                </div>
            </div>  
        </form>
    </div>

    <!-- ============================================== -->
    <!-- MODAL TAMBAH PEGAWAI P3K (KHUSUS P3K VIA SURAT) -->
    <!-- ============================================== -->
    <div x-show="showP3kModal" 
         x-cloak 
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeModalP3k()">
        
        <!-- Backdrop -->
        <div x-show="showP3kModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="closeModalP3k()"></div>

        <!-- Modal Dialog Content -->
        <div x-show="showP3kModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative bg-card-gradient rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-amber-200/80 space-y-5 z-10">
            
            <div class="flex items-start justify-between border-b border-amber-200/60 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center border border-amber-200 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy">Tambah Pegawai Baru (P3K)</h3>
                        <p class="text-xs text-slate-500 font-medium">Khusus Pegawai Pemerintah dengan Perjanjian Kerja</p>
                    </div>
                </div>
                <button type="button" @click="closeModalP3k()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer" title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Error Alert -->
            <div x-show="p3kError" style="display: none;" class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-xl text-xs font-semibold" x-text="p3kError"></div>

            <form @submit.prevent="submitP3k()" class="space-y-4">
                <div>
                    <label class="form-label text-xs">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="p3kForm.nama" required placeholder="Cth: Ahmad Fauzi, S.Kom" class="form-input text-xs">
                </div>

                <div>
                    <label class="form-label text-xs">Nomor Identitas P3K <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="p3kForm.nip" required placeholder="Nomor Identitas P3K (cth: 199503152023211001)..." class="form-input text-xs font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">*Nomor Identitas resmi P3K (harus unik).</p>
                </div>

                <div>
                    <label class="form-label text-xs">Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="p3kForm.jabatan" required placeholder="Cth: Ahli Pertama - Pranata Komputer" class="form-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-amber-100">
                    <button type="button" @click="closeModalP3k()" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer" :disabled="p3kLoading">
                        Batal
                    </button>
                    <button type="submit" class="btn-pill-primary px-5 py-2 text-xs font-bold shadow-md cursor-pointer !bg-gradient-to-r !from-amber-500 !to-orange-500 hover:!from-amber-600 hover:!to-orange-600 flex items-center gap-1.5" :disabled="p3kLoading">
                        <svg x-show="p3kLoading" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="p3kLoading ? 'Menyimpan...' : 'Simpan & Pilih Pegawai'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function personelForm() {
        return {
            pegawaiData: [
                @foreach($pegawais as $p)
                { id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', nip: '{{ addslashes($p->nip) }}', jabatan: '{{ addslashes($p->jabatan) }}', kategori_pegawai: '{{ addslashes($p->kategori_pegawai ?? "ASN") }}' },
                @endforeach
            ],
            personelList: [
                { id: Date.now(), search: '', selected: null, showDropdown: false }
            ],
            showP3kModal: false,
            p3kLoading: false,
            p3kError: '',
            activeP3kItem: null,
            p3kForm: {
                nama: '',
                nip: '',
                jabatan: ''
            },
            addPersonel() {
                this.personelList.push({ id: Date.now(), search: '', selected: null, showDropdown: false });
            },
            removePersonel(index) {
                this.personelList.splice(index, 1);
            },
            clearSelection(item) {
                item.selected = null;
                item.search = '';
                setTimeout(() => { item.showDropdown = true; }, 50);
            },
            filteredPegawai(search, currentItem = null) {
                const selectedIds = this.personelList
                    .map(item => item.selected ? item.selected.id : null)
                    .filter(id => id !== null && (currentItem && currentItem.selected ? id !== currentItem.selected.id : true));

                const s = search ? search.toLowerCase().trim() : '';

                return this.pegawaiData.filter(p => {
                    if ([1, 2, 3].includes(Number(p.id))) return false;
                    if (selectedIds.includes(p.id)) return false;
                    if (!s) return true;
                    return (p.nama && p.nama.toLowerCase().includes(s)) ||
                           (p.nip && p.nip.includes(s)) ||
                           (p.kategori_pegawai && p.kategori_pegawai.toLowerCase().includes(s)) ||
                           (p.jabatan && p.jabatan.toLowerCase().includes(s));
                });
            },
            selectPegawai(item, pegawai) {
                item.selected = pegawai;
                item.search = pegawai.nama;
                item.showDropdown = false;
            },
            openModalP3k(item = null, initialName = '') {
                this.activeP3kItem = item;
                this.p3kError = '';
                this.p3kForm = {
                    nama: initialName || '',
                    nip: '',
                    jabatan: ''
                };
                this.showP3kModal = true;
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
                    
                    // 1. Tambahkan ke pegawaiData lokal
                    this.pegawaiData.push(newPegawai);

                    // 2. Langsung pasang ke baris personil aktif atau baris kosong
                    if (this.activeP3kItem && this.personelList.includes(this.activeP3kItem)) {
                        this.selectPegawai(this.activeP3kItem, newPegawai);
                    } else {
                        let target = this.personelList.find(it => it.selected === null);
                        if (!target) {
                            target = { id: Date.now(), search: '', selected: null, showDropdown: false };
                            this.personelList.push(target);
                        }
                        this.selectPegawai(target, newPegawai);
                    }

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