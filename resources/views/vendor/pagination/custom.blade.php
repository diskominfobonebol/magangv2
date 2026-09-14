@if ($paginator->hasPages() || $paginator->total() > 0)
<nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 w-full">
    <!-- Keterangan Jumlah Hasil (Kiri Bawah) -->
    <div>
        <p class="text-xs text-slate-500 font-medium">
            Showing
            @if ($paginator->firstItem())
                <span class="font-bold text-navy">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-bold text-navy">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-bold text-navy">{{ $paginator->count() }}</span>
            @endif
            of
            <span class="font-bold text-navy">{{ $paginator->total() }}</span>
            results
        </p>
    </div>

    <!-- Tombol Pagination (Kanan Bawah) -->
    @if ($paginator->hasPages())
    <div>
        <div class="inline-flex items-center gap-1.5">
            {{-- Tombol Previous (Panah Kiri) --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold bg-white/60 text-slate-300 border border-slate-200/60 cursor-not-allowed select-none shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold bg-white text-navy hover:text-primary hover:bg-blue-50 border border-blue-200/80 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Nomor-Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Separator "..." --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-slate-400 select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-full text-xs font-extrabold bg-navy text-white shadow-md shadow-navy/20 select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-full text-xs font-bold bg-white text-navy hover:text-primary hover:bg-blue-50 border border-blue-200/80 shadow-xs transition-all cursor-pointer">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Next (Panah Kanan) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold bg-white text-navy hover:text-primary hover:bg-blue-50 border border-blue-200/80 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold bg-white/60 text-slate-300 border border-slate-200/60 cursor-not-allowed select-none shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </div>
    @endif
</nav>
@endif
