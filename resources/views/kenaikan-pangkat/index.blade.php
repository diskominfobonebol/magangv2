@extends('layouts.app')

@section('title', 'Kenaikan Pangkat & Berkala')

@section('content')
<!-- BUNGKUS UTAMA ALPINE.JS -->
<div x-data="{ modalBuka: false }" class="space-y-6">

    <!-- Header & Button -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-navy">Kenaikan Pangkat & Berkala</h2>
            <p class="text-slate-500 text-sm mt-1">Pantau jadwal dan progres unggah berkas seluruh pegawai</p>
        </div>
        <button @click="modalBuka = true" type="button" class="btn-pill-primary px-5 py-2.5 text-sm gap-2 shadow-lg shadow-blue-500/25">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pegawai
        </button>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-card-gradient rounded-3xl p-5 card-interactive">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">TOTAL</p>
            <p class="text-2xl font-extrabold text-navy">{{ $metrics['total'] }}</p>
        </div>
        <div class="bg-card-gradient rounded-3xl p-5 card-interactive relative overflow-hidden">
            <div class="absolute right-0 top-0 w-2 h-full" style="background-color: var(--color-pink);"></div>
            <p class="text-[10px] font-bold text-pink-accent uppercase tracking-wider mb-1">MENDEKATI JT</p>
            <p class="text-2xl font-extrabold text-navy">{{ $metrics['mendekati_jt'] }}</p>
        </div>
        <div class="bg-card-gradient rounded-3xl p-5 card-interactive relative overflow-hidden">
            <div class="absolute right-0 top-0 w-2 h-full bg-red-500"></div>
            <p class="text-[10px] font-bold text-red-600 uppercase tracking-wider mb-1">LEWAT JT</p>
            <p class="text-2xl font-extrabold text-navy">{{ $metrics['lewat_jt'] }}</p>
        </div>
        <div class="bg-card-gradient rounded-3xl p-5 card-interactive">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">BELUM LENGKAP</p>
            <p class="text-2xl font-extrabold text-navy">{{ $metrics['belum_lengkap'] }}</p>
        </div>
        <div class="bg-card-gradient rounded-3xl p-5 card-interactive relative overflow-hidden">
            <div class="absolute right-0 top-0 w-2 h-full" style="background-color: var(--color-primary);"></div>
            <p class="text-[10px] font-bold text-primary uppercase tracking-wider mb-1">BERKAS LENGKAP</p>
            <p class="text-2xl font-extrabold text-navy">{{ $metrics['berkas_lengkap'] }}</p>
        </div>
    </div>

    <!-- Bagian Distribusi Status Pengajuan -->
    <div class="bg-card-gradient rounded-3xl p-6 space-y-4">
        <h3 class="font-bold text-navy">Distribusi Status Pengajuan</h3>
        <div class="w-full progress-track h-3 flex overflow-hidden rounded-full">
            <div class="bg-amber-400 h-full transition-all duration-500" style="width: {{ ($progress['menunggu'] / $total_progress) * 100 }}%" title="Menunggu: {{ $progress['menunggu'] }}"></div>
            <div class="bg-indigo-500 h-full transition-all duration-500" style="width: {{ ($progress['diproses'] / $total_progress) * 100 }}%" title="Diproses: {{ $progress['diproses'] }}"></div>
            <div class="bg-blue-500 h-full transition-all duration-500" style="width: {{ ($progress['disetujui'] / $total_progress) * 100 }}%" title="Disetujui: {{ $progress['disetujui'] }}"></div>
            <div class="bg-pink-500 h-full transition-all duration-500" style="width: {{ ($progress['ditolak'] / $total_progress) * 100 }}%" title="Ditolak: {{ $progress['ditolak'] }}"></div>
        </div>
        <div class="flex flex-wrap gap-6 text-xs font-bold text-slate-600 pt-1">
            <span class="flex items-center gap-2"><span class="w-3 h-3 bg-amber-400 rounded-full inline-block"></span> Menunggu ({{ $progress['menunggu'] }})</span>
            <span class="flex items-center gap-2"><span class="w-3 h-3 bg-indigo-500 rounded-full inline-block"></span> Diproses ({{ $progress['diproses'] }})</span>
            <span class="flex items-center gap-2"><span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span> Disetujui / ACC ({{ $progress['disetujui'] }})</span>
            <span class="flex items-center gap-2"><span class="w-3 h-3 bg-pink-500 rounded-full inline-block"></span> Ditolak / Dikembalikan ({{ $progress['ditolak'] }})</span>
        </div>
    </div>

    <!-- Bagian Filter & Pencarian Form -->
    <form method="GET" action="{{ route('kenaikan-pangkat.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-card-gradient p-4 rounded-3xl items-center">
        <!-- Input Cari Nama atau NIP -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau NIP..." class="form-input !pl-10">
        </div>

        <!-- Dropdown Jenis Kenaikan -->
        <select name="jenis" onchange="this.form.submit()" class="form-input">
            <option value="">Semua Jenis Kenaikan</option>
            <option value="Kenaikan Pangkat" {{ request('jenis') == 'Kenaikan Pangkat' ? 'selected' : '' }}>Kenaikan Pangkat</option>
            <option value="Gaji Berkala" {{ request('jenis') == 'Gaji Berkala' ? 'selected' : '' }}>Gaji Berkala</option>
            <option value="Keduanya" {{ request('jenis') == 'Keduanya' ? 'selected' : '' }}>Keduanya</option>
        </select>

        <!-- Dropdown Status Jadwal -->
        <select name="status" onchange="this.form.submit()" class="form-input">
            <option value="">Semua Status Jadwal</option>
            <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Tidak Aktif" {{ request('status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>

        <!-- Dropdown Status ACC -->
        <select name="acc" onchange="this.form.submit()" class="form-input">
            <option value="">Semua Status ACC</option>
            <option value="Menunggu" {{ request('acc') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
            <option value="Diproses" {{ request('acc') == 'Diproses' ? 'selected' : '' }}>Diproses</option>
            <option value="Disetujui" {{ request('acc') == 'Disetujui' || request('acc') == 'ACC' ? 'selected' : '' }}>Disetujui / ACC</option>
            <option value="Ditolak" {{ request('acc') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </form>

    <!-- Table Card Container -->
    <div class="bg-card-gradient rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-blue-100/50">
                <thead class="bg-white/70">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama / NIP</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Jadwal (Jatuh Tempo & TMT)</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status Jadwal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-48">Progres Berkas</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">ACC</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-100/40">
                    @forelse($pegawai as $p)
                    @php 
                        $pengajuan = $p->kenpaBerkalas->first(); 
                        $jenisVal = $pengajuan ? $pengajuan->jenis : ''; 
                        $statusJadwalVal = $pengajuan ? $pengajuan->status : 'Aktif'; 
                        $progres = $pengajuan ? $pengajuan->progres_berkas : 0; 
                        $accInfo = $p->acc_status_info ?? $p->acc_status;
                    @endphp
                    <tr class="hover:bg-white/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-navy">{{ $p->nama }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ ($p->kategori_pegawai ?? 'ASN') === 'P3K' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                    {{ $p->kategori_pegawai ?? 'ASN' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-semibold">{{ $p->nip }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge-pink inline-flex items-center px-3 py-1 rounded-full text-xs font-bold">
                                {{ $jenisVal ?: '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="text-sm font-bold text-navy flex items-center gap-1.5" title="Tanggal Jatuh Tempo">
                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $pengajuan && $pengajuan->tgl_jatuh_tempo ? \Carbon\Carbon::parse($pengajuan->tgl_jatuh_tempo)->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="text-xs text-slate-500 font-medium">
                                    TMT: {{ $pengajuan && $pengajuan->tgl_terakhir ? \Carbon\Carbon::parse($pengajuan->tgl_terakhir)->format('d M Y') : '-' }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold badge-navy">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> 
                                {{ $statusJadwalVal }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="flex-grow progress-track h-2">
                                    <div class="progress-bar-primary h-2 rounded-full transition-all duration-500" style="width: {{ $progres }}%"></div>
                                </div>
                                <span class="text-xs font-extrabold text-navy w-8 text-right">{{ $progres }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($accInfo['key'] == 'disetujui')
                                <span class="badge-blue inline-flex items-center px-3 py-1 rounded-full text-xs font-bold">ACC</span>
                            @elseif($accInfo['key'] == 'diproses')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">{{ $accInfo['label'] }}</span>
                            @elseif($accInfo['key'] == 'ditolak')
                                <span class="badge-pink inline-flex items-center px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Tombol Edit -->
                                <a href="{{ route('kenaikan-pangkat.edit', $p->id) }}" class="btn-pill-secondary px-3 py-1.5 text-xs font-bold">
                                    Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('kenaikan-pangkat.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-50 text-rose-600 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold rounded-full transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </form>

                                <!-- Tombol Detail -->
                                <a href="{{ route('kenaikan-pangkat.show', $p->id) }}" class="btn-pill-primary px-3 py-1.5 text-xs font-bold gap-1 group">
                                    Detail 
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="font-semibold text-navy">Tidak ada data pegawai ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-blue-200/40">
            {{ $pegawai->links() }}
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL TAMBAH PEGAWAI (FIXED ALPINE.JS MODAL) -->
    <!-- ============================================== -->
    <div x-show="modalBuka" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
            
            <!-- Background overlay -->
            <div x-show="modalBuka" 
                 x-transition.opacity 
                 class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" 
                 @click="modalBuka = false"></div>

            <!-- Panel Form Modal -->
            <div x-show="modalBuka" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative inline-block align-bottom bg-modal-gradient rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-50 border border-blue-200/60">
                
                <form action="{{ route('kenaikan-pangkat.store') }}" method="POST">
                    @csrf
                    <div class="p-6 sm:p-8">
                        <h3 class="text-xl font-bold text-navy mb-6 border-b border-blue-200/40 pb-3" id="modal-title">Tambah Pegawai Baru</h3>
                        
                        <!-- BAGIAN FORM INPUT -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nama Lengkap -->
                            <div class="sm:col-span-2">
                                <label class="form-label">Nama Lengkap & Gelar</label>
                                <input type="text" name="nama" required placeholder="Cth: Dr. H. Syamsuddin, M.Si" class="form-input">
                            </div>
                            
                            <!-- NIP -->
                            <div>
                                <label class="form-label">NIP</label>
                                <input type="text" name="nip" required placeholder="Nomor Induk Pegawai" class="form-input">
                                <p class="text-[10px] text-slate-500 mt-1">*Otomatis jadi username login.</p>
                            </div>

                            <!-- No. WhatsApp -->
                            <div>
                                <label class="form-label">No. WhatsApp</label>
                                <input type="text" name="no_wa" required placeholder="Cth: 081234567890" class="form-input">
                                <p class="text-[10px] text-slate-500 mt-1">*Untuk notifikasi pengingat.</p>
                            </div>
                            
                            <!-- Jabatan -->
                            <div>
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="jabatan" required placeholder="Nama jabatan saat ini" class="form-input">
                            </div>

                            <!-- Kategori Pegawai -->
                            <div>
                                <label class="form-label">Kategori Pegawai</label>
                                <input type="text" value="ASN (PNS)" readonly class="form-input bg-blue-50/60 font-bold text-navy cursor-not-allowed">
                                <input type="hidden" name="kategori_pegawai" value="ASN">
                            </div>

                            <!-- Password Login -->
                            <div class="sm:col-span-2">
                                <label class="form-label">Password Login</label>
                                <input type="text" name="password" required value="123456" placeholder="123456" class="form-input">
                                <p class="text-[10px] text-slate-500 mt-1">*Default terisi 123456.</p>
                            </div>
                            
                            <!-- Jenis Kenaikan -->
                            <div class="sm:col-span-2">
                                <label class="form-label">Jenis Kenaikan</label>
                                <select name="jenis" required class="form-input">
                                    <option value="" disabled selected>-- Pilih Jenis Kenaikan / Berkala --</option>
                                    <option value="Kenaikan Pangkat">Kenaikan Pangkat</option>
                                    <option value="Berkala">Gaji Berkala</option>
                                    <option value="Keduanya">Keduanya (Pangkat & Berkala)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/60 px-6 py-4 sm:px-8 flex justify-end gap-3 border-t border-blue-200/40">
                        <button type="button" @click="modalBuka = false" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                            Simpan Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection