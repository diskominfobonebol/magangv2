@extends('layouts.app')

@section('title', 'Kelola Instansi Pengirim')

@section('content')
@php
    $initialItems = $instansis->getCollection()->map(function($item) {
        return [
            'id' => $item->id,
            'nama' => $item->nama,
            'nama_normal' => $item->nama_normal,
            'surat_masuk_count' => (int) $item->surat_masuk_count,
            'created_at_formatted' => \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y'),
        ];
    })->values()->all();
@endphp

<div class="max-w-5xl mx-auto space-y-6" x-data="instansiManager()">

    <!-- Header Halaman & Tombol Kembali -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('surat.masuk') }}" class="p-2 rounded-xl bg-white/70 hover:bg-white text-slate-500 hover:text-navy border border-blue-100 transition-all shadow-sm" title="Kembali ke Surat Masuk">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="text-3xl font-extrabold text-navy">Kelola Instansi Pengirim</h2>
                    <p class="text-slate-500 mt-0.5 text-xs sm:text-sm">Daftar master nama instansi permanen untuk saran combobox Surat Masuk.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200"
                  x-text="totalCount + ' Instansi Terdaftar'">
                {{ $totalInstansi }} Instansi Terdaftar
            </span>
            @if(auth()->user()->role_id == 1)
                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                    Mode Lihat Saja (Admin Master)
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi Server (Session Success / Error) -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    @if(session('error') || (isset($errors) && $errors->any()))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                @if(session('error'))
                    <span>{{ session('error') }}</span>
                @elseif(isset($errors) && $errors->any())
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    <!-- Alert Notifikasi Client / AJAX Tambah Instansi -->
    <div x-show="tambahAlert.show" x-cloak>
        <div :class="tambahAlert.isError ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700'"
             class="p-4 border rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <template x-if="tambahAlert.isError">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
                <template x-if="!tambahAlert.isError">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
                <span x-text="tambahAlert.message"></span>
            </div>
            <button type="button" @click="tambahAlert.show = false" class="hover:opacity-75">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Form Tambah Instansi (Khusus Admin Kasubag - Role 2) -->
    @if(auth()->user()->role_id == 2)
    <div class="bg-card-gradient rounded-3xl p-5 sm:p-6 border border-blue-200/60 shadow-xl shadow-blue-500/5">
        <div class="border-b border-blue-100 pb-2 mb-4">
            <h3 class="text-sm font-bold text-navy uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Instansi Pengirim Baru
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan nama instansi baru secara langsung ke master agar muncul sebagai saran pilihan.</p>
        </div>

        <form action="{{ route('surat-masuk.instansi.store') }}" 
              method="POST" 
              @submit.prevent="submitTambahInstansi($event)"
              class="space-y-2">
            @csrf
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex-1 min-w-0 w-full relative">
                    <label for="nama_instansi_input" class="sr-only">Nama Instansi Pengirim</label>
                    <input type="text" 
                           id="nama_instansi_input"
                           name="nama" 
                           x-model="newNama" 
                           @input="tambahInputError = ''"
                           value="{{ old('nama') }}" 
                           placeholder="Ketik nama instansi pengirim, contoh: Dinas Pendidikan" 
                           aria-label="Nama Instansi Pengirim"
                           required 
                           :disabled="isSubmittingTambah"
                           :class="tambahInputError ? '!border-rose-400 !ring-1 !ring-rose-400' : ''"
                           class="form-input !h-11 !py-2.5 !px-4 text-xs font-semibold w-full @error('nama') !border-rose-400 !ring-1 !ring-rose-400 @enderror">
                </div>
                <button type="submit" 
                        :disabled="isSubmittingTambah"
                        class="btn-pill-primary h-11 px-6 text-xs font-bold gap-2 shadow-md shadow-blue-500/20 flex items-center justify-center flex-shrink-0 w-full sm:w-auto whitespace-nowrap">
                    <template x-if="isSubmittingTambah">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!isSubmittingTambah">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </template>
                    <span x-text="isSubmittingTambah ? 'Menyimpan...' : 'Simpan Instansi'">Simpan Instansi</span>
                </button>
            </div>

            <template x-if="tambahInputError">
                <p class="text-rose-500 text-[11px] font-semibold flex items-center gap-1 mt-1" x-text="tambahInputError"></p>
            </template>
            @error('nama')
                <p class="text-rose-500 text-[11px] font-semibold flex items-center gap-1 mt-1">{{ $message }}</p>
            @enderror
        </form>
    </div>
    @endif

    <!-- Card Tabel Daftar Instansi -->
    <div class="bg-card-gradient rounded-3xl p-5 sm:p-6 border border-blue-200/60 shadow-xl shadow-blue-500/5 space-y-4">
        <!-- Pencarian & Info -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-navy">Daftar Instansi Tersimpan</h3>
            
            <form action="{{ route('surat-masuk.instansi.index') }}" method="GET" class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       name="search" 
                       x-model="searchQuery" 
                       placeholder="Cari nama instansi..." 
                       aria-label="Cari nama instansi"
                       class="form-input !h-10 !py-2 !pl-10 !pr-8 text-xs font-medium w-full">
                <button type="button" 
                        x-show="searchQuery && searchQuery.trim().length > 0" 
                        @click="clearSearch()" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        title="Bersihkan pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>

        <!-- 1. State Kosong Master (totalCount === 0) -->
        <div x-show="totalCount === 0" x-cloak class="py-14 text-center">
            <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-700">Belum Ada Instansi Pengirim</h4>
            <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                Daftar master instansi akan terisi secara otomatis saat Admin Kasubag mencatat surat masuk baru, atau dapat ditambahkan secara manual melalui form di atas.
            </p>
        </div>

        <!-- 2. State Pencarian Tidak Ditemukan (totalCount > 0 && filteredItems.length === 0) -->
        <div x-show="totalCount > 0 && filteredItems.length === 0" x-cloak class="py-14 text-center">
            <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-700">Instansi Tidak Ditemukan</h4>
            <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                Tidak ditemukan nama instansi yang cocok dengan kata kunci <span class="font-bold text-slate-600" x-text="'\'' + searchQuery + '\''"></span>.
            </p>
            <button type="button" @click="clearSearch()" class="mt-3 px-4 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Bersihkan Pencarian
            </button>
        </div>

        <!-- 3. Tabel Data (filteredItems.length > 0) -->
        <div x-show="filteredItems.length > 0" class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-blue-50/60 border-b border-blue-100 text-slate-600 font-bold">
                        <th class="p-3.5 text-center w-12">No</th>
                        <th class="p-3.5">Nama Instansi Pengirim</th>
                        <th class="p-3.5 text-center w-36">Penggunaan</th>
                        <th class="p-3.5 text-center w-36">Terdaftar Sejak</th>
                        @if(auth()->user()->role_id == 2)
                            <th class="p-3.5 text-center w-32">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(item, idx) in filteredItems" :key="item.id">
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="p-3.5 text-center font-bold text-slate-400" x-text="firstItemIndex + idx"></td>
                            <td class="p-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                                    <span class="font-bold text-navy" x-text="item.nama"></span>
                                </div>
                            </td>
                            <td class="p-3.5 text-center">
                                <template x-if="item.surat_masuk_count > 0">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700" x-text="item.surat_masuk_count + ' Surat'"></span>
                                </template>
                                <template x-if="!item.surat_masuk_count || item.surat_masuk_count == 0">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">0 Surat</span>
                                </template>
                            </td>
                            <td class="p-3.5 text-center text-slate-400 font-medium text-[11px]" x-text="item.created_at_formatted"></td>
                            @if(auth()->user()->role_id == 2)
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            @click="openEditModal(item)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            title="Ubah Nama Instansi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            @click="openDeleteModal(item)" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Hapus dari Master Saran">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination (Hanya tampil saat tidak sedang filter client-side dan data ada) -->
        <div class="mt-4" x-show="totalCount > 0 && filteredItems.length > 0 && (!searchQuery || searchQuery.trim() === '')">
            {{ $instansis->links() }}
        </div>
    </div>

    <!-- MODAL EDIT NAMA INSTANSI (Khusus Kasubag) -->
    <div x-show="isEditModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-edit-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isEditModalOpen" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="isEditModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="isEditModalOpen" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-blue-100">
                
                <form :action="'{{ url('/surat/masuk/instansi') }}/' + editId" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                            <h3 class="text-base font-bold text-navy" id="modal-edit-title">Ubah Nama Instansi Pengirim</h3>
                            <button type="button" @click="isEditModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Peringatan Dampak Sinkronisasi Surat Masuk -->
                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <span class="font-bold">Konfirmasi Dampak Pembaruan:</span>
                                <p class="mt-0.5 leading-relaxed text-[11px] text-amber-800">
                                    Saat ini terdapat <span class="font-bold text-amber-950 underline" x-text="editSuratCount + ' data Surat Masuk'"></span> yang menggunakan nama ini. 
                                    Mengubah nama instansi akan <strong>otomatis memperbarui</strong> nilai asal surat pada seluruh surat masuk terkait tersebut dalam satu transaksi database.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="nama_edit_input" class="form-label">Nama Instansi Baru <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   id="nama_edit_input"
                                   name="nama" 
                                   x-model="editNama" 
                                   required 
                                   class="form-input text-xs font-semibold w-full">
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="isEditModalOpen = false" class="btn-pill-secondary px-5 py-2 text-xs font-bold text-slate-600">
                            Batal
                        </button>
                        <button type="submit" class="btn-pill-primary px-6 py-2 text-xs font-bold flex items-center gap-1.5 shadow-md shadow-blue-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS INSTANSI DARI MASTER (Khusus Kasubag) -->
    <div x-show="isDeleteModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-delete-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isDeleteModalOpen" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="isDeleteModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="isDeleteModalOpen" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-rose-100">
                
                <form :action="'{{ url('/surat/masuk/instansi') }}/' + deleteId" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="p-6 space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <h3 class="text-base font-bold text-navy text-center" id="modal-delete-title">Hapus Instansi dari Master Saran?</h3>
                        <p class="text-xs text-slate-600 text-center leading-relaxed">
                            Apakah Anda yakin ingin menghapus <strong class="text-navy" x-text="'\'' + deleteNama + '\''"></strong> dari daftar saran?
                        </p>

                        <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-2xl text-[11px] text-blue-900 leading-relaxed">
                            <span class="font-bold block mb-0.5">Catatan Keamanan Data:</span>
                            Penghapusan ini <strong>hanya menghapus dari daftar saran</strong> combobox. Sebanyak <span class="font-bold" x-text="deleteSuratCount + ' data Surat Masuk'"></span> yang sudah menggunakan nama ini <strong>TIDAK AKAN DIUBAH ATAU DIHAPUS</strong>, dan tetap menyimpan nama aslinya.
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="isDeleteModalOpen = false" class="btn-pill-secondary px-5 py-2 text-xs font-bold text-slate-600">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold rounded-full bg-rose-600 hover:bg-rose-700 text-white transition-all shadow-md shadow-rose-500/20">
                            Ya, Hapus dari Saran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function instansiManager() {
    return {
        // Master data & pagination state
        items: @json($initialItems),
        totalCount: {{ $totalInstansi }},
        searchQuery: '{{ addslashes($search ?? '') }}',
        firstItemIndex: {{ $instansis->firstItem() ?? 1 }},

        // State Form Tambah Instansi
        newNama: '',
        isSubmittingTambah: false,
        tambahInputError: '',
        tambahAlert: { show: false, isError: false, message: '' },

        // State Modal Edit & Delete
        isEditModalOpen: false,
        isDeleteModalOpen: false,
        editId: null,
        editNama: '',
        editSuratCount: 0,
        deleteId: null,
        deleteNama: '',
        deleteSuratCount: 0,

        // Real-time filter getter
        get filteredItems() {
            const q = this.searchQuery.trim().toLowerCase();
            if (!q) {
                return this.items;
            }
            return this.items.filter(item => {
                return item.nama && item.nama.toLowerCase().includes(q);
            });
        },

        // Bersihkan pencarian (kembali ke index penuh jika sebelumnya submit GET)
        clearSearch() {
            this.searchQuery = '';
            @if(!empty($search))
                window.location.href = "{{ route('surat-masuk.instansi.index') }}";
            @endif
        },

        // Handler tambah instansi baru via AJAX dengan fallback
        submitTambahInstansi(event) {
            const raw = this.newNama || '';
            const trimmed = raw.trim();

            this.tambahInputError = '';
            this.tambahAlert.show = false;

            if (!trimmed) {
                this.tambahInputError = 'Nama instansi pengirim wajib diisi.';
                return;
            }

            // Pengecekan cepat duplikat di sisi client
            const dup = this.items.find(it => it.nama.trim().toLowerCase() === trimmed.toLowerCase());
            if (dup) {
                const msg = `Instansi '${trimmed}' sudah terdaftar dalam master.`;
                this.tambahInputError = msg;
                this.tambahAlert = {
                    show: true,
                    isError: true,
                    message: msg
                };
                return;
            }

            this.isSubmittingTambah = true;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                       || document.querySelector('input[name="_token"]')?.value;

            fetch("{{ route('surat-masuk.instansi.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ nama: trimmed })
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const msg = data.message || 'Terjadi kesalahan saat menyimpan instansi.';
                    throw new Error(msg);
                }
                return data;
            })
            .then((data) => {
                if (data.success) {
                    // Masukkan ke posisi teratas daftar agar langsung terlihat
                    if (data.data) {
                        this.items.unshift(data.data);
                    }
                    if (typeof data.total_count !== 'undefined') {
                        this.totalCount = data.total_count;
                    } else {
                        this.totalCount++;
                    }

                    // Reset form & pencarian
                    this.newNama = '';
                    this.tambahInputError = '';
                    this.searchQuery = '';

                    // Munculkan notifikasi sukses
                    this.tambahAlert = {
                        show: true,
                        isError: false,
                        message: data.message || 'Instansi berhasil ditambahkan.'
                    };
                }
            })
            .catch((error) => {
                this.tambahInputError = error.message;
                this.tambahAlert = {
                    show: true,
                    isError: true,
                    message: error.message
                };
            })
            .finally(() => {
                this.isSubmittingTambah = false;
            });
        },

        openEditModal(item) {
            this.editId = item.id;
            this.editNama = item.nama;
            this.editSuratCount = item.surat_masuk_count ?? 0;
            this.isEditModalOpen = true;

            fetch(`{{ url('/surat/masuk/instansi') }}/${item.id}/count`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                if (typeof d.count !== 'undefined') {
                    this.editSuratCount = d.count;
                }
            })
            .catch(() => {});
        },

        openDeleteModal(item) {
            this.deleteId = item.id;
            this.deleteNama = item.nama;
            this.deleteSuratCount = item.surat_masuk_count ?? 0;
            this.isDeleteModalOpen = true;

            fetch(`{{ url('/surat/masuk/instansi') }}/${item.id}/count`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                if (typeof d.count !== 'undefined') {
                    this.deleteSuratCount = d.count;
                }
            })
            .catch(() => {});
        }
    };
}
</script>
@endsection
