<?php

namespace App\Http\Controllers\Admins\Boards;

use App\Http\Controllers\Controller;
use App\Models\Boards\BoardCategory;
use App\Models\Boards\BoardConfig;
use App\Models\Boards\BoardConfigMemberGrade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BoardConfigController extends Controller
{
    const LIMIT = 15;
    protected $sessionId;

    public function __construct(
        public mixed $boardConfigModel = new BoardConfig
    ) {
        $this->middleware('web');
        $this->middleware(function ($request, $next) {
            $this->sessionId = session()->get('blot_mbid');
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
//    public function index(Request $request) : array
//    {
//        try
//        {
//            $validate = Validator::make(
//                $request->all(),
//                [
//                    'is_active' => 'in:0,1|nullable',
//                ],
//                [
//                    'is_active' => '허용하지 않는 사용 상태입니다.',
//                ]
//            );
//
//            if ($validate->fails()) {
//                throw new Exception($validate->errors()->first());
//            }
//
//            $params = $request->all();
//            $params['limit'] = $request->limit ?? self::LIMIT;
//
//            $params['order'] = $request->period_type ?? 'created_at';
//            $params['by'] = 'desc';
//
//            $boardConfigsResult = $this->boardConfigModel->getBoardConfigs($params, true);
//            if ($boardConfigsResult['success']){
//                foreach ($boardConfigsResult['data']['rows'] as $key => $item) {
//                    $post_count = DB::table('bl_board_' . $item->board_id . '_posts')->count();
//                    $boardConfigsResult['data']['rows'][$key]->post_count = $post_count;
//                }
//            }
//
//            return $boardConfigsResult;
//        }
//        catch (Exception $e)
//        {
//            return [
//                'success' => false,
//                'message' => $e->getMessage()
//            ];
//        }
//    }

    /**
     * Web Display a listing of the resource.
     *
     *
     */
    public function list(Request $request)
    {
        $query = DB::table('bl_board_configs')
            ->leftJoin('bl_members', 'bl_board_configs.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_board_configs.*',
                'bl_members.user_name',
                'bl_members.user_type'
            );

        $query->where('bl_board_configs.is_deleted', 0);

        // 제목 검색
        if ($request->filled('board_name')) {
            $query->where('bl_board_configs.board_name', 'like', '%' . $request->input('board_name') . '%');
        }

        // 유형 검색
        if ($request->filled('board_type') && $request->input('board_type') !== '전체') {
            $reboard_type = $request->board_type === '게시판' ? 'COMMON' : 'GALLERY';
            $query->where('bl_board_configs.board_type', $reboard_type);
        }

        // 등록 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_board_configs.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_board_configs.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 노출 여부 필터링 board_active
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '사용함' ? '1' : '0';
            $query->where('bl_board_configs.is_active', $status);
        }

        // 정렬 설정 추출
        $sortOrder = $request->input('sort_order', 'created_at__desc');
        $sortParts = explode('__', $sortOrder);

        // 분리된 값이 적절한 형식인지 확인
        $sortField = "created_at";
        $sortDirection = "desc";

        if (count($sortParts) >= 2) {
            $sortField = $sortParts[0];
            $sortDirection =  $sortParts[1];
        }
        $query->orderBy('bl_board_configs.'.$sortField, $sortDirection);

        // 페이징 적용
        $boardconfig_contents = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $boardconfig_contents->appends($request->query());

//        //dewbian 디버그용 스크립트
//        $sql = $query->toSql();
//        $bindings = $query->getBindings();
//
//        debug_info($sql);
//        debug_info($bindings);
//
//        echo "<pre>";
//        print_r($boardconfig_contents);
//        echo "</pre>";
//        exit;

        return view('admin.boards.boardConfigList', compact('boardconfig_contents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     *
     */
    public function create()
    {

        $member_config = DB::table('bl_config')
            ->select(DB::raw('code_name, code'))
            ->where('code_group', 'member')
            ->orderBy('config_id', 'asc')
            ->get();

        $category_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '1')
            ->orderBy('depth_code', 'asc')
            ->get();

            return view("admin.boards.boardConfigForm", [
                "mode" => "create",
                "member_level" => $member_config,
                "categorys" => $category_config
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'board_name' => 'required|max:100',
                'board_type' => 'required',
                'board_id' => 'required',
            ], [
                'board_name.required' => '게시판명을 입력하세요.',
                'board_type.required' => '게시판 유형을 선택하세요.',
                'board_id.required' => '게시판 아이디를 입력하세요.',
            ]);

            if ($validator->fails()) {
                return handle_validation_response($request, $validator, 'configBoards.create');
            }

            //테이블 생성
            $createTable = $this->boardConfigModel->createBoardTable(['board_id' => $request->board_id, 'board_name' => $request->board_name]);

            if(!$createTable['success']) {
                return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
            }

            $params = [
                'admin_id' => $this->sessionId ?? '',
                'board_name' => $request->board_name ?? '',
                'board_id' => $request->board_id ?? '',
                'is_active' => $request->is_active ?? 0,
                'board_type' => $request->board_type ?? '',
                'writer_display_type' => $request->writer_mark ?? 'USER_ID',
                'is_display_writer' => $request->is_display_writer ?? 0,
                'is_secret' => $request->is_secret ?? 0,
                'is_auto_secret' => $request->is_auto_secret ?? 0,
                'is_display_hits' => $request->is_display_hits ?? 0,
                'is_new_notification' => $request->is_new_notification ?? 0,
                'new_notification_duration' => $request->new_notification_duration ?? 0,
                'is_captcha' => $request->is_captcha ?? 0,
                'is_prevent_abuse' => $request->is_prevent_abuse ?? 0,
                'abuse_duration' => $request->abuse_duration ?? 1,
                'abuse_count' => $request->abuse_count ?? 2,
                'abuse_block_duration' => $request->abuse_block_duration ?? 5,
                'is_admin_notification' => $request->is_admin_notification ?? 0,
                'is_reply' => $request->is_reply ?? 0,
                'is_reply_like' => $request->is_reply_like ?? 0,
                'is_reply_photo' => $request->is_reply_photo ?? 0,
                'is_like' => $request->is_like ?? 0,
                'is_ban' => $request->is_ban ?? 0,
                'is_topfix' => $request->is_topfix ?? 0,
                'is_secret_reply' => $request->is_secret_reply ?? 0,
                'is_auto_secret_reply' => $request->is_auto_secret_reply ?? 0,
                'is_reply_captcha' => $request->is_reply_captcha ?? 0,
                'is_category' => $request->is_category ?? 0,
                'is_inquiry_type' => $request->is_inquiry_type ?? 0,
                'list_num' => $request->list_num ?? 0,
                'list_view_authority_type' => $request->list_view_authority_type ?? '0',
                'content_view_authority_type' => $request->content_view_authority_type ?? '0',
                'content_write_authority_type' => $request->content_write_authority_type ?? '0',
                'reply_write_authority_type' => $request->reply_write_authority_type ?? '0',
                'ban_words' => $request->ban_words ?? '',
                'is_file' => $request->is_file ?? 0,
                'file_uploadable_count' => $request->file_uploadable_count ?? 0,
                'file_max_size' => $request->file_max_size ?? 0,
                'gallery_theme' => $request->gallery_theme ?? '',
                'gallery_uploadable_count' => $request->gallery_uploadable_count ?? 0,
                'gallery_max_size' => $request->gallery_max_size ?? 0,
                'is_editor' => $request->is_editor ?? 0,
                'incoming_mail' => $request->incoming_mail ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $sql = $this->boardConfigModel->setInsertGetId($params);
            if(!$sql['success']) {
                return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
            }
            return handle_success_response($request,'게시판이 생성되었습니다.','configBoards.list', []);

        } catch (\Exception $e) {
            // 쿼리 실행 중 오류 발생
            return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
        }
    }
    public function getCodeNameByCode(array $rows, string $code): ?string
    {
        foreach ($rows as $item) {
            if ($item->code === $code) {
                return $item->code_name;
            }
        }
        return null;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return
     */
    public function edit(int $board_config_id)
    {
        try
        {

            $member_config = DB::table('bl_config')
                ->select(DB::raw('code_name, code'))
                ->where('code_group', 'member')
                ->orderBy('config_id', 'asc')
                ->get();

            $category_config = DB::table('bl_board_category')
                ->select(DB::raw('depth_code, kname'))
                ->where('is_view', 'Y')
                ->where('depth', '1')
                ->orderBy('depth_code', 'asc')
                ->get();

            $board_config = DB::table('bl_board_configs as bc')
                ->leftJoin('bl_members as m'
                    , 'bc.admin_id', '=', 'm.member_id')
                ->leftJoin('bl_board_category as c', function($join) {
                    $join->on('bc.is_category', '=', 'c.depth_code')
                        ->where('c.is_view', 'Y');
                })
                ->where('bc.board_config_id', $board_config_id)
                ->select([
                    'bc.*',
                    'm.user_name',
                    'c.kname as category_txt',
                ])
                ->first();

            $board_config->list_view_authority_type_tmp = $this->getCodeNameByCode($member_config->toArray(), $board_config->list_view_authority_type);
            $board_config->content_view_authority_type_tmp = $this->getCodeNameByCode($member_config->toArray(), $board_config->content_view_authority_type);
            $board_config->content_write_authority_type_tmp = $this->getCodeNameByCode($member_config->toArray(), $board_config->content_write_authority_type);
            $board_config->reply_write_authority_type_tmp = $this->getCodeNameByCode($member_config->toArray(), $board_config->reply_write_authority_type);

            return view('admin.boards.boardConfigEditForm', [
                'mode' => 'edit',
                'board_config' => $board_config,
                "member_level" => $member_config,
                "categorys" => $category_config
            ]);
        }
        catch (Exception $e)
        {
        return redirect()
            ->back()
            ->withErrors('데이터 처리 중 오류가 발생했습니다.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function update(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'board_name' => 'required|max:100',
            ], [
                'board_name.required' => '게시판명을 입력하세요.',
            ]);

            if ($validator->fails()) {
                return handle_validation_response($request, $validator, 'configBoards.create');
            }

            $params = [
                'admin_id' => $this->sessionId ?? '',
                'board_name' => $request->board_name ?? '',
                'board_id' => $request->board_id ?? '',
                'is_active' => $request->is_active ?? 0,
                'board_type' => $request->board_type ?? '',
                'writer_display_type' => $request->writer_mark ?? 'USER_ID',
                'is_display_writer' => $request->is_display_writer ?? 0,
                'is_secret' => $request->is_secret ?? 0,
                'is_auto_secret' => $request->is_auto_secret ?? 0,
                'is_display_hits' => $request->is_display_hits ?? 0,
                'is_new_notification' => $request->is_new_notification ?? 0,
                'new_notification_duration' => $request->new_notification_duration ?? 0,
                'is_captcha' => $request->is_captcha ?? 0,
                'is_prevent_abuse' => $request->is_prevent_abuse ?? 0,
                'abuse_duration' => $request->abuse_duration ?? 1,
                'abuse_count' => $request->abuse_count ?? 2,
                'abuse_block_duration' => $request->abuse_block_duration ?? 5,
                'is_admin_notification' => $request->is_admin_notification ?? 0,
                'is_reply' => $request->is_reply ?? 0,
                'is_reply_like' => $request->is_reply_like ?? 0,
                'is_reply_photo' => $request->is_reply_photo ?? 0,
                'is_like' => $request->is_like ?? 0,
                'is_ban' => $request->is_ban ?? 0,
                'is_topfix' => $request->is_topfix ?? 0,
                'is_secret_reply' => $request->is_secret_reply ?? 0,
                'is_auto_secret_reply' => $request->is_auto_secret_reply ?? 0,
                'is_reply_captcha' => $request->is_reply_captcha ?? 0,
                'is_category' => $request->is_category ?? 0,
                'is_inquiry_type' => $request->is_inquiry_type ?? 0,
                'list_num' => $request->list_num ?? 0,
                'list_view_authority_type' => $request->list_view_authority_type ?? '0',
                'content_view_authority_type' => $request->content_view_authority_type ?? '0',
                'content_write_authority_type' => $request->content_write_authority_type ?? '0',
                'reply_write_authority_type' => $request->reply_write_authority_type ?? '0',
                'ban_words' => $request->ban_words ?? '',
                'is_file' => $request->is_file ?? 0,
                'file_uploadable_count' => $request->file_uploadable_count ?? 0,
                'file_max_size' => $request->file_max_size ?? 0,
                'gallery_theme' => $request->gallery_theme ?? '',
                'gallery_uploadable_count' => $request->gallery_uploadable_count ?? 0,
                'gallery_max_size' => $request->gallery_max_size ?? 0,
                'is_editor' => $request->is_editor ?? 0,
                'incoming_mail' => $request->incoming_mail ?? '',
                'updated_at' => now(),
            ];

            $sql = $this->boardConfigModel->setUpdate($params, ['board_config_id' => $request->board_config_id]);

            if(!$sql['success']) {
                return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
            }
            return handle_success_response($request,'게시판이 수정되었습니다.','configBoards.list', []);

        } catch (\Exception $e) {
            // 쿼리 실행 중 오류 발생
            return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return array
     */
    public function destroy(int $board_config_id)
    {

        $deleted = DB::table('bl_board_configs')
            ->where('board_config_id', $board_config_id)
            ->update([
                'is_deleted' => 1
            ]);

        if (!$deleted) {
            return redirect()->route('configBoards.list')
                ->with('error', '게시글 삭제에 실패했습니다.');
        }

        return redirect()->route('configBoards.list')
            ->with('success', '게시글이 성공적으로 삭제되었습니다.');

//        return redirect()
//            ->back()
//            ->withErrors('데이터 처리 중 오류가 발생했습니다.');
    }

    /**
     * categorylist
     *
     */
    public function categorylist()
    {

        $category_config = DB::table('bl_board_category')
            ->select(DB::raw('board_category_id, parent_id, depth_code, kname, depth, sort_order'))
            ->where('is_view', 'Y')
            ->where('depth', '1')
            ->orderBy('depth_code', 'asc')
            ->get();
        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('board_category_id, parent_id, depth_code, kname, depth, sort_order'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view("admin.boards.layer_classManagement", ["categorys" => $category_config, "categorys_sub" => $category_sub_config]);
    }

    /**
     * categorylist
     *
     */
    public function categorylist_ajax()
    {

        $category_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '1')
            ->orderBy('depth_code', 'asc')
            ->get();

        $returnData = [ 'categorylist' => $category_config ];

        return [
            'success' => true,
            'data' => $returnData
        ];
    }

    public function categorystore(Request $request)
    {

        // 1. 검증
        $validator = Validator::make($request->all(), [
            'cate'   => 'required|array',
            'cate.*' => 'required|string|max:100',
        ], [
            'cate.required'   => '카테고리명이 등록 되지 않은 항목이 있습니다.',
            'cate.*.required' => '각 카테고리명을 입력하세요.',
            'cate.*.max'      => '카테고리명은 최대 100자까지 입력 가능합니다.',
        ]);

        if ($validator->fails()) {
            // AJAX / JSON 요청이면 JSON 으로 응답
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            // 일반 요청이면 리다이렉트
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // 1. 입력값 가져오기
        $codes  = $request->input('code', []);   // ex. ['010000','010100',…,'']
        $depths = $request->input('depth', []);  // ex. [1,2,2,2,1,2,2,1]
        $cates  = $request->input('cate', []);   // ex. ['카테고리1',…,'카테고리3']

        // 2. 작업용 복사본
        $generated = $codes;

        // 3. 이미 있는 depth=1 코드 그룹(앞2자리) 수집
        $depth1Groups = [];
        foreach ($generated as $i => $code) {
            if (!empty($code) && isset($depths[$i]) && $depths[$i] == 1) {
                $depth1Groups[] = (int) substr($code, 0, 2);
            }
        }

        // 4. 순회하며 빈 code 자동 생성
        $currentParent = null; // depth=2일 때 사용할 부모 그룹
        $total = count($depths);
        for ($i = 0; $i < $total; $i++) {
            // 이미 코드가 있으면 depth1 일 경우 부모 갱신
            if (!empty($generated[$i])) {
                if ($depths[$i] == 1) {
                    $currentParent = (int) substr($generated[$i], 0, 2);
                }
                continue;
            }

            // depth = 1 → 새로운 최상위 코드 생성
            if ($depths[$i] == 1) {
                $max = !empty($depth1Groups) ? max($depth1Groups) : 0;
                $new = $max + 1;
                $grp = str_pad($new, 2, '0', STR_PAD_LEFT);
                $generated[$i] = "{$grp}0000";
                $depth1Groups[] = $new;
                $currentParent = $new;

                // depth = 2 → 현재 부모 그룹 기준으로 중간 2자리 증가
            } elseif ($depths[$i] == 2) {
                // (1) 부모 그룹이 비어 있으면, 바로 위 depth=1 항목 찾아 설정
                if (is_null($currentParent)) {
                    for ($j = $i - 1; $j >= 0; $j--) {
                        if ($depths[$j] == 1) {
                            $currentParent = (int) substr($generated[$j], 0, 2);
                            break;
                        }
                    }
                }
                // (2) 같은 부모 그룹의 기존 자식들 중간 숫자(max) 꺼내기
                $childNums = [];
                for ($j = 0; $j < $i; $j++) {
                    if ($depths[$j] == 2 && !empty($generated[$j])) {
                        if (substr($generated[$j], 0, 2) === str_pad($currentParent, 2, '0', STR_PAD_LEFT)) {
                            $childNums[] = (int) substr($generated[$j], 2, 2);
                        }
                    }
                }
                $maxChild = !empty($childNums) ? max($childNums) : 0;
                $nextChild = $maxChild + 1;
                $grp2 = str_pad($nextChild, 2, '0', STR_PAD_LEFT);
                $generated[$i] = str_pad($currentParent, 2, '0', STR_PAD_LEFT) . "{$grp2}00";
            }
        }

        // 5. 순서대로 DB 저장
        $rows = [];
        foreach ($generated as $i => $code) {
            if($cates[$i]) {
                $rows[] = [
                    'depth_code' => $code,
                    'kname' => $cates[$i],
                    'depth' => $depths[$i],
                    'sort_order' => $i,
                ];
            }
        }

        //is_view 를 'N'로 변경후 신규 등록
        DB::table('bl_board_category')
            ->where('is_view', 'Y')
            ->update([
                'is_view' => 'N'
            ]);
//        DB::table('bl_board_category')->truncate();
        DB::table('bl_board_category')->insert($rows);

        return response()->json([
            'success' => true
        ], 200);
    }

    /**
     * boardcopy
     *
     */
    public function boardcopy(int $board_config_id)
    {
        try
        {
            $board_config = DB::table('bl_board_configs')
                ->select(
                    'board_config_id',
                    'board_name'
                )
                ->where('board_config_id', $board_config_id)->first();

            return view("admin.boards.boardCopy", [
                'board_config' => $board_config
            ]);
        } catch (\Exception $e) {
            // 쿼리 실행 중 오류 발생
            return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
        }

    }
    /**
     * boardcopy
     *
     */
    public function boardcopyadd(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'board_name' => 'required|max:100',
                'board_config_id' => 'required',
                'board_id' => 'required',
            ], [
                'board_name.required' => '게시판명을 입력하세요.',
                'board_config_id.required' => '선택된 게시판명이 없습니다.',
                'board_id.required' => '보도 아이디가 생성되지 않았습니다.',
            ]);

            if ($validator->fails()) {
                return handle_validation_response($request, $validator, 'configBoards.list');
            }

            //복제할 목록
            $board_config = DB::table('bl_board_configs')
                ->select(
                    'bl_board_configs.*'
                )
                ->where('board_config_id', $request->board_config_id)->first();


            //테이블 생성
            $createTable = $this->boardConfigModel->createBoardTable(['board_id' => $request->board_id, 'board_name' => $request->board_name]);

            if(!$createTable['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'board_name' => ['데이터 업데이트 중 오류가 발생했습니다.']
                    ]
                ], 422);
            }

            $params = [
                'admin_id' => $this->sessionId ?? '',
                'board_name' => $request->board_name ?? '',
                'board_id' => $request->board_id ?? '',
                'is_active' => $board_config->is_active ?? 0,
                'board_type' => $board_config->board_type ?? '',
                'writer_display_type' => $board_config->writer_mark ?? 'USER_ID',
                'is_display_writer' => $board_config->is_display_writer ?? 0,
                'is_secret' => $board_config->is_secret ?? 0,
                'is_auto_secret' => $board_config->is_auto_secret ?? 0,
                'is_display_hits' => $board_config->is_display_hits ?? 0,
                'is_new_notification' => $board_config->is_new_notification ?? 0,
                'new_notification_duration' => $board_config->new_notification_duration ?? 0,
                'is_captcha' => $board_config->is_captcha ?? 0,
                'is_prevent_abuse' => $board_config->is_prevent_abuse ?? 0,
                'abuse_duration' => $board_config->abuse_duration ?? 1,
                'abuse_count' => $board_config->abuse_count ?? 2,
                'abuse_block_duration' => $board_config->abuse_block_duration ?? 5,
                'is_admin_notification' => $board_config->is_admin_notification ?? 0,
                'is_reply' => $board_config->is_reply ?? 0,
                'is_reply_like' => $board_config->is_reply_like ?? 0,
                'is_reply_photo' => $board_config->is_reply_photo ?? 0,
                'is_like' => $board_config->is_like ?? 0,
                'is_ban' => $board_config->is_ban ?? 0,
                'is_topfix' => $board_config->is_topfix ?? 0,
                'is_secret_reply' => $board_config->is_secret_reply ?? 0,
                'is_auto_secret_reply' => $board_config->is_auto_secret_reply ?? 0,
                'is_reply_captcha' => $board_config->is_reply_captcha ?? 0,
                'is_category' => $board_config->is_category ?? 0,
                'is_inquiry_type' => $board_config->is_inquiry_type ?? 0,
                'list_num' => $board_config->list_num ?? 0,
                'list_view_authority_type' => $board_config->list_view_authority_type ?? '0',
                'content_view_authority_type' => $board_config->content_view_authority_type ?? '0',
                'content_write_authority_type' => $board_config->content_write_authority_type ?? '0',
                'reply_write_authority_type' => $board_config->reply_write_authority_type ?? '0',
                'ban_words' => $board_config->ban_words ?? '',
                'is_file' => $board_config->is_file ?? 0,
                'file_uploadable_count' => $board_config->file_uploadable_count ?? 0,
                'file_max_size' => $board_config->file_max_size ?? 0,
                'gallery_theme' => $board_config->gallery_theme ?? '',
                'gallery_uploadable_count' => $board_config->gallery_uploadable_count ?? 0,
                'gallery_max_size' => $board_config->gallery_max_size ?? 0,
                'is_editor' => $board_config->is_editor ?? 0,
                'incoming_mail' => $board_config->incoming_mail ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $sql = $this->boardConfigModel->setInsertGetId($params);
            if(!$sql['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'board_name' => ['데이터 업데이트 중 오류가 발생했습니다.']
                    ]
                ], 422);
            }

            if($request->copy_type === 'structure_data') {
                $postTableName_old = 'bl_board_' . $board_config->board_id . "_posts";
                $replyTableName_old = 'bl_board_' . $board_config->board_id . "_replies";
                $fileTableName_old = 'bl_board_' . $board_config->board_id . "_files";
                $postTableName_new = 'bl_board_' . $request->board_id . "_posts";
                $replyTableName_new = 'bl_board_' . $request->board_id . "_replies";
                $fileTableName_new = 'bl_board_' . $request->board_id . "_files";

                DB::statement("INSERT INTO `{$postTableName_new}` SELECT * FROM `{$postTableName_old}`");
                DB::statement("INSERT INTO `{$replyTableName_new}` SELECT * FROM `{$replyTableName_old}`");
                DB::statement("INSERT INTO `{$fileTableName_new}` SELECT * FROM `{$fileTableName_old}`");
            }

            return response()->json([
                'success' => true,
                'message' => '게시판이 성공적으로 생성되었습니다.',
                'redirect' => route('configBoards.list')  // 선택사항
            ], 200);

        } catch (\Exception $e) {
            // 쿼리 실행 중 오류 발생
            return response()->json([
                'success' => false,
                'errors' => [
                    'board_name' => ['데이터 업데이트 중 오류가 발생했습니다.']
                ]
            ], 422);
        }

    }
}
