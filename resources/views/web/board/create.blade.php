@extends('web.layout.master')

@section('required-page-title', $board_config->board_name.' 등록')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/board/edit.css">
    <link rel="stylesheet" href="/src/style/ckeditor5.css">
@stop

@section('required-page-header-js')
    <script type="module" src="/web/js/board/edit.js" defer></script>
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- CKEditor 5 한국어 언어팩 -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/translations/ko.js"></script>
@stop

@section('required-page-banner-blade')
{{--    <div>페이지별 배너 </div>--}}
@stop


@section('required-page-main-content')
    <main>
        <section class="container_edit">
            <div class="container w-820">
                <h2 class="section_title">게시판 글쓰기</h2>
                <form id="mainForm" action="{{ route('boards.store', [$board_config->board_id]) }}" method="POST" enctype="multipart/form-data" class="max_width">
                    @csrf

                    @if($board_config->is_category)
                    <fieldset class="category_container">
                        <legend class="label-hidden">카테고리 선택</legend>
                        <select name="category" value="{{ old('category') }}">
                            @foreach($category_sub as $category)
                                <option value="{{ $category->depth_code }}">{{ $category->kname }}</option>
                            @endforeach
                            <!-- 추가 옵션들 -->
                        </select>
                    </fieldset>
                    <div id="category_error" class="error_msg"></div>
                    @endif
                    <div class="input_item_container">
                        <label for="title" class="label-hidden">제목</label>
                        <input type="text" id="title" name="subject" value="{{ old('subject') }}" placeholder="제목을 입력해주세요." required>
                        @if($board_config->is_secret === 1)
                            <input type="checkbox" name="is_secret" value="1"> 비밀글 사용
                            <input type="hidden" name="secret_password" value="">
                        @endif
                    </div>
                    <div id="subject_error" class="error_msg"></div>


                    @if($board_config->board_type === 'COMMON' && $board_config->is_file === 1)
                    <div class="input_item_container file_container">
                        <ul role="list" class="type_txt">

                        </ul>
                        <label for="post_file">파일첨부</label>
                        <input type="file" id="post_file" name="post_file[]" multiple>
                    </div>
                    @endif
                    @if($board_config->board_type === 'GALLERY' )
                        <div class="input_item_container file_container">
                            <ul role="list" class="type_img">

                            </ul>
                            <label for="favicon_file">파일첨부</label>
                            <input type="file" id="favicon_file" name="gallery_file[]" accept="image/jpeg, image/png, image/gif" multiple>
                        </div>
                    @endif
                    <div class="content">
                        <label for="content" class="label-hidden">내용</label>
                        <textarea id="contents" name="contents" class="editor">{{ old('contents') }}</textarea>
                    </div>
                    <div id="contents_error" class="error_msg"></div>
                    <div class="board_controller">
                        <a href="{{ route('boards.list', [$board_config->board_id]) }}" >목록으로</a>
                        <button type="submit" id="submitBtn" class="submit_button">등록하기</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
@stop

@section('required-page-add-content')
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            @if($board_config->board_type === 'COMMON' && $board_config->is_file === 1)
            //일반게시판 첨부파일 사용시
            const fileMaxCnt   = {{ $board_config->file_uploadable_count ?? 5 }};
            const fileMaxSizeMB= {{ $board_config->file_max_size ?? 10 }};
            const fileMaxBytes = fileMaxSizeMB * 1024 * 1024;

            const postFileInput = document.getElementById('post_file');
            const boxWrap       = document.querySelector('.file_container .type_txt');

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
                // ─── li.file_item 생성 ───
                const listItem = document.createElement('li');
                listItem.classList.add('file_item');

                // ① 숨겨진 file input 생성 & DataTransfer로 파일 할당
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'file';
                hiddenInput.name = 'post_file[]';
                hiddenInput.style.display = 'none';
                const dt = new DataTransfer();
                dt.items.add(file);
                hiddenInput.files = dt.files;
                listItem.appendChild(hiddenInput);

                // ② 파일명 표시
                const nameSpan = document.createElement('span');
                nameSpan.classList.add('file_name');
                nameSpan.textContent = file.name;
                listItem.appendChild(nameSpan);

                // ③ 삭제 버튼
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.classList.add('del_btn');
                delBtn.setAttribute('aria-label', '삭제하기');
                delBtn.textContent = '삭제';
                delBtn.addEventListener('click', () => listItem.remove());
                listItem.appendChild(delBtn);

                // 부모 요소에 추가 (boxWrap을 파일 리스트 컨테이너로 변경)
                boxWrap.appendChild(listItem);
            }
            @endif
            @if($board_config->board_type === 'GALLERY')
            //겔러리형
            const GA_fileMaxCnt   = {{ $board_config->gallery_uploadable_count ?? 5 }};
            const GA_fileMaxSizeMB= {{ $board_config->gallery_max_size ?? 10 }};
            const GA_fileMaxBytes = GA_fileMaxSizeMB * 1024 * 1024;

            const galleryInput = document.getElementById('favicon_file');
            const previewWrapper       = document.querySelector('.file_container .type_img');

            if (!galleryInput || !previewWrapper) {
                console.error('필요한 요소를 찾을 수 없습니다.');
                return;
            }

            galleryInput.addEventListener('change', handleFileSelect);

            function handleFileSelect(e) {
                const files = Array.from(e.target.files);
                // console.log('selected files:', files, 'count:', files.length);

                if (files.length === 0) {
                    alert('파일이 선택되지 않았습니다.');
                    return;
                }

                files.forEach(file => {
                    // 개수 제한
                    if (previewWrapper.children.length >= GA_fileMaxCnt) {
                        alert(`파일은 최대 ${GA_fileMaxCnt}개까지 업로드 가능합니다.`);
                        return;
                    }
                    // 용량 제한
                    if (file.size > GA_fileMaxBytes) {
                        alert(`파일 크기는 최대 ${GA_fileMaxSizeMB}MB까지 업로드 가능합니다.`);
                        return;
                    }
                    createPreview(file);
                });

                // 다시 같은 파일 선택 허용
                galleryInput.value = '';
            }

            function createPreview(file) {
                // ─── li.file_item 생성 ───
                const listItem = document.createElement('li');
                listItem.classList.add('file_item');

                // ① 숨겨진 file input 생성 & DataTransfer로 파일 할당
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'file';
                hiddenInput.name = 'gallery_file[]';
                hiddenInput.style.display = 'none';
                const dt = new DataTransfer();
                dt.items.add(file);
                hiddenInput.files = dt.files;
                listItem.appendChild(hiddenInput);

                // ② 파일명 표시
                const nameSpan = document.createElement('span');
                nameSpan.classList.add('file_name');
                nameSpan.textContent = file.name;
                listItem.appendChild(nameSpan);

                // ③ 삭제 버튼
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.classList.add('del_btn');
                delBtn.setAttribute('aria-label', '삭제하기');
                delBtn.textContent = '삭제';
                delBtn.addEventListener('click', () => listItem.remove());
                listItem.appendChild(delBtn);

                // 부모 요소에 추가 (boxWrap을 파일 리스트 컨테이너로 변경)
                previewWrapper.appendChild(listItem);
            }
            @endif
            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // 버튼 비활성화
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                @if($board_config->is_category)
                // document.querySelector('input[name="category"]').value = document.querySelector('input[name="category_tmp"]').getAttribute('data-value');
                if(!document.querySelector('select[name="category"]').value) {
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

                @if($board_config->board_type === 'COMMON')
                if(!document.querySelector('textarea[name="contents"]').value.trim()) {
                    document.getElementById('contents_error').textContent = "내용을 입력해주세요.";
                    document.getElementById('contents_error').style.display = 'block';
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }
               @endif

                const formData = new FormData(this);
                const url = '{{ route('boards.store', [$board_config->board_id]) }}';

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
                            window.location.href = data.redirect || '{{ route('boards.list', [$board_config->board_id]) }}';
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
                        // // 버튼 다시 활성화
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
@stop
