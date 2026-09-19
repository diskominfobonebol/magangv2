@extends('layouts.app')

@section('title', 'Surat Keputusan (SK)')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="suratSkManager()">

    <!-- Header Utama & Tombol Aksi -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-navy">Surat Keputusan (SK)</h2>
            <p class="text-slate-500 mt-1 text-sm">Pencatatan dan arsip penerbitan Surat Keputusan (SK) Dinas Kominfo.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('surat.sk.exportPdf', request()->query()) }}" target="_blank" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center hover:text-primary transition-all" title="Export Rekapitulasi Surat SK ke PDF">
                <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Rekapitulasi PDF
            </a>
            @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
            <a href="{{ route('surat.sk.create') }}" class="btn-pill-primary px-5 py-2.5 text-xs font-bold gap-2 shadow-lg shadow-blue-500/25 flex items-center" title="Buat Surat Keputusan (SK) Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Buat SK Baru
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
        <form method="GET" action="{{ route('surat.sk') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Cari Nomor / Tentang / Keterangan (Col span 6) -->
            <div class="md:col-span-6">
                <label class="form-label">Cari Nomor/Perihal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" placeholder="Cari nomor SK, perihal tentang, keterangan..." value="{{ request('search') }}" class="form-input !pl-10">
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
                <a href="{{ route('surat.sk') }}" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Kotak Ringkasan Metrik Rekap (Style Sama Persis dengan SPT) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Total Surat SK -->
        <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-500/15 block">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-primary transition-colors">Total Surat SK</p>
                    </div>
                    <h3 class="text-3xl font-extrabold text-navy group-hover:text-primary transition-colors">{{ $totalSk ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-primary flex items-center justify-center font-bold text-xl group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                    📜
                </div>
            </div>
            <div class="mt-4 pt-3 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium text-slate-600">Surat Keputusan Kadis</span>
                <span class="text-primary font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                    Semua Dokumen &rarr;
                </span>
            </div>
        </div>

        <!-- Card 2: Total Surat Bulan Ini -->
        <a href="{{ route('surat.sk', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" 
           class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 card-interactive group relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/15 block cursor-pointer"
           title="Klik untuk menyaring surat SK pada bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}">
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

    <!-- TABEL REKAPITULASI SURAT SK -->
    <div class="bg-card-gradient rounded-3xl p-6 md:p-8 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-blue-100/60">
            <div>
                <h3 class="text-lg font-bold text-navy">Tabel Rekapitulasi Surat Keputusan (SK)</h3>
                <p class="text-xs text-slate-500">Daftar arsip penerbitan nomor Surat Keputusan (SK) beserta dokumen digital.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center text-xs font-bold text-primary bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">
                    {{ $suratSks->total() }} Total SK
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-blue-100 text-xs font-bold text-slate-400 uppercase bg-white/40">
                        <th class="py-3.5 px-3">Tanggal SK</th>
                        <th class="py-3.5 px-3">Nomor Surat SK</th>
                        <th class="py-3.5 px-3">Perihal / Tentang</th>
                        <th class="py-3.5 px-3">Keterangan</th>
                        <th class="py-3.5 px-3 text-center">Berkas Lampiran</th>
                        <th class="py-3.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($suratSks as $item)
                    @php
                        $jsonData = [
                            'id' => $item->id,
                            'nomor_sk' => $item->nomor_sk,
                            'tanggal_sk' => $item->tanggal_sk->format('Y-m-d'),
                            'tanggal_formatted' => \Carbon\Carbon::parse($item->tanggal_sk)->translatedFormat('d F Y'),
                            'tentang' => $item->tentang,
                            'keterangan' => $item->keterangan ?? '-',
                            'has_file' => !empty($item->file_sk),
                            'download_url' => !empty($item->file_sk) ? route('surat.sk.download', $item->id) : null,
                        ];
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors border-b border-slate-100">
                        <td class="py-4 px-3 font-semibold text-slate-600 text-xs whitespace-nowrap">
                            {{ $item->tanggal_sk->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-4 px-3 font-mono font-bold text-primary text-xs whitespace-nowrap">
                            <span class="hover:underline cursor-pointer" @click="openDetail({{ json_encode($jsonData) }})">
                                {{ $item->nomor_sk }}
                            </span>
                        </td>
                        <td class="py-4 px-3 text-slate-800 text-xs">
                            <div class="font-bold text-navy leading-relaxed">{{ $item->tentang }}</div>
                        </td>
                        <td class="py-4 px-3 text-xs text-slate-600">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                        <td class="py-4 px-3 text-center whitespace-nowrap">
                            @if($item->file_sk)
                                <a href="{{ route('surat.sk.download', $item->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors" title="Unduh Berkas Lampiran">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    PDF Lampiran
                                </a>
                            @else
                                <span class="text-slate-400 text-xs italic">Tanpa Berkas</span>
                            @endif
                        </td>
                        <td class="py-4 px-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Lihat -->
                                <button type="button" 
                                        @click="openDetail({{ json_encode($jsonData) }})" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-primary hover:bg-primary hover:text-white font-bold text-xs border border-blue-200/60 shadow-sm transition-all cursor-pointer"
                                        title="Lihat Detail Surat SK">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat
                                </button>

                                <!-- Tombol Unduh File -->
                                @if($item->file_sk)
                                <a href="{{ route('surat.sk.download', $item->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs border border-emerald-200 shadow-sm transition-all"
                                   title="Unduh Berkas PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh
                                </a>
                                @endif

                                <!-- Tombol Hapus -->
                                @if(auth()->user()->role_id == 2 || auth()->user()->role_id == 1)
                                <button type="button" 
                                        @click="confirmDelete('{{ $item->id }}', '{{ $item->nomor_sk }}')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs border border-rose-200/60 shadow-sm transition-all cursor-pointer"
                                        title="Hapus Surat SK">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <span class="text-4xl">📂</span>
                                <p class="text-sm font-semibold">Belum ada arsip Surat Keputusan (SK) yang cocok dengan filter pencarian.</p>
                                <a href="{{ route('surat.sk') }}" class="text-xs text-primary hover:underline font-bold">Reset Filter</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suratSks->hasPages())
        <div class="pt-4 border-t border-blue-100/60">
            {{ $suratSks->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL DETAIL SURAT SK -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 md:p-8 shadow-2xl border border-slate-100 space-y-6 relative"
             @click.away="showDetailModal = false">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="badge-blue text-xs font-bold px-2.5 py-1 rounded-full">Detail Surat Keputusan</span>
                    <h3 class="text-xl font-extrabold text-navy mt-2" x-text="detailData.nomor_sk"></h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Diterbitkan pada ' + detailData.tanggal_formatted"></p>
                </div>
                <button type="button" @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition-colors">
                    ✕
                </button>
            </div>

            <!-- Konten Detail -->
            <div class="space-y-4 text-xs">
                <!-- Tentang / Judul SK -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Perihal / Tentang SK:</span>
                    <p class="text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-100 leading-relaxed font-bold text-sm" x-text="detailData.tentang"></p>
                </div>

                <!-- Keterangan -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Keterangan Tambahan:</span>
                    <p class="text-slate-600 bg-white p-3 rounded-xl border border-slate-100 leading-relaxed" x-text="detailData.keterangan || '-'"></p>
                </div>

                <!-- Berkas Digital Lampiran -->
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Berkas Lampiran PDF:</span>
                    <template x-if="detailData.has_file">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 bg-emerald-50">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">📄</span>
                                <div>
                                    <p class="font-bold text-emerald-900 text-xs">Dokumen SK Resmi Terlampir</p>
                                    <p class="text-[10px] text-emerald-700">Format PDF / Dokumen Digital</p>
                                </div>
                            </div>
                            <a :href="detailData.download_url" class="btn-pill-primary !bg-emerald-600 hover:!bg-emerald-700 px-4 py-1.5 text-xs font-bold">
                                Unduh Berkas
                            </a>
                        </div>
                    </template>
                    <template x-if="!detailData.has_file">
                        <p class="text-slate-400 italic">Tidak ada berkas fisik yang diunggah saat penerbitan.</p>
                    </template>
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
                <h3 class="text-lg font-extrabold text-navy">Hapus Surat Keputusan (SK)?</h3>
                <p class="text-xs text-slate-500 mt-1">Anda yakin ingin menghapus Surat SK dengan nomor <strong class="text-rose-600" x-text="deleteNomor"></strong> beserta berkas lampirannya dari arsip? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form :action="'{{ url('/surat/sk') }}/' + deleteId" method="POST" class="flex items-center gap-3 pt-2">
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
function suratSkManager() {
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
