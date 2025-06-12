<meta charset="UTF-8" >
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">

@php
    // 기본 메타데이터
    $meta_title = $metaData['meta-title']['value'] ?? '';
    $meta_desc = $metaData['meta-desc']['value'] ?? '';
    $meta_keyword = $metaData['meta-keyword']['value'] ?? '';
    $meta_author = $metaData['meta-author']['value'] ?? '';
    $meta_home_name_kr = $metaData['meta-home-name-kr']['value'] ?? '';

    // 파비콘
    $favicon_path = !empty($metaData['meta-favicon']['value'])
        ? '/storage/site/' . $metaData['meta-favicon']['value']
        : '/storage/site/favicon_1748331085.png';

    // OG 전용 데이터 (있으면 사용, 없으면 기본값 사용)
    $og_title = $metaData['og-title']['value'] ?? $meta_title;
    $og_description = $metaData['og-description']['value'] ?? $meta_desc;
    $og_image = $metaData['og-description']['value'] ?? $favicon_path;
@endphp
<meta name="description" content="{{ $meta_desc }}">
<meta name="keywords" content="{{ $meta_keyword }}">
<meta name="author" content="{{ $meta_author }}">
<link rel="icon" href="{{ $favicon_path }}">

    <!-- Open Graph 태그 -->
<meta property="og:title" content="{{ $og_title }}">
<meta property="og:description" content="{{ $og_description }}">
<meta property="og:image" content="{{ $og_image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/png">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $meta_home_name_kr }}">


<!-- Twitter Card 태그 -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $og_title }}">
<meta name="twitter:description" content="{{ $og_description }}">
<meta name="twitter:image" content="{{ $og_image }}">

<!-- 추가 메타 태그들 -->
<meta name="robots" content="index, follow">
<meta name="googlebot" content="index, follow">
<link rel="canonical" href="{{ url()->current() }}">
