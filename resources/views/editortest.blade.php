@extends('admin.layout.master')

@section('required-page-title', '약관 목록')

@section('required-page-header-css')
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- CKEditor 5 한국어 언어팩 -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/translations/ko.js"></script>
    <link rel="stylesheet" href="/src/style/ckeditor5.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>
        @php
            $editor_url = asset('vendor/ckeditor5/ckeditor5');
            $id = $id ?? 'editor';
            $content = $content ?? '';
            $nonce_key = csrf_token();
        @endphp

        <textarea id="{{ $id }}" name="{{ $id }}">{{ $content }}</textarea>
    </main>
@stop

@section('required-page-add-content')
    <script>
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
        // CKEditor 초기화
        ClassicEditor
            .create(document.querySelector('#editor'), {
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
                console.log('CKEditor가 초기화되었습니다.', editor);
            })
            .catch(error => {
                console.error('CKEditor 초기화 중 오류가 발생했습니다:', error);
            });
    </script>
@stop
