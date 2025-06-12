@extends('web.layout.master')

@section('required-page-title', '1:1 문의')
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
            <h2>1:1문의 목록</h2>
            <form action="{{ route('front_inquiry.index') }}" method="GET" id="searchForm">
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
                <span class="state">처리상태</span>
                <span class="date">작성일<button type="button" id="sortByDate">⬇⬆</button></span>
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
                            <a href="{{ route('front_inquiry.view', [$item->post_id]) }}">
                                <span class="number">{{ ($startNumber - $index) }}</span>
                                <span class="title">{{ ( $item->subject )  ?? '' }}</span>
                                <span class="count">{{ ( $item->reply_status  == 'READY')  ? '답변대기' : '답변완료' }}</span>
                                <span class="date">{{ format_date( $item->created_at ) }}</span>
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
            <div class="board_controller">
                @if(!session()->get('blot_mbid'))
                    <a href="/login">✍글쓰기</a>
                @else
                    <a href="{{route('front_inquiry.create')}}">✍글쓰기</a>
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

        });

    </script>
@stop
