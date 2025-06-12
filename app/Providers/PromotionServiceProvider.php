<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\PromotionComposer;

class PromotionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 모든 뷰에 배너 데이터 제공
        View::composer('web.*', PromotionComposer::class);
        // 또는 특정 레이아웃에만 적용
        // View::composer(['layouts.app', 'layouts.main'], BannerComposer::class);
    }

    public function register()
    {
        //
    }
}
