@extends('layouts.app')

@section('title', 'Manajemen Aset & Peralatan Mesin')

@push('styles')
<style>
[x-cloak] { display: none !important; }
        :root {
            --color-primary: #3B82F6;
            --color-blue-light: #93C5FD;
            --color-white: #FFFFFF;
            --color-pink: #EC4899;
            --color-pink-light: #F9A8D4;
            --color-navy: #172554;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .bg-sidebar-gradient {
            background: linear-gradient(180deg, var(--color-navy) 0%, #1E3A8A 55%, #1E1B4B 100%) !important;
            box-shadow: 4px 0 25px rgba(23, 37, 84, 0.18);
        }
        .bg-card-gradient, .card-soft {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.96) 0%, rgba(239, 246, 255, 0.65) 50%, rgba(253, 242, 248, 0.65) 100%) !important;
            border: 1px solid rgba(147, 197, 253, 0.45);
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(23, 37, 84, 0.07), 0 4px 12px -2px rgba(23, 37, 84, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-interactive:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -6px rgba(59, 130, 246, 0.15), 0 8px 16px -4px rgba(236, 72, 153, 0.1);
            border-color: rgba(59, 130, 246, 0.6);
        }
        .bg-modal-gradient {
            background: linear-gradient(145deg, #FFFFFF 0%, rgba(239, 246, 255, 0.95) 50%, rgba(253, 242, 248, 0.95) 100%) !important;
            border-radius: 1.5rem;
            border: 1px solid rgba(147, 197, 253, 0.45);
            box-shadow: 0 25px 50px -12px rgba(23, 37, 84, 0.25);
        }
        .bg-header-gradient {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.94) 0%, rgba(255, 255, 255, 0.88) 100%) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(147, 197, 253, 0.35);
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
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
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

        /* Styling khusus untuk cetak label QR Code */
        @@media print {
            @@page {
                size: 80mm 90mm;
                margin: 0mm !important;
            }
            html, body {
                width: 80mm !important;
                height: 90mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body > * {
                display: none !important;
            }
            aside, header, nav, .no-print, [role="navigation"] {
                display: none !important;
            }
            body * {
                visibility: hidden !important;
            }
            #printable-qr-label, #printable-qr-label * {
                visibility: visible !important;
            }
            #printable-qr-label {
                position: fixed !important;
                left: 50% !important;
                top: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 74mm !important;
                max-width: 74mm !important;
                margin: 0 !important;
                padding: 4mm 3mm !important;
                background: #ffffff !important;
                border: 2px dashed #3B82F6 !important;
                border-radius: 12px !important;
                box-shadow: none !important;
                text-align: center !important;
                display: block !important;
                z-index: 999999 !important;
            }
            #printable-qr-label img {
                width: 38mm !important;
                height: 38mm !important;
                margin: 0 auto !important;
                display: block !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request()->get('tab') === 'laporan' || request()->has('filter_bulan') || request()->has('filter_tahun') || request()->has('filter_kondisi') ? 'laporan' : 'master' }}', showAddModal: false, showEditModal: false, showQrModal: false, editItem: {}, qrItem: {} }">

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

                @if(isset($errors) && $errors->any())
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

                <!-- Sub-Menu Tabs Navigation (Pill styled tabs) -->
                <div class="flex bg-white/90 p-1.5 rounded-full border border-slate-200 shadow-sm flex-wrap gap-1 max-w-fit">
                    <button @click="activeTab = 'master'" :class="activeTab === 'master' ? 'bg-[#3B82F6] text-white font-extrabold shadow-md' : 'text-slate-600 hover:text-[#172554] font-medium'" class="py-2.5 px-5 rounded-full text-xs transition flex items-center space-x-2">
                        <i class="fa-solid fa-database"></i>
                        <span>Master Data Aset</span>
                    </button>
                    <button @click="activeTab = 'laporan'" :class="activeTab === 'laporan' ? 'bg-gradient-to-r from-[#3B82F6] to-[#EC4899] text-white font-extrabold shadow-md' : 'text-slate-600 hover:text-[#172554] font-medium'" class="py-2.5 px-5 rounded-full text-xs transition flex items-center space-x-2">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Laporan Rekap Bulanan</span>
                    </button>
                </div>

                <!-- TAB 1: MASTER DATA ASET -->
                <div x-show="activeTab === 'master'" class="bg-card-gradient rounded-3xl p-6 space-y-4">
                    <div class="flex justify-between items-center flex-wrap gap-2 border-b border-blue-100/80 pb-4">
                        <div>
                            <h3 class="font-extrabold text-[#172554] text-base flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center">
                                    <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                </div>
                                <span>Pendataan Aset Peralatan dan Mesin Diskominfo</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Kelola data seluruh aset, generate QR code, dan perbarui data perolehan.</p>
                        </div>
                        <button @click="showAddModal = true" class="bg-gradient-to-r from-[#3B82F6] to-[#EC4899] hover:from-blue-600 hover:to-pink-600 text-white text-xs px-5 py-2.5 rounded-full font-bold flex items-center space-x-2 shadow-lg transition transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Aset Baru</span>
                        </button>
                    </div>

                    <!-- Toolbar Filter & Pencarian Master Data Aset -->
                    <form action="{{ route('admin.aset') }}" method="GET" class="bg-white/90 p-3 rounded-2xl border border-blue-100/90 shadow-sm flex flex-wrap items-center gap-2">
                        <!-- Input Kata Kunci Pencarian -->
                        <div class="relative flex-1 min-w-[200px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Reg, Jenis, Merek, Penanggung Jawab, Lokasi..." class="w-full text-xs pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-full focus:ring-2 focus:ring-[#3B82F6] focus:bg-white text-slate-700 font-medium">
                        </div>

                        <!-- Filter Kategori / Jenis Barang -->
                        <select name="kategori" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#3B82F6] font-medium text-slate-700 max-w-[160px]">
                            <option value="">Semua Kategori</option>
                            @foreach($listKategori as $kat)
                                <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>

                        <!-- Filter Kondisi -->
                        <select name="kondisi" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#3B82F6] font-medium text-slate-700">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ request('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ request('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            <option value="Hilang" {{ request('kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                        </select>

                        <!-- Filter Lokasi -->
                        <select name="lokasi" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#3B82F6] font-medium text-slate-700 max-w-[160px]">
                            <option value="">Semua Lokasi</option>
                            @foreach($listLokasi as $lok)
                                <option value="{{ $lok }}" {{ request('lokasi') == $lok ? 'selected' : '' }}>{{ $lok }}</option>
                            @endforeach
                        </select>

                        <!-- Filter Tahun -->
                        <select name="tahun" class="text-xs border border-slate-200 rounded-full px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-[#3B82F6] font-medium text-slate-700">
                            <option value="">Semua Tahun</option>
                            @foreach($listTahun as $th)
                                <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                            @endforeach
                        </select>

                        <!-- Tombol Terapkan Filter & Reset -->
                        <button type="submit" class="bg-[#3B82F6] hover:bg-blue-700 text-white text-xs px-4 py-2 rounded-full font-bold flex items-center space-x-1.5 shadow-sm transition">
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
                    <div class="overflow-x-auto rounded-2xl border border-blue-200/60 shadow-sm bg-white/80 backdrop-blur-md">
                        <table class="w-full text-left text-[11px] text-slate-600 whitespace-nowrap">
                            <thead class="bg-gradient-to-r from-blue-100/90 to-pink-100/90 text-[#172554] uppercase font-black border-b border-blue-200">
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
                                        'Baik' => 'bg-emerald-100 text-emerald-800',
                                        'Rusak Ringan' => 'bg-amber-100 text-amber-800',
                                        'Rusak Berat', 'Hilang' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                    $regId = $item->no_reg_pemda ?: '-';
                                    $regKominfo = $item->no_reg_kominfo ?: '-';
                                    $itemId = $item->id;
                                @endphp
                                <tr class="hover:bg-blue-50/50 transition">
                                    <td class="px-3 py-3 text-center font-medium">{{ $aset->firstItem() + $index }}</td>
                                    <td class="px-3 py-3 font-bold text-[#172554]">{{ $item->penanggung_jawab ?? '-' }}</td>
                                    <td class="px-3 py-3 font-semibold text-slate-800">{{ $item->jenis_barang ?? '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-[#3B82F6] font-bold">{{ $regId }}</td>
                                    <td class="px-3 py-3 font-mono text-emerald-600 font-bold">{{ $regKominfo }}</td>
                                    <td class="px-3 py-3">{{ $item->merek_tipe ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $item->tahun ?? '-' }}</td>
                                    <td class="px-3 py-3 font-mono font-semibold">Rp {{ number_format($item->harga_perolehan ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_rangka_seri ?: '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_mesin ?: '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_polisi ?: '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-slate-600">{{ $item->no_bpkb ?: '-' }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full font-bold {{ $badgeColor }}">
                                             {{ $item->kondisi ?? 'Baik' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">{{ $item->keterangan_lokasi_unit ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $item->no_sk_bast ?? '-' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <!-- QR Modal Trigger -->
                                            <button @click="showQrModal = true; qrItem = { id: '{{ addslashes($regId) }}', kominfo: '{{ addslashes($regKominfo) }}', jenis: '{{ addslashes($item->jenis_barang ?? '-') }}', penanggung: '{{ addslashes($item->penanggung_jawab ?? '-') }}', merek: '{{ addslashes($item->merek_tipe ?? '-') }}', url: '{{ $item->public_url }}', qr_img: '{{ url('/aset/' . $itemId . '/qr-image') }}' }" class="bg-pink-100 hover:bg-[#EC4899] text-[#EC4899] hover:text-white font-extrabold px-3 py-1 rounded-full transition flex items-center space-x-1 text-[10px] shadow-sm" title="Cetak Label QR Code">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>QR</span>
                                            </button>
                                            <!-- Edit Modal Trigger -->
                                            <button @click="showEditModal = true; editItem = {{ json_encode($item) }}; formatEditHarga();" class="text-[#3B82F6] hover:text-blue-800 font-bold px-2 py-1 rounded-full hover:bg-blue-50 transition" title="Edit Data Aset">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <!-- Delete Form Trigger -->
                                            <form action="{{ route('admin.aset.destroy', ['no_reg_pemda' => $itemId]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-full hover:bg-rose-50 transition" title="Hapus Data Aset">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="16" class="px-4 py-8 text-center text-slate-400">Belum ada data aset terdaftar.</td>
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

                <!-- TAB 2: LAPORAN REKAP BULANAN -->
                <div x-show="activeTab === 'laporan'" x-cloak class="bg-card-gradient rounded-3xl p-6 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-black text-[#172554] text-lg">Laporan Rekap Kondisi Barang Bulanan</h3>
                            <p class="text-xs text-slate-500">Ringkasan statistik, diagram visualisasi data, dan rekapitulasi aset berdasarkan periode bulan/tahun.</p>
                        </div>

                        <!-- Filter Form & Export PDF -->
                        <form action="{{ route('admin.aset') }}" method="GET" class="flex flex-wrap items-center gap-2 bg-slate-50 p-2.5 rounded-full border border-slate-200">
                            @if(isset($filterKondisi) && $filterKondisi !== 'all')
                                <input type="hidden" name="filter_kondisi" value="{{ $filterKondisi }}">
                            @endif
                            <div>
                                <select name="filter_bulan" class="text-xs border border-slate-300 rounded-full px-3 py-1.5 bg-white focus:ring-2 focus:ring-[#3B82F6] font-semibold text-[#172554]">
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
                                <select name="filter_tahun" class="text-xs border border-slate-300 rounded-full px-3 py-1.5 bg-white focus:ring-2 focus:ring-[#3B82F6] font-semibold text-[#172554]">
                                    <option value="all" {{ $filterTahun === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $filterTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="bg-[#3B82F6] hover:bg-blue-700 text-white text-xs px-4 py-1.5 rounded-full font-bold flex items-center space-x-1 shadow-sm transition">
                                <i class="fa-solid fa-filter"></i>
                                <span>Filter</span>
                            </button>
                            @if(request()->has('filter_kondisi') || request()->has('filter_bulan') || request()->has('filter_tahun'))
                                <a href="{{ route('admin.aset', ['filter_bulan' => 'all', 'filter_tahun' => date('Y')]) }}" class="text-xs text-slate-500 hover:text-[#3B82F6] font-bold px-2 py-1.5 flex items-center gap-1 transition" title="Reset Semua Filter ke Default">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Reset</span>
                                </a>
                            @endif
                            <a href="{{ route('admin.aset.laporan.pdf', array_filter(['filter_bulan' => $filterBulan, 'filter_tahun' => $filterTahun, 'filter_kondisi' => (isset($filterKondisi) && $filterKondisi !== 'all' ? $filterKondisi : null)])) }}" target="_blank" class="bg-[#EC4899] hover:bg-pink-600 text-white text-xs px-4 py-1.5 rounded-full font-bold flex items-center space-x-1 shadow-sm transition">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>Export PDF</span>
                            </a>
                        </form>
                    </div>

                    <!-- Interactive Visual Summary Cards (Selaras Pola Surat Menyurat & Rekap Bulanan) -->
                    @php
                        $currKondisi = $filterKondisi ?? 'all';
                        $isTotalActive = ($currKondisi === 'all' || empty($currKondisi));
                        $isBaikActive = ($currKondisi === 'Baik');
                        $isRinganActive = ($currKondisi === 'Rusak Ringan');
                        $isBeratActive = ($currKondisi === 'Rusak Berat');
                        $isHilangActive = ($currKondisi === 'Hilang');

                        $commonParams = [
                            'tab' => 'laporan',
                            'filter_bulan' => $filterBulan, 
                            'filter_tahun' => $filterTahun
                        ];

                        $urlTotal = route('admin.aset', array_merge($commonParams, ['filter_kondisi' => 'all']));
                        $urlBaik = route('admin.aset', array_merge($commonParams, ['filter_kondisi' => $isBaikActive ? 'all' : 'Baik']));
                        $urlRingan = route('admin.aset', array_merge($commonParams, ['filter_kondisi' => $isRinganActive ? 'all' : 'Rusak Ringan']));
                        $urlBerat = route('admin.aset', array_merge($commonParams, ['filter_kondisi' => $isBeratActive ? 'all' : 'Rusak Berat']));
                        $urlHilang = route('admin.aset', array_merge($commonParams, ['filter_kondisi' => $isHilangActive ? 'all' : 'Hilang']));
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Card 1: Semua Aset -->
                        <a href="{{ $urlTotal }}" 
                           class="p-5 rounded-3xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer flex flex-col justify-between group relative {{ $isTotalActive ? 'border-2 border-[#EC4899] bg-pink-50/20 shadow-md shadow-pink-500/10' : 'border border-slate-200/90 bg-slate-50/70 hover:bg-white hover:border-slate-300 shadow-sm' }}"
                           title="Semua Aset (Klik untuk menampilkan seluruh data aset)">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-600">Semua Aset</span>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-slate-200/80 text-slate-700">
                                        <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <p class="text-3xl font-black text-[#172554]">{{ $rekapKondisi['Total'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Total Periode Ini</span>
                                @if($isTotalActive)
                                    <span class="bg-[#EC4899] text-white text-[10px] font-black px-2 py-0.5 rounded-md tracking-wider uppercase shadow-xs">AKTIF</span>
                                @else
                                    <span class="text-slate-400 group-hover:text-slate-600 font-semibold transition">Pilih &rarr;</span>
                                @endif
                            </div>
                        </a>

                        <!-- Card 2: Kondisi Baik (Indigo Harmonis - Non-Hijau) -->
                        <a href="{{ $urlBaik }}" 
                           class="p-5 rounded-3xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer flex flex-col justify-between group relative {{ $isBaikActive ? 'border-2 border-[#EC4899] bg-pink-50/20 shadow-md shadow-pink-500/10' : 'border border-indigo-200/80 bg-indigo-50/50 hover:bg-indigo-50/80 hover:border-indigo-300 shadow-sm' }}"
                           title="Kondisi Baik (Klik untuk menyaring aset kondisi Baik)">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-indigo-800">Baik</span>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-indigo-100 text-indigo-600 shadow-xs">
                                        <i class="fa-solid fa-circle-check text-xs"></i>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <p class="text-3xl font-black text-indigo-600">{{ $rekapKondisi['Baik'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-indigo-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-indigo-700/80 font-medium">Kondisi Normal</span>
                                @if($isBaikActive)
                                    <span class="bg-[#EC4899] text-white text-[10px] font-black px-2 py-0.5 rounded-md tracking-wider uppercase shadow-xs">AKTIF</span>
                                @else
                                    <span class="text-indigo-600 group-hover:text-indigo-800 font-semibold transition">Pilih &rarr;</span>
                                @endif
                            </div>
                        </a>

                        <!-- Card 3: Kondisi Rusak Ringan (Pink) -->
                        <a href="{{ $urlRingan }}" 
                           class="p-5 rounded-3xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer flex flex-col justify-between group relative {{ $isRinganActive ? 'border-2 border-[#EC4899] bg-pink-50/20 shadow-md shadow-pink-500/10' : 'border border-pink-200/80 bg-pink-50/50 hover:bg-pink-50/80 hover:border-pink-300 shadow-sm' }}"
                           title="Kondisi Rusak Ringan (Klik untuk menyaring aset kondisi Rusak Ringan)">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-pink-800">Rusak Ringan</span>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-pink-100 text-[#EC4899] shadow-xs">
                                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <p class="text-3xl font-black text-[#EC4899]">{{ $rekapKondisi['Rusak Ringan'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-pink-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-pink-700/80 font-medium">Perlu Perbaikan</span>
                                @if($isRinganActive)
                                    <span class="bg-[#EC4899] text-white text-[10px] font-black px-2 py-0.5 rounded-md tracking-wider uppercase shadow-xs">AKTIF</span>
                                @else
                                    <span class="text-[#EC4899] group-hover:text-pink-700 font-semibold transition">Pilih &rarr;</span>
                                @endif
                            </div>
                        </a>

                        <!-- Card 4: Kondisi Rusak Berat (Navy) -->
                        <a href="{{ $urlBerat }}" 
                           class="p-5 rounded-3xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer flex flex-col justify-between group relative {{ $isBeratActive ? 'border-2 border-[#EC4899] bg-pink-50/20 shadow-md shadow-pink-500/10' : 'border border-slate-300/80 bg-slate-100/70 hover:bg-slate-100/90 hover:border-slate-400 shadow-sm' }}"
                           title="Kondisi Rusak Berat (Klik untuk menyaring aset kondisi Rusak Berat)">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Rusak Berat</span>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-slate-200 text-[#172554] shadow-xs">
                                        <i class="fa-solid fa-circle-xmark text-xs"></i>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <p class="text-3xl font-black text-[#172554]">{{ $rekapKondisi['Rusak Berat'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-300/60 flex items-center justify-between text-[11px]">
                                <span class="text-slate-600 font-medium">Rusak Parah / Afkir</span>
                                @if($isBeratActive)
                                    <span class="bg-[#EC4899] text-white text-[10px] font-black px-2 py-0.5 rounded-md tracking-wider uppercase shadow-xs">AKTIF</span>
                                @else
                                    <span class="text-slate-500 group-hover:text-[#172554] font-semibold transition">Pilih &rarr;</span>
                                @endif
                            </div>
                        </a>

                        <!-- Card 5: Kondisi Hilang (Biru Muda) -->
                        <a href="{{ $urlHilang }}" 
                           class="p-5 rounded-3xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer flex flex-col justify-between group relative {{ $isHilangActive ? 'border-2 border-[#EC4899] bg-pink-50/20 shadow-md shadow-pink-500/10' : 'border border-sky-200/80 bg-sky-50/50 hover:bg-sky-50/80 hover:border-sky-300 shadow-sm' }}"
                           title="Kondisi Hilang (Klik untuk menyaring aset kondisi Hilang)">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-sky-800">Hilang</span>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-sky-100 text-sky-600 shadow-xs">
                                        <i class="fa-solid fa-ghost text-xs"></i>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <p class="text-3xl font-black text-sky-600">{{ $rekapKondisi['Hilang'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-sky-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-sky-700/80 font-medium">Tidak Ditemukan</span>
                                @if($isHilangActive)
                                    <span class="bg-[#EC4899] text-white text-[10px] font-black px-2 py-0.5 rounded-md tracking-wider uppercase shadow-xs">AKTIF</span>
                                @else
                                    <span class="text-sky-600 group-hover:text-sky-800 font-semibold transition">Pilih &rarr;</span>
                                @endif
                            </div>
                        </a>
                    </div>

                    <!-- Visual Statistics Chart Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                        <div class="bg-white/90 p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h4 class="font-extrabold text-[#172554] text-sm flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center">
                                        <i class="fa-solid fa-chart-pie text-xs"></i>
                                    </div>
                                    <span>Persentase Kondisi Aset</span>
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

                    <!-- Filter Status Indicator Bar (Muncul saat filter kondisi aktif) -->
                    @if(isset($filterKondisi) && $filterKondisi !== 'all')
                        <div class="flex items-center justify-between bg-blue-50/90 border border-blue-200/80 rounded-2xl px-4 py-2.5 text-xs shadow-sm">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-slate-600 font-medium">Filter Kondisi Aktif:</span>
                                <span class="font-extrabold text-[#172554] bg-white px-3 py-1 rounded-full border border-blue-200 shadow-sm flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ match($filterKondisi) { 'Baik' => 'bg-indigo-500', 'Rusak Ringan' => 'bg-[#EC4899]', 'Rusak Berat' => 'bg-[#172554]', 'Hilang' => 'bg-sky-500', default => 'bg-slate-400' } }}"></span>
                                    {{ $filterKondisi }}
                                </span>
                                <span class="text-slate-500 font-semibold">({{ $laporanAset->total() }} aset ditemukan)</span>
                            </div>
                            <a href="{{ route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'laporan_page', 'page']), ['filter_bulan' => $filterBulan, 'filter_tahun' => $filterTahun])) }}" class="text-[#3B82F6] hover:text-blue-800 hover:underline font-bold flex items-center gap-1 transition">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Reset Filter Kondisi</span>
                            </a>
                        </div>
                    @endif

                    <!-- Tabel Rekap Aset Bulanan -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                            <thead class="bg-slate-100 text-[#172554] uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3">No. Reg Pemda</th>
                                    <th class="px-4 py-3">No. Reg KOMINFO</th>
                                    <th class="px-4 py-3">Jenis Barang</th>
                                    <th class="px-4 py-3">Merek / Tipe</th>
                                    <th class="px-4 py-3">Penanggung Jawab</th>
                                    <th class="px-4 py-3">Tahun</th>
                                    <th class="px-4 py-3">Kondisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($laporanAset as $index => $item)
                                @php
                                    $badgeColor = match($item->kondisi ?? 'Baik') {
                                        'Baik' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
                                        'Rusak Ringan' => 'bg-pink-100 text-[#EC4899] border border-pink-200',
                                        'Rusak Berat' => 'bg-[#172554] text-white border border-slate-900',
                                        'Hilang' => 'bg-sky-100 text-sky-800 border border-sky-200',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 text-center font-medium">{{ $laporanAset->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-700">{{ $item->no_reg_pemda }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $item->no_reg_kominfo ?: '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#172554]">{{ $item->jenis_barang }}</td>
                                    <td class="px-4 py-3">{{ $item->merek_tipe }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $item->penanggung_jawab ?: 'Belum Ditentukan' }}</td>
                                    <td class="px-4 py-3">{{ $item->tahun ?: '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full font-bold text-xs {{ $badgeColor }}">
                                            {{ $item->kondisi }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                        @if(isset($filterKondisi) && $filterKondisi !== 'all')
                                            Tidak ada data aset dengan kondisi <span class="font-bold text-slate-600">"{{ $filterKondisi }}"</span> pada periode filter ini.
                                        @else
                                            Tidak ada data aset pada periode filter ini.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Navigasi Pagination Tabel Rekap Bulanan (10 data / page) -->
                    <div class="pt-2">
                        {{ $laporanAset->appends(request()->query())->links() }}
                    </div>
                </div>

    <!-- MODAL QR CODE GENERATED (Tampilan Cetak Sederhana) -->
    <div x-show="showQrModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-modal-gradient rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center space-y-4 border border-blue-200/80" @click.away="showQrModal = false">
            
            <!-- Header Modal (Elemen UI - Sembunyikan saat Cetak) -->
            <div class="flex justify-between items-center border-b border-blue-100 pb-3 no-print">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-full bg-pink-100 text-[#EC4899] flex items-center justify-center">
                        <i class="fa-solid fa-qrcode text-sm"></i>
                    </div>
                    <span>QR Code Aset</span>
                </h3>
                <button @click="showQrModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Konten Utama Label QR Code yang Dicetak -->
            <div id="printable-qr-label" class="border-2 border-dashed border-[#3B82F6] rounded-2xl p-4 bg-white space-y-3 mx-auto max-w-[300px] shadow-sm text-center">
                <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm inline-block mx-auto">
                    <template x-if="qrItem.id">
                        <img :src="qrItem.qr_img || ('https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(qrItem.url))" 
                             x-on:error="$el.src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(qrItem.url)"
                             alt="QR Code" class="w-44 h-44 sm:w-48 sm:h-48 mx-auto block object-contain">
                    </template>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-center gap-1.5 flex-wrap text-xs font-bold font-mono">
                        <span data-qr-field="id" class="text-[#3B82F6]" x-text="'Pemda: ' + (qrItem.id || '-')"></span>
                        <span class="text-slate-300">|</span>
                        <span data-qr-field="kominfo" class="text-emerald-600" x-text="'Kominfo: ' + (qrItem.kominfo || '-')"></span>
                    </div>
                    <p data-qr-field="jenis" class="font-extrabold text-[#172554] text-sm leading-tight" x-text="qrItem.jenis"></p>
                    <p data-qr-field="merek" class="text-slate-600 font-semibold text-xs" x-text="qrItem.merek"></p>
                    <p data-qr-field="penanggung" class="text-slate-500 font-medium text-[11px]" x-text="qrItem.penanggung"></p>
                    <div class="pt-1 no-print">
                        <span class="inline-block text-[10px] text-slate-400 font-mono truncate max-w-[240px]" x-text="qrItem.url" :title="qrItem.url"></span>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi (Elemen UI - Sembunyikan saat Cetak) -->
            <div class="pt-3 border-t border-slate-100 flex flex-col space-y-2 no-print">
                <a :href="qrItem.url" target="_blank" class="w-full bg-gradient-to-r from-[#3B82F6] to-[#2563EB] hover:from-blue-600 hover:to-blue-800 text-white py-2.5 rounded-full font-bold text-xs flex items-center justify-center space-x-2 shadow-md transition">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Buka Halaman Detail Publik</span>
                </a>
                <button type="button" @click="printQrLabel()" class="w-full bg-white hover:bg-slate-50 text-slate-700 py-2.5 rounded-full font-bold text-xs flex items-center justify-center space-x-2 transition border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-print text-blue-600"></i>
                    <span>Cetak Label QR</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ASET (URUTAN KOLOM FORM PERSIS DENGAN TABEL) -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-modal-gradient rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 space-y-5 max-h-[90vh] overflow-y-auto border border-blue-200/80" @click.away="showAddModal = false">
            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </div>
                    <span>Tambah Data Aset Peralatan & Mesin</span>
                </h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.aset.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

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
                    <select name="kondisi" required class="form-input font-semibold">
                        <option value="Baik">Baik</option>
                        <option value="Hilang">Hilang</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Keterangan Unit Lokasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="keterangan_lokasi_unit" placeholder="Bidang E-Government" required class="form-input">
                </div>

                <div class="md:col-span-2 border-t border-slate-200/60 pt-2">
                    <label class="form-label">No. SK, BAST, Naskah Perjanjian Pinjam Pakai</label>
                    <input type="text" name="no_sk_bast" placeholder="SK/001/KOMINFO/2026" class="form-input">
                </div>

                <div class="md:col-span-2 flex justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-full font-bold transition text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#3B82F6] to-[#EC4899] hover:from-blue-600 hover:to-pink-600 text-white rounded-full font-bold hover:shadow-lg shadow-md transition text-xs">
                        Simpan Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT ASET (URUTAN KOLOM FORM PERSIS DENGAN TABEL) -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-modal-gradient rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 space-y-5 max-h-[90vh] overflow-y-auto border border-blue-200/80" @click.away="showEditModal = false">
            <div class="flex justify-between items-center border-b border-blue-100 pb-3">
                <h3 class="font-black text-[#172554] text-base flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-pink-100 text-[#EC4899] flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </div>
                    <span>Edit Data Aset Peralatan & Mesin</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Action URL Safe Encoding to Prevent 404 -->
            <form :action="'{{ url('/admin/aset') }}/' + encodeURIComponent(editItem.id || editItem.no_reg_pemda)" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">No Reg / ID Pemda</label>
                    <input type="text" name="no_reg_pemda" x-model="editItem.no_reg_pemda" placeholder="Contoh: 02.03.01.05.01" class="form-input font-mono font-bold text-[#3B82F6]">
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
                    <select name="kondisi" x-model="editItem.kondisi" required class="form-input font-semibold">
                        <option value="Baik">Baik</option>
                        <option value="Hilang">Hilang</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Keterangan Unit Lokasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="keterangan_lokasi_unit" x-model="editItem.keterangan_lokasi_unit" required class="form-input">
                </div>

                <div>
                    <label class="form-label">No. SK, BAST, Naskah Pinjam Pakai</label>
                    <input type="text" name="no_sk_bast" x-model="editItem.no_sk_bast" class="form-input">
                </div>

                <div class="md:col-span-2 flex justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-full font-bold transition text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#3B82F6] to-[#EC4899] hover:from-blue-600 hover:to-pink-600 text-white rounded-full font-bold hover:shadow-lg shadow-md transition text-xs">
                        Perbarui Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
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

            // Mapping Warna Konsisten Sistem: Indigo (Baik), Pink (Rusak Ringan), Navy (Rusak Berat), Biru Muda (Hilang)
            const CONDITION_COLORS = {
                'Baik': '#4F46E5',         // Indigo Harmonis (Non-Hijau)
                'Rusak Ringan': '#EC4899', // Bright Pink
                'Rusak Berat': '#172554',  // Dark Navy
                'Hilang': '#38BDF8'        // Biru Muda
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
                            legend: { display: false }
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

        // Fungsi Khusus Cetak Label QR Code (Hanya Kotak Garis Putus-Putus)
        window.printQrLabel = function() {
            const label = document.getElementById('printable-qr-label');
            if (!label) {
                window.print();
                return;
            }

            const qrImg = label.querySelector('img');
            const qrSrc = qrImg ? qrImg.src : '';
            const id = label.querySelector('[data-qr-field="id"]')?.innerText || '';
            const kominfo = label.querySelector('[data-qr-field="kominfo"]')?.innerText || '';
            const jenis = label.querySelector('[data-qr-field="jenis"]')?.innerText || '';
            const merek = label.querySelector('[data-qr-field="merek"]')?.innerText || '';
            const penanggung = label.querySelector('[data-qr-field="penanggung"]')?.innerText || '';

            // Hapus iframe print lama jika masih ada
            const oldFrame = document.getElementById('qr-print-frame');
            if (oldFrame) {
                oldFrame.remove();
            }

            // Buat iframe tersembunyi khusus untuk proses print yang 100% terisolasi dari halaman admin
            const printFrame = document.createElement('iframe');
            printFrame.id = 'qr-print-frame';
            printFrame.style.position = 'fixed';
            printFrame.style.top = '-9999px';
            printFrame.style.left = '-9999px';
            printFrame.style.width = '80mm';
            printFrame.style.height = '90mm';
            printFrame.style.border = '0';
            document.body.appendChild(printFrame);

            const frameDoc = printFrame.contentWindow.document;
            frameDoc.open();
            frameDoc.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <style>
        @@page {
            size: 80mm 90mm;
            margin: 0 !important;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            width: 80mm;
            height: 90mm;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .label-box {
            width: 74mm;
            background: #ffffff;
            border: 2px dashed #3B82F6;
            border-radius: 12px;
            padding: 4mm 3mm;
            text-align: center;
            margin: auto;
        }
        .qr-wrapper {
            background: #ffffff;
            padding: 2mm;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            margin-bottom: 2.5mm;
        }
        .qr-wrapper img {
            width: 38mm;
            height: 38mm;
            display: block;
            margin: 0 auto;
        }
        .reg-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 9.5pt;
            font-weight: 700;
            color: #2563EB;
            letter-spacing: 0.5px;
            margin-bottom: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .reg-code .kominfo {
            color: #059669;
        }
        .item-jenis {
            font-size: 10.5pt;
            font-weight: 800;
            color: #172554;
            line-height: 1.25;
            margin-bottom: 1mm;
        }
        .item-merek {
            font-size: 8.5pt;
            font-weight: 600;
            color: #475569;
            margin-bottom: 1mm;
        }
        .item-penanggung {
            font-size: 8pt;
            font-weight: 500;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="label-box">
        <div class="qr-wrapper">
            <img src="${qrSrc}" alt="QR Code">
        </div>
        <div class="reg-code">
            <span>${id}</span>
            <span>|</span>
            <span class="kominfo">${kominfo}</span>
        </div>
        <div class="item-jenis">${jenis}</div>
        <div class="item-merek">${merek}</div>
        <div class="item-penanggung">${penanggung}</div>
    </div>
</body>
</html>`);
            frameDoc.close();

            const triggerPrint = () => {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                } catch (err) {
                    window.print();
                }
            };

            const img = frameDoc.querySelector('img');
            if (img && img.src) {
                if (img.complete) {
                    setTimeout(triggerPrint, 250);
                } else {
                    img.onload = () => setTimeout(triggerPrint, 250);
                    img.onerror = () => setTimeout(triggerPrint, 250);
                }
            } else {
                setTimeout(triggerPrint, 250);
            }
        };
    </script>
@endpush
