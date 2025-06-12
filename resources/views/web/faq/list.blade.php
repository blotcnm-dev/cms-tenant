@extends('web.layout.master')

@section('required-page-title', '자주 묻는 질문')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/questions/list.css">
@stop

@section('required-page-header-js')
    <script type="module" src="/web/js/questions/list.js" defer></script>
@stop

@section('required-page-banner-blade')
{{--    <div>페이지별 배너 </div>--}}
@stop


@section('required-page-main-content')
    <main>
        <section class="container_list">
            <div class="container w-820">
                <h2>자주 묻는 질문 목록</h2>
                <div class="list_type_container">
                    <span>총 <b>{{ $boards->total() }}</b> 건의 글이 있습니다.</span>
                    <form action="{{ route('front_faq.index') }}" method="GET" id="searchForm">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'created_at__desc') }}" >
                        <select name="category" id="categorySelect">
                            <option value="">전체</option>
                            @foreach($category_sub as $config)
                                <option value="{{ $config->depth_code }}" {{ (request('category') === $config->depth_code) ? 'selected': '' }}>{{ $config->kname }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="list_header">
                    <span class="type">분류</span>
                    <span class="title">제목</span>
                </div>
                @if($boards->total() < 1 )
                    <div class="list_header">
                      <span class="title">등록된 정보가 없습니다.</span>
                    </div>
                @else
                <ul role="list" class="questions_list">
                    @php
                        $startNumber = $boards->total() - ($boards->perPage() * ($boards->currentPage() - 1));
                    @endphp

                    @foreach($boards as $index => $item)
                    <li role="listitem">
                        <button type="button" class="acc_btn">
                            <span class="type">{{$item->kname}}</span>
                            <span class="title">{{$item->subject}}</span>
                            <span class="select_box_button">
                            <svg aria-hidden="true" fill="currentColor" focusable="false" height="24" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" width="24"><path clip-rule="evenodd" d="M4.29289 7.79289C4.68342 7.40237 5.31658 7.40237 5.70711 7.79289L12 14.0858L18.2929 7.79289C18.6834 7.40237 19.3166 7.40237 19.7071 7.79289C20.0976 8.18342 20.0976 8.81658 19.7071 9.20711L12.7071 16.2071C12.3166 16.5976 11.6834 16.5976 11.2929 16.2071L4.29289 9.20711C3.90237 8.81658 3.90237 8.18342 4.29289 7.79289Z" fill-rule="evenodd"></path></svg>
                        </span>
                        </button>
                        <div class="acc_content">
                            {!! renderContentAllowHtmlButEscapeScript($item->content) !!}

                            <br><br>
                            @foreach($item->files as $file)
                                <div class="txt_box">
                                    <div class="info_box">
                                        <span class="name">{{$file->fname}} ({{$file->fsize}}MB)</span>
                                        <a href="{{ route('file.download', ['path' => $file->path, 'filename' => $file->fname]) }}">다운로드</a>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </li>
                        @endforeach
                </ul>
                    <!-- 페이지네이션 S -->
                    {{ $boards->links('vendor.pagination.front') }}
                    <!-- 페이지네이션 E -->
                @endif
            </div>
        </section>
    </main>
@stop

@section('required-page-add-content')
    <!-- 개발용 스크립트 S -->
    <script>
        document.querySelector('#categorySelect')?.addEventListener('change', () => {
            document.querySelector('#searchForm')?.submit();
        });
    </script>
@stop
