@extends('web.layout.master')

@section('required-page-title', '회원가입')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/members/signup.css">
@stop

@section('required-page-header-js')
    <script type="module" src="/web/js/members/signup.js" defer></script>
@stop

@section('required-page-main-content')
    <main>
        <section class="wrapper_container">
            <div class="container w-560">
                <h2 class="section_title">BLOT : 회원가입</h2>

                <form id="mainForm" method="post" enctype="multipart/form-data" class="max_width">
                    @csrf
                    @if($profileConfig->first()->use == '1')
                    <div class="input_item_container">
                        <label for="profile_image_file">프로필
                            @if($profileConfig->first()->sort == '1')
                                <span style="color:#ff402b">*</span>
                            @endif
                        </label>
                        <div class="profile_image_container">
                            <img src="/web/images/members_default.jpg" alt="">
                        </div>
                        <div class="input_button_container profile_button_container">
                            <input type="file" id="user_profile" name="profile_image" >
                            <label for="user_profile">📷︎</label>
                        </div>
                        <div id="profile_image-error" class="error_notice"></div>
                    </div>
                    @endif
                        <!-- 회원관리 기본 필드 : S //-->
                        @foreach($basic_fields as $field)
                            {!! $field !!}
                        @endforeach
                        <!-- 회원관리 기본 필드 : E //-->

                        <!-- 커스텀 된 추가필드 : S //-->
                        @foreach($etc_fields as $field)
                            {!! $field !!}
                        @endforeach
                        <!-- 커스텀 된 추가필드 : E //-->
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn fixed">
                        <button class="submit_button" id="mainForm_submit">
                            <span>확인</span>
                        </button>
                    </div>
                    <!-- 하단 버튼 E -->
                </form>
            </div>
        </section>

    </main>
@stop

@section('required-page-add-content')<!-- 개발용 스크립트 S -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('mainForm').addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            //error메시지 스타일 숨김
            const errorElements = document.querySelectorAll(`.error_notice`);
            errorElements.forEach(element => {
                element.style.display = "none";
            });

            disable_button(mainForm_submit);

            //form submit
            let frm = document.forms['mainForm'];
            let formData = new FormData(frm);

            const fileInput = document.getElementById('user_profile');
            if (fileInput.files.length > 0) {
                console.log('제출 직전 파일 확인:', fileInput.files[0].name);
            } else {
                console.log('제출할 파일 없음');
            }

            // AJAX 요청의 URL을 변수에 저장
            const url = '{{ route('front_member.store') }}';

            formData.forEach((value, key) => {
                console.log(key + ":" + value);
            });

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
                        // 성공 시 처리
                        alert(data.message);
                        window.location.href = data.redirect || '{{ route('member.list') }}';
                    } else {
                        if(data.message){
                            alert(data.message);
                        }

                        console.log(data.error);
                        restore_button(mainForm_submit);
                        // 에러 처리
                        if (data.errors) {
                            // 각 필드의 오류 메시지 수집
                            for (const field in data.errors) {
                                const errorElement = document.getElementById(`${field}-error`);
                                // 오류 표시 요소가 있으면 메시지 표시
                                if (errorElement && data.errors[field].length > 0) {
                                    errorElement.textContent = data.errors[field][0];
                                    errorElement.style.display = 'block';
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    restore_button(mainForm_submit);
                });
        });


        // //프로필 이미지 추가 할 때
        // const $profile_image_file = document.getElementById('profile_image_file');
        // $profile_image_file.addEventListener('change', handleFileSelect);
        //
        // // 파일 선택 처리
        // function handleFileSelect(e) {
        //     const files = e.target.files;
        //     handleFiles(files);
        //     // 같은 파일을 다시 선택할 수 있도록 value 초기화
        //     //$profile_image_file.value = '';
        // }
        //
        // // 선택된 파일 처리 및 미리보기 생성
        // function handleFiles(files) {
        //     Array.from(files).forEach(file => {
        //         // 이미지 파일인지 확인
        //         if (!file.type.match('image.*')) {
        //             alert('이미지 파일만 업로드 가능합니다.');
        //             return;
        //         }
        //
        //         // 파일 크기 제한 (10MB)
        //         if (file.size > 10 * 1024 * 1024) {
        //             alert('파일 크기는 10MB 이하여야 합니다.');
        //             return;
        //         }
        //
        //         // 미리보기 생성
        //         createPreview(file);
        //     });
        // }
        //
        // function createPreview(file) {
        //     const reader = new FileReader();
        //     const preViewBox = document.getElementById('preViewBox');
        //     const preViewImg = document.getElementById('preViewImg');
        //     reader.onload = function(e) {
        //         const img_src = e.target.result;
        //         preViewImg.src = img_src;
        //
        //         const nameElement = document.querySelector('.favi_name .name');
        //         const capacityElement = document.querySelector('.favi_name .capacity');
        //         nameElement.textContent = file.name;
        //         capacityElement.textContent = `(${formatFileSize(file.size)})`;
        //         preViewBox.style = 'display:block';
        //     };
        //     // 파일 읽기 시작
        //     reader.readAsDataURL(file);
        //     document.getElementById('profile_image_file').dataset.hasFile = 'true';
        // }
        //
        // // 파일 크기 포맷팅
        // function formatFileSize(bytes) {
        //     if (bytes === 0) return '0 Bytes';
        //
        //     const k = 1024;
        //     const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        //     const i = 1; //Math.floor(Math.log(bytes) / Math.log(k));
        //
        //     return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        // }

        //프로필 사진 삭제
        // const profileDeleteButtons = document.querySelectorAll('.del_btn[data-target="profile_image"]');
        // profileDeleteButtons.forEach(function(button) {
        //     button.addEventListener('click', function() {
        //         // 파비콘 미리보기 초기화
        //         const previewImg = document.getElementById('preViewImg');
        //         const preViewBox = document.getElementById('preViewBox');
        //         if (previewImg) {
        //             previewImg.src = '';
        //         }
        //
        //         const favicon_file = document.getElementById('favicon_file');
        //         const nameElement = document.querySelector('.favi_name .name');
        //         const capacityElement = document.querySelector('.favi_name .capacity');
        //
        //         if (favicon_file) favicon_file.value = '';
        //         if (nameElement) nameElement.textContent = '';
        //         if (capacityElement) capacityElement.textContent = '';
        //
        //         preViewBox.style="display:none";
        //     });
        // });

    });

    function disable_button(element){
        element.disabled = true;
        element.classList.add('loading');
    }

    function restore_button(element){
        element.disabled = false;
        element.classList.remove('loading');
    }



</script>
<!-- 개발용 스크립트 E -->
@stop
