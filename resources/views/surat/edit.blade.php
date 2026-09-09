@extends('layouts.app')

@section('title', 'Edit Surat')

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
                    <a href="{{ route('surat.show', $surat->id) }}" class="hover:text-primary transition-colors font-semibold">Detail Surat</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Edit</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.rekap') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Rekapitulasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Edit Surat</h2>
                <p class="text-xs text-slate-500 font-semibold">Perbarui informasi nomor surat & SPPD terkait</p>
            </div>
        </div>
        <a href="{{ route('surat.rekap') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" 
         x-data="editForm()">
        
        <form action="{{ route('surat.update', $surat->id) }}" method="POST" class="p-6 md:p-8">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="has_sppd" x-bind:value="pilihanSppd === 'ya' ? 1 : 0">
            <input type="hidden" name="mode_nomor" x-bind:value="modeNomor">
            
            <div class="space-y-8">
                
                <!-- BAGIAN 1: INFORMASI SURAT -->
                <div>
                    <h3 class="text-sm font-bold text-navy border-b border-blue-200/40 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[10px] font-bold">1</span>
                        Informasi Dasar
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Field: TANGGAL SURAT -->
                        <div>
                            <label class="form-label">Tanggal Surat</label>
                            <input type="date" name="tgl_surat" x-model="tanggal" class="form-input" required>
                            <p class="mt-1 text-xs text-slate-500">Backdate diizinkan &mdash; pilih tanggal mundur sesuai kebutuhan</p>
                            <p x-show="formatTanggal()" style="display:none;" x-text="formatTanggal()" class="mt-1 text-sm font-bold text-primary"></p>
                        </div>

                        <!-- Field: JENIS SURAT -->
                        <div>
                            <label class="form-label">Jenis Surat</label>
                            <select name="jenis_surat" x-model="jenisSurat" class="form-input" required>
                                <option value="">&mdash; Pilih Jenis Surat &mdash;</option>
                                <option value="SPT">Surat Perintah Tugas (SPT) &mdash; Kode: 555</option>
                                <option value="SPPD">Surat Perintah Perjalanan Dinas (SPPD) &mdash; Kode: 090</option>
                            </select>
                            
                            <div x-show="jenisSurat" style="display:none;" class="mt-2 flex items-center gap-2">
                                <span class="badge-blue text-xs px-3 py-1 rounded-full font-bold" x-text="jenisSurat"></span>
                                <span class="text-xs font-semibold text-slate-500" x-text="'Kode: ' + kodeSurat()"></span>
                            </div>
                        </div>

                        <!-- Field: NOMOR SURAT -->
                        <div>
                            <label class="form-label">Nomor Surat</label>
                            <div class="inline-flex bg-white/70 p-1 rounded-2xl border border-blue-200/50 mb-3 w-full md:w-auto">
                                <button type="button" @click="modeNomor = 'otomatis'" :class="modeNomor === 'otomatis' ? 'btn-pill-primary !px-4 !py-1.5 !text-xs !shadow-sm' : 'text-slate-600 font-bold px-4 py-1.5 rounded-full text-xs hover:text-navy transition-all'" class="flex-1 md:flex-none">
                                    Generate Otomatis
                                </button>
                                <button type="button" @click="modeNomor = 'manual'" :class="modeNomor === 'manual' ? 'btn-pill-primary !px-4 !py-1.5 !text-xs !shadow-sm' : 'text-slate-600 font-bold px-4 py-1.5 rounded-full text-xs hover:text-navy transition-all'" class="flex-1 md:flex-none">
                                    Isi Manual
                                </button>
                            </div>

                            <div class="mt-1">
                                <div x-show="modeNomor === 'otomatis'" style="display: none;" class="bg-white/80 border border-blue-200/50 rounded-2xl p-4">
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Preview nomor surat:</p>
                                    <div class="text-sm font-extrabold text-primary mb-1 font-mono">
                                        <span x-text="kodeSurat()"></span>/001/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                                    </div>
                                    <p class="text-xs text-slate-400 font-mono">Format: <span x-text="kodeSurat()"></span>/URUT/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span></p>
                                    <input type="hidden" name="nomor_surat" :value="kodeSurat() + '/001/' + formatBulanRomawi() + '/' + formatTahun()">
                                </div>
                                <div x-show="modeNomor === 'manual'" style="display: none;">
                                    <input type="text" name="nomor_surat_manual" x-model="manualNumber" placeholder="Contoh: 555/001/IX/2026" class="form-input font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- Field: TUJUAN SURAT -->
                        <div>
                            <label class="form-label">Tujuan Surat</label>
                            <input type="text" name="tujuan" value="{{ old('tujuan', $surat->tujuan) }}" placeholder="Contoh: Kementerian Dalam Negeri, Jakarta" class="form-input" required>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: PERSONEL & URAIAN -->
                <div>
                    <h3 class="text-sm font-bold text-navy border-b border-blue-200/40 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[10px] font-bold">2</span>
                        Personel & Uraian
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Field: URAIAN SURAT -->
                        <div>
                            <label class="form-label">Uraian Surat</label>
                            <textarea name="uraian" rows="3" class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ old('uraian', $surat->uraian) }}</textarea>
                        </div>

                        <!-- Field: DAFTAR PERSONEL -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="form-label mb-0">Daftar Personel</label>
                                <button type="button" @click="addPersonel()" class="btn-pill-secondary !px-3 !py-1 !text-xs gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Personel
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(item, index) in personelList" :key="item.id">
                                    <div class="flex items-start gap-3">
                                        <input type="hidden" name="pegawai_id[]" :value="item.selected ? item.selected.id : ''">
                                        
                                        <div class="mt-2 text-sm font-bold text-slate-400 w-5 text-right" x-text="(index + 1) + '.'"></div>
                                        <div class="flex-1 relative">
                                            
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input type="text" x-model="item.search" @focus="item.showDropdown = true" @click.away="item.showDropdown = false" :readonly="item.selected !== null" placeholder="Cari nama pegawai..." class="form-input !bg-white" :class="{ 'border-b-0 rounded-b-none !bg-blue-50/40': item.selected !== null }">
                                                <button type="button" x-show="item.selected === null && personelList.length > 1" @click="removePersonel(index)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-pink-accent">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown Autocomplete -->
                                            <div x-show="item.showDropdown && item.selected === null && filteredPegawai(item.search).length > 0" style="display:none;" class="absolute z-20 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                                <ul class="py-1 divide-y divide-slate-100">
                                                    <template x-for="pegawai in filteredPegawai(item.search)" :key="pegawai.id">
                                                        <li @click="selectPegawai(item, pegawai)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors">
                                                            <div class="text-sm text-navy font-bold" x-text="pegawai.nama"></div>
                                                            <div class="text-xs text-slate-500 mt-0.5"><span x-text="pegawai.nip"></span> &bull; <span x-text="pegawai.jabatan"></span></div>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>

                                            <!-- Selected Panel -->
                                            <div x-show="item.selected !== null" style="display:none;" class="bg-blue-50/70 border border-t-0 border-blue-200 rounded-b-2xl p-3 relative">
                                                <div class="pr-8">
                                                    <p class="text-xs font-bold text-primary mb-0.5" x-text="item.selected?.jabatan"></p>
                                                    <p class="text-xs font-semibold text-slate-500" x-text="'NIP. ' + item.selected?.nip"></p>
                                                </div>
                                                <button type="button" @click="clearSelection(item)" class="absolute top-3 right-3 text-slate-400 hover:text-pink-accent rounded p-1 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Field: KETERANGAN TAMBAHAN -->
                        <div>
                            <label class="form-label">Keterangan Tambahan</label>
                            <textarea name="keterangan" rows="2" class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ old('keterangan', $surat->keterangan) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: NOMOR SPPD -->
                <div>
                    <h3 class="text-sm font-bold text-navy border-b border-blue-200/40 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[10px] font-bold">3</span>
                        Opsi SPPD Terkait
                    </h3>
                    
                    <div class="border border-blue-200/60 rounded-3xl p-6 bg-white/50">
                        <div x-show="pilihanSppd === 'ya'" x-transition>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-sm font-bold text-navy">Surat ini memiliki SPPD Terkait</span>
                                <button type="button" @click="pilihanSppd = 'tidak'" class="ml-2 text-xs text-pink-accent font-bold hover:underline cursor-pointer">
                                    Hapus SPPD
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Nomor SPPD akan dipertahankan atau disesuaikan untuk seluruh personel yang ada.</p>
                        </div>
                        
                        <div x-show="pilihanSppd === 'tidak'" style="display:none;" x-transition>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <span class="text-sm font-bold text-slate-600">Surat ini tidak memiliki SPPD terkait.</span>
                                <button type="button" @click="pilihanSppd = 'ya'" class="ml-2 text-xs text-primary font-bold hover:underline cursor-pointer">
                                    Aktifkan SPPD
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Buttons -->
            <div class="mt-10 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <a href="{{ route('surat.rekap') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Batal & Exit
                </a>
                <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md flex items-center cursor-pointer">
                    Simpan Perubahan 
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
    function editForm() {
        const initialPegawais = {!! $surat->pegawais ? $surat->pegawais->toJson() : '[]' !!};
        let initList = initialPegawais.map((p, index) => ({
            id: Date.now() + index,
            search: p.nama,
            selected: { id: p.id, nama: p.nama, nip: p.nip, jabatan: p.jabatan },
            showDropdown: false
        }));
        if (initList.length === 0) {
            initList = [{ id: Date.now(), search: '', selected: null, showDropdown: false }];
        }

        return {
            tanggal: '{{ old('tgl_surat', $surat->tgl_surat) }}',
            jenisSurat: '{{ old('jenis_surat', $surat->jenisSurat->nama_jenis ?? 'SPT') }}',
            modeNomor: 'manual',
            manualNumber: '{{ old('nomor_surat', $surat->nomor_surat) }}',
            pilihanSppd: '{{ $surat->has_sppd ? "ya" : "tidak" }}',
            
            kodeSurat() {
                if (this.jenisSurat === 'SPPD') return '090';
                if (this.jenisSurat === 'SPT') return '555';
                return 'XXX';
            },
            formatTanggal() {
                if (!this.tanggal) return '';
                const d = new Date(this.tanggal);
                if (isNaN(d)) return '';
                const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return hari[d.getDay()] + ', ' + d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
            },
            formatBulanRomawi() {
                if (!this.tanggal) return 'BLN';
                const d = new Date(this.tanggal);
                if (isNaN(d)) return 'BLN';
                const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                return romawi[d.getMonth()];
            },
            formatTahun() {
                if (!this.tanggal) return 'THN';
                const d = new Date(this.tanggal);
                if (isNaN(d)) return 'THN';
                return d.getFullYear();
            },
            
            pegawaiData: [
                @foreach($pegawais ?? [] as $p)
                { id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', nip: '{{ addslashes($p->nip) }}', jabatan: '{{ addslashes($p->jabatan) }}' },
                @endforeach
            ],
            personelList: initList,
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
            filteredPegawai(search) {
                if (!search || search.trim() === '') return this.pegawaiData;
                const s = search.toLowerCase();
                return this.pegawaiData.filter(p => p.nama.toLowerCase().includes(s) || p.nip.includes(s));
            },
            selectPegawai(item, pegawai) {
                item.selected = pegawai;
                item.search = pegawai.nama;
                item.showDropdown = false;
            },
            init() {
                this.$watch('jenisSurat', value => { if(value === 'SPPD') this.modeNomor = 'otomatis' });
            }
        }
    }
</script>
@endsection
