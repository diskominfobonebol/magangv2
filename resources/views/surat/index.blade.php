@extends('layouts.app')

@section('title', 'Nomor Surat')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="suratIndexManager()">
    
    <!-- TAMPILAN 1: TABEL DAN REKAPITULASI SURAT -->
    <div x-show="viewMode === 'table'" class="space-y-6">
        <!-- Header Utama & Tombol Aksi -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-navy">Nomor Surat</h2>
                <p class="text-slate-500 mt-1 text-sm">Kelola dan telusuri arsip penerbitan nomor surat instansi.</p>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $hasActiveSuratFilter = request()->filled('filter_jenis') || request()->filled('jenis') || request()->filled('search') || request()->filled('year') || request()->filled('start_date') || request()->filled('end_date');
                @endphp
                <a href="{{ route('surat.rekap.exportPdf', request()->query()) }}" target="_blank" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center hover:text-primary transition-all {{ $hasActiveSuratFilter ? 'ring-2 ring-pink-400/40 border-pink-300 bg-pink-50/40' : '' }}" title="{{ $hasActiveSuratFilter ? 'Export PDF sesuai filter yang sedang aktif' : 'Export seluruh data rekapitulasi ke PDF' }}">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Export Rekapitulasi PDF</span>
                    @if($hasActiveSuratFilter)
                        <span class="bg-pink-500 text-white text-[9px] px-2 py-0.5 rounded-full font-extrabold ml-0.5">Filter Aktif</span>
                    @endif
                </a>
                @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
                <a href="{{ route('surat.create') }}" class="btn-pill-primary px-5 py-2.5 text-xs font-bold gap-2 shadow-lg shadow-blue-500/25 flex items-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Nomor Surat Baru
                </a>
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
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
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

                <div class="md:col-span-12 flex justify-between items-center pt-1 border-t border-blue-100/50">
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
                <div class="mt-4 pt-3 border-t border-blue-100/60 flex items-center justify-between text-xs text-slate-500">
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
                <div class="mt-4 pt-3 border-t border-pink-100/60 flex items-center justify-between text-xs text-slate-500">
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
                <div class="mt-4 pt-3 border-t border-indigo-100/60 flex items-center justify-between text-xs text-slate-500">
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
                    <a href="{{ route('surat.rekap.exportPdf', request()->query()) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-pink-600 bg-white hover:bg-pink-50 px-3 py-1 rounded-full border border-slate-200 hover:border-pink-300 transition gap-1 shadow-2xs" title="Export data tabel saat ini ke PDF">
                        <svg class="w-3.5 h-3.5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>PDF</span>
                    </a>
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
                    <tbody class="divide-y divide-blue-50 text-sm">
                        @forelse($surats ?? [] as $item)
                        @php
                            // Siapkan data JSON untuk panel "Lihat"
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
                                'jenis' => optional($item->jenisSurat)->nama_jenis ?? 'SPT',
                                'pegawais' => $item->pegawais->map(function($p) use ($item) {
                                    return [
                                        'id' => $p->id,
                                        'nama' => $p->nama,
                                        'nip' => $p->nip,
                                        'jabatan' => $p->jabatan,
                                        'nomor_sppd' => $p->pivot->nomor_sppd ?? ''
                                    ];
                                })->values()->toArray()
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-4 px-3 font-semibold text-slate-600 text-xs whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->tgl_surat)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-4 px-3 font-mono font-bold text-primary text-xs whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('surat.show', $item->id) }}" class="hover:underline">
                                        {{ $item->nomor_surat }}
                                    </a>
                                    @if(($item->status ?? 'Terbit') === 'Draft')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Draft</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-3 font-mono font-bold text-xs">
                                @if($item->has_sppd && $item->pegawais->count() > 0)
                                    <ul class="space-y-1">
                                        @foreach($item->pegawais as $pegawai)
                                            @php
                                                $nomorSppd = $pegawai->pivot->nomor_sppd ?? '-';
                                            @endphp
                                            <li>
                                                <a href="{{ route('surat.show', $item->id) }}" class="text-pink-600 hover:underline inline-flex items-center gap-1.5 whitespace-nowrap" title="{{ $pegawai->nama }}">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block flex-shrink-0"></span>
                                                    {{ $nomorSppd }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif($item->has_sppd)
                                    <span class="text-pink-600 font-semibold text-xs whitespace-nowrap">SPPD Aktif</span>
                                @else
                                    <span class="text-slate-400 font-semibold px-2">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-navy font-semibold text-xs max-w-xs">
                                <div class="truncate font-bold">{{ $item->perihal }}</div>
                                @if($item->uraian)
                                    <div class="text-[11px] text-slate-500 font-normal truncate mt-0.5">{{ $item->uraian }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-slate-700 font-medium text-xs">
                                {{ $item->tujuan }}
                            </td>
                            <td class="py-4 px-3 text-xs text-slate-700">
                                @if($item->pegawais && $item->pegawais->count() > 0)
                                    <ul class="list-disc pl-4 space-y-1">
                                        @foreach($item->pegawais as $pegawai)
                                            <li><strong class="text-navy">{{ $pegawai->nama }}</strong> <span class="text-slate-500 text-[11px]">(NIP. {{ $pegawai->nip }})</span></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada pegawai</span>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-xs text-slate-600">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td class="py-4 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Lihat -->
                                    <button type="button" 
                                            @click="openDetail({{ json_encode($jsonData) }})" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-primary hover:bg-primary hover:text-white font-bold text-xs border border-blue-200/60 shadow-sm transition-all cursor-pointer"
                                            title="Lihat Detail & Edit Cepat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Lihat
                                    </button>

                                    <!-- Tombol Hapus -->
                                    @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
                                    <button type="button" 
                                            @click="confirmDelete('{{ $item->id }}', '{{ $item->nomor_surat }}')" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs border border-rose-200/60 shadow-sm transition-all cursor-pointer"
                                            title="Hapus Surat">
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

            @if(isset($surats) && $surats->hasPages())
            <div class="mt-4 pt-3 border-t border-blue-100/50">
                {{ $surats->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- TAMPILAN 2: PANEL DETAIL NOMOR SURAT (FULL PAGE PANEL SESUAI SHOW.BLADE) -->
    <div x-show="viewMode === 'detail'" x-cloak class="space-y-6" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        
        <form id="detailSuratForm" :action="'/surat/' + (activeSurat.id || '')" method="POST">
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
                        
                        <!-- Header Card: Badge Jenis Surat & Perihal / Judul Dokumen -->
                        <div class="flex items-center justify-between border-b border-blue-100 pb-4">
                            <div class="w-full mr-4">
                                <span class="badge-blue px-3 py-1 rounded-full text-xs font-extrabold uppercase" x-text="activeSurat.jenis || 'SPT'"></span>
                                
                                <!-- Mode Tampilan Judul / Perihal -->
                                <div x-show="!isEditing">
                                    <h3 class="text-lg font-bold text-navy mt-2" x-text="activeSurat.perihal || 'Surat Tugas'"></h3>
                                </div>
                                
                                <!-- Mode Edit Judul / Perihal -->
                                <div x-show="isEditing" class="mt-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Perihal / Judul Dokumen</label>
                                    <input type="text" name="perihal" x-model="activeSurat.perihal" class="form-input text-sm font-bold text-navy w-full">
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs text-slate-400 font-semibold block">Tanggal Terbit</span>
                                <span class="text-sm font-bold text-navy" x-text="activeSurat.tgl_formatted"></span>
                            </div>
                        </div>

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
                            <label class="text-[11px] font-bold uppercase tracking-wider text-primary block">Keterangan tambahan &middot; opsional</label>
                            <textarea name="keterangan" rows="2" x-model="activeSurat.keterangan" placeholder="Keterangan tambahan jika diperlukan (opsional)..." class="w-full rounded-xl border border-blue-200 p-2.5 focus:border-blue-500 focus:outline-none text-xs text-navy bg-white"></textarea>
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
                                     class="absolute z-30 w-full mt-1 bg-white border border-blue-200 rounded-2xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                                    <div class="p-2 bg-blue-50/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex justify-between items-center">
                                        <span>Pilih Pegawai dari Daftar</span>
                                        <span x-text="filteredPegawais().length + ' pegawai ditemukan'"></span>
                                    </div>
                                    <template x-for="p in filteredPegawais()" :key="'opt-' + p.id">
                                        <div @mousedown.prevent="addPegawai(p)" class="p-3 hover:bg-blue-50/70 cursor-pointer transition-colors flex items-center justify-between">
                                            <div>
                                                <div class="text-xs font-bold text-navy" x-text="p.nama"></div>
                                                <div class="text-[11px] text-slate-500" x-text="'NIP. ' + (p.nip || '-') + ' • ' + (p.jabatan || '-')"></div>
                                            </div>
                                            <span class="text-xs font-bold text-primary bg-blue-50 hover:bg-primary hover:text-white px-2.5 py-1 rounded-lg border border-blue-200 transition-all flex items-center gap-1">
                                                + Tambah
                                            </span>
                                        </div>
                                    </template>
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
                                <tbody class="divide-y divide-blue-50 text-sm">
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
                            <div class="flex justify-between items-center py-2">
                                <span class="text-slate-500 font-medium">Ada SPPD Terkait</span>
                                <span class="font-bold" 
                                      :class="activeSurat.has_sppd ? 'text-primary' : 'text-slate-400'"
                                      x-text="activeSurat.has_sppd ? 'Ya (' + (activeSurat.pegawais ? activeSurat.pegawais.length : 0) + ' Dokumen)' : 'Tidak'">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card SPPD Terkait (Per Personil) -->
                    <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPPD Terkait</h4>
                            <span class="badge-blue px-2.5 py-0.5 rounded-full text-xs font-bold" 
                                  x-text="activeSurat.has_sppd ? ((activeSurat.pegawais ? activeSurat.pegawais.length : 0) + ' Dokumen') : 'Tidak Ada'">
                            </span>
                        </div>

                        <!-- Mode Edit: Pilihan Apakah Ada SPPD Terkait -->
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

                        <!-- Daftar SPPD per personil jika has_sppd aktif -->
                        <div class="space-y-3" x-show="activeSurat.has_sppd">
                            <template x-for="(pegawai, pIdx) in (activeSurat.pegawais || [])" :key="'sppd-' + pegawai.id">
                                <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-2">
                                    <div class="flex justify-between items-start">
                                        <div class="w-full mr-2">
                                            <!-- Mode Tampilan SPPD -->
                                            <div x-show="!isEditing">
                                                <span class="text-xs font-mono font-bold text-pink-600 block" 
                                                      x-text="pegawai.nomor_sppd || '-'"></span>
                                            </div>
                                            <!-- Mode Edit SPPD -->
                                            <div x-show="isEditing" class="mb-1">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5">Nomor SPPD</label>
                                                <input type="text" 
                                                       :name="'nomor_sppd[' + pegawai.id + ']'" 
                                                       x-model="pegawai.nomor_sppd" 
                                                       placeholder="090/001/..." 
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

                        <!-- Tampilan jika tidak memiliki SPPD -->
                        <div x-show="!activeSurat.has_sppd" class="p-4 bg-slate-50 rounded-2xl border border-slate-200/60 text-center">
                            <span class="text-xs text-slate-400 italic">Surat ini tidak memiliki SPPD terkait.</span>
                        </div>
                    </div>

                </div>

            </div>

        </form>

    </div>

    <!-- MODAL KONFIRMASI HAPUS SURAT -->
    <div x-show="isDeleteModalOpen" 
         x-cloak 
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-delete-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Blur -->
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="cancelDelete()"></div>

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isDeleteModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-rose-100 space-y-4">
                
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
                    <form :action="'/surat/' + deleteSuratId" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/25 flex items-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Ya, Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    function suratIndexManager() {
        return {
            viewMode: 'table', // 'table' | 'detail'
            isEditing: false,
            activeSurat: {},
            allPegawaiList: {!! json_encode($allPegawais ?? \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])->get()) !!},
            baseSppdCounter: {{ $nextSppdCounter ?? 1 }},
            searchPegawai: '',
            showPegawaiDropdown: false,
            isDeleteModalOpen: false,
            deleteSuratId: null,
            deleteSuratNomor: '',
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
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            saveDetail() {
                const form = document.getElementById('detailSuratForm');
                if (form) {
                    form.submit();
                }
            },
            recalculateLiveSppd() {
                if (!this.activeSurat.pegawais) return;
                let tgl = this.activeSurat.tgl_surat ? new Date(this.activeSurat.tgl_surat) : new Date();
                if (isNaN(tgl.getTime())) tgl = new Date();
                const romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                const bln = romawi[tgl.getMonth()];
                const thn = tgl.getFullYear();

                let offset = 0;
                this.activeSurat.pegawais.forEach(p => {
                    if (!p.is_saved_sppd) {
                        const count = this.baseSppdCounter + offset;
                        const pad = String(count).padStart(3, '0');
                        p.nomor_sppd = `090/${pad}/${bln}/${thn}`;
                        offset++;
                    }
                });
            },
            filteredPegawais() {
                if (!this.allPegawaiList) return [];
                const existingIds = (this.activeSurat.pegawais || []).map(p => p.id);
                const query = this.searchPegawai ? this.searchPegawai.toLowerCase().trim() : '';
                return this.allPegawaiList.filter(p => {
                    if ([1, 2, 3].includes(Number(p.id))) return false;
                    if (existingIds.includes(p.id)) return false;
                    if (!query) return true;
                    return (p.nama && p.nama.toLowerCase().includes(query)) ||
                           (p.nip && p.nip.includes(query)) ||
                           (p.jabatan && p.jabatan.toLowerCase().includes(query));
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
            confirmDelete(id, nomor) {
                this.deleteSuratId = id;
                this.deleteSuratNomor = nomor;
                this.isDeleteModalOpen = true;
            },
            cancelDelete() {
                this.isDeleteModalOpen = false;
                this.deleteSuratId = null;
                this.deleteSuratNomor = '';
            }
        }
    }
</script>
@endsection