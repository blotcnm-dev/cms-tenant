<!DOCTYPE html>
<html lang="ko">
<head>
    <title>blot CMS</title>
    <!-- meta -->
    @include('admin.layout.headerMeta')
    <!-- javascript -->
    <!-- include('admin.layout.headerJs') //-->
    <!-- css -->
    @include('admin.layout.headerCss')
    <link rel="stylesheet" href="/src/style/member/login.css">
</head>
<body>

<main>
    @if(session('error'))
        <script>
            alert('{{ session('error') }}');
        </script>
    @endif
    <div id="wrap">
        <!-- 컨텐츠 S -->
        <div class="container">
            <div class="login_wrap">
                <h1 class="logo">BLOT CMS</h1>
                <form id="mainForm" method="POST">
                    @csrf
                    <div class="login_box">
                        <div class="input_box id">
                            <input type="text" name="user_id" class="common_input" placeholder="아이디">
                            <div id="user_id-error" class="error_msg" style="display:none;"></div>
                        </div>
                        <div class="input_box password">
                            <input type="password" name="password" class="common_input" placeholder="비밀번호">
                            <div id="password-error" class="error_msg" style="display:none;"></div>
                        </div>
                    </div>
                    <button type="submit" class="fill_btn black" id="submitBtn">
                        <span>LOGIN</span>
                    </button>
                </form>
                <div class="btn_flex">
                    <a href="/">회원가입</a>
                    <a href="/">아이디 / 비밀번호 찾기</a>
                </div>
            </div>
            <div class="info_box">
                <p class="sub_tit">Best and total brand experience creator BLOT</p>
                <p class="tit">
                    Good products ultimately make companies smile,
                    and good brand experiences make customers happy.
                </p>
                <ul role="list">
                    <li role="listitem" class="line">서울특별시 강서구 양천로 357 려산빌딩 8층</li>
                    <li role="listitem">TEL: 02-859-0955</li>
                    <li role="listitem" class="line">E-mail: hi@b-lot.co.kr</li>
                    <li role="listitem" class="line">사업자 등록번호: 717-86-02532</li>
                    <li role="listitem">주식회사 비롯시앤엠</li>
                </ul>
                <p class="copyright">Copyright (c) 2021 B.lot. All Rights Reserved.</p>
            </div>
        </div>
        <!-- 컨텐츠 E -->
    </div>
</main>
</body>

<!-- 개발용 스크립트 S -->
<script>
    document.addEventListener("DOMContentLoaded", function() {

    document.getElementById('mainForm').addEventListener('submit', function(e) {
        e.preventDefault();

        //error메시지 스타일 숨김
        const errorElements = document.querySelectorAll(`.error_msg`);
        errorElements.forEach(element => {
            element.style.display = "none";
        });


        // 버튼 비활성화
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;

        const formData = new FormData(this);
        const url = '{{ route('master.loginProc') }}';
        let urlObject = new URL(window.location.href);
        let referrer = urlObject.searchParams.get("referrer"),
            goUrl = referrer ?? '/master';

        // AJAX 요청
        fetch( url , {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if(data.message){ alert(data.message); }
                    if (referrer && referrer !== 'null' && referrer !== 'undefined') {
                        window.location.href = referrer;
                    } else {
                        window.location.href = '/'; // 예시: 관리자 대시보드 등
                    }
                } else {
                    if(data.message){ alert(data.message); }
                    // 에러 처리
                    if (data.errors) {
                        // 에러 메시지 표시
                        if (data.errors.user_id) {
                            document.getElementById('user_id-error').textContent = data.errors.user_id[0];
                            document.getElementById('user_id-error').style.display = 'block';
                        }
                        if (data.errors.password) {
                            document.getElementById('password-error').textContent = data.errors.password[0];
                            document.getElementById('password-error').style.display = 'block';
                        }
                    }
                    // 버튼 다시 활성화
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // 버튼 다시 활성화
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                alert('처리 중 오류가 발생했습니다.');
            });
        });
    });
</script>
</html>
