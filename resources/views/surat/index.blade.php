@extends('layouts.app')

@section('title', 'Surat Keluar (SPT & SPPD)')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="suratIndexManager()">

    <!-- TAMPILAN 1: TABEL DAN REKAPITULASI SURAT -->
    <div x-show="viewMode === 'table'" class="space-y-6">
        <!-- Header Utama & Tombol Aksi -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-navy">Surat Keluar (SPT & SPPD)</h2>
                <p class="text-slate-500 mt-1 text-sm">Kelola dan telusuri arsip penerbitan nomor surat tugas (SPT) dan perjalanan dinas (SPPD).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('surat.rekap.exportPdf', request()->query()) }}" target="_blank" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center hover:text-primary transition-all">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Rekapitulasi PDF
                </a>
                @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.create') }}" class="btn-pill-primary px-5 py-2.5 text-xs font-bold gap-2 shadow-lg shadow-blue-500/25 flex items-center" title="Buat Surat Perintah Tugas (SPT) Baru">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        + Buat SPT Baru
                    </a>
                    <button type="button" @click="openCreateSppdModal()" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center bg-white text-pink-600 border border-pink-200 hover:bg-pink-50 hover:border-pink-300 hover:text-pink-700 cursor-pointer" title="Buat Surat Perintah Perjalanan Dinas (SPPD) Baru">
                        <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        + Buat SPPD
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Alert Notifikasi Sukses -->
        @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @endif

        <!-- Alert Notifikasi Error -->
        @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @endif

        <!-- Filter & Pencarian Form -->
        <div class="bg-card-gradient p-6 rounded-3xl border border-blue-200/50 shadow-sm">
            <form method="GET" action="{{ route('surat.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @if(request('filter_jenis'))
                    <input type="hidden" name="filter_jenis" value="{{ request('filter_jenis') }}">
                @endif

                <!-- Cari Nomor/Perihal (Col span 6) -->
                <div class="md:col-span-6">
                    <label class="form-label">Cari Nomor/Perihal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" placeholder="Cari nomor surat, perihal, personil..." value="{{ request('search') }}" class="form-input !pl-10">
                    </div>
                </div>

                <!-- Tahun Arsip (Col span 2) -->
                <div class="md:col-span-2">
                    <label class="form-label">Tahun Arsip</label>
                    <select name="year" onchange="this.form.submit()" class="form-input">
                        <option value="">Semua Tahun</option>
                        @php
                            $currentYear = date('Y');
                            $startYear = $currentYear - 5;
                        @endphp
                        @for ($y = $currentYear; $y >= $startYear; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Dari Tanggal (Col span 2) -->
                <div class="md:col-span-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" onchange="this.form.submit()" value="{{ request('start_date') }}" class="form-input">
                </div>

                <!-- Sampai Tanggal (Col span 2) -->
                <div class="md:col-span-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" onchange="this.form.submit()" value="{{ request('end_date') }}" class="form-input">
                </div>

                <div class="md:col-span-12 flex justify-between items-center pt-1">
                    <span class="text-xs text-slate-400 font-medium">Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded text-[10px] font-mono">Enter</kbd> atau pilih opsi untuk menyaring data.</span>
                    <a href="{{ route('surat.index') }}" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Reset Filter
                    </a>
                </div>
            </form>
        </div>

        <!-- Kotak Ringkasan Metrik Rekap -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card Total Nomor SPT (Interaktif Filter) -->
            @php
                $isSptActive = (request('filter_jenis') == 'SPT' || request('jenis') == 'SPT');
                $sptUrl = $isSptActive ? route('surat.index', request()->except(['filter_jenis', 'jenis', 'page'])) : route('surat.index', array_merge(request()->except('page'), ['filter_jenis' => 'SPT']));
            @endphp
            <a href="{{ $sptUrl }}" 
               class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl cursor-pointer block {{ $isSptActive ? 'border-primary ring-2 ring-blue-500/50 bg-blue-50/40 shadow-blue-500/20' : 'border-blue-200/50 hover:border-blue-400 hover:shadow-blue-500/15' }}"
               title="{{ $isSptActive ? 'Klik untuk membatalkan filter SPT' : 'Klik untuk menyaring baris yang memiliki Nomor Surat SPT' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider transition-colors {{ $isSptActive ? 'text-primary' : 'text-slate-400 group-hover:text-primary' }}">Total Nomor SPT</p>
                            @if($isSptActive)
                                <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Filter Aktif</span>
                            @endif
                        </div>
                        <h3 class="text-3xl font-extrabold text-navy group-hover:text-primary transition-colors">{{ $totalSpt ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-all duration-300 shadow-sm {{ $isSptActive ? 'bg-primary text-white scale-105' : 'bg-blue-50 text-primary group-hover:bg-primary group-hover:text-white' }}">
                        📄
                    </div>
                </div>
                <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium text-slate-600">Surat Perintah Tugas</span>
                    <span class="text-primary font-bold {{ $isSptActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }} transition-opacity flex items-center gap-1">
                        {{ $isSptActive ? '✕ Batalkan Filter' : 'Filter SPT →' }}
                    </span>
                </div>
            </a>

            <!-- Card Total Nomor SPPD (Interaktif Filter) -->
            @php
                $isSppdActive = (request('filter_jenis') == 'SPPD' || request('jenis') == 'SPPD');
                $sppdUrl = $isSppdActive ? route('surat.index', request()->except(['filter_jenis', 'jenis', 'page'])) : route('surat.index', array_merge(request()->except('page'), ['filter_jenis' => 'SPPD']));
            @endphp
            <a href="{{ $sppdUrl }}" 
               class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl cursor-pointer block {{ $isSppdActive ? 'border-pink-500 ring-2 ring-pink-500/50 bg-pink-50/40 shadow-pink-500/20' : 'border-blue-200/50 hover:border-pink-400 hover:shadow-pink-500/15' }}"
               title="{{ $isSppdActive ? 'Klik untuk membatalkan filter SPPD' : 'Klik untuk menyaring baris yang memiliki Nomor Surat SPPD' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider transition-colors {{ $isSppdActive ? 'text-pink-600' : 'text-slate-400 group-hover:text-pink-500' }}">Total Nomor SPPD</p>
                            @if($isSppdActive)
                                <span class="bg-pink-100 text-pink-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Filter Aktif</span>
                            @endif
                        </div>
                        <h3 class="text-3xl font-extrabold text-navy group-hover:text-pink-600 transition-colors">{{ $totalSppd ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-all duration-300 shadow-sm {{ $isSppdActive ? 'bg-pink-500 text-white scale-105' : 'bg-pink-50 text-pink-500 group-hover:bg-pink-500 group-hover:text-white' }}">
                        ⚡
                    </div>
                </div>
                <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium text-slate-600">Surat Perjalanan Dinas</span>
                    <span class="text-pink-600 font-bold {{ $isSppdActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }} transition-opacity flex items-center gap-1">
                        {{ $isSppdActive ? '✕ Batalkan Filter' : 'Filter SPPD →' }}
                    </span>
                </div>
            </a>

            <!-- Card Total Surat Bulan Ini -->
            <a href="{{ route('surat.index', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" 
               class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/15 block cursor-pointer"
               title="Klik untuk menyaring surat pada bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 group-hover:text-indigo-500 transition-colors">Total Surat Bulan Ini</p>
                        <h3 class="text-3xl font-extrabold text-navy group-hover:text-indigo-600 transition-colors">{{ $totalBulanIni ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center font-bold text-xl group-hover:scale-110 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-300 shadow-sm">
                        📅
                    </div>
                </div>
                <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium text-slate-600">Periode {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                    <span class="text-indigo-600 font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                        Lihat Filter &rarr;
                    </span>
                </div>
            </a>
        </div>

        <!-- TABEL REKAPITULASI KESELURUHAN -->
        <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-blue-100/60">
                <div>
                    <h3 class="text-lg font-bold text-navy">Tabel Rekapitulasi Keseluruhan</h3>
                    <p class="text-xs text-slate-500">Daftar arsip penerbitan nomor surat SPT berdampingan dengan nomor SPPD terkait.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center text-xs font-bold text-primary bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">
                        {{ $totalSpt ?? 0 }} Total SPT
                    </span>
                    <span class="inline-flex items-center text-xs font-bold text-pink-600 bg-pink-50 px-3 py-1 rounded-full border border-pink-200/60">
                        {{ $totalSppd ?? 0 }} Total SPPD
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-blue-100 text-xs font-bold text-slate-400 uppercase bg-white/40">
                            <th class="py-3.5 px-3">Tanggal Surat</th>
                            <th class="py-3.5 px-3">Nomor Surat SPT</th>
                            <th class="py-3.5 px-3">Nomor Surat SPPD</th>
                            <th class="py-3.5 px-3">Perihal / Uraian Tugas</th>
                            <th class="py-3.5 px-3">Tujuan</th>
                            <th class="py-3.5 px-3">Pegawai yang Ditugaskan</th>
                            <th class="py-3.5 px-3">Keterangan</th>
                            <th class="py-3.5 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($surats ?? [] as $item)
                        @php
                            // Siapkan data JSON untuk panel "Lihat"
                            $isSppdItem = ($item->jenis_surat_id == 1 || str_starts_with($item->nomor_surat, '090/'));
                            
                            $directSppds = [];
                            if ($item->has_sppd && $item->pegawais) {
                                foreach ($item->pegawais as $p) {
                                    if (!empty($p->pivot->nomor_sppd) && $p->pivot->nomor_sppd !== '-') {
                                        $directSppds[] = [
                                            'nomor' => $p->pivot->nomor_sppd,
                                            'nama' => $p->nama,
                                            'url' => route('surat.show', $item->id),
                                            'is_child' => false,
                                        ];
                                    }
                                }
                            }

                            $childSppds = [];
                            if ($item->children && $item->children->count() > 0) {
                                foreach ($item->children as $child) {
                                    if ($child->pegawais && $child->pegawais->count() > 0) {
                                        foreach ($child->pegawais as $cp) {
                                            $cNomor = $cp->pivot->nomor_sppd ?: $child->nomor_surat;
                                            if (!empty($cNomor) && $cNomor !== '-') {
                                                $childSppds[] = [
                                                    'nomor' => $cNomor,
                                                    'nama' => $cp->nama,
                                                    'url' => route('surat.show', $child->id),
                                                    'is_child' => true,
                                                ];
                                            }
                                        }
                                    } else {
                                        $childSppds[] = [
                                            'nomor' => $child->nomor_surat,
                                            'nama' => '-',
                                            'url' => route('surat.show', $child->id),
                                            'is_child' => true,
                                        ];
                                    }
                                }
                            }

                            $allSppdList = [];
                            if ($item->has_sppd && $item->pegawais) {
                                foreach ($item->pegawais as $p) {
                                    if (!empty($p->pivot->nomor_sppd) && $p->pivot->nomor_sppd !== '-') {
                                        $allSppdList[] = [
                                            'id' => $p->id,
                                            'nomor' => $p->pivot->nomor_sppd,
                                            'nama' => $p->nama,
                                            'nip' => $p->nip ?? '-',
                                            'jabatan' => $p->jabatan ?? '-',
                                            'url' => route('surat.show', $item->id),
                                            'is_child' => false,
                                            'label' => 'SPPD SPT',
                                        ];
                                    }
                                }
                            }

                            if ($item->children && $item->children->count() > 0) {
                                foreach ($item->children as $child) {
                                    if ($child->pegawais && $child->pegawais->count() > 0) {
                                        foreach ($child->pegawais as $cp) {
                                            $cNomor = $cp->pivot->nomor_sppd ?: $child->nomor_surat;
                                            if (!empty($cNomor) && $cNomor !== '-') {
                                                $allSppdList[] = [
                                                    'id' => $cp->id,
                                                    'nomor' => $cNomor,
                                                    'nama' => $cp->nama,
                                                    'nip' => $cp->nip ?? '-',
                                                    'jabatan' => $cp->jabatan ?? '-',
                                                    'url' => route('surat.show', $child->id),
                                                    'is_child' => true,
                                                    'label' => 'SPPD Susulan',
                                                ];
                                            }
                                        }
                                    } else {
                                        $allSppdList[] = [
                                            'id' => null,
                                            'nomor' => $child->nomor_surat,
                                            'nama' => '-',
                                            'nip' => '-',
                                            'jabatan' => '-',
                                            'url' => route('surat.show', $child->id),
                                            'is_child' => true,
                                            'label' => 'SPPD Susulan',
                                        ];
                                    }
                                }
                            }

                            $jsonData = [
                                'id' => $item->id,
                                'nomor_surat' => $item->nomor_surat,
                                'tgl_surat' => $item->tgl_surat,
                                'tgl_formatted' => \Carbon\Carbon::parse($item->tgl_surat)->translatedFormat('d F Y'),
                                'perihal' => $item->perihal,
                                'tujuan' => $item->tujuan,
                                'uraian' => $item->uraian ?? '',
                                'keterangan' => $item->keterangan ?? '',
                                'has_sppd' => (int)$item->has_sppd,
                                'status' => $item->status ?? 'Terbit',
                                'jenis' => optional($item->jenisSurat)->nama_jenis ?? ($isSppdItem ? 'SPPD' : 'SPT'),
                                'is_sppd' => $isSppdItem,
                                'parent_id' => $item->parent_id,
                                'parent_nomor' => $item->parent ? $item->parent->nomor_surat : null,
                                'parent_tgl' => $item->parent ? \Carbon\Carbon::parse($item->parent->tgl_surat)->translatedFormat('d F Y') : null,
                                'parent_uraian' => $item->parent ? ($item->parent->uraian ?: $item->parent->perihal) : null,
                                'spt_induk_manual' => $item->spt_induk_manual,
                                'is_spt_manual' => !empty($item->spt_induk_manual) && empty($item->parent_id),
                                'file_name' => $item->file_name,
                                'file_path' => $item->file_path,
                                'google_drive_file_id' => $item->google_drive_file_id,
                                'google_drive_url' => $item->google_drive_url,
                                'drive_upload_status' => $item->drive_upload_status ?? 'none',
                                'all_sppds' => $allSppdList,
                                'pegawais' => $item->pegawais->map(function($p) use ($item) {
                                    return [
                                        'id' => $p->id,
                                        'nama' => $p->nama,
                                        'nip' => $p->nip,
                                        'jabatan' => $p->jabatan,
                                        'nomor_sppd' => $p->pivot->nomor_sppd ?? ''
                                    ];
                                })->values()->toArray(),
                                'children' => $item->children ? $item->children->map(function($c) {
                                    return [
                                        'id' => $c->id,
                                        'nomor_surat' => $c->nomor_surat,
                                        'tgl_surat' => $c->tgl_surat,
                                        'tujuan' => $c->tujuan,
                                        'pegawais' => $c->pegawais->map(function($cp) {
                                            return [
                                                'nama' => $cp->nama,
                                                'nip' => $cp->nip,
                                                'nomor_sppd' => $cp->pivot->nomor_sppd ?? ''
                                            ];
                                        })->values()->toArray()
                                    ];
                                })->values()->toArray() : []
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-4 px-3 font-semibold text-slate-600 text-xs whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->tgl_surat)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-4 px-3 font-mono font-bold text-primary text-xs whitespace-nowrap">
                                @if($isSppdItem)
                                    {{-- Jika baris ini adalah SPPD mandiri (legacy) --}}
                                    <div id="spt-link-row-{{ $item->id }}">
                                        @if($item->parent_id && $item->parent)
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('surat.show', $item->parent_id) }}" class="hover:underline text-primary" title="Lihat SPT Induk (Terhubung)">
                                                    {{ $item->parent->nomor_surat }}
                                                </a>
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-primary border border-blue-200" title="Terhubung ke SPT Database">Induk</span>
                                            </div>
                                        @elseif(!empty($item->spt_induk_manual))
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-slate-700">{{ $item->spt_induk_manual }}</span>
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="SPT Induk diketik manual / di luar sistem">Manual</span>
                                            </div>
                                        @else
                                            <span class="text-amber-600 font-bold text-[10px] bg-amber-50 px-2 py-0.5 rounded border border-amber-200" title="Belum terhubung ke SPT Induk">Belum Ada Induk</span>
                                        @endif
                                    </div>
                                @else
                                    {{-- Jika baris ini adalah SPT --}}
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('surat.show', $item->id) }}" class="hover:underline">
                                            {{ $item->nomor_surat }}
                                        </a>
                                        @if($item->children && $item->children->count() > 0)
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-primary border border-blue-200" title="SPT Induk (Memiliki {{ $item->children->count() }} Dokumen SPPD Susulan)">Induk</span>
                                        @endif
                                        @if(($item->status ?? 'Terbit') === 'Draft')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Draft</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-3 font-mono font-bold text-xs">
                                @if(count($directSppds) > 0 || count($childSppds) > 0)
                                    <ul class="space-y-1.5">
                                        @foreach($directSppds as $sppd)
                                            <li>
                                                <a href="{{ $sppd['url'] }}" class="text-pink-600 hover:underline inline-flex items-center gap-1.5 whitespace-nowrap" title="{{ $sppd['nama'] }}">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block flex-shrink-0"></span>
                                                    {{ $sppd['nomor'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                        @foreach($childSppds as $sppd)
                                            <li>
                                                <a href="{{ $sppd['url'] }}" class="text-pink-600 hover:underline inline-flex items-center gap-1.5 whitespace-nowrap" title="{{ $sppd['nama'] }} (SPPD Susulan)">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block flex-shrink-0"></span>
                                                    {{ $sppd['nomor'] }}
                                                    <span class="text-[9px] font-bold text-pink-700 bg-pink-50 border border-pink-200 px-1.5 py-0.2 rounded-full">Susulan</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif($item->has_sppd)
                                    <span class="bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-full font-bold text-[10px] whitespace-nowrap inline-flex items-center gap-1" title="Surat ditandai memiliki SPPD tetapi belum ada personel / nomor SPPD yang tercatat">
                                        <svg class="w-3 h-3 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Data Tidak Lengkap
                                    </span>
                                @else
                                    <span class="text-slate-400 font-semibold px-2">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-navy font-semibold text-xs max-w-xs">
                                @if(!empty($item->uraian))
                                    <div class="line-clamp-2 leading-relaxed font-medium" title="{{ $item->uraian }}">{{ $item->uraian }}</div>
                                @elseif(($item->status ?? 'Terbit') === 'Draft')
                                    <div class="text-slate-400 italic font-normal">(Belum diisi &mdash; Draft)</div>
                                @else
                                    <div class="text-slate-400 italic font-normal">-</div>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-slate-700 font-medium text-xs">
                                {{ $item->tujuan }}
                            </td>
                            <td class="py-4 px-3 text-xs text-slate-700">
                                @if($item->pegawais && $item->pegawais->count() > 0)
                                    <div>
                                        @if($item->children && $item->children->count() > 0)
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Personel SPT:</span>
                                        @endif
                                        <ul class="list-disc pl-4 space-y-1">
                                            @foreach($item->pegawais as $pegawai)
                                                <li><strong class="text-navy">{{ $pegawai->nama }}</strong> <span class="text-slate-500 text-[11px]">(NIP. {{ $pegawai->nip }})</span></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada pegawai</span>
                                @endif

                                @if($item->children && $item->children->count() > 0)
                                    @foreach($item->children as $child)
                                        @if($child->pegawais && $child->pegawais->count() > 0)
                                            <div class="mt-2.5 pt-2 border-t border-blue-100/70">
                                                <span class="text-[10px] font-bold text-pink-600 uppercase tracking-wider block mb-1">
                                                    Personel SPPD Susulan ({{ $child->nomor_surat }}):
                                                </span>
                                                <ul class="list-disc pl-4 space-y-0.5">
                                                    @foreach($child->pegawais as $cp)
                                                        <li><strong class="text-slate-700">{{ $cp->nama }}</strong> <span class="text-slate-500 text-[11px]">(NIP. {{ $cp->nip }})</span></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td class="py-4 px-3 text-xs text-slate-600">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td class="py-4 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Berkas Drive / Upload Cepat -->
                                    @if($item->google_drive_url)
                                        <a href="{{ $item->google_drive_url }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs border border-emerald-200/60 shadow-sm transition-all"
                                           title="Buka Berkas di Google Drive (Tab Baru)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            Lihat File
                                        </a>
                                    @else
                                        <button type="button" 
                                                @click="openUploadModalFor({{ json_encode($jsonData) }})" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-50 text-slate-600 hover:bg-primary hover:text-white font-bold text-xs border border-slate-200 shadow-sm transition-all cursor-pointer"
                                                title="Unggah Berkas Fisik Scan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            Upload
                                        </button>
                                    @endif

                                    <!-- Tombol Lihat Detail -->
                                    <button type="button" 
                                            @click="openDetail({{ json_encode($jsonData) }})" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-primary hover:bg-primary hover:text-white font-bold text-xs border border-blue-200/60 shadow-sm transition-all cursor-pointer"
                                            title="Lihat Detail & Edit Cepat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Lihat
                                    </button>

                                    <!-- Tombol Hapus -->
                                    @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
                                    @php
                                        $childCount = $item->children ? $item->children->count() : 0;
                                        $childNomors = $item->children ? $item->children->pluck('nomor_surat')->filter()->values()->all() : [];
                                    @endphp
                                    <button type="button" 
                                            @click="confirmDelete('{{ $item->id }}', '{{ $item->nomor_surat }}', {{ $childCount }}, {{ json_encode($childNomors) }})" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs border border-rose-200/60 shadow-sm transition-all cursor-pointer"
                                            title="{{ $childCount > 0 ? 'SPT ini memiliki ' . $childCount . ' SPPD terkait (tidak dapat dihapus)' : 'Hapus Surat' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-slate-400 italic">Belum ada data rekapitulasi nomor surat yang sesuai dengan filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-2 py-4 border-t border-blue-200/40">
                {{ $surats->links() }}
            </div>
        </div>
    </div>

    <!-- TAMPILAN 2: PANEL DETAIL NOMOR SURAT (FULL PAGE PANEL SESUAI SHOW.BLADE) -->
    <div x-show="viewMode === 'detail'" x-cloak class="space-y-6" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        
        <form id="detailSuratForm" :action="'{{ url('surat') }}/' + (activeSurat.id || '')" method="POST">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="tgl_surat" :value="activeSurat.tgl_surat">
            <input type="hidden" name="nomor_surat" :value="activeSurat.nomor_surat">
            <input type="hidden" name="has_sppd" :value="activeSurat.has_sppd">

            <template x-for="p in (activeSurat.pegawais || [])" :key="'hid-p-' + p.id">
                <input type="hidden" name="pegawai_id[]" :value="p.id">
            </template>

            <!-- Breadcrumb & Header Panel -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-blue-100/60">
                <div>
                    <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1">
                            <li>
                                <button type="button" @click="closeDetail()" class="hover:text-primary font-semibold text-slate-500 cursor-pointer">
                                    Nomor Surat
                                </button>
                            </li>
                            <li><span class="mx-2 text-slate-300">/</span></li>
                            <li><span class="text-slate-400 font-medium">Detail Nomor Surat</span></li>
                        </ol>
                    </nav>
                    <h2 class="text-2xl font-bold text-navy">Detail Nomor Surat</h2>
                    <p class="text-xs text-slate-500 font-semibold" x-text="'Nomor: ' + (activeSurat.nomor_surat || '-')"></p>
                </div>

                <!-- 3 Tombol Aksi Header: Edit, Simpan, Tutup/Kembali -->
                <div class="flex items-center gap-2.5">
                    <!-- Tombol 1: Edit (Ikon edit) -->
                    <button type="button" 
                            @click="isEditing = !isEditing" 
                            :class="isEditing ? 'bg-amber-100 text-amber-900 border-amber-300' : 'btn-pill-secondary text-slate-700 hover:text-primary'"
                            class="px-4 py-2 text-xs font-bold flex items-center gap-1.5 rounded-xl border shadow-sm transition-all cursor-pointer"
                            title="Edit data surat">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span x-text="isEditing ? 'Batal Edit' : 'Edit'"></span>
                    </button>

                    <!-- Tombol 2: Simpan (Ikon check, warna accent/primary) -->
                    <button type="button" 
                            @click="saveDetail()" 
                            class="btn-pill-primary px-5 py-2 text-xs font-bold gap-1.5 shadow-md flex items-center cursor-pointer"
                            title="Simpan perubahan data">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan
                    </button>

                    <!-- Tombol 3: Tutup/Kembali (Ikon X) -->
                    <button type="button" 
                            @click="closeDetail()" 
                            class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-rose-600 hover:border-rose-300 transition-all cursor-pointer"
                            title="Tutup dan kembali ke tabel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Tutup
                    </button>
                </div>
            </div>

            <!-- Struktur Layout Panel Detail: Dua Kolom -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">
                
                <!-- KOLOM KIRI (Area Utama, Lebih Lebar) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card Informasi Detail Laporan -->
                    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
                        
                        <!-- Header Card: Badge Jenis Surat & Nomor Surat -->
                        <div class="flex items-center justify-between border-b border-blue-100 pb-4">
                            <div class="w-full mr-4">
                                <span class="badge-blue px-3 py-1 rounded-full text-xs font-extrabold uppercase" x-text="activeSurat.jenis || 'SPT'"></span>
                                <h3 class="text-lg font-bold text-navy mt-2 font-mono" x-text="activeSurat.nomor_surat"></h3>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs text-slate-400 font-semibold block">Tanggal Terbit</span>
                                <span class="text-sm font-bold text-navy" x-text="activeSurat.tgl_formatted"></span>
                            </div>
                        </div>

                        <!-- Baris SPT Induk (Khusus SPPD) -->
                        <template x-if="activeSurat.parent_nomor || activeSurat.spt_induk_manual">
                            <div class="p-3.5 bg-blue-50/70 border border-blue-200/80 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 font-semibold">Merujuk ke SPT Induk:</span>
                                    <span class="font-mono font-bold text-navy" x-text="activeSurat.parent_nomor || activeSurat.spt_induk_manual"></span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1 w-fit" 
                                      :class="activeSurat.is_spt_manual ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'badge-green'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="activeSurat.is_spt_manual ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                                    <span x-text="activeSurat.is_spt_manual ? 'Catatan Manual' : 'Terhubung ke Database'"></span>
                                </span>
                            </div>
                        </template>
                        <template x-if="(activeSurat.is_sppd || activeSurat.jenis === 'SPPD') && !activeSurat.parent_nomor && !activeSurat.spt_induk_manual">
                            <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 text-amber-900 font-semibold">
                                    <span>⚠️ Belum terhubung ke SPT Induk</span>
                                    <span class="text-[11px] text-amber-700 font-normal">(Data legacy sebelum aturan relasi diterapkan)</span>
                                </div>
                                <button type="button" @click="startConnectSpt()" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                                    Hubungkan ke SPT
                                </button>
                            </div>
                        </template>

                        <!-- Baris Data: Nomor Surat & Tujuan / Instansi -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block mb-1">Nomor Surat</span>
                                <p class="text-sm font-mono font-bold text-primary bg-blue-50/60 p-3 rounded-2xl border border-blue-100" x-text="activeSurat.nomor_surat"></p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block mb-1">Tujuan / Instansi</span>
                                <div x-show="!isEditing">
                                    <p class="text-sm font-bold text-navy bg-blue-50/60 p-3 rounded-2xl border border-blue-100" x-text="activeSurat.tujuan"></p>
                                </div>
                                <div x-show="isEditing">
                                    <input type="text" name="tujuan" x-model="activeSurat.tujuan" class="form-input text-sm font-bold text-navy w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Uraian / Maksud Surat -->
                        <div>
                            <span class="text-xs font-semibold text-slate-400 block mb-1">Uraian / Maksud Surat</span>
                            <div x-show="!isEditing">
                                <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-200/60 leading-relaxed" 
                                   x-text="activeSurat.uraian || '-'"></p>
                            </div>
                            <div x-show="isEditing">
                                <textarea name="uraian" rows="3" x-model="activeSurat.uraian" placeholder="Uraian atau maksud surat..." class="w-full rounded-2xl border border-slate-300 p-3 focus:border-blue-500 focus:outline-none text-xs text-navy bg-white"></textarea>
                            </div>
                        </div>

                        <!-- Keterangan Tambahan (Opsional) -->
                        <!-- Mode Tampilan (Read-Only): HANYA TAMPIL JIKA MEMILIKI ISI (Highlight Border Kiri Tebal) -->
                        <div x-show="!isEditing && activeSurat.keterangan && activeSurat.keterangan.trim().length > 0" 
                             class="bg-blue-50/70 border-l-4 border-primary rounded-r-2xl p-4 shadow-sm space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-primary block">Keterangan tambahan &middot; opsional</span>
                            <p class="text-sm text-slate-700 leading-relaxed" x-text="activeSurat.keterangan"></p>
                        </div>

                        <!-- Mode Edit: Input Textarea Keterangan -->
                        <div x-show="isEditing" 
                             class="bg-blue-50/40 border-l-4 border-primary rounded-r-2xl p-4 shadow-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-primary block">Keterangan tambahan &middot; opsional</label>
                                <span class="text-[11px] font-semibold" :class="(activeSurat.keterangan || '').length > 140 ? 'text-amber-600 font-bold' : 'text-slate-400'">
                                    <span x-text="(activeSurat.keterangan || '').length"></span>/150 karakter
                                </span>
                            </div>
                            <textarea name="keterangan" rows="2" maxlength="150" x-model="activeSurat.keterangan" placeholder="Keterangan tambahan jika diperlukan (opsional, maks 150 karakter)..." class="w-full rounded-xl border border-blue-200 p-2.5 focus:border-blue-500 focus:outline-none text-xs text-navy bg-white"></textarea>
                        </div>

                    </div>

                    <!-- Tabel Pegawai yang Ditugaskan -->
                    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-navy uppercase tracking-wider">Pegawai yang Ditugaskan</h4>
                                <p x-show="isEditing" class="text-xs text-slate-500 mt-0.5">Pilih dan tambahkan personel instansi yang ditugaskan dalam surat ini.</p>
                            </div>
                            <span class="text-xs font-bold bg-blue-100 text-primary px-2.5 py-1 rounded-full" 
                                  x-text="(activeSurat.pegawais ? activeSurat.pegawais.length : 0) + ' Orang'"></span>
                        </div>

                        <!-- Mode Edit: Search & Add Pegawai -->
                        <div x-show="isEditing" class="space-y-3 pt-1">
                            <div class="relative" @click.outside="showPegawaiDropdown = false">
                                <div class="relative">
                                    <input type="text" 
                                           x-model="searchPegawai" 
                                           @focus="showPegawaiDropdown = true" 
                                           @input="showPegawaiDropdown = true"
                                           @click="showPegawaiDropdown = true"
                                           placeholder="Ketik nama / NIP / jabatan pegawai untuk menambahkan..." 
                                           class="form-input text-xs !bg-white !pl-9 !pr-8">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <button type="button" 
                                            x-show="searchPegawai && searchPegawai.length > 0" 
                                            @click="searchPegawai = ''; showPegawaiDropdown = true" 
                                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <!-- Dropdown Hasil Pencarian Pegawai -->
                                <div x-show="showPegawaiDropdown && filteredPegawais().length > 0" 
                                     x-cloak
                                     style="display: none;" 
                                     class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                    <ul class="py-1 divide-y divide-slate-100">
                                        <template x-for="p in filteredPegawais()" :key="'opt-' + p.id">
                                            <li @mousedown.prevent="addPegawai(p)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors flex items-center justify-between">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-bold text-navy" x-text="p.nama"></span>
                                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold"
                                                              :class="p.kategori_pegawai === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                                              x-text="p.kategori_pegawai || 'ASN'">
                                                        </span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 mt-0.5" x-text="'NIP. ' + (p.nip || '-') + ' • ' + (p.jabatan || '-')"></div>
                                                </div>
                                                <span class="text-xs font-bold text-primary bg-blue-50 hover:bg-primary hover:text-white px-2.5 py-1 rounded-lg border border-blue-200 transition-all flex items-center gap-1">
                                                    + Tambah
                                                </span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                                <!-- Notifikasi jika tidak ada hasil -->
                                <div x-show="showPegawaiDropdown && searchPegawai && searchPegawai.trim().length > 0 && filteredPegawais().length === 0"
                                     x-cloak
                                     style="display: none;" 
                                     class="absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 text-center text-xs text-slate-400 italic">
                                    Tidak ditemukan pegawai dengan kata kunci "<span class="font-semibold text-slate-600" x-text="searchPegawai"></span>".
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Daftar Pegawai Terpilih -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-blue-100 text-xs font-bold text-slate-400 uppercase">
                                        <th class="py-3 px-2 w-12">No</th>
                                        <th class="py-3 px-4">Nama Pegawai</th>
                                        <th class="py-3 px-4">NIP</th>
                                        <th class="py-3 px-4">Jabatan</th>
                                        <th x-show="isEditing" class="py-3 px-3 text-center w-16">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <template x-for="(pegawai, index) in (activeSurat.pegawais || [])" :key="pegawai.id">
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="py-3 px-2 font-semibold text-slate-400 text-xs" x-text="index + 1"></td>
                                            <td class="py-3 px-4 font-bold text-navy text-xs" x-text="pegawai.nama"></td>
                                            <td class="py-3 px-4 font-mono text-xs text-slate-600" x-text="pegawai.nip"></td>
                                            <td class="py-3 px-4 text-slate-600 text-xs" x-text="pegawai.jabatan"></td>
                                            <td x-show="isEditing" class="py-3 px-3 text-center">
                                                <button type="button" 
                                                        @click="removePegawai(index)" 
                                                        class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors cursor-pointer"
                                                        title="Hapus personel ini">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="!activeSurat.pegawais || activeSurat.pegawais.length === 0">
                                        <tr>
                                            <td :colspan="isEditing ? 5 : 4" class="py-6 text-center text-xs text-slate-400 italic">
                                                Belum ada pegawai yang ditugaskan pada surat ini.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN (Sidebar Ringkasan) -->
                <div class="space-y-6">
                    
                    <!-- Card Ringkasan Arsip -->
                    <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ringkasan Arsip</h4>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-blue-100/60">
                                <span class="text-slate-500 font-medium">Status</span>
                                <span :class="activeSurat.status === 'Draft' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'badge-green'" 
                                      class="px-3 py-1 rounded-full text-xs font-bold" 
                                      x-text="activeSurat.status || 'Terbit'">
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-blue-100/60">
                                <span class="text-slate-500 font-medium">Dibuat Oleh</span>
                                <span class="font-bold text-navy">Admin Kasubag</span>
                            </div>

                            <!-- Baris Relasi di Ringkasan Arsip -->
                            <template x-if="!activeSurat.is_sppd && activeSurat.jenis !== 'SPPD'">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-slate-500 font-medium">Ada SPPD Terkait</span>
                                    <span class="font-bold" 
                                          :class="(activeSurat.all_sppds && activeSurat.all_sppds.length > 0) ? 'text-primary' : (activeSurat.has_sppd ? 'text-primary' : 'text-slate-400')"
                                          x-text="(activeSurat.all_sppds && activeSurat.all_sppds.length > 0) ? ('Ya (' + activeSurat.all_sppds.length + ' Dokumen)') : (activeSurat.has_sppd ? 'Ya' : 'Tidak')">
                                    </span>
                                </div>
                            </template>
                            <template x-if="activeSurat.is_sppd || activeSurat.jenis === 'SPPD'">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-slate-500 font-medium">SPT Induk</span>
                                    <template x-if="activeSurat.parent_nomor || activeSurat.spt_induk_manual">
                                        <span class="font-bold font-mono text-xs text-primary" 
                                              x-text="activeSurat.parent_nomor || activeSurat.spt_induk_manual">
                                        </span>
                                    </template>
                                    <template x-if="!activeSurat.parent_nomor && !activeSurat.spt_induk_manual">
                                        <span class="font-bold text-xs text-amber-600">
                                            Belum Terhubung
                                        </span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Card Berkas Fisik (Hasil Scan & Google Drive) -->
                    <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Berkas Fisik (Scan)</h4>
                            <template x-if="activeSurat.google_drive_url">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Google Drive
                                </span>
                            </template>
                            <template x-if="!activeSurat.google_drive_url && activeSurat.drive_upload_status === 'failed'">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Gagal Upload
                                </span>
                            </template>
                            <template x-if="!activeSurat.google_drive_url && activeSurat.drive_upload_status !== 'failed'">
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                                    Belum Ada File
                                </span>
                            </template>
                        </div>

                        <!-- Info Berkas -->
                        <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 space-y-3">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Nama File:</span>
                                <p class="text-xs font-mono font-bold text-navy truncate" x-text="activeSurat.file_name || 'Belum ada berkas hasil scan'"></p>
                            </div>

                            <!-- Tombol Aksi Berkas -->
                            <div class="space-y-2 pt-2 border-t border-blue-50">
                                <template x-if="activeSurat.google_drive_url">
                                    <div class="space-y-2">
                                        <a :href="activeSurat.google_drive_url" target="_blank" rel="noopener noreferrer"
                                           class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            Lihat File di Google Drive
                                        </a>
                                        <button type="button" @click="openUploadModalFor(activeSurat)"
                                                class="w-full py-1.5 px-3 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            Ganti / Upload Ulang File
                                        </button>
                                    </div>
                                </template>
                                
                                <template x-if="!activeSurat.google_drive_url">
                                    <div class="space-y-2">
                                        <button type="button" @click="openUploadModalFor(activeSurat)"
                                                class="w-full py-2 px-3 bg-primary hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            Unggah Berkas Fisik Scan
                                        </button>

                                        <template x-if="activeSurat.drive_upload_status === 'failed'">
                                            <form :action="'/surat/' + activeSurat.id + '/retry-drive'" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full py-1.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl border border-amber-200 flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    Coba Upload Ulang ke Drive
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- KONDISI 1: JIKA DOKUMEN SPT (Card SPPD Terkait) -->
                    <template x-if="!activeSurat.is_sppd && activeSurat.jenis !== 'SPPD'">
                        <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPPD Terkait</h4>
                                <span class="badge-blue px-2.5 py-0.5 rounded-full text-xs font-bold" 
                                      x-text="(activeSurat.all_sppds && activeSurat.all_sppds.length > 0) ? (activeSurat.all_sppds.length + ' Dokumen') : (activeSurat.has_sppd ? '1 Dokumen' : 'Tidak Ada')">
                                </span>
                            </div>

                            <!-- Mode Edit: Pilihan Apakah Ada SPPD Terkait (Toggle Ya / Tidak Ada) -->
                            <div x-show="isEditing" class="space-y-3 pb-2 border-b border-blue-100">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Opsi SPPD Terkait</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" 
                                            @click="setHasSppd(true)" 
                                            :class="activeSurat.has_sppd ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'" 
                                            class="py-2 px-3 text-xs font-bold rounded-xl border transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Ya, Ada SPPD
                                    </button>
                                    <button type="button" 
                                            @click="setHasSppd(false)" 
                                            :class="!activeSurat.has_sppd ? 'bg-slate-700 text-white border-slate-700 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'" 
                                            class="py-2 px-3 text-xs font-bold rounded-xl border transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Tidak Ada
                                    </button>
                                </div>
                            </div>

                            <!-- Mode Tampilan (Non-Edit): Daftar SPPD Lengkap (Direct & Children) -->
                            <div x-show="!isEditing" class="space-y-3">
                                <template x-if="activeSurat.all_sppds && activeSurat.all_sppds.length > 0">
                                    <div class="space-y-3">
                                        <template x-for="(sppdItem, sIdx) in activeSurat.all_sppds" :key="'drawer-sppd-' + sIdx">
                                            <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-2">
                                                <div class="flex justify-between items-start">
                                                    <div class="w-full mr-2">
                                                        <a :href="sppdItem.url" class="text-xs font-mono font-bold text-pink-600 hover:underline block" 
                                                           x-text="sppdItem.nomor || '-'"></a>
                                                        <h5 class="text-sm font-bold text-navy mt-0.5" x-text="sppdItem.nama"></h5>
                                                    </div>
                                                    <span :class="sppdItem.is_child ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'badge-green'" 
                                                          class="text-[10px] px-2 py-0.5 rounded-full font-bold flex-shrink-0" 
                                                          x-text="sppdItem.is_child ? 'SPPD Susulan' : 'Terbit'">
                                                    </span>
                                                </div>
                                                <p class="text-xs text-slate-500" x-text="'NIP. ' + (sppdItem.nip || '-') + (sppdItem.jabatan && sppdItem.jabatan !== '-' ? ' • ' + sppdItem.jabatan : '')"></p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!activeSurat.all_sppds || activeSurat.all_sppds.length === 0">
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/60 text-center">
                                        <span class="text-xs text-slate-400 italic">Surat ini tidak memiliki SPPD terkait.</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Mode Edit: Edit SPPD Langsung SPT -->
                            <div x-show="isEditing" class="space-y-3">
                                <div class="space-y-3" x-show="activeSurat.has_sppd">
                                    <template x-for="(pegawai, pIdx) in (activeSurat.pegawais || [])" :key="'edit-sppd-' + pegawai.id">
                                        <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-2">
                                            <div class="flex justify-between items-start">
                                                <div class="w-full mr-2">
                                                    <div class="mb-1">
                                                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5">Nomor SPPD</label>
                                                        <input type="text" 
                                                               :name="'nomor_sppd[' + pegawai.id + ']'" 
                                                               x-model="pegawai.nomor_sppd" 
                                                               placeholder="090/KOMINFO-BB/SPPD/DD/..." 
                                                               class="form-input !py-1 !px-2 text-xs font-mono font-bold text-pink-600 w-full">
                                                    </div>
                                                    <h5 class="text-sm font-bold text-navy mt-0.5" x-text="pegawai.nama"></h5>
                                                </div>
                                                <span class="badge-green text-[10px] px-2 py-0.5 rounded-full font-bold flex-shrink-0">Terbit</span>
                                            </div>
                                            <p class="text-xs text-slate-500" x-text="'NIP. ' + (pegawai.nip || '-') + ' • ' + (pegawai.jabatan || '-')"></p>
                                        </div>
                                    </template>
                                    <template x-if="!activeSurat.pegawais || activeSurat.pegawais.length === 0">
                                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-700">
                                            Tambahkan pegawai yang ditugaskan di tabel sebelah kiri agar nomor SPPD masing-masing personel dapat digenerate.
                                        </div>
                                    </template>
                                </div>

                                <div x-show="!activeSurat.has_sppd" class="p-4 bg-slate-50 rounded-2xl border border-slate-200/60 text-center">
                                    <span class="text-xs text-slate-400 italic">Surat ini diatur tidak memiliki SPPD.</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- KONDISI 2: JIKA DOKUMEN SPPD (Card SPT Terkait) -->
                    <template x-if="activeSurat.is_sppd || activeSurat.jenis === 'SPPD'">
                        <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPT Terkait</h4>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" 
                                      :class="(activeSurat.parent_id || activeSurat.spt_induk_manual) ? 'badge-blue' : 'bg-amber-100 text-amber-800 border border-amber-200'"
                                      x-text="(activeSurat.parent_id || activeSurat.spt_induk_manual) ? 'SPT Induk' : 'Belum Ada Induk'">
                                </span>
                            </div>

                            <div class="space-y-3">
                                <!-- STATE A: SUDAH MEMILIKI SPT INDUK (Read-only / Permanent) -->
                                <template x-if="activeSurat.parent_id || activeSurat.spt_induk_manual">
                                    <div class="space-y-3">
                                        <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-3">
                                            <div class="flex justify-between items-start gap-2">
                                                <div>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Nomor SPT Induk</span>
                                                    <template x-if="activeSurat.parent_id">
                                                        <a :href="'/surat/' + activeSurat.parent_id" class="text-sm font-mono font-bold text-primary hover:underline block" x-text="activeSurat.parent_nomor || '-'"></a>
                                                    </template>
                                                    <template x-if="!activeSurat.parent_id">
                                                        <span class="text-sm font-mono font-bold text-slate-800 block" x-text="activeSurat.spt_induk_manual || activeSurat.parent_nomor || '-'"></span>
                                                    </template>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1"
                                                      :class="activeSurat.is_spt_manual ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'badge-green'">
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="activeSurat.is_spt_manual ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                                                    <span x-text="activeSurat.is_spt_manual ? 'Manual' : 'Terhubung'"></span>
                                                </span>
                                            </div>

                                            <template x-if="activeSurat.parent_tgl">
                                                <div class="text-xs text-slate-500 border-t border-blue-50 pt-2">
                                                    <span class="font-semibold text-slate-400">Tanggal SPT:</span>
                                                    <span class="font-bold text-slate-700 ml-1" x-text="activeSurat.parent_tgl"></span>
                                                </div>
                                            </template>

                                            <template x-if="activeSurat.parent_uraian">
                                                <div class="text-xs text-slate-500 border-t border-blue-50 pt-2">
                                                    <span class="font-semibold text-slate-400">Uraian Tugas:</span>
                                                    <p class="text-slate-700 mt-0.5 line-clamp-2" x-text="activeSurat.parent_uraian"></p>
                                                </div>
                                            </template>

                                            <template x-if="activeSurat.parent_id">
                                                <div class="pt-1">
                                                    <a :href="'/surat/' + activeSurat.parent_id" class="text-xs text-primary font-bold hover:underline inline-flex items-center gap-1">
                                                        Lihat Detail SPT Induk
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="p-3 bg-blue-50/60 border border-blue-100 rounded-xl text-[11px] text-slate-500 leading-relaxed">
                                            <span class="font-bold text-primary block mb-0.5">ℹ️ Informasi Relasi</span>
                                            Dokumen ini berjenis SPPD yang telah menginduk pada SPT di atas. Relasi bersifat permanen dan tidak dapat ditiadakan.
                                        </div>
                                    </div>
                                </template>

                                <!-- STATE B: BELUM MEMILIKI SPT INDUK (Legacy SPPD) -> TAMPILKAN FITUR HUBUNGKAN KE SPT -->
                                <template x-if="!activeSurat.parent_id && !activeSurat.spt_induk_manual">
                                    <div class="space-y-3">
                                        <!-- Kotak Peringatan Data Belum Terhubung -->
                                        <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 space-y-3">
                                            <div class="flex items-start gap-2.5 text-amber-900">
                                                <div class="w-7 h-7 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-700 mt-0.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                </div>
                                                <div>
                                                    <h5 class="text-xs font-bold text-amber-900">Belum Terhubung ke SPT Induk</h5>
                                                    <p class="text-[11px] text-amber-700 leading-relaxed mt-0.5">Dokumen ini merupakan data legacy sebelum aturan relasi diterapkan. Hubungkan dokumen ini ke nomor SPT induk yang sesuai.</p>
                                                </div>
                                            </div>

                                            <!-- Tombol Mulai Hubungkan jika belum aktif -->
                                            <div x-show="!isConnectingSpt">
                                                <button type="button" 
                                                        @click="startConnectSpt()" 
                                                        class="w-full py-2.5 px-4 bg-primary hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                                    Hubungkan ke SPT
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Panel Pencarian & Pemilihan SPT Induk (Clean Inline List Tanpa Blur/Overlay) -->
                                        <div x-show="isConnectingSpt" style="display:none;" x-transition class="bg-white border border-blue-200 rounded-2xl p-4 shadow-md space-y-3">
                                            <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-navy uppercase tracking-wider">Cari SPT di Database</span>
                                                    <span class="badge-blue text-[10px] px-2 py-0.5 rounded-full font-bold">Pilih Langsung</span>
                                                </div>
                                                <button type="button" @click="cancelConnectSpt()" class="text-xs text-slate-400 hover:text-slate-600 font-bold cursor-pointer">Batal</button>
                                            </div>

                                            <!-- Input Search Combobox -->
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-500 block">Ketik Nomor / Tanggal / Tujuan / Uraian:</label>
                                                <div class="relative">
                                                    <input type="text" 
                                                           x-model="sptSearch" 
                                                           placeholder="Contoh: 555/KOMINFO-BB/SPT-DD/003/IX/2026..." 
                                                           autocomplete="off"
                                                           class="form-input text-xs font-mono !pr-8 !bg-blue-50/40">
                                                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hasil Pencarian List SPT (Inline Clean List) -->
                                            <div class="space-y-1.5 max-h-60 overflow-y-auto pr-1 rounded-xl border border-blue-100 bg-slate-50/60 p-2">
                                                <template x-for="spt in filteredSptList()" :key="'spt-res-' + spt.id">
                                                    <div class="p-2.5 bg-white hover:bg-blue-50/80 rounded-xl border border-slate-200/80 transition-all flex items-center justify-between gap-2 shadow-sm">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-mono font-bold text-primary text-xs truncate" x-text="spt.nomor_surat"></div>
                                                            <div class="text-[11px] text-slate-500 truncate" x-text="(spt.uraian || 'Surat Tugas') + ' • ' + (spt.tgl_surat || '') + (spt.tujuan ? ' • ' + spt.tujuan : '')"></div>
                                                        </div>
                                                        <button type="button" 
                                                                @click="connectSpecificSpt(spt)" 
                                                                :disabled="connectingSptId !== null"
                                                                :class="connectingSptId === spt.id ? 'bg-blue-700 text-white' : 'btn-pill-primary'"
                                                                class="!px-3 !py-1.5 !text-[11px] font-bold flex items-center gap-1 cursor-pointer flex-shrink-0 shadow-sm transition-all">
                                                            <span x-show="connectingSptId !== spt.id">Pilih</span>
                                                            <span x-show="connectingSptId === spt.id" style="display:none;" class="flex items-center gap-1 text-white">
                                                                <svg class="animate-spin w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                                Menghubungkan...
                                                            </span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <div x-show="filteredSptList().length === 0" class="p-4 text-center text-xs text-slate-400 italic">
                                                    Tidak ditemukan dokumen SPT yang sesuai dengan kata kunci pencarian.
                                                </div>
                                            </div>

                                            <!-- Pesan Error jika Ada -->
                                            <div x-show="connectErrorMessage" style="display:none;" class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-semibold flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span x-text="connectErrorMessage"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>

            </div>

        </form>

    </div>

    <!-- MODAL KONFIRMASI HAPUS SURAT -->
    <div x-show="isDeleteModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" 
         @keydown.escape.window="cancelDelete()"
         aria-labelledby="modal-delete-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Blur -->
        <div x-show="isDeleteModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="cancelDelete()"></div>

        <!-- Modal Dialog Box -->
        <div x-show="isDeleteModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-rose-100 space-y-4 z-10 text-left overflow-hidden">
            
            <!-- State 1: Ada SPPD Anak Terkait (Tidak Boleh Dihapus) -->
            <template x-if="deleteSuratChildCount > 0">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-amber-600">
                        <div class="w-10 h-10 rounded-2xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-navy">Tidak Dapat Dihapus</h3>
                            <p class="text-xs text-slate-500">SPT ini masih memiliki SPPD turunan terkait</p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 leading-relaxed space-y-2">
                        <p>
                            Dokumen SPT <span class="font-mono font-bold text-navy" x-text="deleteSuratNomor"></span> <strong>tidak dapat dihapus</strong> karena masih menjadi induk dari <strong x-text="deleteSuratChildCount + ' dokumen SPPD'"></strong>:
                        </p>
                        <div class="bg-white/80 p-2.5 rounded-xl border border-amber-200/80 font-mono text-[11px] font-bold text-pink-700 max-h-32 overflow-y-auto space-y-1">
                            <template x-for="nomor in deleteSuratChildNomors" :key="nomor">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500 flex-shrink-0"></span>
                                    <span x-text="nomor"></span>
                                </div>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Sesuai aturan bisnis, silakan hapus atau hubungkan ulang dokumen SPPD tersebut ke SPT lain terlebih dahulu.
                        </p>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="button" @click="cancelDelete()" class="btn-pill-secondary px-5 py-2 text-xs font-bold text-slate-700 cursor-pointer">
                            Mengerti & Tutup
                        </button>
                    </div>
                </div>
            </template>

            <!-- State 2: Bebas Child / Aman Dihapus -->
            <template x-if="deleteSuratChildCount === 0">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-rose-600">
                        <div class="w-10 h-10 rounded-2xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-navy" id="modal-delete-title">Hapus Arsip Surat</h3>
                            <p class="text-xs text-slate-500">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>

                    <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-4 text-xs text-slate-600 leading-relaxed">
                        Apakah Anda yakin ingin menghapus arsip surat dengan nomor:
                        <div class="font-mono font-bold text-rose-600 text-sm mt-1" x-text="deleteSuratNomor"></div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="cancelDelete()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 cursor-pointer">
                            Batal
                        </button>
                        <form :action="'{{ url('surat') }}/' + deleteSuratId" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/25 flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </template>

        </div>
    </div>

    <!-- Global Floating Toast Notification (Root Viewport Level) -->
    <div x-show="globalToast.show" 
         x-cloak
         style="display:none;"
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-6 right-6 z-50 max-w-sm w-full bg-white rounded-2xl p-4 shadow-2xl border flex items-start gap-3 pointer-events-auto"
         :class="globalToast.type === 'error' ? 'border-rose-200 shadow-rose-900/10' : 'border-emerald-200 shadow-emerald-900/10'">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
             :class="globalToast.type === 'error' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'">
            <template x-if="globalToast.type !== 'error'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </template>
            <template x-if="globalToast.type === 'error'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </template>
        </div>
        <div class="flex-1 min-w-0">
            <h6 class="text-xs font-bold" :class="globalToast.type === 'error' ? 'text-rose-900' : 'text-emerald-900'" x-text="globalToast.title"></h6>
            <p class="text-[11px] mt-0.5" :class="globalToast.type === 'error' ? 'text-rose-700' : 'text-emerald-700'" x-text="globalToast.message"></p>
        </div>
        <button type="button" @click="globalToast.show = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL FORM: BUAT SPPD STANDALONE (TUGAS 3)               -->
    <!-- ======================================================== -->
    <div x-show="isCreateSppdOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-sppd-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div x-show="isCreateSppdOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="closeCreateSppdModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="isCreateSppdOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-pink-200/70">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-pink-600 via-rose-600 to-indigo-600 px-6 py-4 text-white flex items-center justify-between rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-lg font-bold">
                            ✈️
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Buat Surat SPPD Baru</h3>
                            <p class="text-xs text-white/80 font-medium">Wajib menghubungkan ke Surat Perintah Tugas (SPT) Induk</p>
                        </div>
                    </div>
                    <button type="button" @click="closeCreateSppdModal()" class="text-white/80 hover:text-white p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Form Body -->
                <form action="{{ route('surat.sppd.storeStandalone') }}" method="POST" enctype="multipart/form-data" @submit="submitStandaloneSppd($event)" class="p-6 space-y-5" x-data="{ selectedSppdFileName: '', selectedSppdFileSize: '', isSppdDragging: false }">
                    @csrf
                    
                    <input type="hidden" name="parent_id" :value="sppdModeSpt === 'pilih' && sppdSelectedSpt ? sppdSelectedSpt.id : ''">
                    <input type="hidden" name="spt_induk_manual" :value="sppdModeSpt === 'manual' ? sppdSptManualText.trim() : ''">

                    <!-- SEKSI 1: PILIH SPT INDUK (WAJIB) -->
                    <div class="border border-blue-200/70 rounded-2xl p-4 bg-blue-50/40 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-blue-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-blue-100 text-primary flex items-center justify-center text-[11px] font-bold">1</span>
                                    <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Tentukan SPT Induk</h4>
                                    <span class="badge-pink text-[10px] px-2 py-0.2 rounded-full font-bold">Wajib</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">SPPD wajib merujuk ke nomor SPT Induk.</p>
                            </div>

                            <!-- Mode Selector Pill -->
                            <div class="inline-flex bg-white/80 p-1 rounded-xl border border-slate-200">
                                <button type="button" 
                                        @click="sppdModeSpt = 'pilih'" 
                                        :class="sppdModeSpt === 'pilih' ? 'bg-primary text-white shadow-sm font-bold' : 'text-slate-500 font-semibold hover:text-navy'"
                                        class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer">
                                    Pilih dari Database
                                </button>
                                <button type="button" 
                                        @click="sppdModeSpt = 'manual'" 
                                        :class="sppdModeSpt === 'manual' ? 'bg-primary text-white shadow-sm font-bold' : 'text-slate-500 font-semibold hover:text-navy'"
                                        class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer">
                                    Ketik Manual
                                </button>
                            </div>
                        </div>

                        <!-- OPSI 1: PILIH DARI DATABASE (COMBOBOX) -->
                        <div x-show="sppdModeSpt === 'pilih'" class="space-y-2">
                            <div class="relative" @click.outside="sppdShowSptDropdown = false">
                                <label class="form-label text-xs">Cari SPT Induk <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" 
                                           x-model="sppdSptSearch" 
                                           @focus="sppdShowSptDropdown = true" 
                                           @input="sppdShowSptDropdown = true"
                                           placeholder="Ketik nomor SPT atau perihal..." 
                                           class="form-input !pl-9 !pr-16 text-xs font-mono font-semibold">
                                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                        <button type="button" @click="sppdShowSptDropdown = !sppdShowSptDropdown" class="text-slate-400 hover:text-primary p-1 cursor-pointer">
                                            <svg class="w-4 h-4 transition-transform" :class="sppdShowSptDropdown ? 'rotate-180 text-primary' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Dropdown List SPT -->
                                <div x-show="sppdShowSptDropdown && filteredStandaloneSptList().length > 0" 
                                     x-cloak 
                                     style="display:none;"
                                     class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                    <ul class="py-1 divide-y divide-slate-100">
                                        <template x-for="spt in filteredStandaloneSptList()" :key="'spt-standalone-' + spt.id">
                                            <li @mousedown.prevent="selectStandaloneSpt(spt)" 
                                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors flex justify-between items-center group">
                                                <div>
                                                    <span class="font-mono font-bold text-primary group-hover:underline text-xs" x-text="spt.nomor_surat"></span>
                                                    <span class="text-[10px] text-slate-400 ml-1" x-text="'• ' + spt.tgl_surat"></span>
                                                    <p class="text-[11px] text-slate-600 truncate max-w-sm" x-text="spt.uraian || 'Surat Tugas'"></p>
                                                </div>
                                                <span class="text-[10px] font-bold text-primary bg-blue-100 px-2 py-0.5 rounded-md">Pilih &crarr;</span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <div x-show="sppdShowSptDropdown && filteredStandaloneSptList().length === 0" 
                                     x-cloak 
                                     style="display:none;"
                                     class="absolute z-40 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                                    <p class="text-xs text-slate-500 italic">SPT tidak ditemukan. Gunakan tab "Ketik Manual" jika SPT dari luar sistem.</p>
                                </div>
                            </div>

                            <!-- Selected SPT Card Preview -->
                            <template x-if="sppdSelectedSpt">
                                <div class="bg-gradient-to-r from-emerald-50 to-blue-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between text-xs shadow-sm">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-navy text-xs" x-text="sppdSelectedSpt.nomor_surat"></span>
                                            <span class="badge-green text-[9px] px-1.5 py-0.2 rounded-full font-bold">Terhubung</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 mt-0.5" x-text="sppdSelectedSpt.uraian || 'Surat Tugas'"></p>
                                    </div>
                                    <button type="button" @click="clearStandaloneSpt()" class="text-xs text-rose-500 font-bold hover:underline cursor-pointer">
                                        Ganti
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- OPSI 2: KETIK MANUAL -->
                        <div x-show="sppdModeSpt === 'manual'" style="display: none;" class="space-y-2">
                            <label class="form-label text-xs">Ketik Nomor SPT Induk Manual <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   x-model="sppdSptManualText" 
                                   placeholder="Contoh: 555/KOMINFO-BB/SPT-DD/003/IX/2026 atau 555/001/SPT/SETDA/2026" 
                                   class="form-input text-xs font-mono font-semibold">
                            <p class="text-[11px] text-amber-700 font-medium">Gunakan opsi ini jika SPT fisik diterbitkan di luar sistem.</p>
                        </div>
                    </div>

                    <!-- SEKSI 2: INFORMASI SPPD -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs">Tanggal SPPD <span class="text-rose-500">*</span></label>
                            <input type="date" name="tgl_surat" x-model="sppdTanggal" @change="evaluateStandaloneSppdBackdate()" @input="evaluateStandaloneSppdBackdate()" required class="form-input text-xs font-semibold">
                            <p class="text-[11px] text-slate-400 mt-1">Backdate diizinkan &mdash; nomor menyesuaikan.</p>
                        </div>
                        <div>
                            <label class="form-label text-xs">Tujuan / Instansi <span class="text-rose-500">*</span></label>
                            <input type="text" name="tujuan" x-model="sppdTujuan" required class="form-input text-xs font-semibold" placeholder="Contoh: Kementerian Kominfo, Jakarta">
                        </div>
                    </div>

                    <!-- Notice Banner Backdate SPPD Modal -->
                    <div x-show="sppdIsBackdate && sppdBackdateNotice" x-cloak class="p-3.5 bg-amber-50/90 border border-amber-300/80 rounded-2xl text-xs text-amber-900 font-semibold flex items-start gap-2.5 shadow-sm">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="flex-1">
                            <span x-text="sppdBackdateNotice"></span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs">Uraian / Maksud Perjalanan Dinas <span class="text-rose-500">*</span></label>
                        <textarea name="uraian" x-model="sppdUraian" rows="2" required class="form-input text-xs font-medium" placeholder="Tuliskan maksud perjalanan dinas..."></textarea>
                    </div>

                    <div>
                        <label class="form-label text-xs">Keterangan Tambahan (Opsional, Maks 150 Karakter)</label>
                        <input type="text" name="keterangan" x-model="sppdKeterangan" maxlength="150" class="form-input text-xs" placeholder="Contoh: Menggunakan transportasi dinas...">
                    </div>

                    <!-- SEKSI 3: PERSONEL & PREVIEW NOMOR -->
                    <div class="space-y-2">
                        <label class="form-label text-xs">Personel yang Ditugaskan (SPPD) <span class="text-rose-500">*</span></label>
                        
                        <!-- Search & Dropdown Add Personel -->
                        <div class="relative" @click.outside="sppdShowPegawaiDropdown = false">
                            <div class="relative">
                                <input type="text" 
                                       x-model="sppdSearchPegawai" 
                                       @focus="sppdShowPegawaiDropdown = true" 
                                       @click="sppdShowPegawaiDropdown = true" 
                                       @input="sppdShowPegawaiDropdown = true" 
                                       placeholder="Ketik nama atau NIP untuk menambah personel..." 
                                       class="form-input !bg-white !pr-16 text-xs font-medium">
                                
                                <button type="button" 
                                        x-show="sppdSearchPegawai" 
                                        @click="sppdSearchPegawai = ''; sppdShowPegawaiDropdown = true" 
                                        class="absolute inset-y-0 right-8 pr-1 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer"
                                        title="Hapus pencarian">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Dropdown List Pegawai -->
                            <div x-show="sppdShowPegawaiDropdown && filteredStandalonePegawais().length > 0" 
                                 x-cloak 
                                 style="display:none;"
                                 class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                <ul class="py-1 divide-y divide-slate-100">
                                    <template x-for="p in filteredStandalonePegawais()" :key="'sppd-peg-' + p.id">
                                        <li @mousedown.prevent="addStandalonePegawai(p)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <div class="text-sm text-navy font-bold" x-text="p.nama"></div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                                          :class="p.kategori_pegawai === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                                          x-text="p.kategori_pegawai || 'ASN'">
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-0.5"><span x-text="(p.kategori_pegawai === 'P3K' ? 'No. Identitas P3K: ' : 'NIP. ') + (p.nip || '-')"></span> &bull; <span x-text="p.jabatan"></span></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-primary bg-blue-100 px-2.5 py-1 rounded-lg shrink-0 ml-2">+ Tambah</span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div x-show="sppdShowPegawaiDropdown && sppdSearchPegawai && sppdSearchPegawai.trim().length > 0 && filteredStandalonePegawais().length === 0" 
                                 x-cloak 
                                 style="display:none;" 
                                 class="absolute z-40 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                                 <p class="text-xs text-slate-500">Tidak ada pegawai yang cocok dengan kata kunci "<span class="font-bold text-navy" x-text="sppdSearchPegawai"></span>".</p>
                            </div>
                        </div>

                        <!-- Selected Personel List with Live SPPD Number Preview -->
                        <div class="border border-pink-200/60 rounded-2xl p-3 bg-slate-50/50 space-y-2 mt-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Daftar Personel & Preview Nomor SPPD:</span>
                            
                            <template x-for="(p, index) in getSelectedStandalonePegawaiObjects()" :key="'sel-sppd-p-' + p.id">
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-pink-100 shadow-sm text-xs">
                                    <input type="hidden" name="pegawai_id[]" :value="p.id">
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
                                    <div class="flex items-center gap-2">
                                        <span class="badge-pink font-mono text-xs px-2.5 py-0.5 rounded-full font-bold" x-text="getStandaloneSppdPreview(index)"></span>
                                        <button type="button" @click="removeStandalonePegawai(p.id)" class="text-slate-400 hover:text-rose-600 p-1 cursor-pointer" title="Hapus personel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="sppdSelectedPegawaiIds.length === 0" class="p-3 text-center text-xs text-rose-500 font-semibold italic">
                                Belum ada personel yang dipilih. Silakan cari dan pilih minimal 1 personel di atas.
                            </div>
                        </div>
                    </div>

                    <!-- SEKSI 4: UPLOAD BERKAS SCAN (OPSIONAL) -->
                    <div class="space-y-2">
                        <label class="form-label text-xs">Unggah Berkas Fisik Scan (Opsional)</label>
                        
                        <div class="relative border-2 border-dashed rounded-2xl p-4 text-center transition-all bg-slate-50/60"
                             :class="isSppdDragging ? 'border-primary bg-blue-50/50 scale-[0.99]' : 'border-blue-200 hover:border-blue-300'"
                             @dragover.prevent="isSppdDragging = true"
                             @dragleave.prevent="isSppdDragging = false"
                             @drop.prevent="
                                isSppdDragging = false; 
                                if ($event.dataTransfer.files.length > 0) {
                                    $refs.fileInputSppdStandalone.files = $event.dataTransfer.files;
                                    const f = $event.dataTransfer.files[0];
                                    selectedSppdFileName = f.name;
                                    selectedSppdFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                                }
                             ">
                            <input type="file" 
                                   name="file_surat" 
                                   x-ref="fileInputSppdStandalone"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   @change="
                                    if ($event.target.files.length > 0) {
                                        const f = $event.target.files[0];
                                        selectedSppdFileName = f.name;
                                        selectedSppdFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                                    } else {
                                        selectedSppdFileName = '';
                                        selectedSppdFileSize = '';
                                    }
                                   ">
                            
                            <div class="flex flex-col items-center justify-center space-y-1 pointer-events-none">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-primary flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                </div>
                                <div>
                                    <template x-if="!selectedSppdFileName">
                                        <div>
                                            <p class="text-xs font-bold text-navy">
                                                Pilih file scan atau tarik ke sini (PDF, JPG, JPEG, PNG maks 10MB)
                                            </p>
                                            <p class="text-[11px] text-slate-400">
                                                Dapat diunggah belakangan setelah surat dicap dan ditandatangani.
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="selectedSppdFileName">
                                        <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-medium inline-flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span>File dipilih: <strong class="font-mono text-emerald-900" x-text="selectedSppdFileName"></strong> (<span x-text="selectedSppdFileSize"></span>)</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="closeCreateSppdModal()" class="btn-pill-secondary px-4 py-2 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="sppdSubmitting" 
                                :class="sppdSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                                class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-1.5 shadow-md">
                            <span x-text="sppdSubmitting ? 'Menerbitkan...' : 'Terbitkan Surat SPPD'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL CEPAT: UNGGAH BERKAS FISIK SCAN KE GOOGLE DRIVE     -->
    <!-- ======================================================== -->
    <div x-show="isUploadModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" 
         @keydown.escape.window="closeUploadModal()"
         aria-labelledby="modal-upload-title" 
         role="dialog" 
         aria-modal="true">
        
        <div x-show="isUploadModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="closeUploadModal()"></div>

        <div x-show="isUploadModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-blue-100 space-y-5 z-10 text-left overflow-hidden"
             x-data="{ selectedModalFileName: '', selectedModalFileSize: '', isModalDragging: false, uploading: false }">
            
            <div class="flex items-center justify-between pb-3 border-b border-blue-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 flex items-center justify-center text-primary font-bold">
                        📁
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-navy" id="modal-upload-title">Unggah Berkas Fisik Scan</h3>
                        <p class="text-xs text-slate-500 font-mono" x-text="uploadSuratNomor"></p>
                    </div>
                </div>
                <button type="button" @click="closeUploadModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="'/surat/' + uploadSuratId + '/upload-file'" method="POST" enctype="multipart/form-data" @submit="uploading = true" class="space-y-4">
                @csrf

                <template x-if="uploadSuratExistingUrl">
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-700">File sudah ada di Google Drive</span>
                        </div>
                        <a :href="uploadSuratExistingUrl" target="_blank" class="text-primary font-bold hover:underline inline-flex items-center gap-1">
                            Buka File
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                </template>

                <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all bg-slate-50/50"
                     :class="isModalDragging ? 'border-primary bg-blue-50/50 scale-[0.99]' : 'border-blue-200 hover:border-blue-300'"
                     @dragover.prevent="isModalDragging = true"
                     @dragleave.prevent="isModalDragging = false"
                     @drop.prevent="
                        isModalDragging = false; 
                        if ($event.dataTransfer.files.length > 0) {
                            $refs.fileInputQuickModal.files = $event.dataTransfer.files;
                            const f = $event.dataTransfer.files[0];
                            selectedModalFileName = f.name;
                            selectedModalFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                        }
                     ">
                    <input type="file" 
                           name="file_surat" 
                           x-ref="fileInputQuickModal"
                           accept=".pdf,.jpg,.jpeg,.png"
                           required
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           @change="
                            if ($event.target.files.length > 0) {
                                const f = $event.target.files[0];
                                selectedModalFileName = f.name;
                                selectedModalFileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                            } else {
                                selectedModalFileName = '';
                                selectedModalFileSize = '';
                            }
                           ">
                    
                    <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-primary flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <div>
                            <template x-if="!selectedModalFileName">
                                <div>
                                    <p class="text-xs font-bold text-navy">
                                        Pilih file scan atau tarik ke sini
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Format: <strong>PDF, JPG, JPEG, PNG</strong> (Maksimal 10 MB)
                                    </p>
                                </div>
                            </template>
                            <template x-if="selectedModalFileName">
                                <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-medium inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span><strong class="font-mono text-emerald-900" x-text="selectedModalFileName"></strong> (<span x-text="selectedModalFileSize"></span>)</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500">
                    File scan yang sudah bertanda tangan dan berstempel basah akan otomatis diunggah ke Google Drive instansi.
                </p>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeUploadModal()" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-slate-600 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="uploading"
                            :class="uploading ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'"
                            class="btn-pill-primary px-5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-md">
                        <span x-show="!uploading">Unggah ke Google Drive</span>
                        <span x-show="uploading" style="display:none;" class="flex items-center gap-1.5">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function suratIndexManager() {
        return {
            viewMode: 'table', // 'table' | 'detail'
            isEditing: false,
            activeSurat: {},
            allPegawaiList: @json($allPegawais ?? []),
            sptList: @json($sptList ?? []),
            seriesSummary: @json($seriesSummary ?? null),
            baseSppdCounter: @json($nextSppdCounter ?? 1),
            searchPegawai: '',
            showPegawaiDropdown: false,
            isDeleteModalOpen: false,
            deleteSuratId: null,
            deleteSuratNomor: '',
            deleteSuratChildCount: 0,
            deleteSuratChildNomors: [],

            // State Upload Berkas Scan Cepat
            isUploadModalOpen: false,
            uploadSuratId: null,
            uploadSuratNomor: '',
            uploadSuratExistingUrl: '',
            uploadSuratFileName: '',

            openUploadModalFor(surat) {
                if (!surat) return;
                this.uploadSuratId = surat.id;
                this.uploadSuratNomor = surat.nomor_surat || 'Surat Keluar';
                this.uploadSuratExistingUrl = surat.google_drive_url || '';
                this.uploadSuratFileName = surat.file_name || '';
                this.isUploadModalOpen = true;
            },
            closeUploadModal() {
                this.isUploadModalOpen = false;
                this.uploadSuratId = null;
                this.uploadSuratNomor = '';
                this.uploadSuratExistingUrl = '';
                this.uploadSuratFileName = '';
            },

            // State Buat SPPD Standalone (Tugas 3)
            isCreateSppdOpen: false,
            sppdModeSpt: 'pilih',
            sppdSptSearch: '',
            sppdShowSptDropdown: false,
            sppdSelectedSpt: null,
            sppdSptManualText: '',
            sppdTanggal: '{{ date('Y-m-d') }}',
            sppdTujuan: '',
            sppdUraian: '',
            sppdKeterangan: '',
            sppdSelectedPegawaiIds: [],
            sppdSearchPegawai: '',
            sppdShowPegawaiDropdown: false,
            sppdSubmitting: false,
            sppdIsBackdate: false,
            sppdBackdateNotice: '',
            sppdPredecessorNomor: '',
            sppdBaseSeq: '001',
            sppdStartLetterIdx: 0,

            getLetterSuffix(index) {
                let result = '';
                let n = index;
                while (n >= 0) {
                    result = String.fromCharCode(97 + (n % 26)) + result;
                    n = Math.floor(n / 26) - 1;
                }
                return result;
            },

            openCreateSppdModal() {
                this.isCreateSppdOpen = true;
                this.sppdModeSpt = 'pilih';
                this.sppdSptSearch = '';
                this.sppdShowSptDropdown = false;
                this.sppdSelectedSpt = null;
                this.sppdSptManualText = '';
                this.sppdTanggal = new Date().toISOString().split('T')[0];
                this.sppdTujuan = '';
                this.sppdUraian = '';
                this.sppdKeterangan = '';
                this.sppdSelectedPegawaiIds = [];
                this.sppdSearchPegawai = '';
                this.sppdShowPegawaiDropdown = false;
                this.sppdSubmitting = false;
                this.evaluateStandaloneSppdBackdate();
            },
            closeCreateSppdModal() {
                this.isCreateSppdOpen = false;
            },
            filteredStandaloneSptList() {
                if (!this.sptList) return [];
                const q = this.sppdSptSearch ? this.sppdSptSearch.toLowerCase().trim() : '';
                if (!q) return this.sptList;
                return this.sptList.filter(s => 
                    (s.nomor_surat && s.nomor_surat.toLowerCase().includes(q)) ||
                    (s.uraian && s.uraian.toLowerCase().includes(q)) ||
                    (s.tujuan && s.tujuan.toLowerCase().includes(q)) ||
                    (s.tgl_surat && s.tgl_surat.includes(q))
                );
            },
            selectStandaloneSpt(spt) {
                this.sppdSelectedSpt = spt;
                this.sppdSptSearch = spt.nomor_surat;
                this.sppdShowSptDropdown = false;
                if (!this.sppdTujuan && spt.tujuan) {
                    this.sppdTujuan = spt.tujuan;
                }
                if (!this.sppdUraian && spt.uraian) {
                    this.sppdUraian = spt.uraian;
                }
                this.evaluateStandaloneSppdBackdate();
            },
            clearStandaloneSpt() {
                this.sppdSelectedSpt = null;
                this.sppdSptSearch = '';
                this.sppdShowSptDropdown = false;
                this.evaluateStandaloneSppdBackdate();
            },
            formatSppdBulanRomawi() {
                if (!this.sppdTanggal) return 'IX';
                const m = parseInt(this.sppdTanggal.split('-')[1], 10);
                const r = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
                return r[m-1] || 'IX';
            },
            formatSppdTahun() {
                if (!this.sppdTanggal) return new Date().getFullYear();
                return this.sppdTanggal.split('-')[0] || new Date().getFullYear();
            },
            evaluateStandaloneSppdBackdate() {
                if (!this.sppdTanggal) return;
                const targetDate = this.sppdTanggal;
                const targetYear = parseInt(targetDate.split('-')[0], 10);
                const bln = this.formatSppdBulanRomawi();
                const thn = this.formatSppdTahun();
                const jp = (this.sppdSelectedSpt && this.sppdSelectedSpt.jenis_penugasan) ? this.sppdSelectedSpt.jenis_penugasan : 'DD';

                if (this.seriesSummary && this.seriesSummary.sppd) {
                    const sppdData = this.seriesSummary.sppd;
                    const items = (sppdData.items || []).filter(it => it.tgl_surat.startsWith(String(targetYear)) || it.nomor_surat.endsWith('/' + targetYear));

                    let maxBaseSeq = 0;
                    let highestSurat = null;
                    if (items.length > 0) {
                        maxBaseSeq = Math.max(...items.map(it => it.base_seq));
                        const candidates = items.filter(it => it.base_seq === maxBaseSeq).sort((a, b) => b.tgl_surat.localeCompare(a.tgl_surat));
                        highestSurat = candidates[0];
                    }

                    const highestDate = highestSurat ? highestSurat.tgl_surat : null;
                    const isBack = (highestSurat !== null && highestDate !== null && targetDate < highestDate);

                    if (isBack) {
                        this.sppdIsBackdate = true;
                        const predecessors = items.filter(it => it.tgl_surat <= targetDate).sort((a, b) => {
                            if (a.tgl_surat !== b.tgl_surat) return b.tgl_surat.localeCompare(a.tgl_surat);
                            if (a.base_seq !== b.base_seq) return b.base_seq - a.base_seq;
                            return (b.letter || '').localeCompare(a.letter || '');
                        });

                        let pred = predecessors.length > 0 ? predecessors[0] : (items.slice().sort((a, b) => a.base_seq - b.base_seq)[0]);
                        const baseSeq = pred ? pred.base_seq : 1;
                        const paddedBase = String(baseSeq).padStart(3, '0');
                        this.sppdBaseSeq = paddedBase;
                        const predNomor = pred ? pred.nomor_surat : ('090/KOMINFO-BB/SPPD/DD/' + paddedBase + '/' + bln + '/' + thn);
                        this.sppdPredecessorNomor = predNomor;

                        const usedLetters = items.filter(it => it.base_seq === baseSeq).map(it => it.letter).filter(Boolean);
                        let letterIdx = 0;
                        while (usedLetters.includes(this.getLetterSuffix(letterIdx))) {
                            letterIdx++;
                        }
                        this.sppdStartLetterIdx = letterIdx;
                        const letter = this.getLetterSuffix(letterIdx);
                        const previewNomor = `090/KOMINFO-BB/SPPD/${jp}/${paddedBase}${letter}/${bln}/${thn}`;
                        this.sppdBackdateNotice = `Terdeteksi tanggal mundur (backdate) — nomor akan disisipkan sebagai anak dari nomor ${predNomor}, menjadi ${previewNomor}.`;
                    } else {
                        this.sppdIsBackdate = false;
                        this.sppdBackdateNotice = '';
                        this.sppdBaseSeq = String(maxBaseSeq + 1).padStart(3, '0');
                        this.sppdStartLetterIdx = 0;
                    }
                }

                // Sync API
                fetch(`/surat/api/check-backdate?series=SPPD&tgl_surat=${targetDate}&jenis_penugasan=${jp}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data) {
                            this.sppdIsBackdate = Boolean(data.is_backdate);
                            this.sppdBackdateNotice = data.notice || '';
                            this.sppdPredecessorNomor = data.predecessor_nomor || '';
                            if (data.base_seq) this.sppdBaseSeq = data.base_seq;
                        }
                    })
                    .catch(() => {});
            },
            getStandaloneSppdPreview(index) {
                const jp = (this.sppdSelectedSpt && this.sppdSelectedSpt.jenis_penugasan) ? this.sppdSelectedSpt.jenis_penugasan : 'DD';
                const bln = this.formatSppdBulanRomawi();
                const thn = this.formatSppdTahun();

                if (this.sppdIsBackdate) {
                    const letter = this.getLetterSuffix(this.sppdStartLetterIdx + index);
                    return `090/KOMINFO-BB/SPPD/${jp}/${this.sppdBaseSeq}${letter}/${bln}/${thn}`;
                } else {
                    const baseCounter = parseInt(this.sppdBaseSeq, 10) + index;
                    const seq = String(baseCounter).padStart(3, '0');
                    return `090/KOMINFO-BB/SPPD/${jp}/${seq}/${bln}/${thn}`;
                }
            },
            filteredStandalonePegawais() {
                if (!this.allPegawaiList || !Array.isArray(this.allPegawaiList)) return [];
                const selected = (this.sppdSelectedPegawaiIds || []).map(Number);
                const query = this.sppdSearchPegawai ? this.sppdSearchPegawai.toLowerCase().trim() : '';
                return this.allPegawaiList.filter(p => {
                    if (!p || !p.id) return false;
                    if ([1, 2, 3].includes(Number(p.id))) return false;
                    if (selected.includes(Number(p.id))) return false;
                    if (!query) return true;
                    const nama = String(p.nama || '').toLowerCase();
                    const nip = String(p.nip || '').toLowerCase();
                    const kat = String(p.kategori_pegawai || 'ASN').toLowerCase();
                    const jab = String(p.jabatan || '').toLowerCase();
                    return nama.includes(query) || nip.includes(query) || kat.includes(query) || jab.includes(query);
                });
            },
            addStandalonePegawai(p) {
                if (!p || !p.id) return;
                const numId = Number(p.id);
                if (!this.sppdSelectedPegawaiIds.map(Number).includes(numId)) {
                    this.sppdSelectedPegawaiIds.push(numId);
                }
                this.sppdSearchPegawai = '';
                this.sppdShowPegawaiDropdown = false;
            },
            removeStandalonePegawai(pId) {
                const numId = Number(pId);
                this.sppdSelectedPegawaiIds = this.sppdSelectedPegawaiIds.filter(id => Number(id) !== numId);
            },
            getSelectedStandalonePegawaiObjects() {
                if (!this.allPegawaiList || !Array.isArray(this.allPegawaiList)) return [];
                const selected = (this.sppdSelectedPegawaiIds || []).map(Number);
                return this.allPegawaiList.filter(p => selected.includes(Number(p.id)));
            },
            submitStandaloneSppd(e) {
                if (this.sppdModeSpt === 'pilih' && !this.sppdSelectedSpt) {
                    if (e) e.preventDefault();
                    this.showToast('error', 'SPT Induk Belum Dipilih', 'Silakan cari dan pilih SPT Induk dari database terlebih dahulu.');
                    return false;
                }
                if (this.sppdModeSpt === 'manual' && !this.sppdSptManualText.trim()) {
                    if (e) e.preventDefault();
                    this.showToast('error', 'Nomor SPT Manual Kosong', 'Silakan ketik nomor SPT Induk manual terlebih dahulu.');
                    return false;
                }
                if (!this.sppdTanggal) {
                    if (e) e.preventDefault();
                    this.showToast('error', 'Tanggal SPPD Belum Diisi', 'Silakan tentukan tanggal surat SPPD.');
                    return false;
                }
                if (!this.sppdTujuan || !this.sppdTujuan.trim()) {
                    if (e) e.preventDefault();
                    this.showToast('error', 'Tujuan Belum Diisi', 'Silakan isi tujuan atau instansi perjalanan dinas.');
                    return false;
                }
                if (!this.sppdUraian || !this.sppdUraian.trim()) {
                    if (e) e.preventDefault();
                    this.showToast('error', 'Uraian Belum Diisi', 'Silakan isi uraian atau maksud perjalanan dinas.');
                    return false;
                }
                if (this.sppdSelectedPegawaiIds.length === 0) {
                    if (e) e.preventDefault();
                    this.showToast('error', 'Personel Belum Dipilih', 'Silakan cari dan pilih minimal satu personel yang ditugaskan.');
                    return false;
                }
                if (this.sppdSubmitting) {
                    if (e) e.preventDefault();
                    return false;
                }
                this.sppdSubmitting = true;
            },

            // State Fitur Hubungkan ke SPT untuk Legacy SPPD
            sptSearch: '',
            isConnectingSpt: false,
            connectingSptId: null,
            connectErrorMessage: '',
            globalToast: {
                show: false,
                type: 'success',
                title: '',
                message: ''
            },

            showToast(type, title, message) {
                this.globalToast.type = type;
                this.globalToast.title = title;
                this.globalToast.message = message;
                this.globalToast.show = true;
                setTimeout(() => {
                    this.globalToast.show = false;
                }, 4000);
            },

            openDetail(surat) {
                this.activeSurat = JSON.parse(JSON.stringify(surat));
                if (!this.activeSurat.pegawais) {
                    this.activeSurat.pegawais = [];
                }
                this.activeSurat.has_sppd = this.activeSurat.has_sppd ? 1 : 0;
                
                // Tandai mana pegawai yang sudah memiliki nomor SPPD tersimpan dari DB
                this.activeSurat.pegawais.forEach(p => {
                    p.is_saved_sppd = Boolean(p.nomor_sppd && p.nomor_sppd.trim() !== '' && p.nomor_sppd !== '-');
                });

                this.isEditing = false;
                this.searchPegawai = '';
                this.showPegawaiDropdown = false;
                this.isConnectingSpt = false;
                this.connectingSptId = null;
                this.sptSearch = '';
                this.connectErrorMessage = '';
                this.viewMode = 'detail';
                this.recalculateLiveSppd();

                // Refresh baseSppdCounter terbaru dari server
                fetch('{{ route('surat.api.nextSppd') }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.next_counter) {
                            this.baseSppdCounter = data.next_counter;
                            this.recalculateLiveSppd();
                        }
                    }).catch(() => {});

                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            closeDetail() {
                this.viewMode = 'table';
                this.isEditing = false;
                this.activeSurat = {};
                this.searchPegawai = '';
                this.showPegawaiDropdown = false;
                this.isConnectingSpt = false;
                this.connectingSptId = null;
                this.sptSearch = '';
                this.connectErrorMessage = '';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            saveDetail() {
                const form = document.getElementById('detailSuratForm');
                if (form) {
                    form.submit();
                }
            },
            filteredSptList() {
                if (!this.sptList) return [];
                const q = this.sptSearch ? this.sptSearch.toLowerCase().trim() : '';
                if (!q) return this.sptList;
                return this.sptList.filter(s => 
                    (s.nomor_surat && s.nomor_surat.toLowerCase().includes(q)) ||
                    (s.uraian && s.uraian.toLowerCase().includes(q)) ||
                    (s.tujuan && s.tujuan.toLowerCase().includes(q)) ||
                    (s.tgl_surat && s.tgl_surat.includes(q))
                );
            },
            startConnectSpt() {
                this.isConnectingSpt = true;
                this.sptSearch = '';
                this.connectingSptId = null;
                this.connectErrorMessage = '';
            },
            cancelConnectSpt() {
                this.isConnectingSpt = false;
                this.connectingSptId = null;
                this.sptSearch = '';
                this.connectErrorMessage = '';
            },
            async connectSpecificSpt(spt) {
                if (!spt || this.connectingSptId !== null) return;
                this.connectingSptId = spt.id;
                this.connectErrorMessage = '';
                try {
                    const response = await fetch('/surat/' + this.activeSurat.id + '/hubungkan-spt', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            parent_id: spt.id
                        })
                    });
                    const res = await response.json();
                    if (response.ok && res.success) {
                        this.activeSurat.parent_id = res.parent.id;
                        this.activeSurat.parent_nomor = res.parent.nomor_surat;
                        this.activeSurat.parent_tgl = res.parent.tgl_surat;
                        this.activeSurat.parent_uraian = res.parent.uraian;
                        this.activeSurat.is_spt_manual = false;
                        this.isConnectingSpt = false;
                        this.connectingSptId = null;
                        this.sptSearch = '';
                        
                        // Perbarui data surat pada baris tabel
                        const rowSptLink = document.getElementById('spt-link-row-' + this.activeSurat.id);
                        if (rowSptLink) {
                            rowSptLink.innerHTML = `<div class="flex items-center gap-1.5"><a href="/surat/${res.parent.id}" class="hover:underline text-primary font-mono text-xs font-bold" title="Lihat SPT Induk (Terhubung)">${res.parent.nomor_surat}</a><span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-primary border border-blue-200" title="Terhubung ke SPT Database">Induk</span></div>`;
                        }

                        this.showToast('success', 'Berhasil Terhubung', res.message || 'SPPD berhasil dihubungkan ke SPT Induk.');
                    } else {
                        this.connectingSptId = null;
                        this.connectErrorMessage = res.message || 'Gagal menghubungkan SPT. Silakan coba lagi.';
                        this.showToast('error', 'Gagal Menghubungkan', this.connectErrorMessage);
                    }
                } catch (err) {
                    this.connectingSptId = null;
                    this.connectErrorMessage = 'Terjadi kesalahan sistem saat menghubungkan SPT.';
                    this.showToast('error', 'Kesalahan Sistem', this.connectErrorMessage);
                }
            },
            getLetterSuffix(index) {
                let result = '';
                let n = index;
                while (n >= 0) {
                    result = String.fromCharCode(97 + (n % 26)) + result;
                    n = Math.floor(n / 26) - 1;
                }
                return result;
            },
            recalculateLiveSppd() {
                if (!this.activeSurat.pegawais) return;
                let tgl = this.activeSurat.tgl_surat ? new Date(this.activeSurat.tgl_surat) : new Date();
                if (isNaN(tgl.getTime())) tgl = new Date();
                const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                const bln = romawi[tgl.getMonth()];
                const thn = tgl.getFullYear();

                let sptUrut = '001';
                let jp = this.activeSurat.jenis_penugasan || 'DD';
                let sptRef = this.activeSurat.nomor_surat;
                if (this.activeSurat.is_sppd || this.activeSurat.jenis === 'SPPD') {
                    sptRef = this.activeSurat.parent_nomor || this.activeSurat.spt_induk_manual || this.activeSurat.nomor_surat;
                }
                if (sptRef) {
                    const parts = sptRef.split('/');
                    if (parts.length >= 6 && parts[3] && /^\d+$/.test(parts[3])) {
                        sptUrut = parts[3].padStart(3, '0');
                    } else if (parts[1] && /^\d+$/.test(parts[1])) {
                        sptUrut = parts[1].padStart(3, '0');
                    } else {
                        const m = sptRef.match(/(?:SPT-[A-Z0-9_-]+\/|^555\/)(\d+)/i) || sptRef.match(/\d+/);
                        if (m) sptUrut = (m[1] || m[0]).padStart(3, '0');
                    }
                }

                // Ambil huruf yang sudah digunakan oleh pegawai yang tersimpan
                let existingLetters = [];
                this.activeSurat.pegawais.forEach(p => {
                    if (p.is_saved_sppd && p.nomor_sppd) {
                        const regex = /090\/(?:[^\/]+\/SPPD\/[^\/]+\/)?\d+([a-z]+)\//i;
                        const m = p.nomor_sppd.match(regex);
                        if (m) existingLetters.push(m[1].toLowerCase());
                    }
                });

                let letterIndex = 0;
                this.activeSurat.pegawais.forEach(p => {
                    if (!p.is_saved_sppd) {
                        let letter = this.getLetterSuffix(letterIndex);
                        while (existingLetters.includes(letter)) {
                            letterIndex++;
                            letter = this.getLetterSuffix(letterIndex);
                        }
                        p.nomor_sppd = `090/KOMINFO-BB/SPPD/${jp}/${sptUrut}${letter}/${bln}/${thn}`;
                        existingLetters.push(letter);
                        letterIndex++;
                    }
                });
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
                if (!this.allPegawaiList) return [];
                const existingIds = (this.activeSurat.pegawais || []).map(p => Number(p.id));
                const rawSearch = (this.searchPegawai || '').toLowerCase().trim();
                let results = [];
                if (!rawSearch) {
                    results = this.allPegawaiList.filter(p => {
                        if ([1, 2, 3].includes(Number(p.id))) return false;
                        return !existingIds.includes(Number(p.id));
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

                    results = this.allPegawaiList.filter(p => {
                        if ([1, 2, 3].includes(Number(p.id))) return false;
                        if (existingIds.includes(Number(p.id))) return false;

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
            addPegawai(p) {
                if (!this.activeSurat.pegawais) {
                    this.activeSurat.pegawais = [];
                }
                this.activeSurat.pegawais.push({
                    id: p.id,
                    nama: p.nama,
                    nip: p.nip,
                    jabatan: p.jabatan,
                    nomor_sppd: '',
                    is_saved_sppd: false
                });
                this.searchPegawai = '';
                this.showPegawaiDropdown = false;
                this.recalculateLiveSppd();
            },
            removePegawai(index) {
                if (this.activeSurat.pegawais) {
                    this.activeSurat.pegawais.splice(index, 1);
                    this.recalculateLiveSppd();
                }
            },
            setHasSppd(val) {
                this.activeSurat.has_sppd = val ? 1 : 0;
                if (this.activeSurat.has_sppd) {
                    this.recalculateLiveSppd();
                }
            },
            confirmDelete(id, nomor, childCount = 0, childNomors = []) {
                this.deleteSuratId = id;
                this.deleteSuratNomor = nomor;
                this.deleteSuratChildCount = childCount || 0;
                this.deleteSuratChildNomors = Array.isArray(childNomors) ? childNomors : [];
                this.isDeleteModalOpen = true;
            },
            cancelDelete() {
                this.isDeleteModalOpen = false;
                this.deleteSuratId = null;
                this.deleteSuratNomor = '';
                this.deleteSuratChildCount = 0;
                this.deleteSuratChildNomors = [];
            }
        }
    }
</script>
@endsection