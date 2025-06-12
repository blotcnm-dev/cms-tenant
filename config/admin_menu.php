<?php
return [
    'menus' => array_filter([
        [
            'code' => '1000',
            'name' => '대시보드',
            'icon' => 'gnb_dashboard.png',
            'icon_dark' => 'gnb_dashboard_dark.png',
            'children' => [
                [
                    'code' => '1100',
                    'name' => '대시보드',
                    'link' => '/master',
                    'route_name' => 'admin.dashboard'
                ],
            ]
        ],
        [
            'code' => '2000',
            'name' => '사이트 관리',
            'icon' => 'gnb_site.png',
            'icon_dark' => 'gnb_site_dark.png',
            'children' => [
                [
                    'code' => '2100',
                    'name' => '메뉴 설정',
                    'link' => '/master/menus',
                    'route_name' => 'menus.index'
                ],
                [
                    'code' => '2200',
                    'name' => '회원가입 관리',
                    'link' => '/master/user',
                    'route_name' => 'user.index'
                ],
                [
                    'code' => '2300',
                    'name' => '사이트 설정',
                    'link' => '/master/site',
                    'route_name' => 'site.index'
                ],
                [
                    'code' => '2400',
                    'name' => '배너 목록',
                    'link' => '/master/banner',
                    'route_name' => 'banner.index'
                ],
                [
                    'code' => '2500',
                    'name' => '팝업 목록',
                    'link' => '/master/popup',
                    'route_name' => 'popup.index'
                ],
                [
                    'code' => '2600',
                    'name' => '약관 목록',
                    'link' => '/master/policy',
                    'route_name' => 'policy.index'
                ],
            ]
        ],
        [
            'code' => '3000',
            'name' => '사용자 관리',
            'icon' => 'gnb_user.png',
            'icon_dark' => 'gnb_user_dark.png',
            'children' => [
                [
                    'code' => '3100',
                    'name' => '사용자 목록',
                    'link' => '/master/member',
                    'route_name' => 'user_list'
                ],
                [
                    'code' => '3200',
                    'name' => '등급 명칭 설정',
                    'link' => '/master/grade',
                    'route_name' => 'grade_setting'
                ],
                [
                    'code' => '3300',
                    'name' => '관리자 권한 설정',
                    'link' => '/master/auth',
                    'route_name' => 'admin_auth'
                ],
            ]
        ],
        [
            'code' => '4000',
            'name' => '게시판 관리',
            'icon' => 'gnb_board.png',
            'icon_dark' => 'gnb_board_dark.png',
            'children' => [
                [
                    'code' => '4100',
                    'name' => '게시판 목록',
                    'link' => '/master/configBoards',
                    'route_name' => 'board_list'
                ],
                [
                    'code' => '4200',
                    'name' => '게시글관리',
                    'link' => '/master/board',
                    'route_name' => 'board_list',
                    'hidden' =>'Y'
                ],
            ]
        ],
        [
            'code' => '5000',
            'name' => '고객지원',
            'icon' => 'gnb_support.png',
            'icon_dark' => 'gnb_support_dark.png',
            'children' => [
                [
                    'code' => '5100',
                    'name' => '자주 묻는 질문 설정',
                    'link' => '/master/faq',
                    'route_name' => 'faq'
                ],
                [
                    'code' => '5200',
                    'name' => '1:1 문의 목록',
                    'link' => '/master/inquiry',
                    'route_name' => 'inquiry'
                ],
            ]
        ],
        [
            'code' => '6000',
            'name' => '통계',
            'icon' => 'gnb_stats.png',
            'icon_dark' => 'gnb_stats_dark.png',
            'children' => [
//                [
//                    'code' => '6100',
//                    'name' => '신규 가입자 수',
//                    'link' => '/',
//                    'route_name' => 'stats_newuser'
//                ],
//                [
//                    'code' => '6200',
//                    'name' => '일일 방문자 수',
//                    'link' => '/',
//                    'route_name' => 'stats_daily'
//                ],
//                [
//                    'code' => '6300',
//                    'name' => '월 방문자 수',
//                    'link' => '/',
//                    'route_name' => 'stats_monthly'
//                ],
//                [
//                    'code' => '6400',
//                    'name' => '게시글 좋아요 수',
//                    'link' => '/',
//                    'route_name' => 'stats_post_likes'
//                ],
//                [
//                    'code' => '6500',
//                    'name' => '댓글 좋아요 수',
//                    'link' => '/',
//                    'route_name' => 'stats_comment_likes'
//                ],
//                [
//                    'code' => '6600',
//                    'name' => '인기 검색어 순위',
//                    'link' => '/',
//                    'route_name' => 'stats_search_terms'
//                ],
//                [
//                    'code' => '6700',
//                    'name' => '접속 로그 기록',
//                    'link' => '/',
//                    'route_name' => 'stats_access_log'
//                ],
            ],
        ]
    ])
];
