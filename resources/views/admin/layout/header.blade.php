<header>
    <div class="header_lnb">
        <h1 class="logo">
            <a href="/">
                <picture>
                    <source media="(min-width: 1400px)" srcset="/src/assets/icons/logo@x2.png">
                    <source media="(max-width: 1399px)" srcset="/src/assets/icons/logo@x3.png">
                    <img src="/src/assets/icons/logo@x2.png" alt="로고">
                </picture>
            </a>
        </h1>
        <button type="button" class="gnb_btn mob_block">메뉴 열기</button>
        <div class="gnb">
            <span class="lnb_desc">기본메뉴</span>
            <ul role="list">
                @foreach($menus as $index => $menu)
                    <li role="listitem" class="gnb_1depth {{ $activeParentMenuCode == $menu['code'] ? 'on' : '' }}">
                        <button type="button" class="js_gnb_open_button">
                            <span class="icon">
                                <img src="/src/assets/icons/{{$menu['icon']}}" alt="gnb icon" class="light">
                                <img src="/src/assets/icons/{{$menu['icon_dark']}}" alt="gnb icon" class="dark">
                            </span>
                            {{ $menu['name'] }}
                        </button>

                        <ul role="list" class="gnb_2depth">
                            @foreach($menu['children'] as $childIndex => $child)
                                @if( !(isset($child['hidden']) &&  $child['hidden'] == 'Y') )
                                    <li role="listitem" class="depth_item {{ $activeMenuCode == $child['code'] ? 'on' : '' }}">
                                        <a href="{{ $child['link'] }}">{{ $child['name'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bottom_box">
            <label class="change_mode">
                <input type="checkbox" name="change_mode">
                <div class="toggle_box">
                    다크 모드로 변경
                    <div class="btn">
                        <span></span>
                    </div>
                </div>
            </label>
        </div>
    </div>
    <div class="header_top">
        <div class="depth_name">
                <span class="icon">
                    <img src="/src/assets/icons/{{$activeParentMenuIcon}}" alt="page icon" class="light">
                    <img src="/src/assets/icons/{{$activeParentMenuIconDark}}" alt="page icon" class="dark">
                </span>
            <h2 class="title">{{$activeParentMenuCodeName}}</h2>
        </div>
        <div class="profile">
            <div class="profile_img">
                @if(session()->has('blot_upf') && session()->get('blot_upf'))
                    <img src="{{ session()->get('blot_upf') }}"
                         alt="프로필 사진"
                         onerror="this.onerror=null; this.src='{{ asset('/storage/member/none.png') }}';">
                @else
                    <img src="{{ asset('/storage/member/none.png') }}" alt="프로필 사진 없음">
                @endif
            </div>
            <div class="profile_info">
                <p class="name"><span>{{ session()->get('blot_adnm') }}</span> 님</p>
                <p class="right">{{ session()->get('blot_ugnm') }}</p>
            </div>
        </div>
        <div class="btn_flex">
            <a href="{{ route('master.logout') }}" class="logout">
                <span>로그아웃</span>
            </a>
            <a href="javascript:void(0);" class="setting">
                <span>설정</span>
            </a>
        </div>
    </div>
</header>
