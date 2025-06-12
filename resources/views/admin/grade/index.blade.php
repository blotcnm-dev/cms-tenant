@extends('admin.layout.master')

@section('required-page-title', '등급 명칭 설정')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/userManagement/gradeNameSetting.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')

    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">권한 관리</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="gradeForm" class="max_width">
                    @csrf
                    <div class="content_title_box no_mg">
                        <h3 class="title">관리자 등급 명칭 설정</h3>
                    </div>
                    <div class="common_table manager">
                        <div class="thead">
                            <div class="level">구분</div>
                            <div class="name">명칭</div>
                            <div class="management">관리</div>
                        </div>
                        <ul role="list" class="tbody">
                            @foreach($adminGrades as $index => $grade)
                                <li role="listitem">
                                    <div class="level">
                                        <span>구분</span>
                                        <p>{{ $grade->code }}레벨</p>
                                    </div>
                                    <div class="name">
                                        <span>명칭</span>
                                        <div>
                                            <input type="text" class="common_input admin-grade-name"
                                                   name="admin_grades[{{ $index }}][name]"
                                                   value="{{ $grade->code_name }}"
                                                   data-original="{{ $grade->code_name }}">
                                            <input type="hidden" name="admin_grades[{{ $index }}][id]" value="{{ $grade->config_id }}">
                                        </div>
                                    </div>
                                    <div class="management">
                                        <span>관리</span>
                                        <a href="{{ route('auth.index') }}?grade_id={{ $grade->code }}" class="border_btn white">권한 보기</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="content_title_box">
                        <h3 class="title">사용자 등급 명칭 설정</h3>
                    </div>
                    <div class="common_table user">
                        <div class="thead">
                            <div class="level">구분</div>
                            <div class="name">명칭</div>
                            <div class="management">관리</div>
                        </div>
                        <ul role="list" class="tbody">
                            @foreach($userGrades as $index => $grade)
                                <li role="listitem">
                                    <div class="level">
                                        <span>구분</span>
                                        <p>{{ $grade->code }}레벨</p>
                                    </div>
                                    <div class="name">
                                        <span>명칭</span>
                                        <div>
                                            <input type="text" class="common_input user-grade-name"
                                                   name="user_grades[{{ $index }}][name]"
                                                   value="{{ $grade->code_name }}"
                                                   data-original="{{ $grade->code_name }}">
                                            <input type="hidden" name="user_grades[{{ $index }}][id]" value="{{ $grade->config_id }}">
                                        </div>
                                    </div>
                                    <div class="management">
{{--                                        <span>관리</span>--}}
{{--                                        <button type="button" class="border_btn white view-permission"--}}
{{--                                                data-grade-id="{{ $grade->config_id }}">권한 보기</button>--}}
                                        <span>관리</span>
                                        <button type="button" class="border_btn white layerOpen" data-title="권한 보기" data-url="{{route('grade.getPermissions', $grade->config_id)}}">권한 보기</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn">
                        <a href="javascript:void(0);" class="border_btn cancel btn-cancel">
                            <span>취소</span>
                        </a>
                        <a href="javascript:void(0);" class="border_btn register btn-submit">
                            <span>수정</span>
                        </a>
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
        import { layerHandler } from "/src/js/components/layer.js";
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
        document.addEventListener('DOMContentLoaded', function() {
            // 수정 버튼 클릭
            document.querySelector('.btn-submit').addEventListener('click', function() {
                // 변경된 필드가 있는지 확인
                const adminGradeInputs = document.querySelectorAll('.admin-grade-name');
                const userGradeInputs = document.querySelectorAll('.user-grade-name');

                let hasChanges = false;

                adminGradeInputs.forEach(input => {
                    if (input.value !== input.dataset.original) {
                        hasChanges = true;
                    }
                });

                userGradeInputs.forEach(input => {
                    if (input.value !== input.dataset.original) {
                        hasChanges = true;
                    }
                });

                if (!hasChanges) {
                    alert('변경된 내용이 없습니다.');
                    return;
                }

                // 폼 데이터 수집
                const formData = new FormData(document.getElementById('gradeForm'));

                // AJAX 요청
                fetch('{{ route("grade.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            // 원본 값 업데이트
                            adminGradeInputs.forEach(input => {
                                input.dataset.original = input.value;
                            });
                            userGradeInputs.forEach(input => {
                                input.dataset.original = input.value;
                            });
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('등급 명칭 변경 중 오류가 발생했습니다.');
                    });
            });

            // 취소 버튼 클릭
            document.querySelector('.btn-cancel').addEventListener('click', function() {
                // 원래 값으로 되돌리기
                document.querySelectorAll('.admin-grade-name, .user-grade-name').forEach(input => {
                    input.value = input.dataset.original;
                });
            });

            {{--// 권한 보기 버튼 클릭--}}
            {{--document.querySelectorAll('.view-permission').forEach(button => {--}}
            {{--    button.addEventListener('click', function() {--}}
            {{--        const gradeId = this.getAttribute('data-grade-id');--}}
            {{--        alert("gradeId===>["+gradeId+"]");--}}
            {{--        // AJAX 요청으로 권한 정보 가져오기--}}


            {{--        // Fetch API를 사용한 AJAX 요청--}}
            {{--        fetch('{{ route("grade.getPermissions", "") }}/' + gradeId )--}}
            {{--            .then(response => response.json())--}}
            {{--            .then(data => {--}}
            {{--                if (data.success) {--}}
            {{--                    // 레이어 팝업으로 권한 정보 표시 (레이어 팝업 구현 필요)--}}
            {{--                    showPermissionsLayer(data.data.grade, data.data.permissions);--}}
            {{--                } else {--}}
            {{--                    alert(data.message);--}}
            {{--                }--}}
            {{--            })--}}
            {{--            .catch(error => {--}}
            {{--                console.error('Error:', error);--}}
            {{--                alert('권한 정보를 불러오는 중 오류가 발생했습니다.');--}}
            {{--            });--}}
            {{--    });--}}
            {{--});--}}

            {{--// 권한 정보 레이어 팝업 표시 함수 (실제 구현은 기존 레이어 팝업 시스템에 맞게 수정 필요)--}}
            {{--function showPermissionsLayer(grade, permissions) {--}}
            {{--    // 예시: 기존 레이어 팝업 시스템이 있다고 가정--}}
            {{--    layerPopup.open({--}}
            {{--        title: grade.code_name + ' 권한 보기',--}}
            {{--        content: generatePermissionsHTML(permissions)--}}
            {{--    });--}}

            {{--    console.log('Grade:', grade);--}}
            {{--    console.log('Permissions:', permissions);--}}
            {{--    alert('권한 정보 팝업은 기존 레이어 팝업 시스템에 맞게 구현해야 합니다.');--}}
            {{--}--}}

            {{--// 권한 정보 HTML 생성 함수--}}
            {{--function generatePermissionsHTML(permissions) {--}}
            {{--    let html = '<div class="permissions-container">';--}}

            {{--    for (const category in permissions) {--}}
            {{--        html += `<div class="permission-category">--}}
            {{--    <h4>${category}</h4>--}}
            {{--    <div class="permission-items">`;--}}

            {{--        const permissionData = permissions[category];--}}
            {{--        for (const action in permissionData) {--}}
            {{--            const hasPermission = permissionData[action];--}}
            {{--            html += `<div class="permission-item">--}}
            {{--        <span>${action}:</span>--}}
            {{--        <span class="permission-status ${hasPermission ? 'allowed' : 'denied'}">--}}
            {{--            ${hasPermission ? '허용' : '거부'}--}}
            {{--        </span>--}}
            {{--    </div>`;--}}
            {{--        }--}}

            {{--        html += `</div></div>`;--}}
            {{--    }--}}

            {{--    html += '</div>';--}}
            {{--    return html;--}}
            {{--}--}}
        });
    </script>
    <!-- 개발용 스크립트 E -->
@endsection
