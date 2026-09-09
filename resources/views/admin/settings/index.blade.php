@extends('layouts.app')

@section('title', 'Pengaturan WhatsApp')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="mb-2">
        <h1 class="text-2xl font-bold text-navy">Pengaturan Sistem & API WhatsApp</h1>
        <p class="text-sm text-slate-500">Kelola token API gateway WhatsApp (Fonnte/Wablas) secara terpusat.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 space-y-6">
        @csrf

        <div>
            <label class="form-label">API Token WhatsApp (Fonnte / Provider Lain)</label>
            <input type="text" name="wa_token" value="{{ $waToken ?? '' }}" placeholder="Masukkan token API di sini..." class="form-input">
        </div>

        <div>
            <label class="form-label">Nomor / Device ID Pengirim (Opsional)</label>
            <input type="text" name="wa_device" value="{{ $waDevice ?? '' }}" placeholder="Contoh: 08123456789 atau Device ID" class="form-input">
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection