<div class="banner_wrap">
    <div class="banner_swiper">
        <div class="swiper-wrapper">
            @foreach($banners as $banner)
                <div class="swiper-slide">
                    <a href="{{ $banner['link'] }}" target="{{ $banner['target'] }}">
                        <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}">
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
<script>
    var swiper = new Swiper(".banner_swiper", {
        speed: 600,
        slidesPerView: 1,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
    })
</script>
