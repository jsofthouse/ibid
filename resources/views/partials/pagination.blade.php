@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-line pt-4 mt-4 text-sm">
        <p class="text-ink/70">
            Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            <span class="hidden sm:inline">&middot; {{ $paginator->total() }} data</span>
        </p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded border border-line text-ink/40">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded border border-line hover:bg-card">Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded border border-line hover:bg-card">Berikutnya</a>
            @else
                <span class="px-3 py-1.5 rounded border border-line text-ink/40">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
