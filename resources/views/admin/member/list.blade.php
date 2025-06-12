@extends('admin.layout.master')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/userManagement/userManagement.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">사용자 목록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 조건 검색 S -->
                <form action="{{ route('member.list') }}" method="GET"  id="searchForm">
                    <input type="hidden" name="member_grade_id" value="{{ request('member_grade_id') }}" >
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                    <div class="search_box">
                        <div class="input_box">
                            <div class="input_item half">
                                <label class="input_title" for="user_id">아이디</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="user_id" name="user_id" value="{{ request('user_id') }}" placeholder="검색어를 입력하세요">
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="phone">전화번호</label>
                                <div class="inner_box">
                                    <input type="tel" class="common_input" id="phone" name="phone" value="{{ request('phone') }}" placeholder="검색어를 입력하세요">
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="user_name">성명</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="user_name" name="user_name" value="{{ request('user_name') }}" placeholder="검색어를 입력하세요">
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">수신동의</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" name="agree_type" value="{{ request('agree_type', '전체') }}" placeholder="전체" data-value="{{ request('agree_type', '') }}" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="">전체</li>
                                            <li role="listitem" data-value="sms">문자</li>
                                            <li role="listitem" data-value="email">이메일</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="input_item half date">
                                <label class="input_title">가입일</label>
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
                                <label class="input_title">회원상태</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" name="state" value="{{ request('state', '전체') }}" placeholder="전체" data-value="{{ request('state', '') }}" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="">전체</li>
                                            <li role="listitem" data-value="0">정상</li>
                                            <li role="listitem" data-value="1">탈퇴</li>
                                            <li role="listitem" data-value="2">차단</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">가입 방식</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" name="join_type" value="{{ request('join_type', '전체') }}" placeholder="전체" data-value="{{ request('join_type', '') }}" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="">전체</li>
                                            <li role="listitem" data-value="0">일반 가입</li>
                                            <li role="listitem" data-value="kakao">카카오</li>
                                            <li role="listitem" data-value="naver">네이버</li>
                                            <li role="listitem" data-value="google">구글</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">회원등급</label>
                                <div class="inner_box">

                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" name="member_grade_name" value="{{ request('member_grade_name' , '전체') }}" placeholder="전체"  readonly>
                                        <ul role="list">
                                            @foreach($grades as $grade)
                                                <li role="listitem" data-value="{{ $grade->code }}" >{{ $grade->code_name }}</li>
                                            @endforeach
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
                <div class="search_result  @if($members->total() < 1 ) no_result @endif">
                    <div class="result_top">
                        <div class="flex">
                            <p class="tit">검색결과</p>
                            <p class="count pc_block">총 <span>{{ $members->total() }}</span>건의 결과가 조회 되었습니다.</p>
                        </div>
                        @if($members->total() > 0 )
                        <a href="{{ route('member.download.excel') }}?{{ http_build_query(request()->query()) }}" class="fill_btn download">
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
{{--                            <ul role="list">--}}
{{--                                <li role="listitem" data-value="created_at__desc">최신순</li>--}}
{{--                                <li role="listitem" data-value="created_at__asc">오래된 순</li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
                    </div>
                    <div class="result_list">
                        @if($members->isEmpty())
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
                                        <div class="id">아이디</div>
                                        <div class="name">성명</div>
                                        <div class="right">등급</div>
                                        <div class="email">이메일</div>
                                        <div class="tel">전화번호</div>
                                        <div class="date">
                                            <button type="button" class="sorting_btn">가입일</button>
                                        </div>
                                        <div class="state">회원상태</div>
                                    </div>
                                    <ul role="list" class="tbody">
                                        @foreach($members as $member)
                                            <li role="listitem"
                                                class="
                                            @if($member->member_grade_id)
                                                grade_{{$member->member_grade_id}}
                                            @endif
                                            @if($member->state == 0)
                                                normal
                                            @elseif($member->state == 1)
                                                withdrawal
                                            @elseif($member->state == 2)
                                                reject
                                            @elseif($member->sleep == 1)
                                                inactive
                                            @endif
                                            "
                                            >
                                                <a href="{{ route('member.show', $member->member_id) }}">
                                                    <div class="num">
                                                        <span>번호</span>
                                                        <p>{{ $members->total() - (($members->currentPage() - 1) * $members->perPage() + $loop->index) }}</p>
                                                    </div>
                                                    <div class="id left">
                                                        <span>아이디</span>
                                                        <p>{{ $member->user_id }}</p>
                                                    </div>
                                                    <div class="name">
                                                        <span>성명</span>
    {{--                                                    마스킹 방법 구현 --}}
    {{--                                                        {{ mb_substr($member->user_name, 0, 1) }}*{{ mb_substr($member->user_name, -1, 1) }}--}}
    {{--                                                    --}}
                                                        <p>{{ $member->user_name }}</p>
                                                    </div>
                                                    <div class="right">
                                                        <span>등급</span>
                                                        <p>{{$member->member_grade_name}}</p>
                                                    </div>
                                                    <div class="email">
                                                        <span>이메일</span>
                                                        <p>{{ $member->email}}</p>
                                                    </div>
                                                    <div class="tel">
                                                        <span>전화번호</span>
                                                        <p>{{ $member->phone }}</p>
                                                    </div>
                                                    <div class="date">
                                                        <span>가입일</span>
                                                        <p>{{ format_date( $member->created_at ) }}</p>
                                                    </div>
                                                    <div class="state">
                                                        <span>회원상태</span>
                                                        <p>
                                                            @if($member->state == 0)
                                                                정상
                                                            @elseif($member->state == 1)
                                                                탈퇴
                                                            @elseif($member->state == 2)
                                                                차단
                                                            @elseif($member->sleep == 1)
                                                                휴면
                                                            @endif
                                                        </p>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="bottom_btn">
                                    <a href="{{ route('member.create') }}" class="fill_btn black">
                                        <span>등록</span>
                                    </a>
                                </div>
                                <!-- 페이지네이션 S -->
                                {{ $members->links('vendor.pagination.default') }}
                                <!-- 페이지네이션 E -->
                            </div>
                        @endif
                    </div>
                    <div class="bottom_btn fixed">
                        <a href="{{ route('member.create') }}" class="border_btn register">
                            <span>등록</span>
                        </a>
                    </div>
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



            document.getElementById('searchForm').addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const memberGradeNameInput = document.querySelector('input[name="member_grade_name"]');
                const memberGradeIdInput = document.querySelector('input[name="member_grade_id"]');

                if (memberGradeNameInput && memberGradeIdInput) {
                    const dataValue = memberGradeNameInput.getAttribute('data-value');
                    if (dataValue) {
                        memberGradeIdInput.value = dataValue;
                    }
                }

                this.submit();
            });


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



            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('edit_btn')) {
                    const parentDiv = e.target.closest('div');
                    const inputElement = parentDiv.querySelector('input');

                    if (inputElement) {
                        if (inputElement.hasAttribute('readonly')) {
                            inputElement.removeAttribute('readonly');
                            inputElement.focus();
                        } else {
                            inputElement.setAttribute('readonly', true);
                        }
                    }
                }
            });

        });

        function disable_button(element){
            element.disabled = true;
            element.classList.add('loading');
        }

        function restore_button(element){
            element.disabled = false;
            element.classList.remove('loading');
        }

        function collectFooterData(formData) {
            const footerItems = [];
            const footerElements = document.querySelectorAll('#footerList .draggable');

            footerElements.forEach(function(element, index) {
                const titleInput = element.querySelector('.w-300 input');
                const contentInput = element.querySelector('.inner_cnt > div:nth-child(2) input');
                const activeSwitch = element.querySelector('.common_switch input[type="checkbox"]');

                if (titleInput && contentInput && activeSwitch) {
                    footerItems.push({
                        title: titleInput.value,
                        content: contentInput.value,
                        active: activeSwitch.checked,
                        order: index
                    });
                }
            });

            // JSON 문자열로 변환하여 formData에 추가
            formData.append('footer_settings', JSON.stringify(footerItems));

            return formData;
        }

        function collectSnsData(formData) {
            const snsItems = [];
            const snsElements = document.querySelectorAll('#snsList .draggable');

            snsElements.forEach(function(element, index) {
                const nameInput = element.querySelector('.w-300 input');
                const linkInput = element.querySelector('.inner_cnt > div:nth-child(2) input');
                const activeSwitch = element.querySelector('.common_switch input[type="checkbox"]');

                if (nameInput && linkInput && activeSwitch) {
                    snsItems.push({
                        name: nameInput.value,
                        link: linkInput.value,
                        active: activeSwitch.checked,
                        order: index
                    });
                }
            });

            // JSON 문자열로 변환하여 formData에 추가
            formData.append('sns_settings', JSON.stringify(snsItems));

            return formData;
        }


        function collectLoginData(formData) {
            const loginItems = [];
            const loginElements = document.querySelectorAll('#loginList .draggable');

            loginElements.forEach(function(element, index) {
                // 각 로그인 항목의 입력 필드 가져오기
                const korNameInput = element.querySelector('.item:nth-child(1) div:nth-child(1) input');
                const engNameInput = element.querySelector('.item:nth-child(1) div:nth-child(2) input');
                const clientIdInput = element.querySelector('.item:nth-child(2) div:nth-child(1) input');
                const clientSecretInput = element.querySelector('.item:nth-child(2) div:nth-child(2) input');
                const redirectUrlInput = element.querySelector('.item:nth-child(3) div:nth-child(1) input');
                const apiKeyInput = element.querySelector('.item:nth-child(3) div:nth-child(2) input');
                const activeSwitch = element.querySelector('.common_switch input[type="checkbox"]');

                // 필수 항목이 존재하는지 확인
                if (korNameInput && engNameInput && clientIdInput && clientSecretInput) {
                    loginItems.push({
                        korName: korNameInput.value,
                        engName: engNameInput.value,
                        clientId: clientIdInput.value,
                        clientSecret: clientSecretInput.value,
                        redirectUrl: redirectUrlInput ? redirectUrlInput.value : '',
                        apiKey: apiKeyInput ? apiKeyInput.value : '',
                        active: activeSwitch ? activeSwitch.checked : false,
                        order: index
                    });
                }
            });

            formData.append('login_settings', JSON.stringify(loginItems));

            return formData;
        }
        function collectForbidData(formData) {
            const forbidItems = [];
            const forbidElements = document.querySelectorAll('.forbid_list li');
            const forbidUseRadio = document.querySelector('#forbid_1');

            // 각 금칙어 항목 수집
            forbidElements.forEach(function(element, index) {
                const wordText = element.querySelector('span').textContent;

                forbidItems.push({
                    word: wordText,
                    order: index
                });
            });

            // 금칙어 설정 정보 생성
            const forbidSettings = {
                words: forbidItems,
                active: forbidUseRadio ? forbidUseRadio.checked : true,
                count: forbidItems.length
            };

            // JSON 형태로 변환하여 폼 데이터에 추가
            formData.append('forbid_settings', JSON.stringify(forbidSettings));

            return formData;
        }
    </script>
    <!-- 개발용 스크립트 E -->
@stop
