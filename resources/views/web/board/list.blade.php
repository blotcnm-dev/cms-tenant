@extends('web.layout.master')

@section('required-page-title', $board_config->board_name.'')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/board/list.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-banner-blade')
{{--    <div>페이지별 배너 </div>--}}
@stop


@section('required-page-main-content')
<main>
    <section class="container_list">
        <div class="container w-820">
            <h2>{{ $board_config->board_name }}</h2>
            <form action="{{ route('boards.list', [$board_config->board_id]) }}" method="GET" id="searchForm">
                <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                <div class="search_container">
                    <select name="fild_id">
                        <option value="" {{ request('fild_id') == '' ? 'selected' : '' }}>전체</option>
                        <option value="content" {{ request('fild_id') == 'content' ? 'selected' : '' }}>내용</option>
                        <option value="subject" {{ request('fild_id') == 'subject' ? 'selected' : '' }}>제목</option>
                    </select>
                    <input type="text" name="fild_val" value="{{ request('fild_val') }}">
                    <button type="submit" id="searchBtn">🔍</button>

            </div>
            <div class="list_type_container">
                <span>총 <b>{{ $boards->total() }}</b> 건의 글이 있습니다.</span>
                <select name="limit_cnt" id="limitCnt">
                    <option value="10" {{ request('limit_cnt') == '10' ? 'selected' : '' }}>10개씩</option>
                    <option value="20" {{ request('limit_cnt') == '20' ? 'selected' : '' }}>20개씩</option>
                    <option value="30" {{ request('limit_cnt') == '30' ? 'selected' : '' }}>30개씩</option>
                </select>
            </div>
            </form>
            <div class="list_header">
                <span class="number">번호</span>
                <span class="title">제목</span>
                <span class="editor">작성자</span>
                <span class="date">작성일<button type="button" id="sortByDate">⬇⬆</button></span>
                <span class="count">조회<button type="button" id="sortByHits">⬇⬆</button></span>
                @if($board_config->is_like === 1 )
                <span class="like">좋아요</span>
                @endif
            </div>
            <ul role="list" class="board_list">
                @if($boards->total() < 1 )
                    <li role="listitem">
                        <a>
                            <span class="title">조회된 데이터가 없습니다.</span>
                        </a>
                    </li>
                @else
                    @php
                        $startNumber = $boards->total() - ($boards->perPage() * ($boards->currentPage() - 1));
                    @endphp

                    @foreach($boards as $index => $item)
                        <li role="listitem">
                        @if(!session()->get('blot_mbid') && $board_config->is_read === 'N')
                            <a href="/login">
                        @else
                            <a href="{{ ($board_config->is_read === 'Y') ? route('boards.show', [$board_config->board_id,$item->post_id]) : 'javascript:void(0);' }}" @if($board_config->is_read === 'N') onclick="alert('권한이 없습니다.'); return false;" @endif>
                        @endif
                                <span class="number">{!! ( $item->is_best === 1 )  ? '<span class="marker">📌</span>': ($startNumber - $index) !!}</span>
                                <span class="title">{!! ( $item->is_secret === 1 )  ? '[비밀글]':'' !!}{{ ( $item->subject )  ?? '' }} </span>
                                <span class="editor">{{ safe_decrypt( $item->user_name )  ?? '' }}</span>
                                <span class="date">{{ format_date( $item->created_at ) }}</span>
                                <span class="count">{{ ( $item->hits )  ?? 0 }}</span>
                                @if($board_config->is_like === 1 )
                                <span class="like">{{ ( $item->likes )  ?? 0 }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
            <div class="board_controller">
                @if(!session()->get('blot_mbid') && $board_config->is_write === 'N')
                    <a href="/login">✍글쓰기</a>
                @else
                    <a href="{{ ($board_config->is_write === 'Y') ? route('boards.create', [$board_config->board_id]) : 'javascript:void(0);' }}" @if($board_config->is_write === 'N') onclick="alert('권한이 없습니다.'); return false;" @endif>✍글쓰기</a>
                @endif
            </div>
            <!-- 페이지네이션 S -->
            {{ $boards->links('vendor.pagination.front') }}
            <!-- 페이지네이션 E -->
        </div>
    </section>
</main>
@stop

@section('required-page-add-content')
    <!-- 개발용 스크립트 S -->
    <script>
        // jQuery 대신 순수 JavaScript 사용
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('searchForm');
            const sortOrderInput = document.querySelector('input[name="sort_order"]');

            // 정렬 버튼 상태 업데이트 함수
            function updateSortButtons() {
                const currentSort = sortOrderInput?.value || '';

                const sortByDateBtn = document.getElementById('sortByDate');
                const sortByHitsBtn = document.getElementById('sortByHits');

                // 작성일 버튼
                if (sortByDateBtn) {
                    if (currentSort === 'created_at__desc') {
                        sortByDateBtn.textContent = '⬇';
                    } else if (currentSort === 'created_at__asc') {
                        sortByDateBtn.textContent = '⬆';
                    } else {
                        sortByDateBtn.textContent = '⬇⬆';
                    }
                }

                // 조회수 버튼
                if (sortByHitsBtn) {
                    if (currentSort === 'hits__desc') {
                        sortByHitsBtn.textContent = '⬇';
                    } else if (currentSort === 'hits__asc') {
                        sortByHitsBtn.textContent = '⬆';
                    } else {
                        sortByHitsBtn.textContent = '⬇⬆';
                    }
                }
            }

            // 초기 버튼 상태 설정
            updateSortButtons();

            // 페이지 수 변경
            const limitCnt = document.getElementById('limitCnt');
            if (limitCnt) {
                limitCnt.addEventListener('change', function() {
                    form.submit();
                });
            }

            // 작성일 정렬
            const sortByDate = document.getElementById('sortByDate');
            if (sortByDate) {
                sortByDate.addEventListener('click', function() {
                    const currentSort = sortOrderInput.value;
                    sortOrderInput.value = currentSort === 'created_at__desc' ? 'created_at__asc' : 'created_at__desc';
                    form.submit();
                });
            }

            // 조회수 정렬
            const sortByHits = document.getElementById('sortByHits');
            if (sortByHits) {
                sortByHits.addEventListener('click', function() {
                    const currentSort = sortOrderInput.value;
                    sortOrderInput.value = currentSort === 'hits__desc' ? 'hits__asc' : 'hits__desc';
                    form.submit();
                });
            }
        });

    </script>
@stop
