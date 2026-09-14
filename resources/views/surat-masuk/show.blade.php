@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
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
                    <li><span class="text-slate-400 font-medium">Detail</span></li>
                </ol>
            </nav>
            <h2 class="text-2xl font-bold text-navy">Detail Surat Masuk</h2>
            <p class="text-slate-500 text-xs mt-0.5">Nomor: <span class="font-mono font-bold text-primary">{{ $suratMasuk->nomor_surat }}</span></p>
        </div>
        <div class="flex items-center gap-2.5">
            @if(auth()->user()->role_id == 2)
            <a href="{{ route('surat-masuk.edit', $suratMasuk->id) }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold text-amber-700 border-amber-300 hover:bg-amber-50 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Data
            </a>
            @endif
            <a href="{{ route('surat-masuk.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-slate-900 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Card Rincian Surat Masuk -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-xs">Nomor Surat</span>
                <p class="text-base font-mono font-bold text-primary">{{ $suratMasuk->nomor_surat }}</p>
            </div>
            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-xs">Tanggal Surat</span>
                <p class="text-base font-bold text-navy">{{ $suratMasuk->tanggal_surat->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
            <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-xs">Asal Surat / Instansi Pengirim</span>
            <p class="text-sm font-bold text-navy">{{ $suratMasuk->asal_surat }}</p>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
            <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-xs">Uraian / Ringkasan Isi Surat</span>
            <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line font-medium">{{ $suratMasuk->uraian }}</p>
        </div>

        @if($suratMasuk->keterangan)
        <div class="bg-blue-50/40 border-l-4 border-primary rounded-r-2xl p-4 shadow-sm space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-primary block">Keterangan Tambahan</span>
            <p class="text-xs text-slate-700 leading-relaxed">{{ $suratMasuk->keterangan }}</p>
        </div>
        @endif

        <div class="bg-emerald-50/60 border border-emerald-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-emerald-800 font-bold block mb-0.5 text-xs">Bukti Scan Fisik Surat</span>
                <span class="text-slate-500 text-[11px] truncate block max-w-md">{{ $suratMasuk->link_google_drive }}</span>
            </div>
            <a href="{{ $suratMasuk->link_google_drive }}" target="_blank" rel="noopener noreferrer" class="btn-pill-primary !bg-emerald-600 hover:!bg-emerald-700 px-4 py-2 text-xs font-bold gap-1.5 flex items-center shadow-md flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                Buka di Google Drive &rarr;
            </a>
        </div>

        <div class="pt-2 text-[11px] text-slate-400 flex justify-between items-center border-t border-slate-100">
            <span>Dicatat oleh: <strong class="text-slate-600">{{ optional($suratMasuk->creator)->name ?? 'Admin Kasubag' }}</strong></span>
            <span>Waktu input: {{ $suratMasuk->created_at ? $suratMasuk->created_at->translatedFormat('d F Y, H:i') : '-' }}</span>
        </div>
    </div>

</div>
@endsection
