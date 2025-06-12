<!DOCTYPE html>
<html lang="ko">
<head>
    <title>@yield('required-page-title')</title>
    <!-- meta -->
    @include('web.layout.headerMeta')
    <!-- javascript -->
    @include('web.layout.headerJs')
    <!-- javascript per page -->
    @yield('required-page-header-js')
    <!-- css -->
    @include('web.layout.headerCss')
    <!-- css per page -->
    @yield('required-page-header-css')
    @php

        $gtm_head = $scriptData['gtm-head']['value'] ?? '';
        $gtm_body = $scriptData['gtm-body']['value'] ?? '';
        $gta_head = $scriptData['gta-head']['value'] ?? '';
    @endphp
@if(app()->environment('production'))
        <!-- Google Tag Manager -->
        {!! $gtm_head !!}
{{--        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':--}}
{{--                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],--}}
{{--                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=--}}
{{--                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);--}}
{{--            })(window,document,'script','dataLayer','GTM-MF79BDSD');</script>--}}
        <!-- End Google Tag Manager -->

        <!-- Google tag (gtag.js) -->
        {!! $gta_head !!}
{{--        <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZPXKPNDMNG"></script>--}}
{{--        <script>--}}
{{--            window.dataLayer = window.dataLayer || [];--}}
{{--            function gtag(){dataLayer.push(arguments);}--}}
{{--            gtag('js', new Date());--}}

{{--            gtag('config', 'G-ZPXKPNDMNG');--}}
{{--        </script>--}}
    @endif
</head>
<body>
    @if(app()->environment('production'))
        <!-- Google Tag Manager (noscript) -->
        {!! $gta_body !!}
{{--        <noscript>--}}
{{--            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MF79BDSD"--}}
{{--                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>--}}
        <!-- End Google Tag Manager (noscript) -->
    @endif
<!-- header -->
@include('web.layout.header')
<!-- banner per page -->
@yield('required-page-banner-blade')
<!-- 처리결과 S -->
@if(session('success'))
    <script>
        alert('layout master blade messgage ::::: {{ session('success') }}');
    </script>
    {{--                <div class="alert alert-success">--}}
    {{--                    {{ session('success') }}--}}
    {{--                </div>--}}
@endif
<!-- 처리결과 E -->

<!-- 내용 -->
@yield('required-page-main-content')
<!-- 페이지별 추가 컨텐츠  -->
@yield('required-page-add-content')

<!-- header -->
@include('web.layout.footer')
</body>
</html>
