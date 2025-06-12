<?php

namespace App\Http\Controllers\Admins\Boards;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Services\EmailService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class BoardInquiryController extends Controller
{
    protected $sessionId;
    protected $sessionadnm;
    public function __construct()
    {
        $this->middleware('web');
        $this->middleware(function ($request, $next) {
            $this->sessionId = session()->get('blot_mbid');
            $this->sessionadnm = session()->get('blot_adnm');
            return $next($request);
        });
    }

    /**
     * 게시글 목록 표시 (검색 및 필터링 추가)
     */
    public function front_list(Request $request)
    {
        $member_id = ($this->sessionId) ? $this->sessionId : 0;
        $query = DB::table('bl_inquiry_posts')
            ->leftJoin('bl_board_category', function($join) {
                $join->on('bl_board_category.depth_code', '=', 'bl_inquiry_posts.category')
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->select(
                'bl_inquiry_posts.*',
                'bl_board_category.kname'
            );
        $query->where('bl_inquiry_posts.is_display', 1);
        $query->where('bl_inquiry_posts.member_id', $member_id );
//        // 제목 검색
//        if ($request->filled('subject')) {
//            $query->where('bl_inquiry_posts.subject', 'like', '%' . $request->input('subject') . '%');
//        }
//
//        // 등록 기간 검색
//        if ($request->filled('start_date')) {
//            $query->where('bl_inquiry_posts.created_at', '>=', $request->input('start_date') . ' 00:00:00');
//        }
//
//        if ($request->filled('end_date')) {
//            $query->where('bl_inquiry_posts.created_at', '<=', $request->input('end_date') . ' 23:59:59');
//        }
//
//        // 문의 답변 상태
//        if ($request->filled('status') && $request->input('status') !== '전체') {
//            $status = $request->input('status') === '답변대기' ? 'READY' : 'COMPLETE';
//            $query->where('bl_inquiry_posts.reply_status', $status);
//        }
        // 검색 조건
        if ($request->filled('fild_id') && $request->filled('fild_val')) {
            $fieldId = $request->input('fild_id');
            $fieldVal = $request->input('fild_val');

            // 허용된 필드만 검색 가능하도록 보안 강화
            $allowedFields = ['content', 'subject', 'reply_status'];

            if (in_array($fieldId, $allowedFields)) {
                $query->where('bl_inquiry_posts.'.$fieldId, 'like', '%' . $fieldVal . '%');
            }
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
        $query->orderBy('bl_inquiry_posts.'.$sortField, $sortDirection);

        // 페이징 적용
        if ($request->filled('limit_cnt')) {
            $limitCnt = $request->input('limit_cnt');
        } else {
            $limitCnt = 10;
        }
        $inquiry_contents = $query->paginate($limitCnt);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $inquiry_contents->appends($request->query());

        //카테고리가 있을경우
        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '02%')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('web.inquiry.list', ['boards' => $inquiry_contents, 'category_sub' => $category_sub_config]);
    }

    public function front_create()
    {

        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '02%')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('web.inquiry.create', [
            'category_sub' => $category_sub_config
        ]);
    }


    public function front_store(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'subject' => 'required',
                'contents' => 'required',
            ], [
                'subject.required' => '제목은 필수 입력입니다.',
                'contents.required' => '내용을 입력해주세요.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $bl_config = DB::table('bl_config')
                ->select(DB::raw('value'))
                ->where('code', 'forbid_settings')->first();

            // 금칙어 처리
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

            $bl_email = '';
            $bl_is_email = 0;
            if($this->sessionId) {
            $bl_member_info = DB::table('bl_members')
                ->select(DB::raw('email'))
                ->where('member_id', $this->sessionId)->first();
                $bl_email = $bl_member_info->email;
                $bl_is_email = 1;
            }

            $table_post = 'bl_inquiry_posts';
            $table_files = 'bl_inquiry_files';
            $post_id = DB::table($table_post)
                ->insertGetId([
                    'category'   => $request->category   ?? '',
                    'subject'    => $request->subject    ?? '',
                    'content'    => $request->contents   ?? '',
                    'is_secret'  => $request->is_secret  ?? 0,
                    'is_email'  => $bl_is_email,
                    'email'  => $bl_email  ?? '',
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
                            $storedPath = $file->store('board/inquiry/' . $level1 . '/' . $level2 , 'public');
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
    public function front_view(Request $request, int $post_id)
    {

        $validateRoleRequest = array_merge(['post_id' => $post_id]);

        $validator = Validator::make($validateRoleRequest, [
            'post_id' => 'required|numeric',
        ], [
            'post_id.required' => '글번호는 필수 입니다.',
            'post_id.numeric' => '잘못된 접근입니다.',
        ]);

        $validateRoleRequest = (object) $validateRoleRequest;
        if ($validator->fails()) {
            return alertAndRedirect('정보가 없습니다.', 'front_inquiry.index', []);
        }
        if (!$this->sessionId) {
            return alertAndRedirect('로그인 후 확인 가능합니다.', 'login', []);
        }


        $table_post = 'bl_inquiry_posts';
        $table_files = 'bl_inquiry_files';
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
                'bl_members.profile_image'
            ])
            ->where("{$table_post}.is_display", '1')
            ->where("{$table_post}.post_id", $post_id)
            ->where("{$table_post}.member_id", $this->sessionId)
            ->first();

        if (!$boards) {
            return alertAndRedirect('접근 할 수 없습니다.', 'front_inquiry.index', []);
        }



        // 세션에 조회한 게시글 ID 저장
        $viewedPosts = session('viewed_posts', []);
        $sess_addid = "inquiry_".$post_id;
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

        $files = DB::table($table_files)
            ->select(DB::raw('id, fname, path, fsize,post_type'))
            ->where('post_id', $post_id)
            ->orderBy('id', 'asc')
            ->get();
        // 빈 배열 초기화
        $post_files = [];
        $reply_files = [];

        foreach ($files as $file) {
            if ($file->post_type === 'POSTS') {
                $post_files[] = $file;   // POSTS 타입은 $post_files 에 담고
            } else {
                $reply_files[] = $file;   // 그 외는 $reply_files 에 담는다
            }
        }

        $post_files = collect($post_files);
        $reply_files = collect($reply_files);

        return view('web.inquiry.show', ['boards' => $boards, 'post_files' => $post_files, 'reply_files' => $reply_files]);
    }

    /**
     * 게시글 목록 표시 (검색 및 필터링 추가)
     */
    public function index(Request $request)
    {

        $query = DB::table('bl_inquiry_posts')
            ->leftJoin('bl_board_category', function($join) {
                $join->on('bl_board_category.depth_code', '=', 'bl_inquiry_posts.category')
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->select(
                'bl_inquiry_posts.*',
                'bl_board_category.kname'
            );

        // 제목 검색
        if ($request->filled('subject')) {
            $query->where('bl_inquiry_posts.subject', 'like', '%' . $request->input('subject') . '%');
        }

        // 등록 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_inquiry_posts.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_inquiry_posts.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        if ($request->filled('category')) {
            $query->where('bl_inquiry_posts.category', $request->input('category'));
        }

        // 문의 답변 상태
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '답변대기' ? 'READY' : 'COMPLETE';
            $query->where('bl_inquiry_posts.reply_status', $status);
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
        $query->orderBy('bl_inquiry_posts.'.$sortField, $sortDirection);

        // 페이징 적용
        $inquiry_contents = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $inquiry_contents->appends($request->query());

        //카테고리가 있을경우
        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '02%')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.inquiry.list', ['boards' => $inquiry_contents, 'category_sub' => $category_sub_config]);
    }


    /**
     * 게시글 상세 보기
     */
    public function view(int $post_id)
    {
        if (!$post_id) {
            throw new Exception((string) "게시글 번호가 누락되었습니다.");
        }

        $table_post = 'bl_inquiry_posts';
        $table_files = 'bl_inquiry_files';
        $boards = DB::table($table_post)
            ->leftJoin('bl_board_category', function($join) use ($table_post) {
                $join->on('bl_board_category.depth_code', '=', "{$table_post}.category")
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->select(DB::raw($table_post.'.*,bl_board_category.kname'))
            ->where($table_post.'.post_id', $post_id)->first();

        if (!$boards) {
            return redirect()
                ->back()
                ->withErrors('글정보가 없습니다.');
        }

        $files = DB::table($table_files)
            ->select(DB::raw('id, fname, path, fsize,post_type'))
            ->where('post_id', $post_id)
            ->orderBy('id', 'asc')
            ->get();
        // 빈 배열 초기화
        $post_files = [];
        $reply_files = [];

        foreach ($files as $file) {
            if ($file->post_type === 'POSTS') {
                $post_files[] = $file;   // POSTS 타입은 $post_files 에 담고
            } else {
                $reply_files[] = $file;   // 그 외는 $reply_files 에 담는다
            }
        }

        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '02%')
            ->orderBy('sort_order', 'asc')
            ->get();

        $category_tmp = '';
        foreach ($category_sub_config as $category) {
            if($boards->category === $category->depth_code) {
                $category_tmp = $category->kname;
            }
        }
        $boards = (array) $boards;
        $boards['category_tmp'] = $category_tmp;
        $boards = (object) $boards;
        $post_files = collect($post_files);
        $reply_files = collect($reply_files);

        return view('admin.inquiry.view', ['boards' => $boards, 'post_files' => $post_files, 'reply_files' => $reply_files, 'category_sub' => $category_sub_config]);
    }

    /**
     * 게시글 상세 보기
     */
    public function show(int $post_id)
    {
        if (!$post_id) {
            throw new Exception((string) "게시글 번호가 누락되었습니다.");
        }

        $table_post = 'bl_inquiry_posts';
        $table_files = 'bl_inquiry_files';
        $boards = DB::table($table_post)
            ->leftJoin('bl_board_category', function($join) use ($table_post) {
                $join->on('bl_board_category.depth_code', '=', "{$table_post}.category")
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->select(DB::raw($table_post.'.*,bl_board_category.kname'))
            ->where($table_post.'.post_id', $post_id)->first();

        if (!$boards) {
            return redirect()
                ->back()
                ->withErrors('글정보가 없습니다.');
        }

        $files = DB::table($table_files)
            ->select(DB::raw('id, fname, path, fsize,post_type'))
            ->where('post_id', $post_id)
            ->orderBy('id', 'asc')
            ->get();
        // 빈 배열 초기화
        $post_files = [];
        $reply_files = [];

        foreach ($files as $file) {
            if ($file->post_type === 'POSTS') {
                $post_files[] = $file;   // POSTS 타입은 $post_files 에 담고
            } else {
                $reply_files[] = $file;   // 그 외는 $reply_files 에 담는다
            }
        }

        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '02%')
            ->orderBy('sort_order', 'asc')
            ->get();

        $category_tmp = '';
        foreach ($category_sub_config as $category) {
            if($boards->category === $category->depth_code) {
                $category_tmp = $category->kname;
            }
        }
        $boards = (array) $boards;
        $boards['category_tmp'] = $category_tmp;
        $boards = (object) $boards;
        $post_files = collect($post_files);
        $reply_files = collect($reply_files);

        return view('admin.inquiry.show', ['boards' => $boards, 'post_files' => $post_files, 'reply_files' => $reply_files, 'category_sub' => $category_sub_config]);
    }

    public function update(Request $request, int $post_id)
    {
        try
        {
            if (!$post_id) {
                throw new Exception((string) "게시글 번호가 누락되었습니다.");
            }

            $validator = Validator::make($request->all(), [
                'contents' => 'required',
            ], [
                'contents.required' => '내용을 입력해주세요.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $table_post = 'bl_inquiry_posts';
            $table_files = 'bl_inquiry_files';

            DB::table($table_post)
                ->where('post_id', $post_id)
                ->update([
                    'recontent'    => $request->contents   ?? '',
                    'reply_status'  => 'COMPLETE',
                    'admin_id'  => $this->sessionId,
                    'writer_name'  => $this->sessionadnm,
                    'updated_at' => now(),
                ]);

            $hidden_file = array_merge(
                $request->input('post_hidden_file', [])
            );
            //기존 파일 삭제 되었을 경우
            if (!empty($hidden_file)) {
                $paths = DB::table($table_files)
                    ->select('path')
                    ->where('post_id', $post_id)
                    ->where('post_type', 'REPLIES')
                    ->whereNotIn('id', $hidden_file)
                    ->pluck('path')->toArray();

                foreach ($paths as $path) {
                    public_delete($path);
                }

                DB::table($table_files)
                    ->where('post_id',   $post_id)
                    ->where('post_type', 'REPLIES')
                    ->whereNotIn('id',   $hidden_file)
                    ->delete();
            } else {
                //파일을 다 삭제 했을 경우 첨부가 있을때 삭제
                $paths = DB::table($table_files)
                    ->select('path')
                    ->where('post_id', $post_id)
                    ->where('post_type', 'REPLIES')
                    ->pluck('path')->toArray();

                foreach ($paths as $path) {
                    public_delete($path);
                }

                DB::table($table_files)
                    ->where('post_id',   $post_id)
                    ->where('post_type', 'REPLIES')
                    ->delete();
            }

            // 허용 확장자 배열
            $allowed_ext = [
                'jpg','jpeg','png','gif','bmp',
                'pdf','doc','docx','ppt','pptx','txt',
                'zip','rar','7z'
            ];

            $hash    = md5($post_id);
            $level1 = substr($hash, 0, 2);
            $level2 = substr($hash, 2, 2);

            $attachments = [];
            // 일반첨부파일이 업로드되었는지 확인
            if ($request->hasFile('post_file') && $post_id) {
                foreach ($request->file('post_file') as $file) {
                    if ($file->isValid()) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/inquiry/' . $level1 . '/' . $level2 , 'public');
                            $sizeBytes = $file->getSize();
                            $sizeMB = round($sizeBytes / 1024 / 1024, 2);//MB
                            $filetype = $file->getMimeType();
                            // 결과 배열에 추가
                            DB::table($table_files)->insert([
                                'post_id' => $post_id,
                                'post_type' => 'REPLIES',
                                'ftype' => $filetype,
                                'fsize' => $sizeMB,
                                'path' => '/storage/'.$storedPath,
                                'fname' => $originalName,
                                'created_at' => NOW()
                            ]);
                            $attachments[] = [
                                'path' => '/storage/'.$storedPath,
                                'name' => $originalName
                            ];
                        }
                    }
                }
            }

            //이메일 발송 로직 추가, 알림 보낼거면 폼에 알림 받기 추가 후 작업
            //$request->is_email, $request->email
            if($request->email && $request->is_email === '1') {
                $this->sendInquiryEmail($request, $attachments);
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
    public function destroy(Request $request)
    {
        $validateRoleRequest = array_merge($request->all());

        $validator = Validator::make($validateRoleRequest, [
            'post_list'    => 'required|array|min:1',
            'post_list.*'  => 'integer',
        ], [
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


        $postTableName = 'bl_inquiry_posts';
        $fileTableName = 'bl_inquiry_files';

        DB::transaction(function () use (
            $request,
            $postTableName,
            $fileTableName
        ) {
            // 1) 삭제할 게시물 ID 배열
            $postIds = (array) $request->input('post_list', []);
            if (empty($postIds)) {
                return;
            }

            // 3) 파일 경로 수집 (게시물 + 댓글), 각 타입 구분
            // 3-1) 게시물 파일
            $postFilePaths = DB::table($fileTableName)
                ->whereIn('post_id', $postIds)
                ->where('post_type', 'POSTS')
                ->pluck('path')
                ->filter()
                ->toArray();

            // 4) 실제 스토리지 파일 삭제
            foreach (array_merge($postFilePaths) as $path) {
                public_delete($path);
            }

            // 6) 파일 레코드 삭제 (post_type 구분)
            DB::table($fileTableName)
                ->whereIn('post_id', $postIds)
                ->where('post_type', 'POSTS')
                ->delete();

            // 8) 게시물 레코드 삭제
            DB::table($postTableName)
                ->whereIn('post_id', $postIds)
                ->delete();
        });

        return redirect()->route('inquiry.index')
            ->with('success', '게시글이 성공적으로 삭제되었습니다.');
    }

    /**
     * 문의 답변 메일 전송
     */
    private function sendInquiryEmail(Request $request, array $attachments = [])
    {
        try {

            $emails = array_filter(array_map('trim', explode(',', $request->email)));

            // 각 암호화된 이메일을 복호화하여 새로운 배열에 저장
//            $emails = array();
//            foreach ($emails_tmp as $encryptedEmail) {
//                $decryptedEmail = safe_decrypt($encryptedEmail);
//                $emails[] = $decryptedEmail;
//            }

            if (empty($emails)) {
                throw new Exception('이메일을 최소 한 개 이상 입력해 주세요.');
            }
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("이메일 주소 형식이 올바르지 않습니다: {$email}");
                }
            }

            $allowed = '<h2><h3><p><br><strong><em><ul><li>';
            $cleanContents = strip_tags($request->contents, $allowed);
            $questionContent = strip_tags($request->getContent(), $allowed);

            // 메일 데이터 준비
            $mail_data = [
                'subject'    => '답변 : '.$request->subject,
                'question_subject' => $request->subject,
                'question_content' => $questionContent,
                'answer_content' => $cleanContents,
                'reset_time'    => now()->format('Y-m-d H:i:s')
            ];
            $emailService = app(EmailService::class);

            $queuedEmail = $emailService->queueEmail(
                'emails.answersend',            // 뷰 경로
                $mail_data,                         // 뷰에 전달할 데이터
                $emails,                            // 수신자
                '',                        // 발신자메일주소
                '',                        //발신자명
                $attachments                        //첨부파일
            );

            Log::info('문의 답변 전송 완료 ' . $queuedEmail);

            return true;

        } catch (Exception $e) {
            Log::error('이메일 발송 오류: ' . $e->getMessage());
            throw $e; // 상위 메서드에서 처리할 수 있도록 예외를 다시 던짐
        }

    }
    private function sendInquiryEmail_origin(Request $request)
    {
        try {
            $emails = array_filter(array_map('trim', explode(',', $request->email)));

            if (empty($emails)) {
//                Log::error('이메일이 입력되지 않았습니다.');
//                throw new \Exception('이메일을 최소 한 개 이상 입력해 주세요.');
                return true;
            }

            // 메일 데이터 준비
            $data = [
                'recontent' => $request->contents,
                'site_name' => config('app.name'),
                'reset_date' => now()->format('Y-m-d H:i:s')
            ];

            $allowed = '<h2><h3><p><br><strong><em><ul><li>';
            $cleanContents = strip_tags($request->contents, $allowed);

            $message = "안녕하세요,\n\n";
            $message .= "답변: {$cleanContents}\n\n";
            $message .= "감사합니다.\n";
            $message .= config('app.name') . " 팀";

            $subject = '[' . config('app.name') . '] 답변: '.$request->subject;
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//                    Log::error('이메일 주소 형식 오류: ' . $email);
//                    throw new \Exception("이메일 주소 형식이 올바르지 않습니다: {$email}");
                } else {
                    //메일 발송
                    Mail::html($message, function ($mail) use ($email, $subject, $message) {
                        $mail->to($email)
                            ->subject($subject);
                    });
                }
            }

            return true;
        } catch (\Exception $e) {
//            Log::error('이메일 발송 오류: ' . $e->getMessage());
//            throw $e; // 상위 메서드에서 처리할 수 있도록 예외를 다시 던짐
            return true;
        }
    }
}
