<?php
namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Cache;

class PromotionComposer
{
    protected $promotionController;

    public function __construct(PromotionController $promotionController)
    {
        $this->promotionController = $promotionController;
    }
    public function compose(View $view)
    {
        // 디바이스 감지
        $device = $this->detectDevice();
        $device = 'pc'; // 임시로 고정

        // 캐시 키 생성 (디바이스별로 다른 캐시)
        $cacheKey = "global_banners_{$device}";

        // 캐시에서 배너 데이터 가져오기 (10분 캐시)
        $banners = Cache::remember($cacheKey, 600, function () use ($device) {
            return [
                'banner_1' => $this->promotionController->getBanners(17, $device),
                'banner_2' => $this->promotionController->getBanners(18, $device),
                'banner_3' => $this->promotionController->getBanners(19, $device),
                'banner_4' => $this->promotionController->getBanners(20, $device),
                'popup' => $this->promotionController->getPopupBanners($device),
            ];
        });
        //dd($banners);

        $view->with('globalBanners', $banners);
    }

    private function detectDevice()
    {
        $userAgent = request()->header('User-Agent');

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            return 'tablet';
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            return 'mobile';
        }
        return 'pc';
    }
}
