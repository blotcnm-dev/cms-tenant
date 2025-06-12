<div class="input_box gray_box">
    <div class="input_item">
        <label class="input_title">제목</label>
        <div class="inner_box">
            <input type="text" class="common_input" value="{{ $policy_history_content->title }}" readonly>
        </div>
    </div>
    <div class="input_item">
        <label class="input_title">내용</label>
        <div class="inner_box">
            <textarea class="common_textarea" readonly>{{ $policy_history_content->info }}</textarea>
        </div>
    </div>
    <div class="input_item">
        <label class="input_title">수정 일자</label>
        <div class="inner_box">
            <input type="text" class="common_input" value="{{ format_date($policy_history_content->created_at, 'Y-m-d H:i:s') }}" readonly>
        </div>
    </div>
    <div class="input_item">
        <label class="input_title">작성자</label>
        <div class="inner_box">
            <input type="text" class="common_input" value="{{ $policy_history_content->user_name }}" readonly>
        </div>
    </div>
</div>

<div class="layer_btn_box">
    <button type="button" class="border_btn save js_remove_btn">확인</button>
</div>
