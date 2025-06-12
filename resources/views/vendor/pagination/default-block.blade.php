@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Previous Block Link --}}
        @php
            $currentPage = $paginator->currentPage();
            $lastPage = $paginator->lastPage();
            $perBlock = 5;
            $currentBlock = ceil($currentPage / $perBlock);
            $startPage = (($currentBlock - 1) * $perBlock) + 1;
            $endPage = min($startPage + $perBlock - 1, $lastPage);
            $hasPreviousBlock = $startPage > 1;
            $hasNextBlock = $endPage < $lastPage;
            $previousBlockPage = max(1, $startPage - $perBlock);
            $nextBlockPage = min($lastPage, $endPage + 1);
        @endphp

        {{-- Previous Block Button --}}
        @if ($hasPreviousBlock)
            <a href="{{ $paginator->url($previousBlockPage) }}" rel="prev" aria-label="Previous Block" class="move_btn before"></a>
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
            <a href="{{ $paginator->url($nextBlockPage) }}" class="move_btn after"></a>
        @else
            <a href="javascript:;" class="move_btn after disabled"></a>
        @endif
    </div>
@endif
