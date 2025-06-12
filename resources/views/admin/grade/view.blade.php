<div class="common_table grade_top user">
    <div class="thead">
        <div>권한명</div>
    </div>
    <ul role="list" class="tbody">
        <li role="listitem">
            <div>
                <p>사용자 ({{$grade_name->code_name}})</p>
            </div>
        </li>
    </ul>
</div>
<div class="common_table grade_bottom user">
    <table class="grade_table">
        <thead>
        <tr>
            <th colspan="2">게시판명</th>
            <th>Read<br>(읽기)</th>
            <th>Update<br>(수정, 등록)</th>
            <th>Delete<br>(삭제)</th>
            <th>권한수정</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="6">
                @php
//dd($user_permissions)
                @endphp
                <table>
                    <tbody>
                    @foreach($user_permissions as $user_permission)
                        <tr>
                            <td>{{ $user_permission['board_name']}}</td>
                            <td>
                                <label class="chk_input">
                                    <input type="checkbox" {{ $user_permission['read'] ? 'checked' : '' }} disabled>
                                    <span></span>
                                </label>
                            </td>
                            <td>
                                <label class="chk_input">
                                    <input type="checkbox" {{ $user_permission['write'] ? 'checked' : '' }} disabled>
                                    <span></span>
                                </label>
                            </td>
                            <td>
                                <label class="chk_input">
                                    <input type="checkbox" {{ $user_permission['delete'] ? 'checked' : '' }} disabled>
                                    <span></span>
                                </label>
                            </td>
                            <td>
                                <a href="{{ route('configBoards.edit',  $user_permission['board_id'] ) }}" class="border_btn white">권한 수정</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>
