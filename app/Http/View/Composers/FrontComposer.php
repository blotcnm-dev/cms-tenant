<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Services\FrontendDataService;
use Illuminate\Support\Facades\Cache;

class FrontComposer
{
    protected $frontendDataService;

    public function __construct(FrontendDataService $frontendDataService)
    {
        $this->frontendDataService = $frontendDataService;
    }

    /**
     * 뷰에 데이터 바인딩
     */
    public function compose(View $view)
    {
        // 캐시를 통해 성능 최적화 (5분 캐시)
        $frontendData = Cache::remember('frontend_json_data', 5, function() {
            return $this->frontendDataService->getFrontendData();
        });
        // 메뉴 데이터를 계층 구조로 변환
        $menus = $this->buildMenuTree($frontendData['menus'] ?? []);

        // 현재 경로 정보
        $currentUrl = request()->url();
        $currentPath = request()->path();

        $view->with([
            'frontMenus' => $menus,
            'footerData' => $frontendData['settings']['footer'] ?? '',
            'snsData' => $frontendData['settings']['sns'] ?? '',
            'metaData' => $frontendData['settings']['meta'] ?? '',
            'scriptData' => $frontendData['settings']['script'] ?? '',
            'siteSettings' => $frontendData['settings'] ?? [],
            'dataVersion' => $frontendData['version'] ?? time(),
            'dataGeneratedAt' => $frontendData['generated_at'] ?? null,
            'currentUrl' => $currentUrl,
            'currentPath' => $currentPath
        ]);
    }

    /**
     * 메뉴 배열을 계층 구조로 변환
     */
    private function buildMenuTree($menus)
    {
        $menuTree = [];
        $menuMap = [];

        // 먼저 모든 메뉴를 맵에 저장
        foreach ($menus as $menu) {

            // 배열인지 객체인지 확인 후 적절히 접근
            $menuId = is_array($menu) ? $menu['id'] : $menu->id;
            $parentId = is_array($menu) ? $menu['parent_id'] : $menu->parent_id;

            $menuMap[$menuId] = (object) array_merge((array) $menu, ['children' => []]);
        }

        // 부모-자식 관계 설정
        foreach ($menuMap as $menu) {
            if ($menu->parent_id && isset($menuMap[$menu->parent_id])) {
                $menuMap[$menu->parent_id]->children[] = $menu;
            } else {
                $menuTree[] = $menu;
            }
        }

        return $menuTree;
    }
}
