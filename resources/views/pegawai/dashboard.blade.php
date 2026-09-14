@extends('layouts.app')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="pegawaiDashboard()">
    <div class="mb-2">
        <h1 class="text-2xl font-bold text-navy">Dashboard Pegawai</h1>
        <p class="text-sm text-slate-500">Selamat datang di portal layanan mandiri Sinosip.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Profil & Riwayat Surat -->
        <div class="space-y-6">
            <div class="bg-card-gradient rounded-3xl p-6">
                <div class="w-20 h-20 text-white rounded-2xl flex items-center justify-center text-2xl font-extrabold mx-auto mb-4 shadow-lg shadow-blue-500/25" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">
                    {{ strtoupper(substr($pegawai->nama ?? 'User', 0, 2)) }}
                </div>
                <div class="text-center">
                    <h2 class="text-lg font-bold text-navy">{{ $pegawai->nama ?? '-' }}</h2>
                    <p class="text-xs text-slate-500 font-semibold mt-1">NIP. {{ $pegawai->nip ?? '-' }}</p>
                </div>
                
                <div class="mt-6 pt-6 border-t border-blue-200/40 space-y-4 text-sm">
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

            <!-- Riwayat Surat / SPT -->
            <div class="bg-card-gradient rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-blue-200/40">
                    <h3 class="font-bold text-navy flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Riwayat Surat / SPT
                    </h3>
                    @if(isset($pegawai->surats) && $pegawai->surats->count() > 0)
                        <span class="inline-flex items-center text-xs font-bold text-primary bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200/60">
                            {{ $pegawai->surats->count() }} Surat
                        </span>
                    @endif
                </div>

                @if(isset($pegawai->surats) && $pegawai->surats->count() > 0)
                    <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                        @foreach($pegawai->surats as $surat)
                            @php
                                $mySppd = $surat->pivot->nomor_sppd ?? null;
                                $jsonData = [
                                    'id' => $surat->id,
                                    'nomor_surat' => $surat->nomor_surat,
                                    'tgl_surat' => $surat->tgl_surat,
                                    'tgl_formatted' => \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d F Y'),
                                    'perihal' => $surat->perihal ?? 'Surat Perintah Tugas',
                                    'tujuan' => $surat->tujuan ?? '-',
                                    'uraian' => $surat->uraian ?? '',
                                    'keterangan' => $surat->keterangan ?? '',
                                    'has_sppd' => (int)$surat->has_sppd,
                                    'status' => $surat->status ?? 'Terbit',
                                    'jenis' => optional($surat->jenisSurat)->nama_jenis ?? 'Surat Perintah Tugas (SPT)',
                                    'pegawais' => $surat->pegawais->map(function($p) use ($pegawai) {
                                        return [
                                            'id' => $p->id,
                                            'nama' => $p->nama,
                                            'nip' => $p->nip,
                                            'jabatan' => $p->jabatan,
                                            'nomor_sppd' => $p->pivot->nomor_sppd ?? '-',
                                            'is_me' => ($p->id == $pegawai->id)
                                        ];
                                    })->values()->toArray()
                                ];
                            @endphp
                            <div class="p-3.5 bg-white/80 rounded-2xl border border-blue-100/80 hover:border-blue-300 hover:shadow-sm transition-all duration-200 space-y-2.5">
                                <!-- Baris Tanggal, Status, & Tombol Detail -->
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-semibold flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ \Carbon\Carbon::parse($surat->tgl_surat)->translatedFormat('d M Y') }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        @if(($surat->status ?? 'Terbit') === 'Draft')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Draft</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">Terbit</span>
                                        @endif
                                        
                                        <button type="button" 
                                                @click="openDetail({{ json_encode($jsonData) }})" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-blue-50 text-primary hover:bg-primary hover:text-white font-bold text-[11px] border border-blue-200/60 shadow-xs transition-all cursor-pointer"
                                                title="Lihat Detail Surat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Detail
                                        </button>
                                    </div>
                                </div>

                                <!-- Baris Nomor Surat SPT -->
                                <div class="flex items-center justify-between text-xs bg-blue-50/50 px-3 py-2 rounded-xl border border-blue-100/60">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Nomor SPT</span>
                                    <span class="font-mono font-bold text-primary text-xs">{{ $surat->nomor_surat }}</span>
                                </div>

                                <!-- Baris Nomor Surat SPPD (Ringkas) -->
                                <div class="flex items-center justify-between text-xs bg-pink-50/40 px-3 py-2 rounded-xl border border-pink-100/60">
                                    <span class="text-[10px] uppercase font-bold text-pink-500/80 tracking-wider">Nomor SPPD</span>
                                    @if($surat->has_sppd && $mySppd)
                                        <span class="font-mono font-bold text-pink-600 text-xs inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block"></span>
                                            {{ $mySppd }}
                                            <span class="text-[9px] px-1.5 py-0.2 bg-pink-100 text-pink-700 font-sans font-bold rounded">Milik Anda</span>
                                        </span>
                                    @elseif($surat->has_sppd)
                                        <span class="text-pink-600 font-semibold text-xs">SPPD Aktif</span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Tidak Ada SPPD</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-400 flex items-center justify-center mx-auto text-xl font-bold">
                            📄
                        </div>
                        <p class="text-sm font-medium text-slate-500">Belum ada riwayat surat/SPT.</p>
                        <p class="text-xs text-slate-400">Anda belum ditugaskan pada surat perintah tugas manapun.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Jadwal, Progress, Tab Pilihan Kenpa/Berkala, & Upload -->
        <div class="lg:col-span-2 space-y-6">
            @if(!isset($kenpa) || !$kenpa)
                <!-- Tampilan Jika Belum Ada Jadwal -->
                <div class="bg-card-gradient rounded-3xl p-6 flex items-start gap-4 border border-amber-200">
                    <div class="icon-circle-pink flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-navy">Belum Ada Jadwal</h3>
                        <p class="text-sm text-slate-600 mt-1">Saat ini Anda belum memiliki jadwal pengajuan Kenaikan Pangkat atau Gaji Berkala yang didaftarkan oleh admin. Silakan hubungi bagian kepegawaian untuk info lebih lanjut.</p>
                    </div>
                </div>
            @else
                <!-- Card Jadwal Kenaikan Pangkat / Berkala -->
                <div class="bg-card-gradient rounded-3xl p-6 relative overflow-hidden">
                    <div class="flex justify-between items-start mb-4 pb-3 border-b border-blue-200/40">
                        <div>
                            <span class="text-xs font-bold text-primary uppercase tracking-wider">Jadwal {{ $kenpa->jenis ?? 'Kenaikan Pangkat' }}</span>
                            <h3 class="text-lg font-bold text-navy mt-1">Jadwal {{ $kenpa->jenis ?? 'Kenaikan Pangkat' }}</h3>
                        </div>
                        <span class="px-3 py-1 badge-blue text-xs font-bold rounded-full">Aktif</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-blue-200/40 text-sm">
                        <div>
                            <span class="block text-slate-400 text-xs font-bold uppercase">Tenggat Waktu / Jatuh Tempo</span>
                            <span class="font-semibold text-navy">{{ $kenpa->jatuh_tempo ?? '06 October 2026' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-xs font-bold uppercase">Status Persetujuan Berkas</span>
                            <span class="font-bold text-amber-600">{{ $kenpa->status_berkas ?? 'Menunggu' }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-blue-200/40">
                        <div class="flex justify-between items-center mb-2 text-sm">
                            <span class="text-slate-600 font-bold">Penyelesaian Kelengkapan Berkas</span>
                            <span class="font-extrabold text-primary">{{ $kenpa->progres_berkas ?? 0 }}%</span>
                        </div>
                        <div class="w-full progress-track h-3">
                            <div class="progress-bar-primary h-full transition-all duration-500 rounded-full" style="width: {{ $kenpa->progres_berkas ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Upload Berkas Persyaratan dengan Tab Interaktif Kenpa / Berkala -->
                <div class="bg-card-gradient rounded-3xl p-6 space-y-4" x-data="{ tab: '{{ strtolower($kenpa->jenis ?? "kenpa") }}' }">
                    <h3 class="font-bold text-navy mb-2">Upload Berkas Persyaratan</h3>
                    
                    <!-- Tombol Tab Pilihan -->
                    <div class="flex border-b border-blue-200/40 space-x-6 text-sm">
                        <button @click="tab = 'kenpa'" :class="tab === 'kenpa' ? 'tab-btn-active pb-2' : 'tab-btn-inactive pb-2'">
                            Kenaikan Pangkat
                        </button>
                        <button @click="tab = 'berkala'" :class="tab === 'berkala' ? 'tab-btn-active pb-2' : 'tab-btn-inactive pb-2'">
                            Kenaikan Gaji Berkala
                        </button>
                    </div>

                    <!-- Konten Tab: Kenaikan Pangkat (Kenpa) -->
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
                                                <input type="file" name="file_dokumen" required class="hidden" onchange="this.nextElementSibling.innerText = this.files[0].name">
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

                    <!-- Konten Tab: Kenaikan Gaji Berkala (KGB) -->
                    <div x-show="tab === 'berkala'" class="space-y-4 pt-2" style="display: none;">
                        <div class="p-6 text-center text-sm text-slate-500 bg-white/60 rounded-2xl border border-dashed border-blue-200">
                            Silakan pilih pengajuan Kenaikan Gaji Berkala untuk melihat daftar berkas persyaratan KGB.
                        </div>
                    </div>
                </div>

                <!-- Tabel Berkas Saya di Bawah -->
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

    <!-- Modal Detail Riwayat Surat (Reuse Tampilan Detail Nomor Surat) -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="closeDetail()">
        
        <!-- Backdrop -->
        <div x-show="showDetailModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="closeDetail()"></div>

        <!-- Modal Dialog -->
        <div x-show="showDetailModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-card-gradient rounded-3xl p-6 sm:p-8 max-w-3xl w-full shadow-2xl border border-blue-200/60 space-y-6 z-10 max-h-[90vh] overflow-y-auto">
            
            <template x-if="activeSurat">
                <div class="space-y-6">
                    <!-- Header Modal -->
                    <div class="flex items-start justify-between border-b border-blue-100 pb-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="badge-blue px-3 py-0.5 rounded-full text-xs font-extrabold uppercase" x-text="activeSurat.jenis || 'SPT'"></span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                      :class="activeSurat.status === 'Draft' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200'"
                                      x-text="activeSurat.status || 'Terbit'"></span>
                            </div>
                            <h3 class="text-xl font-bold text-navy pt-1" x-text="activeSurat.perihal || 'Surat Tugas'"></h3>
                            <p class="text-xs text-slate-500 font-semibold flex items-center gap-1">
                                <span>Tanggal Terbit:</span>
                                <span class="text-navy font-bold" x-text="activeSurat.tgl_formatted"></span>
                            </p>
                        </div>
                        <button type="button" 
                                @click="closeDetail()" 
                                class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer"
                                title="Tutup">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Nomor Surat & Tujuan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50/60 p-3.5 rounded-2xl border border-blue-100 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Surat SPT</span>
                            <p class="text-sm font-mono font-bold text-primary break-all" x-text="activeSurat.nomor_surat"></p>
                        </div>
                        <div class="bg-blue-50/60 p-3.5 rounded-2xl border border-blue-100 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tujuan / Instansi</span>
                            <p class="text-sm font-bold text-navy" x-text="activeSurat.tujuan || '-'"></p>
                        </div>
                    </div>

                    <!-- Uraian / Maksud Surat -->
                    <template x-if="activeSurat.uraian">
                        <div class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Uraian / Maksud Tugas</span>
                            <div class="text-xs text-slate-700 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/60 leading-relaxed" x-text="activeSurat.uraian"></div>
                        </div>
                    </template>

                    <!-- Keterangan Tambahan -->
                    <template x-if="activeSurat.keterangan">
                        <div class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Keterangan Tambahan</span>
                            <div class="text-xs text-slate-700 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/60 leading-relaxed" x-text="activeSurat.keterangan"></div>
                        </div>
                    </template>

                    <!-- Daftar Pegawai yang Ditugaskan & Nomor SPPD -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-navy uppercase tracking-wider">Pegawai yang Ditugaskan & Nomor SPPD</span>
                            <span class="text-xs font-bold bg-blue-100 text-primary px-2.5 py-0.5 rounded-full" x-text="(activeSurat.pegawais || []).length + ' Orang'"></span>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white/60">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-blue-100 bg-blue-50/50 text-slate-400 font-bold uppercase text-[11px]">
                                        <th class="py-2.5 px-3 w-10">No</th>
                                        <th class="py-2.5 px-3">Nama Pegawai / NIP</th>
                                        <th class="py-2.5 px-3">Jabatan</th>
                                        <th class="py-2.5 px-3">Nomor SPPD</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-50">
                                    <template x-for="(p, index) in (activeSurat.pegawais || [])" :key="'peg-' + p.id">
                                        <tr :class="p.is_me ? 'bg-blue-50/60 font-semibold' : 'hover:bg-blue-50/30'">
                                            <td class="py-3 px-3 text-slate-500" x-text="index + 1"></td>
                                            <td class="py-3 px-3">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-navy" x-text="p.nama"></span>
                                                    <template x-if="p.is_me">
                                                        <span class="text-[9px] px-1.5 py-0.2 bg-blue-100 text-primary font-bold rounded">Anda</span>
                                                    </template>
                                                </div>
                                                <div class="text-[10px] text-slate-400" x-text="'NIP. ' + p.nip"></div>
                                            </td>
                                            <td class="py-3 px-3 text-slate-600" x-text="p.jabatan || '-'"></td>
                                            <td class="py-3 px-3">
                                                <template x-if="activeSurat.has_sppd && p.nomor_sppd && p.nomor_sppd !== '-'">
                                                    <span class="font-mono font-bold text-pink-600 inline-flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block"></span>
                                                        <span x-text="p.nomor_sppd"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!activeSurat.has_sppd || !p.nomor_sppd || p.nomor_sppd === '-'">
                                                    <span class="text-slate-400 italic text-[11px]">Tidak Ada SPPD</span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="flex justify-end pt-3 border-t border-blue-100">
                        <button type="button" 
                                @click="closeDetail()" 
                                class="btn-pill-secondary px-5 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function pegawaiDashboard() {
        return {
            showDetailModal: false,
            activeSurat: null,
            openDetail(data) {
                this.activeSurat = data;
                this.showDetailModal = true;
            },
            closeDetail() {
                this.showDetailModal = false;
                this.activeSurat = null;
            }
        };
    }
</script>
@endsection