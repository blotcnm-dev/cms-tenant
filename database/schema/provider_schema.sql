/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `bl_admin_user_auths` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_grade_id` int(10) unsigned NOT NULL,
  `menu_code` varchar(50) NOT NULL,
  `menu_path` varchar(255) NOT NULL,
  `permission_read` tinyint(4) DEFAULT 0,
  `permission_write` tinyint(4) DEFAULT 0,
  `permission_delete` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `member_grade_id` (`member_grade_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `bl_board_category` (
  `board_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시판 카테고리',
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '부모 ID',
  `depth_code` varchar(10) NOT NULL DEFAULT '000000' COMMENT '고유번호	2자리씩 3Depth',
  `kname` varchar(50) NOT NULL COMMENT '국문',
  `ename` varchar(50) DEFAULT NULL COMMENT '영문',
  `depth` char(1) NOT NULL DEFAULT '1' COMMENT '1: 1Depth, 2: 2Depth, 3: 3Depth',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '정렬',
  `is_view` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT '노출여부',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  PRIMARY KEY (`board_category_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판카테고리';

CREATE TABLE IF NOT EXISTS `bl_board_configs` (
  `board_config_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시판 설정 ',
  `admin_id` int(10) unsigned NOT NULL COMMENT '관리자 아이디',
  `board_name` varchar(100) NOT NULL COMMENT '게시판 이름',
  `board_id` varchar(100) NOT NULL COMMENT '게시판 아이디',
  `is_active` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '사용여부 ( 0 : 미사용, 1 : 사용 )',
  `board_type` varchar(30) NOT NULL DEFAULT 'COMMON' COMMENT '게시판 유형 ( COMMON : 일반, GALLERY : 갤러리, INQUIRY : 문의)',
  `writer_display_type` varchar(15) NOT NULL DEFAULT 'CLOSED' COMMENT '작성자표기 ( CLOSED : 비공개, USER_ID : 아이디, USER_NAME : 이름, NICK_NAME : 닉네임 )',
  `is_display_writer` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '작성자노출 ( 0 : 미노출, 1 : 노출 )',
  `is_secret` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '비밀글 기능 ( 0 : 미사용, 1 : 사용 )',
  `is_auto_secret` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '자동 비밀글 ( 0 : 미사용, 1 : 사용 )',
  `is_display_hits` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '조회수 노출 ( 0 : 미노출, 1 : 노출 )',
  `is_admin_notification` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '게시글 작성 관리자 알림 ( 0 : 미사용, 1 : 사용 )',
  `is_new_notification` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '신규 게시글 알림 ( 0 : 미사용, 1 : 사용 )',
  `new_notification_duration` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '신규 게시글 알림 시간',
  `list_num` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '한 페이지에 출력될 게시물 수',
  `is_captcha` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '자등등록방지 ( 0 : 미사용, 1 : 사용 )',
  `is_prevent_abuse` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '게시글 연속 등록 방지 ( 0 : 미사용, 1 : 사용 )',
  `abuse_duration` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '게시글 연속 등록 방지 기간',
  `abuse_count` tinyint(3) unsigned NOT NULL DEFAULT 2 COMMENT '게시글 연속 등록 방지 게시물 수',
  `abuse_block_duration` tinyint(3) unsigned NOT NULL DEFAULT 5 COMMENT '게시글 연속 등록 방지 등록 불가 기간',
  `is_reply` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '댓글 사용 ( 0 : 미사용, 1 : 사용 )',
  `is_secret_reply` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '비밀 댓글 ( 0 : 미사용, 1 : 사용 )',
  `is_auto_secret_reply` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '자동 비밀 댓글 ( 0 : 미사용, 1 : 사용 )',
  `is_reply_captcha` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '댓글 자동등록방지 ( 0 : 미사용, 1 : 사용 )',
  `is_reply_like` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '댓글좋아요 ( 0 : 미사용, 1 : 사용 )',
  `is_like` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '게시글좋아요 ( 0 : 미사용, 1 : 사용 )',
  `is_reply_photo` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '댓글사진첨부 ( 0 : 미사용, 1 : 사용 )',
  `is_category` varchar(10) NOT NULL DEFAULT '0' COMMENT '게시판 분류 사용 ( 0 : 미사용, 사용시카테고리 depth_code값 )',
  `is_inquiry_type` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '문의 유형 사용 ( 0 : 미사용, 1 : 사용 )',
  `list_view_authority_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '읽기권한 ( 0 : 전체 , 1~10 )',
  `content_view_authority_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '등록수정 권한 ( 0 : 전체 , 1~10 )',
  `content_write_authority_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '삭제 권한 ( 0 : 전체 , 1~10 )',
  `reply_write_authority_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '댓글 권한 ( 0 : 전체 , 1~10 )',
  `ban_words` varchar(255) DEFAULT NULL COMMENT '금지어',
  `is_ban` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '금칙어 사용여부 ( 0 : 미사용, 1 : 사용 )',
  `is_topfix` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '최상단고정 ( 0 : 미사용, 1 : 사용 )',
  `is_file` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '첨부파일 사용여부 ( 0 : 미사용, 1 : 사용 )',
  `file_uploadable_count` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '등록 가능 첨부파일 수',
  `file_max_size` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '파일업로드 사이즈',
  `is_editor` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '에디터 사용여부 ( 0 : 미사용, 1 : 사용 )',
  `gallery_uploadable_count` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '등록 가능 첨부파일 수',
  `gallery_max_size` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '파일업로드 사이즈',
  `gallery_theme` varchar(30) NOT NULL DEFAULT 'list' COMMENT '겔러리테마  list, grid',
  `is_re_mail_send` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '답변메일 발송 사용여부 ( 0 : 미사용, 1 : 사용 )',
  `incoming_mail` varchar(100) DEFAULT NULL COMMENT '답변메일 ; 구분',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '삭제여부 ( 0 : 미삭제, 1 : 삭제 )',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`board_config_id`),
  KEY `created_at` (`created_at`),
  KEY `admin_id` (`admin_id`),
  KEY `index_board_id` (`board_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='게시판 설정';

CREATE TABLE IF NOT EXISTS `bl_config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '일련번호',
  `code_group` varchar(30) DEFAULT NULL COMMENT '코드그룹 site, footer, snstype, member',
  `code_type` varchar(50) NOT NULL DEFAULT '' COMMENT '코드분류',
  `code` varchar(50) DEFAULT NULL COMMENT '코드',
  `sub_code` varchar(15) DEFAULT NULL COMMENT '서브코드_필요시사용',
  `code_name` varchar(50) DEFAULT NULL COMMENT '코드명',
  `code_name_en` varchar(50) DEFAULT NULL COMMENT '코드명_영문',
  `use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용여부(1:사용, 0:미사용)',
  `sort` smallint(5) unsigned NOT NULL DEFAULT 1 COMMENT '정렬순서',
  `sub_sort` smallint(5) unsigned DEFAULT NULL COMMENT '서브 정렬순서',
  `code_info1` varchar(255) DEFAULT NULL COMMENT '정보',
  `code_info2` varchar(255) DEFAULT NULL COMMENT '정보',
  `code_info3` varchar(255) DEFAULT NULL COMMENT '정보',
  `code_info4` varchar(255) DEFAULT NULL COMMENT '정보',
  `value` longtext DEFAULT NULL COMMENT 'JSON데이터',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`config_id`) USING BTREE,
  KEY `idx_code_group` (`code_group`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='사이트설정';

CREATE TABLE IF NOT EXISTS `bl_faq_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '파일 아이디',
  `post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '게시글 아이디',
  `post_type` varchar(30) NOT NULL DEFAULT 'POSTS' COMMENT 'POSTS, REPLIES, ETC',
  `ftype` varchar(30) NOT NULL DEFAULT '' COMMENT '파일타입',
  `fsize` varchar(50) NOT NULL DEFAULT '' COMMENT '파일사이즈',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '원본 파일 경로',
  `fname` varchar(255) NOT NULL DEFAULT '' COMMENT '파일명',
  `thumbnail` varchar(255) NOT NULL DEFAULT '' COMMENT '썸네일',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  PRIMARY KEY (`id`),
  KEY `index_post_id_ftype` (`post_id`,`ftype`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='FAQ 게시판 첨부파일';

CREATE TABLE IF NOT EXISTS `bl_faq_posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시글 아이디',
  `is_display` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '게시글 노출 여부',
  `category` varchar(30) NOT NULL DEFAULT '' COMMENT '게시글 카테고리',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '게시글 제목',
  `content` text NOT NULL COMMENT '게시글 내용',
  `hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '조회수',
  `admin_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '관리자 시퀀스',
  `member_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '작성자 시퀀스',
  `writer_name` varchar(30) NOT NULL DEFAULT '' COMMENT '작성자 이름',
  `writer_ip` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '작성자 아이피',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`post_id`),
  KEY `index_created_at` (`created_at`),
  KEY `index_subject` (`subject`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='FAQ 게시판';

CREATE TABLE IF NOT EXISTS `bl_inquiry_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '파일 아이디',
  `post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '게시글 아이디',
  `post_type` varchar(30) NOT NULL DEFAULT 'POSTS' COMMENT 'POSTS, REPLIES, ETC',
  `ftype` varchar(30) NOT NULL DEFAULT '' COMMENT '파일타입',
  `fsize` varchar(50) NOT NULL DEFAULT '' COMMENT '파일사이즈',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '원본 파일 경로',
  `fname` varchar(255) NOT NULL DEFAULT '' COMMENT '파일명',
  `thumbnail` varchar(255) NOT NULL DEFAULT '' COMMENT '썸네일',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  PRIMARY KEY (`id`),
  KEY `index_post_id_ftype` (`post_id`,`ftype`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='1:1문의 게시판 첨부파일';

CREATE TABLE IF NOT EXISTS `bl_inquiry_posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시글 아이디',
  `is_display` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '게시글 노출 여부',
  `category` varchar(30) NOT NULL DEFAULT '' COMMENT '게시글 카테고리',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '게시글 제목',
  `content` text NOT NULL COMMENT '게시글 내용',
  `recontent` text DEFAULT NULL COMMENT '답변',
  `hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '조회수',
  `is_secret` tinyint(4) NOT NULL DEFAULT 0 COMMENT '비밀글 여부',
  `secret_password` varchar(255) NOT NULL DEFAULT '' COMMENT '비밀글 비밀번호',
  `admin_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '관리자 시퀀스',
  `member_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '작성자 시퀀스',
  `reply_status` varchar(30) NOT NULL DEFAULT 'READY' COMMENT '문의 답변 상태 (READY : 답변대기, ONGOING : 답변중, COMPLETE : 답변완료 )',
  `writer_name` varchar(30) NOT NULL DEFAULT '' COMMENT '작성자 이름',
  `writer_ip` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '작성자 아이피',
  `is_aligo` tinyint(4) NOT NULL DEFAULT 0 COMMENT '알림톡 알림받기 ( 0 : 미사용 , 1 : 사용 )',
  `phone` text NOT NULL DEFAULT '' COMMENT '전화번호',
  `is_email` tinyint(4) NOT NULL DEFAULT 0 COMMENT '이메일 알림받기 ( 0 : 미사용 , 1 : 사용 )',
  `email` text NOT NULL DEFAULT '' COMMENT '이메일주소',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`post_id`),
  KEY `index_created_at` (`created_at`),
  KEY `index_subject` (`subject`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='1:1문의 게시판';

CREATE TABLE IF NOT EXISTS `bl_members` (
  `member_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '회원구분 0:사용자 1:관리자',
  `user_id` varchar(50) NOT NULL COMMENT '회원ID',
  `user_name` text NOT NULL COMMENT '회원이름',
  `user_name_hash` text NOT NULL,
  `nick_name` varchar(60) DEFAULT NULL COMMENT '닉네임',
  `member_grade_id` int(10) unsigned NOT NULL COMMENT '등급',
  `member_grade_date` date DEFAULT NULL COMMENT '등급 지정일자',
  `password` varchar(255) NOT NULL COMMENT '비밀번호',
  `password_old` varchar(255) DEFAULT NULL COMMENT '변경전 비밀번호',
  `password_changed_at` datetime DEFAULT NULL COMMENT '비밀번호 변경일시',
  `password_expected_at` datetime DEFAULT NULL COMMENT '비밀번호 수정 예정 일시',
  `state` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '회원상태  0:정상 1:탈퇴 2:차단',
  `gender` varchar(6) DEFAULT NULL COMMENT '성별',
  `birthday_date` text DEFAULT NULL COMMENT '생년월일',
  `email` text NOT NULL COMMENT '이메일주소',
  `email_hash` text NOT NULL,
  `phone` text NOT NULL COMMENT '전화번호',
  `phone_hash` text NOT NULL,
  `profile_image` varchar(100) DEFAULT NULL COMMENT '프로필이미지',
  `mail_agree` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '메일수신동의',
  `mail_agree_date` date DEFAULT NULL COMMENT '메일동의일시',
  `sms_agree` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '문자수신동의',
  `sms_agree_date` date DEFAULT NULL COMMENT 'SMS 동의일시',
  `add_items` longtext DEFAULT NULL COMMENT '부가정보',
  `last_login_at` datetime DEFAULT NULL COMMENT '마지막 로그인일시',
  `last_login_ip` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '마지막 로그인 IP',
  `login_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '로그인 횟수',
  `failures_count` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '로그인 실패 횟수',
  `first_failure_at` datetime DEFAULT NULL COMMENT '최초 로그인 실패',
  `lock_expires_at` datetime DEFAULT NULL COMMENT '로그인 제한 일시',
  `entry_device` varchar(10) NOT NULL DEFAULT 'pc' COMMENT '가입 디바이스',
  `withdrawal_at` datetime DEFAULT NULL COMMENT '탈퇴회원 처리일시',
  `sleep` tinyint(1) NOT NULL DEFAULT 0 COMMENT '휴면회원',
  `sleep_period` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '휴면회원_방지기간',
  `sleep_apply_at` datetime DEFAULT NULL COMMENT '휴면회원 적용일시',
  `sleep_restore_at` datetime DEFAULT NULL COMMENT '휴면회원 해지일시',
  `required_policy` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '이용약관',
  `required_privacy` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '필수-개인정보 수집/이용 동의',
  `select_privacy` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '회원-선택-개인정보 수집/이용 동의',
  `sns_type` enum('facebook','twitter','me2day','yozm','cyworld','google+','mypeople','naver','kakao','daum','instagram','apple') DEFAULT NULL COMMENT 'SNS 연동종류',
  `join_type` tinyint(1) DEFAULT 0 COMMENT '가입형태(0:일반,1:sns)',
  `admin_memo` longtext DEFAULT NULL COMMENT '관리자메모',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`member_id`) USING BTREE,
  UNIQUE KEY `index_member_user_id` (`user_id`),
  KEY `index_member_created` (`created_at`),
  KEY `index_member_grade` (`member_grade_id`),
  KEY `index_member_sleep_apply_at` (`sleep_apply_at`),
  KEY `index_member_user_type` (`user_type`),
  KEY `index_member_state` (`state`),
  KEY `index_member_sns_type` (`sns_type`),
  KEY `index_member_last_login` (`last_login_at`),
  KEY `index_member_register_device` (`entry_device`),
  KEY `index_member_user_name` (`user_name`(768)),
  KEY `index_member_nick_name` (`nick_name`),
  KEY `index_member_email` (`email`(768)),
  KEY `index_member_phone` (`phone`(768)),
  KEY `member_id` (`member_id`),
  KEY `idx_email_hash` (`email_hash`(768)),
  KEY `idx_phone_hash` (`phone_hash`(768))
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='고객정보';

CREATE TABLE IF NOT EXISTS `bl_member_etc` (
  `memberetc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL DEFAULT 0,
  `field1` varchar(200) DEFAULT NULL,
  `field2` varchar(200) DEFAULT NULL,
  `field3` varchar(200) DEFAULT NULL,
  `field4` varchar(200) DEFAULT NULL,
  `field5` varchar(200) DEFAULT NULL,
  `field6` varchar(200) DEFAULT NULL,
  `field7` varchar(200) DEFAULT NULL,
  `field8` varchar(200) DEFAULT NULL,
  `field9` varchar(200) DEFAULT NULL,
  `field10` varchar(200) DEFAULT NULL,
  `field11` varchar(200) DEFAULT NULL,
  `field12` varchar(200) DEFAULT NULL,
  `field13` varchar(200) DEFAULT NULL,
  `field14` varchar(200) DEFAULT NULL,
  `field15` varchar(200) DEFAULT NULL,
  `field16` varchar(200) DEFAULT NULL,
  `field17` varchar(200) DEFAULT NULL,
  `field18` varchar(200) DEFAULT NULL,
  `field19` varchar(200) DEFAULT NULL,
  `field20` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`memberetc_id`) USING BTREE,
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='고객추가정보';

CREATE TABLE IF NOT EXISTS `bl_member_password_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT 0,
  `member_id` int(10) unsigned NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `index_member_password_change_log_member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bl_member_sns` (
  `member_sns_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '일련번호',
  `member_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '회원번호',
  `uuid` varchar(128) NOT NULL COMMENT 'SNS식별자',
  `sns_join_fl` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'SNS회원가입여부(1:가입)',
  `sns_type` varchar(20) DEFAULT NULL COMMENT 'SNS타입',
  `connect_fl` tinyint(1) NOT NULL DEFAULT 0 COMMENT '계정연결여부(1:연동)',
  `email` text DEFAULT NULL COMMENT 'sns 이메일',
  `token` varchar(1000) NOT NULL COMMENT '연결토큰',
  `access_token` varchar(1000) NOT NULL COMMENT '연결토큰',
  `refresh_token` varchar(1000) NOT NULL COMMENT '갱신토큰',
  `expires_in` varchar(30) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일시',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일시',
  PRIMARY KEY (`member_sns_id`),
  KEY `index_member_sns_member_id` (`member_id`),
  KEY `index_member_sns_type` (`sns_type`),
  KEY `index_member_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SNS 회원관리';

CREATE TABLE IF NOT EXISTS `bl_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` varchar(100) NOT NULL COMMENT '메뉴 고유 ID (URL 등에 사용)',
  `title` varchar(100) NOT NULL COMMENT '메뉴명',
  `en_title` varchar(100) NOT NULL COMMENT '영문 메뉴명',
  `path` varchar(255) NOT NULL COMMENT '경로',
  `description` text DEFAULT NULL COMMENT '설명',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '사용 여부 (1:사용, 0:미사용)',
  `parent_id` int(11) DEFAULT NULL COMMENT '상위 메뉴 ID (최상위 메뉴는 NULL)',
  `depth` int(11) NOT NULL DEFAULT 1 COMMENT '메뉴 깊이 (1, 2, 3 등)',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '정렬 순서',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_id` (`menu_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `bl_menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `bl_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=464 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bl_policy_contents` (
  `policy_contents_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '일련번호',
  `pc_type` varchar(50) NOT NULL DEFAULT 'terms' COMMENT '코드분류 terms privacy',
  `admin_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '제목',
  `info` text NOT NULL COMMENT '내용',
  `is_state` enum('N','Y') DEFAULT 'N' COMMENT '사용여부',
  `version` tinyint(3) unsigned DEFAULT 1 COMMENT '버젼관리 : 버젼 올라 갈대 신규등록',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`policy_contents_id`) USING BTREE,
  KEY `idx_pc_type` (`pc_type`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='배너팝업';

CREATE TABLE IF NOT EXISTS `bl_policy_contents_history` (
  `history_id` int(10) NOT NULL AUTO_INCREMENT,
  `policy_contents_id` int(10) unsigned NOT NULL COMMENT '일련번호',
  `admin_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '제목',
  `info` text NOT NULL COMMENT '내용',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '등록일',
  PRIMARY KEY (`history_id`),
  KEY `policy_contents_id` (`policy_contents_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='약관수정히스토리';

CREATE TABLE IF NOT EXISTS `bl_promotions` (
  `promotions_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '일련번호',
  `promotions_type` varchar(50) NOT NULL DEFAULT 'banner' COMMENT '코드분류 banner, popup',
  `admin_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '제목',
  `pc_img` varchar(100) NOT NULL COMMENT '디폴트 이미지',
  `mo_img` varchar(100) DEFAULT NULL COMMENT '모바일 이미지',
  `path` varchar(100) DEFAULT NULL COMMENT '링크 경로',
  `target` varchar(20) DEFAULT '_self' COMMENT '타겟 _self, _blank',
  `info` varchar(200) DEFAULT NULL COMMENT '대체 텍스트',
  `device` enum('A','P','M') DEFAULT 'A' COMMENT 'A 전체 , P PC, M Mobile',
  `is_view` enum('N','always','period') DEFAULT 'N' COMMENT '노출방식',
  `is_state` enum('N','Y') DEFAULT 'N' COMMENT '사용여부',
  `is_today` enum('N','Y') DEFAULT 'N' COMMENT '하루 보지않기 사용여부',
  `sdate` date DEFAULT NULL COMMENT '노출시작일',
  `edate` date DEFAULT NULL COMMENT '노출종료일',
  `position` int(10) DEFAULT 0 COMMENT 'config 노출 정보 등록 후 시퀀스 등록',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`promotions_id`) USING BTREE,
  KEY `idx_promotions_type` (`promotions_type`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='배너팝업';

CREATE TABLE IF NOT EXISTS `bl_queued_email` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '수신자 이메일 정보' CHECK (json_valid(`recipients`)),
  `from_email` varchar(255) DEFAULT NULL COMMENT '발신자 이메일',
  `from_name` varchar(255) DEFAULT NULL COMMENT '발신자 이름',
  `subject` varchar(255) NOT NULL COMMENT '이메일 제목',
  `content` longtext NOT NULL COMMENT '렌더링된 이메일 내용',
  `attachments` longtext DEFAULT NULL COMMENT '첨부파일 정보',
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued' COMMENT '상태',
  `attempts` int(11) NOT NULL DEFAULT 0 COMMENT '시도 횟수',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT '전송 시간',
  `error` text DEFAULT NULL COMMENT '에러 메시지',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `queued_emails_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `bl_config` (`config_id`, `code_group`, `code_type`, `code`, `sub_code`, `code_name`, `code_name_en`, `use`, `sort`, `sub_sort`, `code_info1`, `code_info2`, `code_info3`, `code_info4`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'member', 'user', '1', NULL, '입문회원', 'level1', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(2, 'member', 'user', '2', NULL, '브론즈회원', 'level2', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(3, 'member', 'user', '3', NULL, '실버회원', 'level3', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(4, 'member', 'user', '4', NULL, '골드회원', 'level4', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(5, 'member', 'user', '5', NULL, '플래티넘회원', 'level5', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(7, 'member', 'master', '6', NULL, '모니터링요원', 'level1', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(8, 'member', 'master', '7', NULL, '운영진', 'level2', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(9, 'member', 'master', '8', NULL, '운영관리', 'level3', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(10, 'member', 'master', '9', NULL, '책임자', 'level4', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(11, 'member', 'master', '10', NULL, '대표', 'level5', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:37:44'),
	(12, 'site', 'master', 'home_name_kr', NULL, '홈페이지한글명', 'home_name_kr', 1, 1, NULL, NULL, NULL, NULL, NULL, '비롯CMS', NULL, '2025-06-04 19:09:25'),
	(13, 'site', 'master', 'home_name_en', NULL, '홈페이지영문명', 'home_name_en', 1, 1, NULL, NULL, NULL, NULL, NULL, 'BlotC&M CMS', '2025-04-16 18:11:05', '2025-06-04 19:09:25'),
	(14, 'site', 'master', 'domain', NULL, '도메인', 'domain', 1, 1, NULL, NULL, NULL, NULL, NULL, 'https://cms.blot-i.co.kr', '2025-04-16 18:11:05', '2025-06-04 19:09:25'),
	(15, 'site', 'master', 'favicon', NULL, '파비콘', 'favicon', 1, 1, NULL, '10kb', NULL, NULL, NULL, 'favicon_1748331085.png', '2025-04-16 18:11:05', '2025-05-27 07:31:25'),
	(16, 'site', 'master', 'meta-title', NULL, '메타제목', 'meta-title', 1, 1, NULL, NULL, NULL, NULL, NULL, '비롯', '2025-04-16 18:11:05', '2025-06-04 19:09:25'),
	(17, 'banner', '', 'main_1', NULL, '메인배너1', 'main banner 1', 1, 1, NULL, NULL, NULL, NULL, NULL, 'main_1', '2025-04-21 17:08:45', '2025-04-21 17:08:45'),
	(18, 'banner', '', 'main_2', NULL, '메인배너2', 'main banner 2', 1, 1, NULL, NULL, NULL, NULL, NULL, 'main_2', '2025-04-21 17:08:45', '2025-04-21 17:08:45'),
	(19, 'banner', '', 'sub_1', NULL, '서브배너1', 'sub banner 1', 1, 1, NULL, NULL, NULL, NULL, NULL, 'sub_1', '2025-04-21 17:08:45', '2025-04-21 17:08:45'),
	(20, 'banner', '', 'sub_2', NULL, '서브배너2', 'sub banner 2', 1, 1, NULL, NULL, NULL, NULL, NULL, 'sub_2', '2025-04-21 17:08:45', '2025-04-21 17:08:45'),
	(21, 'site', 'master', 'meta-desc', NULL, '메타설명', 'meta-desc', 1, 1, NULL, NULL, NULL, NULL, NULL, '온오프라인 통합 마케팅 & 크리에이티브 디지털 에이전시', '2025-04-23 13:56:33', '2025-06-04 19:09:25'),
	(22, 'site', 'master', 'meta-keyword', NULL, '메타키워드', 'meta-keyword', 1, 1, NULL, NULL, NULL, NULL, NULL, '온오프라인 마케팅,행사,메타버스,비대면행사,UI/UX,반응형웹,웹개발,웹에이전시', '2025-04-23 13:56:33', '2025-06-04 19:09:25'),
	(23, 'site', 'master', 'meta-author', NULL, '메타저작자', 'meta-author', 1, 1, NULL, NULL, NULL, NULL, NULL, '비롯', '2025-04-23 13:56:33', '2025-06-04 19:09:25'),
	(24, 'site', 'master', 'footer_settings', NULL, '푸터셋팅', 'footer_settings', 1, 1, NULL, NULL, NULL, NULL, NULL, '[{"title":"주소","content":"서울특별시 강서구 양천로 357 려산빌딩 8층","active":true,"order":0},{"title":"회사명","content":"주식회사 비롯시앤엠","active":true,"order":1},{"title":"TEL","content":"02-859-0955","active":true,"order":2},{"title":"E-mail","content":"hi@b-lot.co.kr","active":true,"order":3},{"title":"사업자등록번호","content":"717-86-02532","active":true,"order":4}]', '2025-04-23 13:56:33', '2025-06-04 19:09:25'),
	(25, 'site', 'master', 'sns_settings', NULL, '소셜미디어셋팅', 'sns_settings', 1, 1, NULL, NULL, NULL, NULL, NULL, '[{"name":"인스타그램","link":"https://www.instagram.com/b.lot_official/","active":true,"order":0},{"name":"유튜브","link":"https://www.youtube.com/channel/UC91iQ1cH00830btaXzAsxEA","active":true,"order":1}]', '2025-04-23 13:56:33', '2025-06-04 19:09:25'),
	(26, 'site', 'master', 'login_settings', NULL, '로그인연동셋팅', 'login_settings', 1, 1, NULL, NULL, NULL, NULL, NULL, '[]', '2025-04-23 13:56:33', '2025-06-04 19:09:25'),
	(28, 'site', 'master', 'forbid_settings', NULL, '금칙어', 'forbid_settings', 1, 1, NULL, NULL, NULL, NULL, NULL, '{"words":[{"word":"내란수괴","order":0},{"word":"시벌","order":1}],"active":true,"count":2}', '2025-04-23 17:05:11', '2025-06-04 19:09:25'),
	(29, 'user', '', 'user_id', NULL, '아이디', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 'input', '2025-04-24 16:28:29', '2025-06-04 11:05:08'),
	(30, 'user', '', 'password', NULL, '비밀번호', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 'password', '2025-04-24 16:36:10', '2025-06-04 11:05:08'),
	(31, 'user', '', 'user_name', NULL, '성명', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 'input', '2025-04-24 16:36:24', '2025-06-04 11:05:08'),
	(32, 'user', '', 'phone', NULL, '전화번호', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 'input', '2025-04-24 16:38:23', '2025-06-04 11:05:08'),
	(33, 'user', '', 'email', NULL, '이메일', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 'input', '2025-04-24 16:41:23', '2025-06-04 11:05:08'),
	(34, 'user', '', 'gender', NULL, '성별', NULL, 1, 0, NULL, '남,여', NULL, NULL, NULL, 'radio', '2025-04-24 16:37:13', '2025-06-04 16:07:31'),
	(35, 'user', '', 'nick_name', NULL, '닉네임', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, 'input', '2025-04-24 16:36:43', '2025-06-04 16:07:31'),
	(36, 'user', '', 'birthday_date', NULL, '생년월일', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, 'date', '2025-04-24 16:36:50', '2025-06-04 16:07:31'),
	(37, 'user', '', 'profile_image', NULL, '프로필이미지', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 'input', '2025-04-24 16:41:28', '2025-06-04 16:07:31'),
	(38, 'user', '', 'user_etc', NULL, '추가항목', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '[{"name_kr":"추가전화번호","name_en":"add_phone","field_type":"input","options":"","is_active":true,"is_required":false,"is_enabled":true,"etc_no":"1"},{"name_kr":"추가이메일주소","name_en":"add_email","field_type":"input","options":"","is_active":true,"is_required":false,"is_enabled":true,"etc_no":"2"},{"name_kr":"당신의 최애 먹거리는?","name_en":"add_food","field_type":"checkbox","options":"한식,중식,일식,양식","is_active":true,"is_required":false,"is_enabled":true,"etc_no":"3"},{"name_kr":"체크박스 테스트 항목","name_en":"checkbox_text","field_type":"checkbox","options":"갤럭시, 유튜브, 네이버웍스","is_active":true,"is_required":true,"is_enabled":true,"etc_no":"4"}]', '2025-04-24 16:28:29', '2025-06-04 16:07:31'),
	(98, 'site', '', 'gtm-head', NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':\r\n                    new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],\r\n                j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\r\n                \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);\r\n            })(window,document,\'script\',\'dataLayer\',\'GTM-MF79BDSD\');\r\n</script>', '2025-06-04 18:45:48', '2025-06-04 19:09:25'),
	(99, 'site', '', 'gtm-body', NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, '<noscript>\r\n<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MF79BDSD"  height="0" width="0" style="display:none;visibility:hidden"></iframe>\r\n</noscript>', '2025-06-04 18:47:27', '2025-06-04 19:09:25'),
	(100, 'site', '', 'gta-head', NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, '<script async src="https://www.googletagmanager.com/gtag/js?id=G-ZPXKPNDMNG"></script>\r\n<script>\r\n            window.dataLayer = window.dataLayer || [];\r\n            function gtag(){dataLayer.push(arguments);}\r\n            gtag(\'js\', new Date());\r\n\r\n            gtag(\'config\', \'G-ZPXKPNDMNG\');\r\n</script>', '2025-06-04 18:47:27', '2025-06-04 19:09:25');

INSERT INTO `bl_board_category` (`board_category_id`, `parent_id`, `depth_code`, `kname`, `ename`, `depth`, `sort_order`, `is_view`, `created_at`) VALUES
	(1, 0, '010000', '자주 묻는 질문', NULL, '1', 0, 'Y', '2025-05-30 10:27:06'),
	(2, 0, '020000', '1:1 문의', NULL, '1', 1, 'Y', '2025-05-30 10:27:06');




INSERT INTO `bl_admin_user_auths` (`id`, `member_grade_id`, `menu_code`, `menu_path`, `permission_read`, `permission_write`, `permission_delete`, `created_at`, `updated_at`) VALUES 
	(211, 10, '4200', '/master/board', 1, 1, 1, '2025-05-06 21:55:57', '2025-05-09 00:02:24'),
	(225, 10, '1100', '/master', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(226, 10, '2100', '/master/menus', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(227, 10, '2200', '/master/user', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(228, 10, '2300', '/master/site', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(229, 10, '2400', '/master/banner', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(230, 10, '2500', '/master/popup', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(231, 10, '2600', '/master/policy', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(232, 10, '3100', '/master/member', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(233, 10, '3200', '/master/grade', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(234, 10, '3300', '/master/auth', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(235, 10, '4100', '/master/configBoards', 1, 1, 0, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(236, 10, '5100', '/master/faq', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(237, 10, '5200', '/master/inquiry', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06'),
	(238, 10, '7100', '/', 1, 1, 1, '2025-06-04 16:58:06', '2025-06-04 16:58:06');


/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
