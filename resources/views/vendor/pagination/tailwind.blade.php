@if ($paginator->hasPages())
<nav class="ppw-pagination" role="navigation" aria-label="Pagination">
    <div class="ppw-pagination-info">
        Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
    </div>
    <ul class="ppw-pagination-links">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="ppw-page-item disabled">
                <span class="ppw-page-link"><i class="fas fa-chevron-left"></i></span>
            </li>
        @else
            <li class="ppw-page-item">
                <a class="ppw-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="ppw-page-item disabled">
                    <span class="ppw-page-link ppw-dots">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="ppw-page-item active">
                            <span class="ppw-page-link" aria-current="page">{{ $page }}</span>
                        </li>
                    @else
                        <li class="ppw-page-item">
                            <a class="ppw-page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="ppw-page-item">
                <a class="ppw-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="ppw-page-item disabled">
                <span class="ppw-page-link"><i class="fas fa-chevron-right"></i></span>
            </li>
        @endif

    </ul>
</nav>

<style>
.ppw-pagination {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 0 0.25rem;
}
.ppw-pagination-info {
    font-size: 0.82rem;
    color: var(--gray-500, #6b7280);
    text-align: center;
}
.ppw-pagination-links {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    list-style: none;
    margin: 0;
    padding: 0;
}
.ppw-page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 0.5rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid var(--gray-200, #e5e7eb);
    background: var(--white, #fff);
    color: var(--gray-700, #374151);
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    line-height: 1;
    cursor: pointer;
}
.ppw-page-link i {
    font-size: 0.7rem;
}
a.ppw-page-link:hover {
    background: var(--primary-light, #dbeafe);
    border-color: var(--primary, #2563eb);
    color: var(--primary, #2563eb);
}
.ppw-page-item.active .ppw-page-link {
    background: var(--primary, #2563eb);
    border-color: var(--primary, #2563eb);
    color: #fff;
    cursor: default;
}
.ppw-page-item.disabled .ppw-page-link {
    background: var(--gray-50, #f9fafb);
    border-color: var(--gray-200, #e5e7eb);
    color: var(--gray-300, #d1d5db);
    cursor: not-allowed;
}
.ppw-dots {
    border: none;
    background: transparent;
    color: var(--gray-400, #9ca3af);
    letter-spacing: 0.1em;
}
</style>
@endif
