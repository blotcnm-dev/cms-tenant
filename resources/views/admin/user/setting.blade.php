@extends('admin.layout.master')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/memberManagement.css">
@stop

@section('required-page-header-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
@stop

@section('required-page-main-content')

    <main>
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">회원가입 관리</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="mainForm" method="post">
                    @csrf
                    <div class="common_table vertical">
                        <div class="thead">
                            <div>항목명 (한글)</div>
                            <div>항목명 (영문)</div>
                            <div>사용여부</div>
                            <div>필수여부</div>
                            <div>유형</div>
                        </div>

                        <ul role="list" class="tbody">
                            @foreach($user_config as $index => $item)
                                <li role="listitem">
                                    <div>{{$item->code_name}} </div>
                                    <div>{{$item->code}}</div>

                                    <div>
                                        <label class="chk_input">
                                            <input type="checkbox"
                                                   name="use_chk[{{$item->code}}]"
                                                   value="1"
                                                {{ isset($item->use) && $item->use == 1 ? 'checked' : '' }}
                                                {{ $item->disabled ? 'disabled' : '' }}   />
                                            <span></span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="chk_input">
                                            <input type="checkbox"
                                                   name="require_chk[{{$item->code}}]"
                                                   value="1"
                                                {{ isset($item->sort) && $item->sort == 1 ? 'checked' : '' }}
                                                {{ $item->disabled ? 'disabled' : '' }}   />
                                            <span></span>
                                        </label>
                                    </div>
                                    <div> {{ $item->value_txt }} </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="white_wrap">
                        <div class="content_title_box">
                            <h3 class="title">
                                부가 설정
                            </h3>
                            <button type="button" class="fill_btn plus">
                                <span>항목 추가</span>
                            </button>
                        </div>
                        <div class="setting_list">
                            <ul role="list" id="customFieldsList">
                                @if(empty($etcData) )
                                    <!-- 기본 빈 항목 -->
                                    <li role="listitem">
                                        <button type="button" class="del_btn">삭제</button>
                                        <div class="inner_cnt">
                                            <div class="cnt_input">
                                                <div class="half">
                                                    <input type="text" class="common_input" placeholder="항목명 (한글)">
                                                    <button type="button" class="edit_btn">수정</button>
                                                </div>
                                                <div class="half">
                                                    <input type="text" class="common_input" placeholder="항목명 (영문)">
                                                    <button type="button" class="fill_btn black" id="duplicate_btn">
                                                        <span>중복체크</span>
                                                    </button>
                                                </div>
                                                <div class="half">
                                                    <div class="custom_select_1 js_custom_select">
                                                        <input type="text" class="common_input select_value" placeholder="선택" readonly data-value="">
                                                        <ul role="list">
                                                            @foreach($fieldTypeMap as $value => $text)
                                                                <li role="listitem" data-value="{{ $value }}">{{ $text }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="half">
                                                    <input type="text" class="common_input" placeholder="옵션">
                                                    <button type="button" class="edit_btn">수정</button>
                                                </div>
                                                <div class="half input_gap">
                                                    <label class="cnt_tit">사용 여부</label>
                                                    <label class="radio_input">
                                                        <input type="radio" name="plus_use_1" checked>
                                                        <span>사용</span>
                                                    </label>
                                                    <label class="radio_input">
                                                        <input type="radio" name="plus_use_1">
                                                        <span>사용안함</span>
                                                    </label>
                                                </div>
                                                <div class="half input_gap">
                                                    <label class="cnt_tit">필수 여부</label>
                                                    <label class="radio_input">
                                                        <input type="radio" name="plus_essential_1" checked>
                                                        <span>필수</span>
                                                    </label>
                                                    <label class="radio_input">
                                                        <input type="radio" name="plus_essential_1">
                                                        <span>필수아님</span>
                                                    </label>
                                                </div>
                                                <div class="half">
                                                    <input type="hidden" name="etc_no" value="1">
                                                </div>
                                            </div>
    {{--                                        <div class="common_switch">--}}
    {{--                                            <input type="checkbox" id="plus_switch_1" aria-label="활성화" checked>--}}
    {{--                                            <label for="plus_switch_1"></label>--}}
    {{--                                        </div>--}}
                                        </div>
                                    </li>
                                @else
                                    @foreach($etcData as $index => $field)
                                        <li role="listitem">
                                            <button type="button" class="del_btn" data-etc_no="{{ $field['etc_no'] }}" >삭제</button>
                                            <div class="inner_cnt">
                                                <div class="cnt_input">
                                                    <div class="half">
                                                        <input type="text" class="common_input" placeholder="항목명 (한글)" value="{{ $field['name_kr'] ?? '' }}" readonly>
    {{--                                                     <button type="button" class="edit_btn">수정</button> --}}
                                                    </div>
                                                    <div class="half">
                                                        <input type="text" class="common_input" placeholder="항목명 (영문)" value="{{ $field['name_en'] ?? '' }}" readonly>
    {{--                                                    <button type="button" class="fill_btn black"  id="duplicate_btn">--}}
    {{--                                                        <span>중복체크</span>--}}
    {{--                                                    </button>--}}
                                                    </div>
                                                    <div class="half">
                                                        <div class="custom_select_1 js_custom_select">
                                                            <input type="text" class="common_input select_value" placeholder="선택" readonly
                                                                   value="{{ $fieldTypeMap[$field['field_type'] ?? ''] ?? '' }}"
                                                                   data-value="{{ $field['field_type'] ?? '' }}">
    {{--                                                        <ul role="list">--}}
    {{--                                                            @foreach($fieldTypeMap as $value => $text)--}}
    {{--                                                                <li role="listitem" data-value="{{ $value }}">{{ $text }}</li>--}}
    {{--                                                            @endforeach--}}
    {{--                                                        </ul>--}}
                                                        </div>
                                                    </div>
                                                    <div class="half">
                                                        <input type="text" class="common_input" placeholder="옵션" value="{{ $field['options'] ?? '' }}" readonly>
    {{--                                                    <button type="button" class="edit_btn">수정</button>--}}
                                                    </div>
                                                    <div class="half input_gap">
                                                        <label class="cnt_tit">사용 여부</label>
                                                        <label class="radio_input">
                                                            <input type="radio" name="plus_use_{{ $index + 1 }}" {{ ($field['is_active'] ?? true) ? 'checked' : '' }}>
                                                            <span>사용</span>
                                                        </label>
                                                        <label class="radio_input">
                                                            <input type="radio" name="plus_use_{{ $index + 1 }}" {{ !($field['is_active'] ?? true) ? 'checked' : '' }}>
                                                            <span>사용안함</span>
                                                        </label>
                                                    </div>
                                                    <div class="half input_gap">
                                                        <label class="cnt_tit">필수 여부</label>
                                                        <label class="radio_input">
                                                            <input type="radio" name="plus_essential_{{ $index + 1 }}" {{ ($field['is_required'] ?? true) ? 'checked' : '' }}>
                                                            <span>필수</span>
                                                        </label>
                                                        <label class="radio_input">
                                                            <input type="radio" name="plus_essential_{{ $index + 1 }}" {{ !($field['is_required'] ?? true) ? 'checked' : '' }}>
                                                            <span>필수아님</span>
                                                        </label>
                                                    </div>
                                                    <div class="half">
                                                        <input type="hidden" name="etc_no" value="{{ $field['etc_no'] }}">
                                                    </div>
                                                </div>
    {{--                                            <div class="common_switch">--}}
    {{--                                                <input type="checkbox" id="plus_switch_{{ $index + 1 }}" aria-label="활성화"--}}
    {{--                                                    {{ ($field['is_enabled'] ?? true) ? 'checked' : '' }}>--}}
    {{--                                                <label for="plus_switch_{{ $index + 1 }}"></label>--}}
    {{--                                            </div>--}}
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <!-- 하단 버튼 S -->
                        <div class="common_bottom_btn">
                            <a href="{{route('user.index')}}" class="border_btn cancel">
                                <span>취소</span>
                            </a>
                            <button type="submit" class="border_btn modify" id="mainForm_submit">
                                <span>수정</span>
                            </button>
                        </div>
                        <!-- 하단 버튼 E -->
                    </div>
                </form>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>

@stop

@section('required-page-add-content')
    <template id="memberManagement">
        <li role="listitem">
            <button type="button" class="del_btn">삭제</button>

            <div class="inner_cnt">
                <div class="cnt_input">
                    <div class="half">
                        <input
                            type="text"
                            class="common_input"
                            placeholder="항목명 (한글)"
                        />
                        <button type="button" class="edit_btn">수정</button>
                    </div>
                    <div class="half">
                        <input
                            type="text"
                            class="common_input"
                            placeholder="항목명 (영문)"
                        />
                        <button type="button" class="fill_btn black"  id="duplicate_btn">
                            <span>중복체크</span>
                        </button>
                    </div>
                    <div class="half">
                        <div class="custom_select_1 js_custom_select">
                            <input
                                type="text"
                                class="common_input select_value"
                                placeholder="선택"
                                readonly
                                data-value=""
                            />
                            <ul role="list">
                                @foreach($fieldTypeMap as $value => $text)
                                    <li role="listitem" data-value="{{ $value }}">{{ $text }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="half">
                        <input type="text" class="common_input" placeholder="옵션" />
                        <button type="button" class="edit_btn">수정</button>
                    </div>
                    <div class="half input_gap">
                        <label class="cnt_tit">사용 여부</label>
                        <label class="radio_input">
                            <input type="radio" name="plus_use" checked />
                            <span>사용</span>
                        </label>
                        <label class="radio_input">
                            <input type="radio" name="plus_use" />
                            <span>사용안함</span>
                        </label>
                    </div>
                    <div class="half input_gap">
                        <label class="cnt_tit">필수 여부</label>
                        <label class="radio_input">
                            <input type="radio" name="plus_essential" checked />
                            <span>필수</span>
                        </label>
                        <label class="radio_input">
                            <input type="radio" name="plus_essential" />
                            <span>필수아님</span>
                        </label>
                    </div>
                    <div class="half">
                        <input type="hidden" name="etc_no" value="">
                    </div>
                </div>
{{--                <div class="common_switch">--}}
{{--                    <input--}}
{{--                        type="checkbox"--}}
{{--                        id="plus_switch"--}}
{{--                        aria-label="활성화"--}}
{{--                        checked--}}
{{--                    />--}}
{{--                    <label for="plus_switch"></label>--}}
{{--                </div>--}}
            </div>
        </li>
    </template>
    <script type="module">
        //import { gnbHandler } from "/src/js/navigation/gnbClassController.js";
        import { listManagement } from "/src/js/dev/listManagement.js";
        //gnbHandler(1, 1);

        document.addEventListener("DOMContentLoaded", () => {
            const $container = document.querySelector('.setting_list ul');
            $container.addEventListener('click', (e)=> {
                const $delBtn = e.target.closest('.del_btn');
                const etcNo = $delBtn?.getAttribute("data-etc_no");
                const $target = e.target.closest("li, .draggable");
                if (etcNo) {
                    // handleItemDelete($target, etcNo, $container, e);


                    if (handleItemDelete($target, etcNo, $container) === false) {
                        // 사용자가 취소했을 경우 이벤트 처리를 중단합니다
                        e.preventDefault();
                        e.stopPropagation(); // 이벤트 버블링 중단
                        return; // 이벤트 핸들러 함수 종료
                    }
                }
            }, true);

            // 항목 추가, 삭제
            listManagement({
                btnSelector: ".content_title_box .plus",
                templateId: "memberManagement",
                containerSelector: ".setting_list ul",
                switchPrefix: "plus_switch",
            });
        });
    </script>
    <!-- 개발용 스크립트 S -->
    <script>

        function handleItemDelete(target, etcNo, container) {
            const confirmDelete = confirm("이 항목을 삭제하시겠습니까?\n※ 등록된 회원정보가 있다면 데이터가 영구적으로 삭제됩니다.");

            if (!confirmDelete) {
                return false;
            }

            target.remove();
            const newFormData = new FormData();
            const updatedFormData = collectCustomFieldsData(newFormData);
            // 유효성 검사 실패 시 제출하지 않음
            if (!updatedFormData) {
                event.preventDefault();
                restore_button(mainForm_submit);
                return;
            }
            updatedFormData.append('etc_no', etcNo);
            // updatedFormData.forEach((value, key) => {
            //     console.log(key + ":" + value);
            // });

            // const etcId = etcNo;
            {{--//const url = `{{ route('user.etc_del', ['id' => '__ID__']) }}`.replace('__ID__', etcId);--}}
            const url = `{{ route('user.etc_del') }}`;
            $.ajax({
                url: url,
                data: updatedFormData,
                type: 'post',
                dataType: 'json',
                processData: false,  // 중요: FormData를 처리하지 않도록 설정
                contentType: false,  // 중요: 컨텐트 타입을 자동으로 설정하지 않도록 함
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',   // 헤더 이름 수정
                    'X-HTTP-Method-Override': 'DELETE'      // 서버에 DELETE 의도를 알림
                },
                success: function (res) {
                    //console.log(res);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('status: ' + jqXHR.status);
                    console.log('textStatus: ' + textStatus);
                    console.log('errorThrown: ' + errorThrown);
                },
                complete: function (jqXHR, textStatus) {
                    // 필요한 경우 주석 해제
                    // location.reload();
                    // restore_button(mainForm_submit);
                }
            });
            {{--// $.ajax({--}}
            {{--//     url: `{{route('user.etc_del')}}`,--}}
            {{--//     type: 'DELETE',--}}
            {{--//     dataType: 'json',--}}
            {{--//     headers: {--}}
            {{--//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')--}}
            {{--//     },--}}
            {{--//     success: function(response) {--}}
            {{--//         // 삭제 성공 시 UI에서 요소 제거--}}
            {{--//         target.remove();--}}
            {{--//--}}
            {{--//         // 성공 메시지 표시 (선택적)--}}
            {{--//         if (response && response.message) {--}}
            {{--//             alert(response.message);--}}
            {{--//         } else {--}}
            {{--//             console.log(`ID: ${etcNo} 항목이 성공적으로 삭제되었습니다.`);--}}
            {{--//         }--}}
            {{--//--}}
            {{--//         // 목록 번호 재정렬 (필요한 경우)--}}
            {{--//         reorderItems(container);--}}
            {{--//     },--}}
            {{--//     error: function(xhr, status, error) {--}}
            {{--//         // 오류 처리--}}
            {{--//         console.error("삭제 중 오류 발생:", error);--}}
            {{--//--}}
            {{--//         // 사용자에게 오류 알림--}}
            {{--//         let errorMsg = "데이터 삭제 중 오류가 발생했습니다.";--}}
            {{--//         if (xhr.responseJSON && xhr.responseJSON.message) {--}}
            {{--//             errorMsg = `삭제 실패: ${xhr.responseJSON.message}`;--}}
            {{--//         }--}}
            {{--//         alert(errorMsg);--}}
            {{--//     }--}}
            {{--// });--}}
            {{--//--}}
        }

        function disable_button(element) {
            element.disabled = true;
            element.classList.add('loading');
        }

        function restore_button(element) {
            element.disabled = false;
            element.classList.remove('loading');
        }
        function collectCustomFieldsData(formData) {
            const customFields = [];
            const fieldElements = document.querySelectorAll('#customFieldsList > li');
            let isValid = true;
            let errorMessage = '';
            let settingNum = 0;

            fieldElements.forEach(function(element, index) {
                // 필수 입력 필드 가져오기
                const nameKrInput = element.querySelector('.half:nth-child(1) input');
                const nameEnInput = element.querySelector('.half:nth-child(2) input');
                const selectInput = element.querySelector('.select_value');

                // 다른 필드들 가져오기
                const optionsInput = element.querySelector('.half:nth-child(4) input');
                const useRadios = element.querySelectorAll('.half:nth-child(5) .radio_input input');
                const requireRadios = element.querySelectorAll('.half:nth-child(6) .radio_input input');
                const enableSwitch = element.querySelector('.common_switch input[type="checkbox"]');

                const uniqNo = element.querySelector('.half:nth-child(7) input');
                const order  = uniqNo.value.trim();

                //console.log("순서--->["+order+"]");

                // 설정 번호 가져오기 (오류 메시지용)
                settingNum = index+1;

                // 필수 입력 필드 유효성 검사
                if (!nameKrInput.value.trim()) {
                    isValid = false;
                    errorMessage += `${settingNum}번째 부가설정의 항목명(한글)을 입력해주세요.\n`;
                    nameKrInput.classList.add('error');
                }

                if (!nameEnInput.value.trim()) {
                    isValid = false;
                    errorMessage += `${settingNum}번째 부가설정의 항목명(영문)을 입력해주세요.\n`;
                    nameEnInput.classList.add('error');
                }

                if (!selectInput.dataset.value) {
                    isValid = false;
                    errorMessage += `${settingNum}번째 부가설정의 필드 유형을 선택해주세요.\n`;
                    selectInput.classList.add('error');
                }

                // 데이터가 유효한 경우에만 객체에 추가
                customFields.push({
                    name_kr: nameKrInput.value.trim(),
                    name_en: nameEnInput.value.trim(),
                    field_type: selectInput ? selectInput.dataset.value : '',
                    options: optionsInput ? optionsInput.value.trim() : '',
                    is_active: useRadios && useRadios[0].checked,
                    is_required: requireRadios && requireRadios[0].checked,
                    is_enabled: enableSwitch ? enableSwitch.checked : true,
                    etc_no: order
                });
            });

            // 유효성 검사 실패 시 처리
            if (!isValid) {
                alert(errorMessage);
                return false; // 유효하지 않음을 반환
            }


            const baseFields = [
                "user_id", "password", "user_name", "phone", "email",
                "gender", "nick_name", "birthday_date", "profile_image"
            ];

            const customFieldNames = customFields.map(field => field.name_en);
            const allFieldNames = [...baseFields, ...customFieldNames];
            const uniqueFieldNames = new Set(allFieldNames);
            if (allFieldNames.length !== uniqueFieldNames.size) {
                // 중복 항목 찾기
                const duplicates = allFieldNames.filter((name, index) =>
                    allFieldNames.indexOf(name) !== index
                );

                alert(`중복된 영문명이 있습니다: ${duplicates.join(', ')}`);
                return false;
            }


            // 모든 검증 통과 - JSON 문자열로 변환하여 formData에 추가
            formData.append('custom_fields', JSON.stringify(customFields));

            return formData;
        }


        document.addEventListener("DOMContentLoaded", function() {

            document.getElementById('mainForm').addEventListener('submit', function (e) {

                e.preventDefault();
                e.stopPropagation();
                disable_button(mainForm_submit);

                //form submit
                let frm = document.forms['mainForm'];
                let formData = new FormData(frm);

                const updatedFormData = collectCustomFieldsData(formData);
                // 유효성 검사 실패 시 제출하지 않음
                if (!updatedFormData) {
                    restore_button(mainForm_submit);
                    return false;
                }
                // updatedFormData.forEach((value, key) => {
                //     console.log(key + ":" + value);
                // });

                // AJAX 요청의 URL을 변수에 저장
                const url = '{{ route('user.store') }}';
                $.ajax({
                    url: url,
                    data: formData,
                    type: 'post',
                    dataType: 'json',
                    processData: false,  // 중요: FormData를 처리하지 않도록 설정
                    contentType: false,  // 중요: 컨텐트 타입을 자동으로 설정하지 않도록 함
                    success: function (res) {
                        alert(res.msg);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log('status : ' + jqXHR.status);
                        console.log('textStatus : ' + textStatus);
                    },
                    complete: function (jqXHR, textStatus) {
                        //location.reload();
                        restore_button(mainForm_submit);
                    }
                });
            });

            // 이벤트 리스너 설정
            document.addEventListener('click', function(e) {

                if (e.target.closest('#duplicate_btn') || e.target.id === 'duplicate_btn') {
                    e.preventDefault();
                    const button = e.target.closest('#duplicate_btn') || e.target;
                    const input = button.previousElementSibling;
                    checkDuplicateEnName(input);
                }


                if (e.target.classList.contains('edit_btn')) {
                    const parentDiv = e.target.closest('div');
                    const inputElement = parentDiv.querySelector('input');

                    if (inputElement) {
                        if (inputElement.hasAttribute('readonly')) {
                            inputElement.removeAttribute('readonly');
                            inputElement.focus();
                        } else {
                            inputElement.setAttribute('readonly', true);
                        }
                    }
                }
            });

        });



        // 중복 체크 함수
        function checkDuplicateEnName(input) {
            const englishName = input.value.trim();

            if (!englishName) {
                alert('항목명(영문)을 입력해주세요.');
                return false;
            }

            const baseFields = [
                "user_id", "password", "user_name", "phone", "email",
                "gender", "nick_name", "birthday_date", "profile_image"
            ];
            const currentEnglishName = input.value.trim();

            const otherCustomFieldNames = [...document.querySelectorAll('#customFieldsList .half:nth-child(2) input')]
                .filter(field => field !== input)
                .map(field => field.value.trim());
            const allFieldNames = [...baseFields, ...otherCustomFieldNames, currentEnglishName];
            const uniqueFieldNames = new Set(allFieldNames);
            if (allFieldNames.length !== uniqueFieldNames.size) {
                const isDuplicate = baseFields.includes(currentEnglishName) ||
                    otherCustomFieldNames.includes(currentEnglishName);

                if (isDuplicate) {
                    alert('중복된 영문명이 있습니다.');
                    input.focus();
                    return false;
                }
            }

            alert('사용 가능한 영문명입니다.');
            // 중복 체크 통과 시 readonly 속성 추가
            input.setAttribute('readonly', true);
            return true;
        }

    </script>
@stop
