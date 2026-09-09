@extends('layouts.app')

@section('title', 'Detail Pengajuan Pegawai')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
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
        
        <!-- Card Profil -->
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
                            $accClass = match($kenpa->status_acc) {
                                'Disetujui', 'ACC' => 'badge-blue',
                                'Ditolak', 'Dikembalikan' => 'badge-pink',
                                default => 'bg-amber-100 text-amber-800 border border-amber-200'
                            };
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full {{ $accClass }}">
                            {{ $kenpa->status_acc ?? 'Menunggu' }}
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
</div>
@endsection