<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AdminMenus
{
    /**
     * Handle an incoming request.
     * 관리자 메뉴 정보를 가져와서 파싱 처리
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('adminMenu', getConfigJson('adminMenu', '/common/configs/'));
        if(request()->attributes->has('adminMenu')) {
            $urlPath = substr(parse_url(request()->url())['path'], 1);
            $menus = $request->attributes->get('adminMenu');
            foreach($menus as $menu) {
                if (isset($menu->sub) === true) {
                    foreach($menu->sub as $_menu) {
                        while(Str::contains($urlPath, $_menu->info->path)) {
                            // URL로 매칭하여 현재에 있는 메뉴 추출
                            if (Str::contains($urlPath, $_menu->info->path)) {
                                $request->attributes->set('adminMenuId', $_menu->info->admin_menu_id);
                                break;
                            }
                            $urlParts = explode('/', $urlPath);
                            unset($urlParts[array_key_last($urlParts)]);
                            $urlPath = implode('/', $urlParts);

                            if (count($urlParts) < 2) break;
                        }
                    }
                }
            }
        }

        $admin_id = session('ADMIN_ID');
        if (isset($admin_id) === true && trim($admin_id) != "")  {
            $adminInfo = [
                'admin_id' => $admin_id,
                'admin_user_id' => session('USER_ID'),
                'admin_name' => session('ADMIN_NAME'),
                'admin_type' => session('ADMIN_TYPE'),
            ];

            $request->attributes->set('adminInfo', $adminInfo);
        }
        return $next($request);
    }
}
