<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Aset - KOMTARA Diskominfo</title>
    <!-- Plus Jakarta Sans Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gradient-background.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        [x-cloak] { display: none !important; }
        /* Hilangkan spinner / sort arrow pada input type number */
        .no-spin::-webkit-inner-spin-button,
        .no-spin::-webkit-outer-spin-button,
        input[type="number"].no-spin::-webkit-inner-spin-button,
        input[type="number"].no-spin::-webkit-outer-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        .no-spin,
        input[type="number"].no-spin {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
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
        :root {
            --color-primary: #146C43;
            --color-primary-hover: #0F5132;
            --color-accent: #F59E0B;
            --color-accent-hover: #D97706;
            --color-white: #FFFFFF;
            --color-navy: #172554;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .bg-card-gradient, .card-soft {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 1.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(23, 37, 84, 0.05), 0 4px 12px -2px rgba(23, 37, 84, 0.02) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-interactive:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -6px rgba(20, 108, 67, 0.15), 0 8px 16px -4px rgba(245, 158, 11, 0.1);
            border-color: rgba(20, 108, 67, 0.6) !important;
        }
        .bg-modal-gradient {
            background: #FFFFFF !important;
            border-radius: 1.5rem !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 25px 50px -12px rgba(23, 37, 84, 0.25) !important;
        }
        .bg-header-gradient {
            background: #FFFFFF !important;
            backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        .form-input {
            width: 100% !important;
            height: 2.625rem !important;
            box-sizing: border-box !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            line-height: 1.25rem !important;
            color: #172554 !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s ease-in-out !important;
        }
        .form-input:focus {
            outline: none !important;
            border-color: #198754 !important;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2) !important;
        }
        .form-label {
            display: block !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #172554 !important;
            margin-bottom: 0.375rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.025em !important;
        }

        /* Styling khusus untuk cetak label QR Code & Berita Acara BAST */
        @media print {
            header, aside, nav, form, .no-print, button {
                display: none !important;
            }

            body, html {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                color: #000000 !important;
            }

            body * {
                visibility: hidden !important;
            }

            /* Reset modal wrapper saat mencetak dari dialog agar tidak floating / terpotong */
            .fixed.inset-0 {
                position: static !important;
                display: block !important;
                background: transparent !important;
                backdrop-filter: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                box-shadow: none !important;
                border: none !important;
            }

            .bg-white.rounded-3xl.shadow-2xl,
            .bg-modal-gradient.rounded-3xl {
                position: static !important;
                max-width: 100% !important;
                width: 100% !important;
                max-height: none !important;
                height: auto !important;
                overflow: visible !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                background: #ffffff !important;
            }

            /* 1. Cetak Label QR */
            #printable-qr-label, #printable-qr-label * {
                visibility: visible !important;
            }
            #printable-qr-label {
                position: fixed !important;
                left: 50% !important;
                top: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 280px !important;
                margin: 0 !important;
                padding: 20px !important;
                background: #ffffff !important;
                border: 2px dashed #198754 !important;
                border-radius: 16px !important;
                box-shadow: none !important;
                text-align: center !important;
            }

            /* 2. Cetak Dokumen Berita Acara BAST */
            #printable-bast-doc, #printable-bast-doc * {
                visibility: visible !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            #printable-bast-doc {
                position: static !important;
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10mm 15mm !important;
                background: #ffffff !important;
            }
            #printable-bast-doc tr.bg-slate-50 {
                background-color: #f8fafc !important;
            }
        }
    </style>
</head>
<body class="app-background font-sans antialiased text-slate-800" :class="{ 'overflow-hidden': showAddModal || showEditModal || showQrModal || showUploadBaModal || showPreviewBaModal || showDetailModal }" x-data="{ 
    activeTab: '{{ $currentTab ?? 'master' }}', 
    showAddModal: false, 
    showEditModal: false, 
    showDetailModal: false,
    showQrModal: false, 
    showUploadBaModal: false,
    baModalMode: 'upload',
    showPreviewBaModal: false,
    asetMap: {{ json_encode($listAsetOption->keyBy('id')) }},
    selectedBa: {
        id: '',
        jenis: '',
        sumber: 'upload',
        nomor_surat: '',
        tanggal: '',
        keterangan: '',
        aset_nama: '',
        aset_kode: '',
        pj: '',
        pihak_pertama: '',
        pihak_kedua: '',
        preview_url: '',
        download_pdf_url: '',
        file_nama: ''
    },
    genBaForm: {
        nomor_surat: '555/KOMINFO-BB/BEN-BAR/BAP/2/IX/{{ date('Y') }}',
        aset_id: '',
        tanggal: '{{ date('Y-m-d') }}',
        jumlah_barang: '',
        keperluan: 'pekerjaan',
        mengetahui_nama: '{{ addslashes($pejabatKadis->nama ?? '') }}',
        mengetahui_nip: '{{ addslashes($pejabatKadis->nip ?? '') }}',
        mengetahui_jabatan: '{{ addslashes($pejabatKadis->jabatan ?? '') }}',
        tempat_tanda_tangan: 'Suwawa',
        keterangan: ''
    },
    pegawaiList: {{ json_encode($pegawaiList ?? []) }},
    p1SelectedId: '',
    p1Nama: '',
    p1Nip: '',
    p1Jabatan: '',
    p1Search: '',
    p1Open: false,

    p2SelectedId: '',
    p2Nama: '',
    p2Nip: '',
    p2Jabatan: '',
    p2Search: '',
    p2Open: false,

    cleanNameForMatch(str) {
        if (!str) return '';
        let base = str.split(',')[0] || '';
        return base.toLowerCase().replace(/\s+/g, ' ').trim();
    },

    get p1DisplayLabel() {
        if (!this.p1SelectedId) return '-- Pilih Nama --';
        if (this.p1SelectedId === 'manual') return 'Lainnya (isi manual)';
        const found = this.pegawaiList.find(p => String(p.id) === String(this.p1SelectedId));
        return found ? found.nama : '-- Pilih Nama --';
    },

    get p2DisplayLabel() {
        if (!this.p2SelectedId) return '-- Pilih Nama --';
        if (this.p2SelectedId === 'manual') return 'Lainnya (isi manual)';
        const found = this.pegawaiList.find(p => String(p.id) === String(this.p2SelectedId));
        return found ? found.nama : '-- Pilih Nama --';
    },

    get filteredPegawai1() {
        if (!this.p1Search) return this.pegawaiList;
        const s = this.p1Search.toLowerCase();
        return this.pegawaiList.filter(p => (p.nama && p.nama.toLowerCase().includes(s)) || (p.jabatan && p.jabatan.toLowerCase().includes(s)));
    },

    get filteredPegawai2() {
        if (!this.p2Search) return this.pegawaiList;
        const s = this.p2Search.toLowerCase();
        return this.pegawaiList.filter(p => (p.nama && p.nama.toLowerCase().includes(s)) || (p.jabatan && p.jabatan.toLowerCase().includes(s)));
    },

    selectPihakPertama(val) {
        this.p1SelectedId = val;
        this.p1Open = false;
        this.p1Search = '';
        if (val === 'manual') {
            this.p1Nama = '';
            this.p1Nip = '';
            this.p1Jabatan = '';
        } else {
            const found = this.pegawaiList.find(p => String(p.id) === String(val));
            if (found) {
                this.p1Nama = found.nama;
                this.p1Nip = found.nip || '-';
                this.p1Jabatan = found.jabatan;
            } else {
                this.p1Nama = '';
                this.p1Nip = '';
                this.p1Jabatan = '';
            }
        }
    },

    selectPihakKedua(val) {
        this.p2SelectedId = val;
        this.p2Open = false;
        this.p2Search = '';
        if (val === 'manual') {
            this.p2Nama = '';
            this.p2Nip = '';
            this.p2Jabatan = '';
        } else {
            const found = this.pegawaiList.find(p => String(p.id) === String(val));
            if (found) {
                this.p2Nama = found.nama;
                this.p2Nip = found.nip || '-';
                this.p2Jabatan = found.jabatan;
            } else {
                this.p2Nama = '';
                this.p2Nip = '';
                this.p2Jabatan = '';
            }
        }
    },

    onSelectAsetForGenerate(asetId) {
        if (!asetId || !this.asetMap[asetId]) return;
        const item = this.asetMap[asetId];
        this.genBaForm.aset_id = asetId;

        // Auto-select Pihak Kedua jika nama penanggung jawab cocok
        if (item.penanggung_jawab) {
            const targetClean = this.cleanNameForMatch(item.penanggung_jawab);
            const matched = this.pegawaiList.find(p => this.cleanNameForMatch(p.nama) === targetClean);
            if (matched) {
                this.selectPihakKedua(matched.id);
            } else {
                this.p2SelectedId = '';
                this.p2Nama = '';
                this.p2Nip = '';
                this.p2Jabatan = '';
            }
        } else {
            this.p2SelectedId = '';
            this.p2Nama = '';
            this.p2Nip = '';
            this.p2Jabatan = '';
        }

        const merekWords = (item.merek_tipe || '').trim().split(/\s+/);
        const firstMerekWord = merekWords[0] || '';
        let namaBarang = item.jenis_barang || 'Barang';
        if (firstMerekWord && !namaBarang.toLowerCase().includes(firstMerekWord.toLowerCase())) {
            namaBarang += ' ' + firstMerekWord;
        }
        this.genBaForm.jumlah_barang = '1 Unit ' + namaBarang;
    },
    editItem: {}, 
    detailItem: {},
    qrItem: {}, 
    kondisiStandar: {{ json_encode(config('aset.kondisi_standar')) }},
    addKondisi: 'Baik',
    addPersentase: 100,
    onAddKondisiChange(val) {
        this.addKondisi = val;
        if (this.kondisiStandar[val]) {
            this.addPersentase = this.kondisiStandar[val].default;
        }
    },
    onEditKondisiChange(val) {
        this.editItem.kondisi = val;
        if (this.kondisiStandar[val]) {
            this.editItem.persentase_kondisi = this.kondisiStandar[val].default;
        }
    },
    addFotoPreview: null,
    editFotoPreview: null,
    hapusFotoEdit: false,
    nomorWarning: '',
    uploadNomorSurat: '',
    uploadNomorWarning: '',
    previewAddFoto(e) {
        const file = e.target.files[0];
        if (file) {
            this.addFotoPreview = URL.createObjectURL(file);
        } else {
            this.addFotoPreview = null;
        }
    },
    clearAddFoto() {
        this.addFotoPreview = null;
        const el = document.getElementById('add_foto_input');
        if (el) el.value = '';
    },
    previewEditFoto(e) {
        const file = e.target.files[0];
        if (file) {
            this.editFotoPreview = URL.createObjectURL(file);
            this.hapusFotoEdit = false;
        } else {
            this.editFotoPreview = null;
        }
    },
    clearEditFoto() {
        this.editFotoPreview = null;
        const el = document.getElementById('edit_foto_input');
        if (el) el.value = '';
    },
    checkNomorSurat() {
        const no = (this.genBaForm.nomor_surat || '').trim();
        if (!no) {
            this.nomorWarning = '';
            return;
        }
        fetch('{{ route('admin.berita_acara.check_nomor') }}?nomor=' + encodeURIComponent(no))
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    this.nomorWarning = data.message;
                } else {
                    this.nomorWarning = '';
                }
            })
            .catch(err => {
                console.error('Check nomor BA error:', err);
            });
    },
    checkUploadNomorSurat(val) {
        const no = (val !== undefined ? val : this.uploadNomorSurat || '').trim();
        if (!no) {
            this.uploadNomorWarning = '';
            return;
        }
        fetch('{{ route('admin.berita_acara.check_nomor') }}?nomor=' + encodeURIComponent(no))
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    this.uploadNomorWarning = data.message;
                } else {
                    this.uploadNomorWarning = '';
                }
            })
            .catch(err => {
                console.error('Check nomor BA error:', err);
            });
    },
    filterKondisi: '{{ request('kondisi', 'all') }}',
    filterStatusBast: '{{ request('status_bast', 'all') }}',
    getLaporanExportPdfUrl() {
        const url = new URL('{{ route('admin.aset.laporan.pdf') }}', window.location.origin);
        const form = document.getElementById('laporanFilterForm');
        
        const bulan = form ? form.querySelector('[name=filter_bulan]')?.value : '{{ $filterBulan ?? 'all' }}';
        if (bulan && bulan !== 'all') {
            url.searchParams.set('filter_bulan', bulan);
        }
        
        const tahun = form ? form.querySelector('[name=filter_tahun]')?.value : '{{ $filterTahun ?? date('Y') }}';
        if (tahun && tahun !== 'all') {
            url.searchParams.set('filter_tahun', tahun);
        }

        if (this.filterKondisi && this.filterKondisi !== 'all') {
            url.searchParams.set('kondisi', this.filterKondisi);
        }

        return url.toString();
    }
}">

    <div class="min-h-screen flex">
        <!-- Sidebar Navigation Terpadu Admin Panel -->
        @include('components.sidebar-admin', ['active' => 'aset', 'subActive' => $currentTab ?? 'master'])

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-x-hidden">
            <header class="bg-header-gradient px-6 py-4 flex items-center justify-between shadow-xs border-b border-slate-200">
                <div>
                    <h1 class="text-xl font-black text-[#172554] tracking-tight">
                        @if(($currentTab ?? '') === 'berita-acara')
                            Berita Acara Serah Terima (BAST)
                        @elseif(($currentTab ?? '') === 'laporan')
                            Laporan Rekap Bulanan Aset
                        @else
                            Manajemen Aset & Peralatan Mesin
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        @if(($currentTab ?? '') === 'berita-acara')
                            Administrasi nomor SK BAST, naskah pinjam pakai, dan pejabat pemegang aset dinas.
                        @elseif(($currentTab ?? '') === 'laporan')
                            Statistik kondisi barang, visualisasi data, dan rekapitulasi aset per periode bulanan.
                        @else
                            Kelola data seluruh aset, generate label QR code dinamis, dan perbarui data perolehan.
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('account.security') }}" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-[#172554] border border-slate-200 rounded-xl text-xs font-bold transition flex items-center space-x-2 shadow-2xs">
                        <i class="fa-solid fa-shield-halved text-[#198754]"></i>
                        <span class="hidden sm:inline">Keamanan Akun</span>
                    </a>
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/logo-pemda-transparent.png') }}" alt="Pemda Bone Bolango" class="h-8 w-auto mix-blend-multiply">
                        <img src="{{ asset('images/logo-kominfo-transparent.png') }}" alt="Kominfo Bone Bolango" class="h-8 w-auto mix-blend-multiply">
                    </div>
                </div>
            </header>

            <div class="p-6 space-y-6 flex-1">
                
                @if(session('success'))
                    <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl text-sm space-y-1 shadow-sm">
                        <div class="flex items-center space-x-2 font-bold">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                            <span>Terdapat Kesalahan Input:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs pl-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- TAB 1: MASTER DATA ASET -->
                <div x-show="activeTab === 'master'" class="bg-card-gradient rounded-3xl p-6 space-y-4">
                    <div class="flex justify-between items-center flex-wrap gap-2 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-[#172554] text-base flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center">
                                    <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                </div>
                                <span>Pendataan Aset Peralatan dan Mesin Diskominfo</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Kelola data seluruh aset, generate QR code, dan perbarui data perolehan.</p>
                        </div>
                        <!-- Tombol Tambah Aset Baru -->
                        <button @click="showAddModal = true; addKondisi = 'Baik'; addPersentase = 100; addFotoPreview = null; clearAddFoto();" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs px-5 py-2.5 rounded-full font-bold flex items-center space-x-2 shadow-md transition transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Aset Baru</span>
                        </button>
                    </div>

                    <!-- Toolbar Filter & Pencarian Master Data Aset -->
                    <form action="{{ route('admin.aset') }}" method="GET" class="bg-white/90 p-3 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-2">
                        <input type="hidden" name="tab" value="master">
                        <!-- Input Kata Kunci Pencarian -->
                        <div class="relative flex-1 min-w-[200px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Reg, Jenis, Merek, Penanggung Jawab, Lokasi..." class="w-full text-xs pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-full focus:ring-2 focus:ring-[#198754] focus:bg-white text-slate-700 font-medium">
                        </div>

                        <!-- Filter Kategori / Jenis Barang -->
                        <select name="kategori" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#198754] font-medium text-slate-700 max-w-[160px]">
                            <option value="">Semua Kategori</option>
                            @foreach($listKategori as $kat)
                                <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>

                        <!-- Filter Kondisi -->
                        <select name="kondisi" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#198754] font-medium text-slate-700">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ request('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ request('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            <option value="Hilang" {{ request('kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                        </select>

                        <!-- Filter Lokasi -->
                        <select name="lokasi" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#198754] font-medium text-slate-700 max-w-[160px]">
                            <option value="">Semua Lokasi</option>
                            @foreach($listLokasi as $lok)
                                <option value="{{ $lok }}" {{ request('lokasi') == $lok ? 'selected' : '' }}>{{ $lok }}</option>
                            @endforeach
                        </select>

                        <!-- Filter Tahun -->
                        <select name="tahun" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#198754] font-medium text-slate-700">
                            <option value="">Semua Tahun</option>
                            @foreach($listTahun as $th)
                                <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                            @endforeach
                        </select>

                        <!-- Tombol Terapkan Filter & Reset -->
                        <button type="submit" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs px-4 py-2 rounded-full font-bold flex items-center space-x-1.5 shadow-sm transition">
                            <i class="fa-solid fa-filter"></i>
                            <span>Filter</span>
                        </button>

                        @if(request()->hasAny(['search', 'kategori', 'kondisi', 'lokasi', 'tahun']))
                            <a href="{{ route('admin.aset') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-3 py-2 rounded-full hover:bg-rose-50 transition flex items-center space-x-1" title="Reset filter">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Reset</span>
                            </a>
                        @endif
                    </form>

                    <!-- Tabel Master Data Aset Wrapper dengan Gradasi & Soft Shadow -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 shadow-sm bg-white/80 backdrop-blur-md">
                        <table class="w-full text-left text-[11px] text-slate-600 whitespace-nowrap">
                            <thead class="bg-slate-100/95 text-[#172554] uppercase font-black border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-3.5 text-center">No</th>
                                    <th class="px-3 py-3.5">Penanggung Jawab</th>
                                    <th class="px-3 py-3.5">Jenis Barang</th>
                                    <th class="px-3 py-3.5">No Reg/ID Pemda</th>
                                    <th class="px-3 py-3.5">No Reg KOMINFO</th>
                                    <th class="px-3 py-3.5">Merek/Tipe</th>
                                    <th class="px-3 py-3.5">Tahun</th>
                                    <th class="px-3 py-3.5">Harga Perolehan (Rp)</th>
                                    <th class="px-3 py-3.5">Nomor Rangka/Seri</th>
                                    <th class="px-3 py-3.5">Nomor Mesin</th>
                                    <th class="px-3 py-3.5">Nomor Polisi</th>
                                    <th class="px-3 py-3.5">Nomor BPKB</th>
                                    <th class="px-3 py-3.5">Kondisi Aset</th>
                                    <th class="px-3 py-3.5">Keterangan Unit Lokasi</th>
                                    <th class="px-3 py-3.5">No. SK/BAST/Pinjam Pakai</th>
                                    <th class="px-3 py-3.5 text-center">QR Code & Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white/90">
                                @forelse($aset as $index => $item)
                                @php
                                    $badgeColor = match($item->kondisi ?? 'Baik') {
                                        'Baik' => 'bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]',
                                        'Rusak Ringan' => 'bg-[#FEF9C3] text-[#854D0E] border border-[#FDE047]',
                                        'Rusak Berat' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]',
                                        'Hilang' => 'bg-[#F1F5F9] text-[#334155] border border-[#CBD5E1]',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                    $regId = $item->no_reg_pemda ?: '-';
                                    $regKominfo = $item->no_reg_kominfo ?: '-';
                                    $itemId = $item->id;
                                @endphp
                                <tr class="hover:bg-[#D1E7DD]/30 transition">
                                    <td class="px-3 py-3 text-center font-medium">{{ $aset->firstItem() + $index }}</td>
                                    <td class="px-3 py-3 font-bold text-[#172554]">{{ $item->penanggung_jawab ?? '-' }}</td>
                                    <td class="px-3 py-3 font-semibold text-slate-800">{{ $item->jenis_barang ?? '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-[#198754] font-bold">{{ $regId }}</td>
                                    <td class="px-3 py-3 font-mono text-emerald-600 font-bold">{{ $regKominfo }}</td>
                                    <td class="px-3 py-3">{{ $item->merek_tipe ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $item->tahun ?? '-' }}</td>
                                    <td class="px-3 py-3 font-mono font-semibold">Rp {{ number_format($item->harga_perolehan ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_rangka_seri ?: '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_mesin ?: '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_polisi ?: '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_bpkb ?: '-' }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $badgeColor }}">
                                            {{ $item->kondisi ?? 'Baik' }} ({{ $item->persentase_kondisi ?? 100 }}%)
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">{{ $item->keterangan_lokasi_unit ?? '-' }}</td>
                                    @php
                                        $baList = $item->beritaAcaras;
                                        $hasBa = $baList && $baList->isNotEmpty();
                                        $latestBa = $hasBa ? $baList->first() : null;
                                        $extraBaCount = $hasBa ? ($baList->count() - 1) : 0;
                                    @endphp
                                    <td class="px-3 py-3">
                                        @if($hasBa && $latestBa)
                                            @php
                                                $displayNomor = $latestBa->display_nomor;
                                                $fullTitle = $displayNomor . ($extraBaCount > 0 ? " (+{$extraBaCount} berkas lainnya)" : '');
                                                $fileUrl = route('admin.berita_acara.file', $latestBa->id, false);
                                            @endphp
                                            <div class="flex items-center space-x-1.5 max-w-[200px]" title="{{ $fullTitle }}">
                                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-[#146C43] hover:text-[#0F5132] font-mono font-bold hover:underline truncate text-xs inline-flex items-center space-x-1">
                                                    <i class="fa-solid fa-file-pdf text-[11px] shrink-0 text-[#146C43]"></i>
                                                    <span class="truncate">{{ $displayNomor }}</span>
                                                </a>
                                                @if($extraBaCount > 0)
                                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-[#146C43] shrink-0" title="Ada {{ $extraBaCount }} berkas BAST lainnya">+{{ $extraBaCount }}</span>
                                                @endif
                                            </div>
                                        @elseif(!empty($item->no_sk_bast))
                                            <span class="font-mono text-xs text-slate-700 truncate max-w-[180px] inline-block" title="{{ $item->no_sk_bast }}">{{ $item->no_sk_bast }}</span>
                                        @else
                                            <span class="text-slate-400 font-semibold">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <!-- Detail & Foto Internal Trigger -->
                                            <button type="button" @click="showDetailModal = true; detailItem = {{ json_encode($item) }};" class="bg-[#D1E7DD] hover:bg-[#198754] text-[#0F5132] hover:text-white font-extrabold px-2.5 py-1 rounded-full transition flex items-center space-x-1 text-[10px] shadow-2xs" title="Lihat Detail & Foto Aset">
                                                <i class="fa-solid fa-eye"></i>
                                                <span>Detail</span>
                                            </button>
                                            <!-- QR Modal Trigger -->
                                            <button @click="showQrModal = true; qrItem = { id: '{{ addslashes($regId) }}', kominfo: '{{ addslashes($regKominfo) }}', jenis: '{{ addslashes($item->jenis_barang ?? '-') }}', penanggung: '{{ addslashes($item->penanggung_jawab ?? '-') }}', merek: '{{ addslashes($item->merek_tipe ?? '-') }}', url: '{{ $item->public_url }}' }" class="bg-pink-100 hover:bg-[#EC4899] text-[#EC4899] hover:text-white font-extrabold px-3 py-1 rounded-full transition flex items-center space-x-1 text-[10px] shadow-sm">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>QR</span>
                                            </button>
                                            <!-- Edit Modal Trigger -->
                                            <button @click="showEditModal = true; editItem = Object.assign({}, {{ json_encode($item) }}); if (editItem.persentase_kondisi === undefined || editItem.persentase_kondisi === null) { editItem.persentase_kondisi = (kondisiStandar[editItem.kondisi]?.default ?? 100); } hapusFotoEdit = false; editFotoPreview = null; formatEditHarga();" class="text-[#198754] hover:text-[#0F5132] font-bold px-2 py-1 rounded-full hover:bg-[#D1E7DD]/50 transition" title="Edit Aset">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <!-- Delete Form Trigger -->
                                            <form action="{{ route('admin.aset.destroy', ['no_reg_pemda' => $itemId]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-full hover:bg-rose-50 transition" title="Hapus Aset">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="px-4 py-8 text-center text-slate-400">Belum ada data aset terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Navigasi Pagination Laravel -->
                    <div class="pt-2">
                        {{ $aset->appends(request()->query())->links() }}
                    </div>
                </div>

                <!-- TAB 2: BERITA ACARA (POSISI DI TENGAH) -->
                <div x-show="activeTab === 'berita-acara'" x-cloak class="bg-card-gradient rounded-3xl p-6 space-y-6">
                    <div class="flex justify-between items-center flex-wrap gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="font-extrabold text-[#172554] text-base flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center">
                                    <i class="fa-solid fa-file-contract text-xs"></i>
                                </div>
                                <span>Manajemen & Arsip Berita Acara</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Pembuatan dokumen resmi BAST (PDF) secara otomatis serta pengarsipan berkas scan fisik.</p>
                        </div>
                        <button type="button" @click="showUploadBaModal = true" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs px-5 py-2.5 rounded-full font-bold flex items-center space-x-2 shadow-md transition transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-file-circle-plus"></i>
                            <span>+ Tambah Berita Acara</span>
                        </button>
                    </div>

                    <!-- Summary Stat Cards Berita Acara -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white/90 p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center text-base shrink-0">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Dokumen</p>
                                <h4 class="text-xl font-black text-[#172554]">{{ $rekapBast['total_ba'] ?? 0 }} Berkas</h4>
                            </div>
                        </div>

                        <div class="bg-white/90 p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center text-base shrink-0">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hasil Generate</p>
                                <h4 class="text-xl font-black text-[#0F5132]">{{ $rekapBast['total_generate'] ?? 0 }} Dokumen</h4>
                            </div>
                        </div>

                        <div class="bg-white/90 p-4 rounded-2xl border border-emerald-100 shadow-xs flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base shrink-0">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Scan Fisik</p>
                                <h4 class="text-xl font-black text-emerald-600">{{ $rekapBast['total_upload'] ?? 0 }} Berkas</h4>
                            </div>
                        </div>

                        <div class="bg-white/90 p-4 rounded-2xl border border-purple-100 shadow-xs flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-base shrink-0">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Aset Terkait</p>
                                <h4 class="text-xl font-black text-purple-600">{{ $rekapBast['total_aset_terkait'] ?? 0 }} Aset</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar Filter & Pencarian Berita Acara -->
                    <form action="{{ route('admin.aset.berita_acara') }}" method="GET" class="bg-white/90 p-3 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-2">
                        <input type="hidden" name="tab" value="berita-acara">
                        
                        <!-- Input Kata Kunci Pencarian -->
                        <div class="relative flex-1 min-w-[200px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Surat, Jenis, Nama Aset, Pihak Terlibat, Keterangan..." class="w-full text-xs pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-full focus:ring-2 focus:ring-[#198754] focus:bg-white text-slate-700 font-medium">
                        </div>

                        <!-- Filter Jenis Berita Acara -->
                        <select name="jenis_ba" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#198754] font-medium text-slate-700">
                            <option value="all">Semua Jenis BA</option>
                            <option value="Berita Acara Serah Terima (BAST)" {{ request('jenis_ba') === 'Berita Acara Serah Terima (BAST)' ? 'selected' : '' }}>Berita Acara Serah Terima (BAST)</option>
                            <option value="Berita Acara Penerimaan Barang" {{ request('jenis_ba') === 'Berita Acara Penerimaan Barang' ? 'selected' : '' }}>Berita Acara Penerimaan Barang</option>
                        </select>

                        <!-- Filter Sumber Dokumen -->
                        <select name="sumber_ba" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#198754] font-medium text-slate-700">
                            <option value="all">Semua Mode</option>
                            <option value="generate" {{ request('sumber_ba') === 'generate' ? 'selected' : '' }}>Hasil Generate (PDF)</option>
                            <option value="upload" {{ request('sumber_ba') === 'upload' ? 'selected' : '' }}>Upload Scan Fisik</option>
                        </select>

                        <!-- Tombol Cari -->
                        <button type="submit" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs px-4 py-2 rounded-full font-bold flex items-center space-x-1.5 shadow-sm transition">
                            <i class="fa-solid fa-filter"></i>
                            <span>Filter</span>
                        </button>

                        @if(request()->hasAny(['search', 'jenis_ba', 'sumber_ba']))
                        <a href="{{ route('admin.aset.berita_acara') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs px-3 py-2 rounded-full font-semibold transition flex items-center space-x-1">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                            <span>Reset</span>
                        </a>
                        @endif
                    </form>

                    <!-- Tabel Daftar Berita Acara (Terpadu: Generate & Upload) -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white/95">
                        <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                            <thead class="bg-slate-100/90 text-[#172554] uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5 text-center">No</th>
                                    <th class="px-4 py-3.5">Tanggal</th>
                                    <th class="px-4 py-3.5">Dokumen & Nomor</th>
                                    <th class="px-4 py-3.5">Aset Terkait</th>
                                    <th class="px-4 py-3.5">Pihak Terlibat</th>
                                    <th class="px-4 py-3.5">Format Berkas</th>
                                    <th class="px-4 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($beritaAcaras as $index => $item)
                                @php
                                    $isGenerate = ($item->sumber === 'generate');
                                    $isBast = str_contains($item->jenis, 'BAST') || str_contains($item->jenis, 'Serah Terima');
                                @endphp
                                <tr class="hover:bg-[#D1E7DD]/30 transition">
                                    <td class="px-4 py-3.5 text-center font-medium">{{ $beritaAcaras->firstItem() + $index }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-800">
                                        <i class="fa-regular fa-calendar-days text-[#198754] mr-1.5"></i>
                                        <span>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="space-y-1">
                                            @if($isGenerate)
                                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#D1E7DD] text-[#0F5132] border border-[#A3CFBB]">
                                                    <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                                                    <span>BAST Resmi (Generate)</span>
                                                </span>
                                                <div class="font-mono text-xs font-bold text-[#172554]">
                                                    {{ $item->nomor_surat ?: 'Nomor Belum Diatur' }}
                                                </div>
                                            @else
                                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                    <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                                                    <span>{{ $item->jenis }}</span>
                                                </span>
                                                <div class="text-[11px] text-slate-400 italic">Scan Dokumen Fisik</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($item->aset)
                                            <div class="font-bold text-[#172554]">{{ $item->aset->jenis_barang }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $item->aset->merek_tipe }}</div>
                                            <div class="flex items-center space-x-2 text-[10px] font-mono mt-0.5">
                                                @if($item->aset->no_reg_pemda)
                                                    <span class="text-[#198754] font-semibold">Pemda: {{ $item->aset->no_reg_pemda }}</span>
                                                @endif
                                                @if($item->aset->no_reg_kominfo)
                                                    <span class="text-emerald-600 font-semibold">Kominfo: {{ $item->aset->no_reg_kominfo }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Aset telah dihapus</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-xs">
                                        @if($isGenerate)
                                            <div class="space-y-0.5">
                                                <div class="text-[11px] text-slate-700">
                                                    <span class="text-slate-400 font-medium">Dari:</span> <strong>{{ $item->pihak_pertama_nama ?: '-' }}</strong>
                                                </div>
                                                <div class="text-[11px] text-slate-700">
                                                    <span class="text-slate-400 font-medium">Kepada:</span> <strong class="text-[#0F5132]">{{ $item->pihak_kedua_nama ?: ($item->aset->penanggung_jawab ?? '-') }}</strong>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-slate-600 max-w-[200px] truncate" title="{{ $item->keterangan }}">
                                                <span class="font-medium text-slate-700">PJ:</span> {{ $item->aset->penanggung_jawab ?? '-' }}
                                                @if($item->keterangan)
                                                    <div class="text-[11px] text-slate-400 truncate">{{ $item->keterangan }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($isGenerate)
                                            <div class="flex items-center space-x-1.5">
                                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                    <span>PDF Resmi</span>
                                                </span>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                                <span class="font-medium text-slate-700 max-w-[130px] truncate" title="{{ $item->file_nama_asli }}">{{ $item->file_nama_asli ?: 'scan.pdf' }}</span>
                                                <span class="text-[10px] text-slate-400">({{ $item->formatted_file_size }})</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <!-- HANYA TOMBOL LIHAT (View-Only Konsisten, Buka Preview & Opsi Download Word/PDF) -->
                                        <button type="button" 
                                                @click="selectedBa = {
                                                    id: '{{ $item->id }}',
                                                    jenis: '{{ addslashes($item->jenis) }}',
                                                    sumber: '{{ $item->sumber ?? 'upload' }}',
                                                    nomor_surat: '{{ addslashes($item->nomor_surat ?? '-') }}',
                                                    tanggal: '{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}',
                                                    keterangan: '{{ addslashes($item->keterangan ?? '-') }}',
                                                    aset_nama: '{{ addslashes(($item->aset->jenis_barang ?? 'Aset') . ($item->aset->merek_tipe ? ' - ' . $item->aset->merek_tipe : '')) }}',
                                                    aset_kode: '{{ addslashes($item->aset->no_reg_pemda ?? ($item->aset->no_reg_kominfo ?? '-')) }}',
                                                    pj: '{{ addslashes($item->aset->penanggung_jawab ?? '-') }}',
                                                    pihak_pertama: '{{ addslashes($item->pihak_pertama_nama ?? '-') }}',
                                                    pihak_kedua: '{{ addslashes($item->pihak_kedua_nama ?? ($item->aset->penanggung_jawab ?? '-')) }}',
                                                    preview_url: '{{ route('admin.berita_acara.file', $item->id, false) }}',
                                                    download_pdf_url: '{{ route('berita_acara.download_pdf', $item->id) }}',
                                                    file_nama: '{{ addslashes($item->file_nama_asli ?? 'Dokumen.pdf') }}'
                                                }; showPreviewBaModal = true;"
                                                title="Lihat Dokumen & Download" 
                                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-[#D1E7DD] text-[#0F5132] hover:bg-[#198754] hover:text-white transition font-bold text-xs shadow-2xs">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <i class="fa-solid fa-file-circle-xmark text-3xl text-slate-300"></i>
                                            <p class="font-medium text-xs">Belum ada dokumen Berita Acara yang tersimpan.</p>
                                            <button type="button" @click="showUploadBaModal = true" class="text-xs text-[#198754] hover:underline font-bold">
                                                + Tambah Berita Acara Sekarang
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Navigasi Pagination Laravel -->
                    <div class="pt-2">
                        {{ $beritaAcaras->appends(request()->query())->links() }}
                    </div>
                </div>

                <!-- TAB 3: LAPORAN REKAP BULANAN -->
                <div x-show="activeTab === 'laporan'" x-cloak class="bg-card-gradient rounded-3xl p-6 space-y-6">
                    <!-- Header Judul & Sub Judul (Lebar Penuh) -->
                    <div class="space-y-1">
                        <h3 class="font-black text-[#172554] text-lg sm:text-xl">Laporan Rekap Kondisi Barang Bulanan</h3>
                        <p class="text-xs text-slate-500">Ringkasan statistik, diagram visualisasi data, dan rekapitulasi aset berdasarkan periode bulan/tahun.</p>
                    </div>

                    <!-- Filter Form & Export PDF (Diletakkan di Bawah Judul) -->
                    <form id="laporanFilterForm" action="{{ route('admin.aset.laporan') }}" method="GET" class="flex flex-wrap items-center gap-3 bg-slate-50/90 p-3 sm:p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                        <input type="hidden" name="tab" value="laporan">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold text-slate-600 flex items-center space-x-1.5">
                                <i class="fa-regular fa-calendar text-[#198754]"></i>
                                <span class="hidden sm:inline">Periode:</span>
                            </span>
                            <select name="filter_bulan" class="text-xs border border-slate-300 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-[#198754] font-semibold text-[#172554] shadow-xs cursor-pointer">
                                <option value="all" {{ $filterBulan === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                                <option value="1" {{ $filterBulan == '1' ? 'selected' : '' }}>Januari</option>
                                <option value="2" {{ $filterBulan == '2' ? 'selected' : '' }}>Februari</option>
                                <option value="3" {{ $filterBulan == '3' ? 'selected' : '' }}>Maret</option>
                                <option value="4" {{ $filterBulan == '4' ? 'selected' : '' }}>April</option>
                                <option value="5" {{ $filterBulan == '5' ? 'selected' : '' }}>Mei</option>
                                <option value="6" {{ $filterBulan == '6' ? 'selected' : '' }}>Juni</option>
                                <option value="7" {{ $filterBulan == '7' ? 'selected' : '' }}>Juli</option>
                                <option value="8" {{ $filterBulan == '8' ? 'selected' : '' }}>Agustus</option>
                                <option value="9" {{ $filterBulan == '9' ? 'selected' : '' }}>September</option>
                                <option value="10" {{ $filterBulan == '10' ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{ $filterBulan == '11' ? 'selected' : '' }}>November</option>
                                <option value="12" {{ $filterBulan == '12' ? 'selected' : '' }}>Desember</option>
                            </select>
                        </div>
                        <div>
                            <select name="filter_tahun" class="text-xs border border-slate-300 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-[#198754] font-semibold text-[#172554] shadow-xs cursor-pointer">
                                <option value="all" {{ $filterTahun === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $filterTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <input type="hidden" name="kondisi" :value="filterKondisi">
                        <button type="submit" class="bg-[#146C43] hover:bg-[#0F5132] text-white text-xs px-4 py-2 rounded-xl font-bold flex items-center space-x-1.5 shadow-sm transition">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            <span>Filter</span>
                        </button>
                        <a :href="getLaporanExportPdfUrl()" target="_blank" class="bg-[#F59E0B] hover:bg-[#D97706] text-[#172554] text-xs px-4 py-2 rounded-xl font-black flex items-center space-x-1.5 shadow-sm transition sm:ml-auto">
                            <i class="fa-solid fa-file-pdf text-[11px]"></i>
                            <span>Export PDF</span>
                        </a>
                    </form>

                    <!-- Interactive Visual Summary Cards (Filter Aktif, Judul & Jumlah Saja) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div @click="filterKondisi = 'all'" :class="filterKondisi === 'all' ? 'ring-4 ring-[#198754] bg-[#D1E7DD] shadow-lg scale-[1.03] border-[#A3CFBB]' : 'bg-[#D1E7DD]/50 border-[#A3CFBB]/70 hover:bg-[#D1E7DD]'" class="p-5 rounded-3xl border shadow-sm cursor-pointer card-interactive flex flex-col justify-between transition-all duration-200 select-none relative overflow-hidden group">
                            <div class="flex items-center justify-between text-[#0F5132]">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Semua Aset</span>
                                <div class="w-8 h-8 rounded-full bg-white/80 flex items-center justify-center">
                                    <i class="fa-solid fa-boxes-stacked text-sm text-[#0F5132]"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between mt-2">
                                <div>
                                    <p class="text-3xl font-black text-[#172554]">{{ $rekapKondisi['Total'] }}</p>
                                </div>
                                <span x-show="filterKondisi === 'all'" x-cloak class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#198754] text-white shadow-xs">Aktif</span>
                            </div>
                        </div>

                        <div @click="filterKondisi = filterKondisi === 'Baik' ? 'all' : 'Baik'" :class="filterKondisi === 'Baik' ? 'ring-4 ring-emerald-500/80 bg-emerald-100 shadow-lg scale-[1.03] border-emerald-400' : 'bg-[#DCFCE7]/70 border-[#86EFAC] hover:bg-[#DCFCE7]'" class="p-5 rounded-3xl border shadow-sm cursor-pointer card-interactive flex flex-col justify-between transition-all duration-200 select-none relative overflow-hidden group">
                            <div class="flex items-center justify-between text-[#16A34A]">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-[#166534]">Baik</span>
                                <div class="w-8 h-8 rounded-full bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center">
                                    <i class="fa-solid fa-circle-check text-sm"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between mt-2">
                                <div>
                                    <p class="text-3xl font-black text-[#16A34A]">{{ $rekapKondisi['Baik'] }}</p>
                                </div>
                                <span x-show="filterKondisi === 'Baik'" x-cloak class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#16A34A] text-white shadow-xs">Aktif</span>
                            </div>
                        </div>

                        <div @click="filterKondisi = filterKondisi === 'Rusak Ringan' ? 'all' : 'Rusak Ringan'" :class="filterKondisi === 'Rusak Ringan' ? 'ring-4 ring-amber-500/80 bg-amber-100 shadow-lg scale-[1.03] border-amber-400' : 'bg-[#FEF9C3]/70 border-[#FDE047] hover:bg-[#FEF9C3]'" class="p-5 rounded-3xl border shadow-sm cursor-pointer card-interactive flex flex-col justify-between transition-all duration-200 select-none relative overflow-hidden group">
                            <div class="flex items-center justify-between text-[#854D0E]">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-[#854D0E]">Rusak Ringan</span>
                                <div class="w-8 h-8 rounded-full bg-[#FEF9C3] text-[#854D0E] flex items-center justify-center">
                                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between mt-2">
                                <div>
                                    <p class="text-3xl font-black text-[#854D0E]">{{ $rekapKondisi['Rusak Ringan'] }}</p>
                                </div>
                                <span x-show="filterKondisi === 'Rusak Ringan'" x-cloak class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#EAB308] text-white shadow-xs">Aktif</span>
                            </div>
                        </div>

                        <div @click="filterKondisi = filterKondisi === 'Rusak Berat' ? 'all' : 'Rusak Berat'" :class="filterKondisi === 'Rusak Berat' ? 'ring-4 ring-rose-500/80 bg-rose-100 shadow-lg scale-[1.03] border-rose-400' : 'bg-[#FEE2E2]/70 border-[#FCA5A5] hover:bg-[#FEE2E2]'" class="p-5 rounded-3xl border shadow-sm cursor-pointer card-interactive flex flex-col justify-between transition-all duration-200 select-none relative overflow-hidden group">
                            <div class="flex items-center justify-between text-[#DC2626]">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-[#991B1B]">Rusak Berat</span>
                                <div class="w-8 h-8 rounded-full bg-[#FEE2E2] text-[#DC2626] flex items-center justify-center">
                                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between mt-2">
                                <div>
                                    <p class="text-3xl font-black text-[#DC2626]">{{ $rekapKondisi['Rusak Berat'] }}</p>
                                </div>
                                <span x-show="filterKondisi === 'Rusak Berat'" x-cloak class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#DC2626] text-white shadow-xs">Aktif</span>
                            </div>
                        </div>

                        <div @click="filterKondisi = filterKondisi === 'Hilang' ? 'all' : 'Hilang'" :class="filterKondisi === 'Hilang' ? 'ring-4 ring-slate-500/80 bg-slate-200 shadow-lg scale-[1.03] border-slate-400' : 'bg-[#F1F5F9]/80 border-[#CBD5E1] hover:bg-[#F1F5F9]'" class="p-5 rounded-3xl border shadow-sm cursor-pointer card-interactive flex flex-col justify-between col-span-2 sm:col-span-1 transition-all duration-200 select-none relative overflow-hidden group">
                            <div class="flex items-center justify-between text-[#64748B]">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-[#334155]">Hilang</span>
                                <div class="w-8 h-8 rounded-full bg-[#F1F5F9] text-[#64748B] flex items-center justify-center">
                                    <i class="fa-solid fa-ghost text-sm"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between mt-2">
                                <div>
                                    <p class="text-3xl font-black text-[#64748B]">{{ $rekapKondisi['Hilang'] }}</p>
                                </div>
                                <span x-show="filterKondisi === 'Hilang'" x-cloak class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#64748B] text-white shadow-xs">Aktif</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Statistics Chart Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                        <div class="bg-white/90 p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h4 class="font-extrabold text-[#172554] text-sm flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center">
                                        <i class="fa-solid fa-chart-pie text-xs"></i>
                                    </div>
                                    <span>Komposisi Kondisi Aset</span>
                                </h4>
                                <span class="text-[11px] text-slate-400 font-semibold">Statistik Visual</span>
                            </div>
                            <div class="relative h-60 flex items-center justify-center">
                                <canvas id="kondisiDoughnutChart"></canvas>
                            </div>
                        </div>

                        <div class="bg-white/90 p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h4 class="font-extrabold text-[#172554] text-sm flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-pink-100 text-[#EC4899] flex items-center justify-center">
                                        <i class="fa-solid fa-chart-column text-xs"></i>
                                    </div>
                                    <span>Grafik Rekapitulasi Jumlah Unit</span>
                                </h4>
                                <span class="text-[11px] text-slate-400 font-semibold">Statistik Visual</span>
                            </div>
                            <div class="relative h-60">
                                <canvas id="kondisiBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- MODAL QR CODE ASET (Preview & Cetak Sederhana Sesuai Referensi Desain) -->
    <div x-show="showQrModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-card-gradient rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center space-y-4 border border-slate-200 relative my-auto max-h-[calc(100vh-2rem)] sm:max-h-[90vh] overflow-y-auto custom-scrollbar" @click.away="showQrModal = false">
            
            <!-- Header Modal: Ikon Kotak Grid Pink, Judul "QR Code Aset", Tombol Close (×) -->
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 no-print">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-pink-100 text-[#EC4899] flex items-center justify-center shadow-xs">
                        <i class="fa-solid fa-qrcode text-sm"></i>
                    </div>
                    <h3 class="font-black text-[#172554] text-base">QR Code Aset</h3>
                </div>
                <button @click="showQrModal = false" type="button" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-100/60">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Konten Utama Label QR Code yang Dicetak -->
            <div id="printable-qr-label" class="space-y-3.5">
                <!-- QR Code di Tengah dalam Kotak Putih dengan Padding, Rounded Corner, Soft Shadow -->
                <div class="p-4 rounded-3xl bg-slate-50 border border-slate-200 shadow-inner flex items-center justify-center">
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-md inline-block">
                        <template x-if="qrItem.id">
                            <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=4&data=' + encodeURIComponent(qrItem.url)" 
                                 alt="QR Code" 
                                 class="w-44 h-44 mx-auto object-contain">
                        </template>
                    </div>
                </div>

                <!-- Di bawah QR Code: Berurutan dan Rata Tengah -->
                <div class="space-y-1 text-center px-1">
                    <div class="flex items-center justify-center gap-2 flex-wrap text-xs font-bold font-mono">
                        <span class="text-[#198754]" x-text="'Pemda: ' + (qrItem.id || '-')"></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-emerald-600" x-text="'Kominfo: ' + (qrItem.kominfo || '-')"></span>
                    </div>
                    <!-- 2. Nama/Jenis Aset (Hitam, Bold, Ukuran Lebih Besar) -->
                    <h4 class="font-black text-[#172554] text-base leading-snug" x-text="qrItem.jenis"></h4>
                    <!-- 3. Merek/Tipe (Abu-abu, Ukuran Sedang) -->
                    <p class="text-xs font-semibold text-slate-500" x-text="qrItem.merek"></p>
                    <!-- 4. Penanggung Jawab (Abu-abu, Ukuran Sedang) -->
                    <p class="text-xs font-medium text-slate-400" x-text="qrItem.penanggung"></p>
                </div>
            </div>

            <!-- Tombol Aksi (Elemen UI - Sembunyikan saat Cetak) -->
            <div class="pt-2 border-t border-slate-100/80 flex flex-col space-y-2 no-print">
                <a :href="qrItem.url" target="_blank" class="w-full bg-gradient-to-r from-[#198754] to-[#146C43] hover:from-[#146C43] hover:to-[#0F5132] text-white py-2.5 rounded-full font-bold text-xs flex items-center justify-center space-x-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Buka Halaman Detail Publik</span>
                </a>
                <button @click="window.print()" type="button" class="w-full bg-white hover:bg-slate-50 text-slate-700 py-2.5 rounded-full font-bold text-xs flex items-center justify-center space-x-2 transition border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Label QR</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ASET (URUTAN KOLOM FORM PERSIS DENGAN TABEL) -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-modal-gradient rounded-3xl shadow-2xl max-w-2xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col overflow-hidden border border-slate-200 my-auto" @click.away="showAddModal = false">
            <form action="{{ route('admin.aset.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf

                <!-- Header Modal (Tetap / Sticky) -->
                <div class="flex justify-between items-center border-b border-slate-100 p-4 sm:px-6 sm:py-4 shrink-0 bg-white/80">
                    <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </div>
                        <span>Tambah Data Aset Peralatan & Mesin</span>
                    </h3>
                    <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Body Modal (Scrollable Form Fields) -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-2 gap-3.5 custom-scrollbar">
                    <div>
                        <label class="form-label">No Reg / ID Pemda</label>
                        <input type="text" name="no_reg_pemda" placeholder="Contoh: 02.03.01.05.01" class="form-input font-mono">
                    </div>

                    <div>
                        <label class="form-label">No Reg KOMINFO</label>
                        <input type="text" name="no_reg_kominfo" placeholder="Contoh: KOMINFO-2026-001" class="form-input font-mono">
                    </div>

                    <div>
                        <label class="form-label">Penanggung Jawab <span class="text-rose-500">*</span></label>
                        <input type="text" name="penanggung_jawab" placeholder="Contoh: Ahmad Rizky" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Jenis Barang <span class="text-rose-500">*</span></label>
                        <input type="text" name="jenis_barang" placeholder="Contoh: Laptop, AC, Router, Mobil, dll" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Merek / Tipe Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="merek_tipe" placeholder="Contoh: Lenovo ThinkPad / Toyota Avanza" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Tahun Perolehan <span class="text-rose-500">*</span></label>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" name="tahun" placeholder="2026" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Harga Perolehan <span class="text-rose-500">*</span></label>
                        <input type="text" name="harga_perolehan" id="add_harga_perolehan" oninput="formatRupiahInput(this)" placeholder="Contoh: 15.000.000" required class="form-input font-mono">
                    </div>

                    <div>
                        <label class="form-label">Nomor Rangka / Nomor Seri</label>
                        <input type="text" name="no_rangka_seri" placeholder="Isi jika ada (Opsional)" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Nomor Mesin</label>
                        <input type="text" name="no_mesin" placeholder="Isi jika ada (Opsional)" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Nomor Polisi</label>
                        <input type="text" name="no_polisi" placeholder="DM 1234 AB (Opsional)" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Nomor BPKB</label>
                        <input type="text" name="no_bpkb" placeholder="Isi jika kendaraan dinas (Opsional)" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Kondisi Aset <span class="text-rose-500">*</span></label>
                        <div class="flex items-center space-x-2">
                            <select name="kondisi" x-model="addKondisi" @change="onAddKondisiChange($event.target.value)" required class="form-input font-semibold flex-1">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                            <div class="relative w-24 shrink-0">
                                <input type="number" name="persentase_kondisi" x-model.number="addPersentase" :readonly="addKondisi === 'Hilang'" :min="kondisiStandar[addKondisi]?.min ?? 0" :max="kondisiStandar[addKondisi]?.max ?? 100" required class="form-input pr-6 text-center font-mono font-bold no-spin [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" :class="addKondisi === 'Hilang' ? 'bg-slate-100 cursor-not-allowed text-slate-500' : ''">
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500 pointer-events-none">%</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1" x-text="addKondisi === 'Hilang' ? 'Kondisi Hilang terkunci pada 0%' : 'Rentang standar: ' + (kondisiStandar[addKondisi]?.rentang_label || '') + ' (Default: ' + (kondisiStandar[addKondisi]?.default || 0) + '%)'"></p>
                    </div>

                    <div>
                        <label class="form-label">Keterangan Unit Lokasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="keterangan_lokasi_unit" placeholder="Bidang E-Government" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Foto Aset <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <div class="space-y-1.5">
                            <input type="file" name="foto" id="add_foto_input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" 
                                   @change="previewAddFoto($event)" 
                                   class="block w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-[11px] file:font-bold file:bg-[#D1E7DD] file:text-[#0F5132] hover:file:bg-[#A3CFBB] cursor-pointer border border-slate-300 rounded-xl bg-white p-1">
                            <p class="text-[10px] text-slate-400">Format: JPG, JPEG, PNG, WEBP. Maks. 2MB.</p>
                            <template x-if="addFotoPreview">
                                <div class="relative inline-block mt-1">
                                    <img :src="addFotoPreview" class="h-16 w-16 object-cover rounded-xl border border-slate-300 shadow-xs" alt="Preview Foto">
                                    <button type="button" @click="clearAddFoto()" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[10px] shadow" title="Hapus Pratinjau">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="md:col-span-2 border-t border-slate-200/60 pt-2">
                        <label class="form-label">No. SK, BAST, Naskah Perjanjian Pinjam Pakai</label>
                        <input type="text" name="no_sk_bast" placeholder="SK/001/KOMINFO/2026" class="form-input">
                    </div>
                </div>

                <!-- Footer Modal (Tetap / Sticky di Bawah) -->
                <div class="p-3.5 sm:px-6 sm:py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/90 flex justify-end space-x-2">
                    <button type="button" @click="showAddModal = false" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-full font-bold transition text-xs">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-[#146C43] hover:bg-[#0F5132] text-white rounded-full font-bold hover:shadow-lg shadow-md transition text-xs">Simpan Aset Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETAIL ASET INTERNAL (ADMIN MASTER & BENDAHARA BARANG) -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-modal-gradient rounded-3xl shadow-2xl max-w-2xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col overflow-hidden border border-slate-200 my-auto" @click.away="showDetailModal = false">
            <!-- Header Modal -->
            <div class="flex justify-between items-center border-b border-slate-100 p-4 sm:px-6 sm:py-4 shrink-0 bg-white/80">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-circle-info text-sm"></i>
                    </div>
                    <span>Detail Aset & Foto Dokumentasi</span>
                </h3>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Body Modal (Scrollable) -->
            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 custom-scrollbar text-xs">
                <!-- Direct Photo View Section (No full resolution, no link <a>, non-clickable) -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center">
                    <template x-if="detailItem.foto">
                        <div class="flex justify-center">
                            <img :src="'/storage/' + (detailItem.foto || '').replace(/^\/+/, '')" 
                                 class="max-h-60 sm:max-h-64 w-auto max-w-full rounded-xl object-contain border border-slate-200 shadow-sm pointer-events-none select-none" 
                                 alt="Foto Dokumentasi Aset">
                        </div>
                    </template>
                    <template x-if="!detailItem.foto">
                        <div class="py-8 text-slate-400 flex flex-col items-center justify-center">
                            <i class="fa-regular fa-image text-4xl mb-2 text-slate-300"></i>
                            <p class="font-semibold text-xs text-slate-600">Belum ada foto yang diunggah untuk aset ini.</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Anda dapat menambahkan foto melalui tombol Edit.</p>
                        </div>
                    </template>
                </div>

                <!-- Detail Specification Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-700">
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Nomor Registrasi Pemda</p>
                        <p class="font-mono font-bold text-sm text-[#172554] mt-0.5" x-text="detailItem.no_reg_pemda || '-'"></p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Nomor Registrasi KOMINFO</p>
                        <p class="font-mono font-bold text-sm text-emerald-600 mt-0.5" x-text="detailItem.no_reg_kominfo || '-'"></p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Jenis Barang & Merek/Tipe</p>
                        <p class="font-bold text-xs text-[#172554] mt-0.5">
                            <span x-text="detailItem.jenis_barang"></span> - <span x-text="detailItem.merek_tipe"></span>
                        </p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Penanggung Jawab</p>
                        <p class="font-bold text-xs text-[#172554] mt-0.5" x-text="detailItem.penanggung_jawab || '-'"></p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Kondisi Aset</p>
                        <div class="mt-1">
                            <span class="inline-block px-2.5 py-0.5 rounded-full font-bold text-xs" 
                                  :class="{
                                      'bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]': detailItem.kondisi === 'Baik',
                                      'bg-[#FEF9C3] text-[#854D0E] border border-[#FDE047]': detailItem.kondisi === 'Rusak Ringan',
                                      'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]': detailItem.kondisi === 'Rusak Berat',
                                      'bg-[#F1F5F9] text-[#334155] border border-[#CBD5E1]': detailItem.kondisi === 'Hilang'
                                  }" 
                                  x-text="detailItem.kondisi + ' (' + (detailItem.persentase_kondisi ?? (kondisiStandar[detailItem.kondisi]?.default ?? 100)) + '%)'"></span>
                        </div>
                        <p class="text-[10.5px] text-slate-500 mt-1 italic leading-tight" x-text="kondisiStandar[detailItem.kondisi]?.arti || ''"></p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Tahun Perolehan</p>
                        <p class="font-bold text-xs text-[#172554] mt-0.5" x-text="detailItem.tahun || '-'"></p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs sm:col-span-2">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Keterangan Lokasi Unit</p>
                        <p class="font-semibold text-xs text-slate-800 mt-0.5" x-text="detailItem.keterangan_lokasi_unit || '-'"></p>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs sm:col-span-2">
                        <p class="text-[10px] uppercase font-bold text-slate-400">No. SK / BAST / Naskah Pinjam Pakai</p>
                        <div class="mt-1 space-y-1">
                            <template x-if="detailItem.berita_acaras && detailItem.berita_acaras.length > 0">
                                <div class="space-y-1.5">
                                    <template x-for="(ba, bIdx) in detailItem.berita_acaras" :key="ba.id">
                                        <div class="flex items-center space-x-2">
                                            <a :href="ba.file_url" 
                                               target="_blank" 
                                               rel="noopener" 
                                               class="inline-flex items-center space-x-1.5 text-xs font-bold text-[#146C43] hover:text-[#0F5132] hover:underline"
                                               :title="ba.display_nomor">
                                                <i class="fa-solid fa-file-pdf text-xs text-[#146C43]"></i>
                                                <span class="font-mono" x-text="ba.display_nomor"></span>
                                            </a>
                                            <template x-if="bIdx === 0">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-emerald-100 text-[#146C43]">Terbaru</span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="(!detailItem.berita_acaras || detailItem.berita_acaras.length === 0)">
                                <div>
                                    <template x-if="detailItem.no_sk_bast">
                                        <p class="font-mono font-semibold text-xs text-slate-800" x-text="detailItem.no_sk_bast"></p>
                                    </template>
                                    <template x-if="!detailItem.no_sk_bast">
                                        <p class="text-xs text-slate-400 font-semibold">-</p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-3.5 sm:px-6 sm:py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/90 flex justify-end space-x-2">
                <button type="button" @click="showDetailModal = false" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-full font-bold transition text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT ASET (URUTAN KOLOM FORM PERSIS DENGAN TABEL) -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-modal-gradient rounded-3xl shadow-2xl max-w-2xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col overflow-hidden border border-slate-200 my-auto" @click.away="showEditModal = false">
            <!-- Action URL Safe Encoding to Prevent 404 -->
            <form :action="'{{ url('/admin/aset') }}/' + encodeURIComponent(editItem.id || editItem.no_reg_pemda)" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')

                <!-- Header Modal (Tetap / Sticky) -->
                <div class="flex justify-between items-center border-b border-slate-100 p-4 sm:px-6 sm:py-4 shrink-0 bg-white/80">
                    <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-pink-100 text-[#EC4899] flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </div>
                        <span>Edit Data Aset Peralatan & Mesin</span>
                    </h3>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Body Modal (Scrollable Form Fields) -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-2 gap-3.5 custom-scrollbar">
                    <div>
                        <label class="form-label">No Reg / ID Pemda</label>
                        <input type="text" name="no_reg_pemda" x-model="editItem.no_reg_pemda" placeholder="Contoh: 02.03.01.05.01" class="form-input font-mono font-bold text-[#198754]">
                    </div>

                    <div>
                        <label class="form-label">No Reg KOMINFO</label>
                        <input type="text" name="no_reg_kominfo" x-model="editItem.no_reg_kominfo" placeholder="Contoh: KOMINFO-2026-001" class="form-input font-mono font-bold text-emerald-600">
                    </div>

                    <div>
                        <label class="form-label">Penanggung Jawab <span class="text-rose-500">*</span></label>
                        <input type="text" name="penanggung_jawab" x-model="editItem.penanggung_jawab" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Jenis Barang <span class="text-rose-500">*</span></label>
                        <input type="text" name="jenis_barang" x-model="editItem.jenis_barang" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Merek / Tipe Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="merek_tipe" x-model="editItem.merek_tipe" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Tahun Perolehan <span class="text-rose-500">*</span></label>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" name="tahun" x-model="editItem.tahun" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Harga Perolehan <span class="text-rose-500">*</span></label>
                        <input type="text" name="harga_perolehan" id="edit_harga_perolehan" x-model="editItem.harga_perolehan" oninput="formatRupiahInput(this)" placeholder="Contoh: 15.000.000" required class="form-input font-mono">
                    </div>

                    <div>
                        <label class="form-label">Nomor Rangka / Nomor Seri</label>
                        <input type="text" name="no_rangka_seri" x-model="editItem.no_rangka_seri" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Nomor Mesin</label>
                        <input type="text" name="no_mesin" x-model="editItem.no_mesin" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Nomor Polisi</label>
                        <input type="text" name="no_polisi" x-model="editItem.no_polisi" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Nomor BPKB</label>
                        <input type="text" name="no_bpkb" x-model="editItem.no_bpkb" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Kondisi Aset <span class="text-rose-500">*</span></label>
                        <div class="flex items-center space-x-2">
                            <select name="kondisi" x-model="editItem.kondisi" @change="onEditKondisiChange($event.target.value)" required class="form-input font-semibold flex-1">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                            <div class="relative w-24 shrink-0">
                                <input type="number" name="persentase_kondisi" x-model.number="editItem.persentase_kondisi" :readonly="editItem.kondisi === 'Hilang'" :min="kondisiStandar[editItem.kondisi]?.min ?? 0" :max="kondisiStandar[editItem.kondisi]?.max ?? 100" required class="form-input pr-6 text-center font-mono font-bold no-spin [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" :class="editItem.kondisi === 'Hilang' ? 'bg-slate-100 cursor-not-allowed text-slate-500' : ''">
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500 pointer-events-none">%</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1" x-text="editItem.kondisi === 'Hilang' ? 'Kondisi Hilang terkunci pada 0%' : 'Rentang standar: ' + (kondisiStandar[editItem.kondisi]?.rentang_label || '') + ' (Default: ' + (kondisiStandar[editItem.kondisi]?.default || 0) + '%)'"></p>
                    </div>

                    <div>
                        <label class="form-label">Keterangan Unit Lokasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="keterangan_lokasi_unit" x-model="editItem.keterangan_lokasi_unit" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Foto Aset <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <div class="space-y-1.5">
                            <!-- Foto saat ini jika ada -->
                            <template x-if="editItem.foto && !hapusFotoEdit && !editFotoPreview">
                                <div class="flex items-center space-x-2.5 p-2 bg-slate-50 border border-slate-200 rounded-xl">
                                    <img :src="'/storage/' + (editItem.foto || '').replace(/^\/+/, '')" class="h-12 w-12 object-cover rounded-lg border border-slate-200 shadow-2xs shrink-0" alt="Foto Aset">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-bold text-[#172554] truncate">Foto Terpasang</p>
                                        <div class="flex items-center space-x-2 mt-0.5">
                                            <label for="edit_foto_input" class="text-[10px] font-bold text-[#198754] hover:underline cursor-pointer">
                                                <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Ganti
                                            </label>
                                            <span class="text-slate-300 text-xs">|</span>
                                            <button type="button" @click="hapusFotoEdit = true" class="text-[10px] font-bold text-rose-500 hover:underline">
                                                <i class="fa-solid fa-trash-can mr-0.5"></i> Hapus Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Pesan jika foto ditandai akan dihapus -->
                            <template x-if="hapusFotoEdit && !editFotoPreview">
                                <div class="flex items-center justify-between p-2 bg-rose-50 border border-rose-200 rounded-xl text-[11px] text-rose-700">
                                    <span class="font-semibold"><i class="fa-solid fa-trash-can mr-1"></i> Foto akan dihapus saat disimpan</span>
                                    <button type="button" @click="hapusFotoEdit = false" class="text-[10px] font-bold text-[#198754] hover:underline">Batal</button>
                                </div>
                            </template>

                            <!-- Preview foto baru terpilih (Ganti foto) -->
                            <template x-if="editFotoPreview">
                                <div class="flex items-center space-x-2.5 p-2 bg-[#D1E7DD]/50 border border-[#A3CFBB] rounded-xl">
                                    <img :src="editFotoPreview" class="h-12 w-12 object-cover rounded-lg border border-[#A3CFBB] shadow-2xs shrink-0" alt="Preview Foto Baru">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-bold text-[#0F5132]">Foto Baru Terpilih</p>
                                        <button type="button" @click="clearEditFoto()" class="text-[10px] font-bold text-rose-500 hover:underline mt-0.5">
                                            <i class="fa-solid fa-xmark mr-0.5"></i> Batalkan Ganti
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Hidden input untuk flag hapus_foto -->
                            <input type="hidden" name="hapus_foto" :value="hapusFotoEdit ? '1' : '0'">

                            <!-- File input -->
                            <div :class="editItem.foto && !hapusFotoEdit && !editFotoPreview ? 'hidden' : 'block'">
                                <input type="file" name="foto" id="edit_foto_input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" 
                                       @change="previewEditFoto($event)" 
                                       class="block w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-[11px] file:font-bold file:bg-[#D1E7DD] file:text-[#0F5132] hover:file:bg-[#A3CFBB] cursor-pointer border border-slate-300 rounded-xl bg-white p-1">
                                <p class="text-[10px] text-slate-400 mt-1">Format: JPG, JPEG, PNG, WEBP. Maks. 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 border-t border-slate-200/60 pt-2">
                        <label class="form-label">No. SK, BAST, Naskah Pinjam Pakai</label>
                        <input type="text" name="no_sk_bast" x-model="editItem.no_sk_bast" class="form-input">
                    </div>
                </div>

                <!-- Footer Modal (Tetap / Sticky di Bawah) -->
                <div class="p-3.5 sm:px-6 sm:py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/90 flex justify-end space-x-2">
                    <button type="button" @click="showEditModal = false" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-full font-bold transition text-xs">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-[#146C43] hover:bg-[#0F5132] text-white rounded-full font-bold hover:shadow-lg shadow-md transition text-xs">Perbarui Data Aset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DUAL-MODE BERITA ACARA (Upload Scan Fisik & Buat Dokumen Baru) -->
    <div x-show="showUploadBaModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col border border-slate-200 relative overflow-hidden my-auto" @click.away="showUploadBaModal = false">
            
            <!-- Header Modal (Tetap / Sticky) -->
            <div class="flex justify-between items-center border-b border-slate-100 p-4 sm:px-6 sm:py-4 shrink-0 bg-white">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center shadow-xs">
                        <i class="fa-solid fa-file-signature text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-[#172554] text-base">Kelola Berita Acara Aset</h3>
                        <p class="text-[11px] text-slate-400">Pilih antara upload berkas scan fisik atau generate dokumen resmi baru.</p>
                    </div>
                </div>
                <button @click="showUploadBaModal = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Tab Switcher Dua Mode: "Upload Scan Fisik" & "Buat Dokumen Baru" -->
            <div class="px-4 sm:px-6 pt-2 bg-slate-50 border-b border-slate-200 flex space-x-2 shrink-0">
                <button type="button" 
                        @click="baModalMode = 'upload'"
                        :class="baModalMode === 'upload' ? 'border-[#146C43] text-[#146C43] bg-white shadow-xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
                        class="px-4 py-2 text-xs rounded-t-xl border-b-2 flex items-center space-x-2 transition">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Upload Scan Fisik</span>
                </button>
                <button type="button" 
                        @click="baModalMode = 'generate'"
                        :class="baModalMode === 'generate' ? 'border-[#146C43] text-[#146C43] bg-white shadow-xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
                        class="px-4 py-2 text-xs rounded-t-xl border-b-2 flex items-center space-x-2 transition">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                    <span>Buat Dokumen Baru</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-amber-100 text-amber-800">BARU</span>
                </button>
            </div>

            <!-- MODE 1: FORM UPLOAD SCAN FISIK -->
            <form x-show="baModalMode === 'upload'" action="{{ route('admin.berita_acara.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf

                <!-- Body Modal (Scrollable Form Area) -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-3.5 custom-scrollbar">
                    <!-- 1. Aset Terkait (Pilih dari Master Data Aset) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Aset Terkait <span class="text-rose-500">*</span>
                        </label>
                        <select name="aset_id" required class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#146C43] focus:border-[#146C43] font-medium text-slate-800">
                            <option value="">-- Pilih Aset dari Master Data --</option>
                            @foreach($listAsetOption as $optAset)
                                <option value="{{ $optAset->id }}">
                                    {{ $optAset->jenis_barang }} / {{ $optAset->no_reg_pemda ?: ($optAset->no_reg_kominfo ?: '-') }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Satu aset dapat memiliki beberapa riwayat Berita Acara.</p>
                    </div>

                    <!-- 2. Jenis Berita Acara -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Jenis Berita Acara <span class="text-rose-500">*</span>
                        </label>
                        <select name="jenis" required class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#146C43] focus:border-[#146C43] font-semibold text-slate-800">
                            <option value="Berita Acara Serah Terima (BAST)">Berita Acara Serah Terima (BAST)</option>
                            <option value="Berita Acara Penerimaan Barang">Berita Acara Penerimaan Barang</option>
                        </select>
                    </div>

                    <!-- Nomor Berita Acara (Opsional) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nomor Berita Acara <span class="text-slate-500 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" 
                               name="nomor_surat" 
                               x-model="uploadNomorSurat"
                               @blur="checkUploadNomorSurat($event.target.value)"
                               maxlength="150" 
                               placeholder="Contoh: 555/KOMINFO-BB/BEN-BAR/BAP/2/IX/2026" 
                               class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#146C43] font-mono font-bold text-[#172554]"
                               :class="uploadNomorWarning ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200 bg-rose-50/40 text-rose-800' : ''">
                        <p class="text-[10px] text-slate-500 mt-1">Isi jika nomor tertera pada dokumen. Boleh dikosongkan.</p>
                        <p x-show="uploadNomorWarning" x-cloak class="text-[11px] text-rose-600 font-bold mt-1.5 flex items-center space-x-1.5 bg-rose-50 p-2 rounded-xl border border-rose-200">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 shrink-0"></i>
                            <span x-text="uploadNomorWarning"></span>
                        </p>
                    </div>

                    <!-- 3. Tanggal Berita Acara -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tanggal Berita Acara <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#146C43] font-semibold text-slate-800">
                        <p class="text-[10px] text-slate-500 mt-1">Tanggal saat dokumen fisik dibuat atau ditandatangani di lapangan.</p>
                    </div>

                    <!-- 4. Keterangan (Opsional) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Keterangan <span class="text-slate-500 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="keterangan" rows="2" placeholder="Catatan singkat terkait Berita Acara fisik ini (opsional)..." class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#146C43] font-medium text-slate-800"></textarea>
                    </div>

                    <!-- 5. Upload File Hasil Scan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Upload File Hasil Scan (PDF) <span class="text-rose-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-[#A3CFBB] hover:border-[#198754] rounded-2xl p-3 sm:p-4 text-center bg-[#D1E7DD]/20 hover:bg-[#D1E7DD]/40 transition">
                            <i class="fa-solid fa-file-pdf text-xl text-rose-500 mb-1 block"></i>
                            <input type="file" name="file_scan" accept=".pdf,application/pdf" required class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#198754] file:text-white hover:file:bg-[#146C43] cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-1.5 font-medium">Format wajib PDF, ukuran maksimal 2MB (2048 KB). Pastikan dokumen fisik sudah ditandatangani.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal Mode Upload -->
                <div class="flex justify-end items-center space-x-2.5 p-3.5 sm:px-6 sm:py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/90">
                    <button type="button" @click="showUploadBaModal = false" class="px-5 py-2 rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 rounded-full bg-[#146C43] hover:bg-[#0F5132] text-white font-bold text-xs flex items-center space-x-2 shadow-md transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Unggah & Simpan Berkas</span>
                    </button>
                </div>
            </form>

            <!-- MODE 2: FORM BUAT DOKUMEN BARU (GENERATE OTOMATIS BAST RESMI PDF) -->
            <form x-show="baModalMode === 'generate'" action="{{ route('admin.berita_acara.generate') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf

                <!-- Body Modal (Scrollable Form Fields) -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 custom-scrollbar text-xs">
                    
                    <!-- Banner Info Mode Generate -->
                    <div class="p-3 bg-[#D1E7DD]/50 border border-[#A3CFBB] rounded-2xl flex items-center space-x-2.5 text-[#172554]">
                        <i class="fa-solid fa-wand-magic-sparkles text-[#0F5132] text-base shrink-0"></i>
                        <div class="text-[11px] leading-relaxed">
                            <strong>Mode Generate Otomatis:</strong> Dokumen BAST resmi dengan Kop Surat, klausul hukum baku, terbilang tanggal, serta blok tanda tangan akan dibuat otomatis dalam format <strong>PDF</strong>.
                        </div>
                    </div>

                    <!-- 1. Nomor Surat & Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Nomor Berita Acara <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_surat" x-model="genBaForm.nomor_surat" 
                                   @blur="checkNomorSurat()" 
                                   required placeholder="Contoh: 555/KOMINFO-BB/BEN-BAR/BAP/2/IX/2026" 
                                   class="form-input font-mono font-bold text-[#172554]"
                                   :class="nomorWarning ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200 bg-rose-50/40 text-rose-800' : ''">
                            <p x-show="nomorWarning" x-cloak class="text-[11px] text-rose-600 font-bold mt-1.5 flex items-center space-x-1.5 bg-rose-50 p-2 rounded-xl border border-rose-200">
                                <i class="fa-solid fa-triangle-exclamation text-rose-500 shrink-0"></i>
                                <span x-text="nomorWarning"></span>
                            </p>
                            <p x-show="!nomorWarning" class="text-[10px] text-slate-400 mt-1">Format bebas sesuai penomoran naskah dinas Anda.</p>
                        </div>
                        <div>
                            <label class="form-label">Tanggal Berita Acara <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal" x-model="genBaForm.tanggal" required class="form-input font-semibold">
                            <p class="text-[10px] text-slate-400 mt-1">Tanggal & tahun akan otomatis dikonversi ke format terbilang.</p>
                        </div>
                    </div>

                    <!-- 2. Aset Terkait (Master Data Dropdown dengan Auto-Fill) -->
                    <div>
                        <label class="form-label">Aset Terkait <span class="text-rose-500">*</span></label>
                        <select name="aset_id" x-model="genBaForm.aset_id" @change="onSelectAsetForGenerate($event.target.value)" required class="form-input font-semibold">
                            <option value="">-- Pilih Aset Terkait dari Master Data --</option>
                            @foreach($listAsetOption as $optAset)
                                <option value="{{ $optAset->id }}">
                                    {{ $optAset->jenis_barang }} / {{ $optAset->no_reg_pemda ?: ($optAset->no_reg_kominfo ?: '-') }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Memilih aset akan otomatis mengisi data Pihak Kedua, Merek/Tipe, dan Spesifikasi pada naskah.</p>
                    </div>

                    <!-- 3. Data Pihak Pertama (Yang Menyerahkan) -->
                    <div class="bg-slate-50/90 p-3.5 rounded-2xl border border-slate-200/90 space-y-2.5">
                        <div class="flex items-center space-x-2 text-[#172554] font-bold text-xs">
                            <div class="w-5 h-5 rounded-full bg-[#D1E7DD] text-[#0F5132] flex items-center justify-center text-[10px] font-black">1</div>
                            <span>Pihak Pertama (Yang Menyerahkan)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div class="relative" @click.outside="p1Open = false">
                                <label class="form-label text-[10px]">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="hidden" name="pihak_pertama_user_id" :value="p1SelectedId" required>
                                <button type="button" @click="p1Open = !p1Open" class="w-full flex items-center justify-between form-input bg-white text-left text-xs font-semibold cursor-pointer">
                                    <span class="truncate" :class="!p1SelectedId ? 'text-slate-400 font-normal' : 'text-[#172554] font-bold'" x-text="p1DisplayLabel"></span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0 ml-2"></i>
                                </button>
                                
                                <div x-show="p1Open" x-cloak class="absolute left-0 right-0 z-40 mt-1 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 space-y-1.5">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                                        <input type="text" x-model="p1Search" placeholder="Cari nama pegawai..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#198754]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 custom-scrollbar">
                                        <template x-for="p in filteredPegawai1" :key="p.id">
                                            <div @click="selectPihakPertama(p.id)" class="px-2.5 py-2 rounded-xl text-xs hover:bg-[#D1E7DD] hover:text-[#0F5132] cursor-pointer transition flex items-center justify-between" :class="p1SelectedId == p.id ? 'bg-[#D1E7DD] font-bold text-[#0F5132]' : 'text-slate-700'">
                                                <div>
                                                    <p class="font-bold text-xs" x-text="p.nama"></p>
                                                    <p class="text-[10px] text-slate-400" x-text="p.jabatan"></p>
                                                </div>
                                                <i x-show="p1SelectedId == p.id" class="fa-solid fa-check text-xs text-[#198754]"></i>
                                            </div>
                                        </template>
                                        <div x-show="filteredPegawai1.length === 0" class="px-2.5 py-2 text-xs text-slate-400 text-center">
                                            Tidak ada pegawai cocok
                                        </div>
                                        <div class="pt-1 border-t border-slate-100">
                                            <div @click="selectPihakPertama('manual')" class="px-2.5 py-2 rounded-xl text-xs hover:bg-amber-50 text-amber-800 font-bold cursor-pointer transition flex items-center space-x-2" :class="p1SelectedId === 'manual' ? 'bg-amber-100' : ''">
                                                <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                                                <span>Lainnya (isi manual)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="p1SelectedId === 'manual'" class="mt-2" x-cloak>
                                    <input type="text" name="pihak_pertama_nama" x-model="p1Nama" :required="p1SelectedId === 'manual'" placeholder="Ketik Nama Lengkap & Gelar" class="form-input text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="form-label text-[10px]">Jabatan <span class="text-rose-500">*</span></label>
                                <input type="text" name="pihak_pertama_jabatan" x-model="p1Jabatan" :readonly="p1SelectedId !== 'manual'" :required="p1SelectedId === 'manual'" placeholder="Terisi otomatis dari nama" :class="p1SelectedId !== 'manual' ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800'" class="form-input font-medium text-xs">
                            </div>
                            <div>
                                <label class="form-label text-[10px]">NIP</label>
                                <input type="text" name="pihak_pertama_nip" x-model="p1Nip" :readonly="p1SelectedId !== 'manual'" placeholder="Terisi otomatis" :class="p1SelectedId !== 'manual' ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white font-mono'" class="form-input font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Data Pihak Kedua (Yang Menerima) -->
                    <div class="bg-slate-50/90 p-3.5 rounded-2xl border border-slate-200/90 space-y-2.5">
                        <div class="flex items-center space-x-2 text-[#172554] font-bold text-xs">
                            <div class="w-5 h-5 rounded-full bg-pink-100 text-[#EC4899] flex items-center justify-center text-[10px] font-black">2</div>
                            <span>Pihak Kedua (Yang Menerima)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div class="relative" @click.outside="p2Open = false">
                                <label class="form-label text-[10px]">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="hidden" name="pihak_kedua_user_id" :value="p2SelectedId" required>
                                <button type="button" @click="p2Open = !p2Open" class="w-full flex items-center justify-between form-input bg-white text-left text-xs font-semibold cursor-pointer">
                                    <span class="truncate" :class="!p2SelectedId ? 'text-slate-400 font-normal' : 'text-[#172554] font-bold'" x-text="p2DisplayLabel"></span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 shrink-0 ml-2"></i>
                                </button>
                                
                                <div x-show="p2Open" x-cloak class="absolute left-0 right-0 z-40 mt-1 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 space-y-1.5">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                                        <input type="text" x-model="p2Search" placeholder="Cari nama pegawai..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#198754]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 custom-scrollbar">
                                        <template x-for="p in filteredPegawai2" :key="p.id">
                                            <div @click="selectPihakKedua(p.id)" class="px-2.5 py-2 rounded-xl text-xs hover:bg-[#D1E7DD] hover:text-[#0F5132] cursor-pointer transition flex items-center justify-between" :class="p2SelectedId == p.id ? 'bg-[#D1E7DD] font-bold text-[#0F5132]' : 'text-slate-700'">
                                                <div>
                                                    <p class="font-bold text-xs" x-text="p.nama"></p>
                                                    <p class="text-[10px] text-slate-400" x-text="p.jabatan"></p>
                                                </div>
                                                <i x-show="p2SelectedId == p.id" class="fa-solid fa-check text-xs text-[#198754]"></i>
                                            </div>
                                        </template>
                                        <div x-show="filteredPegawai2.length === 0" class="px-2.5 py-2 text-xs text-slate-400 text-center">
                                            Tidak ada pegawai cocok
                                        </div>
                                        <div class="pt-1 border-t border-slate-100">
                                            <div @click="selectPihakKedua('manual')" class="px-2.5 py-2 rounded-xl text-xs hover:bg-amber-50 text-amber-800 font-bold cursor-pointer transition flex items-center space-x-2" :class="p2SelectedId === 'manual' ? 'bg-amber-100' : ''">
                                                <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                                                <span>Lainnya (isi manual)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="p2SelectedId === 'manual'" class="mt-2" x-cloak>
                                    <input type="text" name="pihak_kedua_nama" x-model="p2Nama" :required="p2SelectedId === 'manual'" placeholder="Ketik Nama Lengkap & Gelar" class="form-input text-xs font-semibold text-[#172554]">
                                </div>
                            </div>
                            <div>
                                <label class="form-label text-[10px]">Jabatan <span class="text-rose-500">*</span></label>
                                <input type="text" name="pihak_kedua_jabatan" x-model="p2Jabatan" :readonly="p2SelectedId !== 'manual'" :required="p2SelectedId === 'manual'" placeholder="Terisi otomatis dari nama" :class="p2SelectedId !== 'manual' ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800'" class="form-input font-medium text-xs">
                            </div>
                            <div>
                                <label class="form-label text-[10px]">NIP</label>
                                <input type="text" name="pihak_kedua_nip" x-model="p2Nip" :readonly="p2SelectedId !== 'manual'" placeholder="Terisi otomatis" :class="p2SelectedId !== 'manual' ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white font-mono'" class="form-input font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- 5. Jumlah & Nama Barang serta Keperluan Penggunaan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Jumlah & Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="jumlah_barang" x-model="genBaForm.jumlah_barang" required placeholder="Contoh: 1 Unit UPS APC" class="form-input">
                            <p class="text-[10px] text-slate-400 mt-1">Dapat disesuaikan (misal: '1 Unit Laptop Lenovo').</p>
                        </div>
                        <div>
                            <label class="form-label">Keperluan Penggunaan <span class="text-rose-500">*</span></label>
                            <input type="text" name="keperluan" x-model="genBaForm.keperluan" required placeholder="Contoh: pekerjaan" class="form-input">
                            <p class="text-[10px] text-slate-400 mt-1">Dituliskan pada kalimat penyerahan dokumen ("untuk keperluan [keperluan]").</p>
                        </div>
                    </div>

                    <!-- 6. Pejabat yang Mengetahui & Tempat Tanda Tangan -->
                    <div class="bg-[#D1E7DD]/30 p-3.5 rounded-2xl border border-[#A3CFBB] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 text-[#172554] font-bold text-xs">
                                <i class="fa-solid fa-stamp text-[#0F5132]"></i>
                                <span>Pengesahan & Lokasi Penandatanganan</span>
                            </div>
                            @if(isset($pejabatKadis) && $pejabatKadis)
                                <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[#D1E7DD] text-[#0F5132] border border-[#A3CFBB]">
                                    <i class="fa-solid fa-lock text-[9px] text-[#0F5132]"></i>
                                    <span>Otomatis (Hanya-Baca)</span>
                                </span>
                            @endif
                        </div>

                        @if(!isset($pejabatKadis) || !$pejabatKadis)
                            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-center space-x-2">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 shrink-0"></i>
                                <span>Data Plt. Kepala Dinas tidak ditemukan pada data pegawai aktif. Silakan isi data pengesahan secara manual di bawah ini.</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="form-label text-[10px]">Pejabat Mengetahui <span class="text-rose-500">*</span></label>
                                <input type="text" name="mengetahui_nama" x-model="genBaForm.mengetahui_nama" required placeholder="Nama Kepala Dinas" 
                                       @if(isset($pejabatKadis) && $pejabatKadis) readonly @endif
                                       class="form-input @if(isset($pejabatKadis) && $pejabatKadis) bg-slate-100/90 text-slate-700 cursor-not-allowed border-slate-200 font-semibold @endif">
                            </div>
                            <div>
                                <label class="form-label text-[10px]">NIP Pejabat</label>
                                <input type="text" name="mengetahui_nip" x-model="genBaForm.mengetahui_nip" placeholder="NIP Pejabat Mengetahui" 
                                       @if(isset($pejabatKadis) && $pejabatKadis) readonly @endif
                                       class="form-input font-mono @if(isset($pejabatKadis) && $pejabatKadis) bg-slate-100/90 text-slate-700 cursor-not-allowed border-slate-200 @endif">
                            </div>
                            <div>
                                <label class="form-label text-[10px]">Jabatan Pejabat <span class="text-rose-500">*</span></label>
                                <input type="text" name="mengetahui_jabatan" x-model="genBaForm.mengetahui_jabatan" required placeholder="Plt. Kepala Dinas" 
                                       @if(isset($pejabatKadis) && $pejabatKadis) readonly @endif
                                       class="form-input @if(isset($pejabatKadis) && $pejabatKadis) bg-slate-100/90 text-slate-700 cursor-not-allowed border-slate-200 font-semibold @endif">
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-[10px]">Tempat Penandatanganan <span class="text-rose-500">*</span></label>
                            <input type="text" name="tempat_tanda_tangan" x-model="genBaForm.tempat_tanda_tangan" required placeholder="Contoh: Suwawa" class="form-input max-w-xs">
                        </div>
                    </div>

                    <!-- 7. Keterangan Tambahan (Opsional) -->
                    <div>
                        <label class="form-label">Keterangan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <textarea name="keterangan" x-model="genBaForm.keterangan" rows="2" placeholder="Catatan khusus atau keterangan tambahan pada arsip sistem..." class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#198754] font-medium text-slate-800"></textarea>
                    </div>
                </div>

                <!-- Footer Modal Mode Generate -->
                <div class="flex justify-between items-center p-3.5 sm:px-6 sm:py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/90">
                    <button type="button" @click="showUploadBaModal = false" class="px-5 py-2 rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F59E0B] hover:bg-[#D97706] text-[#172554] font-black text-xs flex items-center space-x-2 shadow-md transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-[#172554]"></i>
                        <span>Generate Dokumen (PDF)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PREVIEW DOKUMEN BERITA ACARA ("LIHAT" - VIEW ONLY DENGAN DOWNLOAD WORD & PDF) -->
    <div x-show="showPreviewBaModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full p-4 sm:p-6 space-y-3.5 max-h-[calc(100vh-2rem)] sm:max-h-[90vh] flex flex-col border border-slate-200 relative my-auto overflow-hidden" @click.away="showPreviewBaModal = false">
            
            <!-- Header Modal -->
            <div class="flex justify-between items-start border-b border-slate-200 pb-3 shrink-0">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2 flex-wrap gap-1">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#D1E7DD] text-[#0F5132]" x-text="selectedBa.jenis"></span>
                        <template x-if="selectedBa.sumber === 'generate'">
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                                <span>Hasil Generate Sistem</span>
                            </span>
                        </template>
                        <template x-if="selectedBa.sumber !== 'generate'">
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                                <span>Scan Fisik</span>
                            </span>
                        </template>
                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="fa-solid fa-lock text-[9px]"></i>
                            <span>Arsip Permanen</span>
                        </span>
                    </div>
                    <h3 class="font-black text-[#172554] text-base" x-text="selectedBa.aset_nama"></h3>
                </div>
                <button @click="showPreviewBaModal = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Info Ringkas Dokumen -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200 text-xs shrink-0">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">No. Registrasi Aset:</span>
                    <span class="font-mono font-bold text-[#198754]" x-text="selectedBa.aset_kode"></span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Nomor Berita Acara:</span>
                    <span class="font-mono font-bold text-[#172554]" x-text="selectedBa.nomor_surat || '-'"></span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Tanggal Dokumen:</span>
                    <span class="font-bold text-slate-800" x-text="selectedBa.tanggal"></span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Pihak Penerima (PJ):</span>
                    <span class="font-semibold text-slate-800 truncate block" x-text="selectedBa.pihak_kedua || selectedBa.pj"></span>
                </div>
            </div>

            <!-- PDF Viewer Langsung di Layar (iframe inline) -->
            <div class="flex-1 w-full bg-slate-100 rounded-2xl border border-slate-200 overflow-hidden min-h-[280px] max-h-[58vh] relative">
                <template x-if="selectedBa.preview_url">
                    <iframe :src="selectedBa.preview_url" class="w-full h-full min-h-[280px] border-0" frameborder="0">
                        <p class="p-6 text-center text-xs text-slate-500">
                            Browser Anda tidak mendukung preview PDF langsung. 
                            <a :href="selectedBa.preview_url" target="_blank" class="text-[#198754] underline font-bold">Klik di sini untuk membuka PDF</a>.
                        </p>
                    </iframe>
                </template>
            </div>

            <!-- Footer & Tombol Download Word & PDF -->
            <div class="flex flex-wrap justify-between items-center gap-2 pt-2 border-t border-slate-100 text-xs shrink-0">
                <div class="text-[11px] text-slate-400 flex items-center space-x-1.5">
                    <i class="fa-solid fa-circle-info text-[#198754]"></i>
                    <span>Tersimpan permanen di database riwayat Berita Acara.</span>
                </div>
                
                <div class="flex items-center space-x-2 flex-wrap gap-1.5">
                    <!-- Tombol Download PDF -->
                    <a :href="selectedBa.download_pdf_url || selectedBa.preview_url" class="px-4 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center space-x-1.5 transition shadow-sm">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Download PDF</span>
                    </a>

                    <!-- Tombol Buka Penuh Tab Baru -->
                    <a :href="selectedBa.preview_url" target="_blank" class="px-3.5 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center space-x-1 transition">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>Buka Penuh</span>
                    </a>

                    <!-- Tombol Tutup -->
                    <button type="button" @click="showPreviewBaModal = false" class="px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Formatter & Chart.js Initialization -->
    <script>
        function formatRupiahInput(input) {
            if (!input) return;
            let value = String(input.value || "").replace(/\D/g, "");
            if (value) {
                let num = parseInt(value, 10);
                if (num > 999000000) {
                    num = 999000000;
                }
                input.value = new Intl.NumberFormat('id-ID').format(num);
            } else {
                input.value = "";
            }
        }

        function formatEditHarga() {
            setTimeout(() => {
                const el = document.getElementById('edit_harga_perolehan');
                if (el) formatRupiahInput(el);
            }, 50);
        }

        document.addEventListener("DOMContentLoaded", function () {
            const baikCount = {{ $rekapKondisi['Baik'] ?? 0 }};
            const ringanCount = {{ $rekapKondisi['Rusak Ringan'] ?? 0 }};
            const beratCount = {{ $rekapKondisi['Rusak Berat'] ?? 0 }};
            const hilangCount = {{ $rekapKondisi['Hilang'] ?? 0 }};

            // Mapping Warna Konsisten Sistem
            const CONDITION_COLORS = {
                'Baik': '#16A34A',         // Hijau
                'Rusak Ringan': '#EAB308', // Kuning
                'Rusak Berat': '#DC2626',  // Merah
                'Hilang': '#64748B'        // Abu-abu
            };

            const chartColors = [
                CONDITION_COLORS['Baik'],
                CONDITION_COLORS['Rusak Ringan'],
                CONDITION_COLORS['Rusak Berat'],
                CONDITION_COLORS['Hilang']
            ];

            // 1. Doughnut Chart (Persentase Kondisi Aset dengan Tema Warna Konsisten)
            const ctxDoughnut = document.getElementById('kondisiDoughnutChart');
            if (ctxDoughnut) {
                new Chart(ctxDoughnut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'],
                        datasets: [{
                            data: [baikCount, ringanCount, beratCount, hilangCount],
                            backgroundColor: chartColors,
                            hoverOffset: 8,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { family: 'Plus Jakarta Sans, sans-serif', weight: 'bold', size: 11 },
                                    color: '#172554'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + ' Unit';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Bar Chart (Grafik Batang Jumlah Unit dengan Warna Konsisten)
            const ctxBar = document.getElementById('kondisiBarChart');
            if (ctxBar) {
                new Chart(ctxBar.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'],
                        datasets: [{
                            label: 'Jumlah Unit Aset',
                            data: [baikCount, ringanCount, beratCount, hilangCount],
                            backgroundColor: chartColors,
                            borderRadius: 12,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + ' Unit';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: '#64748B', font: { weight: '600' } },
                                grid: { color: 'rgba(226, 232, 240, 0.7)' }
                            },
                            x: {
                                ticks: { color: '#172554', font: { weight: 'bold' } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });

        // Pastikan data selalu segar saat pengguna menekan tombol Kembali (back button) browser
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

</body>
</html>