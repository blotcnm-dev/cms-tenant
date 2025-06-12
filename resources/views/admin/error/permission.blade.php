@extends('admin.layout.master')

@section('required-page-title', '권한 에러')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/termsList.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
<main>
    <div id="wrap">
        <!-- 컨텐츠 S -->
        <div class="container">

            <!-- 결과 조회 S -->
            <div class="search_result">
                <div class="result_list">
                    <div class="nodata">
                        <div>
                            <p>요청하신 페이지에 접근 권한이 없습니다.</p>
                        </div>
                    </div>
                    <div class="bottom_btn fixed">
                        <button type="button" onclick="window.history.back(-1)" class="fill_btn black">이전 페이지</button>
                        <button type="button" onclick="window.location.href = '/';" class="fill_btn black">홈으로</button>
                    </div>
                </div>
            </div>
            <!-- 결과 조회 E -->
        </div>
        <!-- 컨텐츠 E -->
    </div>

</main>
@stop

@section('required-page-add-content')
@stop
