@extends('web.layout.master')

@section('required-page-title', $boards->subject.'-'.$board_config->board_name)
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/board/detail.css">
@stop

@section('required-page-header-js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@stop

@section('required-page-banner-blade')
{{--    <div>페이지별 배너 </div>--}}
@stop


@section('required-page-main-content')
    <main>
        <section class="container_detail">
            <div class="container w-820">
                <!-- 카테고리 (네비게이션 역할) -->
                <nav class="category_container" aria-label="게시글 카테고리">
                    <span class="category_first">{{$boards->kname}}</span>
{{--                    <span class="category_second">두번째 카테고리</span>--}}
{{--                    <span class="category_third">세번째 카테고리</span>--}}
                </nav>

                <!-- 게시글 제목 -->
                <h2 class="title">
                    {{$boards->subject}}
                    {!! ( $boards->is_secret === 1 )  ? '<span class="marker">비밀글</span>':'' !!}
                </h2>

                <!-- 게시글 정보 -->
                <div class="information_container">
                    <div class="profile_container">
                        <img src="{{($boards->profile_image) ?? '/src/assets/images/no_profile.png'}}" alt="프로필 이미지">
                    </div>
                    <div class="information_content_container">
                        <div class="information_content_box">
                            <span class="name">{{($boards->user_name) ? decrypt($boards->user_name):'익명'}}</span>
                            <span class="degree">{{$boards->code_name}}</span>
                        </div>
                        <div class="information_content_box">
                            <time class="date" datetime="2025-03-13T10:58">{{$boards->created_at}}</time>
                            <span class="count">조회 <b>{{$boards->hits}}</b></span>
                        </div>
                    </div>
                </div>
                @if($board_config->board_type === 'COMMON' && $files->count() > 0)
                <!-- 첨부 파일 영역 -->
                <div class="file_attachment_container">
                    <button type="button" class="file_count">첨부 파일(<b>{{ $files->count() }}</b>)</button>
                    <div class="file_list_popup_container" style="display:none">
                        <button type="button" class="close_popup">닫기</button>
                        <ul>
                            @foreach($files as $file)
                                <li>{{ $file->fname }} <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}" download>내려받기</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                <!-- 본문 콘텐츠 -->
                <div class="content_container">
                    @if($board_config->board_type === 'GALLERY' && $files->count() > 0)
                        @foreach($files as $file)
                            <img src="{{ $file->path }}" alt="{{ $file->fname }}">
                        @endforeach
                    @endif
                    {!! renderContentAllowHtmlButEscapeScript($boards->content) !!}
                </div>

                <!-- 비밀글 설정 -->
{{--                <div class="secret_button_container">--}}
{{--                    <input type="checkbox" id="secret_check" name="secret">--}}
{{--                    <label for="secret_check">비밀글 설정</label>--}}
{{--                </div>--}}

                <!-- 게시글 제어 버튼들 -->
                <div class="controller_container">
{{--                    <a href="/board/edit.html" class="edit_button">글쓰기</a>--}}
                    @if($boards->member_id === ((session()->get('blot_mbid')) ?? 0))
                    <a href="{{ route('boards.edit', [$board_config->board_id,$boards->post_id]) }}" class="modify_button">수정</a>
                    @endif
                    @if($boards->member_id === ((session()->get('blot_mbid')) ?? 0))
                    <form id="listForm" action="{{ route('boards.destroy',[$board_config->board_id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="post_list[]" value="{{$boards->post_id}}">
                        <button type="button" id="deleteBtn" class="button_delete">삭제</button>
                    </form>
                    @endif
                    <a href="{{ route('boards.list', [$board_config->board_id]) }}" class="list_button">목록</a>
                </div>

                <div class="comment_state_container">
                    @if($board_config->is_like === 1 )
                    <span class="like_state">
                      <button type="button" class="icon {!!($boards->likes_id) ? "liked":"" !!}"  data-post-id="{{ $boards->post_id }}" data-like-type="post" aria-label="좋아요"><span {!!($boards->likes_id) ? "style='color:black'":"" !!}>♥</span></button> 좋아요 <span class="count">{{$boards->likes}}</span>
                    </span>
                    @endif
                    @if($board_config->is_reply === 1)
                    <span class="comment_state">
                        <i class="icon" aria-hidden="true">🗪</i> 댓글 <span class="count reply_cnt">0</span>
                    </span>
                    @endif
                </div>
                @if($board_config->is_reply === 1)
                    <!-- 댓글 섹션 -->
                    <div class="comment_container">
                        <!-- 댓글 입력 -->
                        @if($board_config->is_replay === 'Y')
                        <form id="replyForm" action="{{ route('boards.repliesstore', [$board_config->board_id,$boards->post_id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="reid" value="0">
                        <div class="comment_input_container">
                            <label for="comment_input" class="label-hidden">댓글 입력</label>
                            <input type="text" id="commentInput" rows="1" name="comment" value="{{ old('comment') }}" placeholder="댓글을 입력하세요" required>
                            <div id="comment_error" style="display:none; color:red; margin-top:.5rem;"></div>
                            <button type="button" class="comment_write_button save" id="replyBtn">작성</button>
                        </div>
                        </form>
                        @endif
                        <!-- 댓글 목록 -->
                        <ul id="comment_list" class="comment_list" role="list">

                        </ul>
                    </div>
                @endif
            </div>
        </section>
    </main>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('required-page-add-content')
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if($board_config->is_del === 'Y')
            const deleteBtn = document.getElementById('deleteBtn');
            const form      = document.getElementById('listForm');

            if (!deleteBtn) {
                return;
            }

            // 클릭 시 submit 호출
            deleteBtn.addEventListener('click', () => {
                if (!deleteBtn.disabled) {
                    if(confirm('정말 삭제 하시겠습니까?')) {
                        deleteBtn.disabled = true;
                        deleteBtn.textContent = '삭제중...';
                        form.submit();
                    }
                }
            });
            @endif

            @if($board_config->board_type === 'COMMON' && $files->count() > 0)
            const fileCount = document.querySelector('.file_count');
            const popupContainer = document.querySelector('.file_list_popup_container');
            const closeButton = document.querySelector('.file_list_popup_container button');

            if (!fileCount || !popupContainer || !closeButton) {
                return;
            }

            // 파일 개수 클릭시 팝업 토글
            fileCount.addEventListener('click', function(e) {
                e.stopPropagation();

                if (popupContainer.style.display === 'block') {
                    popupContainer.style.display = 'none';
                } else {
                    popupContainer.style.display = 'block';
                }
            });

            // 닫기 버튼 클릭시 팝업 닫기
            closeButton.addEventListener('click', function(e) {
                e.stopPropagation();
                popupContainer.style.display = 'none';
            });

            // 팝업 외부 클릭시 닫기
            document.addEventListener('click', function(e) {
                if (!popupContainer.contains(e.target) && !fileCount.contains(e.target)) {
                    popupContainer.style.display = 'none';
                }
            });

            // 팝업 내부 클릭시 닫히지 않도록
            popupContainer.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            @endif

        });

        function handleLikeClick(button) {
            const postId = button.getAttribute('data-post-id');
            const likeType = button.getAttribute('data-like-type') || 'post';
            var countSpan = button.querySelector('i');

            {{(session()->get('blot_mbid')) ?? 'return;'}}

            if (!postId) {
                console.error('post_id가 없습니다.');
                return;
            }

            if (!countSpan) {
                countSpan = document.querySelector('.like_state .count');
                // console.error('카운트 요소를 찾을 수 없습니다.');
                // return;
            }

            // CSRF 토큰 확인
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                console.error('CSRF 토큰이 없습니다.');
                return;
            }

            const token = csrfMeta.getAttribute('content');

            // 현재 카운트와 좋아요 상태
            let currentCount = parseInt(countSpan.textContent);
            let isLiked = button.classList.contains('liked');

            // 상태 토글
            isLiked = !isLiked;

            // 버튼 색상 변경
            const heartSpan = button.querySelector('span');
            if (isLiked) {
                heartSpan.style.color = 'black';
                button.classList.add('liked');
                countSpan.textContent = currentCount + 1;
            } else {
                heartSpan.style.color = '#e8e8e8';
                button.classList.remove('liked');
                countSpan.textContent = currentCount - 1;
            }

            // AJAX 호출
            fetch('{{route('boards.likes', [$board_config->board_id])}}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    post_id: postId,
                    fild_id: 'likes',
                    fild_val: isLiked ? '+' : '-',
                    like_type: likeType
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // 실패시 원래 상태로 복원
                    isLiked = !isLiked;
                    if (isLiked) {
                        heartSpan.style.color = 'black';
                        button.classList.add('liked');
                        countSpan.textContent = currentCount + 1;
                    } else {
                        heartSpan.style.color = '#e8e8e8';
                        button.classList.remove('liked');
                        countSpan.textContent = currentCount - 1;
                    }
                });
        }

        // 모든 좋아요 버튼에 이벤트 리스너 추가
        document.addEventListener('DOMContentLoaded', function() {
            // 이벤트 위임으로 모든 좋아요 버튼 처리 (정적/동적 모두 포함)
            document.addEventListener('click', function(e) {
                if (e.target.closest('button[data-like-type]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const button = e.target.closest('button[data-like-type]');
                    handleLikeClick(button);
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

                const url = `/${boardId}/reply/${commentId}`;

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
                url: '{{ route('boards.replieslist_ajax', [$board_config->board_id, $boards->post_id]) }}',
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
            var reply_txt = `
    <li class="comment_item" role="listitem">
	<div class="comment_box">
	    <div class="comment">
		<div class="user_information_container">
		    <span class="name">${userName}</span>
		    <time class="date" datetime="${comment.created_at}">${comment.created_at}</time>
		</div>
		<div class="text">
		    ${comment.content.replace(/\r\n|\n|\r/g, '<br>')}
		</div>
		@if($board_config->is_replay === 'Y')
		<button type="button" class="re_comment_button reply_btn" data-comment-id="${comment.id}">답글</button>
		@endif
	    </div>
	    <div class="state_container">
		`;

        @if($board_config->is_reply_like === 1 )
        if (comment.likes_id == 0) {
            reply_txt += `<button type="button" data-post-id="${comment.id}" data-like-type="reply" aria-label="좋아요"><span style="color:gray">♥</span>`;
        } else {
            reply_txt += `<button type="button" class="liked" data-post-id="${comment.id}" data-like-type="reply" aria-label="좋아요"><span style="color:black">♥</span>`;
        }
        reply_txt += `<i>${comment.likes}</i></button>`;
        @endif

        if({{(session()->get('blot_mbid')) ?? 0}} == comment.member_id) {
            reply_txt += `<button type="button" class="reply_del_btn" data-comment-id="${comment.id}">삭제</button>`;
        }
         reply_txt += `</div>
	</div>

 	<!-- 대댓글 목록 -->
	<ul class="re_comment_list" role="list">
	        <!-- 대댓글 목록이 있으면 여기에 추가 -->
		 ${comment.rereplies ? createRepliesHTML(comment.rereplies) : ''}
	    <!-- 대댓글 입력 (리스트 항목으로 감싸기) -->

        <form class="replyForm" action="{{ route('boards.repliesstore', [$board_config->board_id,$boards->post_id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
                <input type="hidden" name="reid" value="${comment.id}">
	    <li class="re_comment_item" role="listitem" id="reply_form_${comment.id}" style="display:none;">
		<div class="comment_input_container re_comment_input_container btn_flex">
		    <label for="re_comment_input" class="label-hidden">답글 입력</label>
		    <input type="text" name="comment" class="re_comment" placeholder="답글을 입력하세요" onkeydown="if(event.key==='Enter') return false;">
		    <button type="button" class="comment_write_button save rerebtn">작성</button>
		</div>
	    </li>
		</form>

	</ul>
    </li>
    `;
                return reply_txt;
        }

        // 대댓글 HTML을 생성하는 함수
        function createRepliesHTML(replies) {

            let repliesHtml = '';

            replies.forEach(reply => {
                const userName = reply.user_name && reply.user_name.trim() !== ''
                    ? reply.user_name
                    : '익명';
                repliesHtml += `
	    <li class="re_comment_item" role="listitem">
		<div class="comment">
		    <div class="user_information_container">
			<span class="name">${userName}</span>
			<time class="date" datetime="${reply.created_at}">${reply.created_at}</time>
		    </div>
		    <div class="text">
			${reply.content.replace(/\r\n|\n|\r/g, '<br>')}
		    </div>
		</div>
		<div class="state_container">
		    `;

            @if($board_config->is_reply_like === 1 )
            if (reply.likes_id == 0) {
                repliesHtml += `<button type="button" data-post-id="${reply.id}" data-like-type="reply" aria-label="좋아요"><span style="color:gray">♥</span>`;
            } else {
                repliesHtml += `<button type="button" class="liked" data-post-id="${reply.id}" data-like-type="reply" aria-label="좋아요"><span style="color:black">♥</span>`;
            }
            repliesHtml += `<i>${reply.likes}</i></button>`;
            @endif

           if({{(session()->get('blot_mbid')) ?? 0}} == reply.member_id) {
               repliesHtml += `<button type="button" class="reply_del_btn" data-comment-id="${reply.id}">삭제</button>`;
           }
                repliesHtml += `</div>
	    </li>
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

            // const likeButtons = document.querySelectorAll('button[data-like-type]');
            // likeButtons.forEach(function(button) {
            //     button.addEventListener('click', function() {
            //         handleLikeClick(this);
            //     });
            // });
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
@stop
