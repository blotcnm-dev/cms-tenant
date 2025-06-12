<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminComposer
{
    /**
     * 관리자 뷰에 데이터 바인딩
     */
    public function compose(View $view)
    {
        $grade_id = session('blot_ugrd');
        $originalMenus = config('admin_menu.menus');

        $permissions = [];
        if ($grade_id) {
            // 캐시 키 생성 - 사용자 등급 ID 포함
            $cacheKey = 'admin_permissions_' . $grade_id;
            $permissions = Cache::remember($cacheKey, 60 * 24, function () use ($grade_id) {
                return collect(DB::table('bl_admin_user_auths')
                    ->where('member_grade_id', $grade_id)
                    ->get())
                    ->keyBy('menu_code')
                    ->toArray();
            });
        }
        echo "grade_id==================>[".$grade_id."]<br>";
        var_dump($permissions);

        echo "originalMenus==================>[".$originalMenus."]<br>";

        // 권한이 있는 메뉴만 필터링하여 새로운 메뉴 배열 생성
        $filteredMenus = $this->filterMenusByPermission($originalMenus, $permissions);
        echo "filteredMenus==================><br>";
        echo "<pre>";
        print_r($filteredMenus);
        echo "</pre>";
        // 현재 경로
        $path = request()->path();
        $currentPath = "/" . $path;
        $segments = explode('/', $path);

        // 기본적으로 기본 경로는 첫 두 세그먼트
        if (count($segments) >= 2) {
            $base_path = '/' . $segments[0] . '/' . $segments[1];
        } else {
            $base_path = '/' . $path;
        }

        // 현재 활성화된 메뉴 정보 찾기
        $menuInfo = $this->findActiveMenuInfo($originalMenus, $base_path);

        // 뷰에 데이터 공유
        $view->with([
            'menus' => $filteredMenus,
            'permissions' => $permissions,
            'activeMenuCode' => $menuInfo['code'],
            'activeParentMenuCode' => $menuInfo['parentCode'],
            'activeParentMenuCodeName' => $menuInfo['parentCodeName'],
            'activeParentMenuIcon' => $menuInfo['parentCodeIcon'],
            'activeParentMenuIconDark' => $menuInfo['parentCodeIconDark'],
            'activePath' => $currentPath
        ]);
    }

    /**
     * 권한에 따른 메뉴 필터링
     */
    private function filterMenusByPermission($menus, $permissions)
    {
        $filteredMenus = [];

        foreach ($menus as $menuCode => $menu) {
            // 권한 체크
            if ($this->hasPermission($menuCode, $permissions)) {
                $filteredMenu = $menu;

                // 서브메뉴가 있다면 서브메뉴도 필터링
                if (isset($menu['sub']) && is_array($menu['sub'])) {
                    $filteredSubMenus = [];
                    foreach ($menu['sub'] as $subMenuCode => $subMenu) {
                        if ($this->hasPermission($subMenuCode, $permissions)) {
                            $filteredSubMenus[$subMenuCode] = $subMenu;
                        }
                    }
                    $filteredMenu['sub'] = $filteredSubMenus;
                }

                $filteredMenus[$menuCode] = $filteredMenu;
            }
        }

        return $filteredMenus;
    }

    /**
     * 권한 확인
     */
    private function hasPermission($menuCode, $permissions)
    {
        // 권한이 없으면 모든 메뉴 허용 (슈퍼 관리자)
        if (empty($permissions)) {
            return true;
        }

        // 특정 권한이 있는지 확인
        return isset($permissions[$menuCode]);
    }

    /**
     * 현재 활성화된 메뉴 정보 찾기
     */
    private function findActiveMenuInfo($menus, $basePath)
    {
        $menuInfo = [
            'code' => '',
            'parentCode' => '',
            'parentCodeName' => '',
            'parentCodeIcon' => '',
            'parentCodeIconDark' => ''
        ];

        foreach ($menus as $menuCode => $menu) {
            // 메인 메뉴 URL 체크
            if (isset($menu['url']) && $menu['url'] === $basePath) {
                $menuInfo['code'] = $menuCode;
                $menuInfo['parentCode'] = $menuCode;
                $menuInfo['parentCodeName'] = $menu['name'] ?? '';
                $menuInfo['parentCodeIcon'] = $menu['icon'] ?? '';
                $menuInfo['parentCodeIconDark'] = $menu['icon_dark'] ?? '';
                break;
            }

            // 서브메뉴 URL 체크
            if (isset($menu['sub']) && is_array($menu['sub'])) {
                foreach ($menu['sub'] as $subMenuCode => $subMenu) {
                    if (isset($subMenu['url']) && $subMenu['url'] === $basePath) {
                        $menuInfo['code'] = $subMenuCode;
                        $menuInfo['parentCode'] = $menuCode;
                        $menuInfo['parentCodeName'] = $menu['name'] ?? '';
                        $menuInfo['parentCodeIcon'] = $menu['icon'] ?? '';
                        $menuInfo['parentCodeIconDark'] = $menu['icon_dark'] ?? '';
                        break 2;
                    }
                }
            }
        }

        return $menuInfo;
    }
}
