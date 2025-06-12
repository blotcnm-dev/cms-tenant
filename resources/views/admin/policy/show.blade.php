@extends('admin.layout.master')

@section('required-page-title', '상세페이지')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/termsView.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="javascript:;" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">약관 상세</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <div class="max_width">
                    <form id="mainForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="version" value="{{ $policy_content->version }}">
                        <div class="input_box gray_box">
                            <div class="input_item">
                                <label class="input_title">노출 여부</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" placeholder="선택" name="status" value="{{ $policy_content->is_state_text ?? '노출' }}" readonly="">
                                        <ul role="list">
                                            <li role="listitem">노출</li>
                                            <li role="listitem">비노출</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="input_item">
                                <label class="input_title" for="terms_title">제목</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="terms_title" placeholder="제목을 입력하세요" name="title" value="{{$policy_content->title}}" readonly>
                                </div>
                            </div>
                            <div class="input_item">
                                <label class="input_title" for="terms_text">내용</label>
                                <div class="inner_box">
                                    <textarea class="common_textarea" id="terms_text" name="info"  placeholder="내용을 입력하세요">{{ $policy_content->info }}</textarea>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="terms_date">수정 날짜</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="terms_date" value="{{ format_date($policy_content->updated_at , 'Y-m-d H:i:s' )  }}" readonly>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="terms_writer">작성자</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" id="terms_writer" value="{{ ($policy_content->user_name) ? decrypt($policy_content->user_name) :'익명' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- 하단 버튼 S -->
                        <div class="common_bottom_btn">
                            <button type="button" class="border_btn cancel" onclick="location.href='{{ route('policy.index') }}'">
                                <span>취소</span>
                            </button>
                            <button type="submit" class="border_btn save" data-type="versionup">
                                <span>버전업데이트</span>
                            </button>
                            <button type="submit" class="border_btn modify" data-type="edit">
                                <span>수정</span>
                            </button>
                        </div>
                        <!-- 하단 버튼 E -->
                    </form>
                    @php
                        $startNumber = $policy_content_history->total() - ($policy_content_history->perPage() * ($policy_content_history->currentPage() - 1));
                    @endphp
                    <div class="content_title_box">
                        <h3 class="title">변경 이력</h3>
                    </div>

                    <form action="{{ route('policy.show', ['id' => $policy_content->policy_contents_id]) }}" method="GET" id="searchForm">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                    </form>
                    <div class="result_list">
                        @if($policy_content_history->total() < 1 )
                        <div class="nodata">
                            <div>
                                <p>조회된 데이터가 없습니다.</p>
                            </div>
                        </div>
                        @else
                        <div class="common_table">
                            <div class="thead">
                                <div class="num">번호</div>
                                <div class="edit">
                                    <button type="button" class="sorting_btn">수정 일자</button>
                                </div>
                                <div class="writer">작성자</div>
                                <div class="management">관리</div>
                            </div>
                            <ul role="list" class="tbody">

                                @foreach($policy_content_history as $index => $item)
                                <li role="listitem">
                                    <div class="num">
                                        <span>번호</span>
                                        <p>{{ $startNumber - $index }}</p>
                                    </div>
                                    <div class="edit">
                                        <span>수정 일자</span>
                                        <p>{{ format_date( $item->created_at, 'Y-m-d H:i:s' )   }}</p>
                                    </div>
                                    <div class="writer">
                                        <span>작성자</span>
                                        <p>{{ ($item->user_name) ?? '익명' }}</p>
                                    </div>
                                    <div class="management">
                                        <span>관리</span>
                                        <p>
                                            <button type="button" class="fill_btn plus add_file layerOpen" data-title="이력 보기" data-url="{{ route('policy.show_history', ['id' => $item->history_id]) }}">이력 보기</button>
                                        </p>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- 페이지네이션 S -->
                        {{ $policy_content_history->links('vendor.pagination.default') }}
                        <!-- 페이지네이션 E -->
                        @endif
                    </div>
                </div>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
@stop

@section('required-page-add-content')
    <script type="module">
        import { gnbHandler } from "/src/js/navigation/gnbClassController.js";
        import { layerHandler } from "/src/js/components/layer.js";

        gnbHandler(1, 5);

        document.addEventListener("DOMContentLoaded", () => {
            document.addEventListener('click', (e)=> {
                const target = e.target.closest('.layerOpen');

                if (target) {
                    const title = target.dataset.title;
                    const contentUrl = target.dataset.url;

                    if (title && contentUrl) {
                        layerHandler(title, contentUrl);
                    }
                }
            })
        })
    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.getElementById('mainForm').addEventListener('submit', function(e) {

                e.preventDefault();
                // 모든 버튼 비활성화
                const actionButtons = this.querySelectorAll('.fill_btn.black');
                actionButtons.forEach(btn => {
                    btn.disabled = true;
                });

                const submitBtn = e.submitter;
                let actionType = '';
                if (submitBtn) {
                    actionType = submitBtn.getAttribute('data-type');
                    submitBtn.classList.add('loading');
                }
                // 액션 타입에 따른 처리
                let url = '{{ route('policy.update', ['id' => $policy_content->policy_contents_id] ) }}';
                if (actionType === 'versionup') {
                    url = '{{ route('policy.versionup', ['id' => $policy_content->policy_contents_id] ) }}';
                } else if (actionType === 'edit') {
                    url = '{{ route('policy.update', ['id' => $policy_content->policy_contents_id] ) }}';
                }
                const formData = new FormData(this);

                //console.log("url==>["+url+"]");
                //console.log("formData==>["+formData+"]");

                // AJAX 요청
                fetch( url , {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {

                        if (data.success) {
                            // 성공 시 처리
                            alert(data.message);
                            window.location.href = data.redirect || '{{ route('policy.index') }}';
                        } else {
                            // 에러 처리
                            if (data.errors) {
                                // 에러 메시지 표시
                                if (data.errors.title) {
                                    document.getElementById('title-error').textContent = data.errors.title[0];
                                }
                                if (data.errors.info) {
                                    document.getElementById('info-error').textContent = data.errors.info[0];
                                }
                            }

                            // 버튼 다시 활성화
                            submitBtn.classList.remove('loading');
                            actionButtons.forEach(btn => {
                                btn.disabled = false;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // 버튼 다시 활성화
                        submitBtn.classList.remove('loading');
                        actionButtons.forEach(btn => {
                            btn.disabled = false;
                        });
                        alert('처리 중 오류가 발생했습니다.');
                    });
            });
        });

    </script>
    <!-- 개발용 스크립트 E -->
@stop
