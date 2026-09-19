@extends('layouts.app')

@section('title', 'Surat Telaah')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="suratTelaahManager()">

    <!-- Header Utama & Tombol Aksi -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-navy">Surat Telaah</h2>
            <p class="text-slate-500 mt-1 text-sm">Pencatatan dan arsip telaahan staf berbasis rujukan Surat Perintah Tugas (SPT).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.telaah.exportPdf', request()->query()) }}" target="_blank" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center hover:text-primary transition-all" title="Export Rekapitulasi Surat Telaah ke PDF">
                <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Rekapitulasi PDF
            </a>
            @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
            <a href="{{ route('surat.telaah.create') }}" class="btn-pill-primary px-5 py-2.5 text-xs font-bold gap-2 shadow-lg shadow-blue-500/25 flex items-center" title="Buat Surat Telaah Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Buat Telaah Baru
            </a>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi Sukses / Error -->
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
        <form method="GET" action="{{ route('surat.telaah') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Cari Nomor / Perihal / Personil (Col span 6) -->
            <div class="md:col-span-6">
                <label class="form-label">Cari Nomor/Perihal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" placeholder="Cari nomor telaah, SPT, perihal tugas, personil..." value="{{ request('search') }}" class="form-input !pl-10">
                </div>
            </div>

            <!-- Tahun Arsip (Col span 2) -->
            <div class="md:col-span-2">
                <label class="form-label">Tahun Arsip</label>
                <select name="year" onchange="this.form.submit()" class="form-input">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears ?? [] as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
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
                <a href="{{ route('surat.telaah') }}" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Kotak Ringkasan Metrik Rekap (Style Sama Persis dengan SPT) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Total Surat Telaah -->
        <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-500/15 block">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-primary transition-colors">Total Surat Telaah</p>
                    </div>
                    <h3 class="text-3xl font-extrabold text-navy group-hover:text-primary transition-colors">{{ $totalTelaah ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-primary flex items-center justify-center font-bold text-xl group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                    📋
                </div>
            </div>
            <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium text-slate-600">Arsip Telaahan Staf</span>
                <span class="text-primary font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                    Semua Dokumen &rarr;
                </span>
            </div>
        </div>

        <!-- Card 2: Total Surat Bulan Ini -->
        <a href="{{ route('surat.telaah', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" 
           class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/15 block cursor-pointer"
           title="Klik untuk menyaring surat telaah pada bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}">
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

    <!-- TABEL REKAPITULASI SURAT TELAAH -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-blue-100/60">
            <div>
                <h3 class="text-lg font-bold text-navy">Tabel Rekapitulasi Surat Telaah</h3>
                <p class="text-xs text-slate-500">Daftar arsip penerbitan surat telaah berbasis rujukan SPT Induk & SPPD.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center text-xs font-bold text-primary bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">
                    {{ $suratTelaahs->total() }} Total Telaah
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-blue-100 text-xs font-bold text-slate-400 uppercase bg-white/40">
                        <th class="py-3.5 px-3">Tanggal Telaah</th>
                        <th class="py-3.5 px-3">Nomor Telaah</th>
                        <th class="py-3.5 px-3">Nomor & Tanggal SPT</th>
                        <th class="py-3.5 px-3">Nomor SPPD</th>
                        <th class="py-3.5 px-3">Perihal / Uraian Tugas</th>
                        <th class="py-3.5 px-3">Tujuan</th>
                        <th class="py-3.5 px-3">Pegawai yang Ditugaskan</th>
                        <th class="py-3.5 px-3">Keterangan</th>
                        <th class="py-3.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($suratTelaahs as $item)
                    @php
                        // Siapkan daftar personil untuk baris tabel dan modal
                        $personilArray = $item->pegawais->count() > 0 ? $item->pegawais : ($item->spt && $item->spt->pegawais ? $item->spt->pegawais : collect());
                        
                        // Siapkan daftar SPPD
                        $sppdList = [];
                        if ($item->sppd && !empty($item->sppd->nomor_surat)) {
                            $sppdList[] = $item->sppd->nomor_surat;
                        }
                        if ($item->spt && $item->spt->children) {
                            foreach ($item->spt->children as $childSppd) {
                                if (!empty($childSppd->nomor_surat)) {
                                    $sppdList[] = $childSppd->nomor_surat;
                                }
                            }
                        }
                        if ($item->spt && $item->spt->pegawais) {
                            foreach ($item->spt->pegawais as $peg) {
                                if (!empty($peg->pivot->nomor_sppd) && $peg->pivot->nomor_sppd !== '-') {
                                    $sppdList[] = $peg->pivot->nomor_sppd;
                                }
                            }
                        }
                        $sppdList = array_values(array_unique(array_filter($sppdList)));

                        $jsonData = [
                            'id' => $item->id,
                            'nomor_telaah' => $item->nomor_telaah,
                            'tanggal_telaah' => $item->tanggal_telaah->format('Y-m-d'),
                            'tanggal_formatted' => \Carbon\Carbon::parse($item->tanggal_telaah)->translatedFormat('d F Y'),
                            'uraian' => $item->uraian,
                            'tujuan' => $item->tujuan,
                            'keterangan' => $item->keterangan ?? '-',
                            'spt_nomor' => $item->spt ? $item->spt->nomor_surat : null,
                            'spt_tgl' => $item->spt ? \Carbon\Carbon::parse($item->spt->tgl_surat)->translatedFormat('d F Y') : null,
                            'sppd_list' => $sppdList,
                            'pegawais' => $personilArray->map(function($p) {
                                return [
                                    'nama' => $p->nama,
                                    'nip' => $p->nip,
                                    'jabatan' => $p->jabatan,
                                    'kategori' => $p->kategori_pegawai ?? 'ASN',
                                ];
                            })->values()->toArray(),
                        ];
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors border-b border-slate-100">
                        <td class="py-4 px-3 font-semibold text-slate-600 text-xs whitespace-nowrap">
                            {{ $item->tanggal_telaah->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-4 px-3 font-mono font-bold text-primary text-xs whitespace-nowrap">
                            <span class="hover:underline cursor-pointer" @click="openDetail({{ json_encode($jsonData) }})">
                                {{ $item->nomor_telaah }}
                            </span>
                        </td>
                        <td class="py-4 px-3 text-xs">
                            @if($item->spt)
                                <a href="{{ route('surat.show', $item->spt_id) }}" class="font-mono font-bold text-primary hover:underline block" title="Lihat SPT Induk">
                                    {{ $item->spt->nomor_surat }}
                                </a>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ \Carbon\Carbon::parse($item->spt->tgl_surat)->translatedFormat('d M Y') }}</span>
                            @else
                                <span class="text-slate-400 italic text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-3 font-mono font-bold text-xs">
                            @if(count($sppdList) > 0)
                                <ul class="space-y-1">
                                    @foreach($sppdList as $noSppd)
                                        <li class="text-pink-600 whitespace-nowrap inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block flex-shrink-0"></span>
                                            {{ $noSppd }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-slate-400 font-normal">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-3 text-slate-800 text-xs max-w-xs">
                            <div class="line-clamp-2 leading-relaxed" title="{{ $item->uraian }}">{{ $item->uraian }}</div>
                        </td>
                        <td class="py-4 px-3 text-slate-700 font-medium text-xs whitespace-nowrap">
                            {{ $item->tujuan }}
                        </td>
                        <td class="py-4 px-3 text-xs text-slate-700">
                            @if($personilArray->count() > 0)
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($personilArray as $pegawai)
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
                                        title="Lihat Detail Surat Telaah">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat
                                </button>

                                <!-- Tombol Hapus -->
                                @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
                                <button type="button" 
                                        @click="confirmDelete('{{ $item->id }}', '{{ $item->nomor_telaah }}')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs border border-rose-200/60 shadow-sm transition-all cursor-pointer"
                                        title="Hapus Surat Telaah">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <span class="text-4xl">📂</span>
                                <p class="text-sm font-semibold">Belum ada arsip Surat Telaah yang cocok dengan filter pencarian.</p>
                                <a href="{{ route('surat.telaah') }}" class="text-xs text-primary hover:underline font-bold">Reset Filter</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suratTelaahs->hasPages())
        <div class="pt-4 border-t border-blue-100/60">
            {{ $suratTelaahs->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL DETAIL SURAT TELAAH -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 md:p-8 shadow-2xl border border-slate-100 space-y-6 relative"
             @click.away="showDetailModal = false">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="badge-blue text-xs font-bold px-2.5 py-1 rounded-full">Detail Surat Telaah</span>
                    <h3 class="text-xl font-extrabold text-navy mt-2" x-text="detailData.nomor_telaah"></h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Diterbitkan pada ' + detailData.tanggal_formatted"></p>
                </div>
                <button type="button" @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition-colors">
                    ✕
                </button>
            </div>

            <!-- Konten Detail -->
            <div class="space-y-4 text-xs">
                <!-- Rujukan SPT & SPPD -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 font-bold block mb-1">Rujukan Surat SPT:</span>
                        <template x-if="detailData.spt_nomor">
                            <div>
                                <span class="font-mono font-bold text-primary text-xs" x-text="detailData.spt_nomor"></span>
                                <span class="text-slate-400 text-[11px] block mt-0.5" x-text="'Tanggal: ' + (detailData.spt_tgl || '-')"></span>
                            </div>
                        </template>
                        <template x-if="!detailData.spt_nomor">
                            <span class="text-slate-400 italic">Tidak terhubung ke SPT</span>
                        </template>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block mb-1">Nomor SPPD Terkait:</span>
                        <template x-if="detailData.sppd_list && detailData.sppd_list.length > 0">
                            <ul class="space-y-1">
                                <template x-for="sppd in detailData.sppd_list" :key="sppd">
                                    <li class="font-mono font-bold text-pink-600 text-xs" x-text="sppd"></li>
                                </template>
                            </ul>
                        </template>
                        <template x-if="!detailData.sppd_list || detailData.sppd_list.length === 0">
                            <span class="text-slate-400 italic">-</span>
                        </template>
                    </div>
                </div>

                <!-- Uraian / Perihal -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Uraian Tugas / Perihal Telaah:</span>
                    <p class="text-slate-700 bg-white p-3 rounded-xl border border-slate-200 leading-relaxed font-medium" x-text="detailData.uraian"></p>
                </div>

                <!-- Tujuan -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Tujuan:</span>
                    <p class="text-slate-700 font-bold" x-text="detailData.tujuan"></p>
                </div>

                <!-- Personil yang Ditugaskan -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Personil yang Ditugaskan:</span>
                    <template x-if="detailData.pegawais && detailData.pegawais.length > 0">
                        <div class="space-y-2">
                            <template x-for="p in detailData.pegawais" :key="p.nip">
                                <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-navy" x-text="p.nama"></p>
                                        <p class="text-[11px] text-slate-500" x-text="'NIP: ' + p.nip + ' | Jabatan: ' + (p.jabatan || '-')"></p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="p.kategori === 'P3K' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'"
                                          x-text="p.kategori || 'ASN'"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!detailData.pegawais || detailData.pegawais.length === 0">
                        <p class="text-slate-400 italic">Tidak ada pegawai terlampir.</p>
                    </template>
                </div>

                <!-- Keterangan -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Keterangan:</span>
                    <p class="text-slate-600" x-text="detailData.keterangan || '-'"></p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" @click="showDetailModal = false" class="btn-pill-secondary px-6 py-2 text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div x-show="showDeleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.away="showDeleteModal = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-2xl font-bold">
                🗑️
            </div>
            <div class="text-center">
                <h3 class="text-lg font-extrabold text-navy">Hapus Surat Telaah?</h3>
                <p class="text-xs text-slate-500 mt-1">Anda yakin ingin menghapus Surat Telaah dengan nomor <strong class="text-rose-600" x-text="deleteNomor"></strong> dari arsip? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form :action="'{{ url('/surat/telaah') }}/' + deleteId" method="POST" class="flex items-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="btn-pill-secondary w-1/2 py-2.5 text-xs font-bold">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition-all cursor-pointer">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function suratTelaahManager() {
    return {
        showDetailModal: false,
        showDeleteModal: false,
        detailData: {},
        deleteId: null,
        deleteNomor: '',

        openDetail(data) {
            this.detailData = data;
            this.showDetailModal = true;
        },

        confirmDelete(id, nomor) {
            this.deleteId = id;
            this.deleteNomor = nomor;
            this.showDeleteModal = true;
        }
    }
}
</script>
@endsection
