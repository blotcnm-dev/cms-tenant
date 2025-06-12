<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\View\Composers\FrontComposer;
use App\Http\View\Composers\AdminComposer;
use App\Services\FrontendDataService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // FrontendDataService 싱글톤 등록
        $this->app->singleton(FrontendDataService::class, function ($app) {
            return new FrontendDataService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //View::composer('admin.*', AdminComposer::class);

        // 모든 admin 레이아웃 뷰에 메뉴 데이터 제공
        View::composer('admin.*', function ($view) {
            $grade_id = session('blot_ugrd');
            $originalMenus = config('admin_menu.menus');
            // 세션 체크 후 조건부 메뉴 추가
            if (session()->get('blot_adid') == 'blot') {
                $originalMenus[] =  [
                    'code' => '7000',
                    'name' => '입점사 관리',
                    'icon' => 'gnb_stats.png',
                    'icon_dark' => 'gnb_stats_dark.png',
                    'children' => [
                        [
                            'code' => '7100',
                            'name' => '입점사 관리',
                            'link' => '/master/provider',
                            'route_name' => 'provider'
                        ],
                    ]
                ];
            }

            $originalMenus = array_filter($originalMenus);




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
            //$permissions = $this->getUserPermissions($grade_id);

            // 권한이 있는 메뉴만 필터링하여 새로운 메뉴 배열 생성
            $filteredMenus = $this->filterMenusByPermission($originalMenus, $permissions);

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
            $view->with('menus', $filteredMenus);
            $view->with('permissions', $permissions);
            $view->with('activeMenuCode', $menuInfo['code']);
            $view->with('activeParentMenuCode', $menuInfo['parentCode']);
            $view->with('activeParentMenuCodeName', $menuInfo['parentCodeName']);
            $view->with('activeParentMenuIcon', $menuInfo['parentCodeIcon']);
            $view->with('activeParentMenuIconDark', $menuInfo['parentCodeIconDark']);
            $view->with('activePath', $currentPath);
        });


        View::composer('web.*', FrontComposer::class);


        // 커스텀 Validator 확장
        Validator::extend('unique_hashed_email', function ($attribute, $value, $parameters, $validator) {
            $hash = hash('sha256', $value);
            $query = DB::table('bl_members')->where('email_hash', $hash);

            if (!empty($parameters[0])) {
                $query->where('member_id', '!=', $parameters[0]);
            }

            return !$query->exists();
        });

        Validator::extend('unique_hashed_phone', function ($attribute, $value, $parameters, $validator) {
            $hash = hash('sha256', $value);
            $query = DB::table('bl_members')->where('phone_hash', $hash);

            if (!empty($parameters[0])) {
                $query->where('member_id', '!=', $parameters[0]);
            }

            return !$query->exists();
        });

        Validator::extend('unique_hashed_user_id', function ($attribute, $value, $parameters, $validator) {
            $query = DB::table('bl_members')->where('user_id', $value);

            if (!empty($parameters[0])) {
                $query->where('member_id', '!=', $parameters[0]);
            }

            return !$query->exists();
        });

    }

    /**
     * 권한이 있는 메뉴만 필터링
     */
    private function filterMenusByPermission(array $menus, array $permissions): array
    {
        $filteredMenus = [];

        foreach ($menus as $index => $menu) {
            // 하위 메뉴가 있는 경우
            if (isset($menu['children']) && is_array($menu['children'])) {
                $filteredChildren = [];
                $hasVisibleChild = false;

                foreach ($menu['children'] as $child) {
                    // 메뉴 코드에 대한 권한이 있는지 확인
                    $hasPermission = isset($permissions[$child['code']]) &&
                        $permissions[$child['code']]->permission_read == 1;

                    if ($hasPermission) {
                        $filteredChildren[] = $child;
                        $hasVisibleChild = true;
                    }
                }

                // 표시할 하위 메뉴가 하나라도 있으면 상위 메뉴도 표시
                if ($hasVisibleChild) {
                    $menuCopy = $menu;
                    $menuCopy['children'] = $filteredChildren;
                    $filteredMenus[] = $menuCopy;
                }
            }
        }

        return $filteredMenus;
    }


    /**
     * 현재 경로에 해당하는 메뉴 코드와 상위 메뉴 코드 찾기
     */
    private function findActiveMenuInfo($menus, $path)
    {
        $result = [
            'code' => null,
            'parentCode' => null,
            'parentCodeName' => null,
            'parentCodeIcon' => null,
            'parentCodeIconDark' => null
        ];
        foreach ($menus as $menu) {
            if (isset($menu['children'])) {
                foreach ($menu['children'] as $child) {
                    if (isset($child['link']) && $child['link'] == $path) {
                        $result['code'] = $child['code'];
                        $result['parentCode'] = $menu['code'];
                        $result['parentCodeName'] = $menu['name'];
                        $result['parentCodeIcon'] = $menu['icon'];
                        $result['parentCodeIconDark'] = $menu['icon_dark'];
                        return $result;
                    }
                }
            }
        }

        return $result;
    }

}

