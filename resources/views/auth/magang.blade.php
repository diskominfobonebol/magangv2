@extends('layouts.app')

@section('title', 'Kelola Pendaftaran Magang')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, selectedItem: {} }">

                @if(session('success'))
                    <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="bg-card-gradient rounded-3xl p-6 space-y-4">
                    <h3 class="font-extrabold text-[#172554] text-base">Daftar Pengajuan Magang Mahasiswa</h3>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-100 text-[#172554] uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Mahasiswa</th>
                                    <th class="px-4 py-3">NIM / No HP</th>
                                    <th class="px-4 py-3">Universitas / Jurusan</th>
                                    <th class="px-4 py-3">Periode Magang</th>
                                    <th class="px-4 py-3">Surat Pengantar</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-center">Verifikasi Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($magang as $index => $item)
                                @php
                                    $badgeColor = match($item->status) {
                                        'Diterima' => 'bg-emerald-100 text-emerald-800',
                                        'Ditolak' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-amber-100 text-amber-800'
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 font-medium">{{ $magang->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-bold text-[#172554]">{{ $item->user->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-mono text-slate-800 font-bold">{{ $item->nim ?? '-' }}</div>
                                        <div class="text-slate-400 text-[10px]">{{ $item->no_hp ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $item->universitas }} ({{ $item->jurusan }})</td>
                                    <td class="px-4 py-3">{{ date('d M Y', strtotime($item->tgl_mulai)) }} s.d {{ date('d M Y', strtotime($item->tgl_selesai)) }}</td>
                                    <td class="px-4 py-3">
                                        @if($item->surat_pengantar)
                                            <a href="{{ asset('storage/' . $item->surat_pengantar) }}" target="_blank" class="inline-flex items-center space-x-1 text-[#3B82F6] hover:underline font-bold">
                                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                                <span>Lihat PDF</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full font-bold text-xs {{ $badgeColor }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="showModal = true; selectedItem = {{ json_encode($item) }}" class="btn-pill-primary px-4 py-1.5 text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-1.5 mx-auto" title="Kelola Verifikasi Pendaftaran">
                                            <i class="fa-solid fa-sliders"></i>
                                            <span>Kelola Verifikasi</span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada pendaftaran magang.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Navigasi Pagination Laravel -->
                    <div class="pt-2">
                        {{ $magang->links() }}
                    </div>
                </div>

    <!-- MODAL VERIFIKASI MAGANG -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-100" @click.away="showModal = false">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-black text-[#172554] text-base">Verifikasi Pendaftaran Magang</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/magang') }}/' + selectedItem.id + '/status'" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                
                <div>
                    <label class="block font-bold text-[#172554] mb-1">Status Keputusan</label>
                    <select name="status" x-model="selectedItem.status" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-[#3B82F6] text-sm font-semibold">
                        <option value="Menunggu">Menunggu Verifikasi</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Ditolak">Ditolak (Otomatis Hapus Data dari DB)</option>
                    </select>
                    <p class="text-[11px] text-rose-500 mt-1 font-medium" x-show="selectedItem.status === 'Ditolak'">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Data pengajuan & berkas akan otomatis dihapus permanen dari database saat ditolak.
                    </p>
                </div>

                <div>
                    <label class="block font-bold text-[#172554] mb-1">Nomor Surat Balasan (Opsional)</label>
                    <input type="text" name="no_surat" placeholder="Contoh: 800/DISKOMINFO/102/2026" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-[#3B82F6]">
                </div>

                <div>
                    <label class="block font-bold text-[#172554] mb-1">Upload Berkas Surat Balasan (PDF, Maks 2MB)</label>
                    <input type="file" name="file_surat_balasan" accept=".pdf" class="w-full text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#3B82F6] hover:file:bg-blue-100 border border-slate-300 rounded-xl cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Surat balasan resmi dari Diskominfo yang nantinya dapat diunduh mahasiswa.</p>
                </div>

                <div>
                    <label class="block font-bold text-[#172554] mb-1">Catatan Admin (Opsional)</label>
                    <textarea name="catatan_admin" rows="2" placeholder="Tuliskan catatan tambahan..." class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-[#3B82F6]"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold shadow-sm transition">Batal</button>
                    <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md hover:shadow-lg transition">Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
