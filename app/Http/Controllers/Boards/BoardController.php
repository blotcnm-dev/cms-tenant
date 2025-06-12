<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Libraries\AutoSentLibrary;
use App\Models\Boards\BoardBlockMember;
use App\Models\Boards\BoardCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Pawlox\VideoThumbnail\Facade\VideoThumbnail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BoardController extends Controller
{
    protected $sessionId;
    protected $sessionugrd;
    public function __construct()
    {
        $this->middleware('web');
        $this->middleware(function ($request, $next) {
            $this->sessionId = session()->get('blot_mbid');
            $this->sessionugrd = session()->get('blot_ugrd');//권한
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        return redirect('/');
    }

    /**
     * Web Display a listing of the resource.
     *
     * @return
     */
    public function list(Request $request, string $board_id)
    {
//        try
//        {
            $validateRoleRequest = array_merge($request->all(), ['board_id' => $board_id]);

            $validator = Validator::make($validateRoleRequest, [
                'board_id' => 'required',
            ], [
                'board_id.required' => '게시판 정보가 없습니다.',
            ]);

            if ($validator->fails()) {
                return alertAndRedirect('게시판 정보가 없습니다.', 'index');
            }

            //board_id, board_name, board_type, is_category,list_view_authority_type,content_view_authority_type,content_write_authority_type,reply_write_authority_type
            $board_config = DB::table('bl_board_configs')
                ->select(DB::raw('bl_board_configs.*'))
                ->where('is_deleted', '0')
                ->where('is_active', '1')
                ->where('bl_board_configs.board_id', $board_id)->first();

            if (!$board_config) {
                return alertAndRedirect('게시판 정보가 없습니다.', 'index');
            }

            //카테고리가 있을경우
//            if ($board_config->is_category) {
//                $category_sub_config = DB::table('bl_board_category')
//                    ->select(DB::raw('depth_code, kname'))
//                    ->where('is_view', 'Y')
//                    ->where('depth', '2')
//                    ->where('depth_code', 'like', substr($board_config->is_category,0,2) . '%')
//                    ->orderBy('sort_order', 'asc')
//                    ->get();
//            } else {
                $category_sub_config = [];
//            }

            // 게시판
            $post_table = 'bl_board_' . $board_id . '_posts';

            $query = DB::table($post_table)
                ->leftJoin('bl_board_category', function($join) use ($post_table) {
                    $join->on('bl_board_category.depth_code', '=', "{$post_table}.category")
                        ->where('bl_board_category.is_view', 'Y');
                })
                ->leftJoin('bl_members', 'bl_members.member_id', '=', $post_table.'.member_id')
                ->select(DB::raw($post_table.'.*,bl_board_category.kname,bl_members.user_name'));

            $query->where($post_table.'.is_display', 1);

            // 검색 조건
            if ($request->filled('fild_id') && $request->filled('fild_val')) {
                $fieldId = $request->input('fild_id');
                $fieldVal = $request->input('fild_val');

                // 허용된 필드만 검색 가능하도록 보안 강화
                $allowedFields = ['content', 'subject'];

                if (in_array($fieldId, $allowedFields)) {
                    $query->where($post_table.'.'.$fieldId, 'like', '%' . $fieldVal . '%');
                }
            }


            // 정렬 설정 추출
            $sortOrder = $request->input('sort_order');
            $sortParts = explode('__', $sortOrder);

            // 분리된 값이 적절한 형식인지 확인
            $sortField = "created_at";
            $sortDirection = "desc";

            if (count($sortParts) >= 2) {
                $sortField = $sortParts[0];
                $sortDirection =  $sortParts[1];

            }
            $query->orderBy($post_table.'.is_best', 'desc')->orderBy($post_table.'.'.$sortField, $sortDirection);

            // 페이징 적용
            if ($request->filled('limit_cnt')) {
                $limitCnt = $request->input('limit_cnt');
            } else {
                $limitCnt = 10;
            }
            $boardlist_contents = $query->paginate($limitCnt);
            // 페이지네이션 링크에 현재 쿼리스트링 유지
            $boardlist_contents->appends($request->query());

            $board_config = (array) $board_config;
            $board_config['is_read'] = 'N';
            $board_config['is_write'] = 'N';
            $board_config['is_del'] = 'N';
            $board_config['is_replay'] = 'N';
            if($board_config['list_view_authority_type'] === 0 || $board_config['list_view_authority_type'] <= $this->sessionugrd) {
                $board_config['is_read'] = 'Y';
            }
            if($board_config['content_view_authority_type'] === 0 || $board_config['content_view_authority_type'] <= $this->sessionugrd) {
                $board_config['is_write'] = 'Y';
            }
            if($board_config['content_write_authority_type'] === 0 || $board_config['content_write_authority_type'] <= $this->sessionugrd) {
                $board_config['is_del'] = 'Y';
            }
            if($board_config['reply_write_authority_type'] === 0 || $board_config['reply_write_authority_type'] <= $this->sessionugrd) {
                $board_config['is_replay'] = 'Y';
            }
            $board_config = (object) $board_config;

            return view('web.board.list', ['board_config' => $board_config, 'boards' => $boardlist_contents, 'category_sub' => $category_sub_config]);

//        }
//        catch (Exception $e)
//        {
//            return handle_success_response($request,'데이터 처리 중 오류가 발생했습니다.','index', []);
//        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|array
     */
    public function create($board_id)
    {
        try
        {

            $validateRoleRequest = array_merge(['board_id' => $board_id]);

            $validator = Validator::make($validateRoleRequest, [
                'board_id' => 'required',
            ], [
                'board_id.required' => '게시판 아이디는 필수 입니다.',
            ]);

            $validateRoleRequest = (object) $validateRoleRequest;

            if ($validator->fails()) {
                return alertAndRedirect('게시판 정보가 없습니다.', 'index');
            }

            $validateRoleRequest = (object) $validateRoleRequest;

            $board_config = DB::table('bl_board_configs')
                ->select(DB::raw('bl_board_configs.*'))
                ->where('is_deleted', '0')
                ->where('is_active', '1')
                ->where('bl_board_configs.board_id', $validateRoleRequest->board_id)->first();

            if (!$board_config) {
                return alertAndRedirect('게시판 정보가 없습니다.', 'index');
            }

            //쓰기권한 체크
            if($board_config->content_view_authority_type === 0 || $board_config->content_view_authority_type <= $this->sessionugrd) {} else {
                return alertAndRedirect('권한이 없습니다.', 'boards.list', [$board_config->board_id]);
            }

            //카테고리가 있을경우
            if ($board_config->is_category) {
                $category_sub_config = DB::table('bl_board_category')
                    ->select(DB::raw('depth_code, kname'))
                    ->where('is_view', 'Y')
                    ->where('depth', '2')
                    ->where('depth_code', 'like', substr($board_config->is_category,0,2) . '%')
                    ->orderBy('sort_order', 'asc')
                    ->get();
            } else {
                $category_sub_config = [];
            }

            return view('web.board.create', [
                'board_config' => $board_config,
                'board_id' => $board_id,
                'category_sub' => $category_sub_config
            ]);
        }
        catch (Exception $e)
        {
            return alertAndRedirect('데이터 처리 중 오류가 발생했습니다.', 'boards.list', [$board_config->board_id]);
        }
    }

    public function store(Request $request, string $board_id)
    {
//        try
//        {
            if (!$board_id) {
                throw new Exception((string) "게시판 유형이 누락되었습니다.");
            }

            $validator = Validator::make($request->all(), [
                'subject' => 'required',
            ], [
                'subject.required' => '제목은 필수 입력입니다.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            //금칙어 체크 로직
            $board_config = DB::table('bl_board_configs')
                ->select(DB::raw('bl_board_configs.*'))
                ->where('is_deleted', '0')
                ->where('is_active', '1')
                ->where('bl_board_configs.board_id', $board_id)->first();

            if (!$board_config) {
                return alertAndRedirect('게시판 정보가 없습니다.', 'index');
            }

            $bl_config = DB::table('bl_config')
                ->select(DB::raw('value'))
                ->where('code', 'forbid_settings')->first();

            // 금칙어 처리
            if ($board_config->is_ban === 1) {
                $filter = implode(
                    '|',
                    array_column(
                        json_decode($bl_config->value, true)['words'],
                        'word'
                    )
                );
                if (preg_match_all('/('.$filter.')/', $request->subject, $match) == true) {
                    return response()->json([
                        'success' => false,
                        'errors'  => [
                            'subject' => ['제목에 허용되지 않는 문자를 포함하고 있습니다.']
                        ],
                    ], 422);
                }
                if (preg_match_all('/('.$filter.')/', $request->contents, $match) == true) {
                    return response()->json([
                        'success' => false,
                        'errors'  => [
                            'contents' => ['내용에 허용되지 않는 문자를 포함하고 있습니다.']
                        ],
                    ], 422);
                }
            }

            // 유저가 비밀글 사용을 선택했을때
//            if (isset($request->is_secret)) {
//                if ($request->is_secret === 1) {
//                    if(!isset($request->secret_password) || empty($request->secret_password)) {
//                        throw new Exception((string) '비밀번호를 입력하세요.');
//                    }
//
//                    $params['is_secret'] = $request->is_secret;
//                    $params['secret_password'] = Hash::make($request->secret_password);
//                }
//            }


            $table_post = 'bl_board_'.$board_id.'_posts';
            $table_files = 'bl_board_'.$board_id.'_files';
            $post_id = DB::table($table_post)
                ->insertGetId([
                    'category'   => $request->category   ?? '',
                    'subject'    => $request->subject    ?? '',
                    'content'    => $request->contents   ?? '',
                    'is_secret'  => $request->is_secret  ?? 0,
                    'secret_password'  => $request->secret_password  ?? '',
                    'member_id'  => $this->sessionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            // 허용 확장자 배열
            $allowed_ext = [
                'jpg','jpeg','png','gif','bmp',
                'pdf','doc','docx','ppt','pptx','txt',
                'zip','rar','7z'
            ];

            $hash    = md5($post_id);
            $level1 = substr($hash, 0, 2);  // "c8"
            $level2 = substr($hash, 2, 2);  // "1e"

            // 일반첨부파일이 업로드되었는지 확인
            if ($request->hasFile('post_file') && $post_id) {
                foreach ($request->file('post_file') as $file) {

                    if ($file->isValid()) {
                        $ext = strtolower($file->getClientOriginalExtension());

                        // 확장자와 MIME 타입 둘 다 검증
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/' . $board_id . '/' . $level1 . '/' . $level2 , 'public');
                            $sizeBytes = $file->getSize();
                            $sizeMB = round($sizeBytes / 1024 / 1024, 2);//MB
//                            $filetype = $file->getMimeType();

                            // 확장자 기반으로 MIME 타입 설정
                            $filetype = $this->getMimeTypeByExtension($ext);
                            // 결과 배열에 추가
                            DB::table($table_files)->insert([
                                'post_id' => $post_id,
                                'post_type' => 'POSTS',
                                'ftype' => $filetype,
                                'fsize' => $sizeMB,
                                'path' => '/storage/'.$storedPath,
                                'fname' => $originalName,
                                'created_at' => NOW()
                            ]);
                        }
                    }
                }
            }
            // 겔러리파일이 업로드되었는지 확인
            if ($request->hasFile('gallery_file') && $post_id) {
                foreach ($request->file('gallery_file') as $file) {
                    if ($file->isValid()) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/' . $board_id . '/' . $level1 . '/' . $level2 , 'public');
                            $sizeBytes = $file->getSize();
                            $sizeMB = round($sizeBytes / 1024 / 1024, 2);//MB
                            $filetype = $file->getMimeType();
                            // 결과 배열에 추가
                            DB::table($table_files)->insert([
                                'post_id' => $post_id,
                                'post_type' => 'POSTS',
                                'ftype' => $filetype,
                                'fsize' => $sizeMB,
                                'path' => '/storage/'.$storedPath,
                                'fname' => $originalName,
                                'created_at' => NOW()
                            ]);
                        }
                    }
                }
            }

        return [
            'success' => true,
            'message' => '등록 되었습니다.'
        ];
//        }
//        catch(Exception $e)
//        {
//            return [
//                'success' => false,
//                'message' => '데이터 처리 중 오류가 발생했습니다.'
//            ];
//        }
    }

    private function getMimeTypeByExtension($ext)
    {
        $mimeTypes = [
            // 이미지 파일
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',

            // 문서 파일
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',

            // 압축 파일
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed'
        ];

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
    /**
     * 게시글 상세 보기
     */
    public function show(Request $request, string $board_id, int $post_id)
    {

        $validateRoleRequest = array_merge(['board_id' => $board_id, 'post_id' => $post_id]);

        $validator = Validator::make($validateRoleRequest, [
            'board_id' => 'required',
            'post_id' => 'required|numeric',
        ], [
            'board_id.required' => '게시판 아이디는 필수 입니다.',
            'post_id.required' => '글번호는 필수 입니다.',
            'post_id.numeric' => '잘못된 접근입니다.',
        ]);

        $validateRoleRequest = (object) $validateRoleRequest;
        if ($validator->fails()) {
            return alertAndRedirect('게시판 정보가 없습니다.', 'boards.list', [$board_id]);
        }

        $board_config = DB::table('bl_board_configs')
            ->select(DB::raw('bl_board_configs.*'))
            ->where('is_deleted', '0')
            ->where('is_active', '1')
            ->where('bl_board_configs.board_id', $validateRoleRequest->board_id)->first();

        if (!$board_config) {
            return alertAndRedirect('게시판 정보가 없습니다.', 'index');
        }

        if($this->sessionId) {
            $member_id_tmp = $this->sessionId;
        } else {
            $member_id_tmp = 0;
        }

        $table_post = 'bl_board_'.$board_id.'_posts';
        $table_files = 'bl_board_'.$board_id.'_files';
        $table_likes = 'bl_board_'.$board_id.'_likes';
        $boards = DB::table($table_post)
            ->leftJoin('bl_board_category', function($join) use ($table_post) {
                $join->on('bl_board_category.depth_code', '=', "{$table_post}.category")
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->leftJoin('bl_members', 'bl_members.member_id', '=', "{$table_post}.member_id")
            ->select([
                DB::raw("{$table_post}.*"),
                'bl_board_category.kname',
                'bl_members.user_name',
                'bl_members.nick_name',
                DB::raw("(SELECT code_name
                 FROM bl_config
                 WHERE code_group = 'member'
                   AND code = bl_members.member_grade_id) AS code_name"),
                DB::raw("(SELECT id
                 FROM {$table_likes}
                 WHERE post_type = 'POSTS'
                   AND post_id = {$table_post}.post_id
                   AND member_id = {$member_id_tmp}) AS likes_id"),
                'bl_members.profile_image'
            ])
            ->where("{$table_post}.is_display", '1')
            ->where("{$table_post}.post_id", $post_id)
            ->first();

        if (!$boards) {
            return alertAndRedirect('존재하지 않는 게시글 입니다.', 'boards.list', [$board_id]);
        }

        //비밀글 체크
        if ($boards->is_secret === 1 && $boards->member_id !== $member_id_tmp) {
            return alertAndRedirect('비밀글 설정된 게시글 입니다.', 'boards.list', [$board_id]);
        }


        $files = DB::table($table_files)
            ->select(DB::raw('fname, path'))
            ->where('post_id', $post_id)
            ->orderBy('id', 'asc')
            ->get();

        // 세션에 조회한 게시글 ID 저장
        $viewedPosts = session('viewed_posts', []);
        $sess_addid = $board_id."_".$post_id;
        // 이미 조회한 게시글이 아니면 조회수 증가
        if (!in_array($sess_addid, $viewedPosts)) {

            //조회수 올리기
            DB::table($table_post)
                ->where('post_id', $post_id)
                ->increment('hits');

            // 세션에 조회한 게시글 ID 추가
            $viewedPosts[] = $sess_addid;
            session(['viewed_posts' => $viewedPosts]);
        }

        $board_config = (array) $board_config;
        $board_config['is_read'] = 'N';
        $board_config['is_write'] = 'N';
        $board_config['is_del'] = 'N';
        $board_config['is_replay'] = 'N';
        if($board_config['list_view_authority_type'] === 0 || $board_config['list_view_authority_type'] <= $this->sessionugrd) {
            $board_config['is_read'] = 'Y';
        }
        if($board_config['content_view_authority_type'] === 0 || $board_config['content_view_authority_type'] <= $this->sessionugrd) {
            $board_config['is_write'] = 'Y';
        }
        if($board_config['content_write_authority_type'] === 0 || $board_config['content_write_authority_type'] <= $this->sessionugrd) {
            $board_config['is_del'] = 'Y';
        }
        if($board_config['reply_write_authority_type'] === 0 || $board_config['reply_write_authority_type'] <= $this->sessionugrd) {
            $board_config['is_replay'] = 'Y';
        }
        $board_config = (object) $board_config;

        return view('web.board.show', ['board_config' => $board_config, 'boards' => $boards, 'files' => $files]);
    }

//좋아요 받는곳
    public function likes_ajax(Request $request, string $board_id)
    {
        try
        {
            if (!$board_id) {
                throw new Exception((string) "게시판 유형이 누락되었습니다.");
            }

            $validator = Validator::make($request->all(), [
                'fild_id' => 'required',
                'fild_val' => 'required',
                'like_type' => 'required',
                'post_id' => 'required',
            ], [
                'fild_id.required' => '정보가 부족합니다.',
                'fild_val.required' => '정보가 부족합니다.',
                'like_type.required' => '정보가 부족합니다.',
                'post_id.required' => '정보가 부족합니다.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            if($request->like_type === 'post') {
                $table_post = 'bl_board_'.$board_id.'_posts';
                $post_type = 'POSTS';
                $fild_name = 'post_id';
            } elseif($request->like_type === 'reply') {
                $table_post = 'bl_board_'.$board_id.'_replies';
                $post_type = 'REPLIES';
                $fild_name = 'id';
            }

            //로그인 회원만 좋아요 적용
            if($this->sessionId) {
                $table_post_likes = 'bl_board_'.$board_id.'_likes';

                // 기존 좋아요 확인
                $existingLike = DB::table($table_post_likes)
                    ->where('post_id', $request->post_id)
                    ->where('post_type', $post_type)
                    ->where('member_id', $this->sessionId)
                    ->exists();

                if (!$existingLike) {
                    DB::table($table_post)
                        ->where($fild_name, $request->post_id)
                        ->update([
                            'likes' => DB::raw('GREATEST(likes + 1, 0)')
                        ]);

                    DB::table($table_post_likes)->insert([
                        'post_id' => $request->post_id,
                        'post_type' => $post_type,
                        'member_id' => $this->sessionId,
                        'created_at' => NOW()
                    ]);
                } else {
                    DB::table($table_post)
                        ->where($fild_name, $request->post_id)
                        ->update([
                            'likes' => DB::raw('GREATEST(likes - 1, 0)')
                        ]);
                    DB::delete("DELETE FROM {$table_post_likes} WHERE post_id = ? AND post_type = ? AND member_id = ?", [
                        $request->post_id,
                        $post_type,
                        $this->sessionId
                    ]);
                }

            }

            return [
                'success' => true,
                'message' => '적용 되었습니다.'
            ];
        }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => '데이터 처리 중 오류가 발생했습니다.'
            ];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param
     * @return
     */
    public function edit(Request $request, string $board_id, int $post_id)
    {
        $validateRoleRequest = array_merge(['board_id' => $board_id, 'post_id' => $post_id]);

        $validator = Validator::make($validateRoleRequest, [
            'board_id' => 'required',
            'post_id' => 'required',
        ], [
            'board_id.required' => '게시판 아이디는 필수 입니다.',
            'post_id.required' => '글번호는 필수 입니다.',
        ]);

        $validateRoleRequest = (object) $validateRoleRequest;

        if ($validator->fails()) {
            return alertAndRedirect('정보가 없습니다.', 'boards.list', [$board_id]);
        }


        $board_config = DB::table('bl_board_configs')
            ->select(DB::raw('bl_board_configs.*'))
            ->where('is_deleted', '0')
            ->where('is_active', '1')
            ->where('bl_board_configs.board_id', $validateRoleRequest->board_id)->first();

        if (!$board_config) {
            return alertAndRedirect('게시판이 존재하지 않습니다.', 'index');
        }

        $table_post = 'bl_board_'.$board_id.'_posts';
        $table_files = 'bl_board_'.$board_id.'_files';
        $boards = DB::table($table_post)
            ->leftJoin('bl_board_category', function($join) use ($table_post) {
                $join->on('bl_board_category.depth_code', '=', "{$table_post}.category")
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->leftJoin('bl_members', 'bl_members.member_id', '=', $table_post.'.member_id')
            ->select(DB::raw($table_post.'.*,bl_board_category.kname,bl_members.user_name'))
            ->where($table_post.'.is_display', '1')
            ->where($table_post.'.post_id', $post_id)->first();

        if (!$boards) {
            return alertAndRedirect('존재하지 않는 게시글 입니다.', 'boards.list', [$board_id]);
        }

        $files = DB::table($table_files)
            ->select(DB::raw('id, fname, path, fsize'))
            ->where('post_id', $post_id)
            ->orderBy('id', 'asc')
            ->get();

        if ($board_config->is_category) {
            $category_sub_config = DB::table('bl_board_category')
                ->select(DB::raw('depth_code, kname'))
                ->where('is_view', 'Y')
                ->where('depth', '2')
                ->where('depth_code', 'like', substr($board_config->is_category,0,2) . '%')
                ->orderBy('sort_order', 'asc')
                ->get();
        } else {
            $category_sub_config = [];
        }

        $category_tmp = '';
        foreach ($category_sub_config as $category) {
            if($boards->category === $category->depth_code) {
                $category_tmp = $category->kname;
            }
        }
        $boards = (array) $boards;
        $boards['category_tmp'] = $category_tmp;
        $boards = (object) $boards;

        return view('web.board.edit', ['board_config' => $board_config, 'boards' => $boards, 'files' => $files, 'category_sub' => $category_sub_config]);
    }


    public function update(Request $request, string $board_id)
    {
        try
        {
            if (!$board_id) {
                throw new Exception((string) "게시판 유형이 누락되었습니다.");
            }

            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
                'subject' => 'required'
            ], [
                'post_id.required' => '글번호가 없습니다.',
                'subject.required' => '제목은 필수 입력입니다.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            //금칙어 체크
            $board_config = DB::table('bl_board_configs')
                ->select(DB::raw('bl_board_configs.*'))
                ->where('is_deleted', '0')
                ->where('is_active', '1')
                ->where('bl_board_configs.board_id', $board_id)->first();

            if (!$board_config) {
                return alertAndRedirect('게시판이 존재하지 않습니다.', 'index');
            }

            $bl_config = DB::table('bl_config')
                ->select(DB::raw('value'))
                ->where('code', 'forbid_settings')->first();

            // 금칙어 처리
            if ($board_config->is_ban === 1) {
                $filter = implode(
                    '|',
                    array_column(
                        json_decode($bl_config->value, true)['words'],
                        'word'
                    )
                );
                if (preg_match_all('/('.$filter.')/', $request->subject, $match) == true) {
                    return response()->json([
                        'success' => false,
                        'errors'  => [
                            'subject' => ['제목에 허용되지 않는 문자를 포함하고 있습니다.']
                        ],
                    ], 422);
                }
                if (preg_match_all('/('.$filter.')/', $request->contents, $match) == true) {
                    return response()->json([
                        'success' => false,
                        'errors'  => [
                            'contents' => ['내용에 허용되지 않는 문자를 포함하고 있습니다.']
                        ],
                    ], 422);
                }
            }

            $table_post = 'bl_board_'.$board_id.'_posts';
            $table_files = 'bl_board_'.$board_id.'_files';

            DB::table($table_post)
                ->where('post_id', $request->post_id)
                ->update([
                    'category'   => $request->category   ?? '',
                    'subject'    => $request->subject    ?? '',
                    'content'    => $request->contents   ?? '',
                    'is_secret'  => $request->is_secret  ?? 0,
                    'secret_password'  => $request->secret_password  ?? '',
                    'updated_at' => now(),
                ]);

            $hidden_file = array_merge(
                $request->input('post_hidden_file', []),
                $request->input('gallery_hidden_file', [])
            );
            //기존 파일 삭제 되었을 경우
            if (!empty($hidden_file)) {
                $paths = DB::table($table_files)
                    ->select('path')
                    ->where('post_id', $request->post_id)
                    ->where('post_type', 'POSTS')
                    ->whereNotIn('id', $hidden_file)
                    ->pluck('path')->toArray();

                foreach ($paths as $path) {
                    public_delete($path);
                }

                DB::table($table_files)
                    ->where('post_id',   $request->post_id)
                    ->where('post_type', 'POSTS')
                    ->whereNotIn('id',   $hidden_file)
                    ->delete();
            } else {
                //파일을 다 삭제 했을 경우 첨부가 있을때 삭제
                $paths = DB::table($table_files)
                    ->select('path')
                    ->where('post_id', $request->post_id)
                    ->where('post_type', 'POSTS')
                    ->pluck('path')->toArray();

                foreach ($paths as $path) {
                    public_delete($path);
                }

                DB::table($table_files)
                    ->where('post_id',   $request->post_id)
                    ->where('post_type', 'POSTS')
                    ->delete();
            }

            // 허용 확장자 배열
            $allowed_ext = [
                'jpg','jpeg','png','gif','bmp',
                'pdf','doc','docx','ppt','pptx','txt',
                'zip','rar','7z'
            ];

            $hash    = md5($request->post_id);
            $level1 = substr($hash, 0, 2);  // "c8"
            $level2 = substr($hash, 2, 2);  // "1e"

            // 일반첨부파일이 업로드되었는지 확인
            if ($request->hasFile('post_file') && $request->post_id) {
                foreach ($request->file('post_file') as $file) {
                    if ($file->isValid()) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/' . $board_id . '/' . $level1 . '/' . $level2 , 'public');
                            $sizeBytes = $file->getSize();
                            $sizeMB = round($sizeBytes / 1024 / 1024, 2);//MB
                            $filetype = $file->getMimeType();
                            // 결과 배열에 추가
                            DB::table($table_files)->insert([
                                'post_id' => $request->post_id,
                                'post_type' => 'POSTS',
                                'ftype' => $filetype,
                                'fsize' => $sizeMB,
                                'path' => '/storage/'.$storedPath,
                                'fname' => $originalName,
                                'created_at' => NOW()
                            ]);
                        }
                    }
                }
            }
            // 겔러리파일이 업로드되었는지 확인
            if ($request->hasFile('gallery_file') && $request->post_id) {
                foreach ($request->file('gallery_file') as $file) {
                    if ($file->isValid()) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/' . $board_id . '/' . $level1 . '/' . $level2 , 'public');
                            $sizeBytes = $file->getSize();
                            $sizeMB = round($sizeBytes / 1024 / 1024, 2);//MB
                            $filetype = $file->getMimeType();
                            // 결과 배열에 추가
                            DB::table($table_files)->insert([
                                'post_id' => $request->post_id,
                                'post_type' => 'POSTS',
                                'ftype' => $filetype,
                                'fsize' => $sizeMB,
                                'path' => '/storage/'.$storedPath,
                                'fname' => $originalName,
                                'created_at' => NOW()
                            ]);
                        }
                    }
                }
            }

            return [
                'success' => true,
                'message' => '등록 되었습니다.'
            ];
        }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => '데이터 처리 중 오류가 발생했습니다.'
            ];
        }
    }
    /**
     * 게시글 삭제
     */
    public function destroy(Request $request, string $board_id)
    {
        if (!$board_id) {
            throw new Exception((string) "게시판 유형이 누락되었습니다.");
        }
        $validateRoleRequest = array_merge($request->all(), ['board_id' => $board_id]);

        $validator = Validator::make($validateRoleRequest, [
            'board_id' => 'required',
            'post_list'    => 'required|array|min:1',
            'post_list.*'  => 'integer',
        ], [
            'board_id.required' => '게시판 아이디는 필수 입니다.',
            'post_list.required'   => '삭제할 항목을 하나 이상 선택해주세요.',
            'post_list.array'      => '잘못된 형식의 요청입니다.',
            'post_list.min'        => '최소 하나 이상의 항목을 선택해야 합니다.',
            'post_list.*.integer'  => '유효하지 않은 게시물 ID가 포함되어 있습니다.',
        ]);


        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $postTableName = 'bl_board_' . $board_id . "_posts";
        $replyTableName = 'bl_board_' . $board_id . "_replies";
        $fileTableName = 'bl_board_' . $board_id . "_files";
        $likeTableName = 'bl_board_' . $board_id . "_likes";

        DB::transaction(function () use (
            $request,
            $postTableName,
            $replyTableName,
            $fileTableName,
            $likeTableName
        ) {
            // 1) 삭제할 게시물 ID 배열
            $postIds = (array) $request->input('post_list', []);
            if (empty($postIds)) {
                return;
            }

            // 2) 해당 게시물의 댓글 ID 수집
            $replyIds = DB::table($replyTableName)
                ->whereIn('post_id', $postIds)
                ->pluck('id')
                ->toArray();

            // 3) 파일 경로 수집 (게시물 + 댓글), 각 타입 구분
            // 3-1) 게시물 파일
            $postFilePaths = DB::table($fileTableName)
                ->whereIn('post_id', $postIds)
                ->where('post_type', 'POSTS')
                ->pluck('path')
                ->filter()
                ->toArray();

            // 3-2) 댓글 파일
            $replyFilePaths = [];
            if (! empty($replyIds)) {
                $replyFilePaths = DB::table($fileTableName)
                    ->whereIn('post_id', $replyIds)
                    ->where('post_type', 'REPLIES')
                    ->pluck('path')
                    ->filter()
                    ->toArray();
            }

            // 4) 실제 스토리지 파일 삭제
            foreach (array_merge($postFilePaths, $replyFilePaths) as $path) {
                public_delete($path);
            }

            // 5) 좋아요 레코드 삭제 (post_type 구분)
            // 5-1) 게시물 좋아요
            DB::table($likeTableName)
                ->whereIn('post_id', $postIds)
                ->where('post_type', 'POSTS')
                ->delete();

            // 5-2) 댓글 좋아요
            if (! empty($replyIds)) {
                DB::table($likeTableName)
                    ->whereIn('post_id', $replyIds)
                    ->where('post_type', 'REPLIES')
                    ->delete();
            }

            // 6) 파일 레코드 삭제 (post_type 구분)
            DB::table($fileTableName)
                ->whereIn('post_id', $postIds)
                ->where('post_type', 'POSTS')
                ->delete();

            if (! empty($replyIds)) {
                DB::table($fileTableName)
                    ->whereIn('post_id', $replyIds)
                    ->where('post_type', 'REPLIES')
                    ->delete();
            }

            // 7) 댓글 레코드 삭제
            if (! empty($replyIds)) {
                DB::table($replyTableName)
                    ->whereIn('id', $replyIds)
                    ->delete();
            }

            // 8) 게시물 레코드 삭제
            DB::table($postTableName)
                ->whereIn('post_id', $postIds)
                ->delete();
        });

        return alertAndRedirect('게시글이 성공적으로 삭제되었습니다.', 'boards.list', [$board_id]);
    }

    /**
     * categorylist
     *
     */
    public function replieslist_ajax(Request $request, string $board_id, int $post_id)
    {
        try
        {
            if (!$board_id) {
                throw new Exception((string) "게시판 유형이 누락되었습니다.");
            }
            $validateRoleRequest = array_merge($request->all(), ['board_id' => $board_id, 'post_id' => $post_id]);

            $validator = Validator::make($validateRoleRequest, [
                'board_id' => 'required',
                'post_id' => 'required'
            ], [
                'board_id.required' => '게시판 아이디는 필수 입니다.',
                'post_id.required' => '글번호는 필수 입니다.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $table_replies = 'bl_board_'.$board_id.'_replies';
            $table_likes = 'bl_board_'.$board_id.'_likes';

            if($this->sessionId) {
                $member_id_tmp = $this->sessionId;
            } else {
                $member_id_tmp = 0;
            }

            // 댓글 쿼리 (cmt_num이 0인 경우 - 원 댓글)
            $query = DB::table($table_replies)
                ->leftJoin('bl_members', 'bl_members.member_id', '=', $table_replies.'.member_id')

                ->where($table_replies.'.post_id', $post_id)
                ->select(
                    $table_replies.'.*',
                    'bl_members.user_name',
                    DB::raw("COALESCE((SELECT id
                     FROM {$table_likes}
                     WHERE post_type = 'REPLIES'
                       AND post_id = {$table_replies}.id
                       AND member_id = {$member_id_tmp}), 0) AS likes_id"),
                    'bl_members.profile_image'
                );

            $query->where($table_replies.'.cmt_num', 0);

            $sortField = "created_at";
            $sortDirection = "desc";
            $query->orderBy($table_replies.'.'.$sortField, $sortDirection);

            // 페이징 적용
            $replies = $query->paginate(10);
            // 페이지네이션 링크에 현재 쿼리스트링 유지
            $replies->appends($request->query());

            $replies->transform(function ($reply) {
                $reply->content = renderContentAllowHtmlButEscapeScript($reply->content);
                $reply->writer_ip = long2ip($reply->writer_ip);
                $reply->user_name = ($reply->user_name) ? decrypt($reply->user_name) : '익명';
                return $reply;
            });

            if($this->sessionId) {
                $member_id_tmp = $this->sessionId;
            } else {
                $member_id_tmp = 0;
            }
            // 각 댓글에 대한 대댓글 추가
            foreach ($replies as $reply) {
                // 대댓글 쿼리 (cmt_num이 댓글의 id인 경우)
                $rereplies = DB::table($table_replies)
                    ->leftJoin('bl_members', 'bl_members.member_id', '=', $table_replies.'.member_id')
                    ->where($table_replies.'.post_id', $post_id)
                    ->where($table_replies.'.cmt_num', $reply->id)
                    ->select(
                        $table_replies.'.*',
                        'bl_members.user_name',
                        DB::raw("COALESCE((SELECT id
                         FROM {$table_likes}
                         WHERE post_type = 'REPLIES'
                           AND post_id = {$table_replies}.id
                           AND member_id = {$member_id_tmp}), 0) AS likes_id"),
                        'bl_members.profile_image'
                    )
                    ->orderBy($table_replies.'.created_at', 'asc')
                    ->get();
                    $rereplies->transform(function ($rereply) {
                        $rereply->content = renderContentAllowHtmlButEscapeScript($rereply->content);
                        $rereply->writer_ip = long2ip($rereply->writer_ip);
                        $rereply->user_name = ($rereply->user_name) ? decrypt($rereply->user_name) : '익명';
                        return $rereply;
                    });
                // 대댓글 연결
                $reply->rereplies = $rereplies;
            }

            $returnData = ['success' => true, 'data' => ['replies' => $replies]];

            return [
                'success' => true,
                'data' => $returnData
            ];
            }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => '데이터 처리 중 오류가 발생했습니다.'
            ];
        }
    }

    /**
     * 게시글 저장
     */
    public function repliesstore(Request $request, string $board_id, int $post_id)
    {

        try
        {
            if (!$board_id) {
                throw new Exception((string) "게시판 유형이 누락되었습니다.");
            }
            $validateRoleRequest = array_merge($request->all(), ['board_id' => $board_id, 'post_id' => $post_id]);

            $validator = Validator::make($validateRoleRequest, [
                'board_id' => 'required',
                'post_id' => 'required',
                'comment' => 'required',
            ], [
                'board_id.required' => '게시판 아이디는 필수 입니다.',
                'post_id.required' => '글번호는 필수 입니다.',
                'comment.required' => '댓글을 입력해주세요.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $table_replies = 'bl_board_'.$board_id.'_replies';

            $ip = $request->getClientIp();
            $ip_as_integer = ip2long($ip);
            DB::table($table_replies)->insert([
                'post_id' => $post_id,
                'member_id' => $this->sessionId,
                'cmt_num' => $request->reid,
                'content' => $request->comment,
                'writer_ip' => $ip_as_integer,
                'created_at' => NOW(),
                'updated_at' => NOW()
            ]);

            return [
                'success' => true,
                'message' => '등록 되었습니다.'
            ];
        }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => '데이터 처리 중 오류가 발생했습니다.'
            ];
        }
    }
    /**
     * categorylist
     *
     */
    public function replyDestroy(string $board_id, int $commentId)
    {
        try
        {
            if (!$board_id) {
                throw new Exception((string) "게시판 유형이 누락되었습니다.");
            }
            $validateRoleRequest = array_merge(['board_id' => $board_id, 'commentId' => $commentId]);

            $validator = Validator::make($validateRoleRequest, [
                'board_id' => 'required',
                'commentId' => 'required'
            ], [
                'board_id.required' => '게시판 아이디는 필수 입니다.',
                'commentId.required' => '글번호는 필수 입니다.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $table_replies = 'bl_board_'.$board_id.'_replies';
            $table_files = 'bl_board_'.$board_id.'_files';

            $paths = DB::table($table_files)
                ->select('path')
                ->where('post_id',   $commentId)
                ->where('post_type', 'REPLIES')
                ->get();

            foreach ($paths as $path) {
                public_delete($path);
            }

            DB::table($table_files)
                ->where('post_id',   $commentId)
                ->where('post_type', 'REPLIES')
                ->delete();

            DB::table($table_replies)
                ->where('id',   $commentId)
                ->delete();
            DB::table($table_replies)
                ->where('cmt_num',   $commentId)
                ->delete();

            return [
                'success' => true,
                'data' => '삭제 되었습니다.'
            ];
        }
        catch(Exception $e)
        {
            return [
                'success' => false,
                'message' => '데이터 처리 중 오류가 발생했습니다.'
            ];
        }
    }
}
