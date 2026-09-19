@extends('layouts.app')

@section('title', 'Dashboard - Admin Master')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-card-gradient p-6 rounded-3xl border border-blue-200/50 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-navy">Selamat Datang, {{ Auth::user()->name ?? Auth::user()->nama }}!</h2>
            <p class="text-slate-600 mt-1">Anda login sebagai <span class="font-bold text-primary">Admin Master</span>. Seluruh modul layanan SIMPATIK & Sinosip terintegrasi dalam satu sistem terpadu.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold badge-gradient shadow-sm shrink-0">
            <span>📍 Diskominfo Bone Bolango</span>
        </div>
    </div>

    <!-- 5 Main Statistics Cards (Semua Modul Terintegrasi) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Pegawai & Pangkat -->
        <a href="{{ route('kenaikan-pangkat.index') }}" class="p-5 bg-card-gradient rounded-3xl card-interactive flex items-center gap-3.5 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-blue flex-shrink-0">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Total Pegawai</p>
                <p class="text-2xl font-black text-navy">{{ $totalPegawai ?? 0 }}</p>
                <span class="text-[10px] text-primary font-bold">KGB & Pangkat &rarr;</span>
            </div>
        </a>
        
        <!-- 2. Surat Keluar (SPT/SPPD) -->
        <a href="{{ route('surat.index') }}" class="p-5 bg-card-gradient rounded-3xl card-interactive flex items-center gap-3.5 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-pink flex-shrink-0">
                <i class="fa-solid fa-envelope-open-text text-lg"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Surat Keluar</p>
                <p class="text-2xl font-black text-navy">{{ $totalSurat ?? 0 }}</p>
                <span class="text-[10px] text-[#EC4899] font-bold">SPT & SPPD &rarr;</span>
            </div>
        </a>

        <!-- 3. Surat Masuk -->
        <a href="{{ route('surat-masuk.index') }}" class="p-5 bg-card-gradient rounded-3xl card-interactive flex items-center gap-3.5 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-navy flex-shrink-0">
                <i class="fa-solid fa-inbox text-lg"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Surat Masuk</p>
                <p class="text-2xl font-black text-navy">{{ $totalSuratMasuk ?? 0 }}</p>
                <span class="text-[10px] text-primary font-bold">Agenda & Disposisi &rarr;</span>
            </div>
        </a>

        <!-- 4. Aset Daerah -->
        <a href="{{ route('admin.aset') }}" class="p-5 bg-card-gradient rounded-3xl card-interactive flex items-center gap-3.5 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-blue flex-shrink-0">
                <i class="fa-solid fa-boxes-stacked text-lg"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Aset & Mesin</p>
                <p class="text-2xl font-black text-navy">{{ $totalAset ?? 0 }}</p>
                <span class="text-[10px] text-primary font-bold">Label & QR &rarr;</span>
            </div>
        </a>

        <!-- 5. Pendaftar Magang -->
        <a href="{{ route('admin.magang') }}" class="p-5 bg-card-gradient rounded-3xl card-interactive flex items-center gap-3.5 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-pink flex-shrink-0">
                <i class="fa-solid fa-user-graduate text-lg"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Peserta Magang</p>
                <p class="text-2xl font-black text-navy">{{ $totalMagang ?? 0 }}</p>
                <span class="text-[10px] text-[#EC4899] font-bold">Verifikasi &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-card-gradient p-6 rounded-3xl border border-blue-200/50 shadow-sm space-y-4">
        <h3 class="font-extrabold text-navy text-lg">Pintasan Fitur Cepat</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('surat.create') }}" class="btn-pill-primary py-3 px-3 text-xs font-bold gap-1.5 text-center justify-center">
                <i class="fa-solid fa-plus"></i>
                <span class="truncate">Nomor SPT/SPPD</span>
            </a>
            <a href="{{ route('surat-masuk.index') }}" class="btn-pill-secondary py-3 px-3 text-xs font-bold gap-1.5 text-center justify-center">
                <i class="fa-solid fa-inbox text-primary"></i>
                <span class="truncate">Surat Masuk</span>
            </a>
            <a href="{{ route('kenaikan-pangkat.index') }}" class="btn-pill-secondary py-3 px-3 text-xs font-bold gap-1.5 text-center justify-center">
                <i class="fa-solid fa-award text-primary"></i>
                <span class="truncate">KGB & Pangkat</span>
            </a>
            <a href="{{ route('admin.aset') }}" class="btn-pill-secondary py-3 px-3 text-xs font-bold gap-1.5 text-center justify-center">
                <i class="fa-solid fa-qrcode text-[#EC4899]"></i>
                <span class="truncate">Label QR Aset</span>
            </a>
            <a href="{{ route('admin.magang') }}" class="btn-pill-secondary py-3 px-3 text-xs font-bold gap-1.5 text-center justify-center">
                <i class="fa-solid fa-user-check text-primary"></i>
                <span class="truncate">Kelola Magang</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn-pill-secondary py-3 px-3 text-xs font-bold gap-1.5 text-center justify-center">
                <i class="fa-solid fa-users-gear text-indigo-600"></i>
                <span class="truncate">Manajemen User</span>
            </a>
        </div>
    </div>
</div>
@endsection
