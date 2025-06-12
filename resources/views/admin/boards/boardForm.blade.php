@extends('admin.layout.master')

@section('required-page-title', '게시물 등록')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/boardManagement/addBoard.css">
    <link rel="stylesheet" href="/src/style/ckeditor5.css">
@stop

@section('required-page-header-js')
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
                <h2 class="title">게시물 등록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="mainForm" action="{{ route('boards.board.store', [$board_config->board_id]) }}" method="POST" enctype="multipart/form-data" class="max_width">
                    @csrf
                    <input type="hidden" name="category" value="">

                    @if($board_config->is_secret === 1)
                    <input type="hidden" name="is_secret" value="0">
                    @endif

                    <div class="input_box gray_box">
                        @if($board_config->is_category)
                        <div class="input_item triple">
                            <label class="input_title">분류 선택</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select">
                                    <input type="text" class="common_input select_value" placeholder="1차 분류" data-value="" name="category_tmp" value="{{ old('category_tmp') }}" readonly>
                                    <ul role="list">
                                        @foreach($category_sub as $category)
                                            <li role="listitem" data-value="{{ $category->depth_code }}">{{ $category->kname }}</li>
                                        @endforeach
                                    </ul>
                                </div>
{{--                                <div class="custom_select_1 js_custom_select" style="display: none;">--}}
{{--                                    <input type="text" class="common_input select_value" placeholder="2차 분류" readonly>--}}
{{--                                    <ul role="list">--}}
{{--                                        <li role="listitem" data-value="depth2_1">분류명1</li>--}}
{{--                                        <li role="listitem" data-value="depth2_2">분류명2</li>--}}
{{--                                        <li role="listitem" data-value="depth2_3">분류명3</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                                <div class="custom_select_1 js_custom_select" style="display: none;">--}}
{{--                                    <input type="text" class="common_input select_value" placeholder="3차 분류" readonly>--}}
{{--                                    <ul role="list">--}}
{{--                                        <li role="listitem" data-value="depth3_1">분류명1</li>--}}
{{--                                        <li role="listitem" data-value="depth3_2">분류명2</li>--}}
{{--                                        <li role="listitem" data-value="depth3_3">분류명3</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
                            </div>
                            <div id="category_error" class="error_msg"></div>
                        </div>
                        @endif
                        <div class="input_item">
                            <label class="input_title" for="post_title">제목</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="post_title" placeholder="제목을 입력하세요" name="subject" value="{{ old('subject') }}">
                            </div>
                            <div id="subject_error" class="error_msg"></div>
                        </div>

                        @if($board_config->board_type === 'COMMON' && $board_config->is_file === 1)
                        <!-- 게시판 타입 S -->
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
                        <!-- 게시판 타입 E -->
                        @endif
                        @if($board_config->board_type === 'GALLERY' )
                        <!-- 갤러리 타입 S -->
                        <div class="input_item">
                            <label class="input_title" for="favicon_file">파일 첨부</label>
                            <div class="inner_box">
                                <div class="uploadFile_box">
                                    <input type="file" id="favicon_file" name="gallery_file[]" accept="image/jpeg, image/png, image/gif" multiple>
                                    <div class="type_img">
                                        <label class="fill_btn plus add_file" for="favicon_file">
                                            <span>파일 첨부</span>
                                        </label>

                                    </div>
                                </div>
                            </div>
                        </div>
                                <!-- <div class="img_box">
                                               <img src="/src/assets/images/sample.jpg" alt="file.jpg">
                                               <button type="button" class="del_btn">삭제</button>
                                           </div> -->
                        <!-- 갤러리 타입 E -->
                        @endif
                        <div class="input_item">
                            <label class="input_title">내용</label>
                            <div class="inner_box">
                                <textarea id="contents" name="contents" class="editor">{{ old('contents') }}</textarea>
                            </div>
                            <div id="contents_error" class="error_msg"></div>
                        </div>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn">
                        <a href="{{ route('boards.board.list', [$board_config->board_id]) }}" class="border_btn cancel">
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

        document.addEventListener('DOMContentLoaded', ()=> {
            selectTypeHandler();
        })
    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            @if($board_config->board_type === 'COMMON' && $board_config->is_file === 1)
            //일반게시판 첨부파일 사용시
            const fileMaxCnt   = {{ $board_config->file_uploadable_count ?? 5 }};
            const fileMaxSizeMB= {{ $board_config->file_max_size ?? 10 }};
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
            @endif
            @if($board_config->board_type === 'GALLERY')
            const galleryInput    = document.getElementById('favicon_file');
            const previewWrapper  = document.querySelector('.uploadFile_box .type_img');

            galleryInput.addEventListener('change', handleGallerySelect);

            function handleGallerySelect(e) {
                const files = Array.from(e.target.files);
                files.forEach(file => createGalleryPreview(file));
                // 같은 파일을 다시 선택할 수 있도록 초기화
                galleryInput.value = '';
            }

            function createGalleryPreview(file) {
                // 1) DataTransfer 로 파일 할당
                const dt = new DataTransfer();
                dt.items.add(file);

                // 2) 미리보기 박스(root .img 요소) 생성
                const previewItem = document.createElement('div');
                previewItem.classList.add('img_box');
                previewItem.dataset.name = file.name;

                // 3) 숨겨진 파일 input
                const hiddenInput = document.createElement('input');
                hiddenInput.type  = 'file';
                hiddenInput.name  = 'gallery_file[]';
                hiddenInput.style.display = 'none';
                hiddenInput.files = dt.files;
                previewItem.appendChild(hiddenInput);

                // 4) 실제 <img> 요소
                const imgEl = document.createElement('img');
                imgEl.src = URL.createObjectURL(file);
                imgEl.alt = '미리보기';
                previewItem.appendChild(imgEl);

                // 5) 삭제 버튼
                const delBtn = document.createElement('button');
                delBtn.type  = 'button';
                delBtn.classList.add('del_btn');
                delBtn.setAttribute('aria-label', '삭제하기');
                delBtn.addEventListener('click', () => {
                    previewItem.remove();
                });
                previewItem.appendChild(delBtn);

                // 6) 컨테이너에 추가
                previewWrapper.appendChild(previewItem);
            }
            @endif
            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // 버튼 비활성화
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                @if($board_config->is_category)
                document.querySelector('input[name="category"]').value = document.querySelector('input[name="category_tmp"]').getAttribute('data-value');
                if(!document.querySelector('input[name="category"]').value) {
                    document.getElementById('category_error').textContent = "카테고리를 선택해주세요.";
                    document.getElementById('category_error').style.display = 'block';
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }
                @endif

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
                const url = '{{ route('boards.board.store', [$board_config->board_id]) }}';

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
                            window.location.href = data.redirect || '{{ route('boards.board.list', [$board_config->board_id]) }}';
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
    </script>
    <!-- 개발용 스크립트 E -->
@stop
