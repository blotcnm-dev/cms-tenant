@if ($paginator->hasPages())
    <div class="pagination">
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

        {{-- First Page Button --}}
        @if ($hasPrevious)
            <a href="{{ $paginator->url(1) }}" rel="first" aria-label="First Page" class="move_btn for_first"></a>
        @else
            <a href="javascript:;" class="move_btn for_first disabled"></a>
        @endif

        {{-- Previous Block Button --}}
        @if ($hasPreviousBlock)
            <a href="{{ $paginator->url(max(1, $startPage - $perBlock)) }}" rel="prev" aria-label="Previous Block" class="move_btn before"></a>
        @else
            <a href="javascript:;" class="move_btn before disabled"></a>
        @endif

        {{-- 모바일 페이징 표시 --}}
        <div class="mob_paging mob_block">
            <span>{{ $currentPage }}</span> / {{ $lastPage }}
        </div>

        {{-- Page Links --}}
        @for ($page = $startPage; $page <= $endPage; $page++)
            @if ($page == $currentPage)
                <a href="javascript:;" class="num active">{{ $page }}</a>
            @else
                <a href="{{ $paginator->url($page) }}" class="num">{{ $page }}</a>
            @endif
        @endfor

        {{-- Next Block Button --}}
        @if ($hasNextBlock)
            <a href="{{ $paginator->url(min($lastPage, $endPage + 1)) }}" rel="next" aria-label="Next Block" class="move_btn after"></a>
        @else
            <a href="javascript:;" class="move_btn after disabled"></a>
        @endif

        {{-- Last Page Button --}}
        @if ($hasNext)
            <a href="{{ $paginator->url($lastPage) }}" rel="last" aria-label="Last Page" class="move_btn for_last"></a>
        @else
            <a href="javascript:;" class="move_btn for_last disabled"></a>
        @endif
    </div>
@endif
