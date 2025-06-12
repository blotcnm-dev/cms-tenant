<div class="default-banner-section banner-{{ $type }}">
    <div class="container">
        @if($type === 'top')
            <div class="banner-slider">
                @foreach($banners as $banner)
                    <div class="banner-slide">
                        <a href="{{ $banner['link'] }}" target="{{ $banner['target'] }}">
                            <img src="{{ asset('storage/' . $banner['image']) }}" alt="{{ $banner['title'] }}">
                        </a>
                    </div>
                @endforeach
            </div>
        @elseif($type === 'middle')
            <div class="banner-grid">
                @foreach($banners as $banner)
                    <div class="banner-card">
                        <a href="{{ $banner['link'] }}" target="{{ $banner['target'] }}">
                            <img src="{{ asset('storage/' . $banner['image']) }}" alt="{{ $banner['title'] }}">
                        </a>
                    </div>
                @endforeach
            </div>
        @elseif($type === 'bottom')
            <div class="banner-horizontal">
                @foreach($banners as $banner)
                    <div class="banner-wide">
                        <a href="{{ $banner['link'] }}" target="{{ $banner['target'] }}">
                            <img src="{{ asset('storage/' . $banner['image']) }}" alt="{{ $banner['title'] }}">
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
