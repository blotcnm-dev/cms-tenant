<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AdminGradeController extends Controller
{
    /**
     * 등급 명칭 설정 페이지 표시
     */
    public function index()
    {
        // 관리자 등급 목록 가져오기 (code_type = 'master')
        $adminGrades = DB::table('bl_config')
            ->where('code_group', 'member')
            ->where('code_type', 'master')
            ->orderBy('config_id', 'desc') // 높은 레벨부터 표시
            ->get();

        // 일반 사용자 등급 목록 가져오기 (code_type = 'user')
        $userGrades = DB::table('bl_config')
            ->where('code_group', 'member')
            ->where('code_type', 'user')
            ->orderBy('code', 'desc') // 높은 레벨부터 표시
            ->get();

        return view('admin.grade.index', compact('adminGrades', 'userGrades'));
    }

    /**
     * 등급 명칭 업데이트
     */
    public function update(Request $request)
    {

        // 유효성 검사
        $validator = Validator::make($request->all(), [
            'admin_grades' => 'required|array',
            'admin_grades.*.id' => 'required|integer',
            'admin_grades.*.name' => 'required|string|max:100',
            'user_grades' => 'required|array',
            'user_grades.*.id' => 'required|integer',
            'user_grades.*.name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 관리자 등급 업데이트
            foreach ($request->input('admin_grades') as $grade) {
                DB::table('bl_config')
                    ->where('config_id', $grade['id'])
                    ->update([
                        'code_name' => $grade['name'],
                        'updated_at' => now()
                    ]);
            }

            // 사용자 등급 업데이트
            foreach ($request->input('user_grades') as $grade) {
                DB::table('bl_config')
                    ->where('config_id', $grade['id'])
                    ->update([
                        'code_name' => $grade['name'],
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '등급 명칭이 성공적으로 변경되었습니다.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('등급 명칭 업데이트 오류: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '등급 명칭 변경 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 관리자 등급별 권한 표시
     */
    public function auth_index()
    {
        // 관리자 등급 목록 가져오기 (code_type = 'master')
        $adminGrades = DB::table('bl_config')
            ->where('code_group', 'member')
            ->where('code_type', 'master')
            ->orderBy('config_id', 'desc') // 높은 레벨부터 표시
            ->get();
        $origin_menus = config('admin_menu.menus');

        foreach ($origin_menus as $key => $item) {
            // children 배열이 있는지 확인합니다
            if (isset($item['children']) && is_array($item['children'])) {
                // children 배열을 순회하며 code가 "4200"인 항목을 찾습니다
                foreach ($item['children'] as $childKey => $child) {
                    if (isset($child['code']) && $child['code'] === "4200") {
                        // code가 "4200"인 항목을 제거합니다
                        unset($origin_menus[$key]['children'][$childKey]);

                        // 배열 인덱스를 재정렬합니다 (선택 사항)
                        $origin_menus[$key]['children'] = array_values($origin_menus[$key]['children']);
                    }
                }
            }
        }


        return view('admin.grade.auth_index', compact('adminGrades', 'origin_menus'));
    }

    /**
     * 특정 등급의 권한 조회
     */
    public function getPermissions(Request $request, $gradeId)
    {

        $validator = Validator::make(['grade_id' => $gradeId], [
            'grade_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '유효하지 않은 등급입니다.']);
        }

        // 해당 등급의 권한 조회
        $permissions = DB::table('bl_admin_user_auths')
            ->where('member_grade_id', $gradeId)
            ->get();

        // 권한 데이터를 menu_path를 키로 하는 배열로 변환
        $permissionData = [];
        foreach ($permissions as $permission) {
            $permissionData[$permission->menu_code] = [
                'path' => $permission->menu_path,
                'read' => $permission->permission_read,
                'write' => $permission->permission_write,
                'delete' => $permission->permission_delete
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $permissionData
        ]);
    }

    /**
     * 권한 업데이트
     */
    public function updatePermissions(Request $request)
    {
        $gradeId = $request->input('grade_id');
        $permissions = $request->input('permissions');

        $validator = Validator::make([
            'grade_id' => $gradeId,
            'permissions' => $permissions
        ], [
            'grade_id' => 'required|integer',
            'permissions' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '유효하지 않은 데이터입니다.']);
        }

        // 트랜잭션 시작
        DB::beginTransaction();
        try {
            // 기존 권한 삭제
            DB::table('bl_admin_user_auths')
                ->where('member_grade_id', $gradeId)
                ->where('menu_code', '!=', 4200)
                ->delete();

            // 새 권한 입력
            $insertData = [];
            foreach ($permissions as $menuCode  => $permission) {
                $insertData[] = [
                    'member_grade_id' => $gradeId,
                    'menu_code' => $menuCode,
                    'menu_path' => $permission['path'],
                    'permission_read' => $permission['read'] ?? 0,
                    'permission_write' => $permission['write'] ?? 0,
                    'permission_delete' => $permission['delete'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($insertData)) {
                DB::table('bl_admin_user_auths')->insert($insertData);
            }


            // 전체 등급의 캐시 삭제 (1~10 등급)
            for ($gradeId = 1; $gradeId <= 10; $gradeId++) {
                Cache::forget('admin_permissions_' . $gradeId);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => '권한이 성공적으로 업데이트되었습니다.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => '권한 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }





    /**
     * 등급별 권한 보기
     */
    public function viewPermissions($grade_id)
    {
        $query = DB::table('bl_board_configs')
            ->select(
                'bl_board_configs.board_name',
                'bl_board_configs.board_config_id',
                'bl_board_configs.list_view_authority_type',
                'bl_board_configs.content_view_authority_type',
                'bl_board_configs.content_write_authority_type',
            );

        $query->where('bl_board_configs.is_deleted', 0);
        $sortField = "created_at";
        $sortDirection = "desc";
        $query->orderBy('bl_board_configs.'.$sortField, $sortDirection);
        $boards = $query->get();


        $grade_name = DB::table('bl_config')
            ->where('config_id', $grade_id)
            ->select( 'code_name')
            ->first();


        // 권한 정보를 담을 배열
        $user_permissions = [];
        foreach ($boards as $board) {

            $user_permissions[] = [
                'board_id' => $board->board_config_id,
                'board_name' => $board->board_name,
                'read' => $board->list_view_authority_type <= $grade_id ? true : false ,
                'write' => $board->content_view_authority_type <= $grade_id ? true : false ,
                'delete' => $board->content_write_authority_type <= $grade_id ? true : false ,
            ];
        }

        return view('admin.grade.view', compact('grade_name', 'user_permissions'));
    }

    /**
     * 특정 그룹에 대한 권한 정보 가져오기 (예시 함수)
     */
    private function getPermissionsData____($gradeCode, $permissionGroup)
    {
        // 이 부분은 실제 권한 테이블 설계에 따라 구현해야 함
        // 현재는 단순화된 예시 데이터 반환
        switch ($permissionGroup) {
            case 'dashboard':
                return [
                    'read' => true,
                    'update' => $gradeCode >= 3,
                    'delete' => $gradeCode >= 4,
                ];
            case 'site':
                return [
                    'read' => $gradeCode >= 2,
                    'update' => $gradeCode >= 3,
                    'delete' => $gradeCode >= 4,
                ];
            case 'user':
                return [
                    'read' => $gradeCode >= 2,
                    'update' => $gradeCode >= 4,
                    'delete' => $gradeCode >= 5,
                ];
            case 'board':
                return [
                    'read' => $gradeCode >= 1,
                    'update' => $gradeCode >= 3,
                    'delete' => $gradeCode >= 4,
                ];
            case 'support':
                return [
                    'read' => $gradeCode >= 1,
                    'update' => $gradeCode >= 2,
                    'delete' => $gradeCode >= 4,
                ];
            default:
                return [
                    'read' => false,
                    'update' => false,
                    'delete' => false,
                ];
        }
    }
}
