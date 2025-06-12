@extends('admin.layout.master')

@section('required-page-title', '자주 묻는 질문 목록')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/support/questionList.css">
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">자주 묻는 질문 목록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 조건 검색 S -->
                <form action="{{ route('faq.index') }}" method="GET" id="searchForm">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                    <input type="hidden" id="categoryInput" name="category" value="" >
                    <div class="search_box">
                        <div class="input_box">
                            <div class="input_item half">
                                <label class="input_title" for="question_title">제목</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="question_title" placeholder="검색어를 입력하세요" name="subject" value="{{ request('subject') ?? '' }}">
                                </div>
                            </div>
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
                                <label class="input_title">노출 여부</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" placeholder="전체" readonly data-value="" name="status" value="{{ request('status') ?? '' }}">
                                        <ul role="list">
                                            <li role="listitem" data-value="all">전체</li>
                                            <li role="listitem" data-value="1">노출</li>
                                            <li role="listitem" data-value="0">비노출</li>
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
                <div class="search_result @if($boards->total() < 1 ) no_result @endif">
                    <div class="result_top">
                        <div class="flex">
                            <p class="tit">검색결과</p>
                            <p class="count pc_block">총 <span>{{ $boards->total() }}</span>건의 결과가 조회 되었습니다.</p>
                        </div>
                        @if($boards->total() > 0 )
                            {{--                        <a href="javascript:void(0);" class="fill_btn download">--}}
                            {{--                            <span>엑셀 다운로드</span>--}}
                            {{--                        </a>--}}
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
                        <form id="listForm" action="{{ route('faq.destroy') }}" method="POST">
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
                                    <div class="type">분류</div>
                                    <div class="title">질문</div>
                                    <div class="date">등록 일자</div>
                                    <div class="writer">작성자</div>
                                    <div class="show">노출 여부</div>
                                    <div class="hits">조회수</div>
                                </div>
                                <ul role="list" class="tbody">
                                @php
                                    $startNumber = $boards->total() - ($boards->perPage() * ($boards->currentPage() - 1));
                                @endphp

                                @foreach($boards as $index => $item)
                                <li role="listitem" class="{{ ($item->is_display  == 1)  ? '' : 'hidden' }}">
                                    <a href="{{ route('faq.show', [$item->post_id]) }}">
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
                                        <div class="type">
                                            <span>분류</span>
                                            <p>{{$item->kname}}</p>
                                        </div>
                                        <div class="title">
                                            <span>질문</span>
                                            <p>{{$item->subject}}</p>
                                        </div>
                                        <div class="date">
                                            <span>등록 일자</span>
                                            <p>{{ format_date( $item->created_at ) }}</p>
                                        </div>
                                        <div class="writer">
                                            <span>작성자</span>
                                            <p>{{ ($item->user_name) ? decrypt($item->user_name) :'익명' }}</p>
                                        </div>
                                        <div class="show">
                                            <span>노출 여부</span>
                                            <p>{{ ($item->is_display  == 1)  ? '노출' : '비노출' }}</p>
                                        </div>
                                        <div class="hits">
                                            <span>조회수</span>
                                            <p>{{$item->hits}}</p>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                            <!-- 페이지네이션 S -->
                            {{ $boards->links('vendor.pagination.default') }}
                            <!-- 페이지네이션 E -->
                        @endif
                        </div>
                        </form>
                        <div class="bottom_btn">
                            <a href="{{ route('faq.create') }}" class="border_btn register">
                                <span>등록</span>
                            </a>
                        </div>
                    </div>
                    @if($boards->total() > 0 )
{{--                        <a href="{{ route('faq.download.excel') }}?{{ http_build_query(request()->query()) }}" class="border_btn blue download">--}}
{{--                            <span>엑셀 다운로드</span>--}}
{{--                        </a>--}}
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
                if (!deleteBtn.disabled) {
                    if(confirm('정말 삭제 하시겠습니까?')) {
                        form.submit();
                    }
                }
            });
            @endif
        });
    </script>
    <!-- 개발용 스크립트 E -->
@stop
