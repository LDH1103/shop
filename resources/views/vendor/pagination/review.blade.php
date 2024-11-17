@if ($paginator->hasPages())
    <nav class="pagination-nav">
        <ul class="pagination">
            {{-- 이전 페이지 링크 --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true">
                    <span>&laquo;</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
                </li>
            @endif

            {{-- 페이지 번호 --}}
            @foreach ($elements as $element)
                {{-- "..." 표시 --}}
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                {{-- 페이지 번호 링크 --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- 다음 페이지 링크 --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true">
                    <span>&raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif