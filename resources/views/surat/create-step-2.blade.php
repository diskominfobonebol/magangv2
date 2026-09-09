@extends('layouts.app')

@section('title', 'Buat Surat Baru - Langkah 2')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
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
    @if ($errors->any())
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
            <span class="ml-2 text-xs font-bold text-primary">Personel & Uraian</span>
        </div>
        <div class="w-16 border-t-2 border-slate-200 mx-3"></div>
        <div class="flex items-center opacity-60">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-500 font-bold text-xs">3</div>
            <span class="ml-2 text-xs font-semibold text-slate-500">Nomor SPPD</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" x-data="personelForm()">
        <form action="{{ route('surat.create.step3') }}" method="POST" class="p-6 md:p-8" id="form-utama">
            @csrf
            <div class="space-y-8">
                
                <!-- Field 1: URAIAN SURAT -->
                <div>
                    <label class="form-label">Uraian Surat</label>
                    <textarea name="uraian" rows="3" placeholder="Tuliskan uraian atau maksud surat secara bebas..." class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ session('s_uraian') }}</textarea>
                </div>

                <!-- Field 2: DAFTAR PERSONEL -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="form-label mb-0">Daftar Pegawai</label>
                        <button type="button" @click="addPersonel()" class="btn-pill-secondary !px-3 !py-1 !text-xs gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Pegawai
                        </button>
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
                                        
                                        <!-- Remove row button (saat belum memilih pegawai) -->
                                        <button type="button" x-show="item.selected === null && personelList.length > 1" @click="removePersonel(index)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-pink-accent cursor-pointer" title="Hapus baris">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>

                                    <!-- Dropdown Autocomplete -->
                                    <div x-show="item.showDropdown && item.selected === null" 
                                         style="display:none;" 
                                         class="absolute z-20 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                        <ul class="py-1 divide-y divide-slate-100">
                                            <template x-for="pegawai in filteredPegawai(item.search, item)" :key="pegawai.id">
                                                <li @click="selectPegawai(item, pegawai)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors">
                                                    <div class="text-sm text-navy font-bold" x-text="pegawai.nama"></div>
                                                    <div class="text-xs text-slate-500 mt-0.5"><span x-text="pegawai.nip"></span> &bull; <span x-text="pegawai.jabatan"></span></div>
                                                </li>
                                            </template>
                                            <template x-if="filteredPegawai(item.search, item).length === 0">
                                                <li class="px-4 py-3 text-xs text-slate-400 italic text-center">
                                                    <span x-show="!item.search || item.search.trim() === ''">Semua pegawai telah ditambahkan ke daftar surat</span>
                                                    <span x-show="item.search && item.search.trim() !== ''">Pegawai tidak ditemukan atau sudah dipilih</span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Selected Panel -->
                                    <div x-show="item.selected !== null" style="display:none;" class="bg-blue-50/70 border border-t-0 border-blue-200 rounded-b-2xl p-3 relative">
                                        <div class="pr-16">
                                            <p class="text-xs font-bold text-primary mb-0.5" x-text="item.selected?.jabatan"></p>
                                            <p class="text-xs font-semibold text-slate-500" x-text="'NIP. ' + item.selected?.nip"></p>
                                            <input type="hidden" name="pegawai_id[]" :value="item.selected?.id" :disabled="!item.selected">
                                        </div>
                                        <div class="absolute top-3 right-3 flex items-center gap-1.5">
                                            <!-- Tombol Hapus Baris Pegawai -->
                                            <button type="button" 
                                                    x-show="personelList.length > 1" 
                                                    @click="removePersonel(index)" 
                                                    class="text-slate-400 hover:text-rose-600 rounded p-1 transition-colors cursor-pointer" 
                                                    title="Hapus baris pegawai ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            <!-- Tombol Ganti / Batal Pilih -->
                                            <button type="button" 
                                                    @click="clearSelection(item)" 
                                                    class="text-slate-400 hover:text-amber-500 rounded p-1 transition-colors cursor-pointer" 
                                                    title="Ganti pegawai">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Field 3: KETERANGAN TAMBAHAN -->
                <div>
                    <label class="form-label">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="2" placeholder="Keterangan tambahan (opsional)..." class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ session('s_keterangan') }}</textarea>
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
                    <button type="button" onclick="document.getElementById('form-simpan-draft').submit();" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold cursor-pointer">
                        Simpan Draft
                    </button>
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md cursor-pointer">
                        Lanjut &rarr;
                    </button>
                </div>
            </div>  
        </form>

        <!-- Form Tersembunyi Khusus untuk Simpan Draft -->
        <form id="form-simpan-draft" action="{{ route('surat.draft') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<script>
    function personelForm() {
        return {
            pegawaiData: [
                @foreach($pegawais as $p)
                { id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', nip: '{{ addslashes($p->nip) }}', jabatan: '{{ addslashes($p->jabatan) }}' },
                @endforeach
            ],
            @php
                $savedPegawaiIds = session('s_pegawai_id', []);
                $initPersonelList = [];
                if (!empty($savedPegawaiIds) && is_array($savedPegawaiIds)) {
                    foreach ($savedPegawaiIds as $spId) {
                        $pModel = $pegawais->firstWhere('id', (int)$spId);
                        if ($pModel) {
                            $initPersonelList[] = [
                                'id' => (int)$spId,
                                'search' => $pModel->nama,
                                'selected' => [
                                    'id' => $pModel->id,
                                    'nama' => $pModel->nama,
                                    'nip' => $pModel->nip ?? '-',
                                    'jabatan' => $pModel->jabatan ?? '-',
                                ],
                                'showDropdown' => false,
                            ];
                        }
                    }
                }
            @endphp
            personelList: @if(!empty($initPersonelList)) {!! json_encode($initPersonelList) !!} @else [
                { id: Date.now(), search: '', selected: null, showDropdown: false }
            ] @endif,
            addPersonel() {
                this.personelList.push({ 
                    id: Date.now() + Math.floor(Math.random() * 1000), 
                    search: '', 
                    selected: null, 
                    showDropdown: false 
                });
            },
            removePersonel(index) {
                if (this.personelList.length > 1) {
                    this.personelList.splice(index, 1);
                } else {
                    this.clearSelection(this.personelList[0]);
                }
            },
            clearSelection(item) {
                item.selected = null;
                item.search = '';
                setTimeout(() => { item.showDropdown = true; }, 50);
            },
            filteredPegawai(search, currentItem = null) {
                // Kumpulkan ID seluruh pegawai yang sedang dipilih pada baris lain
                const selectedIds = this.personelList
                    .filter(it => it.selected !== null && (!currentItem || it !== currentItem))
                    .map(it => it.selected.id);

                // Filter daftar pegawai: kecualikan yang sudah dipilih di baris lain
                let list = this.pegawaiData.filter(p => !selectedIds.includes(p.id));

                if (!search || search.trim() === '') {
                    return list;
                }
                const s = search.toLowerCase();
                return list.filter(p => p.nama.toLowerCase().includes(s) || (p.nip && p.nip.includes(s)));
            },
            selectPegawai(item, pegawai) {
                item.selected = pegawai;
                item.search = pegawai.nama;
                item.showDropdown = false;
            }
        }
    }
</script>
@endsection