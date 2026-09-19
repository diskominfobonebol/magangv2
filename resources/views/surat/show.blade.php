@extends('layouts.app')

@section('title', 'Detail Arsip Surat')

@section('content')
@php
    $isSpt = ($surat->jenis_surat_id == 2 || is_null($surat->jenis_surat_id) || str_starts_with($surat->nomor_surat ?? '', '555/'));
    
    // 1. Direct SPPD dari SPT (Personel SPT)
    $directSppds = [];
    if ($surat->has_sppd && $surat->pegawais) {
        foreach ($surat->pegawais as $p) {
            $n = $p->pivot->nomor_sppd ?? null;
            if (!empty($n) && $n !== '-') {
                $directSppds[] = [
                    'nomor' => $n,
                    'nama' => $p->nama,
                    'nip' => $p->nip,
                    'jabatan' => $p->jabatan,
                    'tipe' => 'SPPD SPT',
                    'url' => null,
                ];
            }
        }
    }

    // 2. SPPD Susulan / Anak (Child Documents via parent_id = ID SPT ini)
    $childSppds = [];
    if ($surat->children && $surat->children->count() > 0) {
        foreach ($surat->children as $child) {
            if ($child->pegawais && $child->pegawais->count() > 0) {
                foreach ($child->pegawais as $cp) {
                    $cNomor = $cp->pivot->nomor_sppd ?: $child->nomor_surat;
                    if (!empty($cNomor) && $cNomor !== '-') {
                        $childSppds[] = [
                            'id' => $child->id,
                            'nomor' => $cNomor,
                            'nama' => $cp->nama,
                            'nip' => $cp->nip,
                            'jabatan' => $cp->jabatan,
                            'tipe' => 'SPPD Susulan',
                            'tgl' => $child->tgl_surat,
                            'uraian' => $child->uraian ?: $child->perihal ?: 'Perjalanan Dinas',
                            'url' => route('surat.show', $child->id),
                        ];
                    }
                }
            } else {
                $childSppds[] = [
                    'id' => $child->id,
                    'nomor' => $child->nomor_surat,
                    'nama' => '-',
                    'nip' => '-',
                    'jabatan' => '-',
                    'tipe' => 'SPPD Susulan',
                    'tgl' => $child->tgl_surat,
                    'uraian' => $child->uraian ?: $child->perihal ?: 'Perjalanan Dinas',
                    'url' => route('surat.show', $child->id),
                ];
            }
        }
    }

    $allTerkaitSppds = array_merge($directSppds, $childSppds);
    $totalSppdCount = count($allTerkaitSppds);
    $hasDirectSppd = count($directSppds) > 0;
    $hasChildSppd = count($childSppds) > 0;
@endphp

<div class="max-w-5xl mx-auto space-y-6" 
     x-data="suratShowManager()">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1">
                    <li><a href="{{ route('surat.index') }}" class="hover:text-primary font-semibold">Nomor Surat</a></li>
                    <li><span class="mx-2 text-slate-300">/</span></li>
                    <li><span class="text-slate-400 font-medium">Detail Nomor Surat</span></li>
                </ol>
            </nav>
            <h2 class="text-2xl font-bold text-navy">Detail Nomor Surat</h2>
            <p class="text-xs text-slate-500 font-semibold">Nomor: {{ $surat->nomor_surat }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            <a href="{{ route('surat.edit', $surat->id) }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit
            </a>

            @if(auth()->check() && (auth()->user()->role_id == 1 || auth()->user()->role_id == 2))
                @php
                    $hasChildren = $surat->children && $surat->children->count() > 0;
                    $childCount = $hasChildren ? $surat->children->count() : 0;
                    $childNomors = $hasChildren ? implode(', ', $surat->children->pluck('nomor_surat')->filter()->values()->all()) : '';
                @endphp
                @if($hasChildren)
                <button type="button" 
                        onclick="alert('Dokumen SPT {{ $surat->nomor_surat }} tidak dapat dihapus karena masih memiliki {{ $childCount }} SPPD terkait ({{ $childNomors }}). Silakan hapus atau hubungkan ulang SPPD tersebut terlebih dahulu.')" 
                        class="btn-pill-secondary px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 border-rose-200 shadow-sm cursor-pointer" 
                        title="SPT memiliki {{ $childCount }} SPPD terkait (tidak dapat dihapus)">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus
                </button>
                @else
                <form action="{{ route('surat.destroy', $surat->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat {{ $surat->nomor_surat }}? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-pill-secondary px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 border-rose-200 shadow-sm cursor-pointer">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus
                    </button>
                </form>
                @endif
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
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
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
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    <!-- Main Report Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Informasi Laporan & Daftar Pegawai (2 Span) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card Informasi Detail Laporan -->
            <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
                <div class="flex items-center justify-between border-b border-blue-100 pb-4">
                    <div>
                        <span class="badge-blue px-3 py-1 rounded-full text-xs font-extrabold uppercase">
                            {{ $surat->jenisSurat->nama_jenis ?? ($isSpt ? 'Surat Perintah Tugas (SPT)' : 'Surat Perintah Perjalanan Dinas (SPPD)') }}
                        </span>
                        <h3 class="text-lg font-bold text-navy mt-2 font-mono">{{ $surat->nomor_surat }}</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 font-semibold block">Tanggal Terbit</span>
                        <span class="text-sm font-bold text-navy">{{ \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d F Y') }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Nomor Surat</span>
                        <p class="text-sm font-mono font-bold text-primary bg-blue-50/60 p-3 rounded-2xl border border-blue-100">
                            {{ $surat->nomor_surat }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Tujuan / Instansi</span>
                        <p class="text-sm font-bold text-navy bg-blue-50/60 p-3 rounded-2xl border border-blue-100">
                            {{ $surat->tujuan }}
                        </p>
                    </div>
                </div>

                @if($surat->parent || !empty($surat->spt_induk_manual))
                <div class="p-4 bg-gradient-to-r from-blue-50/80 to-emerald-50/50 border border-blue-200/80 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-primary flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block">Surat Perintah Tugas (SPT) Induk:</span>
                            @if($surat->parent)
                                <a href="{{ route('surat.show', $surat->parent_id) }}" class="text-sm font-mono font-bold text-primary hover:underline block mt-0.5">
                                    {{ $surat->parent->nomor_surat }} &rarr;
                                </a>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $surat->parent->uraian ?? 'Surat Tugas' }}</p>
                            @else
                                <span class="text-sm font-mono font-bold text-navy block mt-0.5">{{ $surat->spt_induk_manual }}</span>
                                <p class="text-xs text-slate-500 mt-0.5">SPT di luar sistem (dicatat manual)</p>
                            @endif
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold w-fit {{ $surat->parent ? 'badge-green' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                        {{ $surat->parent ? 'Terhubung (Database)' : 'Catatan Manual' }}
                    </span>
                </div>
                @elseif(!$isSpt)
                <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50/50 border border-amber-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-amber-800 block">Perhatian: SPT Induk Belum Terhubung</span>
                            <p class="text-xs text-amber-700 mt-0.5">Dokumen SPPD legacy ini dibuat sebelum relasi wajib diterapkan sehingga belum terhubung ke SPT induk.</p>
                        </div>
                    </div>
                    <a href="{{ route('surat.edit', $surat->id) }}" class="btn-pill-primary px-3.5 py-1.5 text-xs font-bold flex items-center gap-1.5 w-fit whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        Hubungkan ke SPT
                    </a>
                </div>
                @endif

                @if($surat->uraian)
                <div>
                    <span class="text-xs font-semibold text-slate-400 block mb-1">Uraian / Maksud Surat</span>
                    <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-200/60 leading-relaxed">
                        {{ $surat->uraian }}
                    </p>
                </div>
                @endif

                @if($surat->keterangan)
                <div>
                    <span class="text-xs font-semibold text-slate-400 block mb-1">Keterangan Tambahan</span>
                    <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-200/60 leading-relaxed">
                        {{ $surat->keterangan }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Card Daftar Pegawai yang Ditugaskan -->
            <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-navy uppercase tracking-wider">Pegawai yang Ditugaskan</h4>
                    <span class="text-xs font-bold bg-blue-100 text-primary px-2.5 py-1 rounded-full">
                        {{ $surat->pegawais->count() }} Orang
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-blue-100 text-xs font-bold text-slate-400 uppercase">
                                <th class="py-3 px-2 w-12">No</th>
                                <th class="py-3 px-4">Nama Pegawai</th>
                                <th class="py-3 px-4">NIP</th>
                                <th class="py-3 px-4">Jabatan</th>
                                @if(!$isSpt || $hasDirectSppd)
                                <th class="py-3 px-4">Nomor SPPD</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($surat->pegawais as $index => $pegawai)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 px-2 font-semibold text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-navy">{{ $pegawai->nama }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $pegawai->nip }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $pegawai->jabatan }}</td>
                                @if(!$isSpt || $hasDirectSppd)
                                <td class="py-3 px-4">
                                    @if(!empty($pegawai->pivot->nomor_sppd) && $pegawai->pivot->nomor_sppd !== '-')
                                        <span class="badge-pink font-mono text-xs px-2.5 py-0.5 rounded-full font-bold">
                                            {{ $pegawai->pivot->nomor_sppd }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-xs text-slate-400 italic">Tidak ada personel yang ditugaskan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Ringkasan & SPPD Terkait (1 Span) -->
        <div class="space-y-6">
            
            <!-- Ringkasan Laporan -->
            <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ringkasan Arsip</h4>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-blue-100/60">
                        <span class="text-slate-500 font-medium">Status</span>
                        @if(($surat->status ?? 'Terbit') === 'Draft')
                            <span class="bg-amber-100 text-amber-800 border border-amber-200 px-3 py-1 rounded-full text-xs font-bold">Draft</span>
                        @else
                            <span class="badge-green px-3 py-1 rounded-full text-xs font-bold">Terbit</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-blue-100/60">
                        <span class="text-slate-500 font-medium">Jenis Dokumen</span>
                        <span class="font-bold text-navy">{{ $isSpt ? 'Surat Perintah Tugas' : 'Surat Perjalanan Dinas' }}</span>
                    </div>
                    @if(!$isSpt)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-slate-500 font-medium">SPT Induk</span>
                        @if($surat->parent)
                            <a href="{{ route('surat.show', $surat->parent_id) }}" class="font-bold text-primary font-mono text-xs hover:underline">
                                {{ $surat->parent->nomor_surat }}
                            </a>
                        @elseif(!empty($surat->spt_induk_manual))
                            <span class="font-bold text-slate-700 font-mono text-xs">{{ $surat->spt_induk_manual }}</span>
                        @else
                            <span class="bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-bold">Belum Terhubung</span>
                        @endif
                    </div>
                    @else
                    <div class="flex justify-between items-center py-2">
                        <span class="text-slate-500 font-medium">Total SPPD Terkait</span>
                        <span class="font-bold {{ $totalSppdCount > 0 ? 'text-primary' : 'text-slate-400' }}">
                            {{ $totalSppdCount }} Dokumen
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card Berkas Scan Fisik & Google Drive -->
            <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Berkas Fisik (Scan)</h4>
                    @if(!empty($surat->google_drive_url))
                        <span class="badge-green px-2.5 py-0.5 rounded-full text-xs font-bold inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Google Drive
                        </span>
                    @elseif(!empty($surat->file_path) && $surat->drive_upload_status === 'failed')
                        <span class="bg-rose-100 text-rose-800 border border-rose-200 px-2.5 py-0.5 rounded-full text-xs font-bold">
                            Gagal Sync Drive
                        </span>
                    @else
                        <span class="bg-slate-100 text-slate-500 px-2.5 py-0.5 rounded-full text-xs font-bold">
                            Belum Ada File
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @if(!empty($surat->google_drive_url) || !empty($surat->file_path))
                        <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-xl bg-blue-50 text-primary flex items-center justify-center font-bold text-base flex-shrink-0">
                                    📄
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-navy truncate" title="{{ $surat->file_name ?? $surat->nomor_surat }}">
                                        {{ $surat->file_name ?? ($surat->nomor_surat . '.pdf') }}
                                    </h5>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Status: 
                                        @if($surat->drive_upload_status === 'success')
                                            <span class="text-emerald-600 font-semibold">Tersinkronisasi ke Google Drive</span>
                                        @elseif($surat->drive_upload_status === 'failed')
                                            <span class="text-rose-600 font-semibold">Gagal sinkronisasi Drive</span>
                                        @else
                                            <span class="text-amber-600 font-semibold">Pending / Tersimpan Lokal</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-blue-50">
                                @if(!empty($surat->google_drive_url))
                                    <a href="{{ $surat->google_drive_url }}" target="_blank" rel="noopener noreferrer" 
                                       class="btn-pill-primary px-3.5 py-1.5 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        Lihat di Drive
                                    </a>
                                @endif

                                <button type="button" @click="isUploadModalOpen = true" 
                                        class="btn-pill-secondary px-3 py-1.5 text-xs font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Ganti File
                                </button>

                                @if($surat->drive_upload_status === 'failed' && !empty($surat->file_path))
                                    <form action="{{ route('surat.retryDrive', $surat->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 text-[11px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-200 transition-colors cursor-pointer">
                                            Sync Ulang Drive
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/60 text-center space-y-3">
                            <p class="text-xs text-slate-500">
                                Berkas scan surat fisik yang sudah ditandatangani & dicap belum diunggah.
                            </p>
                            <button type="button" @click="isUploadModalOpen = true" 
                                    class="btn-pill-primary px-4 py-2 text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Upload File Surat
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bagian Relasi Terkait -->
            @if(!$isSpt)
            <!-- SPPD: Tampilkan Card SPT Terkait -->
            <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPT Terkait</h4>
                    <span class="badge-blue px-2.5 py-0.5 rounded-full text-xs font-bold">
                        SPT Induk
                    </span>
                </div>

                <div class="space-y-3">
                    @if($surat->parent || !empty($surat->spt_induk_manual))
                    <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Nomor SPT Induk</span>
                                @if($surat->parent)
                                    <a href="{{ route('surat.show', $surat->parent_id) }}" class="text-sm font-mono font-bold text-primary hover:underline block">
                                        {{ $surat->parent->nomor_surat }}
                                    </a>
                                @elseif(!empty($surat->spt_induk_manual))
                                    <span class="text-sm font-mono font-bold text-slate-800 block">{{ $surat->spt_induk_manual }}</span>
                                @endif
                            </div>
                            @if($surat->parent)
                                <span class="badge-green px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Terhubung
                                </span>
                            @elseif(!empty($surat->spt_induk_manual))
                                <span class="bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Manual
                                </span>
                            @endif
                        </div>

                        @if($surat->parent && $surat->parent->tgl_surat)
                        <div class="text-xs text-slate-500 border-t border-blue-50 pt-2">
                            <span class="font-semibold text-slate-400">Tanggal SPT:</span>
                            <span class="font-bold text-slate-700 ml-1">{{ \Carbon\Carbon::parse($surat->parent->tgl_surat)->translatedFormat('d F Y') }}</span>
                        </div>
                        @endif

                        @if($surat->parent && ($surat->parent->uraian || $surat->parent->perihal))
                        <div class="text-xs text-slate-500 border-t border-blue-50 pt-2">
                            <span class="font-semibold text-slate-400">Uraian Tugas:</span>
                            <p class="text-slate-700 mt-0.5 line-clamp-2">{{ $surat->parent->uraian ?: $surat->parent->perihal }}</p>
                        </div>
                        @endif

                        @if($surat->parent_id)
                        <div class="pt-1">
                            <a href="{{ route('surat.show', $surat->parent_id) }}" class="text-xs text-primary font-bold hover:underline inline-flex items-center gap-1">
                                Lihat Detail SPT Induk
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="p-3 bg-blue-50/60 border border-blue-100 rounded-xl text-[11px] text-slate-500 leading-relaxed">
                        <span class="font-bold text-primary block mb-0.5">ℹ️ Informasi Relasi</span>
                        Dokumen ini berjenis SPPD yang wajib menginduk pada SPT di atas. Relasi bersifat permanen dan tidak dapat ditiadakan.
                    </div>
                    @else
                    <!-- State Legacy: Belum Terhubung -->
                    <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Nomor SPT Induk</span>
                                <span class="text-sm font-mono font-bold text-slate-400 block">-</span>
                            </div>
                            <span class="bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Belum Terhubung
                            </span>
                        </div>
                        <p class="text-xs text-amber-900 leading-relaxed">Dokumen SPPD legacy ini dibuat sebelum aturan relasi wajib diterapkan, sehingga belum memiliki SPT induk.</p>
                        <a href="{{ route('surat.edit', $surat->id) }}" class="btn-pill-primary w-full text-center py-2 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            Hubungkan ke SPT Sekarang
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <!-- SPT: Tampilkan Panel SPPD Terkait & Tombol + Tambah SPPD -->
            <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPPD Terkait</h4>
                        <span class="badge-blue px-2.5 py-0.5 rounded-full text-xs font-bold">
                            {{ $totalSppdCount }} Dokumen
                        </span>
                    </div>

                    <!-- Tombol + Tambah SPPD -->
                    <button type="button" 
                            @click="isTambahSppdOpen = true" 
                            class="btn-pill-primary px-3 py-1.5 text-xs font-bold flex items-center gap-1 shadow-sm cursor-pointer whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        + Tambah SPPD
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- 1. SPPD Langsung dari SPT Parent (Personil yang Ditugaskan) -->
                    @if($hasDirectSppd)
                        @foreach($directSppds as $sppd)
                            <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-2 hover:border-pink-200 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs font-mono font-bold text-pink-600 block">
                                            {{ $sppd['nomor'] }}
                                        </span>
                                        <h5 class="text-sm font-bold text-navy mt-0.5">{{ $sppd['nama'] }}</h5>
                                    </div>
                                    <span class="badge-pink text-[10px] px-2 py-0.5 rounded-full font-bold">SPPD SPT</span>
                                </div>
                                <p class="text-xs text-slate-500">NIP. {{ $sppd['nip'] ?: '-' }} &bull; {{ $sppd['jabatan'] ?: '-' }}</p>
                            </div>
                        @endforeach
                    @endif

                    <!-- 2. SPPD Susulan / Anak (Child Documents via parent_id) -->
                    @if($hasChildSppd)
                        @foreach($childSppds as $sppd)
                        <div class="bg-white/90 border border-pink-200/80 rounded-2xl p-4 shadow-sm space-y-2 hover:border-pink-300 transition-colors">
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <a href="{{ $sppd['url'] }}" class="text-xs font-mono font-bold text-pink-600 hover:underline block">
                                        {{ $sppd['nomor'] }} &rarr;
                                    </a>
                                    <h5 class="text-sm font-bold text-navy mt-0.5">{{ $sppd['nama'] }}</h5>
                                </div>
                                <span class="bg-pink-100 text-pink-700 border border-pink-200 text-[10px] px-2 py-0.5 rounded-full font-bold">SPPD Susulan</span>
                            </div>
                            @if(!empty($sppd['nip']) && $sppd['nip'] !== '-')
                                <p class="text-xs text-slate-500">NIP. {{ $sppd['nip'] }} &bull; {{ $sppd['jabatan'] }}</p>
                            @endif
                            <div class="text-[11px] text-slate-500 pt-1.5 border-t border-slate-100 flex items-center justify-between">
                                <span class="line-clamp-1 text-slate-600 font-medium">{{ $sppd['uraian'] }}</span>
                                <span class="font-semibold text-slate-400 whitespace-nowrap ml-2">{{ \Carbon\Carbon::parse($sppd['tgl'])->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    <!-- 3. State Kosong jika belum ada SPPD sama sekali -->
                    @if(!$hasDirectSppd && !$hasChildSppd)
                    <div class="bg-blue-50/50 border border-dashed border-blue-200 rounded-2xl p-5 text-center space-y-2.5">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-primary flex items-center justify-center mx-auto text-base font-bold">
                            ✈️
                        </div>
                        <div>
                            <p class="text-xs font-bold text-navy">Belum Ada SPPD Terkait</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">SPT ini belum memiliki surat perjalanan dinas (SPPD).</p>
                        </div>
                        <button type="button" 
                                @click="isTambahSppdOpen = true" 
                                class="btn-pill-primary px-3 py-1.5 text-xs font-bold mx-auto flex items-center gap-1 shadow-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            + Tambah SPPD Sekarang
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL FORM: TAMBAH SPPD KE SPT INI (TUGAS 2)             -->
    <!-- ======================================================== -->
    @if($isSpt)
    <div x-show="isTambahSppdOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div x-show="isTambahSppdOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="isTambahSppdOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="isTambahSppdOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-blue-200/70">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-pink-600 px-6 py-4 text-white flex items-center justify-between rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-lg font-bold">
                            ✈️
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Tambah SPPD Baru ke SPT</h3>
                            <p class="text-xs text-white/80 font-medium">SPT Induk otomatis terhubung: <span class="font-mono font-bold text-white">{{ $surat->nomor_surat }}</span></p>
                        </div>
                    </div>
                    <button type="button" @click="isTambahSppdOpen = false" class="text-white/80 hover:text-white p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('surat.sppd.storeChild', $surat->id) }}" method="POST" enctype="multipart/form-data" @submit="submitTambahSppd($event)" class="p-6 space-y-5">
                    @csrf

                    <!-- Alert Info SPT Induk Terkunci -->
                    <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="font-medium text-slate-600">SPT Induk Terhubung:</span>
                            <span class="font-mono font-bold text-primary">{{ $surat->nomor_surat }}</span>
                        </div>
                        <span class="badge-green text-[10px] px-2 py-0.5 rounded-full font-bold">Terkunci Otomatis</span>
                    </div>

                    <!-- Row 1: Tanggal SPPD & Tujuan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs">Tanggal SPPD <span class="text-rose-500">*</span></label>
                            <input type="date" name="tgl_surat" x-model="tglSurat" @change="evaluateSppdBackdate()" @input="evaluateSppdBackdate()" required class="form-input text-xs font-semibold">
                            <p class="text-[11px] text-slate-400 mt-1">Backdate diizinkan &mdash; nomor menyesuaikan.</p>
                        </div>
                        <div>
                            <label class="form-label text-xs">Tujuan / Instansi <span class="text-rose-500">*</span></label>
                            <input type="text" name="tujuan" x-model="tujuan" required class="form-input text-xs font-semibold" placeholder="Contoh: Kementerian Kominfo, Jakarta">
                        </div>
                    </div>

                    <!-- Notice Banner Backdate Tambah SPPD Modal -->
                    <div x-show="sppdIsBackdate && sppdBackdateNotice" x-cloak class="p-3.5 bg-amber-50/90 border border-amber-300/80 rounded-2xl text-xs text-amber-900 font-semibold flex items-start gap-2.5 shadow-sm">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="flex-1">
                            <span x-text="sppdBackdateNotice"></span>
                        </div>
                    </div>

                    <!-- Row 2: Uraian SPPD -->
                    <div>
                        <label class="form-label text-xs">Uraian / Maksud Perjalanan Dinas <span class="text-rose-500">*</span></label>
                        <textarea name="uraian" x-model="uraian" rows="2" required class="form-input text-xs font-medium" placeholder="Tuliskan maksud perjalanan dinas..."></textarea>
                    </div>

                    <!-- Row 3: Keterangan Tambahan -->
                    <div>
                        <label class="form-label text-xs">Keterangan Tambahan (Opsional, Maks 150 Karakter)</label>
                        <input type="text" name="keterangan" x-model="keterangan" maxlength="150" class="form-input text-xs" placeholder="Contoh: Menggunakan transportasi darat/udara...">
                    </div>

                    <!-- Row 4: Upload Scan File SPPD (Opsional) -->
                    <div class="p-3.5 border border-blue-200/60 rounded-2xl bg-slate-50/50 space-y-2">
                        <label class="form-label text-xs block">Unggah Berkas Scan SPPD Fisik (Opsional, PDF/Gambar Maks 10MB)</label>
                        <input type="file" name="file_surat" accept=".pdf,.jpg,.jpeg,.png" class="form-input text-xs bg-white">
                        <p class="text-[11px] text-slate-400">File akan otomatis tersimpan dan tersinkronisasi ke Google Drive.</p>
                    </div>

                    <!-- Row 5: Pemilihan Personel yang Ditugaskan -->
                    <div class="space-y-2">
                        <label class="form-label text-xs">Personel yang Ditugaskan (SPPD) <span class="text-rose-500">*</span></label>
                        
                        <!-- Search & Dropdown Add Personel -->
                        <div class="relative" @click.outside="showPegawaiDropdown = false">
                            <div class="relative">
                                <input type="text" 
                                       x-model="searchPegawai" 
                                       @focus="showPegawaiDropdown = true" 
                                       @click="showPegawaiDropdown = true"
                                       @input="showPegawaiDropdown = true"
                                       placeholder="Ketik nama atau NIP untuk menambah personel..." 
                                       class="form-input !pr-16 text-xs font-medium">
                                
                                <button type="button" 
                                        x-show="searchPegawai" 
                                        @click="searchPegawai = ''; showPegawaiDropdown = true" 
                                        class="absolute inset-y-0 right-8 pr-1 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer"
                                        title="Hapus pencarian">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Dropdown List Pegawai -->
                            <div x-show="showPegawaiDropdown && filteredPegawais().length > 0" 
                                 x-cloak 
                                 style="display:none;"
                                 class="absolute z-40 w-full mt-1 bg-white border border-blue-200/80 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                <ul class="py-1 divide-y divide-slate-100">
                                    <template x-for="p in filteredPegawais()" :key="'peg-add-' + p.id">
                                        <li @mousedown.prevent="addPegawai(p)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors flex items-center justify-between">
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
                            <div x-show="showPegawaiDropdown && searchPegawai && searchPegawai.trim().length > 0 && filteredPegawais().length === 0" 
                                 x-cloak 
                                 style="display:none;" 
                                 class="absolute z-40 w-full mt-1 bg-white border border-amber-200/80 rounded-2xl shadow-xl p-4 text-center">
                                <p class="text-xs text-slate-500">Tidak ada pegawai yang cocok dengan kata kunci "<span class="font-bold text-navy" x-text="searchPegawai"></span>".</p>
                            </div>
                        </div>

                        <!-- Selected Personel List with Live SPPD Number Preview -->
                        <div class="border border-blue-200/60 rounded-2xl p-3 bg-slate-50/50 space-y-2 mt-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Daftar Personel & Preview Nomor SPPD:</span>
                            
                            <template x-for="(p, index) in getSelectedPegawaiObjects()" :key="'sel-peg-' + p.id">
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-blue-100 shadow-sm text-xs">
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
                                        <span class="badge-pink font-mono text-xs px-2.5 py-0.5 rounded-full font-bold" x-text="getSppdPreview(index)"></span>
                                        <button type="button" @click="removePegawai(p.id)" class="text-slate-400 hover:text-rose-600 p-1 cursor-pointer" title="Hapus personel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="selectedPegawaiIds.length === 0" class="p-3 text-center text-xs text-rose-500 font-semibold italic">
                                Belum ada personel yang dipilih. Silakan cari dan pilih minimal 1 personel di atas.
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isTambahSppdOpen = false" class="btn-pill-secondary px-4 py-2 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="submittingSppd" 
                                :class="submittingSppd ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                                class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-1.5 shadow-md">
                            <span x-text="submittingSppd ? 'Menerbitkan...' : 'Terbitkan SPPD Baru'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

    <!-- MODAL DEDIKASI: UPLOAD / GANTI BERKAS SCAN SURAT -->
    <div x-show="isUploadModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title-upload" 
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div x-show="isUploadModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="isUploadModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="isUploadModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-blue-200/70 overflow-hidden"
                 x-data="{ fileName: '', fileSize: '', uploading: false, isDrag: false }">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-primary px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-lg font-bold">
                            📁
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Unggah Berkas Fisik Scan</h3>
                            <p class="text-xs text-white/80 font-mono">{{ $surat->nomor_surat }}</p>
                        </div>
                    </div>
                    <button type="button" @click="isUploadModalOpen = false" class="text-white/80 hover:text-white p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Form Upload -->
                <form action="{{ route('surat.uploadFile', $surat->id) }}" method="POST" enctype="multipart/form-data" @submit="uploading = true" class="p-6 space-y-4">
                    @csrf

                    <p class="text-xs text-slate-500">
                        Unggah file scan/foto dokumen surat yang sudah ditandatangani dan dicap basah. File akan otomatis diunggah ke Google Drive instansi.
                    </p>

                    <div class="border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer relative bg-slate-50/60"
                         :class="isDrag ? 'border-primary bg-blue-50/60 ring-2 ring-blue-400/30' : 'border-blue-200 hover:border-primary/60'"
                         @dragover.prevent="isDrag = true"
                         @dragleave.prevent="isDrag = false"
                         @drop.prevent="isDrag = false; if ($event.dataTransfer.files.length > 0) { $refs.modalFileInput.files = $event.dataTransfer.files; const f = $event.dataTransfer.files[0]; fileName = f.name; fileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB'; }">
                        
                        <input type="file" 
                               name="file_surat" 
                               id="modal_file_surat" 
                               x-ref="modalFileInput"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                               @change="if ($event.target.files.length > 0) { const f = $event.target.files[0]; fileName = f.name; fileSize = (f.size / 1024 / 1024).toFixed(2) + ' MB'; } else { fileName = ''; fileSize = ''; }">

                        <div x-show="!fileName" class="space-y-2 pointer-events-none">
                            <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 text-primary flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-navy">Pilih file atau seret ke area ini</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Format: PDF, JPG, JPEG, PNG (Maks 10 MB)</p>
                            </div>
                        </div>

                        <div x-show="fileName" x-cloak class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-xl pointer-events-auto">
                            <div class="flex items-center gap-2.5 text-left truncate">
                                <span class="text-lg">📄</span>
                                <div>
                                    <h5 class="text-xs font-bold text-navy truncate max-w-[200px]" x-text="fileName"></h5>
                                    <p class="text-[10px] text-slate-500 font-semibold" x-text="fileSize"></p>
                                </div>
                            </div>
                            <button type="button" @click="$refs.modalFileInput.value = ''; fileName = ''; fileSize = '';" class="text-xs font-bold text-rose-600 hover:text-rose-800 p-1">
                                Batal
                            </button>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isUploadModalOpen = false" class="btn-pill-secondary px-4 py-2 text-xs font-bold">
                            Tutup
                        </button>
                        <button type="submit" 
                                :disabled="uploading || !fileName" 
                                :class="(uploading || !fileName) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                                class="btn-pill-primary px-6 py-2.5 text-xs font-bold flex items-center gap-1.5 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span x-text="uploading ? 'Mengunggah ke Drive...' : 'Simpan & Upload ke Drive'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function suratShowManager() {
        return {
            isTambahSppdOpen: false,
            isUploadModalOpen: false,
            tglSurat: @json(date('Y-m-d')),
            tujuan: @json($surat->tujuan ?? ''),
            uraian: @json($surat->uraian ?: $surat->perihal ?: 'Perjalanan Dinas'),
            keterangan: '',
            selectedPegawaiIds: [],
            searchPegawai: '',
            showPegawaiDropdown: false,
            allPegawais: @json($allPegawais ?? []),
            sptUrut: @json($sptUrut ?? '001'),
            seriesSummary: @json($seriesSummary ?? null),
            sppdIsBackdate: @json((isset($sppdEval['is_backdate']) && $sppdEval['is_backdate']) ? true : false),
            sppdBackdateNotice: @json($sppdEval['notice'] ?? ''),
            sppdPredecessorNomor: @json($sppdEval['predecessor_nomor'] ?? ''),
            sppdBaseSeq: @json($sppdEval['base_seq'] ?? '001'),
            sppdStartLetterIdx: 0,
            nextSppdCounter: @json($nextSppdCounter ?? 1),
            submittingSppd: false,

            getLetterSuffix(index) {
                let result = '';
                let n = index;
                while (n >= 0) {
                    result = String.fromCharCode(97 + (n % 26)) + result;
                    n = Math.floor(n / 26) - 1;
                }
                return result;
            },
            formatBulanRomawi() {
                if (!this.tglSurat) return 'IX';
                const parts = this.tglSurat.split('-');
                if (parts.length < 2) return 'IX';
                const m = parseInt(parts[1], 10);
                const r = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
                return r[m-1] || 'IX';
            },
            formatTahun() {
                if (!this.tglSurat) return @json(date('Y'));
                return this.tglSurat.split('-')[0] || @json(date('Y'));
            },
            evaluateSppdBackdate() {
                if (!this.tglSurat) return;
                const targetDate = this.tglSurat;
                const targetYear = parseInt(targetDate.split('-')[0], 10);
                const bln = this.formatBulanRomawi();
                const thn = this.formatTahun();
                const jp = @json($surat->jenis_penugasan ?: 'DD');

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
            getSppdPreview(index) {
                const bln = this.formatBulanRomawi();
                const thn = this.formatTahun();
                const jp = @json($surat->jenis_penugasan ?: 'DD');
                
                if (this.sppdIsBackdate) {
                    const letter = this.getLetterSuffix(this.sppdStartLetterIdx + index);
                    return `090/KOMINFO-BB/SPPD/${jp}/${this.sppdBaseSeq}${letter}/${bln}/${thn}`;
                } else {
                    const baseCounter = parseInt(this.sppdBaseSeq, 10) + index;
                    const seq = String(baseCounter).padStart(3, '0');
                    return `090/KOMINFO-BB/SPPD/${jp}/${seq}/${bln}/${thn}`;
                }
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
                if (!this.allPegawais || !Array.isArray(this.allPegawais)) return [];
                const selected = (this.selectedPegawaiIds || []).map(Number);
                const rawSearch = (this.searchPegawai || '').toLowerCase().trim();
                let results = [];
                if (!rawSearch) {
                    results = this.allPegawais.filter(p => {
                        if (!p || !p.id) return false;
                        if ([1, 2, 3].includes(Number(p.id))) return false;
                        return !selected.includes(Number(p.id));
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

                    results = this.allPegawais.filter(p => {
                        if (!p || !p.id) return false;
                        if ([1, 2, 3].includes(Number(p.id))) return false;
                        if (selected.includes(Number(p.id))) return false;

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
                if (!p || !p.id) return;
                const numId = Number(p.id);
                if (!this.selectedPegawaiIds.map(Number).includes(numId)) {
                    this.selectedPegawaiIds.push(numId);
                }
                this.searchPegawai = '';
                this.showPegawaiDropdown = false;
            },
            removePegawai(pId) {
                const numId = Number(pId);
                this.selectedPegawaiIds = this.selectedPegawaiIds.filter(id => Number(id) !== numId);
            },
            getSelectedPegawaiObjects() {
                if (!this.allPegawais || !Array.isArray(this.allPegawais)) return [];
                const selected = (this.selectedPegawaiIds || []).map(Number);
                return this.allPegawais.filter(p => selected.includes(Number(p.id)));
            },
            submitTambahSppd(e) {
                if (this.selectedPegawaiIds.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal satu personel yang ditugaskan untuk SPPD.');
                    return false;
                }
                if (this.submittingSppd) {
                    e.preventDefault();
                    return false;
                }
                this.submittingSppd = true;
            },
            init() {
                if (this.tglSurat) {
                    this.evaluateSppdBackdate();
                }
            }
        };
    }
</script>
@endsection