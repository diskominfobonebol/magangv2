@props([
    'name' => 'pegawai_id[]',
    'label' => 'Personil yang Ditugaskan',
    'badgeNumber' => null,
    'subtitle' => 'Personil otomatis terisi dari data rujukan, namun dapat ditambahkan atau disesuaikan.',
    'emptyMessage' => 'Belum ada personil yang dipilih. Silakan cari personil di atas atau pilih data rujukan.',
    'showP3kButton' => true,
    'includeModal' => true,
])

<div class="space-y-3">
    <!-- Header Section Personil -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
            @if($badgeNumber)
                <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">{{ $badgeNumber }}</span>
            @endif
            <div>
                <h4 class="text-xs font-bold text-navy uppercase tracking-wider">{{ $label }}</h4>
                @if($subtitle)
                    <p class="text-[11px] text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($showP3kButton)
                <button type="button" @click="openModalP3k()" class="btn-pill-secondary !px-3 !py-1 !text-xs gap-1.5 cursor-pointer !border-amber-300 text-amber-700 hover:!bg-amber-50">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    + Tambah Pegawai (P3K)
                </button>
            @endif
            <span class="text-xs font-bold text-primary" x-text="selectedPegawaiIds.length + ' Personil Dipilih'"></span>
        </div>
    </div>

    <!-- Search & Add Personil Dropdown -->
    <div class="relative" @click.outside="showPegawaiDropdown = false">
        <div class="relative">
            <input type="text" 
                   x-model="searchPegawai" 
                   @focus="showPegawaiDropdown = true" 
                   @click="showPegawaiDropdown = true" 
                   @input="showPegawaiDropdown = true" 
                   placeholder="Ketik nama atau NIP untuk mencari & menambah personil..." 
                   class="form-input !bg-white !pr-16 text-xs font-medium">
            
            <button type="button" 
                    x-show="searchPegawai" 
                    @click="searchPegawai = ''; showPegawaiDropdown = true" 
                    class="absolute inset-y-0 right-8 pr-1 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer"
                    title="Hapus pencarian">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>

        <!-- Dropdown List Pegawai -->
        <div x-show="showPegawaiDropdown && filteredPegawais().length > 0" 
             x-cloak
             style="display:none;" 
             class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
            <ul class="py-1 divide-y divide-slate-100">
                <template x-for="p in filteredPegawais()" :key="'peg-opt-' + p.id">
                    <li @mousedown.prevent="addPegawai(p)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors flex items-center justify-between group">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <div class="text-sm text-navy font-bold group-hover:text-primary transition-colors" x-text="p.nama"></div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                      :class="p.kategori_pegawai === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                      x-text="p.kategori_pegawai || 'ASN'">
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                <span x-text="(p.kategori_pegawai === 'P3K' ? 'No. Identitas P3K: ' : 'NIP. ') + (p.nip || '-')"></span> &bull; <span x-text="p.jabatan"></span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-primary bg-blue-100 px-2.5 py-1 rounded-lg shrink-0 ml-2 group-hover:bg-primary group-hover:text-white transition-colors">+ Tambah</span>
                    </li>
                </template>
                @if($showP3kButton)
                    <li @mousedown.prevent="openModalP3k(searchPegawai)" class="px-4 py-2 bg-amber-50/60 hover:bg-amber-100/70 text-amber-800 cursor-pointer transition-colors flex items-center justify-between text-xs font-bold border-t border-amber-100">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Pegawai P3K belum terdaftar?
                        </span>
                        <span class="underline">+ Tambah P3K</span>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Dropdown Empty State -->
        <div x-show="showPegawaiDropdown && searchPegawai && searchPegawai.trim().length > 0 && filteredPegawais().length === 0" 
             x-cloak
             style="display:none;" 
             class="absolute z-40 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
            <p class="text-xs text-slate-500 mb-2">Pegawai "<span class="font-bold text-navy" x-text="searchPegawai"></span>" tidak ditemukan.</p>
            @if($showP3kButton)
                <button type="button" @click="openModalP3k(searchPegawai)" class="btn-pill-primary !bg-gradient-to-r !from-amber-500 !to-orange-500 hover:!from-amber-600 hover:!to-orange-600 px-4 py-1.5 text-xs font-bold gap-1.5 shadow-sm inline-flex items-center cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    + Tambah Pegawai Baru (P3K)
                </button>
            @endif
        </div>
    </div>

    <!-- Selected Personil List -->
    <div class="border border-blue-200/60 rounded-2xl p-3 bg-slate-50/50 space-y-2">
        <template x-for="p in getSelectedPegawaiObjects()" :key="'sel-peg-card-' + p.id">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-blue-100 shadow-sm text-xs">
                <input type="hidden" name="{{ $name }}" :value="p.id">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-navy" x-text="p.nama"></span>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold"
                              :class="p.kategori_pegawai === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                              x-text="p.kategori_pegawai || 'ASN'">
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400 block" x-text="(p.kategori_pegawai === 'P3K' ? 'No. Identitas: ' : 'NIP. ') + (p.nip || '-') + ' • ' + (p.jabatan || '')"></span>
                </div>
                <button type="button" @click="removePegawai(p.id)" class="text-slate-400 hover:text-rose-600 p-1 cursor-pointer transition-colors" title="Hapus personil">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>

        <div x-show="selectedPegawaiIds.length === 0" class="p-3 text-center text-xs text-slate-400 italic bg-white rounded-xl">
            {{ $emptyMessage }}
        </div>
    </div>
</div>

@if($includeModal && $showP3kButton)
    <!-- Modal Tambah Pegawai P3K -->
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

            <div @keydown.enter.prevent="submitP3k()" class="space-y-4">
                <div>
                    <label class="form-label text-xs">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="p3kForm.nama" placeholder="Cth: Ahmad Fauzi, S.Kom" class="form-input text-xs">
                </div>

                <div>
                    <label class="form-label text-xs">Nomor Identitas P3K <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="p3kForm.nip" placeholder="Nomor Identitas P3K (cth: 199503152023211001)..." class="form-input text-xs font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">*Nomor Identitas resmi P3K (harus unik).</p>
                </div>

                <div>
                    <label class="form-label text-xs">Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="p3kForm.jabatan" placeholder="Cth: Ahli Pertama - Pranata Komputer" class="form-input text-xs">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-amber-100">
                    <button type="button" @click="closeModalP3k()" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer" :disabled="p3kLoading">
                        Batal
                    </button>
                    <button type="button" @click="submitP3k()" class="btn-pill-primary px-5 py-2 text-xs font-bold shadow-md cursor-pointer !bg-gradient-to-r !from-amber-500 !to-orange-500 hover:!from-amber-600 hover:!to-orange-600 flex items-center gap-1.5" :disabled="p3kLoading">
                        <svg x-show="p3kLoading" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="p3kLoading ? 'Menyimpan...' : 'Simpan & Pilih Pegawai'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
