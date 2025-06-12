<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Blot API Doc.",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="admin@admin.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )

 * @OA\Server(
 *      url=L5_SWAGGER_CONST_LOCAL_HOST,
 *      description="blot C&M CMS API Server"
 * )
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        app()->singleton('shopConfigs', function() {
            // 기본설정
            $shopDefaultConfig = getConfigJson('shopDefaultConfig');
            $shopDefaultConfig = !is_array($shopDefaultConfig) ? $shopDefaultConfig : (object)[];

            // 쇼핑몰 운영 보안 관리
            $securityConfig = getConfigJson('securityConfig');
            $securityConfig = !is_array($securityConfig) ? $securityConfig : (object)[];

            // 단위 및 명칭관리
            $unitConfig = getConfigJson('unitConfig');
            $unitConfig = !is_array($unitConfig) ? $unitConfig : (object)[];

            // 쇼핑몰 이용 관리
            $usageConfig = getConfigJson('usageConfig');
            $usageConfig = !is_array($usageConfig) ? $usageConfig : (object)[];

            // 추가가능

            return [
                'shopDefaultConfig' => $shopDefaultConfig,
                'securityConfig' => $securityConfig,
                'unitConfig' => $unitConfig,
                'usageConfig' => $usageConfig
            ];
        });
    }
}
