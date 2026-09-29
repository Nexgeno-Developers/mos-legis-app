@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm">
        <p class="text-muted-foreground">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>
        <div class="flex flex-wrap gap-1">
            @if ($paginator->onFirstPage())
                <span class="border border-border px-3 py-1.5 text-muted-foreground opacity-50">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="border border-border bg-card px-3 py-1.5 hover:border-gold">Previous</a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-1.5 text-muted-foreground">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="border border-primary bg-primary px-3 py-1.5 text-primary-foreground">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="border border-border bg-card px-3 py-1.5 hover:border-gold">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="border border-border bg-card px-3 py-1.5 hover:border-gold">Next</a>
            @else
                <span class="border border-border px-3 py-1.5 text-muted-foreground opacity-50">Next</span>
            @endif
        </div>
    </nav>
@endif
