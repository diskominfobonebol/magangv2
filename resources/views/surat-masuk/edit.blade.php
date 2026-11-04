@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    uraian: @js(old('uraian', $suratMasuk->uraian ?? '')),
    keterangan: @js(old('keterangan', $suratMasuk->keterangan ?? '')),
    maxUraian: 200,
    maxKeterangan: 150
}">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1">
                    <li>
                        <a href="{{ route('surat-masuk.index') }}" class="hover:text-primary font-semibold text-slate-500">
                            Surat Masuk
                        </a>
                    </li>
                    <li><span class="mx-2 text-slate-300">/</span></li>
                    <li><span class="text-slate-400 font-medium">Edit Data</span></li>
                </ol>
            </nav>
            <h2 class="text-2xl font-bold text-navy">Edit Data Surat Masuk</h2>
            <p class="text-slate-500 text-xs mt-0.5">Perbarui informasi arsip surat masuk <span class="font-mono font-bold text-primary">{{ $suratMasuk->nomor_surat }}</span></p>
        </div>
        <div>
            <a href="{{ route('surat-masuk.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-slate-900 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Error Validasi Global -->
    @if ($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs space-y-1 shadow-sm">
        <div class="font-bold flex items-center gap-2 text-rose-800">
            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Terdapat beberapa kesalahan pengisian form:
        </div>
        <ul class="list-disc pl-5 space-y-0.5 text-rose-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Card Form Edit Surat Masuk -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50">
        <form action="{{ route('surat-masuk.update', $suratMasuk->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Data Pokok Surat Masuk -->
            <div class="space-y-4" x-data="suratMasukForm({
                nomorSurat: @js(old('nomor_surat', $suratMasuk->nomor_surat)),
                asalSurat: @js(old('asal_surat', $suratMasuk->asal_surat ?? '')),
                options: @js($daftarAsalSurat ?? []),
                excludeId: @js($suratMasuk->id)
            })" @click.outside="showDropdown = false">
                <div class="border-b border-blue-100 pb-2">
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Informasi Surat</h3>
                    <p class="text-xs text-slate-500">Nomor dan tanggal sesuai yang tertera pada dokumen surat fisik yang diterima.</p>
                </div>

                <!-- Warning Non-Blocking Duplikat Surat Masuk -->
                <div x-show="isDuplicateWarning" x-cloak class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl text-xs text-amber-900 font-medium flex items-start gap-2.5 shadow-sm">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <span class="font-bold text-amber-950">Peringatan (Informasi):</span> <span x-text="warningMsg"></span>
                        <span class="block text-[11px] text-amber-700 mt-0.5">Kombinasi Nomor Surat dan Asal Surat ini sudah pernah dicatat sebelumnya. Anda tetap dapat menyimpan perubahan jika dokumen ini memang benar.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nomor Surat (Manual) -->
                    <div>
                        <label for="nomor_surat" class="form-label">Nomor Surat <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               id="nomor_surat" 
                               name="nomor_surat" 
                               x-model="nomorSurat"
                               @input.debounce.400ms="checkDup()"
                               placeholder="Contoh: 005/123/DISPORA/2026" 
                               required 
                               class="form-input font-mono font-semibold @error('nomor_surat') !border-rose-400 !ring-1 !ring-rose-400 @enderror">
                        <span class="text-[11px] text-slate-400 mt-1 block">Ketik persis nomor surat dari instansi pengirim.</span>
                        @error('nomor_surat')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Surat (Date Picker, allow backdate) -->
                    <div>
                        <label for="tanggal_surat" class="form-label">Tanggal Surat <span class="text-rose-500">*</span></label>
                        <input type="date" 
                               id="tanggal_surat" 
                               name="tanggal_surat" 
                               value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat ? $suratMasuk->tanggal_surat->format('Y-m-d') : '') }}" 
                               required 
                               class="form-input @error('tanggal_surat') !border-rose-400 !ring-1 !ring-rose-400 @enderror">
                        <span class="text-[11px] text-slate-400 mt-1 block">Tanggal terbit yang tertera di surat.</span>
                        @error('tanggal_surat')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Asal Surat / Instansi Pengirim (Combobox: Pilih atau Ketik Manual) -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="asal_surat" class="form-label !mb-0">Asal Surat / Instansi Pengirim <span class="text-rose-500">*</span></label>
                        <template x-if="options.length > 0">
                            <span class="text-[11px] text-primary font-semibold flex items-center gap-1 cursor-pointer" @click="showDropdown = !showDropdown">
                                <span x-text="options.length + ' saran tersimpan'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="showDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </template>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <input type="text" 
                               id="asal_surat" 
                               name="asal_surat" 
                               x-model="asalSurat"
                               @focus="showDropdown = true"
                               @click="showDropdown = true"
                               @input.debounce.400ms="showDropdown = true; checkDup()"
                               @keydown.escape="showDropdown = false"
                               list="list-asal-surat"
                               placeholder="Pilih dari saran atau ketik nama instansi baru..." 
                               required 
                               autocomplete="off"
                               class="form-input !pl-10 !pr-14 @error('asal_surat') !border-rose-400 !ring-1 !ring-rose-400 @enderror">

                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                            <button type="button" 
                                    x-show="asalSurat && asalSurat.length > 0" 
                                    @click="asalSurat = ''; showDropdown = true; checkDup()" 
                                    class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer" 
                                    title="Bersihkan teks">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <button type="button" 
                                    @click="showDropdown = !showDropdown" 
                                    class="text-slate-400 hover:text-primary p-1 cursor-pointer" 
                                    title="Tampilkan semua saran instansi">
                                <svg class="w-4 h-4 transition-transform" :class="showDropdown ? 'rotate-180 text-primary' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>

                        <!-- Dropdown Panel Saran Instansi -->
                        <div x-show="showDropdown" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-30 w-full mt-1.5 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs">
                            
                            <!-- State 1: Ada saran yang cocok -->
                            <template x-if="filteredOptions.length > 0">
                                <div>
                                    <div class="p-2.5 bg-blue-50/70 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex justify-between items-center sticky top-0 border-b border-blue-100/60">
                                        <span>Pilih Instansi yang Pernah Diinput:</span>
                                        <span x-text="filteredOptions.length + ' instansi'"></span>
                                    </div>
                                    <template x-for="(opt, idx) in filteredOptions" :key="'asal-opt-' + idx">
                                        <div @mousedown.prevent="selectOption(opt)" 
                                             class="p-3 hover:bg-blue-50/80 cursor-pointer transition-colors flex items-center justify-between group">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2 h-2 rounded-full bg-blue-400 group-hover:bg-primary transition-colors flex-shrink-0"></span>
                                                <span class="font-bold text-navy group-hover:text-primary transition-colors" x-text="opt"></span>
                                            </div>
                                            <span class="text-[10px] font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 bg-blue-100/60 px-2 py-0.5 rounded-md">
                                                Pilih &crarr;
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- State 2: Belum ada data instansi tersimpan di database sama sekali -->
                            <template x-if="options.length === 0">
                                <div class="p-4 text-center">
                                    <div class="w-8 h-8 mx-auto mb-1.5 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Belum ada instansi tersimpan.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Ketik nama instansi baru secara langsung pada field ini.</p>
                                </div>
                            </template>

                            <!-- State 3: Ada saran tersimpan, tetapi filter pencarian tidak cocok dengan yang diketik -->
                            <template x-if="options.length > 0 && filteredOptions.length === 0">
                                <div class="p-4 text-center">
                                    <p class="text-xs font-semibold text-slate-700">Instansi tidak ditemukan dalam saran.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Lanjutkan mengetik untuk menggunakan nama instansi baru ini.</p>
                                </div>
                            </template>
                        </div>

                        <!-- Datalist Fallback -->
                        <datalist id="list-asal-surat">
                            @foreach($daftarAsalSurat ?? [] as $instansi)
                                <option value="{{ $instansi }}">
                            @endforeach
                        </datalist>
                    </div>

                    <span class="text-[11px] text-slate-400 block pt-0.5">
                        Pilih nama instansi dari daftar saran yang sudah ada, atau ketik nama instansi baru secara langsung.
                    </span>

                    @error('asal_surat')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 2: Isi Surat & Keterangan -->
            <div class="space-y-4 pt-2">
                <div class="border-b border-blue-100 pb-2">
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Perihal & Catatan</h3>
                    <p class="text-xs text-slate-500">Ringkasan maksud surat dan keterangan tambahan tindak lanjut.</p>
                </div>

                <!-- Uraian / Perihal Singkat -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="uraian" class="form-label !mb-0">Uraian / Perihal Singkat Surat <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] font-semibold" :class="uraian.length > 190 ? 'text-amber-600 font-bold' : 'text-slate-400'">
                            <span x-text="uraian.length"></span>/200 karakter
                        </span>
                    </div>
                    <textarea id="uraian" 
                              name="uraian" 
                              rows="3" 
                              maxlength="200" 
                              x-model="uraian" 
                              placeholder="Ketik isi pokok atau perihal surat masuk..." 
                              required 
                              class="w-full rounded-2xl border border-slate-300 p-3.5 focus:border-blue-500 focus:outline-none text-xs text-navy bg-white/90 shadow-sm leading-relaxed @error('uraian') !border-rose-400 !ring-1 !ring-rose-400 @enderror"></textarea>
                    @error('uraian')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan Tambahan (Opsional) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="keterangan" class="form-label !mb-0">Keterangan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                        <span class="text-[11px] font-semibold" :class="(keterangan || '').length > 140 ? 'text-amber-600 font-bold' : 'text-slate-400'">
                            <span x-text="(keterangan || '').length"></span>/150 karakter
                        </span>
                    </div>
                    <textarea id="keterangan" 
                              name="keterangan" 
                              rows="2" 
                              maxlength="150" 
                              x-model="keterangan" 
                              placeholder="Catatan tambahan, disposisi, atau status tindak lanjut (opsional, maks 150 karakter)..." 
                              class="w-full rounded-2xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-xs text-navy bg-white/90 shadow-sm leading-relaxed @error('keterangan') !border-rose-400 !ring-1 !ring-rose-400 @enderror"></textarea>
                    @error('keterangan')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 3: Link Google Drive Bukti Fisik -->
            <div class="space-y-4 pt-2">
                <div class="border-b border-blue-100 pb-2">
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wider">Tautan Bukti Fisik</h3>
                    <p class="text-xs text-slate-500">Tautan ke scan atau foto surat fisik yang disimpan di Google Drive.</p>
                </div>

                <div>
                    <label for="link_google_drive" class="form-label">Link Google Drive <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </div>
                        <input type="url" 
                               id="link_google_drive" 
                               name="link_google_drive" 
                               value="{{ old('link_google_drive', $suratMasuk->link_google_drive) }}" 
                               placeholder="https://drive.google.com/file/d/.../view?usp=sharing" 
                               required 
                               class="form-input !pl-10 font-mono text-xs @error('link_google_drive') !border-rose-400 !ring-1 !ring-rose-400 @enderror">
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Pastikan link Google Drive sudah diatur agar memiliki akses baca bagi yang berkepentingan.</span>
                    @error('link_google_drive')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tombol Aksi Submit & Batal -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-blue-100/60">
                <a href="{{ route('surat-masuk.index') }}" class="btn-pill-secondary px-6 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900">
                    Batal
                </a>
                <button type="submit" class="btn-pill-primary px-7 py-2.5 text-xs font-bold gap-2 shadow-lg shadow-blue-500/25 flex items-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('suratMasukForm', (config = {}) => suratMasukForm(config));
    });

    function suratMasukForm(config = {}) {
        return {
            nomorSurat: config.nomorSurat || '',
            asalSurat: config.asalSurat || '',
            showDropdown: false,
            options: config.options || [],
            isDuplicateWarning: false,
            warningMsg: '',
            get filteredOptions() {
                if (!this.asalSurat) return this.options;
                const query = this.asalSurat.toLowerCase().trim();
                return this.options.filter(opt => opt.toLowerCase().includes(query));
            },
            selectOption(val) {
                this.asalSurat = val;
                this.showDropdown = false;
                this.checkDup();
            },
            checkDup() {
                const n = (this.nomorSurat || '').trim();
                const a = (this.asalSurat || '').trim();
                if (!n || !a) {
                    this.isDuplicateWarning = false;
                    this.warningMsg = '';
                    return;
                }
                let url = `/surat/api/check-surat-masuk-duplikat?nomor_surat=${encodeURIComponent(n)}&asal_surat=${encodeURIComponent(a)}`;
                if (config.excludeId) {
                    url += `&exclude_id=${encodeURIComponent(config.excludeId)}`;
                }
                fetch(url)
                    .then(r => r.json())
                    .then(d => {
                        if (d && d.is_duplicate) {
                            this.isDuplicateWarning = true;
                            this.warningMsg = d.message;
                        } else {
                            this.isDuplicateWarning = false;
                            this.warningMsg = '';
                        }
                    })
                    .catch(() => {});
            }
        };
    }
</script>
@endsection
