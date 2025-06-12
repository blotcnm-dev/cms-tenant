@extends('web.layout.master')

@section('required-page-title', '1:1문의 상세보기')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/inquiry/detail.css">
@stop

@section('required-page-header-js')

@stop

@section('required-page-banner-blade')
{{--    <div>페이지별 배너 </div>--}}
@stop


@section('required-page-main-content')
    <main>
        <section class="container_detail">
            <div class="container w-820">
                <!-- 카테고리 (네비게이션 역할) -->
                <nav class="category_container" aria-label="1:1문의 분류">
                    <span class="category_first">{{ $boards->kname }}</span>
                </nav>

                <!-- 게시글 제목 -->
                <h2 class="title">
                    {{ $boards->subject }}
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

                <!-- 본문 컨텐츠 -->
                <div class="inquiry_content_wrap">
                    <div class="inquiry_content_box">
                        <h3>문의</h3>
                        <!-- 첨부 파일 영역 -->
                        @if($post_files->count() > 0)
                        <div class="file_attachment_container">
                            <button type="button" class="file_count" id="filecnt1">첨부 파일(<b>{{ $post_files->count() }}</b>)</button>
                            <div class="file_list_popup_container" id="filepop1" style="display:none">
                                <button type="button">닫기</button>
                                <ul>
                                    @foreach($post_files as $file)
                                        <li>{{$file->fname}} <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}" download>내려받기</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                        <!-- 질문 영역 -->
                        <div class="content">
                            {!! renderContentAllowHtmlButEscapeScript($boards->content) !!}
                        </div>
                    </div>
                    @if($boards->reply_status === 'COMPLETE')
                    <div class="inquiry_content_box">
                        <h3>답변</h3>
                        <!-- 첨부 파일 영역 -->
                        @if($reply_files->count() > 0)
                        <div class="file_attachment_container">
                            <button type="button" class="file_count" id="filecnt2">첨부 파일(<b>{{ $reply_files->count() }}</b>)</button>
                            <div class="file_list_popup_container" id="filepop2" style="display:none">
                                <button type="button">닫기</button>
                                <ul>
                                    @foreach($reply_files as $file)
                                        <li>{{$file->fname}} <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}" download>내려받기</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                        <!-- 질문 영역 -->
                        <div class="content">
                            {!! renderContentAllowHtmlButEscapeScript($boards->recontent) !!}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- 게시글 제어 버튼들 -->
                <div class="controller_container">
{{--                    <a href="/dev/inquiry/edit.html" class="modify_button">수정</a>--}}
{{--                    <button type="button" class="button_delete">삭제</button>--}}
                    <a href="{{ route('front_inquiry.index') }}" class="list_button">목록</a>
                </div>
            </div>
        </section>
    </main>
@stop

@section('required-page-add-content')
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            @if($post_files->count() > 0)
            const fileCount = document.querySelector('#filecnt1');
            const popupContainer = document.querySelector('#filepop1');
            const closeButton = document.querySelector('#filepop1 button');

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
            @if($reply_files->count() > 0)
            const fileCount2 = document.querySelector('#filecnt2');
            const popupContainer2 = document.querySelector('#filepop2');
            const closeButton2 = document.querySelector('#filepop2 button');

            if (!fileCount2 || !popupContainer2 || !closeButton2) {
                return;
            }

            // 파일 개수 클릭시 팝업 토글
            fileCount2.addEventListener('click', function(e) {
                e.stopPropagation();

                if (popupContainer2.style.display === 'block') {
                    popupContainer2.style.display = 'none';
                } else {
                    popupContainer2.style.display = 'block';
                }
            });

            // 닫기 버튼 클릭시 팝업 닫기
            closeButton2.addEventListener('click', function(e) {
                e.stopPropagation();
                popupContainer2.style.display = 'none';
            });

            // 팝업 외부 클릭시 닫기
            document.addEventListener('click', function(e) {
                if (!popupContainer2.contains(e.target) && !fileCount2.contains(e.target)) {
                    popupContainer2.style.display = 'none';
                }
            });

            // 팝업 내부 클릭시 닫히지 않도록
            popupContainer2.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            @endif
        });
    </script>
@stop
