@extends('layouts.app')

@section('title', 'Nomor Surat')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumb & Header Panel -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1">
                    <li>
                        <a href="{{ route('surat.index') }}" class="hover:text-primary font-semibold text-slate-500">
                            Nomor Surat
                        </a>
                    </li>
                    <li><span class="mx-2 text-slate-300">/</span></li>
                    <li><span class="text-slate-400 font-medium">Detail Nomor Surat</span></li>
                </ol>
            </nav>
            <h2 class="text-2xl font-bold text-navy">Detail Nomor Surat</h2>
            <p class="text-xs text-slate-500 font-semibold">Nomor: {{ $surat->nomor_surat }}</p>
        </div>

        <!-- 3 Tombol Aksi Header: Edit, Simpan, Tutup -->
        <div class="flex items-center gap-2.5">
            <!-- Tombol Edit -->
            <a href="{{ route('surat.edit', $surat->id) }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:text-primary shadow-sm transition-all" title="Edit data surat">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit</span>
            </a>

            <!-- Tombol Simpan -->
            <button type="button" onclick="document.getElementById('detailSuratForm').submit();" class="btn-pill-primary px-5 py-2 text-xs font-bold gap-1.5 shadow-md flex items-center cursor-pointer" title="Simpan data surat">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Simpan</span>
            </button>

            <!-- Tombol Tutup -->
            <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-rose-600 hover:border-rose-300 rounded-xl border border-slate-200 bg-white shadow-sm transition-all" title="Tutup dan kembali ke daftar surat">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>Tutup</span>
            </a>
        </div>
    </div>

    <form id="detailSuratForm" action="{{ route('surat.update', $surat->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="tgl_surat" value="{{ $surat->tgl_surat }}">
        <input type="hidden" name="nomor_surat" value="{{ $surat->nomor_surat }}">
        <input type="hidden" name="has_sppd" value="{{ $surat->has_sppd }}">
        @foreach($surat->pegawais as $p)
            <input type="hidden" name="pegawai_id[]" value="{{ $p->id }}">
            <input type="hidden" name="nomor_sppd[{{ $p->id }}]" value="{{ $p->pivot->nomor_sppd ?? '' }}">
        @endforeach

        <!-- Struktur Layout Panel Detail: Dua Kolom -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">
            
            <!-- KOLOM KIRI (Area Utama, Lebih Lebar) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card Informasi Detail Laporan -->
                <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
                    
                    <!-- Header Card: Badge Jenis Surat & Perihal / Judul Dokumen -->
                    <div class="flex items-center justify-between border-b border-blue-100 pb-4">
                        <div class="w-full mr-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-blue-50 text-primary border border-blue-200/60">
                                {{ optional($surat->jenisSurat)->nama_jenis ?? 'SURAT PERINTAH TUGAS (SPT)' }}
                            </span>
                            <h3 class="text-xl font-bold text-navy mt-2">{{ $surat->perihal ?? 'Surat Tugas' }}</h3>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-xs text-slate-400 font-semibold block">Tanggal Terbit</span>
                            <span class="text-sm font-bold text-navy">{{ \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>

                    <!-- Baris Data: Nomor Surat & Tujuan / Instansi -->
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
                                {{ $surat->tujuan ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Uraian / Maksud Surat -->
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Uraian / Maksud Surat</span>
                        <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-200/60 leading-relaxed">
                            {{ $surat->uraian ?: '-' }}
                        </p>
                    </div>

                    @if($surat->keterangan && trim($surat->keterangan) !== '')
                    <div class="bg-blue-50/70 border-l-4 border-primary rounded-r-2xl p-4 shadow-sm space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-primary block">Keterangan tambahan &middot; opsional</span>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $surat->keterangan }}</p>
                    </div>
                    @endif

                </div>

                <!-- Tabel Pegawai yang Ditugaskan -->
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
                            <tbody class="divide-y divide-blue-50 text-sm">
                                @forelse($surat->pegawais as $index => $pegawai)
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="py-3 px-2 font-semibold text-slate-400 text-xs">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-navy text-xs">{{ $pegawai->nama }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $pegawai->nip }}</td>
                                    <td class="py-3 px-4 text-slate-600 text-xs">{{ $pegawai->jabatan }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-xs text-slate-400 italic">
                                        Belum ada pegawai yang ditugaskan pada surat ini.
                                    </td>
                                </tr>
                                @endforelse
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
                            <span class="{{ ($surat->status ?? 'Terbit') === 'Draft' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'badge-green' }} px-3 py-1 rounded-full text-xs font-bold">
                                {{ $surat->status ?? 'Terbit' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-100/60">
                            <span class="text-slate-500 font-medium">Dibuat Oleh</span>
                            <span class="font-bold text-navy">Admin Kasubag</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-500 font-medium">Ada SPPD Terkait</span>
                            <span class="font-bold {{ $surat->has_sppd ? 'text-primary' : 'text-slate-400' }}">
                                {{ $surat->has_sppd ? 'Ya (' . $surat->pegawais->count() . ' Dokumen)' : 'Tidak' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card SPPD Terkait (Per Personil) -->
                <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SPPD Terkait</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $surat->has_sppd ? 'badge-blue' : 'bg-blue-50 text-primary border border-blue-200/60' }}">
                            {{ $surat->has_sppd ? ($surat->pegawais->count() . ' Dokumen') : 'Tidak Ada' }}
                        </span>
                    </div>

                    @if($surat->has_sppd && $surat->pegawais->count() > 0)
                    <div class="space-y-3">
                        @foreach($surat->pegawais as $pegawai)
                        @php
                            $nomorSppd = $pegawai->pivot->nomor_sppd ?? '-';
                        @endphp
                        <div class="bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm space-y-2">
                            <div class="flex justify-between items-start">
                                <div class="w-full mr-2">
                                    <span class="text-xs font-mono font-bold text-pink-600 block">
                                        {{ $nomorSppd }}
                                    </span>
                                    <h5 class="text-sm font-bold text-navy mt-0.5">{{ $pegawai->nama }}</h5>
                                </div>
                                <span class="badge-green text-[10px] px-2 py-0.5 rounded-full font-bold flex-shrink-0">Terbit</span>
                            </div>
                            <p class="text-xs text-slate-500">NIP. {{ $pegawai->nip }} &bull; {{ $pegawai->jabatan }}</p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/60 text-center">
                        <span class="text-xs text-slate-400 italic">Surat ini tidak memiliki SPPD terkait.</span>
                    </div>
                    @endif
                </div>

            </div>

        </div>
    </form>
</div>
@endsection