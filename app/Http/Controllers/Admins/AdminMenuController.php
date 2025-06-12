<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FrontendDataService;

class AdminMenuController extends Controller
{


    public function __construct(FrontendDataService $frontendDataService)
    {
        $this->frontendDataService = $frontendDataService;
    }


    // 메뉴 목록 표시
    public function index()
    {
        $rootMenus = $this->getMenusWithChildren();
        return view('admin.menus.index', compact('rootMenus'));
    }

    // 메뉴 데이터 JSON으로 반환 (AJAX 요청용)
    public function getData()
    {
        $rootMenus = $this->getMenusWithChildren();
        return response()->json(['menus' => $rootMenus]);
    }

    // 재귀적으로 메뉴와 하위 메뉴 가져오기
    private function getMenusWithChildren()
    {
        $allMenus = DB::table('bl_menus')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('id');

        $rootMenus = [];

        foreach ($allMenus as $menu) {
            if ($menu->parent_id === null) {
                $menuData = $this->convertMenuToArray($menu);
                $this->addChildrenToMenu($menuData, $allMenus);
                $rootMenus[] = $menuData;
            }
        }

        return $rootMenus;
    }

    // 메뉴 데이터를 배열로 변환
    private function convertMenuToArray($menu)
    {
        return [
            'id' => $menu->menu_id,
            'title' => $menu->title,
            'enTitle' => $menu->en_title,
            'path' => $menu->path,
            'desc' => $menu->description,
            'use' => (bool)$menu->is_active,
            'depth' => $menu->depth,
            'children' => []
        ];
    }

    // 메뉴에 하위 메뉴 추가 (재귀적)
    private function addChildrenToMenu(&$menuData, $allMenus)
    {
        $menuId = DB::table('bl_menus')
            ->where('menu_id', $menuData['id'])
            ->value('id');

        if (!$menuId) return;

        $children = $allMenus->filter(function($item) use ($menuId) {
            return $item->parent_id == $menuId;
        })->sortBy('sort_order');

        foreach ($children as $child) {
            $childData = $this->convertMenuToArray($child);
            $this->addChildrenToMenu($childData, $allMenus);
            $menuData['children'][] = $childData;
        }
    }

    // 메뉴 저장 (AJAX 요청 처리)
    public function store(Request $request)
    {
        // 트랜잭션 시작
        DB::beginTransaction();

        try {
            // 기존 메뉴 삭제
            DB::table('bl_menus')->delete();

            // 새 메뉴 저장
            $menuItems = $request->input('menus', []);
            $this->saveMenuItems($menuItems);

            // JSON 파일 업데이트
            $this->updateFrontendData();

            // 트랜잭션 커밋
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // 오류 발생 시 롤백
            DB::rollBack();
            Log::error('메뉴 저장 중 오류 발생: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '메뉴 저장 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 재귀적으로 메뉴 항목 저장
    private function saveMenuItems($items, $parentId = null, $depth = 1)
    {
        foreach ($items as $index => $item) {
            $id = DB::table('bl_menus')->insertGetId([
                'menu_id' => $item['id'] ?? 'menu_' . uniqid(),
                'title' => $item['title'] ?? '',
                'en_title' => $item['enTitle'] ?? '',
                'path' => $item['path'] ?? '',
                'description' => $item['desc'] ?? '',
                'is_active' => $item['use'] ? 1 : 0,
                'parent_id' => $parentId,
                'depth' => $depth,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 하위 메뉴가 있으면 재귀적으로 저장
            if (!empty($item['children']) && is_array($item['children'])) {
                $this->saveMenuItems($item['children'], $id, $depth + 1);
            }
        }
    }

    // 메뉴 순서 업데이트 (드래그 앤 드롭 후)
    public function updateOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $orders = $request->input('orders', []);

            foreach ($orders as $menuId => $orderData) {
                DB::table('bl_menus')
                    ->where('id', $menuId)
                    ->update([
                        'parent_id' => $orderData['parent_id'] ?? null,
                        'sort_order' => $orderData['sort_order'] ?? 0,
                        'depth' => $orderData['depth'] ?? 1,
                        'updated_at' => now()
                    ]);
            }

            // JSON 파일 업데이트
            $this->updateFrontendData();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('메뉴 순서 업데이트 중 오류 발생: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '메뉴 순서 업데이트 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    // 단일 메뉴 항목 활성화/비활성화
    public function toggleActive(Request $request, $menuId)
    {
        try {
            $menu = DB::table('bl_menus')->where('id', $menuId)->first();

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => '메뉴를 찾을 수 없습니다.'
                ], 404);
            }

            $isActive = !$menu->is_active;

            DB::table('bl_menus')
                ->where('id', $menuId)
                ->update([
                    'is_active' => $isActive,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'isActive' => $isActive
            ]);
        } catch (\Exception $e) {
            Log::error('메뉴 활성화 상태 변경 중 오류 발생: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '메뉴 활성화 상태 변경 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    // 메뉴 항목 삭제
    public function destroy($menuId)
    {
        DB::beginTransaction();

        try {
            // 메뉴와 모든 하위 메뉴 삭제 (외래 키 제약 조건이 있으므로 자동으로 하위 메뉴도 삭제됨)
            DB::table('bl_menus')->where('id', $menuId)->delete();

            // JSON 파일 업데이트
            $this->updateFrontendData();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('메뉴 삭제 중 오류 발생: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '메뉴 삭제 중 오류가 발생했습니다.'
            ], 500);
        }
    }


    /**
     * 프론트엔드 데이터 업데이트
     */
    private function updateFrontendData()
    {
        // 캐시 삭제
        Cache::forget('frontend_json_data');

        // JSON 파일 재생성
        $success = $this->frontendDataService->generateFrontendDataFile();

        if (!$success) {
            logger()->error('Failed to update frontend data file');
        }

        // 또는 Artisan 명령어로 실행
        // Artisan::call('frontend:generate-data');
    }


}
