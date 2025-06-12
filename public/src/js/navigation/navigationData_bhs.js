const navConfig = [
    {
        name: "대시보드",
        children: [
            { name: "대시보드", link: "/" },
        ]
    },
    {
        name: "사이트 관리",
        children: [
            { name: "메뉴 설정", link: "/master/menus" },
            { name: "회원가입 관리", link: "/master/user" },
            { name: "사이트 설정", link: "/master/site" },
            { name: "배너 목록", link: "/master/banner" },
            { name: "팝업 목록", link: "/master/popup" },
            { name: "약관 목록", link: "/master/policy" },
        ]
    },
    {
        name: "사용자 관리",
        children: [
            { name: "사용자 목록", link: "/master/member" },
            { name: "등급 명칭 설정", link: "/master/grade" },
            { name: "관리자 권한 설정", link: "/master/auth" },
        ]
    },
    {
        name: "게시판 관리",
        children: [
            { name: "게시판 목록", link: "/master/boards/configBoards" },
        ]
    },
    {
        name: "고객지원",
        children: [
            { name: "자주 묻는 질문 설정", link: "/master/faq" },
            { name: "1:1 문의 목록", link: "/master/inquiry" },
        ]
    },
    {
        name: "통계",
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
