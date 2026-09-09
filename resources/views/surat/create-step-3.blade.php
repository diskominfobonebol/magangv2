@extends('layouts.app')

@section('title', 'Buat Surat Baru - Langkah 3')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('surat.index') }}" class="hover:text-primary transition-colors font-semibold">Surat Menyurat</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <a href="{{ route('surat.create') }}" class="hover:text-primary transition-colors font-semibold">Buat Surat</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-400 font-medium">Langkah 3</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.create.step2') }}" class="btn-pill-secondary w-9 h-9 p-0 flex items-center justify-center" title="Kembali ke Langkah 2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-navy">Buat Surat Baru</h2>
                <p class="text-xs text-slate-500 font-semibold">Langkah 3 dari 3</p>
            </div>
        </div>
        <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Kembali / Exit
        </a>
    </div>

    <!-- Stepper -->
    <div class="py-2 flex items-center justify-center">
        <!-- Step 1 (Completed) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Informasi Surat</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-primary mx-2"></div>
        
        <!-- Step 2 (Completed) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-sm" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="ml-2 text-xs font-semibold text-slate-400">Personel & Uraian</span>
        </div>
        <!-- Divider -->
        <div class="w-12 border-t-2 border-primary mx-2"></div>

        <!-- Step 3 (Active) -->
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7 rounded-full text-white font-bold text-xs shadow-md ring-4 ring-blue-100" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                3
            </div>
            <span class="ml-2 text-xs font-bold text-primary">Nomor SPPD</span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden" x-data="{ pilihanSppd: null, tempPilihan: 'ya' }">
        <form action="{{ route('surat.store') }}" method="POST" class="p-6 md:p-8" id="form-utama">
            @csrf
            
            <input type="hidden" name="has_sppd" x-bind:value="pilihanSppd === 'ya' ? 1 : 0">
            <input type="hidden" name="tgl_surat" value="{{ session('s_tgl_surat') }}">
            <input type="hidden" name="tujuan" value="{{ session('s_tujuan') }}">
            <input type="hidden" name="jenis_surat_id" value="{{ session('s_jenis_surat_id') }}">
            <input type="hidden" name="uraian" value="{{ session('s_uraian') }}">
            <input type="hidden" name="keterangan" value="{{ session('s_keterangan') }}">
            
            @if(session('s_pegawai_id'))
                @foreach(session('s_pegawai_id') as $pid)
                    <input type="hidden" name="pegawai_id[]" value="{{ $pid }}">
                @endforeach
            @endif
                
            <div class="space-y-6">
                <div class="border border-blue-200/60 rounded-3xl p-6 bg-white/50">
                    <!-- Kondisi A: Belum Memilih (Menggunakan Dropdown) -->
                    <div x-show="pilihanSppd === null" x-transition>
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 mb-6">
                            <div class="text-amber-600 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-amber-800">Generate Nomor SPPD</h4>
                                <p class="text-xs text-amber-700 mt-1 font-medium">SPT ini dapat memiliki SPPD terkait. Apakah surat ini disertai SPPD?</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <select x-model="tempPilihan" class="form-input flex-1">
                                <option value="ya">Ya, ada SPPD terkait</option>
                                <option value="tidak">Lewati, tidak ada SPPD</option>
                            </select>
                            <button type="button" @click="pilihanSppd = tempPilihan" class="btn-pill-primary px-5 py-2.5 text-xs font-bold cursor-pointer">
                                Konfirmasi
                            </button>
                        </div>
                    </div>

                    <!-- Kondisi B: Memilih Tidak -->
                    <div x-show="pilihanSppd === 'tidak'" style="display: none;" x-transition class="flex items-center gap-3 py-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-sm font-bold text-navy">Surat ini tidak memiliki SPPD terkait.</span>
                        <button type="button" @click="pilihanSppd = null" class="ml-2 text-xs text-primary font-bold hover:underline cursor-pointer">
                            Ubah Pilihan
                        </button>
                    </div>

                    <!-- Kondisi C: Memilih Ya -->
                    <div x-show="pilihanSppd === 'ya'" style="display: none;" x-transition>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-navy">SPPD Terkait Akan Dibuat</span>
                            <button type="button" @click="pilihanSppd = null" class="ml-2 text-xs text-primary font-bold hover:underline cursor-pointer">
                                Ubah Pilihan
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mb-4 font-medium">Sistem akan menggenerate nomor SPPD otomatis untuk seluruh personel yang ditugaskan setelah surat diterbitkan.</p>
                        
                        <!-- Preview Personel SPPD -->
                        <div class="bg-white/80 border border-blue-200/60 rounded-2xl p-4">
                            <ul class="space-y-3">
                                @if(isset($selectedPegawais) && $selectedPegawais->count() > 0)
                                    @foreach($selectedPegawais as $pegawai)
                                    <li class="flex justify-between items-center text-sm">
                                        <div>
                                            <span class="font-bold text-navy">{{ $pegawai->nama }}</span>
                                            <span class="text-slate-500 text-xs ml-2 font-medium">NIP. {{ $pegawai->nip }}</span>
                                        </div>
                                        <span class="badge-blue text-xs font-mono px-2.5 py-1 rounded-full font-bold">Auto-Generate</span>
                                    </li>
                                    @endforeach
                                @else
                                    <li class="text-xs text-slate-400 italic">Tidak ada personel yang dipilih.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 pt-5 border-t border-blue-200/40 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.create.step2') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold inline-block text-center">
                        &larr; Kembali
                    </a>
                    <a href="{{ route('surat.index') }}" class="btn-pill-secondary px-4 py-2.5 text-xs font-bold flex items-center gap-1.5 text-slate-600 hover:text-red-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Batal & Exit
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Tombol Simpan Draft (Men-trigger form tersembunyi di bawah) -->
                    <button type="button" onclick="document.getElementById('form-simpan-draft').submit();" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold cursor-pointer">
                        Simpan Draft
                    </button>
                    <!-- Tombol Terbitkan (Milik form utama) -->
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold gap-2 shadow-md cursor-pointer">
                        Terbitkan Surat
                    </button>
                </div>
            </div>
        </form>

        <!-- Form Tersembunyi Khusus untuk Simpan Draft -->
        <form id="form-simpan-draft" action="{{ route('surat.draft') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>
@endsection