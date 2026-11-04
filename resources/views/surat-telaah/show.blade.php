@extends('layouts.app')

@section('title', 'Detail Surat Telaah')

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
                    <span class="text-slate-400 font-medium">Detail Surat Telaah</span>
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
                <h2 class="text-2xl font-bold text-navy">Detail Surat Telaah</h2>
                <p class="text-xs text-slate-500 font-mono font-semibold">{{ $telaah->nomor_telaah }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->role_id == 2)
                <a href="{{ route('surat.telaah.edit', $telaah->id) }}" class="btn-pill-primary !bg-amber-600 hover:!bg-amber-700 !shadow-amber-500/25 px-4 py-2 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Telaahan
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

    <!-- Card Detail Surat Telaah -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-blue-100">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Nomor Surat Telaah</span>
                <span class="font-mono text-base font-extrabold text-primary">{{ $telaah->nomor_telaah }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Tanggal Telaahan</span>
                <span class="text-sm font-bold text-navy">{{ $telaah->tanggal_telaah ? \Carbon\Carbon::parse($telaah->tanggal_telaah)->translatedFormat('d F Y') : '-' }}</span>
            </div>
        </div>

        @if($telaah->spt)
        <div class="p-4 bg-blue-50/50 border border-blue-200 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 font-semibold block uppercase">Rujukan SPT Induk</span>
                <p class="font-mono font-bold text-xs text-navy mt-0.5">{{ $telaah->spt->nomor_surat }}</p>
                <p class="text-[11px] text-slate-600 mt-0.5">{{ $telaah->spt->uraian ?: $telaah->spt->perihal }}</p>
            </div>
            <a href="{{ route('surat.show', $telaah->spt->id) }}" class="btn-pill-secondary !px-3 !py-1 !text-xs font-bold text-primary">
                Lihat SPT &rarr;
            </a>
        </div>
        @endif

        <div class="space-y-4">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Tujuan Tugas / Instansi</span>
                <p class="text-sm font-semibold text-navy">{{ $telaah->tujuan }}</p>
            </div>

            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Uraian / Maksud Telaahan Staf</span>
                <div class="p-4 bg-white/70 border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $telaah->uraian }}
                </div>
            </div>

            @if($telaah->keterangan)
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Keterangan Tambahan</span>
                <p class="text-xs text-slate-600">{{ $telaah->keterangan }}</p>
            </div>
            @endif

            @if($telaah->link_google_drive)
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-1">Link Dokumen Google Drive</span>
                <a href="{{ $telaah->link_google_drive }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-emerald-600 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Buka Berkas di Google Drive &rarr;
                </a>
            </div>
            @endif
        </div>

        <!-- Personil yang Ditugaskan -->
        <div class="pt-6 border-t border-slate-100 space-y-3">
            <h4 class="text-xs font-bold text-navy uppercase tracking-wider">Personil yang Ditugaskan ({{ $telaah->pegawais->count() }})</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @forelse($telaah->pegawais as $index => $pegawai)
                <div class="p-3 bg-white border border-blue-200/60 rounded-xl flex items-center gap-3 shadow-xs">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-primary flex items-center justify-center text-xs font-bold">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <h5 class="text-xs font-bold text-navy">{{ $pegawai->nama }}</h5>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $pegawai->nip ?: 'Non-NIP' }} &bull; {{ $pegawai->jabatan }}</p>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Tidak ada personil yang dicatat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
