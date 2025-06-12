
<div class="cl_management-top">
    <button type="button" class="fill_btn blue plus add_file">
        <span>분류 추가</span>
    </button>
</div>

<form id="categoryForm" action="{{ route('configBoards.categorystore') }}" method="POST" enctype="multipart/form-data">
    @csrf
<div class="cl_management-cnt">
    <div class="common_table">
        <div class="thead">
            <div class="name">분류명</div>
            <div class="management">관리</div>
        </div>
        <ul id="menu" class="menu">
            @foreach($categorys as $category)
                <li class="menu_item depth_1">
                    <div class="inner">
                        <div class="name">
                            <input type="hidden" name="code[]" value="{{$category->depth_code}}">
                            <input type="hidden" name="depth[]" value="{{$category->depth}}">
                            <input type="text" class="common_input" name="cate[]" placeholder="1차 분류명" value="{{$category->kname}}">
                            <button type="button" class="plus_btn">
                                <span>하위 추가</span>
                            </button>
                            <button type="button" class="del_btn">삭제</button>
                            <button type="button" class="accordion_btn"></button>
                        </div>
                        <div class="accordion">
                            <ul>
                                @foreach($categorys_sub as $category_sub)
                                    @if(substr($category_sub->depth_code, 0, 2) === substr($category->depth_code, 0, 2))
                                        <li class="menu_item depth_2">
                                            <div class="name">
                                                <input type="hidden" name="code[]" value="{{$category_sub->depth_code}}">
                                                <input type="hidden" name="depth[]" value="{{$category_sub->depth}}">
                                                <input type="text" class="common_input" name="cate[]" placeholder="2차 분류명" value="{{$category_sub->kname}}">
                                                <button type="button" class="del_btn">삭제</button>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

</div>

<div class="layer_btn_box">
    <button type="button" class="border_btn cancel js_remove_btn">취소</button>
    <button type="submit" id="submit2Btn" class="border_btn save js_remove_btn"><span>저장</span></button>
</div>
</form>

<template id="depth_1">
    <li class="menu_item depth_1">
        <div class="inner">
            <div class="name">
                <input type="hidden" name="code[]" value="">
                <input type="hidden" name="depth[]" value="1">
                <input type="text" class="common_input" name="cate[]" placeholder="1차 분류명" value="">
                <button type="button" class="plus_btn">
                    <span>분류 추가</span>
                </button>
                <button type="button" class="del_btn">삭제</button>
                <button type="button" class="accordion_btn"></button>
            </div>
            <div class="accordion">
                <ul>
                </ul>
            </div>
        </div>
    </li>
</template>

<template id="depth_2">
    <li class="menu_item depth_2">
        <div class="name">
            <input type="hidden" name="code[]" value="">
            <input type="hidden" name="depth[]" value="2">
            <input type="text" class="common_input" name="cate[]" placeholder="2차 분류명" value="">
            <button type="button" class="del_btn">삭제</button>
        </div>
    </li>
</template>

<!-- 개발용 스크립트 S -->
<script>
    {{--document.addEventListener("DOMContentLoaded", function() {--}}
    {{--    const form = document.getElementById('categoryForm');--}}
    {{--    form.addEventListener('submit', function(e) {--}}
    {{--        e.preventDefault();--}}
    {{--        // 버튼 비활성화--}}
    {{--        const submit2Btn = document.getElementById('submit2Btn');--}}
    {{--        submit2Btn.classList.add('loading');--}}
    {{--        submit2Btn.disabled = true;--}}

    {{--        const url2 = '{{ route('configBoards.categorystore') }}';--}}

    {{--        // AJAX 요청--}}
    {{--        fetch( url2 , {--}}
    {{--            method: 'POST',--}}
    {{--            body: new FormData(this),--}}
    {{--            headers: {--}}
    {{--                'X-CSRF-TOKEN': '{{ csrf_token() }}',--}}
    {{--                'X-Requested-With': 'XMLHttpRequest',--}}
    {{--                'Accept': 'application/json'--}}
    {{--            }--}}
    {{--        })--}}
    {{--            .then(response => {--}}
    {{--                if (response.status === 422) {--}}
    {{--                    // validation 에러--}}
    {{--                    return response.json().then(data => { throw { type:'validation', data }; });--}}
    {{--                }--}}
    {{--                if (!response.ok) {--}}
    {{--                    throw { type:'http', status: response.status };--}}
    {{--                }--}}
    {{--                return response.json();--}}
    {{--            })--}}
    {{--            .then(data => {--}}
    {{--                // 성공했을 때--}}
    {{--                alert(data.message);--}}
    {{--            })--}}
    {{--            .catch(err => {--}}
    {{--                submit2Btn.classList.remove('loading');--}}
    {{--                submit2Btn.disabled = false;--}}

    {{--                if (err.type === 'validation') {--}}
    {{--                    // Laravel 이 반환한 에러 객체--}}
    {{--                    const errors = err.data.errors;--}}
    {{--                    if (errors['cate'])          alert(errors['cate'][0]);--}}
    {{--                    else if (errors['cate.*'])   alert(errors['cate.*'][0]);--}}
    {{--                    else                         alert('입력값을 확인해주세요.');--}}
    {{--                }--}}
    {{--                else {--}}
    {{--                    console.error(err);--}}
    {{--                    alert('서버 통신 중 오류가 발생했습니다.');--}}
    {{--                }--}}
    {{--            });--}}
    {{--    });--}}
    {{--});--}}
</script>
<!-- 개발용 스크립트 E -->
