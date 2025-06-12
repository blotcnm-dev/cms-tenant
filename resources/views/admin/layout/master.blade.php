<!DOCTYPE html>
<html lang="ko">
<head>
    <title>@yield('required-page-title')</title>
    <!-- meta -->
    @include('admin.layout.headerMeta')
    <!-- javascript -->
    @include('admin.layout.headerJs')
    <!-- javascript per page -->
    @yield('required-page-header-js')
    <!-- css -->
    @include('admin.layout.headerCss')
    <!-- css per page -->
    @yield('required-page-header-css')
</head>
<body>
    <!-- header -->
    @include('admin.layout.header')

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

</body>
</html>
