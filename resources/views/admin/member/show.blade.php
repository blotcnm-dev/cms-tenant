@extends('admin.layout.master')

@section('required-page-title', '회원 상세페이지')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/termsView.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="javascript:;" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">사용자 상세</h2>
            </div>
            <!-- 페이지 타이틀 E -->
            <!-- 컨텐츠 S -->
            <div class="container">
                <form class="max_width" id="memberForm" action="{{ route('member.update', $member->member_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="input_box gray_box">
                        <div class="input_item">
                            <label class="input_title" for="favicon_file">프로필
                            @if($profileConfig->first()->sort == '1')
                                <span class="text-danger">*</span>
                            @endif
                            </label>
                            <div class="inner_box">
                                <div class="uploadFile_box">
                                    @if($member->profile_image)
                                    <!-- 기존이미지가 있을때 //-->
                                    <div class="profile-image-container">
                                        <div class="type_txt">
                                            <div class="txt_box">
                                                <div class="info_box">
                                                    <p class="name">{{ basename($member->profile_image) }}</p>
                                                    <span class="capacity">
                                                        @php
                                                            // 전체 서버 경로 구하기
                                                            $fullPath = public_path(ltrim($member->profile_image, '/'));

                                                            if (file_exists($fullPath)) {
                                                                $fileSize = filesize($fullPath);
                                                                echo round($fileSize / 1024, 2) . 'KB';
                                                            } else {
                                                                echo '파일 크기를 가져올 수 없음';
                                                            }
                                                        @endphp
                                                    </span>
                                                    <button type="button" class="del_btn profile-delete-btn" data-id="{{ $member->member_id }}">삭제</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="type_img">
                                            <div class="img_box">
                                                <img src="{{ asset($member->profile_image) }}" alt="프로필 사진" onerror="this.onerror=null; this.src='{{ asset('/storage/member/none.png') }}';">
                                                <button type="button" class="del_btn profile-delete-btn" data-id="{{ $member->member_id }}">삭제</button>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <!-- 기존이미지가 없을때 //-->
                                        <div class="type_txt profile-upload-container">
                                            <input type="file" id="profile_image_file" name="profile_image" accept="image/*">
                                            <label class="fill_btn plus add_file" for="profile_image_file">
                                                <span>파일 첨부</span>
                                            </label>
                                        </div>
                                        <div class="profile-preview-container" style="display:none;">
                                            <div class="type_txt">
                                                <div class="txt_box">
                                                    <div class="info_box">
                                                        <p class="name"></p>
                                                        <span class="capacity"></span>
                                                        <button type="button" class="del_btn" id="remove_preview">삭제</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="type_img">
                                                <div class="img_box">
                                                    <img src="" alt="file.jpg" id="preview_image">
                                                    <button type="button" class="del_btn" id="remove_preview">삭제</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
{{--                        <div class="input_item">--}}
{{--                            <label class="input_title" for="profile_image_file">프로필</label>--}}
{{--                            @if($member->profile_image)--}}
{{--                                <!-- 기존 이미지가 있는 경우 미리보기 표시 -->--}}
{{--                                <div class="inner_box profile-image-container">--}}
{{--                                    <div class="file_after_box">--}}
{{--                                        <div class="favi_name">--}}
{{--                                            <p class="name">{{ basename($member->profile_image) }}</p>--}}
{{--                                            <span class="capacity">--}}
{{--                                                @php--}}
{{--                                                    // 전체 서버 경로 구하기--}}
{{--                                                    $fullPath = public_path(ltrim($member->profile_image, '/'));--}}

{{--                                                    if (file_exists($fullPath)) {--}}
{{--                                                        $fileSize = filesize($fullPath);--}}
{{--                                                        echo round($fileSize / 1024, 2) . 'KB';--}}
{{--                                                    } else {--}}
{{--                                                        echo '파일 크기를 가져올 수 없음';--}}
{{--                                                    }--}}
{{--                                                @endphp--}}
{{--                                            </span>--}}
{{--                                            <button type="button" class="del_btn profile-delete-btn" data-id="{{ $member->member_id }}" aria-label="삭제하기"></button>--}}
{{--                                        </div>--}}
{{--                                        <div class="favi_preview">--}}
{{--                                            <div class="img">--}}
{{--                                                <img src="{{ asset($member->profile_image) }}" alt="프로필 이미지">--}}
{{--                                                <button type="button" class="del_btn profile-delete-btn" data-id="{{ $member->member_id }}" aria-label="삭제하기"></button>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @else--}}
{{--                                <!-- 이미지가 없는 경우 파일 업로드 UI 표시 -->--}}
{{--                                <div class="inner_box profile-upload-container">--}}
{{--                                    <label class="common_file_input">--}}
{{--                                        <input type="file" id="profile_image_file" name="profile_image" accept="image/*">--}}
{{--                                        <span>파일 추가</span>--}}
{{--                                    </label>--}}
{{--                                </div>--}}

{{--                                <!-- 미리보기 영역 (처음에는 숨김) -->--}}
{{--                                <div class="inner_box profile-preview-container11111" style="display:none;">--}}
{{--                                    <div class="file_after_box">--}}
{{--                                        <div class="favi_name">--}}
{{--                                            <p class="name"></p>--}}
{{--                                            <span class="capacity"></span>--}}
{{--                                            <button type="button" class="del_btn" id="remove_preview" aria-label="삭제하기"></button>--}}
{{--                                        </div>--}}
{{--                                        <div class="favi_preview">--}}
{{--                                            <div class="img">--}}
{{--                                                <img src="" alt="미리보기" id="preview_image">--}}
{{--                                                <button type="button" class="del_btn" id="remove_preview" aria-label="삭제하기"></button>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                            <div id="profile_image-error" class="error_msg"></div>--}}
{{--                        </div>--}}

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


                        <div class="input_item half">
                            <label class="input_title" for="state">회원상태</label>
                            <div class="inner_box">
                                <input type="text"  class="common_input"  value="{{ $member->state == 0 ? '정상' : ($member->state == 1 ? '탈퇴' : '차단') }}" readonly>
                            </div>
                        </div>

                        <div class="input_item half">
                            <label class="input_title" for="join_type">가입방식</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="join_type" value="{{ $member->join_type == 0 ? '일반 가입' : ($member->sns_type ? ucfirst($member->sns_type) : 'SNS 가입') }}" readonly>
                            </div>
                        </div>

                        <div class="input_item half">
                            <label class="input_title" for="agree">수신동의</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="agree" value="{{ ($member->mail_agree ? '이메일' : '') }}{{ ($member->mail_agree && $member->sms_agree ? '/' : '') }}{{ ($member->sms_agree ? '문자' : '') }}{{ (!$member->mail_agree && !$member->sms_agree ? '미동의' : '') }}" readonly>
                            </div>
                        </div>

                        <div class="input_item half">
                            <label class="input_title" for="created_at">회원 가입일</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="created_at" value="{{ format_date( $member->created_at , 'Y-m-d H:i:s' ) }}" readonly>
                            </div>
                        </div>

                        <div class="input_item half">
                            <label class="input_title" for="withdrawal_date">회원 탈퇴일</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="withdrawal_date" value="{{ $member->state == 1 ? format_date( $member->withdrawal_at , 'Y-m-d H:i:s' ) : '-' }}" readonly>
                            </div>
                        </div>

                        <div class="input_item half">
                            <label class="input_title" for="last_login_at">최근 접속일</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="last_login_at" value="{{ $member->last_login_at ? format_date( $member->last_login_at , 'Y-m-d H:i:s' )  : '-' }}" readonly>
                            </div>
                        </div>
                        <div class="input_item half">
                            <label class="input_title" for="member_grade_id">권한</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" name="member_grade_id"  id="member_graddd" class="common_input select_value" placeholder="회원등급 선택"
                                           value="{{ ($member->gradeName ?? '등급 없음') }}" readonly data-value="{{ $member->member_grade_id }}">
                                    <ul role="list">
                                        @foreach($grades as $grade)
                                            <li role="listitem" data-value="{{ $grade->code }}" {{ $member->member_grade_id == $grade->code ? 'selected' : '' }} >{{ $grade->code_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if( $member->state != 1 )
                            <div class="input_item half">
                                <label class="input_title" for="block">차단 설정</label>
                                <div class="inner_box">
                                    <label class="chk_input">
                                        <input type="checkbox" id="block" name="state" value="2" {{ $member->state == 2 ? 'checked' : '' }} onchange="this.checked ? document.querySelector('input[name=state]').value = '2' : document.querySelector('input[name=state]').value = '0'">
                                        <span>로그인 차단</span>
                                    </label>
                                </div>
                            </div>
                            <div class="input_item half">
                                <label class="input_title" for="password_reset">비밀번호</label>
                                <div class="inner_box">
                                    <button type="button" class="border_btn long white password-reset-btn" data-id="{{ $member->member_id }}">초기화</button>
                                </div>
                            </div>

                            <div class="input_item half">
                                <label class="input_title" for="member_delete">회원관리</label>
                                <div class="inner_box">
                                    <button type="button" class="border_btn long white member-delete-btn" data-id="{{ $member->member_id }}">회원 탈퇴</button>
                                </div>
                            </div>
                        @endif

                        <div class="input_item">
                            <label class="input_title" for="admin_memo">메모</label>
                            <div class="inner_box">
                                <div class="textarea_count">
                                    <textarea class="common_textarea" id="admin_memo" name="admin_memo" placeholder="내용을 입력해주세요" maxlength="200">{{ old('admin_memo', $member->admin_memo) }}</textarea>
                                    <p><span>{{ mb_strlen($member->admin_memo ?? '') }}</span> / 200</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn">
                        <button type="submit" class="border_btn save" id="mainForm_submit">
                            <span>확인</span>
                        </button>
                    </div>
                    <!-- 하단 버튼 E -->
                </form>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>

@endsection

@section('required-page-add-content')

    <script type="module">
        import { gnbHandler } from "/src/js/navigation/gnbClassController.js";
        import { layerHandler } from "/src/js/components/layer.js";

        gnbHandler(2, 0);

        document.addEventListener("DOMContentLoaded", () => {
            document.addEventListener('click', (e)=> {
                const target = e.target.closest('.layerOpen');

                if (target) {
                    const title = target.dataset.title;
                    const contentUrl = target.dataset.url;

                    if (title && contentUrl) {
                        layerHandler(title, contentUrl);
                    }
                }
            })
        })
    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 파일 업로드 처리
            const profileImageFile = document.getElementById('profile_image_file');
            if (profileImageFile) {
                profileImageFile.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    console.log('파일 선택됨:', file ? file.name : '없음');

                    if (file) {
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

                        // 파일 선택 컨테이너 숨기기
                        const uploadContainer = document.querySelector('.profile-upload-container');
                        if (uploadContainer) {
                            uploadContainer.style.display = 'none';
                        }

                        // 미리보기 표시
                        const previewContainer = document.querySelector('.profile-preview-container');
                        const previewImage = document.getElementById('preview_image');
                        const nameElement = document.querySelector('.profile-preview-container .name');
                        const capacityElement = document.querySelector('.profile-preview-container .capacity');

                        if (previewContainer && previewImage) {
                            // 파일 정보 표시
                            if (nameElement) nameElement.textContent = file.name;
                            if (capacityElement) capacityElement.textContent = `(${formatFileSize(file.size)})`;

                            // 미리보기 이미지 생성
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImage.src = e.target.result;
                                previewContainer.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                });
            }

            // 프로필 이미지 삭제 버튼 클릭 처리
            const profileDeleteButtons = document.querySelectorAll('.profile-delete-btn');
            profileDeleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    if (confirm('프로필 이미지를 삭제하시겠습니까?')) {
                        const memberId = this.getAttribute('data-id');

                        // AJAX로 이미지 삭제 요청
                        fetch(`/master/member/${memberId}/profile_del`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.message) {
                                    alert(data.message);

                                    // 파일 업로드 UI로 교체
                                    const profileContainer = document.querySelector('.profile-image-container');
                                    if (profileContainer) {
                                        profileContainer.innerHTML = `
                                        <input type="file" id="profile_image_file" name="profile_image" accept="image/*">
                                        <label class="fill_btn plus add_file" for="profile_image_file">
                                            <span>파일 첨부</span>
                                        </label> `;


                                        // 새로 추가된 파일 입력 필드에 이벤트 리스너 추가
                                        const newProfileImageFile = document.getElementById('profile_image_file');
                                        if (newProfileImageFile) {
                                            newProfileImageFile.addEventListener('change', handleFileUpload);
                                        }
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('오류가 발생했습니다. 다시 시도해주세요.');
                            });
                    }
                });
            });

            // 미리보기 삭제 버튼 클릭 처리
            const removePreviewButtons = document.querySelectorAll('#remove_preview');
            removePreviewButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // 미리보기 컨테이너 숨기기
                    const previewContainer = document.querySelector('.profile-preview-container');
                    if (previewContainer) {
                        previewContainer.style.display = 'none';
                    }

                    // 파일 선택 컨테이너 표시
                    const uploadContainer = document.querySelector('.profile-upload-container');
                    if (uploadContainer) {
                        uploadContainer.style.display = 'block';
                    }

                    // 파일 입력 필드 초기화
                    const profileImageFile = document.getElementById('profile_image_file');
                    if (profileImageFile) {
                        profileImageFile.value = '';
                    }
                });
            });

            // 회원 정보 폼 제출 처리
            const memberForm = document.getElementById('memberForm');
            if (memberForm) {
                memberForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    //error메시지 스타일 숨김
                    const errorElements = document.querySelectorAll(`.error_msg`);
                    errorElements.forEach(element => {
                        element.style.display = "none";
                    });

                    disable_button(mainForm_submit);

                    const memberGradeInput = document.getElementById('member_graddd');
                    let dataValue = '';
                    if(memberGradeInput) {
                        dataValue = memberGradeInput.getAttribute('data-value');
                    }
                    const formData = new FormData(this);
                    formData.set('member_grade_id', dataValue);


                    formData.forEach((value, key) => {
                        console.log(key + ":" + value);
                    });


                    // API 호출
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message || '회원 정보가 성공적으로 업데이트되었습니다.');

                                // 리다이렉트 처리
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            } else {

                                // 일반 에러 메시지
                                if (data.message) {
                                    alert(data.message);
                                }

                                // 에러 처리
                                if (data.errors) {
                                    // 각 필드의 오류 메시지 수집
                                    for (const field in data.errors) {
                                        console.log(field);
                                        const errorElement = document.getElementById(`${field}-error`);
                                        // 오류 표시 요소가 있으면 메시지 표시
                                        if (errorElement && data.errors[field].length > 0) {
                                            errorElement.textContent = data.errors[field][0];
                                            errorElement.style.display = 'block';
                                        }
                                    }
                                }

                                restore_button(mainForm_submit);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('처리 중 오류가 발생했습니다.');

                            restore_button(mainForm_submit);
                        });
                });
            }

            // 파일 업로드 처리 함수
            function handleFileUpload(event) {
                const file = event.target.files[0];
                if (file) {
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
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewContainer = document.querySelector('.profile-preview-container');
                        const previewImage = document.getElementById('preview_image');
                        const nameElement = document.querySelector('.profile-preview-container .name');
                        const capacityElement = document.querySelector('.profile-preview-container .capacity');

                        if (previewImage) previewImage.src = e.target.result;
                        if (nameElement) nameElement.textContent = file.name;
                        if (capacityElement) capacityElement.textContent = `(${formatFileSize(file.size)})`;

                        // 파일 업로드 UI 숨기기
                        const uploadContainer = document.querySelector('.profile-upload-container');
                        if (uploadContainer) uploadContainer.style.display = 'none';

                        // 미리보기 표시
                        if (previewContainer) previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }

            // 파일 크기 포맷팅 함수
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';

                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));

                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // 회원 탈퇴 버튼 클릭 처리
            $('.member-delete-btn').click(function() {
                if (confirm('정말 이 회원을 탈퇴 처리하시겠습니까?')) {
                    var memberId = $(this).data('id');

                    $.ajax({
                        url: '/master/member/' + memberId + '/withdraw',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert('회원이 탈퇴 처리되었습니다.');
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('오류가 발생했습니다. 다시 시도해주세요.');
                        }
                    });
                }
            });

            // 비밀번호 초기화 버튼 클릭 처리
            $('.password-reset-btn').click(function() {
                if (confirm('회원의 비밀번호를 초기화하시겠습니까?\n초기화된 비밀번호는 회원의 이메일로 전송됩니다.')) {
                    const memberId = this.getAttribute('data-id');
                    // 버튼 비활성화 및 로딩 표시
                    this.disabled = true;
                    this.classList.add('loading');
                    $.ajax({
                        url: '/master/member/' + memberId + '/reset_password',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            //console.log(response);
                            if(response.success){
                                alert('비밀번호가 성공적으로 초기화되었으며, 회원의 이메일로 전송되었습니다.');
                                location.reload();
                            }else{
                                alert(response.message);
                                this.disabled = false;
                                this.classList.remove('loading');
                            }
                        },
                        error: function(xhr) {
                            // 버튼 상태 복원
                            console.log("********************");
                            console.log(xhr);
                            console.log("********************");
                            this.disabled = false;
                            this.classList.remove('loading');
                            alert('오류가 발생했습니다. 다시 시도해주세요.');
                        }
                    });
                }
            });



            const textArea = document.getElementById('admin_memo');
            const countDisplay = document.querySelector('.textarea_count p span');
            const maxLength = 200;

            // 초기 로드 시 텍스트가 있는 경우 카운터에 반영
            function initCounter() {
                if (textArea.value) {
                    const currentLength = textArea.value.length;
                    countDisplay.textContent = currentLength;

                    // 최대 길이 초과 시 스타일 적용
                    if (currentLength > maxLength) {
                        countDisplay.parentElement.classList.add('over');
                    }
                }
            }

            // 입력 이벤트 처리
            function updateCounter() {
                const currentLength = textArea.value.length;
                countDisplay.textContent = currentLength;

                if (currentLength > maxLength) {
                    countDisplay.parentElement.classList.add('over');
                } else {
                    countDisplay.parentElement.classList.remove('over');
                }
            }
            // 초기 카운터 설정
            initCounter();
            // 이벤트 리스너 등록
            textArea.addEventListener('input', updateCounter);

            // 페이지 로드 후 포커스시에도 카운터 확인
            textArea.addEventListener('focus', updateCounter);

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
