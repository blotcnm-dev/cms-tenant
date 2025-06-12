<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PermissionMiddleware
{
    /**
     * 특정 세그먼트와 권한 유형 매핑
     */
    private $segmentPermissions = [
        'create' => 'permission_write',
        'data' => 'permission_read',
        'downloadexcel' => 'permission_read',
        'show' => 'permission_read',
        'edit' => 'permission_write',
        'update' => 'permission_write',
        'destroy' => 'permission_delete',
        'store' => 'permission_write',
        'reset_password' => 'permission_write'
    ];

    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $grade_id = session('blot_ugrd');

        if (!empty($grade_id)) {
            $path = $request->path();
            $method = strtolower($request->method());

            // 기본 경로와 액션 세그먼트 분리
            list($base_path, $action_segment) = $this->extractPathComponents($path);

            // 기본 경로에 대한 권한 확인
            $base_permission_paths = ["/" . $base_path];
            $res = null;

            foreach ($base_permission_paths as $check_path) {
                $res = $this->getAuth($grade_id, $check_path);
                if ($res['success'] && $res['data']['row'] !== null) {
                    break;
                }
            }

            // DB에서 기본 경로에 대한 권한 정보 찾기 실패시
            if (!$res['success'] || $res['data']['row'] === null) {
                // AJAX 요청인 경우 JSON 응답 반환
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "요청하신 내용의 처리 권한이 없습니다."
                    ], 500);
                }
                return redirect()->route('master.permissionError');
            }

            $auth = $res['data']['row'];

            // 권한 유형 결정: 액션 세그먼트가 있으면 그에 맞는 권한, 없으면 HTTP 메소드 기반
            $permission_type = $this->getPermissionTypeBySegment($action_segment);
            if ($permission_type === null) {
                $permission_type = $this->getPermissionType($method);
            }

            // 권한 확인
            $authorized = false;
            if ($auth->$permission_type === 1) {
                $authorized = true;
            }

            // 권한이 없는 경우
            if (!$authorized) {
                // AJAX 요청인 경우 JSON 응답 반환
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "요청하신 내용의 처리 권한이 없습니다."
                    ], 500);
                }
                return redirect()->route('master.permissionError');
            }

            return $next($request);
        }
        return redirect()->route('master.login', ['referrer' => $request->url()]);
    }

    /**
     * URL 경로에서 기본 경로와 액션 세그먼트 추출
     * 예: master/member/create -> ['master/member', 'create']
     * 예: master/member/1/edit -> ['master/member', 'edit']
     * 예: master/member -> ['master/member', null]
     *
     * @param string $path
     * @return array [기본 경로, 액션 세그먼트]
     */
    private function extractPathComponents(string $path): array
    {
        $segments = explode('/', $path);

        // 기본적으로 기본 경로는 첫 두 세그먼트
        if (count($segments) >= 2) {
            $base_path = $segments[0] . '/' . $segments[1];
        } else {
            $base_path = $path;
        }

        $action_segment = null;

        // 추가 세그먼트 있을 경우 액션 확인
        if (count($segments) > 2) {
            // 두 번째 이후 세그먼트 확인
            $last_segment = end($segments);

            // 마지막 세그먼트가 액션인지 확인
            if (isset($this->segmentPermissions[$last_segment])) {
                $action_segment = $last_segment;
            }
            // 숫자가 아닌 세 번째 세그먼트가 액션인지 확인 (예: member/create)
            else if (count($segments) >= 3 && !is_numeric($segments[2]) &&
                isset($this->segmentPermissions[$segments[2]])) {
                $action_segment = $segments[2];
            }
            // ID 다음에 오는 세그먼트가 액션인지 확인 (예: member/1/edit)
            else if (count($segments) >= 4 && is_numeric($segments[2]) &&
                isset($this->segmentPermissions[$segments[3]])) {
                $action_segment = $segments[3];
            }
        }

        return [$base_path, $action_segment];
    }

    /**
     * 세그먼트에 따른 권한 유형 반환
     * @param string|null $segment
     * @return string|null
     */
    private function getPermissionTypeBySegment(?string $segment): ?string
    {
        if ($segment === null) {
            return null;
        }

        return $this->segmentPermissions[$segment] ?? null;
    }

    public function getAuth(int $grade_id, string $path): array
    {
        try{
            $query = DB::table('bl_admin_user_auths')
                ->where('bl_admin_user_auths.member_grade_id', $grade_id)
                ->where('bl_admin_user_auths.menu_path', $path)
                ->select(
                    'bl_admin_user_auths.*',
                );

            return [
                'success' => true,
                'data' => ['row' => $query->first()]
            ];

        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * HTTP 메소드에 따른 권한 유형 반환
     * @param string $method
     * @return string
     */
    private function getPermissionType(string $method): string
    {
        switch ($method) {
            case 'get':
                return 'permission_read';
            case 'post':
            case 'put':
            case 'patch':
                return 'permission_write';
            case 'delete':
                return 'permission_delete';
            default:
                return 'permission_read';
        }
    }
}
