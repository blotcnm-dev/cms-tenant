<footer>
    <div class="inner">
        <div class="left_box">
            {!! renderFooter($footerData) !!}
            <ul role="list" class="link">
                <li role="listitem">
                    <a href="/">회사소개</a>
                </li>
                <li role="listitem">
                    <a href="{{ route('front_policy.index') }}">서비스이용약관</a>
                </li>
                <li role="listitem">
                    <a href="{{ route('front_privacy.index') }}">개인정보처리방침</a>
                </li>
                <li role="listitem">
                    <a href="{{ route('front_faq.index') }}">자주 묻는 질문</a>
                </li>
                <li role="listitem">
                    <a href="{{ route('front_inquiry.index') }}">1:1 문의</a>
                </li>
            </ul>
            {!! renderSNS($snsData) !!}
        </div>
        <span class="copyright">ⓒ BLOT : CMS. All Rights Reserved.</span>
    </div>
</footer>
