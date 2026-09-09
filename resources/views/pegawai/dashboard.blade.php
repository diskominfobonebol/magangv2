@extends('layouts.app')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" 
     x-data="{ 
         showDetailModal: false, 
         detailSurat: {}, 
         openDetail(data) { 
             this.detailSurat = data; 
             this.showDetailModal = true; 
         } 
     }">
    <div class="mb-2">
        <h1 class="text-2xl font-bold text-navy">Dashboard Pegawai</h1>
        <p class="text-sm text-slate-500">Selamat datang di portal layanan mandiri Sinosip.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-sm font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Profil & Riwayat -->
        <div class="space-y-6">
            <div class="bg-card-gradient rounded-3xl p-6 text-center">
                <div class="w-20 h-20 text-white rounded-2xl flex items-center justify-center text-2xl font-extrabold mx-auto mb-4 shadow-lg shadow-blue-500/25" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">
                    {{ strtoupper(substr($pegawai->nama ?? 'User', 0, 2)) }}
                </div>
                <h2 class="text-lg font-bold text-navy">{{ $pegawai->nama ?? '-' }}</h2>
                <p class="text-xs text-slate-500 font-semibold mt-1">NIP. {{ $pegawai->nip ?? '-' }}</p>
                
                <div class="mt-6 pt-6 border-t border-blue-200/40 text-left space-y-3 text-sm">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jabatan</span>
                        <span class="font-semibold text-navy">{{ $pegawai->jabatan ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pangkat / Golongan</span>
                        <span class="font-semibold text-navy">{{ $pegawai->pangkat_golongan ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Riwayat Surat / SPT (Tersinkron dengan Data Real SPT/SPPD) -->
            <div class="bg-card-gradient rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-blue-200/40">
                    <h3 class="font-bold text-navy flex items-center gap-2">
                        <i class="fa-solid fa-file-lines text-primary"></i>
                        Riwayat Surat / SPT
                    </h3>
                    @if(isset($pegawai->surats) && $pegawai->surats->count() > 0)
                        <span class="badge-blue text-[11px] font-bold px-2.5 py-0.5 rounded-full">
                            {{ $pegawai->surats->count() }} Surat
                        </span>
                    @endif
                </div>

                @if(isset($pegawai->surats) && $pegawai->surats->count() > 0)
                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                        @foreach($pegawai->surats as $surat)
                            @php
                                $suratData = [
                                    'id' => $surat->id,
                                    'nomor_surat' => $surat->nomor_surat ?? '-',
                                    'perihal' => $surat->perihal ?? 'Surat Tugas',
                                    'jenis_kode' => $surat->jenisSurat->kode ?? 'SPT',
                                    'jenis_nama' => $surat->jenisSurat->nama_jenis ?? 'Surat Tugas',
                                    'tgl_surat' => $surat->tgl_surat ? \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d F Y') : '-',
                                    'tujuan' => $surat->tujuan ?? '-',
                                    'uraian' => $surat->uraian ?? 'Tidak ada uraian tugas.',
                                    'keterangan' => $surat->keterangan ?? 'Tidak ada keterangan tambahan.',
                                    'status' => $surat->status ?? 'Terbit',
                                    'has_sppd' => (bool)$surat->has_sppd,
                                    'pegawais' => $surat->pegawais->map(function($p) use ($pegawai) {
                                        return [
                                            'id' => $p->id,
                                            'nama' => $p->nama,
                                            'nip' => $p->nip,
                                            'jabatan' => $p->jabatan,
                                            'nomor_sppd' => $p->pivot->nomor_sppd ?: '-',
                                            'is_current_user' => ($p->id === $pegawai->id),
                                        ];
                                    })->values()
                                ];
                            @endphp
                            <div class="p-4 bg-white/80 border border-blue-200/50 rounded-2xl hover:border-blue-300 hover:shadow-xs transition-all space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="badge-blue text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                {{ $surat->jenisSurat->kode ?? 'SPT' }}
                                            </span>
                                            @if($surat->has_sppd)
                                                <span class="badge-pink text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    +SPPD
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-slate-400 font-medium">
                                                <i class="fa-regular fa-calendar text-[10px] mr-0.5"></i>
                                                {{ $surat->tgl_surat ? \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d M Y') : '-' }}
                                            </span>
                                        </div>
                                        <p class="font-bold text-navy text-xs mt-1 truncate" title="{{ $surat->nomor_surat }}">
                                            {{ $surat->nomor_surat ?? '-' }}
                                        </p>
                                    </div>
                                    <button type="button" 
                                            @click="openDetail(@js($suratData))" 
                                            class="btn-pill-secondary px-3 py-1.5 text-xs font-bold gap-1.5 text-primary hover:text-blue-700 shrink-0 shadow-2xs">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                        Detail
                                    </button>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                    {{ $surat->perihal ?? '-' }}
                                </p>
                                @if($surat->pivot && $surat->pivot->nomor_sppd)
                                    <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-400 text-[10px]">No. SPPD Anda:</span>
                                        <span class="font-mono font-bold text-pink-600 text-xs">{{ $surat->pivot->nomor_sppd }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-slate-400 bg-white/40 rounded-2xl border border-dashed border-blue-200/60">
                        <i class="fa-regular fa-folder-open text-2xl text-slate-300 mb-2 block"></i>
                        <p class="text-xs text-slate-500 font-medium">Belum ada riwayat surat/SPT.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Surat tugas atau SPPD atas nama Anda akan otomatis muncul di sini.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Status & Jadwal & Upload Berkas -->
        <div class="lg:col-span-2 space-y-6">
            @if(!isset($kenpa) || !$kenpa)
                <div class="bg-card-gradient rounded-3xl p-6 flex items-start gap-4 border border-amber-200">
                    <div class="icon-circle-pink flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-navy">Belum Ada Jadwal</h3>
                        <p class="text-sm text-slate-600 mt-1">Saat ini Anda belum memiliki jadwal pengajuan Kenaikan Pangkat atau Gaji Berkala yang didaftarkan oleh admin.</p>
                    </div>
                </div>
            @else
                <!-- Card Status & Jadwal Pengajuan -->
                <div class="bg-card-gradient rounded-3xl p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-blue-200/40">
                        <h3 class="font-bold text-navy flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Status & Jadwal Pengajuan
                        </h3>
                        <span class="px-3 py-1 badge-blue text-xs font-bold rounded-full">Aktif</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Pengajuan</span>
                            <span class="inline-block mt-1 px-3 py-1 badge-blue font-bold text-xs rounded-full">
                                {{ $kenpa->jenis ?? 'Kenaikan Pangkat' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Status Pegawai</span>
                            <span class="inline-block mt-1 font-bold text-emerald-600">
                                Aktif
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Terakhir</span>
                            <span class="font-semibold text-navy">{{ $kenpa->tanggal_terakhir ?? '06 September 2022' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Jatuh Tempo</span>
                            <span class="font-semibold text-navy">{{ $kenpa->jatuh_tempo ?? '06 September 2026' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Status Persetujuan (ACC)</span>
                            <span class="font-bold text-amber-600">{{ $kenpa->status_berkas ?? 'Menunggu' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Keterangan</span>
                            <span class="text-slate-600 text-xs">{{ $kenpa->keterangan ?? 'Tidak ada keterangan tambahan.' }}</span>
                        </div>
                    </div>

                    <!-- Progress Kelengkapan Berkas -->
                    <div class="mt-6 pt-5 border-t border-blue-200/40">
                        <div class="flex justify-between items-center mb-2 text-sm">
                            <span class="text-slate-600 font-bold">Progres Kelengkapan Berkas</span>
                            <span class="font-extrabold text-primary">{{ $kenpa->progres_berkas ?? 0 }}%</span>
                        </div>
                        <div class="w-full progress-track h-3">
                            <div class="progress-bar-primary h-full transition-all duration-500 rounded-full" style="width: {{ $kenpa->progres_berkas ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Upload Berkas Persyaratan dengan Tab Interaktif -->
                <div class="bg-card-gradient rounded-3xl p-6 space-y-4" x-data="{ tab: '{{ strtolower($kenpa->jenis ?? "kenpa") }}' }">
                    <h3 class="font-bold text-navy mb-2">Upload Berkas Persyaratan</h3>
                    
                    <div class="flex border-b border-blue-200/40 space-x-6 text-sm">
                        <button @click="tab = 'kenpa'" :class="tab === 'kenpa' ? 'tab-btn-active pb-2' : 'tab-btn-inactive pb-2'" type="button">
                            Kenaikan Pangkat
                        </button>
                        <button @click="tab = 'berkala'" :class="tab === 'berkala' ? 'tab-btn-active pb-2' : 'tab-btn-inactive pb-2'" type="button">
                            Kenaikan Gaji Berkala
                        </button>
                    </div>

                    <!-- Konten Tab Kenpa -->
                    <div x-show="tab === 'kenpa'" class="space-y-4 pt-2">
                        @foreach($jenisDokumens as $jd)
                            @php
                                $uploadedDoc = $kenpa->dokumenPegawais->where('jenis_dokumen_id', $jd->id)->first();
                            @endphp
                            <div class="p-4 bg-white/80 border border-blue-200/50 rounded-2xl flex items-center justify-between gap-4 shadow-sm">
                                <div>
                                    <h4 class="text-sm font-bold text-navy">{{ $jd->nama_dokumen }}</h4>
                                    <p class="text-xs text-slate-400">Format: PDF/JPG/PNG (Maks 5MB)</p>
                                </div>
                                <div>
                                    @if($uploadedDoc)
                                        <div class="flex items-center gap-2">
                                            <span class="badge-blue text-xs font-bold px-3 py-1 rounded-full">Terunggah</span>
                                            <a href="{{ asset('storage/' . $uploadedDoc->file_path) }}" target="_blank" class="btn-pill-secondary px-3 py-1.5 text-xs font-semibold">Lihat File</a>
                                        </div>
                                    @else
                                        <form action="{{ route('dashboard.pegawai.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="kenpa_berkala_id" value="{{ $kenpa->id }}">
                                            <input type="hidden" name="jenis_dokumen_id" value="{{ $jd->id }}">
                                            <label class="btn-pill-secondary px-3 py-1.5 text-xs cursor-pointer">
                                                Choose File
                                                <input type="file" name="file_dokumen" required class="hidden" onchange="this.parentElement.nextElementSibling.innerText = this.files[0].name">
                                            </label>
                                            <span class="text-xs text-slate-400 max-w-[100px] truncate">No file chosen</span>
                                            <button type="submit" class="btn-pill-primary px-4 py-1.5 text-xs font-bold">
                                                Upload
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Konten Tab Berkala -->
                    <div x-show="tab === 'berkala'" class="space-y-4 pt-2" style="display: none;">
                        <div class="p-6 text-center text-sm text-slate-500 bg-white/60 rounded-2xl border border-dashed border-blue-200">
                            Daftar berkas persyaratan Kenaikan Gaji Berkala.
                        </div>
                    </div>
                </div>

                <!-- Tabel Berkas Saya -->
                <div class="bg-card-gradient rounded-3xl p-6">
                    <h3 class="font-bold text-navy mb-4">Berkas Saya</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white/60 border-b border-blue-200/40 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">DOKUMEN</th>
                                    <th class="py-3 px-4">STATUS</th>
                                    <th class="py-3 px-4 text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-100/40 text-sm text-slate-700">
                                @forelse($kenpa->dokumenPegawais as $doc)
                                    <tr class="hover:bg-white/40 transition-colors">
                                        <td class="py-3 px-4 font-bold text-navy">
                                            {{ $doc->jenisDokumen->nama_dokumen ?? 'Dokumen' }}
                                            <span class="block text-[10px] text-slate-400 font-normal">{{ $doc->created_at->format('d M Y, H:i') }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-3 py-1 text-xs font-bold rounded-full badge-pink">
                                                {{ ucfirst($doc->status_verifikasi) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-primary font-bold hover:underline text-xs">Lihat File</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-sm text-slate-500">Pegawai belum mengunggah dokumen apapun.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL DETAIL SURAT (READ-ONLY) -->
    <div x-show="showDetailModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
         style="display: none;"
         @keydown.escape.window="showDetailModal = false">
        
        <div class="bg-white rounded-3xl shadow-2xl border border-blue-100 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
             @click.away="showDetailModal = false">
            
            <!-- 1. Header Modal -->
            <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-start justify-between gap-4 bg-slate-50/50">
                <div class="space-y-1">
                    <!-- Badge Jenis Surat & Badge Status -->
                    <div class="flex items-center gap-2">
                        <span class="badge-blue text-xs font-bold px-3 py-0.5 rounded-full uppercase tracking-wider" 
                              x-text="detailSurat.jenis_kode || 'SPT'"></span>
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold px-3 py-0.5 rounded-full" 
                              x-text="detailSurat.status || 'Terbit'"></span>
                    </div>
                    <!-- Judul (nama jenis surat) & Tanggal Terbit -->
                    <h3 class="text-lg font-bold text-navy leading-snug pt-1" x-text="detailSurat.perihal || detailSurat.jenis_nama || 'Surat Tugas'"></h3>
                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar text-primary"></i>
                        <span>Tanggal Terbit: <strong class="text-slate-700" x-text="detailSurat.tgl_surat || '-'"></strong></span>
                    </p>
                </div>
                
                <!-- Tombol Close (x) di Pojok Kanan Atas -->
                <button type="button" 
                        @click="showDetailModal = false" 
                        class="w-9 h-9 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors shrink-0 shadow-2xs">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- 2. Body Modal (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <!-- Dua Kotak Sejajar: Nomor Surat SPT & Tujuan/Instansi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50/90 border border-slate-200/80 rounded-2xl p-3.5">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-hashtag text-primary mr-1"></i> Nomor Surat SPT
                        </span>
                        <p class="text-xs font-bold text-navy font-mono break-all" x-text="detailSurat.nomor_surat || '-'"></p>
                    </div>
                    <div class="bg-slate-50/90 border border-slate-200/80 rounded-2xl p-3.5">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> Tujuan / Instansi
                        </span>
                        <p class="text-xs font-bold text-navy" x-text="detailSurat.tujuan || '-'"></p>
                    </div>
                </div>

                <!-- Field Uraian / Maksud Tugas (Read-Only) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-align-left text-primary mr-1"></i> Uraian / Maksud Tugas
                    </label>
                    <div class="w-full bg-slate-50/90 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-700 leading-relaxed min-h-[56px] whitespace-pre-line select-text" 
                         x-text="detailSurat.uraian || 'Tidak ada uraian tugas.'"></div>
                </div>

                <!-- Field Keterangan Tambahan (Read-Only) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-circle-info text-primary mr-1"></i> Keterangan Tambahan
                    </label>
                    <div class="w-full bg-slate-50/90 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-700 leading-relaxed min-h-[48px] whitespace-pre-line select-text" 
                         x-text="detailSurat.keterangan || 'Tidak ada keterangan tambahan.'"></div>
                </div>

                <!-- Tabel Pegawai yang Ditugaskan & Nomor SPPD -->
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <h4 class="text-xs font-bold text-navy uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-primary"></i> Pegawai yang Ditugaskan & Nomor SPPD
                        </h4>
                        <span class="badge-blue text-[10px] font-bold px-2.5 py-0.5 rounded-full" 
                              x-text="(detailSurat.pegawais ? detailSurat.pegawais.length : 0) + ' Orang'"></span>
                    </div>

                    <div class="border border-slate-200/80 rounded-2xl overflow-hidden shadow-2xs">
                        <div class="overflow-x-auto max-h-56 overflow-y-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-100/90 border-b border-slate-200/80 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky top-0 bg-slate-100">
                                        <th class="py-2.5 px-3 text-center w-10">No</th>
                                        <th class="py-2.5 px-3">Nama Pegawai / NIP</th>
                                        <th class="py-2.5 px-3">Jabatan</th>
                                        <th class="py-2.5 px-3">Nomor SPPD</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="(peg, idx) in (detailSurat.pegawais || [])" :key="peg.id">
                                        <tr :class="peg.is_current_user ? 'bg-blue-50/60 font-medium' : 'hover:bg-slate-50/50'">
                                            <td class="py-2.5 px-3 text-center text-slate-400 font-medium" x-text="idx + 1"></td>
                                            <td class="py-2.5 px-3">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-navy" x-text="peg.nama"></span>
                                                    <template x-if="peg.is_current_user">
                                                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-600 text-white rounded-full uppercase tracking-wider shadow-2xs">
                                                            Anda
                                                        </span>
                                                    </template>
                                                </div>
                                                <span class="block text-[10px] text-slate-400" x-text="'NIP. ' + (peg.nip || '-')"></span>
                                            </td>
                                            <td class="py-2.5 px-3 text-slate-600" x-text="peg.jabatan || '-'"></td>
                                            <td class="py-2.5 px-3 font-mono font-semibold" 
                                                :class="peg.nomor_sppd && peg.nomor_sppd !== '-' ? 'text-pink-600' : 'text-slate-400'" 
                                                x-text="peg.nomor_sppd || '-'"></td>
                                        </tr>
                                    </template>
                                    <template x-if="!detailSurat.pegawais || detailSurat.pegawais.length === 0">
                                        <tr>
                                            <td colspan="4" class="py-4 text-center text-slate-400 italic">
                                                Tidak ada data pegawai yang ditugaskan.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Footer Modal -->
            <div class="px-6 py-4 bg-slate-50/90 border-t border-slate-200/80 flex items-center justify-end">
                <button type="button" 
                        @click="showDetailModal = false" 
                        class="btn-pill-secondary px-6 py-2 text-xs font-bold text-slate-700 hover:text-navy shadow-2xs">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
@endsection