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
        @media print {
            @page {
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
<div class="space-y-6" x-data="{ activeTab: '{{ request()->has('filter_bulan') || request()->has('filter_tahun') || request()->has('filter_kondisi') || request()->has('kondisi') || (request()->has('tab') && request()->get('tab') === 'laporan') ? 'laporan' : 'master' }}', showAddModal: false, showEditModal: false, showQrModal: false, editItem: {}, qrItem: {} }">

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

                    <!-- Tabel Master Data Aset Wrapper dengan Gradasi & Soft Shadow -->
                    <div class="overflow-x-auto rounded-2xl border border-blue-200/60 shadow-sm bg-white/80 backdrop-blur-md">
                        <table class="w-full text-left text-[11px] text-slate-600 whitespace-nowrap">
                            <thead class="bg-gradient-to-r from-blue-100/90 to-pink-100/90 text-[#172554] uppercase font-black border-b border-blue-200">
                                <tr>
                                    <th class="px-3 py-3.5 text-center">No</th>
                                    <th class="px-3 py-3.5">Penanggung Jawab</th>
                                    <th class="px-3 py-3.5">Jenis Barang</th>
                                    <th class="px-3 py-3.5">No Reg/ID Pemda</th>
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
                                    $regId = $item->no_reg_pemda ?? 'REG-' . ($aset->firstItem() + $index);
                                @endphp
                                <tr class="hover:bg-blue-50/50 transition">
                                    <td class="px-3 py-3 text-center font-medium">{{ $aset->firstItem() + $index }}</td>
                                    <td class="px-3 py-3 font-bold text-[#172554]">{{ $item->penanggung_jawab ?? '-' }}</td>
                                    <td class="px-3 py-3 font-semibold text-slate-800">{{ $item->jenis_barang ?? '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-[#3B82F6] font-bold">{{ $regId }}</td>
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
                                            <button @click="showQrModal = true; qrItem = { id: '{{ addslashes($regId) }}', jenis: '{{ addslashes($item->jenis_barang ?? '-') }}', penanggung: '{{ addslashes($item->penanggung_jawab ?? '-') }}', merek: '{{ addslashes($item->merek_tipe ?? '-') }}', url: '{{ $item->qr_url ?? url('/aset/' . urlencode($regId)) }}' }" class="bg-pink-100 hover:bg-[#EC4899] text-[#EC4899] hover:text-white font-extrabold px-3 py-1 rounded-full transition flex items-center space-x-1 text-[10px] shadow-sm" title="Cetak Label QR Code">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>QR</span>
                                            </button>
                                            <!-- Edit Modal Trigger -->
                                            <button @click="showEditModal = true; editItem = {{ json_encode($item) }}; formatEditHarga();" class="text-[#3B82F6] hover:text-blue-800 font-bold px-2 py-1 rounded-full hover:bg-blue-50 transition" title="Edit Data Aset">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <!-- Delete Form Trigger -->
                                            <form action="{{ route('admin.aset.destroy', ['no_reg_pemda' => $regId]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
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

                    <!-- Interactive Visual Summary Cards (Selaras Pola Surat Menyurat) -->
                    @php
                        $currKondisi = $filterKondisi ?? 'all';
                        $isTotalActive = ($currKondisi === 'all' || empty($currKondisi));
                        $isBaikActive = ($currKondisi === 'Baik');
                        $isRinganActive = ($currKondisi === 'Rusak Ringan');
                        $isBeratActive = ($currKondisi === 'Rusak Berat');
                        $isHilangActive = ($currKondisi === 'Hilang');

                        $commonParams = ['filter_bulan' => $filterBulan, 'filter_tahun' => $filterTahun];

                        $urlTotal = route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'page']), $commonParams));
                        $urlBaik = $isBaikActive 
                            ? route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'page']), $commonParams))
                            : route('admin.aset', array_merge(request()->except('page'), array_merge($commonParams, ['filter_kondisi' => 'Baik'])));
                        $urlRingan = $isRinganActive 
                            ? route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'page']), $commonParams))
                            : route('admin.aset', array_merge(request()->except('page'), array_merge($commonParams, ['filter_kondisi' => 'Rusak Ringan'])));
                        $urlBerat = $isBeratActive 
                            ? route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'page']), $commonParams))
                            : route('admin.aset', array_merge(request()->except('page'), array_merge($commonParams, ['filter_kondisi' => 'Rusak Berat'])));
                        $urlHilang = $isHilangActive 
                            ? route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'page']), $commonParams))
                            : route('admin.aset', array_merge(request()->except('page'), array_merge($commonParams, ['filter_kondisi' => 'Hilang'])));
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Card 1: Total Aset / Semua Aset -->
                        <a href="{{ $urlTotal }}" 
                           class="p-5 rounded-3xl border shadow-sm card-interactive group relative flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl cursor-pointer block {{ $isTotalActive ? 'border-[#3B82F6] ring-2 ring-blue-500/40 bg-blue-100/70 shadow-blue-500/15' : 'bg-blue-50/60 border-blue-200/80 hover:border-blue-400' }}"
                           title="{{ $isTotalActive ? 'Sedang menampilkan seluruh data aset tanpa filter kondisi' : 'Klik untuk menampilkan seluruh data aset tanpa filter kondisi' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-extrabold uppercase tracking-wider transition-colors {{ $isTotalActive ? 'text-[#172554]' : 'text-slate-600 group-hover:text-[#3B82F6]' }}">Semua Aset</span>
                                    @if($isTotalActive)
                                        <span class="bg-blue-600 text-white text-[9px] px-2 py-0.5 rounded-full font-bold">Semua</span>
                                    @endif
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 {{ $isTotalActive ? 'bg-[#3B82F6] text-white shadow-sm scale-105' : 'bg-blue-100 text-[#3B82F6] group-hover:bg-[#3B82F6] group-hover:text-white' }}">
                                    <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-3xl font-black text-[#172554]">{{ $rekapKondisi['Total'] }}</p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-blue-200/70 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Total Periode Ini</span>
                                <span class="font-bold {{ $isTotalActive ? 'text-[#3B82F6]' : 'text-slate-400 group-hover:text-[#3B82F6]' }} transition-colors">
                                    {{ $isTotalActive ? '✓ Aktif' : 'Tampilkan Semua →' }}
                                </span>
                            </div>
                        </a>

                        <!-- Card 2: Kondisi Baik -->
                        <a href="{{ $urlBaik }}" 
                           class="p-5 rounded-3xl border shadow-sm card-interactive group relative flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl cursor-pointer block {{ $isBaikActive ? 'border-[#3B82F6] ring-2 ring-blue-500/50 bg-blue-100/70 shadow-blue-500/20' : 'bg-blue-50/60 border-blue-200/80 hover:border-blue-400' }}"
                           title="{{ $isBaikActive ? 'Klik untuk membatalkan filter kondisi Baik' : 'Klik untuk menyaring aset dengan kondisi Baik' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-extrabold uppercase tracking-wider transition-colors {{ $isBaikActive ? 'text-[#172554]' : 'text-slate-600 group-hover:text-[#3B82F6]' }}">Baik</span>
                                    @if($isBaikActive)
                                        <span class="bg-[#3B82F6] text-white text-[9px] px-2 py-0.5 rounded-full font-bold">Filter Aktif</span>
                                    @endif
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 {{ $isBaikActive ? 'bg-[#3B82F6] text-white shadow-sm scale-105' : 'bg-blue-100 text-[#3B82F6] group-hover:bg-[#3B82F6] group-hover:text-white' }}">
                                    <i class="fa-solid fa-circle-check text-xs"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-3xl font-black text-[#3B82F6]">{{ $rekapKondisi['Baik'] }}</p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-blue-200/70 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Kondisi Normal</span>
                                <span class="font-bold {{ $isBaikActive ? 'text-[#3B82F6]' : 'text-slate-400 group-hover:text-[#3B82F6]' }} transition-colors">
                                    {{ $isBaikActive ? '✕ Batalkan' : 'Filter Baik →' }}
                                </span>
                            </div>
                        </a>

                        <!-- Card 3: Kondisi Rusak Ringan -->
                        <a href="{{ $urlRingan }}" 
                           class="p-5 rounded-3xl border shadow-sm card-interactive group relative flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl cursor-pointer block {{ $isRinganActive ? 'border-[#EC4899] ring-2 ring-pink-500/50 bg-pink-100/70 shadow-pink-500/20' : 'bg-pink-50/60 border-pink-200/80 hover:border-pink-400' }}"
                           title="{{ $isRinganActive ? 'Klik untuk membatalkan filter kondisi Rusak Ringan' : 'Klik untuk menyaring aset dengan kondisi Rusak Ringan' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-extrabold uppercase tracking-wider transition-colors {{ $isRinganActive ? 'text-pink-700' : 'text-slate-600 group-hover:text-[#EC4899]' }}">Rusak Ringan</span>
                                    @if($isRinganActive)
                                        <span class="bg-[#EC4899] text-white text-[9px] px-2 py-0.5 rounded-full font-bold">Filter Aktif</span>
                                    @endif
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 {{ $isRinganActive ? 'bg-[#EC4899] text-white shadow-sm scale-105' : 'bg-pink-100 text-[#EC4899] group-hover:bg-[#EC4899] group-hover:text-white' }}">
                                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-3xl font-black text-[#EC4899]">{{ $rekapKondisi['Rusak Ringan'] }}</p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-pink-200/70 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Perlu Perbaikan</span>
                                <span class="font-bold {{ $isRinganActive ? 'text-[#EC4899]' : 'text-slate-400 group-hover:text-[#EC4899]' }} transition-colors">
                                    {{ $isRinganActive ? '✕ Batalkan' : 'Filter Ringan →' }}
                                </span>
                            </div>
                        </a>

                        <!-- Card 4: Kondisi Rusak Berat -->
                        <a href="{{ $urlBerat }}" 
                           class="p-5 rounded-3xl border shadow-sm card-interactive group relative flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl cursor-pointer block {{ $isBeratActive ? 'border-[#172554] ring-2 ring-slate-900/50 bg-slate-200/90 shadow-slate-900/20' : 'bg-slate-100/80 border-slate-300/80 hover:border-slate-500' }}"
                           title="{{ $isBeratActive ? 'Klik untuk membatalkan filter kondisi Rusak Berat' : 'Klik untuk menyaring aset dengan kondisi Rusak Berat' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-extrabold uppercase tracking-wider transition-colors {{ $isBeratActive ? 'text-[#172554]' : 'text-slate-600 group-hover:text-[#172554]' }}">Rusak Berat</span>
                                    @if($isBeratActive)
                                        <span class="bg-[#172554] text-white text-[9px] px-2 py-0.5 rounded-full font-bold">Filter Aktif</span>
                                    @endif
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 {{ $isBeratActive ? 'bg-[#172554] text-white shadow-sm scale-105' : 'bg-[#172554] text-white group-hover:scale-105' }}">
                                    <i class="fa-solid fa-circle-xmark text-xs"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-3xl font-black text-[#172554]">{{ $rekapKondisi['Rusak Berat'] }}</p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-300/70 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Rusak Parah/Afkir</span>
                                <span class="font-bold {{ $isBeratActive ? 'text-[#172554]' : 'text-slate-400 group-hover:text-[#172554]' }} transition-colors">
                                    {{ $isBeratActive ? '✕ Batalkan' : 'Filter Berat →' }}
                                </span>
                            </div>
                        </a>

                        <!-- Card 5: Kondisi Hilang -->
                        <a href="{{ $urlHilang }}" 
                           class="p-5 rounded-3xl border shadow-sm card-interactive group relative flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl cursor-pointer block col-span-2 sm:col-span-1 {{ $isHilangActive ? 'border-sky-500 ring-2 ring-sky-500/50 bg-sky-100/80 shadow-sky-500/20' : 'bg-sky-50/60 border-sky-200/80 hover:border-sky-400' }}"
                           title="{{ $isHilangActive ? 'Klik untuk membatalkan filter kondisi Hilang' : 'Klik untuk menyaring aset dengan kondisi Hilang' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-extrabold uppercase tracking-wider transition-colors {{ $isHilangActive ? 'text-sky-800' : 'text-slate-600 group-hover:text-sky-600' }}">Hilang</span>
                                    @if($isHilangActive)
                                        <span class="bg-sky-600 text-white text-[9px] px-2 py-0.5 rounded-full font-bold">Filter Aktif</span>
                                    @endif
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 {{ $isHilangActive ? 'bg-sky-600 text-white shadow-sm scale-105' : 'bg-sky-100 text-sky-600 group-hover:bg-sky-600 group-hover:text-white' }}">
                                    <i class="fa-solid fa-ghost text-xs"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-3xl font-black text-sky-700">{{ $rekapKondisi['Hilang'] }}</p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-sky-200/70 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Tidak Ditemukan</span>
                                <span class="font-bold {{ $isHilangActive ? 'text-sky-700' : 'text-slate-400 group-hover:text-sky-600' }} transition-colors">
                                    {{ $isHilangActive ? '✕ Batalkan' : 'Filter Hilang →' }}
                                </span>
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
                                <span class="font-extrabold text-[#172554] bg-white px-3 py-1 rounded-full border border-blue-200 shadow-xs flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ match($filterKondisi) { 'Baik' => 'bg-[#3B82F6]', 'Rusak Ringan' => 'bg-[#EC4899]', 'Rusak Berat' => 'bg-[#172554]', 'Hilang' => 'bg-sky-500', default => 'bg-slate-400' } }}"></span>
                                    {{ $filterKondisi }}
                                </span>
                                <span class="text-slate-500 font-semibold">({{ count($laporanAset) }} aset ditampilkan)</span>
                            </div>
                            <a href="{{ route('admin.aset', array_merge(request()->except(['filter_kondisi', 'kondisi', 'page']), ['filter_bulan' => $filterBulan, 'filter_tahun' => $filterTahun])) }}" class="text-[#3B82F6] hover:text-blue-800 hover:underline font-bold flex items-center gap-1 transition">
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
                                        'Baik' => 'bg-blue-100 text-[#3B82F6] border border-blue-200',
                                        'Rusak Ringan' => 'bg-pink-100 text-[#EC4899] border border-pink-200',
                                        'Rusak Berat' => 'bg-[#172554] text-white border border-slate-900',
                                        'Hilang' => 'bg-sky-100 text-sky-800 border border-sky-200',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-[#3B82F6]">{{ $item->no_reg_pemda }}</td>
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
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">
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
            <div id="printable-qr-label" class="border-2 border-dashed border-[#3B82F6] rounded-2xl p-4 bg-white space-y-3 mx-auto max-w-[280px] shadow-sm text-center">
                <div class="bg-white p-2 rounded-xl border border-slate-100 shadow-sm inline-block mx-auto">
                    <template x-if="qrItem.id">
                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(qrItem.url)" alt="QR Code" class="w-36 h-36 mx-auto block">
                    </template>
                </div>

                <div class="space-y-1">
                    <span data-qr-field="id" class="font-mono text-[#3B82F6] font-bold block text-sm tracking-wide" x-text="qrItem.id"></span>
                    <p data-qr-field="jenis" class="font-extrabold text-[#172554] text-sm leading-tight" x-text="qrItem.jenis"></p>
                    <p data-qr-field="merek" class="text-slate-600 font-semibold text-xs" x-text="qrItem.merek"></p>
                    <p data-qr-field="penanggung" class="text-slate-500 font-medium text-[11px]" x-text="qrItem.penanggung"></p>
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

                <div class="md:col-span-2 bg-blue-50/70 p-3 rounded-2xl border border-blue-100 flex items-center space-x-2 text-xs text-[#3B82F6] font-semibold">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                    <span>No. Reg / ID Pemda akan digenerate otomatis oleh sistem setelah disimpan (Format: REG-YYYY-XXX).</span>
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
            <form :action="'{{ url('/admin/aset') }}/' + encodeURIComponent(editItem.no_reg_pemda)" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">No. Reg / ID Pemda <span class="text-xs text-slate-400 font-normal">(Otomatis/Read-Only)</span></label>
                    <input type="text" name="no_reg_pemda" x-model="editItem.no_reg_pemda" readonly class="form-input font-mono font-bold text-[#3B82F6] bg-slate-100/80 cursor-not-allowed">
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

            // Mapping Warna Konsisten Sistem
            const CONDITION_COLORS = {
                'Baik': '#3B82F6',         // Royal Blue
                'Rusak Ringan': '#EC4899', // Bright Pink
                'Rusak Berat': '#172554',  // Dark Navy
                'Hilang': '#93C5FD'        // Sky Blue
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
        @page {
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
            font-size: 11pt;
            font-weight: 700;
            color: #2563EB;
            letter-spacing: 0.5px;
            margin-bottom: 1.5mm;
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
        <div class="reg-code">${id}</div>
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
