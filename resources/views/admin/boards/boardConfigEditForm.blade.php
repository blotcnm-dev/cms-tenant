@extends('admin.layout.master')

@section('required-page-title', '게시판 수정')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/addBoard.css">
@stop

@section('required-page-header-js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="#" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">게시판 수정</h2>
            </div>
            <!-- 페이지 타이틀 E -->
    @php
//    echo "<pre>";
//print_r($board_config);
    @endphp
            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="mainForm" action="{{ route('configBoards.update', $board_config->board_config_id) }}" method="POST" enctype="multipart/form-data" class="max_width">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="board_config_id" id="board_config_id" value="{{$board_config->board_config_id}}">
                    <input type="hidden" name="board_id" value="{{$board_config->board_id}}">
                    <input type="hidden" name="is_active" value="{{$board_config->is_active}}">
                    <input type="hidden" name="is_category" value="{{$board_config->is_category}}">
                    <input type="hidden" name="board_type" value="{{$board_config->board_type}}">
                    <input type="hidden" name="list_view_authority_type" value="{{$board_config->list_view_authority_type}}">
                    <input type="hidden" name="content_view_authority_type" value="{{$board_config->content_view_authority_type}}">
                    <input type="hidden" name="content_write_authority_type" value="{{$board_config->content_write_authority_type}}">
                    <input type="hidden" name="reply_write_authority_type" value="{{$board_config->reply_write_authority_type}}">
                    <input type="hidden" name="gallery_theme" value="{{$board_config->gallery_theme}}">
                    <div class="content_title_box no_mg">
                        <h3 class="title">분류 / 유형 설정</h3>
                    </div>
                    <div class="input_box gray_box">
                        <div class="input_item half">
                            <label class="input_title">분류</label>
                            <div class="inner_box no_wrap">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="선택" data-value="{{$board_config->is_category}}" name="is_category_tmp" value="{{$board_config->category_txt}}" readonly>
                                    <ul role="list" id="category_list">
                                        @foreach($categorys as $config)
                                            <li role="listitem" data-value="{{ $config->depth_code }}">{{ $config->kname }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="fill_btn plus add_file layerOpen" data-title="분류 관리" data-url="{{ route('configBoards.categorylist') }}">
                                    <span>분류관리</span>
                                </button>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title">유형</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select select_type">
                                    <input type="text" class="common_input select_value" placeholder="선택" data-value="{{$board_config->board_type}}" value="{{ ($board_config->board_type === 'COMMON')  ? '게시판' : '갤러리' }}" name="board_type_tmp" readonly>
                                    <ul role="list">
                                        <li role="listitem" class="board" data-value="COMMON">게시판</li>
                                        <li role="listitem" class="gallery" data-value="GALLERY">갤러리</li>
                                    </ul>
                                </div>
                                <div id="board_type_error" class="error_msg"></div>
                            </div>
                        </div>
                        <div class="input_item">
                            <label class="input_title" for="board_title">제목</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="board_title" placeholder="제목을 입력하세요" name="board_name" value="{{$board_config->board_name}}">
                            </div>
                            <div id="board_name_error" class="error_msg"></div>
                        </div>
                    </div>

                    <div class="content_title_box">
                        <h3 class="title">사용자 권한 설정</h3>
                    </div>
                    <div class="input_box gray_box">
                        <div class="input_item half">
                            <label class="input_title">읽기</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="선택" data-value="{{$board_config->list_view_authority_type}}" name="list_view_authority_type_tmp" value="{{ $board_config->list_view_authority_type_tmp ?? '전체' }}" readonly>
                                    <ul role="list">
                                        <li role="listitem" data-value="0">전체</li>
                                        @foreach($member_level as $config)
                                            <li role="listitem" data-value="{{ $config->code }}">{{ $config->code_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title">등록/수정</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="선택" data-value="{{$board_config->content_view_authority_type}}" name="content_view_authority_type_tmp" value="{{ $board_config->content_view_authority_type_tmp ?? '전체' }}" readonly>
                                    <ul role="list">
                                        <li role="listitem" data-value="0">전체</li>
                                        @foreach($member_level as $config)
                                            <li role="listitem" data-value="{{ $config->code }}">{{ $config->code_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title">삭제</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="선택" data-value="{{$board_config->content_write_authority_type}}" name="content_write_authority_type_tmp" value="{{ $board_config->content_write_authority_type_tmp ?? '전체' }}" readonly>
                                    <ul role="list">
                                        <li role="listitem" data-value="0">전체</li>
                                        @foreach($member_level as $config)
                                            <li role="listitem" data-value="{{ $config->code }}">{{ $config->code_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title">댓글</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="선택" data-value="{{$board_config->reply_write_authority_type}}" name="reply_write_authority_type_tmp" value="{{ $board_config->reply_write_authority_type_tmp ?? '전체' }}" readonly>
                                    <ul role="list">
                                        <li role="listitem" data-value="0">전체</li>
                                        @foreach($member_level as $config)
                                            <li role="listitem" data-value="{{ $config->code }}">{{ $config->code_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content_title_box">
                        <h3 class="title">부가 설정</h3>
                    </div>
                    <div class="input_box gray_box type_box {{ ($board_config->board_type === 'COMMON')  ? 'type_board' : 'type_gallery' }}">
                        <div class="input_box">
                            <div class="input_item half">
                                <label class="input_title">댓글 사용 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_reply" id="comment_use_y" value="1" {{ ($board_config->is_reply === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_reply" id="comment_use_n" value="0" {{ ($board_config->is_reply === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">댓글 좋아요 사용 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_reply_like" id="comment_like_y" value="1" {{ ($board_config->is_reply_like === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_reply_like" id="comment_like_n" value="0" {{ ($board_config->is_reply_like === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">댓글 사진 첨부 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_reply_photo" id="comment_img_y" value="1" {{ ($board_config->is_reply_photo === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_reply_photo" id="comment_img_n" value="0" {{ ($board_config->is_reply_photo === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">게시글 좋아요 사용 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_like" id="board_like_y" value="1" {{ ($board_config->is_like === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_like" id="board_like_n" value="0" {{ ($board_config->is_like === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">금칙어 사용 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_ban" id="board_forbid_y" value="1" {{ ($board_config->is_ban === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_ban" id="board_forbid_n" value="0" {{ ($board_config->is_ban === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">비밀글 사용 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_secret" id="board_secret_y" value="1" {{ ($board_config->is_secret === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_secret" id="board_secret_n" value="0" {{ ($board_config->is_secret === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title">게시글 최상단 고정 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_topfix" id="board_pin_y" value="1" {{ ($board_config->is_topfix === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_topfix" id="board_pin_n" value="0" {{ ($board_config->is_topfix === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half board">
                                <label class="input_title">첨부 파일 업로드 여부</label>
                                <div class="inner_box flex gap_input">
                                    <label class="radio_input">
                                        <input type="radio" name="is_file" id="board_upload_file_y" value="1" {{ ($board_config->is_file === 1)  ? 'checked' : '' }}>
                                        <span>사용</span>
                                    </label>
                                    <label class="radio_input">
                                        <input type="radio" name="is_file" id="board_upload_file_n" value="0" {{ ($board_config->is_file === 0)  ? 'checked' : '' }}>
                                        <span>사용안함</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half board">
                                <label class="input_title">첨부 파일 업로드 개수</label>
                                <div class="inner_box">
                                    <input type="tel" class="common_input" name="file_uploadable_count" value="{{ $board_config->file_uploadable_count }}" >
                                </div>
                            </div>
                            <div class="input_item half board">
                                <label class="input_title file_mb">
                                    첨부 파일 업로드 개수
                                    <span class="sub_title">(최대 200 MB)</span>
                                </label>
                                <div class="inner_box capacity">
                                    <p>파일당</p>
                                    <input type="tel" class="common_input" name="file_max_size" value="{{ $board_config->file_max_size }}">
                                    <span>MB 이하</span>
                                </div>
                            </div>
                            <div class="input_item half gallery">
                                <label class="input_title">갤러리 파일 업로드 개수</label>
                                <div class="inner_box">
                                    <input type="tel" class="common_input" name="gallery_uploadable_count" value="{{ $board_config->gallery_uploadable_count }}">
                                </div>
                            </div>
                            <div class="input_item half gallery">
                                <label class="input_title file_mb">
                                    갤러리 파일 업로드 용량
                                    <span class="sub_title">(최대 200 MB)</span>
                                </label>
                                <div class="inner_box capacity">
                                    <p>파일당</p>
                                    <input type="tel" class="common_input" name="gallery_max_size" value="{{ $board_config->gallery_max_size }}">
                                    <span>MB 이하</span>
                                </div>
                            </div>
                            <div class="input_item half gallery">
                                <label class="input_title">갤러리 테마</label>
                                <div class="inner_box">
                                    <div class="custom_select_1 js_custom_select">
                                        <input type="text" class="common_input select_value" placeholder="선택" data-value="{{ $board_config->gallery_theme }}" name="gallery_theme_tmp" value="{{ ($board_config->gallery_theme === 'list')  ? '리스트형' : '그리드형' }}" readonly>
                                        <ul role="list">
                                            <li role="listitem" data-value="list">리스트형</li>
                                            <li role="listitem" data-value="grid">그리드형</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="input_item">
                                <label class="input_title">문의 알림 메일 설정</label>
                                <div class="inner_box">
                                    <input type="text" class="common_input" name="incoming_mail" value="{{ $board_config->incoming_mail }}">
                                    <span class="noti">여러 개 등록 시 콤마(,)로 구분해 등록해주세요. (예시: example@example.com, contact@sample.com)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn fixed">
                        <a href="{{ route('configBoards.list') }}" class="border_btn cancel">
                            <span>취소</span>
                        </a>
                        <button type="submit" id="submitBtn" class="border_btn register">
                            <span>수정</span>
                        </button>
                    </div>
                    <!-- 하단 버튼 E -->
                </form>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
@stop

@section('required-page-add-content')
    <script type="module">
        import { layerHandler } from "/src/js/components/layer.js";
        import { listManagement } from "/src/js/components/listManagement.js";

        const selectAnswerTypeHandler = () => {
            const $selectBox = document.querySelector('.js_custom_select.select_type');
            const $selectList = $selectBox.querySelectorAll('li');
            const $answerType = document.querySelector('.type_box');

            $selectList.forEach(item => {
                item.addEventListener('click', (e) => {
                    const selected = e.currentTarget.classList[0];
                    const typeClass = `type_${selected}`;

                    $answerType.className = '';
                    $answerType.classList.add('input_box', 'gray_box', 'type_box', typeClass);
                });
            });
        };

        const listManagementDynamic = (options) => {
            const {
                btnSelector,
                templateId,
                containerSelector,
            } = options;

            const $template = document.getElementById(templateId);
            if (!$template) return;

            document.addEventListener("click", (e) => {
                const $btn = e.target.closest(btnSelector);
                if (!$btn) return;

                const $menuItem = $btn.closest(".menu_item");
                const $targetContainer = $menuItem?.querySelector(containerSelector);
                if (!$targetContainer) return;

                const $clone = $template.content.firstElementChild.cloneNode(true);

                $targetContainer.appendChild($clone);
                document.querySelectorAll(".cl_management-cnt .menu_item").forEach(($item) =>
                    $item.classList.remove("on")
                );
                $menuItem?.classList.add("on");
            });

            document.addEventListener("click", (e) => {
                const $delBtn = e.target.closest(".depth_2 .del_btn");
                if ($delBtn) {
                    const $item = $delBtn.closest(".menu_item.depth_2");
                    if ($item) $item.remove();
                }
            });
        };

        const depth2SortableInstances = new Map();

        const initSortables = () => {
            const isMobile = window.innerWidth <= 820;

            document.querySelectorAll("[data-name='categorylist'] .accordion ul").forEach(($ul) => {
                if (isMobile) {
                    const instance = depth2SortableInstances.get($ul);
                    if (instance) {
                        instance.destroy();
                        depth2SortableInstances.delete($ul);
                        $ul.removeAttribute("data-sortable-init");
                    }
                } else {
                    if (!depth2SortableInstances.has($ul)) {
                        const instance = Sortable.create($ul, {
                            group: "depth_2_only",
                            animation: 150,
                            onMove: (evt) => {
                                return evt.dragged.classList.contains("depth_2") &&
                                    evt.related.classList.contains("depth_2");
                            }
                        });
                        depth2SortableInstances.set($ul, instance);
                        $ul.dataset.sortableInit = "true";
                    }
                }
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            selectAnswerTypeHandler();

            document.addEventListener('click', (e) => {
                const $target = e.target;

                const layerTrigger = $target.closest('.layerOpen');
                if (layerTrigger) {
                    const title = layerTrigger.dataset.title;
                    const contentUrl = layerTrigger.dataset.url;

                    if (title && contentUrl) {
                        layerHandler(title, contentUrl, ()=> {
                            listManagement({
                                btnSelector: ".cl_management-top .plus",
                                templateId: "depth_1",
                                containerSelector: "[data-name='categorylist'] .menu",
                                switchPrefix: "depth1",
                            });
                            listManagementDynamic({
                                btnSelector: ".depth_1 .plus_btn",
                                templateId: "depth_2",
                                containerSelector: ".accordion ul",
                            });
                            initSortables();
                        })
                    }

                    return;
                }

                const $accBtn = $target.closest('.accordion_btn');
                if ($accBtn) {
                    const $popup = document.querySelector('[data-name="categorylist"]');
                    if (!$popup) return;

                    const $clicked = $accBtn.closest('.depth_1');
                    const isAlreadyOn = $clicked.classList.contains('on');

                    $popup.querySelectorAll('.depth_1').forEach(item => item.classList.remove('on'));
                    if (!isAlreadyOn) {
                        $clicked.classList.add('on');
                    }
                    return;
                }

                if (e.target.closest(".plus_btn")) {
                    setTimeout(() => initSortables(), 0);
                }
            });

            window.addEventListener("resize", () => {
                initSortables();
            });
        });
    </script>
    <!-- 개발용 스크립트 S -->
    <script>

        document.addEventListener('click', (e) => {

            const btn = e.target.closest('#submit2Btn');
            if (!btn) return;  // 버튼 클릭이 아니면 무시

            e.preventDefault();      // 본래 submit 막기

            // 이제 btn 은 null 이 될 수 없으니 바로 classList / disabled 사용
            btn.classList.add('loading');
            btn.disabled = true;

            // form 처리
            const form = btn.closest('form');
            if (!form) {
                console.error('버튼이 속한 <form>을 찾을 수 없습니다.');
                return;
            }

            const url2 = '{{ route('configBoards.categorystore') }}';

            // AJAX 요청
            fetch( url2 , {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 422) {
                        // validation 에러
                        return response.json().then(data => { throw { type:'validation', data }; });
                    }
                    if (!response.ok) {
                        throw { type:'http', status: response.status };
                    }
                    return response.json();
                })
                .then(data => {
                    // 성공했을 때
                    location.reload();
                })
                .catch(err => {
                    btn.classList.remove('loading');
                    btn.disabled = false;

                    if (err.type === 'validation') {
                        // Laravel 이 반환한 에러 객체
                        const errors = err.data.errors;
                        if (errors['cate'])          alert(errors['cate'][0]);
                        else if (errors['cate.*'])   alert(errors['cate.*'][0]);
                        else                          alert('카테고리 값(공백은 항목 제거 또는 등록)을 확인해주세요.');
                    }
                    else {
                        console.error(err);
                        alert('서버 통신 중 오류가 발생했습니다.');
                    }
                });
        });

        {{--function categoty_list() {--}}
        {{--    const url = '{{ route('configBoards.categorylist_ajax') }}';--}}
        {{--    const params = {};--}}
        {{--    var view_data = '';--}}
        {{--    $.ajax({--}}
        {{--        url: url,--}}
        {{--        data: params,--}}
        {{--        type: 'GET',--}}
        {{--        dataType: 'json',--}}
        {{--        success: function(res) {--}}
        {{--            if (!res.success) {--}}
        {{--                console.error('카테고리 로드 실패');--}}
        {{--                return;--}}
        {{--            }--}}
        {{--            const list = res.data.categorylist;--}}
        {{--            const container = document.getElementById('category_list');--}}
        {{--            container.innerHTML = '';--}}
        {{--            list.forEach(item => {--}}
        {{--                const li = document.createElement('li');--}}
        {{--                li.setAttribute('role', 'listitem');--}}
        {{--                li.setAttribute('data-value', item.depth_code);--}}
        {{--                li.textContent = item.kname;--}}

        {{--                container.appendChild(li);--}}
        {{--            });--}}
        {{--            console.log("결과==>[", res, "]");--}}
        {{--        },--}}
        {{--        error: function(jqXHR, textStatus, errorThrown) {--}}
        {{--            console.log('1 status : ' + jqXHR.status);--}}
        {{--            console.log('2 textStatus : ' + textStatus);--}}
        {{--        },--}}
        {{--        complete: function(jqXHR, textStatus) {--}}
        {{--            console.log("3 AJAX complete : " + url);--}}
        {{--        }--}}
        {{--    });--}}
        {{--}--}}

        document.addEventListener("DOMContentLoaded", function() {

            const comment_use_y   = document.getElementById('comment_use_y');
            const comment_use_n   = document.getElementById('comment_use_n');
            const comment_like_y  = document.getElementById('comment_like_y');
            const comment_like_n  = document.getElementById('comment_like_n');
            const comment_img_y   = document.getElementById('comment_img_y');
            const comment_img_n   = document.getElementById('comment_img_n');

            function toggleDisabled() {
                // comment_use_y 라디오가 체크되었을 때만 false, 아니면 true
                const enable = comment_use_y.checked;
                comment_like_y.disabled = !enable;
                comment_like_n.disabled = !enable;
                comment_img_y.disabled  = !enable;
                comment_img_n.disabled  = !enable;
            }

            // 이벤트 바인딩
            comment_use_y.addEventListener('change', toggleDisabled);
            comment_use_n.addEventListener('change', toggleDisabled);

            // 초기 상태 적용
            toggleDisabled();


            // 라디오 버튼 그룹과 토글 대상 input들을 한 번에 가져옵니다.
            const yesRadio   = document.getElementById('board_upload_file_y');
            const noRadio    = document.getElementById('board_upload_file_n');
            const toggledInputs  = document.querySelectorAll(
                'input[name="file_uploadable_count"], input[name="file_max_size"]'
            );

            // 라디오 값이 "yes"인 경우에만 readOnly 해제, 아니면 readOnly 설정
            const toggleReadOnly = () => {
                const enabled = yesRadio.checked;
                toggledInputs.forEach(i => {
                    // DOM 속성(property)으로 설정
                    i.readOnly = !enabled;
                    // 또는 attribute 방식:
                    if (!enabled) {
                        i.setAttribute('readonly', '');
                    } else {
                        i.removeAttribute('readonly');
                    }
                });
            };

            // 모든 라디오에 change 이벤트 바인딩
            yesRadio.addEventListener('change', toggleReadOnly);
            noRadio.addEventListener('change', toggleReadOnly);

            // 초기 상태 반영
            toggleReadOnly();


            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // 버튼 비활성화
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                document.querySelector('input[name="is_category"]').value = document.querySelector('input[name="is_category_tmp"]').getAttribute('data-value');
                document.querySelector('input[name="board_type"]').value = document.querySelector('input[name="board_type_tmp"]').getAttribute('data-value');
                document.querySelector('input[name="list_view_authority_type"]').value = document.querySelector('input[name="list_view_authority_type_tmp"]').getAttribute('data-value');
                document.querySelector('input[name="content_view_authority_type"]').value = document.querySelector('input[name="content_view_authority_type_tmp"]').getAttribute('data-value');
                document.querySelector('input[name="content_write_authority_type"]').value = document.querySelector('input[name="content_write_authority_type_tmp"]').getAttribute('data-value');
                document.querySelector('input[name="reply_write_authority_type"]').value = document.querySelector('input[name="reply_write_authority_type_tmp"]').getAttribute('data-value');
                document.querySelector('input[name="gallery_theme"]').value = document.querySelector('input[name="gallery_theme_tmp"]').getAttribute('data-value');

                const formData = new FormData(this);
                const url = '{{ route('configBoards.update', $board_config->board_config_id) }}';

                // console.log("======== FormData의 내용을 출력 ======");
                // formData.forEach((value, key) => {
                //     console.log(key + ":"+ value);
                // });
                // console.log("=====================");

                // AJAX 요청
                fetch( url , {
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
                            // 성공 시 처리
                            alert(data.message);
                            window.location.href = data.redirect || '{{ route('configBoards.list') }}';
                        } else {
                            if (data.errors.board_name) {
                                document.getElementById('board_name_error').textContent = data.errors.board_name[0];
                                document.getElementById('board_name_error').style.display = 'block';
                            }
                            // 버튼 다시 활성화
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(errors => {
                        console.log(errors);
                        // 버튼 다시 활성화
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        alert('처리 중 오류가 발생했습니다.');
                    });
            });
        });
    </script>
    <!-- 개발용 스크립트 E -->
@stop
