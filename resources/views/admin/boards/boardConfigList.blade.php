@extends('admin.layout.master')

@section('required-page-title', '게시판 목록')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/boardList.css">
@stop

@section('required-page-main-content')

    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">게시판 목록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 조건 검색 S -->
                <form action="{{ route('configBoards.list') }}" method="GET" id="searchForm">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                    <div class="search_box">
                        <div class="input_box">
                            <div class="input_item half">
                                <label class="input_title" for="board_name">제목</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="board_name" placeholder="검색어를 입력하세요" name="board_name" value="{{ request('board_name') }}">
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="board_type">유형</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select select_type">
                                        <input type="text" class="common_input select_value" placeholder="선택" data-value="" value="{{ request('board_type') }}" name="board_type" readonly>
                                        <ul role="list">
                                            <li role="listitem" class="board" data-value="">전체</li>
                                            <li role="listitem" class="board" data-value="COMMON">게시판</li>
                                            <li role="listitem" class="gallery" data-value="GALLERY">갤러리</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
                            <div class="input_item half input_gap">
                                <label class="input_title">노출 여부</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" placeholder="선택" data-value="" value="{{ request('status') }}" name="status" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="">전체</li>
                                            <li role="listitem" data-value="1">사용함</li>
                                            <li role="listitem" data-value="0">사용안함</li>
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
                <div class="search_result @if($boardconfig_contents->total() < 1 ) no_result @endif">
                    <div class="result_top">
                        <div class="flex">
                            <p class="tit">검색결과</p>
                            <p class="count pc_block">총 <span>{{ $boardconfig_contents->total() }}</span>건의 결과가 조회 되었습니다.</p>
                        </div>
                        {{--                        <a href="javascript:void(0);" class="fill_btn download">--}}
                        {{--                            <span>엑셀 다운로드</span>--}}
                        {{--                        </a>--}}
                    </div>
                    <div class="result_list">
                        @if($boardconfig_contents->total() < 1 )
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
                                        <div class="type">유형</div>
                                        <div class="show">노출여부</div>
                                        <div class="date">
                                            <button type="button" class="sorting_btn">등록 일자</button>
                                        </div>
                                        <div class="writer">작성자</div>
                                        <div class="management">관리</div>
                                    </div>
                                    <ul role="list" class="tbody">
                                        @php
                                            $startNumber = $boardconfig_contents->total() - ($boardconfig_contents->perPage() * ($boardconfig_contents->currentPage() - 1));
                                        @endphp

                                        @foreach($boardconfig_contents as $index => $item)
                                            <li role="listitem" class="{{ ( $item->is_active  == 1)  ? '' : 'hidden' }}">
                                                <div class="num">
                                                    <span>번호</span>
                                                    <p>{{$item->board_config_id}}</p>
                                                </div>
                                                <div class="title left">
                                                    <span>제목</span>
                                                    <p class="arrow">
                                                        <a href="{{ route('configBoards.edit', $item->board_config_id) }}">
                                                            {{$item->board_name}}
                                                        </a>
                                                    </p>
                                                </div>
                                                <div class="type">
                                                    <span>유형</span>
                                                    <p>{{ ( $item->board_type  == 'COMMON')  ? '게시판' : '갤러리' }}</p>
                                                </div>
                                                <div class="show">
                                                    <span>노출여부</span>
                                                    <p>{{ ( $item->is_active  == 1)  ? '노출' : '비노출' }}</p>
                                                </div>
                                                <div class="date">
                                                    <span>등록 일자</span>
                                                    <p>{{ format_date( $item->created_at )   }}</p>
                                                </div>
                                                <div class="writer">
                                                    <span>작성자</span>
                                                    <p>{{ ($item->user_name) ? decrypt($item->user_name) :'익명' }}</p>
                                                </div>
                                                <div class="management">
                                                    <span>관리</span>
                                                    <form action="{{ route('configBoards.destroy', $item->board_config_id) }}" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <p>
                                                            <a href="{{ route('boards.board.list', [$item->board_id]) }}">
                                                                목록
                                                            </a>
                                                            <button type="button" class="copy_btn board-copy-btn" data-board-config-id="{{$item->board_config_id}}" data-board-name="{{$item->board_name}}">복사</button>
                                                            <button type="submit" class="del_btn">삭제</button>
                                                        </p>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <!-- 페이지네이션 S -->
                                {{ $boardconfig_contents->links('vendor.pagination.default') }}
                                <!-- 페이지네이션 E -->
                                @endif
                            </div>
                            <div class="bottom_btn">
                                <a href="{{ route('configBoards.create') }}" class="border_btn register">
                                    <span>등록</span>
                                </a>
                            </div>
                    </div>
                </div>
                <!-- 결과 조회 E -->
            </div>
            <!-- 컨텐츠 E -->
        </div>

        <!-- 게시판 복사 레이어 (숨김 상태) S -->
        <div id="boardCopyLayer" style="display: none;">
            <form id="boardCopyForm" action="{{ route('configBoards.boardcopyadd') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="board_config_id" value="">
                <input type="hidden" name="board_id" value="{{time()}}">
                <div class="input_box gray_box">
                    <div class="input_item">
                        <label class="input_title">게시판 이름</label>
                        <div class="inner_box">
                            <input type="text" name="board_name" class="common_input" value="">
                        </div>
                        <div id="board_name_error" class="error_msg" style="display: none;"></div>
                    </div>
                    <div class="input_item">
                        <label class="input_title">복사 형태</label>
                        <div class="inner_box flex gap_input">
                            <label class="radio_input">
                                <input type="radio" name="copy_type" id="type_1" value="structure" checked>
                                <span>구조만</span>
                            </label>
                            <label class="radio_input">
                                <input type="radio" name="copy_type" id="type_2" value="structure_data">
                                <span>구조+데이터</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="layer_btn_box">
                    <button type="button" class="border_btn cancel js_remove_btn">취소</button>
                    <button type="submit" id="boardCopySubmitBtn" class="border_btn save"><span>확인</span></button>
                </div>
            </form>
        </div>
        <!-- 게시판 복사 레이어 (숨김 상태) E -->
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

            // 게시판 복사 버튼 클릭 이벤트 처리
            document.addEventListener('click', (e) => {
                const copyBtn = e.target.closest('.board-copy-btn');
                if (copyBtn) {
                    const boardConfigId = copyBtn.dataset.boardConfigId;
                    const boardName = copyBtn.dataset.boardName;

                    // 레이어 열기 (내부 HTML 사용)
                    let layerContent = document.getElementById('boardCopyLayer').innerHTML;

                    // 동적으로 값 설정
                    layerContent = layerContent.replace('value=""', `value="${boardConfigId}"`);
                    layerContent = layerContent.replace('value=""', `value="[복사본] ${boardName}"`);
                    layerContent = layerContent.replace('style="display: none;"', 'style="display: none;"');

                    document.documentElement.classList.add('fixed');
                    document.getElementById("layer")?.remove();
                    document.getElementById("overlay")?.remove();

                    const overlay = document.createElement('div');
                    overlay.classList.add('overlay');
                    overlay.id = 'overlay';

                    const layer = document.createElement('div');
                    layer.classList.add('layer_wrap');
                    layer.id = 'layer';
                    layer.setAttribute('data-name', 'boardCopy');

                    layer.innerHTML = `
                        <div class="layer_top">
                            <h2 class="layer_title">게시판 복사</h2>
                            <button type="button" class="close_btn js_remove_btn">닫기</button>
                        </div>
                        <div class="layer_content">${layerContent}</div>
                    `;

                    document.body.appendChild(overlay);
                    document.body.appendChild(layer);

                    // 레이어 닫기 이벤트 처리
                    const closeLayerHandler = () => {
                        document.documentElement.classList.remove('fixed');
                        document.getElementById("layer")?.remove();
                        document.getElementById("overlay")?.remove();
                        document.removeEventListener('click', outsideClickHandler);
                    };

                    layer.addEventListener('click', (event) => {
                        if (event.target.classList.contains('js_remove_btn')) {
                            closeLayerHandler();
                        }
                    });

                    const outsideClickHandler = (event) => {
                        if (!layer.contains(event.target) && !event.target.closest('.del_btn')) {
                            closeLayerHandler();
                        }
                    };

                    document.addEventListener('click', outsideClickHandler);

                    // 폼 제출 이벤트 처리
                    const boardCopyForm = layer.querySelector('#boardCopyForm');
                    if (boardCopyForm) {
                        boardCopyForm.addEventListener('submit', function(e) {
                            e.preventDefault();

                            const submitBtn = layer.querySelector('#boardCopySubmitBtn');
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;

                            const formData = new FormData(this);
                            const url = '{{ route('configBoards.boardcopyadd') }}';

                            fetch(url, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert(data.message);
                                        closeLayerHandler();
                                        window.location.href = data.redirect || '{{ route('configBoards.list') }}';
                                    } else {
                                        if (data.errors.board_name) {
                                            const errorElement = layer.querySelector('#board_name_error');
                                            errorElement.textContent = data.errors.board_name[0];
                                            errorElement.style.display = 'block';
                                        }

                                        submitBtn.classList.remove('loading');
                                        submitBtn.disabled = false;
                                    }
                                })
                                .catch(errors => {
                                    console.log(errors);
                                    submitBtn.classList.remove('loading');
                                    submitBtn.disabled = false;
                                    alert('처리 중 오류가 발생했습니다.');
                                });
                        });
                    }

                    return;
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
        });

    </script>
    <!-- 개발용 스크립트 E -->
@stop
