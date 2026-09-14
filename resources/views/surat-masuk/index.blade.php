@extends('layouts.app')

@section('title', 'Surat Masuk')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="suratMasukManager()">
    
    <!-- Sub-Tab Navigasi Modul Surat Menyurat -->
    <div class="flex items-center gap-2 border-b border-blue-200/60 pb-3">
        <a href="{{ route('surat.index') }}" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 bg-white text-slate-600 hover:text-primary hover:bg-blue-50/60 border border-blue-200/50 shadow-sm">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            Surat Keluar (SPT & SPPD)
        </a>
        <a href="{{ route('surat-masuk.index') }}" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 bg-primary text-white shadow-md shadow-blue-500/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            Surat Masuk
        </a>
    </div>

    <!-- Header Utama & Tombol Aksi -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-navy">Surat Masuk</h2>
            <p class="text-slate-500 mt-1 text-sm">Pencatatan dan arsip surat yang diterima dari pihak luar / instansi lain.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('surat-masuk.export-pdf', request()->query()) }}" target="_blank" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center hover:text-primary transition-all">
                <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Rekapitulasi PDF
            </a>
            @if(auth()->user()->role_id == 2)
            <a href="{{ route('surat-masuk.create') }}" class="btn-pill-primary px-5 py-2.5 text-xs font-bold gap-2 shadow-lg shadow-blue-500/25 flex items-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Surat Masuk
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
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    @php
        $currentYearVal = (string) now()->year;
        $startOfMonthVal = now()->startOfMonth()->format('Y-m-d');
        $endOfMonthVal = now()->endOfMonth()->format('Y-m-d');

        $reqYear = request('year');
        $reqStart = request('start_date');
        $reqEnd = request('end_date');

        $isMonthActive = (!$reqYear && $reqStart === $startOfMonthVal && $reqEnd === $endOfMonthVal);
        $isYearActive = ($reqYear === $currentYearVal && !$reqStart && !$reqEnd);
        $isAllActive = (!$reqYear && !$reqStart && !$reqEnd);
    @endphp

    <!-- Filter & Pencarian Form -->
    <div class="bg-card-gradient p-6 rounded-3xl border border-blue-200/50 shadow-sm">
        <form id="filterForm" method="GET" action="{{ route('surat-masuk.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Cari Nomor / Asal / Uraian (Col span 6) -->
            <div class="md:col-span-6">
                <label class="form-label">Cari Nomor Surat / Asal / Perihal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" placeholder="Ketik nomor surat, instansi pengirim, perihal..." value="{{ request('search') }}" class="form-input !pl-10">
                </div>
            </div>

            <!-- Tahun Arsip (Col span 2) -->
            <div class="md:col-span-2">
                <label class="form-label">Tahun Arsip</label>
                <select name="year" onchange="document.querySelector('input[name=start_date]').value=''; document.querySelector('input[name=end_date]').value=''; this.form.submit()" class="form-input">
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
                <input type="date" name="start_date" onchange="document.querySelector('select[name=year]').value=''; this.form.submit()" value="{{ request('start_date') }}" class="form-input">
            </div>

            <!-- Sampai Tanggal (Col span 2) -->
            <div class="md:col-span-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" onchange="document.querySelector('select[name=year]').value=''; this.form.submit()" value="{{ request('end_date') }}" class="form-input">
            </div>

            <div class="md:col-span-12 flex justify-between items-center pt-1">
                <span class="text-xs text-slate-400 font-medium">Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded text-[10px] font-mono">Enter</kbd> atau klik salah satu kartu statistik di bawah untuk filter cepat.</span>
                <a href="{{ route('surat-masuk.index') }}" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Kotak Ringkasan Metrik Surat Masuk (Quick Filter Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card Total Surat Masuk (Semua Waktu) -->
        <button type="button" 
                onclick="applyQuickFilter('all')" 
                class="w-full text-left rounded-3xl p-6 transition-all duration-300 transform hover:-translate-y-1.5 focus:outline-none cursor-pointer relative overflow-hidden group {{ $isAllActive ? 'bg-gradient-to-br from-blue-50/90 to-blue-100/50 border-2 border-primary shadow-xl shadow-blue-500/15 ring-2 ring-primary/20' : 'bg-card-gradient border border-blue-200/50 hover:border-blue-400 shadow-xl shadow-blue-900/5' }}"
                title="Klik untuk menyaring seluruh data arsip surat masuk (Semua Waktu)">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-bold uppercase tracking-wider {{ $isAllActive ? 'text-primary' : 'text-slate-400 group-hover:text-primary' }} transition-colors">Total Surat Masuk</p>
                        @if($isAllActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-primary text-white shadow-xs">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Filter Aktif
                        </span>
                        @endif
                    </div>
                    <h3 class="text-3xl font-extrabold {{ $isAllActive ? 'text-primary' : 'text-navy group-hover:text-primary' }} transition-colors">{{ $totalSuratMasuk ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl {{ $isAllActive ? 'bg-primary text-white scale-105 shadow-md shadow-blue-500/30' : 'bg-blue-50 text-primary group-hover:bg-primary group-hover:text-white' }} flex items-center justify-center font-bold text-xl transition-all duration-300 shadow-sm">
                    📥
                </div>
            </div>
            <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500 border-t {{ $isAllActive ? 'border-primary/20' : 'border-blue-100/50' }}">
                <span class="font-medium text-slate-600">Seluruh Arsip Diterima</span>
                <span class="{{ $isAllActive ? 'text-primary font-extrabold' : 'text-primary font-bold' }} flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                    Semua Waktu
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </div>
        </button>

        <!-- Card Total Bulan Ini -->
        <button type="button" 
                onclick="applyQuickFilter('month')" 
                class="w-full text-left rounded-3xl p-6 transition-all duration-300 transform hover:-translate-y-1.5 focus:outline-none cursor-pointer relative overflow-hidden group {{ $isMonthActive ? 'bg-gradient-to-br from-pink-50/90 to-rose-100/50 border-2 border-pink-500 shadow-xl shadow-pink-500/15 ring-2 ring-pink-500/20' : 'bg-card-gradient border border-blue-200/50 hover:border-pink-300 shadow-xl shadow-blue-900/5' }}"
                title="Klik untuk menyaring surat masuk bulan berjalan ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-bold uppercase tracking-wider {{ $isMonthActive ? 'text-pink-600' : 'text-slate-400 group-hover:text-pink-600' }} transition-colors">Surat Masuk Bulan Ini</p>
                        @if($isMonthActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-pink-500 text-white shadow-xs">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Filter Aktif
                        </span>
                        @endif
                    </div>
                    <h3 class="text-3xl font-extrabold {{ $isMonthActive ? 'text-pink-600' : 'text-navy group-hover:text-pink-600' }} transition-colors">{{ $totalBulanIni ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl {{ $isMonthActive ? 'bg-pink-500 text-white scale-105 shadow-md shadow-pink-500/30' : 'bg-pink-50 text-pink-500 group-hover:bg-pink-500 group-hover:text-white' }} flex items-center justify-center font-bold text-xl transition-all duration-300 shadow-sm">
                    📅
                </div>
            </div>
            <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500 border-t {{ $isMonthActive ? 'border-pink-200' : 'border-blue-100/50' }}">
                <span class="font-medium text-slate-600">Periode {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                <span class="{{ $isMonthActive ? 'text-pink-600 font-extrabold' : 'text-pink-600 font-bold' }} flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                    Bulan Ini
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </div>
        </button>

        <!-- Card Total Tahun Ini -->
        <button type="button" 
                onclick="applyQuickFilter('year')" 
                class="w-full text-left rounded-3xl p-6 transition-all duration-300 transform hover:-translate-y-1.5 focus:outline-none cursor-pointer relative overflow-hidden group {{ $isYearActive ? 'bg-gradient-to-br from-indigo-50/90 to-indigo-100/50 border-2 border-indigo-500 shadow-xl shadow-indigo-500/15 ring-2 ring-indigo-500/20' : 'bg-card-gradient border border-blue-200/50 hover:border-indigo-300 shadow-xl shadow-blue-900/5' }}"
                title="Klik untuk menyaring surat masuk tahun berjalan ini (Tahun {{ date('Y') }})">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-bold uppercase tracking-wider {{ $isYearActive ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }} transition-colors">Surat Masuk Tahun Ini</p>
                        @if($isYearActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-500 text-white shadow-xs">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Filter Aktif
                        </span>
                        @endif
                    </div>
                    <h3 class="text-3xl font-extrabold {{ $isYearActive ? 'text-indigo-600' : 'text-navy group-hover:text-indigo-600' }} transition-colors">{{ $totalTahunIni ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl {{ $isYearActive ? 'bg-indigo-500 text-white scale-105 shadow-md shadow-indigo-500/30' : 'bg-indigo-50 text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white' }} flex items-center justify-center font-bold text-xl transition-all duration-300 shadow-sm">
                    📂
                </div>
            </div>
            <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500 border-t {{ $isYearActive ? 'border-indigo-200' : 'border-blue-100/50' }}">
                <span class="font-medium text-slate-600">Tahun {{ date('Y') }}</span>
                <span class="{{ $isYearActive ? 'text-indigo-600 font-extrabold' : 'text-indigo-600 font-bold' }} flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                    Tahun Berjalan
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </div>
        </button>
    </div>

    <!-- TABEL REKAPITULASI SURAT MASUK -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-blue-100/60">
            <div>
                <h3 class="text-lg font-bold text-navy">Tabel Arsip Surat Masuk</h3>
                <p class="text-xs text-slate-500">Daftar rekaman surat masuk yang diterima dari instansi / pihak luar.</p>
            </div>
            <div>
                <span class="inline-flex items-center text-xs font-bold text-primary bg-blue-50 px-3.5 py-1.5 rounded-full border border-blue-200/60">
                    {{ $suratMasuks->total() }} Data Ditemukan
                </span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-blue-100 text-xs font-bold text-slate-400 uppercase bg-white/40">
                        <th class="py-3.5 px-3 w-12 text-center">No</th>
                        <th class="py-3.5 px-3">Nomor Surat</th>
                        <th class="py-3.5 px-3">Tanggal Surat</th>
                        <th class="py-3.5 px-3">Asal Surat</th>
                        <th class="py-3.5 px-3">Uraian / Perihal</th>
                        <th class="py-3.5 px-3">Keterangan</th>
                        <th class="py-3.5 px-3 text-center">Bukti Fisik</th>
                        <th class="py-3.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($suratMasuks as $index => $item)
                    @php
                        $jsonData = [
                            'id' => $item->id,
                            'nomor_surat' => $item->nomor_surat,
                            'tanggal_surat' => $item->tanggal_surat->format('Y-m-d'),
                            'tanggal_formatted' => $item->tanggal_surat->translatedFormat('d F Y'),
                            'asal_surat' => $item->asal_surat,
                            'uraian' => $item->uraian,
                            'keterangan' => $item->keterangan ?? '-',
                            'link_google_drive' => $item->link_google_drive,
                            'created_by' => optional($item->creator)->name ?? 'Admin Kasubag',
                            'created_at' => $item->created_at ? $item->created_at->translatedFormat('d F Y, H:i') : '-',
                        ];
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors border-b border-blue-100/40">
                        <td class="py-4 px-3 font-semibold text-slate-400 text-xs text-center">
                            {{ $suratMasuks->firstItem() + $index }}
                        </td>
                        <td class="py-4 px-3 font-mono font-bold text-primary text-xs whitespace-nowrap">
                            <button type="button" @click="openDetail({{ json_encode($jsonData) }})" class="hover:underline text-left cursor-pointer">
                                {{ $item->nomor_surat }}
                            </button>
                        </td>
                        <td class="py-4 px-3 font-semibold text-slate-600 text-xs whitespace-nowrap">
                            {{ $item->tanggal_surat->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-4 px-3 text-navy font-bold text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block flex-shrink-0"></span>
                                <span>{{ $item->asal_surat }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-3 text-slate-700 text-xs max-w-xs">
                            <div class="line-clamp-2 leading-relaxed font-medium" title="{{ $item->uraian }}">
                                {{ $item->uraian }}
                            </div>
                        </td>
                        <td class="py-4 px-3 text-xs text-slate-600 max-w-[180px]">
                            @if($item->keterangan)
                                <div class="line-clamp-2 italic" title="{{ $item->keterangan }}">{{ $item->keterangan }}</div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-3 text-center whitespace-nowrap">
                            <a href="{{ $item->link_google_drive }}" target="_blank" rel="noopener noreferrer" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs border border-emerald-200 shadow-sm transition-all cursor-pointer"
                               title="Buka bukti scan fisik surat di Google Drive">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                Lihat Bukti
                            </a>
                        </td>
                        <td class="py-4 px-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Lihat Detail (Role 1 & 2) -->
                                <button type="button" 
                                        @click="openDetail({{ json_encode($jsonData) }})" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-primary hover:bg-primary hover:text-white font-bold text-xs border border-blue-200/60 shadow-sm transition-all cursor-pointer"
                                        title="Lihat Detail Surat Masuk">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat
                                </button>

                                <!-- Tombol Edit & Hapus (Khusus Admin Kasubag / Role 2) -->
                                @if(auth()->user()->role_id == 2)
                                <a href="{{ route('surat-masuk.edit', $item->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white font-bold text-xs border border-amber-200/60 shadow-sm transition-all"
                                   title="Edit Data Surat Masuk">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit
                                </a>

                                <button type="button" 
                                        @click="confirmDelete('{{ $item->id }}', '{{ $item->nomor_surat }}')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs border border-rose-200/60 shadow-sm transition-all cursor-pointer"
                                        title="Hapus Surat Masuk">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-xs text-slate-400 italic">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <span class="text-3xl">📭</span>
                                <span>Belum ada data arsip surat masuk yang sesuai dengan filter.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-2 py-4 border-t border-blue-200/40">
            {{ $suratMasuks->links() }}
        </div>
    </div>

    <!-- MODAL DETAIL SURAT MASUK (Read-Only) -->
    <div x-show="isDetailModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" 
         @keydown.escape.window="closeDetail()"
         aria-labelledby="modal-detail-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Blur -->
        <div x-show="isDetailModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="closeDetail()"></div>

        <!-- Modal Dialog Box -->
        <div x-show="isDetailModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl border border-blue-100 space-y-6 z-10 text-left overflow-hidden">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-blue-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 text-primary flex items-center justify-center flex-shrink-0 text-lg">
                        📥
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy" id="modal-detail-title">Detail Surat Masuk</h3>
                        <p class="text-xs text-slate-500">Rincian lengkap arsip surat masuk</p>
                    </div>
                </div>
                <button type="button" @click="closeDetail()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Body Details -->
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                        <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Nomor Surat</span>
                        <p class="text-sm font-mono font-bold text-primary" x-text="activeDetail.nomor_surat || '-'"></p>
                    </div>
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                        <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Tanggal Surat</span>
                        <p class="text-sm font-bold text-navy" x-text="activeDetail.tanggal_formatted || '-'"></p>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Asal Surat / Instansi Pengirim</span>
                    <p class="text-sm font-bold text-navy" x-text="activeDetail.asal_surat || '-'"></p>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Uraian / Ringkasan Isi Surat</span>
                    <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line font-medium" x-text="activeDetail.uraian || '-'"></p>
                </div>

                <div class="bg-blue-50/40 border-l-4 border-primary rounded-r-2xl p-4 shadow-sm space-y-1" x-show="activeDetail.keterangan && activeDetail.keterangan !== '-'">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary block">Keterangan Tambahan</span>
                    <p class="text-xs text-slate-700 leading-relaxed" x-text="activeDetail.keterangan"></p>
                </div>

                <div class="bg-emerald-50/60 border border-emerald-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-emerald-800 font-bold block mb-0.5">Bukti Scan Fisik Surat</span>
                        <span class="text-slate-500 text-[11px] truncate block max-w-xs" x-text="activeDetail.link_google_drive"></span>
                    </div>
                    <a :href="activeDetail.link_google_drive" target="_blank" rel="noopener noreferrer" class="btn-pill-primary !bg-emerald-600 hover:!bg-emerald-700 px-4 py-2 text-xs font-bold gap-1.5 flex items-center shadow-md flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Buka di Google Drive &rarr;
                    </a>
                </div>

                <div class="pt-2 text-[11px] text-slate-400 flex justify-between items-center border-t border-slate-100">
                    <span>Dicatat oleh: <strong class="text-slate-600" x-text="activeDetail.created_by"></strong></span>
                    <span>Waktu input: <span x-text="activeDetail.created_at"></span></span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-2">
                @if(auth()->user()->role_id == 2)
                <a :href="'/surat/masuk/' + activeDetail.id + '/edit'" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-amber-700 border-amber-300 hover:bg-amber-50 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Data Ini
                </a>
                @endif
                <button type="button" @click="closeDetail()" class="btn-pill-primary px-5 py-2 text-xs font-bold cursor-pointer">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS SURAT MASUK (Khusus Kasubag) -->
    @if(auth()->user()->role_id == 2)
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
            
            <div class="flex items-center gap-3 text-rose-600">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-navy" id="modal-delete-title">Hapus Surat Masuk</h3>
                    <p class="text-xs text-slate-500">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-4 text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin menghapus arsip surat masuk dengan nomor:
                <div class="font-mono font-bold text-rose-600 text-sm mt-1" x-text="deleteSuratNomor"></div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="cancelDelete()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 cursor-pointer">
                    Batal
                </button>
                <form :action="'/surat/masuk/' + deleteSuratId" method="POST" class="inline">
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
    @endif

</div>

<script>
    function applyQuickFilter(type) {
        const form = document.getElementById('filterForm');
        if (!form) return;

        const yearSelect = form.querySelector('select[name="year"]');
        const startDateInput = form.querySelector('input[name="start_date"]');
        const endDateInput = form.querySelector('input[name="end_date"]');

        if (type === 'all') {
            if (yearSelect) yearSelect.value = '';
            if (startDateInput) startDateInput.value = '';
            if (endDateInput) endDateInput.value = '';
        } else if (type === 'month') {
            if (yearSelect) yearSelect.value = '';
            if (startDateInput) startDateInput.value = '{{ now()->startOfMonth()->format('Y-m-d') }}';
            if (endDateInput) endDateInput.value = '{{ now()->endOfMonth()->format('Y-m-d') }}';
        } else if (type === 'year') {
            if (yearSelect) yearSelect.value = '{{ now()->year }}';
            if (startDateInput) startDateInput.value = '';
            if (endDateInput) endDateInput.value = '';
        }

        form.submit();
    }

    function suratMasukManager() {
        return {
            isDetailModalOpen: false,
            activeDetail: {},
            isDeleteModalOpen: false,
            deleteSuratId: null,
            deleteSuratNomor: '',
            openDetail(data) {
                this.activeDetail = data;
                this.isDetailModalOpen = true;
            },
            closeDetail() {
                this.isDetailModalOpen = false;
                this.activeDetail = {};
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
