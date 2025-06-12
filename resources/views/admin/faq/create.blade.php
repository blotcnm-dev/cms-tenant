@extends('admin.layout.master')

@section('required-page-title', '자주 묻는 질문 등록')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/addBoard.css">
    <link rel="stylesheet" href="/src/style/ckeditor5.css">
@stop

@section('required-page-header-js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- CKEditor 5 한국어 언어팩 -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/translations/ko.js"></script>
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="#" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">자주 묻는 질문 등록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="mainForm" action="{{ route('faq.store') }}" method="POST" enctype="multipart/form-data" class="max_width">
                    @csrf
                    <input type="hidden" name="category" value="">

                    <div class="input_box gray_box">
                        <div class="input_item">
                            <label class="input_title">분류</label>
                            <div class="inner_box no_wrap">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="1차 분류" data-value="" name="category_tmp" value="{{ old('category_tmp') }}" readonly>
                                    <ul role="list">
                                        @foreach($category_sub as $category)
                                            <li role="listitem" data-value="{{ $category->depth_code }}">{{ $category->kname }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="fill_btn plus add_file layerOpen" data-title="분류 관리" data-url="{{ route('configBoards.categorylist') }}">
                                    <span>분류관리</span>
                                </button>
                            </div>
                        </div>
                        <div class="input_item">
                            <label class="input_title" for="q_title">질문</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="q_title" placeholder="제목을 입력하세요" name="subject" value="{{ old('subject') }}">
                            </div>
                            <div id="subject_error" class="error_msg"></div>
                        </div>
                        <div class="input_item">
                            <label class="input_title" for="post_file">파일 첨부</label>
                            <div class="inner_box">
                                <div class="uploadFile_box">
                                    <input type="file" id="post_file" name="post_file[]" multiple>
                                    <div class="type_txt">
                                        <label class="fill_btn plus add_file" for="post_file">
                                            <span>파일 첨부</span>
                                        </label>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="input_item">
                            <label class="input_title">답변</label>
                            <div class="inner_box">
                                <!-- 에디터 들어갈 부분 -->
                                <textarea id="contents" name="contents" class="editor">{{ old('contents') }}</textarea>
                            </div>
                            <div id="contents_error" class="error_msg"></div>
                        </div>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn fixed">
                        <a href="{{ route('faq.index') }}" class="border_btn cancel">
                            <span>취소</span>
                        </a>
                        <button type="submit" id="submitBtn" class="border_btn register">
                            <span>등록</span>
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

        import { layerHandler } from "/src/js/components/layer.js";
        import { listManagement } from "/src/js/components/listManagement.js";

        const selectTypeHandler =()=> {
            const selectBoxes = document.querySelectorAll('.js_custom_select');

            selectBoxes.forEach((box, index) => {
                const input = box.querySelector('.select_value');
                const listItems = box.querySelectorAll('ul li');

                listItems.forEach(item => {
                    item.addEventListener('click', ()=> {
                        for (let i = index + 1; i < selectBoxes.length; i++) {
                            if (i === index + 1) {
                                selectBoxes[i].style.display = "block";
                                selectBoxes[i].querySelector('.select_value').value = "";
                            } else {
                                selectBoxes[i].style.display = "none";
                                selectBoxes[i].querySelector('.select_value').value = "";
                            }
                        }
                    })
                })
            })
        }

        const listManagementDynamic = (options) => {
            const {
                btnSelector,
                templateId,
                containerSelector,
            } = options;

            const $template = document.getElementById(templateId);
            if (!$template) return;

            document.addEventListener("click", (e) => {
                const $btn = e.target.closest(btnSelector);
                if (!$btn) return;

                const $menuItem = $btn.closest(".menu_item");
                const $targetContainer = $menuItem?.querySelector(containerSelector);
                if (!$targetContainer) return;

                const $clone = $template.content.firstElementChild.cloneNode(true);

                $targetContainer.appendChild($clone);
                document.querySelectorAll(".cl_management-cnt .menu_item").forEach(($item) =>
                    $item.classList.remove("on")
                );
                $menuItem?.classList.add("on");
            });

            document.addEventListener("click", (e) => {
                const $delBtn = e.target.closest(".depth_2 .del_btn");
                if ($delBtn) {
                    const $item = $delBtn.closest(".menu_item.depth_2");
                    if ($item) $item.remove();
                }
            });
        };

        const depth2SortableInstances = new Map();

        const initSortables = () => {
            const isMobile = window.innerWidth <= 820;

            document.querySelectorAll("[data-name='categorylist'] .accordion ul").forEach(($ul) => {
                if (isMobile) {
                    const instance = depth2SortableInstances.get($ul);
                    if (instance) {
                        instance.destroy();
                        depth2SortableInstances.delete($ul);
                        $ul.removeAttribute("data-sortable-init");
                    }
                } else {
                    if (!depth2SortableInstances.has($ul)) {
                        const instance = Sortable.create($ul, {
                            group: "depth_2_only",
                            animation: 150,
                            onMove: (evt) => {
                                return evt.dragged.classList.contains("depth_2") &&
                                    evt.related.classList.contains("depth_2");
                            }
                        });
                        depth2SortableInstances.set($ul, instance);
                        $ul.dataset.sortableInit = "true";
                    }
                }
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            selectTypeHandler();

            document.addEventListener('click', (e) => {
                const $target = e.target;

                const layerTrigger = $target.closest('.layerOpen');
                if (layerTrigger) {
                    const title = layerTrigger.dataset.title;
                    const contentUrl = layerTrigger.dataset.url;

                    if (title && contentUrl) {
                        layerHandler(title, contentUrl, ()=> {
                            listManagement({
                                btnSelector: ".cl_management-top .plus",
                                templateId: "depth_1",
                                containerSelector: "[data-name='categorylist'] .menu",
                                switchPrefix: "depth1",
                            });
                            listManagementDynamic({
                                btnSelector: ".depth_1 .plus_btn",
                                templateId: "depth_2",
                                containerSelector: ".accordion ul",
                            });
                            initSortables();
                        })
                    }

                    return;
                }

                const $accBtn = $target.closest('.accordion_btn');
                if ($accBtn) {
                    const $popup = document.querySelector('[data-name="categorylist"]');
                    if (!$popup) return;

                    const $clicked = $accBtn.closest('.depth_1');
                    const isAlreadyOn = $clicked.classList.contains('on');

                    $popup.querySelectorAll('.depth_1').forEach(item => item.classList.remove('on'));
                    if (!isAlreadyOn) {
                        $clicked.classList.add('on');
                    }
                    return;
                }

                if (e.target.closest(".plus_btn")) {
                    setTimeout(() => initSortables(), 0);
                }
            });

            window.addEventListener("resize", () => {
                initSortables();
            });
        });
    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            //일반게시판 첨부파일 사용시
            const fileMaxCnt   = 5;
            const fileMaxSizeMB= 10;
            const fileMaxBytes = fileMaxSizeMB * 1024 * 1024;

            const postFileInput = document.getElementById('post_file');
            const boxWrap       = document.querySelector('.uploadFile_box .type_txt');

            if (!postFileInput || !boxWrap) {
                console.error('필요한 요소를 찾을 수 없습니다.');
                return;
            }

            postFileInput.addEventListener('change', handleFileSelect);

            function handleFileSelect(e) {
                const files = Array.from(e.target.files);
                // console.log('selected files:', files, 'count:', files.length);

                if (files.length === 0) {
                    alert('파일이 선택되지 않았습니다.');
                    return;
                }

                files.forEach(file => {
                    // 개수 제한
                    if (boxWrap.children.length >= fileMaxCnt) {
                        alert(`파일은 최대 ${fileMaxCnt}개까지 업로드 가능합니다.`);
                        return;
                    }
                    // 용량 제한
                    if (file.size > fileMaxBytes) {
                        alert(`파일 크기는 최대 ${fileMaxSizeMB}MB까지 업로드 가능합니다.`);
                        return;
                    }
                    createPreview(file);
                });

                // 다시 같은 파일 선택 허용
                postFileInput.value = '';
            }

            function createPreview(file) {
                // ─── .txt_box ───
                const box = document.createElement('div');
                box.classList.add('txt_box');

                // ─── .info_box ───
                const info = document.createElement('div');
                info.classList.add('info_box');

                // ① 숨겨진 file input 생성 & DataTransfer로 파일 할당
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'file';
                hiddenInput.name = 'post_file[]';
                hiddenInput.style.display = 'none';
                const dt = new DataTransfer();
                dt.items.add(file);
                hiddenInput.files = dt.files;
                info.appendChild(hiddenInput);

                // ② 파일명 표시
                const nameP = document.createElement('p');
                nameP.classList.add('name');
                nameP.textContent = file.name;
                info.appendChild(nameP);

                // ③ 용량 표시
                const capSpan = document.createElement('span');
                capSpan.classList.add('capacity');
                capSpan.textContent = file.size < 1024*1024
                    ? `${(file.size/1024).toFixed(2)}KB`
                    : `${(file.size/1024/1024).toFixed(2)}MB`;
                info.appendChild(capSpan);

                // ④ 삭제 버튼
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.classList.add('del_btn');
                delBtn.setAttribute('aria-label', '삭제하기');
                delBtn.textContent = '삭제';
                delBtn.addEventListener('click', () => box.remove());
                info.appendChild(delBtn);

                box.appendChild(info);
                boxWrap.appendChild(box);
            }

            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // 버튼 비활성화
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                document.querySelector('input[name="category"]').value = document.querySelector('input[name="category_tmp"]').getAttribute('data-value');
                if(!document.querySelector('input[name="category"]').value) {
                    document.getElementById('category_error').textContent = "카테고리를 선택해주세요.";
                    document.getElementById('category_error').style.display = 'block';
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }

                document.querySelector('#contents').value = editorInstance.getData();

                if(!document.querySelector('input[name="subject"]').value) {
                    document.getElementById('subject_error').textContent = "제목을 입력해주세요.";
                    document.getElementById('subject_error').style.display = 'block';
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }
                if(!document.querySelector('textarea[name="contents"]').value.trim()) {
                    document.getElementById('contents_error').textContent = "내용을 입력해주세요.";
                    document.getElementById('contents_error').style.display = 'block';
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }

                const formData = new FormData(this);
                const url = '{{ route('faq.store') }}';

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
                            window.location.href = data.redirect || '{{ route('faq.index') }}';
                        } else {
                            if (data.errors.subject) {
                                document.getElementById('subject_error').textContent = data.errors.subject[0];
                                document.getElementById('subject_error').style.display = 'block';
                            }
                            if (data.errors.contents) {
                                document.getElementById('contents_error').textContent = data.errors.contents[0];
                                document.getElementById('contents_error').style.display = 'block';
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

        //CKEditor start
        class MyUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }
            // 업로드 시작
            upload() {
                return this.loader.file
                    .then(file => new Promise((resolve, reject) => {
                        this._uploadFile(file).then(response => {
                            resolve({
                                default: response.url
                            });
                        }).catch(error => {
                            reject(error);
                        });
                    }));
            }
            // 업로드 중단
            abort() {
                // 업로드 중단 로직이 필요한 경우 여기에 구현
            }
            // 실제 파일 업로드 처리
            _uploadFile(file) {
                const formData = new FormData();
                formData.append('upload', file);

                return fetch('{{ route("ckeditor.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        return response;
                    });
            }
        }
        // 업로드 어댑터 플러그인 함수
        function MyCustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new MyUploadAdapter(loader);
            };
        }

        let editorInstance;
        document.addEventListener('DOMContentLoaded', () => {
            // CKEditor 초기화
            ClassicEditor
                .create(document.querySelector('#contents'), {
                    initialData: document.querySelector('#contents').value,
                    // 에디터 설정
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'indent', 'outdent', '|', 'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'],
                    // 한국어 설정
                    language: 'ko',
                    // 사용자 정의 업로드 어댑터 플러그인 추가
                    extraPlugins: [MyCustomUploadAdapterPlugin],
                    // 이미지 설정
                    image: {
                        toolbar: [
                            'imageTextAlternative',
                            'imageStyle:full',
                            'imageStyle:side'
                        ]
                    }
                })
                .then(editor => {
                    editorInstance = editor;

                    editor.model.document.on('change:data', () => {
                        document.querySelector('#contents').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error('CKEditor 초기화 중 오류가 발생했습니다:', error);
                });
        });


        document.addEventListener('click', (e) => {

            const btn = e.target.closest('#submit2Btn');
            if (!btn) return;  // 버튼 클릭이 아니면 무시

            e.preventDefault();      // 본래 submit 막기

            // 이제 btn 은 null 이 될 수 없으니 바로 classList / disabled 사용
            btn.classList.add('loading');
            btn.disabled = true;

            // form 처리
            const form = btn.closest('form');
            if (!form) {
                console.error('버튼이 속한 <form>을 찾을 수 없습니다.');
                return;
            }

            const url2 = '{{ route('configBoards.categorystore') }}';

            // AJAX 요청
            fetch( url2 , {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 422) {
                        // validation 에러
                        return response.json().then(data => { throw { type:'validation', data }; });
                    }
                    if (!response.ok) {
                        throw { type:'http', status: response.status };
                    }
                    return response.json();
                })
                .then(data => {
                    // 성공했을 때
                    location.reload();
                })
                .catch(err => {
                    submit2Btn.classList.remove('loading');
                    submit2Btn.disabled = false;

                    if (err.type === 'validation') {
                        // Laravel 이 반환한 에러 객체
                        const errors = err.data.errors;
                        if (errors['cate'])          alert(errors['cate'][0]);
                        else if (errors['cate.*'])   alert(errors['cate.*'][0]);
                        else                         alert('카테고리 값(공백은 항목 제거 또는 등록)을 확인해주세요.');
                    }
                    else {
                        console.error(err);
                        alert('서버 통신 중 오류가 발생했습니다.');
                    }
                });
        });
    </script>
    <!-- 개발용 스크립트 E -->
@stop
