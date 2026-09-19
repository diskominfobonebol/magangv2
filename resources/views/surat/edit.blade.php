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
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50" 
         x-data="editForm()">
        
        <form action="{{ route('surat.update', $surat->id) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="has_sppd" x-bind:value="pilihanSppd === 'ya' ? 1 : 0">
            <input type="hidden" name="mode_nomor" x-bind:value="modeNomor">
            <input type="hidden" name="parent_id" x-bind:value="modeSpt === 'pilih' && selectedSpt ? selectedSpt.id : ''">
            <input type="hidden" name="spt_induk_manual" x-bind:value="modeSpt === 'manual' ? sptManualText.trim() : ''">
            
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

                        <!-- Field Khusus SPPD: SPT INDUK -->
                        @if($surat->parent_id)
                        <!-- SPPD Sudah Terhubung (Read-Only Sesuai Aturan Permanen) -->
                        <div x-show="jenisSurat === 'SPPD'" class="border border-emerald-200 rounded-2xl p-4 bg-emerald-50/40 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-navy uppercase tracking-wider">SPT Induk Terkait</h4>
                                    <span class="badge-green text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Terhubung Permanen
                                    </span>
                                </div>
                            </div>
                            <div class="bg-white border border-emerald-200/80 rounded-xl p-3.5 flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor SPT Induk:</span>
                                    <a href="{{ route('surat.show', $surat->parent_id) }}" class="text-sm font-mono font-bold text-primary hover:underline block mt-0.5">
                                        {{ $surat->parent->nomor_surat ?? '-' }} &rarr;
                                    </a>
                                    @if($surat->parent && $surat->parent->tgl_surat)
                                        <div class="text-xs text-slate-500 mt-1">
                                            <span class="font-semibold text-slate-400">Tanggal:</span>
                                            <span class="font-medium text-slate-700 ml-1">{{ \Carbon\Carbon::parse($surat->parent->tgl_surat)->translatedFormat('d F Y') }}</span>
                                        </div>
                                    @endif
                                    @if($surat->parent && ($surat->parent->uraian || $surat->parent->perihal))
                                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $surat->parent->uraian ?: $surat->parent->perihal }}</p>
                                    @endif
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500 italic">ℹ️ Relasi SPPD ke SPT bersifat permanen dan tidak dapat diubah.</p>
                            <input type="hidden" name="parent_id" value="{{ $surat->parent_id }}">
                        </div>
                        @else
                        <!-- SPPD Legacy Belum Terhubung (Fitur Hubungkan ke SPT) -->
                        <div x-show="jenisSurat === 'SPPD'" x-transition class="border border-amber-200 rounded-2xl p-4 bg-amber-50/40 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-amber-200/60">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Hubungkan ke SPT Induk</h4>
                                        <span class="bg-amber-100 text-amber-800 border border-amber-200 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Data Legacy</span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 mt-0.5">Dokumen SPPD ini belum memiliki SPT induk. Silakan cari dan pilih SPT yang ada di database.</p>
                                </div>

                                <!-- Mode Selector Pill -->
                                <div class="inline-flex bg-white/80 p-1 rounded-xl border border-slate-200">
                                    <button type="button" 
                                            @click="modeSpt = 'pilih'" 
                                            :class="modeSpt === 'pilih' ? 'bg-primary text-white shadow-sm font-bold' : 'text-slate-500 font-semibold hover:text-navy'"
                                            class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1">
                                        Pilih Database
                                    </button>
                                    <button type="button" 
                                            @click="modeSpt = 'manual'" 
                                            :class="modeSpt === 'manual' ? 'bg-primary text-white shadow-sm font-bold' : 'text-slate-500 font-semibold hover:text-navy'"
                                            class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1">
                                        Ketik Manual
                                    </button>
                                </div>
                            </div>

                            <!-- OPSI 1: PILIH DARI DATABASE (COMBOBOX DENGAN SEARCH) -->
                            <div x-show="modeSpt === 'pilih'" class="space-y-2">
                                <div class="relative" @click.outside="showSptDropdown = false">
                                    <label class="form-label !mb-1 text-xs">Cari SPT Induk di Database (Nomor / Tanggal / Tujuan / Uraian)</label>
                                    <div class="relative">
                                        <input type="text" 
                                               x-model="sptSearch" 
                                               @focus="showSptDropdown = true" 
                                               @input="showSptDropdown = true"
                                               placeholder="Ketik nomor SPT, tanggal, tujuan, atau uraian..." 
                                               autocomplete="off"
                                               class="form-input font-mono text-xs font-semibold !pr-16">

                                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                                            <button type="button" 
                                                    x-show="selectedSpt || sptSearch" 
                                                    @click="clearSelectedSpt()" 
                                                    class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer" 
                                                    title="Bersihkan pilihan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <button type="button" 
                                                    @click="showSptDropdown = !showSptDropdown" 
                                                    class="text-slate-400 hover:text-primary p-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 transition-transform" :class="showSptDropdown ? 'rotate-180 text-primary' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Dropdown List SPT -->
                                    <div x-show="showSptDropdown && filteredSptList.length > 0" 
                                         x-cloak
                                         style="display:none;"
                                         class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                        <ul class="py-1 divide-y divide-slate-100">
                                            <template x-for="spt in filteredSptList" :key="'spt-opt-' + spt.id">
                                                <li @mousedown.prevent="selectSpt(spt)" 
                                                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors flex items-center justify-between"
                                                    :class="selectedSpt && selectedSpt.id === spt.id ? 'bg-blue-50 font-bold' : ''">
                                                    <div>
                                                        <div class="font-mono font-bold text-primary text-xs" x-text="spt.nomor_surat"></div>
                                                        <div class="text-[11px] text-slate-500 truncate max-w-sm" x-text="(spt.uraian || 'Surat Tugas') + ' • ' + (spt.tgl_surat || '') + (spt.tujuan ? ' • ' + spt.tujuan : '')"></div>
                                                    </div>
                                                    <span class="text-[10px] font-bold text-primary bg-blue-100 px-2 py-0.5 rounded">Pilih</span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div x-show="showSptDropdown && filteredSptList.length === 0" 
                                         x-cloak
                                         style="display:none;"
                                         class="absolute z-40 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                                        <p class="text-xs text-slate-400 italic">Tidak ditemukan SPT yang cocok.</p>
                                    </div>
                                </div>

                                <template x-if="selectedSpt">
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between gap-2">
                                        <div class="text-xs">
                                            <span class="font-mono font-bold text-navy" x-text="selectedSpt.nomor_surat"></span>
                                            <span class="badge-green text-[10px] px-2 py-0.5 rounded-full font-bold ml-1.5">Terpilih</span>
                                            <p class="text-[11px] text-slate-600 mt-0.5" x-text="selectedSpt.uraian || 'Surat Tugas'"></p>
                                        </div>
                                        <button type="button" @click="clearSelectedSpt()" class="text-xs text-rose-500 hover:underline font-bold">Ganti</button>
                                    </div>
                                </template>
                            </div>

                            <!-- OPSI 2: KETIK MANUAL -->
                            <div x-show="modeSpt === 'manual'" style="display: none;" class="space-y-2">
                                <label class="form-label !mb-1 text-xs">Nomor SPT Induk Manual</label>
                                <input type="text" 
                                       x-model="sptManualText" 
                                       placeholder="Contoh: 555/KOMINFO-BB/SPT-DD/003/IX/2026..." 
                                       class="form-input font-mono text-xs font-semibold">
                                <p class="text-[11px] text-amber-700">Nomor ini akan tersimpan sebagai catatan teks manual jika nomor SPT tidak terdaftar di sistem.</p>
                            </div>
                        </div>
                        @endif

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

                            <div class="mt-1 space-y-2.5">
                                <!-- Notice Banner Backdate -->
                                <div x-show="modeNomor === 'otomatis' && isBackdate && backdateNotice" x-cloak class="p-3.5 bg-amber-50/90 border border-amber-300/80 rounded-2xl text-xs text-amber-900 font-semibold flex items-start gap-2.5 shadow-sm">
                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="backdateNotice"></span>
                                </div>

                                <div x-show="modeNomor === 'otomatis'" style="display: none;" class="bg-white/80 border border-blue-200/50 rounded-2xl p-4">
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Preview nomor surat:</p>
                                    <template x-if="jenisSurat === 'SPPD'">
                                        <div>
                                            <div class="text-sm font-extrabold text-pink-600 mb-1 font-mono">
                                                090/KOMINFO-BB/SPPD/<span x-text="(selectedSpt && selectedSpt.jenis_penugasan) ? selectedSpt.jenis_penugasan : 'DD'"></span>/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                                            </div>
                                            <p class="text-xs text-slate-400 font-mono">Format: 090/KOMINFO-BB/SPPD/DD/URUT/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span></p>
                                        </div>
                                    </template>
                                    <template x-if="jenisSurat !== 'SPPD'">
                                        <div>
                                            <div class="text-sm font-extrabold text-primary mb-1 font-mono">
                                                555/KOMINFO-BB/SPT-DD/<span x-text="nomorUrut"></span>/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span>
                                            </div>
                                            <p class="text-xs text-slate-400 font-mono">Format: 555/KOMINFO-BB/SPT-DD/URUT/<span x-text="formatBulanRomawi()"></span>/<span x-text="formatTahun()"></span></p>
                                        </div>
                                    </template>
                                    <input type="hidden" name="nomor_surat" :value="jenisSurat === 'SPPD' ? ('090/KOMINFO-BB/SPPD/' + ((selectedSpt && selectedSpt.jenis_penugasan) ? selectedSpt.jenis_penugasan : 'DD') + '/' + nomorUrut + '/' + formatBulanRomawi() + '/' + formatTahun()) : ('555/KOMINFO-BB/SPT-DD/' + nomorUrut + '/' + formatBulanRomawi() + '/' + formatTahun())">
                                </div>
                                <div x-show="modeNomor === 'manual'" style="display: none;">
                                    <input type="text" name="nomor_surat_manual" x-model="manualNumber" :placeholder="jenisSurat === 'SPPD' ? 'Contoh: 090/KOMINFO-BB/SPPD/DD/001a/IX/' + formatTahun() : 'Contoh: 555/KOMINFO-BB/SPT-DD/003/IX/' + formatTahun()" class="form-input font-mono">
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
                                        <div class="flex-1 relative" @click.outside="item.showDropdown = false">
                                            
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input type="text" 
                                                       x-model="item.search" 
                                                       @focus="item.showDropdown = true" 
                                                       @click="item.showDropdown = true" 
                                                       @input="item.showDropdown = true" 
                                                       :readonly="item.selected !== null" 
                                                       placeholder="Cari nama pegawai atau NIP..." 
                                                       class="form-input !bg-white !pr-16" 
                                                       :class="{ 'border-b-0 rounded-b-none !bg-blue-50/40': item.selected !== null }">
                                                
                                                <!-- Clear search input -->
                                                <button type="button" 
                                                        x-show="item.selected === null && item.search" 
                                                        @click="item.search = ''; item.showDropdown = true" 
                                                        class="absolute inset-y-0 right-8 pr-1 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer"
                                                        title="Hapus pencarian">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>

                                                <button type="button" x-show="item.selected === null && personelList.length > 1" @click="removePersonel(index)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-pink-accent cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown Hasil Pencarian -->
                                            <div x-show="item.showDropdown && item.selected === null && filteredPegawai(item.search, item).length > 0" 
                                                 style="display: none;" 
                                                 class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                                <ul class="py-1 divide-y divide-slate-100">
                                                    <template x-for="pegawai in filteredPegawai(item.search, item)" :key="pegawai.id">
                                                        <li @mousedown.prevent="selectPegawai(item, pegawai)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors">
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
                                                </ul>
                                            </div>

                                            <div x-show="item.showDropdown && item.selected === null && item.search && item.search.trim().length > 0 && filteredPegawai(item.search, item).length === 0" 
                                                 style="display: none;" 
                                                 class="absolute z-40 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                                                <p class="text-xs text-slate-400 italic">Tidak ada pegawai yang cocok dengan kata kunci pencarian.</p>
                                            </div>

                                            <!-- Selected Pegawai Badge -->
                                            <template x-if="item.selected !== null">
                                                <div class="bg-blue-50/40 border border-t-0 border-blue-200/60 rounded-b-xl p-2.5 flex items-center justify-between text-xs">
                                                    <div>
                                                        <span class="font-bold text-navy" x-text="item.selected.nama"></span>
                                                        <span class="text-slate-500 ml-1" x-text="'(NIP. ' + item.selected.nip + ' • ' + item.selected.jabatan + ')'"></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="clearSelection(item)" class="text-[11px] text-primary hover:underline font-bold">Ganti</button>
                                                        <button type="button" x-show="personelList.length > 1" @click="removePersonel(index)" class="text-rose-500 hover:text-rose-700">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Field: KETERANGAN TAMBAHAN -->
                        <div x-data="{ charCount: {{ strlen(old('keterangan', $surat->keterangan ?? '')) }} }">
                            <label class="form-label">Keterangan Tambahan</label>
                            <textarea name="keterangan" 
                                      rows="2" 
                                      maxlength="150"
                                      @input="charCount = $event.target.value.length"
                                      placeholder="Tambahkan catatan khusus atau informasi tambahan jika diperlukan (opsional)" 
                                      class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90">{{ old('keterangan', $surat->keterangan) }}</textarea>
                            <div class="flex items-center justify-between mt-1 text-xs">
                                <span class="text-slate-400">Maksimal 150 karakter</span>
                                <span :class="charCount > 130 ? (charCount >= 150 ? 'text-rose-600 font-bold' : 'text-amber-600 font-semibold') : 'text-slate-400'" 
                                      x-text="charCount + '/150'"></span>
                            </div>
                            @error('keterangan')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: NOMOR SPPD -->
                <div>
                    <h3 class="text-sm font-bold text-navy border-b border-blue-200/40 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[10px] font-bold">3</span>
                        <span x-text="jenisSurat === 'SPPD' ? 'Nomor SPPD Personel' : 'Opsi SPPD Terkait'"></span>
                    </h3>
                    
                    <div class="border border-blue-200/60 rounded-3xl p-6 bg-white/50">
                        <!-- JIKA DOKUMEN SPT: Opsi SPPD Terkait & Penomoran SPPD Personel -->
                        <template x-if="jenisSurat === 'SPT'">
                            <div class="border border-blue-200/50 rounded-2xl p-4 bg-white/60">
                                <div x-show="pilihanSppd === 'ya'" x-transition>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">✓</span>
                                            <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Nomor SPPD Personel</h4>
                                        </div>
                                        <button type="button" @click="pilihanSppd = 'tidak'" class="text-xs text-rose-500 hover:underline font-bold cursor-pointer">
                                            Hapus SPPD Terkait
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium mb-3">Nomor SPPD per personil yang ditugaskan:</p>
                                    <div class="space-y-2">
                                        <template x-for="(item, idx) in personelList.filter(p => p.selected !== null)" :key="item.id">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-white border border-blue-100 rounded-xl">
                                                <div>
                                                    <span class="text-xs font-bold text-navy" x-text="item.selected?.nama"></span>
                                                    <span class="text-[11px] text-slate-400 ml-1" x-text="'(NIP. ' + item.selected?.nip + ')'"></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-slate-400 font-semibold">No SPPD:</span>
                                                    <input type="text" :name="'nomor_sppd[' + item.selected?.id + ']'" x-model="item.nomor_sppd" placeholder="090/KOMINFO-BB/SPPD/DD/..." class="form-input !py-1 !px-2.5 text-xs font-mono w-64 font-bold text-pink-600">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
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
                        </template>

                        <!-- JIKA DOKUMEN SPPD: Nomor SPPD Personel Wajib (tanpa opsi toggle hapus) -->
                        <template x-if="jenisSurat === 'SPPD'">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="text-sm font-bold text-navy">Nomor SPPD Personel</span>
                                    <span class="badge-pink text-[10px] px-2 py-0.5 rounded-full font-bold uppercase ml-1">Wajib</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium mb-3">Nomor SPPD per personil yang ditugaskan:</p>
                                <div class="space-y-2">
                                    <template x-for="(item, idx) in personelList.filter(p => p.selected !== null)" :key="item.id">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-white border border-blue-100 rounded-xl">
                                            <div>
                                                <span class="text-xs font-bold text-navy" x-text="item.selected?.nama"></span>
                                                <span class="text-[11px] text-slate-400 ml-1" x-text="'(NIP. ' + item.selected?.nip + ')'"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-slate-400 font-semibold">No SPPD:</span>
                                                <input type="text" :name="'nomor_sppd[' + item.selected?.id + ']'" x-model="item.nomor_sppd" placeholder="090/KOMINFO-BB/SPPD/DD/..." class="form-input !py-1 !px-2.5 text-xs font-mono w-64 font-bold text-pink-600">
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- BAGIAN 4: BERKAS FISIK (HASIL SCAN) -->
                <div>
                    <h3 class="text-sm font-bold text-navy border-b border-blue-200/40 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[10px] font-bold">4</span>
                        Berkas Fisik (Scan Tanda Tangan & Cap Basah)
                    </h3>
                    
                    <div class="border border-blue-200/60 rounded-3xl p-6 bg-white/50 space-y-4" x-data="{ selectedFileName: '', selectedFileSize: '', isDragging: false }">
                        
                        @if($surat->file_name || $surat->google_drive_url)
                        <!-- Status File Terupload Saat Ini -->
                        <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 border border-blue-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-primary flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Berkas Terpasang Saat Ini:</span>
                                    <h5 class="text-xs font-bold text-navy font-mono">{{ $surat->file_name ?: 'Berkas Scan Surat' }}</h5>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if($surat->drive_upload_status === 'success')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.2 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersimpan di Google Drive
                                            </span>
                                        @elseif($surat->drive_upload_status === 'failed')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-100 px-2 py-0.2 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Drive Sync Gagal
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.2 rounded-full">
                                                Tersimpan Lokal
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($surat->google_drive_url)
                                <a href="{{ $surat->google_drive_url }}" target="_blank" rel="noopener noreferrer"
                                   class="btn-pill-primary !px-3.5 !py-1.5 !text-xs gap-1.5 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    Buka di Google Drive
                                </a>
                            @endif
                        </div>
                        @endif

                        <div class="space-y-2">
                            <label class="form-label text-xs">
                                {{ ($surat->file_name || $surat->google_drive_url) ? 'Ganti / Perbarui Berkas Scan' : 'Unggah Berkas Scan (Opsional)' }}
                            </label>
                            
                            <!-- Drag & Drop Upload Zone -->
                            <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all bg-white"
                                 :class="isDragging ? 'border-primary bg-blue-50/50 scale-[0.99]' : 'border-blue-200 hover:border-blue-300'"
                                 @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="
                                    isDragging = false; 
                                    if ($event.dataTransfer.files.length > 0) {
                                        $refs.fileInputEdit.files = $event.dataTransfer.files;
                                        const f = $event.dataTransfer.files[0];
                                        selectedFileName = f.name;
                                        selectedFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                                    }
                                 ">
                                <input type="file" 
                                       name="file_surat" 
                                       x-ref="fileInputEdit"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                       @change="
                                        if ($event.target.files.length > 0) {
                                            const f = $event.target.files[0];
                                            selectedFileName = f.name;
                                            selectedFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                                        } else {
                                            selectedFileName = '';
                                            selectedFileSize = '';
                                        }
                                       ">
                                
                                <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-primary flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    </div>
                                    <div>
                                        <template x-if="!selectedFileName">
                                            <div>
                                                <p class="text-xs font-bold text-navy">
                                                    Pilih file scan atau tarik (drag & drop) ke sini
                                                </p>
                                                <p class="text-[11px] text-slate-400 mt-0.5">
                                                    Format didukung: <strong>PDF, JPG, JPEG, PNG</strong> (Maksimal 10 MB)
                                                </p>
                                            </div>
                                        </template>
                                        <template x-if="selectedFileName">
                                            <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-medium inline-flex items-center gap-2">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span>File dipilih: <strong class="font-mono text-emerald-900" x-text="selectedFileName"></strong> (<span x-text="selectedFileSize"></span>)</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                💡 File akan otomatis diunggah ke Google Drive ke folder sesuai tahun dan jenis surat setelah Anda menyimpan perubahan.
                            </p>
                            @error('file_surat')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>

            <!-- Footer Buttons -->
            <div class="mt-10 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
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
            nomor_sppd: p.pivot?.nomor_sppd || '',
            showDropdown: false
        }));
        if (initList.length === 0) {
            initList = [{ id: Date.now(), search: '', selected: null, nomor_sppd: '', showDropdown: false }];
        }

        return {
            tanggal: @json(old('tgl_surat', $surat->tgl_surat)),
            jenisSurat: @json(old('jenis_surat', $surat->jenisSurat->nama_jenis ?? 'SPT')),
            modeNomor: 'manual',
            nomorUrut: @json($nomorUrutSurat ?? '001'),
            manualNumber: @json(old('nomor_surat', $surat->nomor_surat)),
            pilihanSppd: @json($surat->has_sppd ? "ya" : "tidak"),
            isBackdate: @json((isset($eval['is_backdate']) && $eval['is_backdate']) ? true : false),
            backdateNotice: @json($eval['notice'] ?? ''),
            predecessorNomor: @json($eval['predecessor_nomor'] ?? ''),
            seriesSummary: @json($seriesSummary ?? null),

            // State Khusus SPPD (SPT Induk)
            modeSpt: @json($surat->parent_id ? "pilih" : ($surat->spt_induk_manual ? "manual" : "pilih")),
            sptSearch: @json($surat->parent ? $surat->parent->nomor_surat : ''),
            showSptDropdown: false,
            sptList: @json($sptList ?? []),
            selectedSpt: {!! json_encode($surat->parent ? ['id' => $surat->parent->id, 'nomor_surat' => $surat->parent->nomor_surat, 'uraian' => $surat->parent->uraian, 'tgl_surat' => $surat->parent->tgl_surat, 'tujuan' => $surat->parent->tujuan, 'jenis_penugasan' => $surat->parent->jenis_penugasan] : null) !!},
            sptManualText: @json(old('spt_induk_manual', $surat->spt_induk_manual ?? '')),

            get filteredSptList() {
                if (!this.sptSearch.trim()) return this.sptList;
                const q = this.sptSearch.toLowerCase().trim();
                return this.sptList.filter(s => 
                    (s.nomor_surat && s.nomor_surat.toLowerCase().includes(q)) ||
                    (s.uraian && s.uraian.toLowerCase().includes(q)) ||
                    (s.tujuan && s.tujuan.toLowerCase().includes(q))
                );
            },

            selectSpt(spt) {
                this.selectedSpt = spt;
                this.sptSearch = spt.nomor_surat;
                this.showSptDropdown = false;
                this.evaluateBackdate();
            },

            clearSelectedSpt() {
                this.selectedSpt = null;
                this.sptSearch = '';
                this.showSptDropdown = false;
                this.evaluateBackdate();
            },
            
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
                { id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', nip: '{{ addslashes($p->nip) }}', jabatan: '{{ addslashes($p->jabatan) }}', kategori_pegawai: '{{ addslashes($p->kategori_pegawai ?? "ASN") }}' },
                @endforeach
            ],
            getLetterSuffix(index) {
                let result = '';
                let n = index;
                while (n >= 0) {
                    result = String.fromCharCode(97 + (n % 26)) + result;
                    n = Math.floor(n / 26) - 1;
                }
                return result;
            },
            evaluateBackdate() {
                if (!this.tanggal) return;
                const d = new Date(this.tanggal);
                if (isNaN(d)) return;
                const thn = String(d.getFullYear());
                const bln = this.formatBulanRomawi();
                const targetDate = this.tanggal;
                const seriesKey = (this.jenisSurat === 'SPPD') ? 'sppd' : 'spt';
                const jp = (this.selectedSpt && this.selectedSpt.jenis_penugasan) ? this.selectedSpt.jenis_penugasan : 'DD';

                if (this.seriesSummary && this.seriesSummary[seriesKey]) {
                    const items = this.seriesSummary[seriesKey][thn] || [];
                    const maxBaseSeq = items.reduce((max, it) => Math.max(max, it.base_seq || 0), 0);
                    const latestDate = items.reduce((latest, it) => (!latest || it.tgl_surat > latest) ? it.tgl_surat : latest, null);

                    if (items.length > 0 && latestDate && targetDate < latestDate) {
                        this.isBackdate = true;
                        const predecessors = items.filter(it => it.tgl_surat <= targetDate)
                            .sort((a, b) => (b.tgl_surat.localeCompare(a.tgl_surat) || b.base_seq - a.base_seq));
                        const pred = predecessors[0] || items.sort((a, b) => a.tgl_surat.localeCompare(b.tgl_surat))[0];
                        const baseSeq = pred ? pred.base_seq : 1;
                        const paddedBase = String(baseSeq).padStart(3, '0');
                        const predNomor = pred ? pred.nomor_surat : (this.jenisSurat === 'SPPD' ? `090/KOMINFO-BB/SPPD/${jp}/${paddedBase}/${bln}/${thn}` : `555/KOMINFO-BB/SPT-DD/${paddedBase}/${bln}/${thn}`);

                        const usedLetters = items.filter(it => it.base_seq === baseSeq).map(it => it.letter).filter(Boolean);
                        let letterIdx = 0;
                        while (usedLetters.includes(this.getLetterSuffix(letterIdx))) {
                            letterIdx++;
                        }
                        const letter = this.getLetterSuffix(letterIdx);
                        this.nomorUrut = paddedBase + letter;
                        const previewNomor = (this.jenisSurat === 'SPPD') 
                            ? `090/KOMINFO-BB/SPPD/${jp}/${paddedBase}${letter}/${bln}/${thn}`
                            : `555/KOMINFO-BB/SPT-DD/${paddedBase}${letter}/${bln}/${thn}`;
                        this.backdateNotice = `Terdeteksi tanggal mundur (backdate) — nomor akan disisipkan sebagai anak dari nomor ${predNomor}, menjadi ${previewNomor}.`;
                    } else {
                        this.isBackdate = false;
                        this.backdateNotice = '';
                        this.nomorUrut = String(maxBaseSeq + 1).padStart(3, '0');
                    }
                }

                // Sync with backend API
                fetch(`/surat/api/check-backdate?series=${this.jenisSurat}&tgl_surat=${targetDate}&jenis_penugasan=${jp}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data) {
                            this.isBackdate = Boolean(data.is_backdate);
                            this.nomorUrut = data.next_seq;
                            this.backdateNotice = data.notice || '';
                            this.predecessorNomor = data.predecessor_nomor || '';
                        }
                    })
                    .catch(() => {});
            },
            getNextSppdNumberForPersonel() {
                const jp = (this.selectedSpt && this.selectedSpt.jenis_penugasan) ? this.selectedSpt.jenis_penugasan : 'DD';
                const bln = this.formatBulanRomawi();
                const thn = this.formatTahun();
                
                // SPPD base sequence is independent of SPT
                let baseSeq = '001';
                if (this.jenisSurat === 'SPPD' && this.nomorUrut) {
                    baseSeq = this.nomorUrut;
                } else if (this.seriesSummary && this.seriesSummary.sppd) {
                    const sppdItems = this.seriesSummary.sppd[thn] || [];
                    const maxSppd = sppdItems.reduce((max, it) => Math.max(max, it.base_seq || 0), 0);
                    baseSeq = String(maxSppd + 1).padStart(3, '0');
                }

                let existingLetters = [];
                this.personelList.forEach(p => {
                    if (p.nomor_sppd) {
                        const m = p.nomor_sppd.match(/(?:SPPD\/[^\/]+\/|^)(\d+)([a-z]*)\//i);
                        if (m && m[1].padStart(3, '0') === baseSeq.padStart(3, '0')) {
                            existingLetters.push((m[2] || '').toLowerCase());
                        }
                    }
                });

                let letterIndex = 0;
                let letter = this.getLetterSuffix(letterIndex);
                while (existingLetters.includes(letter)) {
                    letterIndex++;
                    letter = this.getLetterSuffix(letterIndex);
                }
                return `090/KOMINFO-BB/SPPD/${jp}/${baseSeq}${letter}/${bln}/${thn}`;
            },
            addPersonel() {
                const nextSppd = this.getNextSppdNumberForPersonel();
                this.personelList.push({ id: Date.now(), search: '', selected: null, nomor_sppd: nextSppd, showDropdown: false });
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
                    .map(item => item.selected ? Number(item.selected.id) : null)
                    .filter(id => id !== null && (currentItem && currentItem.selected ? id !== Number(currentItem.selected.id) : true));

                const s = search ? search.toLowerCase().trim() : '';

                return this.pegawaiData.filter(p => {
                    if (!p || !p.id) return false;
                    if ([1, 2, 3].includes(Number(p.id))) return false;
                    if (selectedIds.includes(Number(p.id))) return false;
                    if (!s) return true;
                    const nama = String(p.nama || '').toLowerCase();
                    const nip = String(p.nip || '').toLowerCase();
                    const kat = String(p.kategori_pegawai || 'ASN').toLowerCase();
                    const jab = String(p.jabatan || '').toLowerCase();
                    return nama.includes(s) || nip.includes(s) || kat.includes(s) || jab.includes(s);
                });
            },
            selectPegawai(item, pegawai) {
                item.selected = pegawai;
                item.search = pegawai.nama;
                item.showDropdown = false;
            },
            init() {
                this.$watch('jenisSurat', value => { 
                    if(value === 'SPPD') this.modeNomor = 'otomatis';
                    this.evaluateBackdate();
                });
                this.$watch('tanggal', () => {
                    this.evaluateBackdate();
                });
            }
        }
    }
</script>
@endsection
