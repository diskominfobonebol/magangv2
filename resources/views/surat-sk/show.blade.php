@extends('layouts.app')

@section('title', 'Detail Surat Keputusan (SK)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.keluar') }}" class="hover:text-primary transition-colors font-semibold">Surat Keluar</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Detail Surat SK</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Surat Keluar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Detail Surat Keputusan (SK)</h2>
                <p class="text-xs text-slate-500 font-mono font-semibold">{{ $sk->nomor_sk }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->role_id == 2)
                <a href="{{ route('surat.sk.edit', $sk->id) }}" class="btn-pill-primary !bg-amber-600 hover:!bg-amber-700 !shadow-amber-500/25 px-4 py-2 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Surat SK
                </a>
            @endif
            @if($sk->file_sk)
                <a href="{{ route('surat.sk.download', $sk->id) }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh Berkas
                </a>
            @endif
            <a href="{{ route('surat.keluar') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Card Detail Surat SK -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-blue-100">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Nomor Surat SK</span>
                <span class="font-mono text-base font-extrabold text-primary">{{ $sk->nomor_sk }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Tanggal SK Ditetapkan</span>
                <span class="text-sm font-bold text-navy">{{ $sk->tanggal_sk ? \Carbon\Carbon::parse($sk->tanggal_sk)->translatedFormat('d F Y') : '-' }}</span>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Hal / Pokok Penetapan Surat Keputusan</span>
                <div class="p-4 bg-white/70 border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $sk->tentang }}
                </div>
            </div>

            @if($sk->keterangan)
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Keterangan Tambahan</span>
                <p class="text-xs text-slate-600">{{ $sk->keterangan }}</p>
            </div>
            @endif

            @if($sk->link_google_drive)
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Link Dokumen Google Drive</span>
                <a href="{{ $sk->link_google_drive }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-emerald-600 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Buka Berkas di Google Drive &rarr;
                </a>
            </div>
            @endif

            @if($sk->file_sk)
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Berkas Lampiran Fisik</span>
                <div class="p-3 bg-white border border-blue-200/60 rounded-xl flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📎</span>
                        <span class="text-xs font-medium text-navy">{{ basename($sk->file_sk) }}</span>
                    </div>
                    <a href="{{ route('surat.sk.download', $sk->id) }}" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
                        Unduh Berkas &darr;
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
