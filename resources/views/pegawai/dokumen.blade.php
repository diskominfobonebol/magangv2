<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Dokumen - KOMTARA Diskominfo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .bg-card-gradient {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(23, 37, 84, 0.05), 0 4px 12px -2px rgba(23, 37, 84, 0.02);
        }
        .bg-header-gradient {
            background: #FFFFFF !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        [x-cloak] { display: none !important; }
        body.overflow-hidden {
            overflow: hidden !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.6);
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.5);
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.8);
        }
    </style>
</head>
<body class="app-background font-sans antialiased text-slate-800" :class="{ 'overflow-hidden': showUploadModal || showEditModal || showDetailModal || showDeleteModal }" x-data="{ 
    masterKategori: {{ Js::from($masterKategori) }},
    showUploadModal: false, 
    showEditModal: false,
    showDetailModal: false,
    showDeleteModal: false,

    filterKategoriSelected: '{{ $kategori ?? "all" }}',
    filterJenisSelected: '{{ $jenisDokumen ?? "all" }}',
    filterJenisList: [],

    initFilterJenis() {
        if (this.filterKategoriSelected && this.filterKategoriSelected !== 'all') {
            const cat = this.masterKategori.find(k => k.id == this.filterKategoriSelected || k.nama_kategori == this.filterKategoriSelected);
            this.filterJenisList = cat ? (cat.jenis_dokumens || []) : [];
            if (this.filterJenisSelected && this.filterJenisSelected !== 'all') {
                const foundJenis = this.filterJenisList.find(j => j.id == this.filterJenisSelected || j.nama_jenis == this.filterJenisSelected);
                if (foundJenis) {
                    this.filterJenisSelected = foundJenis.id;
                }
            }
        } else {
            this.filterJenisList = [];
            this.filterJenisSelected = 'all';
        }
    },

    onFilterKategoriChange() {
        if (this.filterKategoriSelected && this.filterKategoriSelected !== 'all') {
            const cat = this.masterKategori.find(k => k.id == this.filterKategoriSelected || k.nama_kategori == this.filterKategoriSelected);
            this.filterJenisList = cat ? (cat.jenis_dokumens || []) : [];
            this.filterJenisSelected = 'all';
        } else {
            this.filterJenisList = [];
            this.filterJenisSelected = 'all';
        }
    },

    uploadKategoriId: '',
    uploadJenisList: [],
    uploadJenisDokumenCustom: '',

    editItem: { 
        id: null, 
        kategori_dokumen_id: '', 
        jenis_dokumen_id: '', 
        jenis_dokumen: '',
        tmt_tahun: '', 
        nomor_dokumen: '', 
        keterangan: '', 
        file_name: '' 
    },
    editJenisList: [],

    detailItem: {
        id: null,
        kategori: '',
        jenisDokumen: '',
        tmtTahun: '',
        nomorDokumen: '',
        keterangan: '',
        updateTerakhir: '',
        previewUrl: '',
        downloadUrl: '',
        fileName: '',
        fileSize: ''
    },

    deleteItem: {
        id: null,
        jenisDokumen: '',
        deleteUrl: ''
    },

    isDokumenLainnya(kategoriId) {
        if (!kategoriId || kategoriId === 'all') return false;
        const cat = this.masterKategori.find(k => k.id == kategoriId || k.nama_kategori == kategoriId);
        return cat && (cat.nama_kategori === 'Dokumen Lainnya' || cat.urutan == 11);
    },

    onUploadKategoriChange() {
        if (this.isDokumenLainnya(this.uploadKategoriId)) {
            this.uploadJenisList = [];
        } else {
            const cat = this.masterKategori.find(k => k.id == this.uploadKategoriId);
            this.uploadJenisList = cat ? (cat.jenis_dokumens || []) : [];
        }
    },

    onEditKategoriChange() {
        if (this.isDokumenLainnya(this.editItem.kategori_dokumen_id)) {
            this.editJenisList = [];
        } else {
            const cat = this.masterKategori.find(k => k.id == this.editItem.kategori_dokumen_id);
            this.editJenisList = cat ? (cat.jenis_dokumens || []) : [];
        }
    },

    openDetail(item) {
        this.detailItem = item;
        this.showDetailModal = true;
    },

    openEdit(item) {
        this.editItem = { ...item };
        if (this.isDokumenLainnya(this.editItem.kategori_dokumen_id)) {
            this.editJenisList = [];
        } else {
            const cat = this.masterKategori.find(k => k.id == this.editItem.kategori_dokumen_id);
            this.editJenisList = cat ? (cat.jenis_dokumens || []) : [];
        }
        this.showEditModal = true;
    },

    openDelete(item) {
        this.deleteItem = item;
        this.showDeleteModal = true;
    }
}" x-init="initFilterJenis()">

    <div class="min-h-screen flex">
        <!-- Sidebar Navigation Terpadu (Satu Komponen Bersama Seluruh Role) -->
        @include('components.sidebar-admin', ['active' => 'arsip'])

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-x-hidden">
            <!-- Header Bar -->
            <header class="bg-header-gradient px-6 py-4 flex items-center justify-between shadow-xs border-b border-slate-200">
                <div>
                    <h1 class="text-xl font-black text-[#172554] tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-folder-tree text-[#198754]"></i>
                        <span>Arsip Dokumen</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Pengelolaan mandiri berkas arsip kepegawaian pribadi Anda yang terverifikasi.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('account.security') }}" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-[#172554] text-xs font-bold rounded-xl border border-slate-200 transition flex items-center gap-2 shadow-2xs">
                        <i class="fa-solid fa-shield-halved text-[#198754]"></i>
                        <span class="hidden sm:inline">Keamanan Akun & 2FA</span>
                    </a>
                    <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                    <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Pemda Bone Bolango" class="h-8 w-auto mix-blend-multiply">
                    <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Kominfo Bone Bolango" class="h-8 w-auto mix-blend-multiply">
                </div>
            </header>

            <div class="p-6 space-y-6 flex-1">

                @if(session('success'))
                    <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl text-sm flex items-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-sm flex items-center space-x-2 shadow-xs">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm shadow-xs space-y-1">
                        <div class="flex items-center space-x-2 font-bold text-rose-900">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                            <span>Terdapat kesalahan saat memproses dokumen:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 pl-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Profile Card Pegawai -->
                <div class="bg-card-gradient rounded-3xl p-6 border border-[#A3CFBB]/60 shadow-md">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#198754] to-[#0F5132] text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-[#198754]/20 uppercase">
                                {{ substr($user->nama, 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-black text-[#172554]">{{ $user->nama }}</h2>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Akun Aktif
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">NIP: <span class="font-bold text-slate-700">{{ $user->nip ?: '-' }}</span></p>
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-1.5 text-xs font-semibold text-[#0F5132]">
                                    <span class="inline-flex items-center gap-1.5 bg-[#D1E7DD] px-2.5 py-0.5 rounded-lg border border-[#A3CFBB]/70">
                                        <i class="fa-solid fa-briefcase text-[11px] text-[#0F5132]"></i>
                                        <span>{{ $user->jabatan ?: 'Pegawai' }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 bg-slate-100 px-2.5 py-0.5 rounded-lg border border-slate-200 text-slate-700">
                                        <i class="fa-solid fa-award text-[11px] text-slate-500"></i>
                                        <span>Golongan: {{ $user->golongan ?: '-' }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center w-full sm:w-auto sm:self-center">
                            <div class="bg-white/90 border border-slate-200 px-5 py-3 rounded-2xl shadow-xs flex items-center space-x-4 w-full sm:w-auto">
                                <div class="w-12 h-12 rounded-xl bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center text-xl shadow-xs shrink-0">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Dokumen</span>
                                    <span class="text-2xl font-black text-[#172554] leading-tight">{{ $dokumens->total() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Tabel Dokumen -->
                <div class="bg-card-gradient rounded-3xl p-6 space-y-4">
                    <!-- Filter, Action Bar & Search -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-[#172554] text-base">Arsip Dokumen Kepegawaian Anda</h3>
                            <p class="text-xs text-slate-500">Anda dapat mengunggah, mengedit, melihat preview, maupun menghapus berkas dokumen milik Anda sendiri.</p>
                        </div>

                        <!-- Tombol Unggah Dokumen Baru untuk Pegawai -->
                        <button type="button" @click="showUploadModal = true; uploadKategoriId = ''; uploadJenisList = [];" class="px-5 py-2.5 bg-[#146C43] hover:bg-[#0F5132] text-white rounded-2xl text-xs font-bold transition shadow-md flex items-center justify-center space-x-2 shrink-0">
                            <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                            <span>Unggah Dokumen Baru</span>
                        </button>
                    </div>

                    <!-- Filter Bar -->
                    <form method="GET" action="{{ route('pegawai.dokumen') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-white/70 p-4 rounded-2xl border border-slate-200">
                        <!-- Filter Kategori 10 Kategori Baku -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Filter Kategori</label>
                            <select name="kategori" x-model="filterKategoriSelected" @change="onFilterKategoriChange()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                <option value="all">Semua Kategori</option>
                                @foreach($masterKategori as $kat)
                                    <option value="{{ $kat->id }}">
                                        {{ $kat->urutan }}. {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Sub-Kategori / Jenis Dokumen (Dependent terhadap Filter Kategori) -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Jenis Dokumen</label>
                            <select name="jenis_dokumen" 
                                x-model="filterJenisSelected" 
                                :disabled="!filterKategoriSelected || filterKategoriSelected === 'all'" 
                                :class="(!filterKategoriSelected || filterKategoriSelected === 'all') ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200' : 'bg-white text-[#172554] border-slate-300'"
                                class="w-full px-3 py-2 border rounded-xl text-xs font-semibold focus:ring-2 focus:ring-[#198754] focus:outline-none transition">
                                <option value="all" x-text="(!filterKategoriSelected || filterKategoriSelected === 'all') ? 'Pilih kategori terlebih dahulu' : 'Semua Jenis Dokumen'"></option>
                                <template x-for="j in filterJenisList" :key="j.id">
                                    <option :value="j.id" x-text="j.nama_jenis"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Pencarian Kata Kunci -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Pencarian Kata Kunci</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ $search }}" placeholder="Cari jenis dokumen / TMT..." class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                            </div>
                        </div>

                        <!-- Tombol Submit & Reset -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="w-full py-2 bg-[#146C43] hover:bg-[#0F5132] text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-filter text-xs"></i>
                                <span>Terapkan Filter</span>
                            </button>

                            @if($search || ($kategori && $kategori !== 'all') || ($jenisDokumen && $jenisDokumen !== 'all'))
                            <a href="{{ route('pegawai.dokumen') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition text-center shrink-0" title="Reset Filter">
                                Reset
                            </a>
                            @endif
                        </div>
                    </form>

                    <!-- TABEL ARSIP DOKUMEN PEGAWAI -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white/95">
                        <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                            <thead class="bg-slate-100 text-[#172554] uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5 text-center w-12">No</th>
                                    <th class="px-4 py-3.5">Kategori</th>
                                    <th class="px-4 py-3.5">Jenis Dokumen</th>
                                    <th class="px-4 py-3.5 text-center">TMT (Tahun)</th>
                                    <th class="px-4 py-3.5">Update Terakhir</th>
                                    <th class="px-4 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($dokumens as $index => $dokumen)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3.5 text-center font-bold text-slate-400">
                                        {{ $dokumens->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border bg-[#D1E7DD] text-[#0F5132] border-[#A3CFBB]">
                                            {{ $dokumen->kategori }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div>
                                            <p class="font-extrabold text-[#172554] text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-file-pdf text-rose-500 text-sm"></i>
                                                <span>{{ $dokumen->jenis_dokumen }}</span>
                                            </p>
                                            @if($dokumen->nomor_dokumen)
                                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">No: {{ $dokumen->nomor_dokumen }}</p>
                                            @endif
                                            @if($dokumen->keterangan)
                                                <p class="text-[10px] text-slate-500 italic mt-0.5 line-clamp-1 max-w-md">{{ $dokumen->keterangan }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($dokumen->tmt_tahun)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-[#D1E7DD] text-[#0F5132] border border-[#A3CFBB] font-mono">
                                                {{ $dokumen->tmt_tahun }}
                                            </span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700 font-medium">
                                            <i class="fa-solid fa-clock-rotate-left text-slate-400 text-[11px]"></i>
                                            <span>{{ $dokumen->update_terakhir_text }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                            {{ $dokumen->file_name }} ({{ $dokumen->formatted_file_size }})
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="inline-flex items-center space-x-1.5">
                                            <!-- Detail & Preview PDF Modal Langsung -->
                                            <button type="button" 
                                                @click="openDetail({
                                                    id: {{ $dokumen->id }},
                                                    kategori: '{{ addslashes($dokumen->kategori) }}',
                                                    jenisDokumen: '{{ addslashes($dokumen->jenis_dokumen) }}',
                                                    tmtTahun: '{{ addslashes($dokumen->tmt_tahun ?? '-') }}',
                                                    nomorDokumen: '{{ addslashes($dokumen->nomor_dokumen ?? '-') }}',
                                                    keterangan: '{{ addslashes($dokumen->keterangan ?? '-') }}',
                                                    updateTerakhir: '{{ addslashes($dokumen->update_terakhir_text) }}',
                                                    previewUrl: '{{ route('dokumen.preview', $dokumen->id) }}',
                                                    downloadUrl: '{{ route('dokumen.download', $dokumen->id) }}',
                                                    fileName: '{{ addslashes($dokumen->file_name) }}',
                                                    fileSize: '{{ $dokumen->formatted_file_size }}'
                                                })"
                                                title="Lihat Detail & Preview PDF" 
                                                class="w-8 h-8 rounded-full bg-[#D1E7DD] text-[#0F5132] hover:bg-[#198754] hover:text-white transition flex items-center justify-center shadow-xs">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </button>

                                            <!-- Edit Dokumen Milik Sendiri -->
                                            <button type="button" 
                                                @click="openEdit({
                                                    id: {{ $dokumen->id }},
                                                    kategori_dokumen_id: '{{ $dokumen->kategori_dokumen_id ?? '' }}',
                                                    jenis_dokumen_id: '{{ $dokumen->jenis_dokumen_id ?? '' }}',
                                                    jenis_dokumen: '{{ addslashes($dokumen->jenis_dokumen ?? '') }}',
                                                    tmt_tahun: '{{ $dokumen->tmt_tahun ?? '' }}',
                                                    nomor_dokumen: '{{ addslashes($dokumen->nomor_dokumen ?? '') }}',
                                                    keterangan: '{{ addslashes($dokumen->keterangan ?? '') }}',
                                                    file_name: '{{ addslashes($dokumen->file_name) }}'
                                                })"
                                                title="Edit Dokumen" 
                                                class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center shadow-xs">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </button>

                                            <!-- Hapus Dokumen Milik Sendiri -->
                                            <button type="button" 
                                                @click="openDelete({
                                                    id: {{ $dokumen->id }},
                                                    jenisDokumen: '{{ addslashes($dokumen->jenis_dokumen) }}',
                                                    deleteUrl: '{{ route('pegawai.dokumen.destroy', $dokumen->id) }}'
                                                })"
                                                title="Hapus Dokumen" 
                                                class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition flex items-center justify-center shadow-xs">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <div class="w-12 h-12 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center text-xl">
                                                <i class="fa-solid fa-folder-open"></i>
                                            </div>
                                            <p class="font-bold text-slate-600 text-sm">Belum Ada Dokumen yang Diarsipkan</p>
                                            <p class="text-xs text-slate-400 max-w-sm">Gunakan tombol "Unggah Dokumen Baru" di atas untuk menambahkan berkas kepegawaian Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    @if($dokumens->hasPages())
                    <div class="pt-2">
                        {{ $dokumens->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL 1: UNGGAH DOKUMEN BARU (ROLE PEGAWAI) -->
    <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div @click.away="showUploadModal = false" class="bg-white rounded-3xl max-w-xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200 my-auto">
            <form action="{{ route('pegawai.dokumen.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf

                <!-- Header Modal (Tetap / Sticky) -->
                <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:px-6 sm:py-4 shrink-0 bg-white">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-[#172554] text-base">Unggah Dokumen Kepegawaian Anda</h3>
                            <p class="text-xs text-slate-400">Pilih kategori dan jenis dokumen untuk menambahkan arsip pribadi.</p>
                        </div>
                    </div>
                    <button type="button" @click="showUploadModal = false" class="text-slate-400 hover:text-slate-600 text-lg p-1 rounded-lg hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body Modal (Scrollable Form Fields) -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-3.5 custom-scrollbar">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- 1. Kategori Dokumen (10 Kategori Baku) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Kategori Dokumen <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori_dokumen_id" x-model="uploadKategoriId" @change="onUploadKategoriChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <template x-for="kat in masterKategori" :key="kat.id">
                                    <option :value="kat.id" x-text="kat.urutan + '. ' + kat.nama_kategori"></option>
                                </template>
                            </select>
                        </div>

                        <!-- 2. Jenis Dokumen (Dinamis Sesuai Kategori / Input Bebas untuk Dokumen Lainnya) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Jenis Dokumen <span class="text-rose-500">*</span>
                            </label>
                            <!-- Input Teks Bebas khusus jika Kategori "Dokumen Lainnya" dipilih -->
                            <div x-show="isDokumenLainnya(uploadKategoriId)">
                                <input type="text" name="jenis_dokumen" x-model="uploadJenisDokumenCustom" :required="isDokumenLainnya(uploadKategoriId)" :disabled="!isDokumenLainnya(uploadKategoriId)" placeholder="Ketik jenis dokumen, contoh: Surat Keterangan Domisili" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                            </div>
                            <!-- Dropdown Pilihan untuk Kategori Baku -->
                            <div x-show="!isDokumenLainnya(uploadKategoriId)">
                                <select name="jenis_dokumen_id" :required="!isDokumenLainnya(uploadKategoriId)" :disabled="isDokumenLainnya(uploadKategoriId) || uploadJenisList.length === 0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="" disabled selected x-text="uploadJenisList.length === 0 ? '-- Pilih Kategori Dulu --' : '-- Pilih Jenis Dokumen --'"></option>
                                    <template x-for="j in uploadJenisList" :key="j.id">
                                        <option :value="j.id" x-text="j.nama_jenis"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- 3. TMT (Tahun) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                TMT (Tahun) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="tmt_tahun" min="1950" max="2099" placeholder="Contoh: 2024" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Format 4 digit tahun berlaku</span>
                        </div>

                        <!-- 4. Nomor Dokumen (Opsional) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Nomor Dokumen / SK
                            </label>
                            <input type="text" name="nomor_dokumen" placeholder="Contoh: 823.3/BKPSDM/2024" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                        </div>
                    </div>

                    <!-- 5. File Berkas (Wajib PDF, Maksimal 2MB) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            File Berkas PDF <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="file_dokumen" accept=".pdf,application/pdf" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#146C43] file:text-white hover:file:bg-[#0F5132] cursor-pointer">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                            <span>Format wajib: <strong class="text-slate-600">.PDF</strong></span>
                            <span>Maksimal ukuran: <strong class="text-rose-600">2 MB (2048 KB)</strong></span>
                        </div>
                    </div>

                    <!-- 6. Keterangan / Catatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan (Opsional)</label>
                        <textarea name="keterangan" rows="2" placeholder="Catatan perihal dokumen kepegawaian Anda..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none"></textarea>
                    </div>
                </div>

                <!-- Footer Modal (Tetap / Sticky di Bawah) -->
                <div class="flex items-center justify-end space-x-3 p-3.5 sm:px-6 sm:py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/90">
                    <button type="button" @click="showUploadModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#146C43] hover:bg-[#0F5132] text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan &amp; Unggah</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: DETAIL DOKUMEN & PREVIEW PDF INLINE (ROLE PEGAWAI) -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
        <div @click.away="showDetailModal = false" class="bg-white rounded-3xl max-w-4xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200 my-auto">
            <!-- Header Modal (Tetap / Sticky) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:px-6 sm:py-4 shrink-0 bg-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-[#172554] text-base" x-text="detailItem.jenisDokumen"></h3>
                        <p class="text-xs text-slate-400">
                            Kategori: <strong class="text-slate-700" x-text="detailItem.kategori"></strong> | TMT: <span class="font-bold text-[#0F5132]" x-text="detailItem.tmtTahun"></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a :href="detailItem.downloadUrl" title="Unduh Berkas PDF" class="px-3.5 py-1.5 bg-[#D1E7DD] hover:bg-[#198754] text-[#0F5132] hover:text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-download text-xs"></i>
                        <span>Unduh Berkas</span>
                    </a>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 text-xl p-1 rounded-lg hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Body Modal (Scrollable Content) -->
            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 custom-scrollbar">
                <!-- Info Badges & Details -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs shrink-0">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Kategori</span>
                        <span class="font-extrabold text-[#172554] truncate block" x-text="detailItem.kategori"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">TMT (Tahun)</span>
                        <span class="font-extrabold text-[#0F5132] font-mono block" x-text="detailItem.tmtTahun"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Nomor SK/Dokumen</span>
                        <span class="font-medium text-slate-700 truncate block font-mono" x-text="detailItem.nomorDokumen"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Ukuran Berkas</span>
                        <span class="font-medium text-slate-700 block font-mono" x-text="detailItem.fileSize"></span>
                    </div>
                </div>

                <!-- Update Terakhir Info -->
                <div class="flex items-center space-x-2 text-xs text-[#0F5132] bg-[#D1E7DD]/50 px-3.5 py-2 rounded-xl border border-[#A3CFBB] shrink-0">
                    <i class="fa-solid fa-circle-info text-[#0F5132]"></i>
                    <span class="font-semibold" x-text="detailItem.updateTerakhir"></span>
                </div>

                <!-- PDF VIEWER LANGSUNG DI DALAM HALAMAN -->
                <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-900 shadow-inner min-h-[300px] max-h-[55vh]">
                    <template x-if="detailItem.previewUrl">
                        <iframe :src="detailItem.previewUrl" class="w-full h-full min-h-[300px]" frameborder="0"></iframe>
                    </template>
                </div>
            </div>

            <!-- Footer Modal (Tetap / Sticky di Bawah) -->
            <div class="flex items-center justify-between p-3.5 sm:px-6 border-t border-slate-100 shrink-0 bg-slate-50/90">
                <p class="text-[11px] text-slate-400 italic">
                    Preview berkas resmi tersimpan aman di server KOMTARA Diskominfo Bone Bolango.
                </p>
                <button type="button" @click="showDetailModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    Tutup Preview
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: EDIT DOKUMEN (ROLE PEGAWAI) -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-3 sm:p-4">
            <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
                <!-- Header (Fixed) -->
                <div class="shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-white">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-pen"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-[#172554] text-base">Edit Dokumen Kepegawaian Anda</h3>
                            <p class="text-xs text-slate-400">Perbarui data atau ganti berkas PDF fisik dokumen Anda.</p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form :action="'{{ url('/pegawai/dokumen') }}/' + editItem.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    @method('PUT')

                    <!-- Body (Scrollable) -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- 1. Kategori Dokumen (10 Kategori Baku) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Kategori Dokumen <span class="text-rose-500">*</span>
                                </label>
                                <select name="kategori_dokumen_id" x-model="editItem.kategori_dokumen_id" @change="onEditKategoriChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                    <template x-for="kat in masterKategori" :key="kat.id">
                                        <option :value="kat.id" :selected="kat.id == editItem.kategori_dokumen_id" x-text="kat.urutan + '. ' + kat.nama_kategori"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- 2. Jenis Dokumen (Dinamis Sesuai Kategori / Input Bebas untuk Dokumen Lainnya) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Jenis Dokumen <span class="text-rose-500">*</span>
                                </label>
                                <!-- Input Teks Bebas khusus jika Kategori "Dokumen Lainnya" dipilih -->
                                <div x-show="isDokumenLainnya(editItem.kategori_dokumen_id)">
                                    <input type="text" name="jenis_dokumen" x-model="editItem.jenis_dokumen" :required="isDokumenLainnya(editItem.kategori_dokumen_id)" :disabled="!isDokumenLainnya(editItem.kategori_dokumen_id)" placeholder="Ketik jenis dokumen, contoh: Surat Keterangan Domisili" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                </div>
                                <!-- Dropdown Pilihan untuk Kategori Baku -->
                                <div x-show="!isDokumenLainnya(editItem.kategori_dokumen_id)">
                                    <select name="jenis_dokumen_id" x-model="editItem.jenis_dokumen_id" :required="!isDokumenLainnya(editItem.kategori_dokumen_id)" :disabled="isDokumenLainnya(editItem.kategori_dokumen_id)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                                        <template x-for="j in editJenisList" :key="j.id">
                                            <option :value="j.id" :selected="j.id == editItem.jenis_dokumen_id" x-text="j.nama_jenis"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- 3. TMT (Tahun) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    TMT (Tahun) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="tmt_tahun" x-model="editItem.tmt_tahun" min="1950" max="2099" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                            </div>

                            <!-- 4. Nomor Dokumen -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Dokumen / SK</label>
                                <input type="text" name="nomor_dokumen" x-model="editItem.nomor_dokumen" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none">
                            </div>
                        </div>

                        <!-- 5. Ganti Berkas PDF (Opsional, PDF max 2MB) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Ganti Berkas PDF (Opsional)
                            </label>
                            <input type="file" name="file_dokumen" accept=".pdf,application/pdf" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#146C43] file:text-white hover:file:bg-[#0F5132] cursor-pointer">
                            <div class="text-[10px] text-slate-400 mt-1 flex justify-between">
                                <span>File saat ini: <strong class="text-slate-600 font-mono" x-text="editItem.file_name"></strong></span>
                                <span>Maksimal: <strong class="text-rose-600">2 MB (PDF)</strong></span>
                            </div>
                        </div>

                        <!-- 6. Keterangan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                            <textarea name="keterangan" x-model="editItem.keterangan" rows="2" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-[#172554] focus:ring-2 focus:ring-[#198754] focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Footer (Sticky) -->
                    <div class="shrink-0 flex items-center justify-end space-x-3 px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-3xl">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-200/80 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5">
                            <i class="fa-solid fa-save text-xs"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 4: KONFIRMASI HAPUS DOKUMEN (ROLE PEGAWAI) -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="showDeleteModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-rose-100 text-center">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 mx-auto flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="font-black text-[#172554] text-lg">Hapus Dokumen Anda?</h3>
                    <p class="text-xs text-slate-500">
                        Anda akan menghapus dokumen <strong class="text-slate-800" x-text="deleteItem.jenisDokumen"></strong>.
                    </p>
                    <p class="text-[11px] text-rose-600 font-semibold bg-rose-50 p-2 rounded-xl mt-2">
                        Peringatan: Berkas fisik PDF akan dihapus secara permanen dari server penyimpanan.
                    </p>
                </div>

                <form :action="deleteItem.deleteUrl" method="POST" class="flex items-center justify-center space-x-3 pt-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-md flex items-center space-x-1.5">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Ya, Hapus Permanen</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
