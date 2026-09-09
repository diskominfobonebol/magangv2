@extends('layouts.app')

@section('title', 'Dashboard - Admin Master')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-card-gradient p-6 rounded-3xl border border-blue-200/50 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-navy">Selamat Datang, {{ Auth::user()->name }}!</h2>
            <p class="text-slate-600 mt-1">Anda login sebagai <span class="font-bold text-primary">Admin Master</span>. Seluruh modul Sinosip terintegrasi dalam satu sistem terpadu.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold badge-gradient shadow-sm">
            <span>📍 Diskominfo Bone Bolango</span>
        </div>
    </div>

    <!-- 4 Main Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <a href="{{ route('kenaikan-pangkat.index') }}" class="p-6 bg-card-gradient rounded-3xl card-interactive flex items-center gap-4 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-blue flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</p>
                <p class="text-2xl font-extrabold text-navy">{{ $totalPegawai ?? 0 }}</p>
                <span class="text-[10px] text-primary font-bold">Kenaikan Pangkat & KGB &rarr;</span>
            </div>
        </a>
        
        <a href="{{ route('surat.index') }}" class="p-6 bg-card-gradient rounded-3xl card-interactive flex items-center gap-4 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-pink flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Arsip Surat</p>
                <p class="text-2xl font-extrabold text-navy">{{ $totalSurat ?? 0 }}</p>
                <span class="text-[10px] text-[#EC4899] font-bold">SPT & SPPD &rarr;</span>
            </div>
        </a>

        <a href="{{ route('admin.aset') }}" class="p-6 bg-card-gradient rounded-3xl card-interactive flex items-center gap-4 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-blue flex-shrink-0">
                <i class="fa-solid fa-boxes-stacked text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aset & Mesin</p>
                <p class="text-2xl font-extrabold text-navy">{{ $totalAset ?? 0 }}</p>
                <span class="text-[10px] text-primary font-bold">Manajemen Aset & QR &rarr;</span>
            </div>
        </a>

        <a href="{{ route('admin.magang') }}" class="p-6 bg-card-gradient rounded-3xl card-interactive flex items-center gap-4 border border-blue-200/50 shadow-sm">
            <div class="icon-circle-navy flex-shrink-0">
                <i class="fa-solid fa-user-graduate text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peserta Magang</p>
                <p class="text-2xl font-extrabold text-navy">{{ $totalMagang ?? 0 }}</p>
                <span class="text-[10px] text-primary font-bold">Verifikasi Pengajuan &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-card-gradient p-6 rounded-3xl border border-blue-200/50 shadow-sm space-y-4">
        <h3 class="font-extrabold text-navy text-lg">Pintasan Fitur Cepat</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('surat.create') }}" class="btn-pill-primary py-3 px-4 text-xs font-bold gap-2">
                <i class="fa-solid fa-plus"></i> Buat Nomor Surat
            </a>
            <a href="{{ route('kenaikan-pangkat.index') }}" class="btn-pill-secondary py-3 px-4 text-xs font-bold gap-2">
                <i class="fa-solid fa-award text-primary"></i> Data KGB & Pangkat
            </a>
            <a href="{{ route('admin.aset') }}" class="btn-pill-secondary py-3 px-4 text-xs font-bold gap-2">
                <i class="fa-solid fa-qrcode text-[#EC4899]"></i> Cetak Label QR Aset
            </a>
            <a href="{{ route('admin.magang') }}" class="btn-pill-secondary py-3 px-4 text-xs font-bold gap-2">
                <i class="fa-solid fa-user-check text-primary"></i> Verifikasi Magang
            </a>
        </div>
    </div>
</div>
@endsection
