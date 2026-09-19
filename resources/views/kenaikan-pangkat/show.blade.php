@extends('layouts.app')

@section('title', 'Detail Pengajuan Pegawai')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="riwayatSuratDetail()">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-bold text-2xl text-navy leading-tight">
                Detail Pengajuan Pegawai
            </h2>
            <p class="text-sm text-slate-500">{{ $pegawai->nama }} &mdash; NIP. {{ $pegawai->nip }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('kenaikan-pangkat.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-bold gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            <a href="{{ route('kenaikan-pangkat.edit', $pegawai->id) }}" class="btn-pill-primary px-4 py-2 text-xs font-bold gap-1.5 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Jalan Pintas Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Card Profil & Riwayat Surat -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-card-gradient rounded-3xl p-6 flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-2xl flex items-center justify-center text-white text-3xl font-extrabold mb-4 shadow-lg shadow-blue-500/25" style="background: linear-gradient(135deg, #3B82F6 0%, #EC4899 100%);">
                    {{ collect(explode(' ', $pegawai->nama))->map(fn($segment) => substr($segment, 0, 1))->take(2)->join('') }}
                </div>
                <h3 class="text-xl font-bold text-navy mb-1">{{ $pegawai->nama }}</h3>
                <p class="text-slate-500 font-semibold text-xs mb-4">NIP. {{ $pegawai->nip }}</p>
                
                <div class="w-full border-t border-blue-200/40 pt-4 mt-2 space-y-3 text-left">
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Jabatan</p>
                        <p class="text-navy font-semibold text-sm">{{ $pegawai->jabatan }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Pangkat / Golongan</p>
                        <p class="text-navy font-semibold text-sm">{{ $pegawai->pangkat_golongan ?? '-' }}</p>
                    </div>
                </div>

                @if($pegawai->no_wa)
                <div class="w-full mt-6">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pegawai->no_wa) }}" target="_blank" class="w-full btn-pill-primary py-2.5 px-4 text-xs font-bold gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        Hubungi via WhatsApp
                    </a>
                </div>
                @endif
            </div>

            <!-- Card Riwayat Surat / SPT -->
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
                                $sppdPegawai = $surat->pivot->nomor_sppd ?? null;
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
                                            'is_target' => ($p->id == $pegawai->id)
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
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-blue-50 text-primary hover:bg-primary hover:text-white font-bold text-[11px] border border-blue-200/60 shadow-sm transition-all cursor-pointer"
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
                                    @if($surat->has_sppd && $sppdPegawai && $sppdPegawai !== '-')
                                        <span class="font-mono font-bold text-pink-600 text-xs inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500 inline-block"></span>
                                            {{ $sppdPegawai }}
                                        </span>
                                    @elseif($surat->has_sppd)
                                        <span class="text-pink-600 font-semibold text-xs">SPPD Aktif</span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">-</span>
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
                        <p class="text-sm font-medium text-slate-500">Belum ada riwayat surat untuk pegawai ini.</p>
                        <p class="text-xs text-slate-400">Pegawai belum ditugaskan pada surat perintah tugas manapun.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan (Status, Progress, Dokumen) -->
        <div class="md:col-span-2 space-y-6">
            
            @if($kenpa)
            <!-- Card Status & Jadwal -->
            <div class="bg-card-gradient rounded-3xl p-6">
                <h3 class="text-lg font-bold text-navy mb-4 pb-2 border-b border-blue-200/40 flex items-center">
                    <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Status & Jadwal Pengajuan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Jenis Pengajuan</p>
                        <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full badge-blue">
                            {{ $kenpa->jenis }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Status Pegawai</p>
                        <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full {{ $kenpa->status === 'Aktif' ? 'badge-blue' : 'badge-pink' }}">
                            {{ $kenpa->status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Tanggal Terakhir</p>
                        <p class="text-navy font-semibold text-sm">{{ \Carbon\Carbon::parse($kenpa->tgl_terakhir)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Jatuh Tempo</p>
                        <p class="text-navy font-semibold text-sm">{{ \Carbon\Carbon::parse($kenpa->tgl_jatuh_tempo)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Status Persetujuan (ACC)</p>
                        @php
                            $accStatusObj = $accInfo ?? ($pegawai->acc_status ?? null);
                            $badgeClass = $accStatusObj['badge'] ?? 'bg-amber-100 text-amber-800 border border-amber-200';
                            $statusLabel = $accStatusObj['label'] ?? ($kenpa->status_acc ?? 'Menunggu');
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Keterangan</p>
                        <div class="p-3 bg-white/70 rounded-xl border border-blue-100 text-xs text-navy font-medium">
                            {{ $kenpa->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Progres Berkas -->
            <div class="bg-card-gradient rounded-3xl p-6">
                <h3 class="text-lg font-bold text-navy mb-4 pb-2 border-b border-blue-200/40 flex items-center">
                    <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Progres Kelengkapan Berkas
                </h3>
                <div class="mt-4">
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-sm font-bold text-slate-600">Penyelesaian Berkas</span>
                        <span class="text-xl font-extrabold text-primary">{{ $kenpa->progres_berkas }}%</span>
                    </div>
                    <div class="w-full progress-track h-3.5">
                        <div class="progress-bar-primary h-full rounded-full transition-all duration-500 ease-in-out" style="width: {{ $kenpa->progres_berkas }}%"></div>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-card-gradient rounded-3xl p-6 border border-amber-200">
                <p class="text-sm text-amber-800 font-semibold">Data Jadwal/Status Kenaikan Pangkat belum dibuat untuk pegawai ini.</p>
            </div>
            @endif

            <!-- Card Dokumen Berkas -->
            <div class="bg-card-gradient rounded-3xl p-6">
                <h3 class="text-lg font-bold text-navy mb-4 pb-2 border-b border-blue-200/40 flex items-center">
                    <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Daftar Dokumen Pendukung
                </h3>

                @if($pegawai->dokumenPegawais && $pegawai->dokumenPegawais->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100/50">
                        <thead class="bg-white/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Dokumen</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100/40">
                            @foreach($pegawai->dokumenPegawais as $index => $dokumen)
                            <tr class="hover:bg-white/40 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="text-sm font-bold text-navy">{{ $dokumen->jenisDokumen->nama_dokumen ?? 'Dokumen' }}</p>
                                    @if($dokumen->status_verifikasi == 'pending')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-amber-100 text-amber-800">Menunggu Verifikasi</span>
                                    @elseif($dokumen->status_verifikasi == 'disetujui')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full badge-blue">Disetujui</span>
                                    @elseif($dokumen->status_verifikasi == 'ditolak')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full badge-pink">Dikembalikan</span>
                                        @if($dokumen->keterangan_admin)
                                            <p class="mt-1 text-xs text-pink-600 font-medium">Catatan: {{ $dokumen->keterangan_admin }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium">
                                    @if($dokumen->file_path)
                                    <div class="flex flex-col items-end space-y-2">
                                        <div>
                                            <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank" class="btn-pill-primary px-3 py-1 text-xs font-bold">Lihat File</a>
                                        </div>
                                        <!-- Form Verifikasi Admin -->
                                        <form action="{{ route('kenaikan-pangkat.dokumen.verifikasi', $dokumen->id) }}" method="POST" class="mt-2 text-left bg-white/90 p-3 rounded-2xl border border-blue-200/60 w-full sm:w-64 shadow-sm">
                                            @csrf
                                            @method('PUT')
                                            <label class="form-label text-[10px]">Aksi Verifikasi</label>
                                            <select name="status_verifikasi" required class="form-input !h-8 text-xs mb-2">
                                                <option value="">-- Pilih --</option>
                                                <option value="disetujui" {{ $dokumen->status_verifikasi == 'disetujui' ? 'selected' : '' }}>Setujui</option>
                                                <option value="ditolak" {{ $dokumen->status_verifikasi == 'ditolak' ? 'selected' : '' }}>Kembalikan / Tolak</option>
                                            </select>
                                            <textarea name="keterangan_admin" rows="2" placeholder="Catatan jika ditolak..." class="w-full text-xs rounded-xl border border-slate-300 p-2 focus:border-blue-500 focus:outline-none mb-2">{{ $dokumen->keterangan_admin }}</textarea>
                                            <button type="submit" class="btn-pill-primary w-full py-1.5 text-xs font-bold">
                                                Simpan Verifikasi
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                    <span class="text-slate-400 italic text-xs">File tidak tersedia</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 bg-white/50 rounded-2xl border-2 border-dashed border-blue-200">
                    <p class="text-sm font-medium text-slate-500">Pegawai belum mengunggah dokumen apapun.</p>
                </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal Detail Riwayat Surat -->
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
                                        <tr :class="p.is_target ? 'bg-blue-50/60 font-semibold' : 'hover:bg-blue-50/30'">
                                            <td class="py-3 px-3 text-slate-500" x-text="index + 1"></td>
                                            <td class="py-3 px-3">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-navy" x-text="p.nama"></span>
                                                    <template x-if="p.is_target">
                                                        <span class="text-[9px] px-1.5 py-0.2 bg-blue-100 text-primary font-bold rounded">Pegawai Ini</span>
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
                                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="flex justify-between items-center pt-3 border-t border-blue-100">
                        <template x-if="activeSurat.id">
                            <a :href="'/surat/' + activeSurat.id" class="btn-pill-primary px-4 py-2 text-xs font-bold gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                Buka Halaman Surat
                            </a>
                        </template>
                        <button type="button" 
                                @click="closeDetail()" 
                                class="btn-pill-secondary px-5 py-2 text-xs font-bold text-slate-600 hover:text-navy cursor-pointer ml-auto">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function riwayatSuratDetail() {
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
</div>
@endsection