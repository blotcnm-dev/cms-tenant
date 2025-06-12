@extends('admin.layout.master')

@section('required-page-title', $board_config->board_name)

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/postList.css">
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">{{$board_config->board_name}}</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 조건 검색 S -->
                <form action="{{ route('boards.board.list', [
                                                    $board_config->board_id
                                                ]) }}" method="GET" id="searchForm">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                    <input type="hidden" id="categoryInput" name="category" value="" >
                    <div class="search_box">
                        <div class="input_box">
                            <div class="input_item half">
                                <label class="input_title" for="post_title">제목</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="post_title" name="subject" placeholder="검색어를 입력하세요" value="{{ request('subject') ?? '' }}">
                                </div>
                            </div>
                            @if(count($category_sub) > 0)
                            <div class="input_item half">
                                <label class="input_title">분류</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" placeholder="전체" name="category_tmp" data-value="{{ request('category') ?? '' }}" value="{{ request('category_tmp') ?? '' }}" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="all">전체</li>
                                            @foreach($category_sub as $config)
                                                <li role="listitem" class="category-item" data-value="{{ $config->depth_code }}">{{ $config->kname }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="input_item half date">
                                <label class="input_title">등록 일자</label>
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
                            <div class="input_item half">
                                <label class="input_title">비밀글</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" name="secret" placeholder="전체" data-value="" value="{{ request('secret') }}" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="all">전체</li>
                                            <li role="listitem" data-value="y">공개</li>
                                            <li role="listitem" data-value="n">비밀</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
{{--                            <div class="input_item half">--}}
{{--                                <label class="input_title">스팸 여부</label>--}}
{{--                                <div class="inner_box">--}}
{{--                                    <div class="custom_select_1 js_custom_select">--}}
{{--                                        <input type="text" class="common_input select_value" placeholder="전체" readonly>--}}
{{--                                        <ul role="list">--}}
{{--                                            <li role="listitem" data-value="all">전체</li>--}}
{{--                                            <li role="listitem" data-value="y">정상</li>--}}
{{--                                            <li role="listitem" data-value="n">의심</li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            </div>
                        <button type="submit" class="search_btn">
                            <span>조회</span>
                        </button>
                    </div>
                </form>
                <!-- 조건 검색 E -->

                <!-- 결과 조회 S -->
                <div class="search_result @if($boards->total() < 1 ) no_result @endif">
                    <div class="result_top">
                        <div class="flex">
                            <p class="tit">검색결과</p>
                            <p class="count pc_block">총 <span>{{ $boards->total() }}</span>건의 결과가 조회 되었습니다.</p>
                        </div>
                        @if($boards->total() > 0 )
                            <a href="{{ ($board_config->is_read === 'Y') ? route('boards.board.download.Excel').'?board_id='.$board_config->board_id.'&'.http_build_query(request()->query()) : 'javascript:void(0);' }}" class="fill_btn download">
                                <span>엑셀 다운로드</span>
                            </a>
                        @endif
                    </div>
                    <div class="result_list">
                        @if($boards->total() < 1 )
                        <div class="nodata">
                            <div>
                                <p>조회된 데이터가 없습니다.</p>
                            </div>
                        </div>
                        @else
                        <form id="listForm" action="{{ route('boards.board.destroy',[$board_config->board_id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                        <div class="white_wrap">
                            <div class="common_table">
                                <div class="thead">
                                    <div class="check">
                                        <label class="chk_input">
                                            <input type="checkbox" id="all_chk" name="post_list">
                                            <span></span>
                                        </label>
                                        @if($boards->total() > 0 )
                                        <button type="button" id="deleteBtn" class="border_btn white" disabled>삭제</button>
                                        @endif
                                    </div>
                                    <div class="num">번호</div>
                                    @if($board_config->is_category !== '0')
                                        <div class="type">분류</div>
                                    @endif
                                    <div class="title">제목</div>
                                    <div class="date">
                                        <button type="button" class="sorting_btn">등록 일자</button>
                                    </div>
                                    <div class="secret">비밀글</div>
                                </div>
                                <ul role="list" class="tbody">
                                @php
                                    $startNumber = $boards->total() - ($boards->perPage() * ($boards->currentPage() - 1));
                                @endphp

                                @foreach($boards as $index => $item)
                                    <li role="listitem" {{ ( $item->is_secret === 1 )  ? 'class="lock"':'' }}>
                                        <a href="{{ ($board_config->is_read === 'Y') ? route('boards.board.show', [$board_config->board_id,$item->post_id]) : '' }}">
                                            <div class="check">
                                                <label class="chk_input">
                                                    <input type="checkbox" class="item_chk" name="post_list[]" value="{{$item->post_id}}">
                                                    <span></span>
                                                </label>
                                            </div>
                                            <div class="num">
                                                <span>번호</span>
                                                <p>{{ $startNumber - $index }}</p>
                                            </div>
                                            @if($board_config->is_category !== '0')
                                            <div class="type">
                                                <span>분류</span>
                                                <p>{{ ( $item->kname )  ?? '' }}</p>
                                            </div>
                                            @endif
                                            <div class="title left">
                                                <span>제목</span>
                                                <p>{{ ( $item->subject )  ?? '' }}</p>
                                            </div>
                                            <div class="date">
                                                <span>등록 일자</span>
                                                <p>{{ format_date( $item->created_at ) }}</p>
                                            </div>
                                            <div class="secret">
                                                <span>비밀글</span>
                                                <p>{{ ( $item->is_secret === 1 )  ? '비공개' : '공개'}}</p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        </div>
                        </form>
                            <!-- 페이지네이션 S -->
                            {{ $boards->links('vendor.pagination.default') }}
                            <!-- 페이지네이션 E -->
                        @endif
                        <div class="bottom_btn">
                            @if($board_config->is_write === 'Y')
                            <a href="{{ route('boards.board.write', [$board_config->board_id])}}" class="border_btn register">
                                <span>등록</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @if($boards->total() > 0 )
                        <a href="{{ ($board_config->is_read === 'Y') ? route('boards.board.download.Excel').'?board_id='.$board_config->board_id.'&'.http_build_query(request()->query()) : 'javascript:void(0);' }}" class="border_btn blue download">
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
                // index 0: 노출기간
                const field = index === 0 ? 'created_at' : 'created_at';

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

            // 모든 .category-item에 클릭 이벤트 바인딩
            document.querySelectorAll('.category-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    // data-value 값을 hidden input에 설정
                    document.getElementById('categoryInput').value = this.dataset.value;

                    // (선택 사항) UI적으로 선택 표시
                    document.querySelectorAll('.category-item.selected').forEach(function(active){
                        active.classList.remove('selected');
                    });
                    this.classList.add('selected');
                });
            });

        });


        document.addEventListener('DOMContentLoaded', () => {
            @if($boards->total() > 0 )
            const allChk    = document.getElementById('all_chk');
            const deleteBtn = document.getElementById('deleteBtn');
            const form      = document.getElementById('listForm');

            const getItems = () => form.querySelectorAll('input[name="post_list[]"]');

            function toggleBtn() {
                deleteBtn.disabled = ![...getItems()].some(chk => chk.checked);
            }

            allChk.addEventListener('change', () => {
                getItems().forEach(chk => chk.checked = allChk.checked);
                toggleBtn();
            });

            form.addEventListener('change', e => {
                if (e.target.matches('input[name="post_list[]"]')) {
                    allChk.checked = [...getItems()].every(chk => chk.checked);
                    toggleBtn();
                }
            });

            // 클릭 시 submit 호출
            deleteBtn.addEventListener('click', () => {
                @if($board_config->is_del === 'Y')
                    if (!deleteBtn.disabled) {
                        if(confirm('정말 삭제 하시겠습니까?')) {
                            form.submit();
                        }
                    }
                @else
                    alert('권한이 없습니다.');
                @endif
            });
            @endif
        });
    </script>
    <!-- 개발용 스크립트 E -->
@stop
