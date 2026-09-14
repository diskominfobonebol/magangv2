@extends('layouts.app')

@section('title', 'Detail Arsip Surat')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
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
        </div>
    </div>

    <!-- Main Report Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Informasi Laporan & Daftar Pegawai (2 Span) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card Informasi Detail Laporan -->
            <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
                <div class="flex items-center justify-between border-b border-blue-100 pb-4">
                    <div>
                        <span class="badge-blue px-3 py-1 rounded-full text-xs font-extrabold uppercase">
                            {{ $surat->jenisSurat->nama_jenis ?? 'SPT / SPPD' }}
                        </span>
                        <h3 class="text-lg font-bold text-navy mt-2">{{ $surat->perihal ?? 'Surat Tugas' }}</h3>
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
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($surat->pegawais as $index => $pegawai)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 px-2 font-semibold text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-navy">{{ $pegawai->nama }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $pegawai->nip }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $pegawai->jabatan }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-xs text-slate-400 italic">Tidak ada personel yang ditugaskan.</td>
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
                        <span class="text-slate-500 font-medium">Dibuat Oleh</span>
                        <span class="font-bold text-navy">Admin Kasubag</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-slate-500 font-medium">Ada SPPD Terkait</span>
                        <span class="font-bold {{ $surat->has_sppd ? 'text-primary' : 'text-slate-400' }}">
                            {{ $surat->has_sppd ? 'Ya (Ada)' : 'Tidak' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bagian SPPD Terkait (Jika Ada) -->
            @if($surat->has_sppd)
            <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPPD Terkait</h4>
                    <span class="badge-blue px-2.5 py-0.5 rounded-full text-xs font-bold">
                        {{ $surat->pegawais->count() }} Dokumen
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($surat->pegawais as $pegawai)
                    @php
                        $nomorSppd = $pegawai->pivot->nomor_sppd ?? '-';
                    @endphp
                    <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-mono font-bold text-pink-600 block">
                                    {{ $nomorSppd }}
                                </span>
                                <h5 class="text-sm font-bold text-navy mt-0.5">{{ $pegawai->nama }}</h5>
                            </div>
                            <span class="badge-green text-[10px] px-2 py-0.5 rounded-full font-bold">Terbit</span>
                        </div>
                        <p class="text-xs text-slate-500">NIP. {{ $pegawai->nip }} &bull; {{ $pegawai->jabatan }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

    </div>
</div>
@endsection