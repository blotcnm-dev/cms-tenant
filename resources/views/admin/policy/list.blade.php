@extends('admin.layout.master')

@section('required-page-title', '약관 목록')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/termsList.css">
@stop

@section('required-page-header-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">약관 목록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 조건 검색 S -->
                <form action="{{ route('policy.index') }}" method="GET" id="searchForm">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                    <div class="search_box">
                        <div class="input_box">
                            <div class="input_item half">
                                <label class="input_title" for="terms_title">제목</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="terms_title" placeholder="검색어를 입력하세요" name="title" value="{{ request('title') }}">
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="terms_writer">작성자</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="terms_writer" placeholder="검색어를 입력하세요" name="author" value="{{ request('author') }}">
                                </div>
                            </div>
                            <div class="input_item half date">
                                <label class="input_title">등록 기간</label>
                                <div class="inner_box">
                                    <div class="calendar_input">
                                        <input type="date" class="common_input" name="start_date" value="{{ request('start_date') }}">
                                    </div>
                                    <span>~</span>
                                    <div class="calendar_input">
                                        <input type="date" class="common_input" name="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="input_item half date">
                                <label class="input_title">수정 기간</label>
                                <div class="inner_box">
                                    <div class="calendar_input">
                                        <input type="date" class="common_input" name="start_update_date" value="{{ request('start_update_date') }}">
                                    </div>
                                    <span>~</span>
                                    <div class="calendar_input">
                                        <input type="date" class="common_input" name="end_update_date" value="{{ request('end_update_date') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">노출 여부</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value"  placeholder="{{ request('status') ?? '전체' }}" readonly name="status">
                                        <ul role="list">
                                            <li role="listitem">전체</li>
                                            <li role="listitem">노출</li>
                                            <li role="listitem">비노출</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="search_btn">
                            <span>조회</span>
                        </button>
                    </div>
                </form>
                <!-- 조건 검색 E -->

                <!-- 결과 조회 S -->
                <div class="search_result @if($policy_contents->total() < 1 ) no_result @endif">
                    <div class="result_top">
                        <div class="flex">
                            <p class="tit">검색결과</p>
                            <p class="count pc_block">총 <span>{{ $policy_contents->total() }}</span>건의 결과가 조회 되었습니다.</p>
                        </div>
                        @if($policy_contents->total() > 0 )
                        <a href="{{ route('policy.download.excel') }}?{{ http_build_query(request()->query()) }}" class="fill_btn download">
                            <span>엑셀 다운로드</span>
                        </a>
                        @endif
{{--                        <div class="custom_select_2 js_custom_select mob_block">--}}
{{--                            <div class="select_value" data-value="{{ request('sort_order', 'created_at__desc') }}">--}}
{{--                                @if(request('sort_order') == 'created_at__asc')--}}
{{--                                    오래된 순--}}
{{--                                @else--}}
{{--                                    최신순--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                            </div>--}}
{{--                            <ul role="list">--}}
{{--                                <li role="listitem" data-value="created_at__desc">최신순</li>--}}
{{--                                <li role="listitem" data-value="created_at__asc">오래된 순</li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
                    </div>
                    <div class="result_list">
                        @if($policy_contents->total() < 1 )
                            <div class="nodata">
                                <div>
                                    <p>조회된 데이터가 없습니다.</p>
                                </div>
                            </div>
                        @else
                            <div class="white_wrap">
                                <div class="common_table">
                                    <div class="thead">
                                        <div class="num">번호</div>
                                        <div class="title">제목</div>
                                        <div class="show">노출 여부</div>
                                        <div class="time">
                                            <button type="button" class="sorting_btn">등록 일자</button>
                                        </div>
                                        <div class="time">
                                            <button type="button" class="sorting_btn">수정 일자</button>
                                        </div>
                                        <div class="writer">작성자</div>
                                        <div class="management">관리</div>
                                    </div>
                                    <ul role="list" class="tbody">

                                        @php
                                            $startNumber = $policy_contents->total() - ($policy_contents->perPage() * ($policy_contents->currentPage() - 1));
                                        @endphp

                                        @foreach($policy_contents as $index => $item)
                                            <li role="listitem" class="{{ $item->is_state == 'Y' ? '' : 'hidden' }}">
                                                <div class="num">
                                                    <span>번호</span>
                                                    <p>{{ $startNumber - $index }}</p>
                                                </div>
                                                <div class="title left">
                                                    <span>제목</span>
                                                    <p>
                                                        <a href="{{ route('policy.show', $item->policy_contents_id) }}">
                                                            {{ ( $item->title )  ?? '' }}   <span>(Version {{$item->version}})</span>
                                                        </a>
                                                    </p>
                                                </div>
                                                <div class="show">
                                                    <span>노출 여부</span>
                                                    <p>{{ ( $item->is_state  == 'Y')  ? '노출' : '비노출' }}</p>
                                                </div>
                                                <div class="time register">
                                                    <span>등록 일자</span>
                                                    <p>{{ format_date( $item->created_at )   }}</p>
                                                </div>
                                                <div class="time">
                                                    <span>수정 일자</span>
                                                    <p>{{ format_date ($item->updated_at) }} </p>
                                                </div>
                                                <div class="writer">
                                                    <span>작성자</span>
                                                    <p>{{ ($item->user_name) ? decrypt($item->user_name) :'익명' }}</p>
                                                </div>
                                                <div class="management">

                                                    <form action="{{ route('policy.destroy', $item->policy_contents_id) }}" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="del_btn">삭제</button>
                                                    </form>
                                                </div>



                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <!-- 페이지네이션 S -->
                                {{ $policy_contents->links('vendor.pagination.default') }}
                                <!-- 페이지네이션 E -->
                                @endif
                            </div>
                            <div class="bottom_btn">
                                <a href="{{ route('policy.create') }}" class="border_btn register">
                                    <span>등록</span>
                                </a>
                            </div>
                    </div>
                    @if($policy_contents->total() > 0 )
                    <a href="{{ route('policy.download.excel') }}?{{ http_build_query(request()->query()) }}" class="border_btn blue download">
                        <span>엑셀 다운로드</span>
                    </a>
                    @endif
                </div>
                <!-- 결과 조회 E -->
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
@stop

@section('required-page-add-content')

    <script type="module">
        import { dateInputChange } from "/src/js/components/dateInput.js";
        document.addEventListener("DOMContentLoaded", () => {
            const searchResult = document.querySelector(".search_result");
            const bottomBtn = document.querySelector(".bottom_btn");

            if (!searchResult || !bottomBtn) return;

            const checkPosition =()=> {
                const rect = searchResult.getBoundingClientRect();
                const triggerPoint = window.innerHeight * 0.7;

                if (rect.top <= triggerPoint && rect.bottom >= triggerPoint) {
                    bottomBtn.classList.add("fixed");
                } else {
                    bottomBtn.classList.remove("fixed");
                }
            }

            window.addEventListener("scroll", checkPosition);
            checkPosition();

            document.querySelectorAll('input[type="date"]').forEach((input) => dateInputChange({ target: input }));
            document.addEventListener('input', (e) => {
                if (e.target && e.target.type === 'date') {
                    dateInputChange(e);
                }
            });
        });
    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sortButtons = document.querySelectorAll('.sorting_btn');

            // sort_order의 현재 값 가져오기
            const sortOrderInput = document.querySelector('input[name="sort_order"]');
            const currentSortOrder = sortOrderInput.value;
            let [currentField, currentDirection] = currentSortOrder.split('__');

            // 기존 정렬 상태에 따라 버튼 스타일 적용
            sortButtons.forEach((button, index) => {
                // index 0: 등록 일자, index 1: 수정 일자
                const field = index === 0 ? 'created_at' : 'updated_at';

                // 현재 정렬 중인 필드에 화살표 표시
                if (currentField === field) {
                    button.classList.add(currentDirection === 'asc' ? 'asc' : 'desc');
                }

                button.addEventListener('click', function() {
                    let newDirection;

                    // 같은 필드를 클릭한 경우 정렬 방향 전환
                    if (currentField === field) {
                        newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        // 새로운 필드를 클릭한 경우 기본 내림차순(최신순)으로 시작
                        newDirection = 'desc';
                    }

                    setSortOrder(field, newDirection);
                });
            });

            // 모바일용 셀렉트 박스 정렬 처리
            const mobileSelect = document.querySelector('.custom_select_2.js_custom_select');
            if (mobileSelect) {
                const selectItems = mobileSelect.querySelectorAll('li');

                selectItems.forEach((item, index) => {
                    item.addEventListener('click', function() {
                        // index 0: 최신순, index 1: 오래된 순
                        const newDirection = index === 0 ? 'desc' : 'asc';
                        setSortOrder('created_at', newDirection); // 모바일에서는 등록일자 기준으로만 정렬
                    });
                });
            }
        });

    </script>
    <!-- 개발용 스크립트 E -->
@stop
