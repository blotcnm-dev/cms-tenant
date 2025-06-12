@extends('admin.layout.master')

@section('required-page-title', '팝업 등록')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/addBanner.css">
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="#" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">팝업 등록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="mainForm" action="{{ route('popup.store') }}" method="POST" enctype="multipart/form-data" class="max_width">
                    @csrf
                    <input type="hidden" name="promotions_type" value="popup">
                    <input type="hidden" name="is_view" value="">
                    <input type="hidden" name="is_state" value="">
                    <input type="hidden" name="position" value="0">
                    <div class="input_box gray_box">
                        <div class="input_item">
                            <label class="input_title" for="popup_title">제목</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="popup_title" placeholder="제목을 입력하세요" name="title" value="{{ old('title') }}">
                            </div>
                            <div id="title_error" class="error_msg"></div>
                        </div>
                        <div class="input_item">
                            <label class="input_title" for="popup_img">이미지</label>
                            <div class="inner_box">
                                <div class="uploadFile_box">
                                    <input type="file" id="popup_img" name="popup_img" accept="image/jpeg, image/png, image/gif">
                                    <div class="type_txt">
                                        <label class="fill_btn plus add_file" for="popup_img">
                                            <span>파일 첨부</span>
                                        </label>
                                    </div>
                                    <div class="type_img">
                                        <div class="img_box" id="file_box" style="display: none">
                                            <img src="/src/assets/images/sample.jpg" alt="file.jpg" id="preViewImg">
                                            <button type="button" class="del_btn" data-target="popup_img"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="img_error" class="error_msg"></div>
                        </div>
                        <div class="input_item url">
                            <label class="input_title" for="popup_url">이동 URL</label>
                            <div class="inner_box flex">
                                <input type="text" class="common_input" id="popup_url" name="path" value="{{ old('path') }}">
                                <label class="chk_input">
                                    <input type="checkbox" name="target" value="_blank">
                                    <span>새 창에서 열기</span>
                                </label>
                            </div>
                        </div>
                        <div class="input_item">
                            <label class="input_title" for="popup_text">텍스트</label>
                            <div class="inner_box">
                                <div class="textarea_count">
                                    <textarea class="common_textarea" id="popup_text" name="info" placeholder="내용을 입력해주세요">{{ old('info') }}</textarea>
                                    <p><span id="text_count">0</span> / 200</p>
                                </div>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title">노출 환경</label>
                            <div class="inner_box flex gap_input">
                                <label class="radio_input">
                                    <input type="radio" id="device_1" name="device" value="A" checked>
                                    <span>전체</span>
                                </label>
                                <label class="radio_input">
                                    <input type="radio" id="device_2" name="device"  value="P" >
                                    <span>데스크톱</span>
                                </label>
                                <label class="radio_input">
                                    <input type="radio" id="device_3" name="device"  value="M" >
                                    <span>모바일</span>
                                </label>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title">노출 방식</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" id="id_is_view" class="common_input select_value" name="is_view_txt" placeholder="선택" data-value="" value="{{ old('is_view_text') }}" readonly>
                                    <ul role="list">
                                        <li role="listitem" data-value="always">상시 노출</li>
                                        <li role="listitem" data-value="period">기간 노출</li>
                                    </ul>
                                </div>
                            </div>
                            <div id="view_error" class="error_msg"></div>
                        </div>
                        <div class="input_item half date w_6">
                            <label class="input_title">노출 기간</label>
                            <div class="inner_box">
                                <div class="calendar_input">
                                    <input type="date" class="common_input check_is_date" name="sdate" value="{{ old('sdate') }}">
                                </div>
                                <span>~</span>
                                <div class="calendar_input">
                                    <input type="date" class="common_input check_is_date" name="edate" value="{{ old('edate') }}">
                                </div>
                            </div>
                        </div>
                        <div class="input_item half w_4">
                            <label class="input_title">노출 여부</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" name="is_state_txt" placeholder="선택" data-value="" value="{{ old('is_state_text') }}" readonly>
                                    <ul role="list">
                                        <li role="listitem" data-value="Y">사용함</li>
                                        <li role="listitem" data-value="N">사용안함</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="input_item half input_gap">
                            <label class="input_title">오늘 하루동안 <br>보지 않기</label>
                            <div class="inner_box flex gap_input">
                                <label class="radio_input">
                                    <input type="radio" id="today_1" name="is_today" value="Y">
                                    <span>사용</span>
                                </label>
                                <label class="radio_input">
                                    <input type="radio" id="today_2" name="is_today"  value="N" checked>
                                    <span>사용안함</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn fixed">
                        <a href="{{ route('popup.index') }}" class="border_btn cancel">
                            <span>취소</span>
                        </a>
                        <button type="submit" id="submitBtn" class="border_btn register">
                            <span>확인</span>
                        </button>
                    </div>
                    <!-- 하단 버튼 E -->
                </form>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
@stop

@section('required-page-add-content')
    <script type="module">
        import { dateInputChange } from "/src/js/components/dateInput.js";

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[type="date"]').forEach((input) => dateInputChange({ target: input }));
            document.addEventListener('input', (e) => {
                if (e.target && e.target.type === 'date') {
                    dateInputChange(e);
                }
            });
        });
    </script>
    <!-- 개발용 스크립트 S -->
    <script>

        function checkIsViewValue() {
            const isViewInput = document.getElementById('id_is_view');
            const dataValue = isViewInput.getAttribute('data-value');
            if (dataValue === 'always') {
                // .check_is_date 내의 모든 input 요소 선택
                const dateInputs = document.querySelectorAll('.check_is_date');
                dateInputs.forEach(input => {
                    input.disabled = true;
                    input.value = '';
                });
            } else {
                const dateInputs = document.querySelectorAll('.check_is_date');
                dateInputs.forEach(input => {
                    input.disabled = false;
                    // input.value = '';
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function() {

            const textarea = document.getElementById('popup_text');
            const counter = document.getElementById('text_count');
            const maxLength = 200;

            // 초기값 반영
            counter.textContent = textarea.value.length;

            textarea.addEventListener('input', function () {
                let length = textarea.value.length;

                // 글자 수 제한 보호 (선택사항, maxlength로도 동작함)
                if (length > maxLength) {
                    textarea.value = textarea.value.substring(0, maxLength);
                    length = maxLength;
                }

                counter.textContent = length;
            });

            // data-value 변화 감지용 옵저버
            const target = document.getElementById('id_is_view');
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-value') {
                        checkIsViewValue();
                    }
                }
            });

            observer.observe(target, { attributes: true });

            // 필요 시 초기 실행
            checkIsViewValue();

            // 추가 할 때
            const $popup_img = document.getElementById('popup_img');
            $popup_img.addEventListener('change', handleFileSelect);

            // 파일 선택 처리
            function handleFileSelect(e) {
                const files = e.target.files;
                handleFiles(files);
                // 같은 파일을 다시 선택할 수 있도록 value 초기화
                //$popup_img.value = '';
            }

            // 선택된 파일 처리 및 미리보기 생성
            function handleFiles(files) {
                Array.from(files).forEach(file => {
                    // 이미지 파일인지 확인
                    if (!file.type.match('image.*')) {
                        alert('이미지 파일만 업로드 가능합니다.');
                        return;
                    }

                    // 파일 크기 제한 (10MB)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('파일 크기는 10MB 이하여야 합니다.');
                        return;
                    }

                    // 미리보기 생성
                    createPreview(file);
                });
            }

            function createPreview(file) {
                const reader = new FileReader();
                const file_box = document.getElementById('file_box');
                const preViewImg = document.getElementById('preViewImg');

                reader.onload = function(e) {
                    const img_src = e.target.result;
                    preViewImg.src = img_src;
                    file_box.style = 'display:block';
                    document.getElementById('common_label_input').style.display = 'none';
                };
                // 파일 읽기 시작
                reader.readAsDataURL(file);
            }

            // 이미지 삭제
            const popupDeleteButtons = document.querySelectorAll('.del_btn[data-target="popup_img"]');

            popupDeleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // 미리보기 초기화
                    const previewImg = document.getElementById('preViewImg');
                    const file_box = document.getElementById('file_box');
                    if (previewImg) {
                        previewImg.src = '';
                    }

                    const popup_img = document.getElementById('popup_img');

                    if (popup_img) popup_img.value = '';

                    file_box.style="display:none";
                    document.getElementById('common_label_input').style.display = 'flex';
                });
            });

            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // 버튼 비활성화
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                document.querySelector('input[name="is_view"]').value = document.querySelector('input[name="is_view_txt"]').getAttribute('data-value');
                document.querySelector('input[name="is_state"]').value = document.querySelector('input[name="is_state_txt"]').getAttribute('data-value');

                const formData = new FormData(this);
                const url = '{{ route('popup.store') }}';

                // $.ajax({
                //     url: url,
                //     data: formData,
                //     type: 'post',
                //     dataType: 'json',
                //     processData: false,  // 중요: FormData를 처리하지 않도록 설정
                //     contentType: false,  // 중요: 컨텐트 타입을 자동으로 설정하지 않도록 함
                //     success: function(res) {
                //         console.log("결과==>["+res+"]");
                //     },
                //     error: function(jqXHR, textStatus, errorThrown) {
                //         console.log('1status : ' + jqXHR.status);
                //         console.log('2textStatus : ' + textStatus);
                //     },
                //     complete: function(jqXHR, textStatus) {
                //         console.log("3AJAX complete : " + url);
                //     }
                // });
                // console.log("======== FormData의 내용을 출력 ======");
                // formData.forEach((value, key) => {
                //     console.log(key + ":"+ value);
                // });
                // console.log("=====================");

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
                            // 성공 시 처리
                            alert(data.message);
                            window.location.href = data.redirect || '{{ route('popup.index') }}';
                        } else {
                            if (data.errors.title) {
                                document.getElementById('title_error').textContent = data.errors.title[0];
                                document.getElementById('title_error').style.display = 'block';
                            }
                            if (data.errors.popup_img) {
                                document.getElementById('img_error').textContent = data.errors.popup_img[0];
                                document.getElementById('img_error').style.display = 'block';
                            }
                            if (data.errors.is_view) {
                                document.getElementById('view_error').textContent = data.errors.is_view[0];
                                document.getElementById('view_error').style.display = 'block';
                            }

                            // 버튼 다시 활성화
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(errors => {
                        console.log(errors);
                        // 버튼 다시 활성화
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        alert('처리 중 오류가 발생했습니다.');
                    });
            });
        });

    </script>
    <!-- 개발용 스크립트 E -->
@stop
