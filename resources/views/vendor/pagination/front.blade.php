@if ($paginator->hasPages())
    <div class="pagination_container">
        @php
            $currentPage = $paginator->currentPage();
            $lastPage = $paginator->lastPage();
            $perBlock = 5;
            $currentBlock = ceil($currentPage / $perBlock);
            $startPage = (($currentBlock - 1) * $perBlock) + 1;
            $endPage = min($startPage + $perBlock - 1, $lastPage);
            $hasPrevious = $currentPage > 1;
            $hasNext = $currentPage < $lastPage;
            $hasPreviousBlock = $startPage > 1;
            $hasNextBlock = $endPage < $lastPage;
        @endphp

        {{-- Previous Block Button --}}
        @if ($hasPreviousBlock)
            <button type="button" onclick="location.href='{{ $paginator->url(max(1, $startPage - $perBlock)) }}'">이전</button>
            <ol role="list">
                @else
                    <button type="button" disabled>이전</button>
                    <ol role="list">
                        @endif

                        {{-- Page Links --}}
                        @for ($page = $startPage; $page <= $endPage; $page++)
                            @if ($page == $currentPage)
                                <li role="listitem"><button type="button">{{ $page }}</button></li>
                            @else
                                <li role="listitem"><button type="button" onclick="location.href='{{ $paginator->url($page) }}'">{{ $page }}</button></li>
                            @endif
                        @endfor

                        {{-- Next Block Button --}}
                        @if ($hasNextBlock)
                    </ol>
                    <button type="button" onclick="location.href='{{ $paginator->url(min($lastPage, $endPage + 1)) }}'">다음</button>
                    @else
            </ol>
            <button type="button" disabled>다음</button>
        @endif
    </div>
@endif
