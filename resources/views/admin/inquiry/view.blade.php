@extends('admin.layout.master')

@section('required-page-title', '1:1 문의 답변')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/postView.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="#" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">1:1 문의 답변</h2>
            </div>
            <!-- 페이지 타이틀 E  -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form class="max_width">
                <!-- 게시물 상단 S -->
                <div class="post_top">
                    <ul role="list">
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">분류</span>
                                <div class="info">{{ $boards->category_tmp }}</div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">등록 일자</span>
                                <div class="info">{{ format_date( $boards->created_at ) }}</div>
                            </div>
                        </li>
                        <li role="listitem" class="full">
                            <div class="inner_box">
                                <span class="title">제목</span>
                                <div class="info">{{ $boards->subject }}</div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">이메일</span>
                                <div class="info">{{ safe_decrypt($boards->email) }}</div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">첨부파일</span>
                                <div class="info file">
                                    @foreach($post_files as $file)
                                        <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}">{{$file->fname}}</a>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        <li role="listitem" class="full auto_height">
                            <div class="inner_box">
                                <span class="title">문의</span>
                                <div class="info block">
                                    {!! renderContentAllowHtmlButEscapeScript($boards->content) !!}
                                </div>
                            </div>
                        </li>
                        <li role="listitem" class="full auto_height">
                            <div class="inner_box">
                                <span class="title">답변</span>
                                <div class="info block">
                                    {!! renderContentAllowHtmlButEscapeScript($boards->recontent) !!}
                                </div>
                            </div>
                        </li>
                        <li role="listitem" class="full">
                            <div class="inner_box">
                                <span class="title">첨부파일</span>
                                <div class="info file">
                                    @foreach($reply_files as $file)
                                        <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}">{{$file->fname}}</a>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">회신 일자</span>
                                <div class="info">{{ format_date( $boards->updated_at ) }}</div>
                            </div>
                        </li>
                        <li role="listitem">
                            <div class="inner_box">
                                <span class="title">회신 담당자</span>
                                <div class="info">{{ $boards->writer_name }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- 게시물 상단 E -->

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn fixed">
                        <a href="{{ route('inquiry.index') }}" class="border_btn cancel">
                            <span>확인</span>
                        </a>
                    </div>
                    <!-- 하단 버튼 E -->
                </form>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('required-page-add-content')
    <script type="module">

    </script>
    <!-- 개발용 스크립트 S -->
    <script>

    </script>
    <!-- 개발용 스크립트 E -->
@stop
