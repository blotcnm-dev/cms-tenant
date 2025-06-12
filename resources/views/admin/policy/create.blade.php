@extends('admin.layout.master')

@section('required-page-title', '약관 작성')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/addBanner.css">
@stop

@section('required-page-header-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <a href="#" onclick="window.history.back(); return false;" aria-label="뒤로가기" class="back_btn"></a>
                <h2 class="title">약관 등록</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="mainForm" action="{{ route('policy.store') }}" method="POST" class="max_width">
                    @csrf
                    <div class="input_box gray_box">
                        <div class="input_item">
                            <label class="input_title" for="terms_title">제목</label>
                            <div class="inner_box">
                                <input type="text" class="common_input" id="terms_title" placeholder="제목을 입력하세요" name="title" value="{{ old('title') }}">
                                <div id="title-error" class="error_msg"></div>
                            </div>
                        </div>
                        <div class="input_item">
                            <label class="input_title" for="terms_text">내용</label>
                            <div class="inner_box">
                                <textarea class="common_textarea" id="terms_text" name="info" placeholder="내용을 입력하세요">{{ old('info') }}</textarea>
                                <div id="info-error" class="error_msg"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn">
                        <a href="{{ route('policy.index') }}" class="border_btn cancel">
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
        import { gnbHandler } from "/src/js/navigation/gnbClassController.js";

        gnbHandler(1, 5);

    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('mainForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // 버튼 비활성화
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                const formData = new FormData(this);
                const url = '{{ route('policy.store') }}';

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
                            window.location.href = data.redirect || '{{ route('policy.index') }}';
                        } else {
                            // 에러 처리
                            if (data.errors) {
                                // 에러 메시지 표시
                                if (data.errors.title) {
                                    document.getElementById('title-error').textContent = data.errors.title[0];
                                    document.getElementById('title-error').style = 'display:block';
                                }
                                if (data.errors.info) {
                                    document.getElementById('info-error').textContent = data.errors.info[0];
                                    document.getElementById('info-error').style = 'display:block';
                                }
                            }

                            // 버튼 다시 활성화
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
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
