@extends('admin.layout.master')

@section('required-page-title', '게시물 상세')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/postView.css">
@stop

@section('required-page-header-js')

@stop

@section('required-page-main-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="#" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">게시물 상세</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 게시물 상단 S -->
                <div class="white_wrap post_top">
                    <ul role="list">
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">분류 선택</span>
                                <div class="info depth">
                                    <p>{{$boards->kname}}</p>
                                </div>
                            </div>
                        </li>
                        <li role="listitem" class="two">
                            <div class="inner_box">
                                <span class="title">게시물 번호</span>
                                <div class="info">{{$boards->post_id}}</div>
                            </div>
                            <div class="inner_box">
                                <span class="title">조회수</span>
                                <div class="info">{{$boards->hits}}</div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">작성자</span>
                                <div class="info">{{($boards->user_name) ? decrypt($boards->user_name):'익명'}}</div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">등록 일자</span>
                                <div class="info">{{format_date( $boards->created_at,'Y.m.d H:m:s' )}}</div>
                            </div>
                        </li>
                        <li role="listitem" class="full">
                            <div class="inner_box">
                                <span class="title">제목</span>
                                <!-- 비밀글 선택 시 'lock' 클래스 추가 -->
                                <div class="info post_tit {{ ( $boards->is_secret === 1 )  ? 'lock': '' }}">{{$boards->subject}}</div>
                            </div>
                            <div class="inner_box input_gap">
                                <label class="chk_input">
                                    <input type="checkbox" name="is_secret" id="chk_lock" {{ ( $boards->is_secret === 1 )  ? 'checked':'' }}>
                                    <span>비밀글</span>
                                </label>
                                <label class="chk_input">
                                    <input type="checkbox" name="is_best" data-post-id="{{ $boards->post_id }}" data-field="is_best" {{ ( $boards->is_best === 1 ) ? 'checked':'' }}>
                                    <span>최상단 고정</span>
                                </label>
                                <label class="chk_input">
                                    <input type="checkbox" name="is_approval" {{ ( $boards->is_approval === 1 )  ? 'checked':'' }}>
                                    <span>스팸 적용</span>
                                </label>
                            </div>
                        </li>
                        @if($board_config->board_type === 'COMMON' && $files->count() > 0)
                        <li role="listitem" class="full">
                            <div class="inner_box">
                                <span class="title">첨부 파일&nbsp;<span>({{ $files->count() }})</span>개</span>
                                <div class="info">
                                    <ul role="list">
                                        @foreach($files as $file)
                                            <li role="listitem">
                                                <span>{{ $file->fname }}</span>
                                                <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}">다운로드</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                    @endif
                </div>

                <!-- 게시물 컨텐츠 S -->
                <div class="white_wrap post_content">
                    @if($board_config->board_type === 'GALLERY' && $files->count() > 0)
                        @foreach($files as $file)
                            <img src="{{ $file->path }}" alt="{{ $file->fname }}">
                        @endforeach
                    @endif
                    {!! renderContentAllowHtmlButEscapeScript($boards->content) !!}
                </div>
                <!-- 게시물 컨텐츠 E -->

                <!-- 하단 버튼 S -->
                @if($board_config->is_write === 'Y')
                <!-- 수정 권한 있을 때 -->
                <div class="common_bottom_btn">
                    <a href="{{ route('boards.board.list', [$board_config->board_id]) }}" class="border_btn cancel">
                        <span>취소</span>
                    </a>
                    <a href="{{ route('boards.board.edit', [$board_config->board_id,$boards->post_id]) }}" class="border_btn modify">
                        <span>수정</span>
                    </a>
                </div>
                @else
                <!-- 수정 권한 없을 때 -->
                <div class="common_bottom_btn">
                    <a href="{{ route('boards.board.list', [$board_config->board_id]) }}" class="border_btn save">
                        <span>확인</span>
                    </a>
                </div>
                @endif
                <!-- 하단 버튼 E -->

                @if($board_config->is_reply === 1)
                <!-- 게시물 댓글 S -->
                <div class="white_wrap post_comment">
                    <div class="comment_count">
                        @if($board_config->is_like === 1 )
                        <div class="heart">
                            <button type="button" class="heart">{{($boards->likes) ?? 0}}</button>
                        </div>
                        @endif
                        <div class="num">
                            <span class="reply_cnt">0</span>
                        </div>
                    </div>
                    <div class="comment_list">
                        <div class="top">
                            <p>댓글 <span class="reply_cnt">0</span></p>
                        </div>
                        <ul role="list">
                            @if($board_config->is_replay === 'Y')
                                <li role="listitem">
                                    <div class="reply_box write" style="display: block;padding-left:2rem">
                                        <div class="inner_box">
                                            <div class="profile">
                                                @if(session()->has('blot_upf') && session()->get('blot_upf'))
                                                    <img src="{{ session()->get('blot_upf') }}" alt="프로필 사진" onerror="this.onerror=null; this.src='{{ asset('/src/assets/images/no_profile.png') }}';">
                                                @else
                                                    <img src="{{ asset('/src/assets/images/no_profile.png') }}" alt="프로필 사진 없음">
                                                @endif
                                            </div>
                                            <div class="comment">
                                                <form id="replyForm" action="{{ route('boards.board.repliesstore', [$board_config->board_id,$boards->post_id]) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="reid" value="0">
                                                    <textarea id="commentInput" rows="1" name="comment" value="{{ old('comment') }}" placeholder="댓글을 입력하세요" style="width: 100%; min-height: 18rem; border-radius: 0.5rem; border: 1px solid var(--color-border); background-color: var(--color-white);"></textarea>
                                                    <div id="comment_error" style="display:none; color:red; margin-top:.5rem;"></div>
                                                    <div class="btn_flex"><button type="button" id="replyBtn" class="save">등록</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                        <ul id="comment_list" role="list">

                        </ul>
                    </div>
                </div>
                <!-- 게시물 댓글 E -->
                @endif
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('required-page-add-content')
    <script type="module">
        const lockHandler =()=> {
            const chkLock = document.getElementById('chk_lock');
            const tit = document.querySelector('.post_tit');

            if (chkLock.checked) {
                tit.classList.add('lock');
            } else {
                tit.classList.remove('lock');
            }

            chkLock.addEventListener('change', () => {
                if (chkLock.checked) {
                    tit.classList.add('lock');
                } else {
                    tit.classList.remove('lock');
                }
            });
        }

        const accordionHandler =()=> {
            document.addEventListener('click', (e) => {
                const accordion = e.target.closest('.js_accordion');
                const btn = e.target.closest('.js_accordion_btn');

                document.querySelectorAll('.js_accordion').forEach(acc => {
                    if (acc !== accordion) {
                        acc.classList.remove('on');
                    }
                });

                if (btn) {
                    accordion.classList.toggle('on');
                } else if (!accordion) {
                    document.querySelectorAll('.js_accordion').forEach(acc => acc.classList.remove('on'));
                }
            });
        }

        const heartHandler = () => {
            document.addEventListener('click', (e) => {
                const heart = e.target.closest('button.heart');
                if (!heart) return;

                let count = parseInt(heart.textContent) || 0;

                if (heart.classList.contains('on')) {
                    heart.classList.remove('on');
                    count -= 1;
                } else {
                    heart.classList.add('on');
                    count += 1;
                }

                heart.textContent = count;
            });
        };

        const replyHandler =()=> {
            document.addEventListener('click', (e) => {
                const replyBtn = e.target.closest('button.reply_btn');
                if (replyBtn) {
                    const li = replyBtn.closest('li');
                    const replyBox = li.querySelector('.reply_box.write');
                    if (replyBox) {
                        replyBox.style.display = 'block';
                    }
                    return;
                }

                const cancelBtn = e.target.closest('.reply_box.write button.cancel');
                if (cancelBtn) {
                    const replyBox = cancelBtn.closest('.reply_box.write');
                    if (replyBox) {
                        replyBox.style.display = 'none';
                    }
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            lockHandler();
            // accordionHandler();
            heartHandler();
            replyHandler();
        })
    </script>
    <!-- 개발용 스크립트 S -->
    <script>

        $(document).on('change', 'input[name="is_best"]', function() {
            const checkbox = $(this);
            const postId = checkbox.data('post-id');
            const fieldId = checkbox.data('field');
            const fieldVal = checkbox.is(':checked') ? 1 : 0;
            const boardId   = @json($board_config->board_id);      // 현재 보드 ID

            // 체크박스 비활성화 (중복 클릭 방지)
            checkbox.prop('disabled', true);

            $.ajax({
                url: `/master/board/${boardId}/state_update`,
                type: 'PUT',
                data: {
                    post_id: postId,
                    fild_id: fieldId,
                    fild_val: fieldVal,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                    } else {
                        checkbox.prop('checked', !checkbox.is(':checked'));
                        alert(response.message);
                    }
                },
                error: function() {
                    checkbox.prop('checked', !checkbox.is(':checked'));
                    alert('서버 오류가 발생했습니다.');
                },
                complete: function() {
                    checkbox.prop('disabled', false);
                }
            });
        });


        @if($board_config->is_reply === 1)
        document.addEventListener('DOMContentLoaded', () => {

            // 1) 가장 먼저 form 가져오기
            const form = document.getElementById('replyForm');
            if (!form) {
                //console.error('replyForm element not found');
                return;
            }

            // 2) 나머지 요소 캐싱
            const btn      = document.getElementById('replyBtn');
            const textarea = document.getElementById('commentInput');
            const errorDiv = document.getElementById('comment_error');
            const url      = form.getAttribute('action');

            if (!btn) {
                console.error('replyBtn element not found');
                return;
            }

            // 3) 버튼 클릭 시 submit 이벤트 트리거
            btn.addEventListener('click', () => {
                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            });

            // 4) 실제 submit 처리
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                errorDiv.style.display = 'none';
                btn.classList.add('loading');
                btn.disabled = true;

                // 클라이언트 검증
                const comment = textarea.value.trim();
                if (!comment) {
                    errorDiv.textContent = '댓글을 입력해주세요.';
                    errorDiv.style.display = 'block';
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    return;
                }

                const formData = new FormData(form);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                        textarea.value = '';
                        // 등록 성공 시 리디렉트
                        loadComments();
                    } else {
                        // 서버 검증 에러 처리 (comment 키 확인)
                        if (data.errors.comment) {
                            errorDiv.textContent = data.errors.comment[0];
                            errorDiv.style.display = 'block';
                        }
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    }
                } catch (err) {
                    console.error(err);
                    alert('처리 중 오류가 발생했습니다.');
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            });

            document.addEventListener('click', function(e) {
                // .reply_del_btn 버튼 클릭 감지
                if (!e.target.matches('.reply_del_btn')) return;

                if(!confirm('정말 삭제 하시겠습니까?')) return;

                const commentId = e.target.dataset.commentId;           // 삭제할 댓글 ID
                const boardId   = @json($board_config->board_id);      // 현재 보드 ID
                const token     = document.querySelector('meta[name="csrf-token"]').content;

                const url = `/master/board/${boardId}/reply/${commentId}`;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                })
                    // 1) HTTP 상태 체크 (필요 시)
                    .then(res => {
                        if (!res.ok) throw new Error(`서버 에러: ${res.status}`);
                        return res.json();     // 2) JSON 파싱
                    })
                    // 3) 파싱된 JSON 객체(data)에 접근
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.data || '댓글 삭제에 실패했습니다.');
                        }
                        // 성공했을 때
                        loadComments();
                        alert('댓글이 삭제 되었습니다.');
                    })
                    .catch(err => {
                        console.error(err);
                        alert(err.message || '댓글 삭제 중 오류가 발생했습니다.');
                    });
            });

        });

        // 현재 페이지, 마지막 페이지, 로딩 상태 플래그
        let currentPage = 1;
        let lastPage    = 1;
        let isLoading   = false;
        function loadComments(page = 1, append = false) {
            isLoading = true;

            $.ajax({
                url: '{{ route('boards.board.replieslist_ajax', [$board_config->board_id, $boards->post_id]) }}',
                type: 'GET',
                dataType: 'json',
                data: { page: page },
                success: function(res) {
                    if (!res.success) {
                        console.error('댓글 로드 실패');
                        return;
                    }

                    const repliesData = res.data.data.replies;
                    const comments    = repliesData.data;        // 실제 댓글 배열
                    currentPage       = repliesData.current_page;
                    lastPage          = repliesData.last_page;
                    const total       = repliesData.total;       // 전체 댓글 수

                    const $container = $('#comment_list');
                    if (!append) {
                        $container.empty();                     // 최초 로드시 컨테이너 비우기
                    }

                    comments.forEach(function(comment) {
                        $container.append(createCommentHTML(comment));
                    });

                    // 전체 댓글 수 표시
                    $('.reply_cnt').text(total);

                    // 답글 버튼 등 다시 바인딩
                    setupReplyButtons();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('댓글 불러오기 오류:', textStatus);
                },
                complete: function() {
                    isLoading = false;
                }
            });
        }

        // 댓글 HTML을 생성하는 함수
        function createCommentHTML(comment) {

            const userName = comment.user_name && comment.user_name.trim() !== ''
                ? comment.user_name
                : '익명';
            return `
    <li role="listitem">
        <div class="inner_box">
            <div class="profile">
                <img src="${comment.profile_image || '/src/assets/images/no_profile.png'}" alt="">
            </div>
            <div class="comment">
                <div class="info">
                    <p class="name">${userName}</p>
                    <span class="date">${comment.created_at}</span>
                </div>
                <div class="txt">
                    ${comment.content.replace(/\r\n|\n|\r/g, '<br>')}
                </div>
                <div class="bottom_box">
                @if($board_config->is_reply_like === 1 )
                    <button type="button" class="heart">${comment.likes}</button>
                @endif
                @if($board_config->is_replay === 'Y')
                    <button type="button" class="reply_btn" data-comment-id="${comment.id}">답글달기</button>
                @endif
                </div>
            </div>
            <ul role="list" class="posi_right">
             @if($board_config->is_replay === 'Y')
                <!--<li role="listitem">
                    <button type="button">수정</button>
                </li>-->
                <li role="listitem">
                    <button type="button" class="reply_del_btn" data-comment-id="${comment.id}">삭제</button>
                </li>
             @endif
            </ul>
        </div>
        <div class="reply_box write" id="reply_form_${comment.id}">
            <div class="inner_box">
                <div class="profile">
            @if(session()->has('blot_upf') && session()->get('blot_upf'))
                <img src="{{ session()->get('blot_upf') }}" alt="프로필 사진" onerror="this.onerror=null; this.src='{{ asset('/src/assets/images/no_profile.png') }}';">
            @else
                <img src="{{ asset('/src/assets/images/no_profile.png') }}" alt="프로필 사진 없음">
            @endif
                </div>
                <div class="comment">
                    <form class="replyForm" action="{{ route('boards.board.repliesstore', [$board_config->board_id,$boards->post_id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="reid" value="${comment.id}">
                        <textarea rows="1" name="comment" placeholder="댓글을 입력하세요"></textarea>
                        <div class="btn_flex">
                            <button type="button" class="cancel">취소</button>
                            <button type="button" class="save rerebtn">등록</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- 대댓글 목록이 있으면 여기에 추가 -->
        ${comment.rereplies ? createRepliesHTML(comment.rereplies) : ''}
    </li>
    `;
        }

        // 대댓글 HTML을 생성하는 함수
        function createRepliesHTML(replies) {

            let repliesHtml = '';

            replies.forEach(reply => {
                const userName = reply.user_name && reply.user_name.trim() !== ''
                    ? reply.user_name
                    : '익명';
                repliesHtml += `
        <div class="reply_box">
            <div class="inner_box">
                <div class="profile">
                    <img src="${reply.profile_image || '/src/assets/images/no_profile.png'}" alt="">
                </div>
                <div class="comment">
                    <div class="info">
                        <p class="name">${userName}</p>
                        <span class="date">${reply.created_at}</span>
                    </div>
                    <div class="txt">
                       ${reply.content.replace(/\r\n|\n|\r/g, '<br>')}
                    </div>
                    <div class="bottom_box">
                    @if($board_config->is_reply_like === 1 )
                        <button type="button" class="heart">${reply.likes}</button>
                    @endif
                    </div>
                </div>
                <ul role="list" class="posi_right">
                    @if($board_config->is_replay === 'Y')
                    <!--<li role="listitem">
                        <button type="button">수정</button>
                    </li>-->
                    <li role="listitem">
                        <button type="button" class="reply_del_btn" data-comment-id="${reply.id}">삭제</button>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        `;
            });

            return repliesHtml;
        }

        // 답글달기 버튼에 이벤트 리스너를 추가하는 함수
        function setupReplyButtons() {
            const replyButtons = document.querySelectorAll('.reply_btn');

            replyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-comment-id');
                    const replyForm = document.getElementById(`reply_form_${commentId}`);

                    // 답글 폼 토글
                    if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                        replyForm.style.display = 'block';
                    } else {
                        replyForm.style.display = 'none';
                    }
                });
            });

            // 답글 등록 버튼에 이벤트 리스너 추가
            const submitButtons = document.querySelectorAll('.rerebtn');
            submitButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('form');
                    const formData = new FormData(form);

                    $.ajax({
                        url: form.action,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                // 댓글 목록 새로고침
                                loadComments();
                            } else {
                                console.error('답글 등록 실패');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('답글 등록 오류:', textStatus);
                        }
                    });
                });
            });
        }

        // 페이지 로드시 댓글 목록 불러오기
        $(document).ready(function() {
            loadComments();

            $(window).on('scroll', function() {
                if (isLoading) return;               // 이미 요청 중이면 무시
                if (currentPage >= lastPage) return; // 더 불러올 게 없으면 무시

                // 문서 바닥에서 100px 이내로 스크롤되면
                if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
                    loadComments(currentPage + 1, true);
                }
            });
        });
        @endif
    </script>
    <!-- 개발용 스크립트 E -->
@stop
