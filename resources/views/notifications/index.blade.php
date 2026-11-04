@extends('layouts.app')

@section('title', 'Pusat Notifikasi')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman & Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-extrabold text-navy">Notifikasi</h2>
                @if($unreadCount > 0)
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-600 border border-rose-200">
                        {{ $unreadCount }} Belum Dibaca
                    </span>
                @endif
            </div>
            <p class="text-slate-500 mt-1 text-sm">Semua pemberitahuan dan informasi penting terkait aktivitas akun Anda.</p>
        </div>

        <div class="flex items-center gap-3">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-pill-secondary px-4 py-2.5 text-xs font-bold gap-2 shadow-sm flex items-center hover:text-primary transition-all">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi Sukses / Error -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    <!-- Tab Filter -->
    <div class="flex items-center gap-2 border-b border-blue-100 pb-2">
        <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
           class="px-4 py-2 text-xs font-bold rounded-xl transition-all {{ $filter !== 'unread' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80' }}">
            Semua ({{ auth()->user()->notifications()->count() }})
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
           class="px-4 py-2 text-xs font-bold rounded-xl transition-all {{ $filter === 'unread' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-white/80' }}">
            Belum Dibaca ({{ $unreadCount }})
        </a>
    </div>

    <!-- Daftar Notifikasi -->
    <div class="bg-card-gradient rounded-3xl p-4 sm:p-6 border border-blue-200/60 shadow-xl shadow-blue-500/5">
        @if($notifications->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Tidak ada notifikasi</h3>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca saat ini.' : 'Belum ada notifikasi yang tersimpan untuk akun Anda.' }}
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isRead = !is_null($notification->read_at);
                        $title = $data['title'] ?? 'Pemberitahuan';
                        $message = $data['message'] ?? '';
                        $url = $data['url'] ?? '#';
                        $type = $data['type'] ?? 'general';
                    @endphp

                    <div class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ !$isRead ? 'bg-white/95 border-blue-200 shadow-sm ring-1 ring-blue-100' : 'bg-white/60 border-slate-200/60 opacity-80 hover:opacity-100' }}">
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 {{ !$isRead ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                @if(str_contains($type, 'security'))
                                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                @elseif(str_contains($type, 'berkas'))
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @elseif(str_contains($type, 'pengingat'))
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                @endif
                            </div>

                            <!-- Teks Notifikasi -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold {{ !$isRead ? 'text-navy' : 'text-slate-700' }}">{{ $title }}</h4>
                                    @if(!$isRead)
                                        <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $message }}</p>
                                <span class="text-[11px] text-slate-400 mt-1.5 inline-block font-medium">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->translatedFormat('d F Y, H:i') }} ({{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }})
                                </span>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($url && $url !== '#')
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="event.preventDefault(); this.form.submit(); window.location='{{ $url }}';" class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        Buka Tautan &rarr;
                                    </button>
                                </form>
                            @endif

                            @if(!$isRead)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="Tandai telah dibaca" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus notifikasi" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

