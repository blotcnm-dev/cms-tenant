const navConfig = [
    { 
        name: "대시보드",
        lightIcon: "/src/assets/icons/gnb_dashboard.png",
        darkIcon: "/src/assets/icons/gnb_dashboard_dark.png",
        children: [
            { name: "대시보드", link: "/" },
        ]
    },
    { 
        name: "사이트 관리",
        lightIcon: "/src/assets/icons/gnb_site.png",
        darkIcon: "/src/assets/icons/gnb_site_dark.png",
        children: [
            { name: "메뉴 설정", link: "/html/siteManagement/menuManagement.html" },
            { name: "회원가입 관리", link: "/html/siteManagement/memberManagement.html" },
            { name: "사이트 설정", link: "/html/siteManagement/siteSetting.html" },
            { name: "배너 목록", link: "/html/siteManagement/bannerList.html" },
            { name: "팝업 목록", link: "/html/siteManagement/popupList.html" },
            { name: "약관 목록", link: "/html/siteManagement/termsList.html" },
        ]
    },
    { 
        name: "사용자 관리",
        lightIcon: "/src/assets/icons/gnb_user.png",
        darkIcon: "/src/assets/icons/gnb_user_dark.png",
        children: [
            { name: "사용자 목록", link: "/html/userManagement/userList.html" },
            { name: "등급 명칭 설정", link: "/html/userManagement/gradeNameSetting.html" },
            { name: "관리자 권한 설정", link: "/html/userManagement/adminGradeSetting.html" },
        ]
    },
    { 
        name: "게시판 관리",
        lightIcon: "/src/assets/icons/gnb_board.png",
        darkIcon: "/src/assets/icons/gnb_board_dark.png",
        children: [
            { name: "게시판 목록", link: "/html/boardManagement/boardList.html" },
        ]
    },
    { 
        name: "고객지원",
        lightIcon: "/src/assets/icons/gnb_support.png",
        darkIcon: "/src/assets/icons/gnb_support_dark.png",
        children: [
            { name: "자주 묻는 질문 설정", link: "/html/support/questionList.html" },
            { name: "1:1 문의 목록", link: "/html/support/inquiryList.html" },
        ]
    },
    { 
        name: "통계",
        lightIcon: "/src/assets/icons/gnb_stats.png",
        darkIcon: "/src/assets/icons/gnb_stats_dark.png",
        children: [
            { name: "신규 가입자 수", link: "/" },
            { name: "일일 방문자 수", link: "/" },
            { name: "월 방문자 수", link: "/" },
            { name: "게시글 좋아요 수", link: "/" },
            { name: "댓글 좋아요 수", link: "/" },
            { name: "인기 검색어 순위", link: "/" },
            { name: "접속 로그 기록", link: "/" },
        ]
    },
];

/**
 * 네비게이션 데이터를 반환하는 함수
 * @returns {Array} - 네비게이션 항목 배열
 */

export const navigationData = () => {
    if (!navConfig) {
        console.warn(`Unknown navigation elements!`);
        return [];
    }
    return navConfig;
};
