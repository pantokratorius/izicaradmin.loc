

@if ($paginator->hasPages())
    @php
        $start = max($paginator->currentPage() - 3, 1);
        $end = min($paginator->currentPage() + 3, $paginator->lastPage());
    @endphp

    <style>
        .pagination-container {
            margin-top: 20px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }

        .pagination-container a {
            padding: 6px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            background: #f1f3f5;
            color: #000;
            transition: background 0.2s;
        }

        .pagination-container a:hover {
            background: #e0e0e0;
        }

        .pagination-container a.active {
            background: #007bff;
            color: #fff;
            pointer-events: none;
        }

        .pagination-container a.disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .pagination-container span {
            padding: 6px 12px;
            font-size: 14px;
        }
    </style>

    <div class="pagination-container">
        {{-- Первая --}}
        @if($paginator->currentPage() > 4)
            <a href="{{ $paginator->url(1) }}">«</a>
        @endif

        {{-- Назад --}}
        <a href="{{ $paginator->previousPageUrl() }}" class="{{ $paginator->onFirstPage() ? 'disabled' : '' }}">&#8249;	</a>

        {{-- Многоточие перед --}}
        @if($start > 1)
            <a href="{{ $paginator->url(1) }}">1</a>
            @if($start > 2)
                <span>...</span>
            @endif
        @endif

        {{-- Основные страницы --}}
        @for ($i = $start; $i <= $end; $i++)
            <a href="{{ $paginator->url($i) }}" class="{{ $paginator->currentPage() == $i ? 'active' : '' }}">
                {{ $i }}
            </a>
        @endfor

        {{-- Многоточие и последняя --}}
        @if($end < $paginator->lastPage())
            @if($end < $paginator->lastPage() - 1)
                <span>...</span>
            @endif
            <a href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
        @endif

        {{-- Вперёд --}}
        <a href="{{ $paginator->nextPageUrl() }}" class="{{ $paginator->hasMorePages() ? '' : 'disabled' }}">&#8250;</a>

        {{-- Последняя --}}
        @if($paginator->currentPage() < $paginator->lastPage() - 3)
            <a href="{{ $paginator->url($paginator->lastPage()) }}">»</a>
        @endif
    </div>
@endif