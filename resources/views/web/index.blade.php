@extends('web.layout.master')

@section('required-page-title', '인덱스')
@section('required-page-header-css')
@stop

@section('required-page-header-js')
@stop

@section('required-page-banner-blade')
    <div>페이지별 배너 </div>
@stop


@section('required-page-main-content')
    <main>
        <!-- 기본 배너 표시 -->
        @if(isset($globalBanners['banner_1']) && count($globalBanners['banner_1']) > 0)
            @include('web.layout.banner.main_1', ['banners' => $globalBanners['banner_1']])
        @endif


    </main>
@stop

@section('required-page-add-content')
    @include('web.layout.banner.popup')
@stop
