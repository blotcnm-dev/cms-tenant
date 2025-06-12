@extends('admin.layout.master')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/siteManagement.css">
@stop

@section('required-page-header-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
        <!-- 페이지 타이틀 S -->
        <div class="page_title">
            <h2 class="title">사이트 설정</h2>
        </div>
        <!-- 페이지 타이틀 E -->
        <!-- 컨텐츠 S -->
        <div class="container">
            <form id="mainForm" method="post" enctype="multipart/form-data" class="max_width">
                @csrf
                <div class="content_title_box no_mg">
                    <h3 class="title">홈페이지 설정</h3>
                </div>
                <div class="input_box gray_box">
                    <div class="input_item half">
                        <label class="input_title" for="home_name_kr">홈페이지 한글명</label>
                        <div class="inner_box">
                            <input type="text" class="common_input" id="home_name_kr" name="home_name_kr"
                                   value="{{ $site_config->where('code', 'home_name_kr')->first()->value ?? '' }}"
                                   placeholder="홈페이지 한글명을 입력하세요">
                        </div>
                    </div>
                    <div class="input_item half">
                        <label class="input_title" for="home_name_en">홈페이지 영문명</label>
                        <div class="inner_box">
                            <input type="text" class="common_input" id="home_name_en" name="home_name_en"
                                   value="{{ $site_config->where('code', 'home_name_en')->first()->value ?? '' }}"
                                   placeholder="홈페이지 영문명을 입력하세요">
                        </div>
                    </div>
                    <div class="input_item">
                        <label class="input_title" for="domain">도메인</label>
                        <div class="inner_box">
                            <input type="text" class="common_input" id="domain" name="domain"
                                   value="{{ $site_config->where('code', 'domain')->first()->value ?? '' }}"
                                   placeholder="도메인명을 입력하세요">
                        </div>
                    </div>

                    @php
                        $favicon = $site_config->where('code', 'favicon')->first();
                        $fileName = '';
                        $fileSize = '0';
                        $formattedSize = '0 KB';
                        $imageUrl = '';
                        if ($favicon && $favicon->value) {

                            $fileName = $favicon->value; // 데이터베이스에 저장된 파일명
                            $filePath = storage_path('app/public/site/' . $fileName); // 실제 서버 경로

                            if (file_exists($filePath)) {
                                $fileSize = filesize($filePath);
                                $formattedSize = round($fileSize / 1024, 2) . ' KB';
                                $imageUrl = asset('storage/site/' . $fileName);
                            }
                        }
                        //echo json_encode($favicon);
                    @endphp
                    <div class="input_item">
                        <label class="input_title" for="favicon_file">파비콘</label>

                        <div class="inner_box">
                            <div class="uploadFile_box">
                                <input type="file" id="favicon_file" name="favicon_file" value="{{ $fileName }}">
                                <div class="type_txt">
                                    <label class="fill_btn plus add_file" for="favicon_file" id="favicon_add_btn">
                                        <span>파일 첨부</span>
                                    </label>
                                </div>
                                <div class="type_img"  id="preViewBox" style="display: {{ ($favicon && $favicon->value)  ? 'block' : 'none' }} ;">
                                    <div class="txt_box" >
                                        <div class="info_box">
                                            <p class="name">{{ $fileName }}</p>
                                            <span class="capacity">{{ $formattedSize }}</span>
                                            <button type="button" class="del_btn" data-target="favicon">삭제</button>
                                        </div>
                                    </div>
                                    <div class="img_box">
                                        <img src="{{ $imageUrl }}" alt="file.jpg" id="preViewImg">
                                        <button type="button" class="del_btn" data-target="favicon">삭제</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input_item">
                        <label class="input_title">
                            <div class="information">
                                메타 태그
                                <div class="a_box"></div>
                            </div>
                        </label>
                        <div class="inner_box">
                            <input type="text" class="common_input" name="meta-title"
                                              value="{{ $site_config->where('code', 'meta-title')->first()->value ?? '' }}"
                                              placeholder="meta-title 웹 페이지의 제목을 나타냅니다.">
                            <input type="text" class="common_input" name="meta-desc"
                                              value="{{ $site_config->where('code', 'meta-desc')->first()->value ?? '' }}"
                                              placeholder="meta-desc 웹 페이지의 내용을 간략하게 요약하여 보여줍니다.">
                            <input type="text" class="common_input" name="meta-keyword"
                                              value="{{ $site_config->where('code', 'meta-keyword')->first()->value ?? '' }}"
                                              placeholder="meta-keyword 웹 페이지의 주요 검색어들을 나열합니다.">
                            <input type="text" class="common_input" name="meta-author"
                                              value="{{ $site_config->where('code', 'meta-author')->first()->value ?? '' }}"
                                              placeholder="meta-author 웹 페이지의 저작자를 명시합니다.">
                         </div>
                    </div>

                    <div class="input_item">
                        <label class="input_title">
                            <div class="information">
                                구글 스크립트 (GTM)
                                <div class="a_box"></div>
                            </div>
                        </label>
                        <div class="inner_box">
                            <textarea  class="common_textarea" name="gtm_head" id="gtm_head"
                                      placeholder="구글 분석 코드입력합니다. head코드 내 ">
                                {{ $site_config->where('code', 'gtm-head')->first()->value ?? '' }}</textarea>
                            <textarea  class="common_textarea" name="gtm_body" id="gtm_body"
                                      placeholder="구글 분석 코드입력합니다. body 내 ">
                                {{ $site_config->where('code', 'gtm-body')->first()->value ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="input_item">
                        <label class="input_title">
                            <div class="information">
                                구글 스크립트 (애널리틱스)
                                <div class="a_box"></div>
                            </div>
                        </label>
                        <div class="inner_box">
                            <textarea  class="common_textarea" name="gta_head" id="gta_head"
                                       placeholder="구글 분석 코드입력합니다. body 내 ">
                                {{ $site_config->where('code', 'gta-head')->first()->value ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="content_title_box">
                    <h3 class="title">푸터 설정</h3>
                    <button type="button" class="fill_btn blue plus footer_plus">
                        <span>항목 추가</span>
                    </button>
                </div>
                <div class="dragzone" id="footerList">
                    @if(empty($footerData))
                        <!-- 기본 빈 항목 -->
                        <div class="draggable">
                            <button type="button" class="del_btn">삭제</button>
                            <div class="inner_cnt">
                                <div class="w-300">
                                    <input type="text" class="common_input" placeholder="구분">
                                    <button type="button" class="edit_btn">수정</button>
                                </div>
                                <div>
                                    <input type="text" class="common_input" placeholder="내용">
                                    <button type="button" class="edit_btn">수정</button>
                                </div>
                                <div class="common_switch">
                                    <input type="checkbox" id="footer_switch_0" class="footer-active" aria-label="활성화">
                                    <label for="footer_switch_0"></label>
                                </div>
                            </div>
                        </div>
                    @else
                        @foreach($footerData as $index => $item)
                            <div class="draggable">
                                <button type="button" class="del_btn">삭제</button>
                                <div class="inner_cnt">
                                    <div class="w-300">
                                        <input type="text" class="common_input" placeholder="구분" value="{{ $item['title'] ?? '' }}" readonly>
                                        <button type="button" class="edit_btn">수정</button>
                                    </div>
                                    <div>
                                        <input type="text" class="common_input" placeholder="내용" value="{{ $item['content'] ?? '' }}" readonly>
                                        <button type="button" class="edit_btn">수정</button>
                                    </div>
                                    <div class="common_switch">
                                        <input type="checkbox" id="footer_switch_{{ $index }}" class="footer-active" aria-label="활성화" {{ !empty($item['active']) ? 'checked' : '' }}>
                                        <label for="footer_switch_{{ $index }}"></label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="content_title_box">
                    <h3 class="title">소셜 미디어 설정</h3>
                    <button type="button" class="fill_btn plus blue sns_plus">
                        <span>항목 추가</span>
                    </button>
                </div>
                <div class="dragzone" id="snsList">
                    @if(empty($snsData))
                        <!-- 기본 빈 항목 -->
                        <div class="draggable">
                            <button type="button" class="del_btn">삭제</button>
                            <div class="inner_cnt">
                                <div class="w-300">
                                    <input type="text" class="common_input" placeholder="SNS 이름">
                                    <button type="button" class="edit_btn">수정</button>
                                </div>
                                <div>
                                    <input type="text" class="common_input" placeholder="링크">
                                    <button type="button" class="edit_btn">수정</button>
                                </div>
                                <div class="common_switch">
                                    <input type="checkbox" id="sns_switch_0" class="sns-active" aria-label="활성화">
                                    <label for="sns_switch_0"></label>
                                </div>
                            </div>
                        </div>
                    @else
                        @foreach($snsData as $index => $item)
                            <div class="draggable">
                                <button type="button" class="del_btn">삭제</button>
                                <div class="inner_cnt">
                                    <div class="w-300">
                                        <input type="text" class="common_input" placeholder="SNS 이름" value="{{ $item['name'] ?? '' }}" readonly>
                                        <button type="button" class="edit_btn">수정</button>
                                    </div>
                                    <div>
                                        <input type="text" class="common_input" placeholder="링크" value="{{ $item['link'] ?? '' }}" readonly>
                                        <button type="button" class="edit_btn">수정</button>
                                    </div>
                                    <div class="common_switch">
                                        <input type="checkbox" id="sns_switch_{{ $index }}" class="sns-active" aria-label="활성화" {{ !empty($item['active']) ? 'checked' : '' }}>
                                        <label for="sns_switch_{{ $index }}"></label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="content_title_box">
                    <h3 class="title">간편 로그인 설정</h3>
                    <button type="button" class="fill_btn blue plus login_plus">
                        <span>항목 추가</span>
                    </button>
                </div>
                <div class="dragzone" id="loginList">
                    @if(empty($loginData))
{{--                        <!-- 기본 빈 항목 -->--}}
{{--                        <div class="draggable">--}}
{{--                            <button type="button" class="del_btn">삭제</button>--}}
{{--                            <div class="inner_cnt login">--}}
{{--                                <div class="flex">--}}
{{--                                    <div class="item">--}}
{{--                                        <div>--}}
{{--                                            <input type="text" class="common_input" placeholder="미디어명 (한글)">--}}
{{--                                            <button type="button" class="edit_btn">수정</button>--}}
{{--                                        </div>--}}
{{--                                        <div>--}}
{{--                                            <input type="text" class="common_input" placeholder="미디어명 (영문)">--}}
{{--                                            <button type="button" class="edit_btn">수정</button>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="item">--}}
{{--                                        <div>--}}
{{--                                            <input type="text" class="common_input" placeholder="Client ID">--}}
{{--                                            <button type="button" class="edit_btn">수정</button>--}}
{{--                                        </div>--}}
{{--                                        <div>--}}
{{--                                            <input type="text" class="common_input" placeholder="Client Secret">--}}
{{--                                            <button type="button" class="edit_btn">수정</button>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="item">--}}
{{--                                        <div>--}}
{{--                                            <input type="text" class="common_input" placeholder="Redirect URL">--}}
{{--                                            <button type="button" class="edit_btn">수정</button>--}}
{{--                                        </div>--}}
{{--                                        <div>--}}
{{--                                            <input type="text" class="common_input" placeholder="API Key">--}}
{{--                                            <button type="button" class="edit_btn">수정</button>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="common_switch">--}}
{{--                                    <input type="checkbox" id="login_0" class="login-active" aria-label="활성화">--}}
{{--                                    <label for="login_0"></label>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    @else
                        @foreach($loginData as $index => $item)
                            <div class="draggable">
                                <button type="button" class="del_btn">삭제</button>
                                <div class="inner_cnt login">
                                    <div class="flex">
                                        <div class="item">
                                            <div>
                                                <input type="text" class="common_input" placeholder="미디어명 (한글)" value="{{ $item['korName'] ?? '' }}" readonly>
                                                <button type="button" class="edit_btn">수정</button>
                                            </div>
                                            <div>
                                                <input type="text" class="common_input" placeholder="미디어명 (영문)" value="{{ $item['engName'] ?? '' }}" readonly>
                                                <button type="button" class="edit_btn">수정</button>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div>
                                                <input type="text" class="common_input" placeholder="Client ID" value="{{ $item['clientId'] ?? '' }}" readonly>
                                                <button type="button" class="edit_btn">수정</button>
                                            </div>
                                            <div>
                                                <input type="text" class="common_input" placeholder="Client Secret" value="{{ $item['clientSecret'] ?? '' }}" readonly>
                                                <button type="button" class="edit_btn">수정</button>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div>
                                                <input type="text" class="common_input" placeholder="Redirect URL" value="{{ $item['redirectUrl'] ?? '' }}" readonly>
                                                <button type="button" class="edit_btn">수정</button>
                                            </div>
                                            <div>
                                                <input type="text" class="common_input" placeholder="API Key" value="{{ $item['apiKey'] ?? '' }}" readonly>
                                                <button type="button" class="edit_btn">수정</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="common_switch">
                                        <input type="checkbox" id="login_{{ $index }}" class="login-active" aria-label="활성화" {{ !empty($item['active']) ? 'checked' : '' }}>
                                        <label for="login_{{ $index }}"></label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="content_title_box">
                    <h3 class="title">금칙어 설정</h3>
                </div>
                <div class="input_box gray_box forbid_box">
                    <div class="input_item">
                        <label class="input_title" for="forbid">금칙어 등록</label>
                        <div class="inner_box flex with_btn">
                            <input type="text" class="common_input" id="forbid" placeholder="금칙어를 등록해주세요">
                            <button type="button" class="fill_btn black mob_border plus">
                                <span>금칙어 추가</span>
                            </button>
                            <span class="noti">하나의 금칙어는 최대 10자(20byte)까지 등록이 가능하며, 여러 개 등록시 콤마(,)로 구분해 등록 해주세요.</span>
                        </div>
                    </div>
                    <div class="input_item center">
                        <label class="input_title" for="forbid">등록 된 금칙어</label>
                        <div class="inner_box">
                            <ul role="list" class="forbid_list">
                                @if(empty($forbidData) || empty($forbidData['words']))
                                    <!-- 기본 데이터가 없는 경우 빈 리스트 -->
                                @else
                                    @foreach($forbidData['words'] as $index => $item)
                                        <li role="listitem">
                                            <span>{{ $item['word'] ?? '' }}</span>
                                            <button type="button" class="del_btn">삭제</button>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
{{--                    <div class="input_item">--}}
{{--                        <label class="input_title" for="forbid">사용 여부</label>--}}
{{--                        <div class="inner_box flex gap_input">--}}
{{--                            <label class="radio_input">--}}
{{--                                <input type="radio" id="forbid_1" name="forbid_radio" {{ ($forbidData['active'] == '') ? ($forbidData['active'] ? 'checked' : '') : 'checked' }}>--}}
{{--                                <span>사용</span>--}}
{{--                            </label>--}}
{{--                            <label class="radio_input">--}}
{{--                                <input type="radio" id="forbid_2" name="forbid_radio" {{ ($forbidData['active']== '') ? (!$forbidData['active'] ? 'checked' : '') : '' }}>--}}
{{--                                <span>사용안함</span>--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>

                <!-- 하단 버튼 S -->
                <div class="common_bottom_btn">
                    <a href="{{ route('site.index') }}" class="border_btn cancel">
                        <span>취소</span>
                    </a>
                    <button class="border_btn save" id="mainForm_submit">
                        <span>저장</span>
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

    <template id="footerSetting">
        <div class="draggable">
            <button type="button" class="del_btn">삭제</button>
            <div class="inner_cnt">
                <div class="w-300">
                    <input type="text" class="common_input" placeholder="구분">
                    <button type="button" class="edit_btn">수정</button>
                </div>
                <div>
                    <input type="text" class="common_input" placeholder="내용">
                    <button type="button" class="edit_btn">수정</button>
                </div>
                <div class="common_switch">
                    <input type="checkbox" id="footer_switch" aria-label="활성화" checked>
                    <label for="footer_switch"></label>
                </div>
            </div>
        </div>
    </template>

    <template id="snsSetting">
        <div class="draggable">
            <button type="button" class="del_btn">삭제</button>
            <div class="inner_cnt">
                <div class="w-300">
                    <input type="text" class="common_input" placeholder="SNS 이름">
                    <button type="button" class="edit_btn">수정</button>
                </div>
                <div>
                    <input type="text" class="common_input" placeholder="링크">
                    <button type="button" class="edit_btn">수정</button>
                </div>
                <div class="common_switch">
                    <input type="checkbox" id="sns_switch" aria-label="활성화">
                    <label for="sns_switch"></label>
                </div>
            </div>
        </div>
    </template>

    <template id="loginSetting">
        <div class="draggable">
            <button type="button" class="del_btn">삭제</button>
            <div class="inner_cnt login">
                <div class="flex">
                    <div class="item">
                        <div>
                            <input type="text" class="common_input" placeholder="미디어명 (한글)">
                            <button type="button" class="edit_btn">수정</button>
                        </div>
                        <div>
                            <input type="text" class="common_input" placeholder="미디어명 (영문)">
                            <button type="button" class="edit_btn">수정</button>
                        </div>
                    </div>
                    <div class="item">
                        <div>
                            <input type="text" class="common_input" placeholder="Client ID">
                            <button type="button" class="edit_btn">수정</button>
                        </div>
                        <div>
                            <input type="text" class="common_input" placeholder="Client Secret">
                            <button type="button" class="edit_btn">수정</button>
                        </div>
                    </div>
                    <div class="item">
                        <div>
                            <input type="text" class="common_input" placeholder="Redirect URL">
                            <button type="button" class="edit_btn">수정</button>
                        </div>
                        <div>
                            <input type="text" class="common_input" placeholder="API Key">
                            <button type="button" class="edit_btn">수정</button>
                        </div>
                    </div>
                </div>
                <div class="common_switch">
                    <input type="checkbox" id="login" aria-label="활성화">
                    <label for="login"></label>
                </div>
            </div>
        </div>
    </template>

    <script type="module">
        import { listManagement } from "/src/js/components/listManagement.js";

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.dragzone').forEach((zone, index) => {
                new Sortable(zone, {
                    group: 'group-' + index,
                    animation: 150,
                    ghostClass: 'dragging'
                });
            });

            // 항목 추가, 삭제
            [
                {
                    btnSelector: '.content_title_box .footer_plus',
                    templateId: 'footerSetting',
                    containerSelector: '#footerList',
                    switchPrefix: 'footer_switch'
                },
                {
                    btnSelector: '.content_title_box .sns_plus',
                    templateId: 'snsSetting',
                    containerSelector: '#snsList',
                    switchPrefix: 'sns_switch'
                },
                {
                    btnSelector: '.content_title_box .login_plus',
                    templateId: 'loginSetting',
                    containerSelector: '#loginList',
                    switchPrefix: 'login'
                },
            ].forEach(config => listManagement(config));

            // 금칙어 단어 추가, 삭제
            const $forbidBox = document.querySelector('.forbid_box');
            const $addForbidBtn = $forbidBox.querySelector('.fill_btn.plus');
            const $forbidInput = $forbidBox.querySelector('input[type="text"]');
            const $forbidList = $forbidBox.querySelector('.forbid_list');
            // const $forbidNum = $forbidBox.querySelector('.input_item.center .input_title span');
            // const updateNum =()=> {
            //     const nowListnum = $forbidList.querySelectorAll('li').length;
            //     $forbidNum.innerHTML = nowListnum;
            // }
            // updateNum();

            $addForbidBtn.addEventListener('click', ()=> {
                const value = $forbidInput.value.trim();
                const currentCount = $forbidList.querySelectorAll('li').length;

                if (!value) {
                    alert('등록하실 금칙어를 입력해주세요.');
                    return;
                }

                if (currentCount >= 100) {
                    alert('최대 100개까지만 등록 가능합니다.');
                    return;
                }

                // 쉼표로 구분하여 배열로 변환
                const forbidWords = value.split(',').map(word => word.trim()).filter(word => word);



                // 각 금칙어마다 개별 항목 생성
                forbidWords.forEach(word => {
                    if (word.length > 10) {
                        alert(`"${word}"는 10자(20byte)를 초과합니다. 10자 이내로 입력해주세요.`);
                        return;
                    }

                    const forbidSpan = document.createElement('li');
                    forbidSpan.innerHTML = `
                            <span>${word}</span>
                            <button type="button" class="del_btn">삭제</button>
                        `;
                    $forbidList.appendChild(forbidSpan);
                });



                // const forbidSpan = document.createElement('li');
                // forbidSpan.innerHTML = `
                //         <span>${value}</span>
                //         <button type="button" class="del_btn">삭제</button>
                //     `
                // $forbidList.appendChild(forbidSpan);
                $forbidInput.value = '';
                //updateNum();
            });

            $forbidList.addEventListener('click', (e)=> {
                const target = e.target.closest('.del_btn');
                const targetLi = e.target.closest('li');

                if (target && targetLi) {
                    targetLi.remove();
                    //updateNum();
                }
            });

        });
    </script>

    <!-- 개발용 스크립트 S -->
    <script>

        //파비콘 추가 할 때
        const $favicon_file = document.getElementById('favicon_file');
        $favicon_file.addEventListener('change', handleFileSelect);

        // 파일 선택 처리
        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
            // 같은 파일을 다시 선택할 수 있도록 value 초기화
            //$favicon_file.value = '';
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
            const preViewBox = document.getElementById('preViewBox');
            const preViewImg = document.getElementById('preViewImg');
            reader.onload = function(e) {
                const img_src = e.target.result;
                preViewImg.src = img_src;

                const nameElement = document.querySelector('.info_box .name');
                const capacityElement = document.querySelector('.info_box .capacity');
                nameElement.textContent = file.name;
                capacityElement.textContent = `(${formatFileSize(file.size)})`;
                preViewBox.style = 'display:block';
                document.getElementById('favicon_add_btn').style="display:none";
            };
            // 파일 읽기 시작
            reader.readAsDataURL(file);
            document.getElementById('favicon_file').dataset.hasFile = 'true';
        }

        // 파일 크기 포맷팅
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = 1; //Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }


        //파비콘 이미지 삭제
        const faviconDeleteButtons = document.querySelectorAll('.del_btn[data-target="favicon"]');
        faviconDeleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {

                if(confirm('파비콘을 삭제하시겠습니까?1')) {
                    const target = $(this).data('target');

                    // Ajax 요청으로 파비콘 삭제
                    $.ajax({
                        url: '{{ route("site.favicon.delete") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            target: target
                        },
                        dataType: 'json',
                        success: function(res) {
                            if(res.success) {

                                // 파비콘 미리보기 초기화
                                const previewImg = document.getElementById('preViewImg');
                                const preViewBox = document.getElementById('preViewBox');
                                if (previewImg) {
                                    previewImg.src = '';
                                }

                                const favicon_file = document.getElementById('favicon_file');
                                const nameElement = document.querySelector('.info_box .name');
                                const capacityElement = document.querySelector('.info_box .capacity');

                                if (favicon_file) favicon_file.value = '';
                                if (nameElement) nameElement.textContent = '';
                                if (capacityElement) capacityElement.textContent = '';

                                preViewBox.style="display:none";
                                document.getElementById('favicon_add_btn').style="display:block";
                            } else {
                                // 실패 시 오류 메시지 표시
                                alert(res.msg);
                            }
                        },
                        error: function() {
                            alert('요청 처리 중 오류가 발생했습니다.');
                        }
                    });
                }


            });
        });


        document.addEventListener("DOMContentLoaded", function() {


            document.addEventListener('click', function(e) {
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

            document.getElementById('mainForm').addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();
                disable_button(mainForm_submit);

                //form submit
                let frm = document.forms['mainForm'];
                let formData = new FormData(frm);
                const fileInput = document.getElementById('favicon_file');
                if (fileInput.files.length > 0) {
                    console.log('제출 직전 파일 확인:', fileInput.files[0].name);
                } else {
                    console.log('제출할 파일 없음');
                }

                // 푸터 데이터 수집
                formData = collectFooterData(formData)
                // 소셜미디어 데이터 수집
                formData = collectSnsData(formData)
                // 간편로그인  데이터 수집
                formData = collectLoginData(formData)
                // 금칙어 데이터 추가
                formData = collectForbidData(formData);

                // formData.forEach((value, key) => {
                //     console.log(key + ":" + value);
                // });

                // AJAX 요청의 URL을 변수에 저장
                const url = '{{ route('site.store') }}';
                $.ajax({
                    url: url,
                    data: formData,
                    type: 'post',
                    dataType: 'json',
                    processData: false,  // 중요: FormData를 처리하지 않도록 설정
                    contentType: false,  // 중요: 컨텐트 타입을 자동으로 설정하지 않도록 함
                    success: function (res) {

                        if (res.success) {
                            alert(res.msg);
                            // 성공 메시지 표시
                            //console.log(res.msg);
                        } else {
                            alert(res.msg);
                            // 전체 오류 메시지 표시
                            //console.log(res.msg);

                            // 세부 오류 메시지 처리
                            if (res.details) {
                                // 각 섹션별 오류 메시지 처리
                                Object.keys(res.details).forEach(function(section) {
                                    const sectionResult = res.details[section];

                                    if (!sectionResult.success) {
                                        // 특정 섹션에 오류가 있는 경우
                                        console.log(section + ' 오류:', sectionResult.msg);

                                        // 유효성 검사 오류가 있는 경우 (파비콘 등)
                                        if (sectionResult.errors) {
                                            Object.values(sectionResult.errors).forEach(function(error) {
                                                console.log(error[0]); // 첫 번째 오류 메시지만 표시
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log('status : ' + jqXHR.status);
                        console.log('textStatus : ' + textStatus);
                    },
                    complete: function (jqXHR, textStatus) {
                        //location.reload();
                        //restore_button(mainForm_submit);
                    }
                });
            });
        });

        function disable_button(element){
            element.disabled = true;
            element.classList.add('loading');
        }

        function restore_button(element){
            element.disabled = false;
            element.classList.remove('loading');
        }

        function collectFooterData(formData) {
            const footerItems = [];
            const footerElements = document.querySelectorAll('#footerList .draggable');

            footerElements.forEach(function(element, index) {
                const titleInput = element.querySelector('.w-300 input');
                const contentInput = element.querySelector('.inner_cnt > div:nth-child(2) input');
                const activeSwitch = element.querySelector('.common_switch input[type="checkbox"]');

                if (titleInput && contentInput && activeSwitch
                    && titleInput.value.trim() !== ''
                    && contentInput.value.trim() !== '' ) {
                    footerItems.push({
                        title: titleInput.value,
                        content: contentInput.value,
                        active: activeSwitch.checked,
                        order: index
                    });
                }
            });

            // JSON 문자열로 변환하여 formData에 추가
            formData.append('footer_settings', JSON.stringify(footerItems));

            return formData;
        }

        function collectSnsData(formData) {
            const snsItems = [];
            const snsElements = document.querySelectorAll('#snsList .draggable');

            snsElements.forEach(function(element, index) {
                const nameInput = element.querySelector('.w-300 input');
                const linkInput = element.querySelector('.inner_cnt > div:nth-child(2) input');
                const activeSwitch = element.querySelector('.common_switch input[type="checkbox"]');

                if (nameInput && linkInput && activeSwitch
                    && nameInput.value.trim() !== ''
                    && linkInput.value.trim() !== '') {
                    snsItems.push({
                        name: nameInput.value,
                        link: linkInput.value,
                        active: activeSwitch.checked,
                        order: index
                    });
                }
            });

            // JSON 문자열로 변환하여 formData에 추가
            formData.append('sns_settings', JSON.stringify(snsItems));

            return formData;
        }


        function collectLoginData(formData) {
            const loginItems = [];
            const loginElements = document.querySelectorAll('#loginList .draggable');

            loginElements.forEach(function(element, index) {
                // 각 로그인 항목의 입력 필드 가져오기
                const korNameInput = element.querySelector('.item:nth-child(1) div:nth-child(1) input');
                const engNameInput = element.querySelector('.item:nth-child(1) div:nth-child(2) input');
                const clientIdInput = element.querySelector('.item:nth-child(2) div:nth-child(1) input');
                const clientSecretInput = element.querySelector('.item:nth-child(2) div:nth-child(2) input');
                const redirectUrlInput = element.querySelector('.item:nth-child(3) div:nth-child(1) input');
                const apiKeyInput = element.querySelector('.item:nth-child(3) div:nth-child(2) input');
                const activeSwitch = element.querySelector('.common_switch input[type="checkbox"]');



                // 필수 항목이 존재하는지 확인
                if (korNameInput && engNameInput && clientIdInput && clientSecretInput
                    && korNameInput.value.trim() !== ''
                    && engNameInput.value.trim() !== ''
                    && clientIdInput.value.trim() !== ''
                    && clientSecretInput.value.trim() !== '') {
                    loginItems.push({
                        korName: korNameInput.value,
                        engName: engNameInput.value,
                        clientId: clientIdInput.value,
                        clientSecret: clientSecretInput.value,
                        redirectUrl: redirectUrlInput ? redirectUrlInput.value : '',
                        apiKey: apiKeyInput ? apiKeyInput.value : '',
                        active: activeSwitch ? activeSwitch.checked : false,
                        order: index
                    });
                }
            });

            formData.append('login_settings', JSON.stringify(loginItems));

            return formData;
        }
        function collectForbidData(formData) {
            const forbidItems = [];
            const forbidElements = document.querySelectorAll('.forbid_list li');
            const forbidUseRadio = document.querySelector('#forbid_1');

            // 각 금칙어 항목 수집
            forbidElements.forEach(function(element, index) {
                const wordText = element.querySelector('span').textContent;

                forbidItems.push({
                    word: wordText,
                    order: index
                });
            });

            // 금칙어 설정 정보 생성
            const forbidSettings = {
                words: forbidItems,
                active: forbidUseRadio ? forbidUseRadio.checked : true,
                count: forbidItems.length
            };

            // JSON 형태로 변환하여 폼 데이터에 추가
            formData.append('forbid_settings', JSON.stringify(forbidSettings));

            return formData;
        }
    </script>
    <!-- 개발용 스크립트 E -->
@stop
