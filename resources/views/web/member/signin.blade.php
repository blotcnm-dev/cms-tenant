@extends('web.layout.master')

@section('required-page-title', '로그인')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/members/signin.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>

        <section class="wrapper_container full-height">
            <div class="container w-375">
                <div class="sign_in_container">
                    <h2 class="section_title">BLOT : LOGIN</h2>
                    <form id="mainForm" method="POST">
                        @csrf
                        <div class="input_container">
                            <label for="user_id" class="label-hidden" aria-hidden="true">회원가입</label>
                            <input type="text" id="user_id" name="user_id" placeholder="아이디를 입력해 주세요.">
                            <p id="user_id-error" class="error_notice" style="display:none;"></p>
                        </div>
                        <div class="input_container">
                            <label for="user_password" class="label-hidden" aria-hidden="true">비밀번호</label>
                            <input type="password" id="password" name="password" placeholder="비밀번호를 입력해 주세요.">
                            <p id="password-error" class="error_notice" style="display:none;"></p>
                        </div>
                        <button type="submit" class="sign_in_submit_button">로그인</button>
                        <div class="sign_in_state_container">
                            <input type="checkbox" id="sign_in_check" name="sign_in_check">
                            <label for="sign_in_check">
                            <span class="sign_in_box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="9" viewBox="0 0 12 9" fill="none"><path d="M10.8908 2.26455C11.2165 1.90136 11.1862 1.34289 10.823 1.01717C10.4598 0.691453 9.90132 0.72183 9.5756 1.08502L5.12776 6.04456L2.48744 3.40424C2.14247 3.05927 1.58318 3.05927 1.23821 3.40424C0.893252 3.7492 0.893251 4.30849 1.23821 4.65346L4.53805 7.95329C4.70959 8.12483 4.94414 8.21828 5.18665 8.21168C5.42917 8.20509 5.6583 8.09905 5.82027 7.91844L10.8908 2.26455Z" fill="white"></path></svg>
                            </span>
                                로그인 상태 유지하기
                            </label>
                            <a href="javascript:void(0)">아이디·비밀번호 찾기</a>
                        </div>
                    </form>
                    <!--
                    <div class="sns_sign_in_container">
                        <h3>SNS 간편 로그인</h3>
                        <div class="sns_link_container">
                            <a href="javascript:void(0)" class="naver_sign_in_icon">
                                <img src="/web/images/icon/naver-logo_v2.png" alt="">
                            </a>
                            <a href="javascript:void(0)" class="kakao_sign_in_icon">
                                <img src="/web/images/icon/kakao-logo_v2.png" alt="">
                            </a>
                            <a href="javascript:void(0)" class="facebook_sign_in_icon">
                                <img src="/web/images/icon/facebook-logo_v2.png" alt="">
                            </a>
                            <a href="javascript:void(0)" class="google_sign_in_icon">
                                <img src="/web/images/icon/google-logo_v2.png" alt="">
                            </a>
                            <a href="javascript:void(0)" class="apple_sign_in_icon">
                                <img src="/web/images/icon/apple-logo_v2.png" alt="">
                            </a>
                        </div>
                    </div>
                    //--><br>
                    <a href="{{ route('front_member.create') }}" class="sign_up_button">회원가입</a>
                </div>
            </div>
        </section>



    </main>
@stop

@section('required-page-add-content')

    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();

                //error메시지 스타일 숨김
                const errorElements = document.querySelectorAll(`.error_notice`);
                errorElements.forEach(element => {
                    element.style.display = "none";
                });
                // 버튼 비활성화
                // const submitBtn = document.getElementById('submitBtn');
                // submitBtn.classList.add('loading');
                // submitBtn.disabled = true;

                const formData = new FormData(this);
                const url = '{{ route('loginProc') }}';
                let urlObject = new URL(window.location.href);
                let referrer = urlObject.searchParams.get("referrer"),
                    goUrl = referrer ?? '/';

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
                        console.log(data);
                        if (data.success) {
                            if (referrer && referrer !== 'null' && referrer !== 'undefined') {
                                window.location.href = referrer;
                            } else {
                                window.location.href = '/'; // 예시: 관리자 대시보드 등
                            }
                        } else {
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



@stop
