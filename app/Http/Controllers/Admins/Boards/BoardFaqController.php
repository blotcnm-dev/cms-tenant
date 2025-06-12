<?php

namespace App\Http\Controllers\Admins\Boards;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class BoardFaqController extends Controller
{
    protected $sessionId;

    public function __construct()
    {
        $this->middleware('web');
        $this->middleware(function ($request, $next) {
            $this->sessionId = session()->get('blot_mbid');
            return $next($request);
        });
    }

    /**
     * 게시글 목록 표시 (검색 및 필터링 추가)
     */
    public function front_list(Request $request)
    {

        $query = DB::table('bl_faq_posts')
            ->leftJoin('bl_board_category', function($join) {
                $join->on('bl_board_category.depth_code', '=', 'bl_faq_posts.category')
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->select(
                'bl_faq_posts.*',
                'bl_board_category.kname'
            )
            ->where('bl_faq_posts.is_display', '1');

        if ($request->filled('category')) {
            $query->where('bl_faq_posts.category', $request->input('category'));
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
        $query->orderBy('bl_faq_posts.'.$sortField, $sortDirection);

        // 페이징 적용
        $faq_contents = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $faq_contents->appends($request->query());

        //카테고리가 있을경우
        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '01%')
            ->orderBy('sort_order', 'asc')
            ->get();

        // 각 FAQ 항목에 files 추가
        $faq_contents->getCollection()->transform(function ($item) {
            $files = DB::table('bl_faq_files')
                ->select('id', 'fname', 'path', 'fsize')
                ->where('post_id', $item->post_id)
                ->orderBy('id', 'asc')
                ->get();

            $item->files = $files;
            return $item;
        });

        return view('web.faq.list', ['boards' => $faq_contents, 'category_sub' => $category_sub_config]);
    }

    /**
     * 게시글 목록 표시 (검색 및 필터링 추가)
     */
    public function index(Request $request)
    {

        $query = DB::table('bl_faq_posts')
            ->leftJoin('bl_members',
                'bl_faq_posts.member_id', '=', 'bl_members.member_id'
            )
            ->leftJoin('bl_board_category', function($join) {
                $join->on('bl_board_category.depth_code', '=', 'bl_faq_posts.category')
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->select(
                'bl_faq_posts.*',
                'bl_members.user_name',
                'bl_board_category.kname'
            );

        // 제목 검색
        if ($request->filled('subject')) {
            $query->where('bl_faq_posts.subject', 'like', '%' . $request->input('subject') . '%');
        }

        // 등록 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_faq_posts.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_faq_posts.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        if ($request->filled('category')) {
            $query->where('bl_faq_posts.category', $request->input('category'));
        }

        // 노출 여부 필터링
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '노출' ? '1' : '0';
            $query->where('bl_faq_posts.is_display', $status);
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
        $query->orderBy('bl_faq_posts.'.$sortField, $sortDirection);

        // 페이징 적용
        $faq_contents = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $faq_contents->appends($request->query());

        //카테고리가 있을경우
        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '01%')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.faq.list', ['boards' => $faq_contents, 'category_sub' => $category_sub_config]);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|array
     */
    public function create() : \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|array
    {


        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '01%')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.faq.create', [
            'category_sub' => $category_sub_config
        ]);
    }
    public function store(Request $request)
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

            $post_id = DB::table('bl_faq_posts')
                ->insertGetId([
                    'category'   => $request->category   ?? '',
                    'subject'    => $request->subject    ?? '',
                    'content'    => $request->contents   ?? '',
                    'member_id'  => $this->sessionId,
                    'writer_name'  => '관리자',
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
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/faq/' . $level1 . '/' . $level2 , 'public');
                            $sizeBytes = $file->getSize();
                            $sizeMB = round($sizeBytes / 1024 / 1024, 2);//MB
                            $filetype = $file->getMimeType();
                            // 결과 배열에 추가
                            DB::table('bl_faq_files')->insert([
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

    /**
     * 게시글 상세 보기
     */
    public function show(int $post_id)
    {
        if (!$post_id) {
            throw new Exception((string) "게시글 번호가 누락되었습니다.");
        }

        $table_post = 'bl_faq_posts';
        $table_files = 'bl_faq_files';
        $boards = DB::table($table_post)
            ->leftJoin('bl_board_category', function($join) use ($table_post) {
                $join->on('bl_board_category.depth_code', '=', "{$table_post}.category")
                    ->where('bl_board_category.is_view', 'Y');
            })
            ->leftJoin('bl_members', 'bl_members.member_id', '=', $table_post.'.member_id')
            ->select(DB::raw($table_post.'.*,bl_board_category.kname,bl_members.user_name'))
            ->where($table_post.'.post_id', $post_id)->first();

        if (!$boards) {
            return redirect()
                ->back()
                ->withErrors('글정보가 없습니다.');
        }

        $files = DB::table($table_files)
            ->select(DB::raw('id, fname, path, fsize'))
            ->where('post_id', $post_id)
            ->orderBy('id', 'asc')
            ->get();

        $category_sub_config = DB::table('bl_board_category')
            ->select(DB::raw('depth_code, kname'))
            ->where('is_view', 'Y')
            ->where('depth', '2')
            ->where('depth_code', 'like', '01%')
            ->orderBy('sort_order', 'asc')
            ->get();

        //조회수 올리기
//        DB::table($table_post)
//            ->where('post_id', $post_id)
//            ->increment('hits');

        $category_tmp = '';
        foreach ($category_sub_config as $category) {
            if($boards->category === $category->depth_code) {
                $category_tmp = $category->kname;
            }
        }
        $boards = (array) $boards;
        $boards['category_tmp'] = $category_tmp;
        $boards = (object) $boards;

        return view('admin.faq.show', ['boards' => $boards, 'files' => $files, 'category_sub' => $category_sub_config]);
    }

    public function update(Request $request, int $post_id)
    {
        try
        {
            if (!$post_id) {
                throw new Exception((string) "게시글 번호가 누락되었습니다.");
            }

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

            $table_post = 'bl_faq_posts';
            $table_files = 'bl_faq_files';

            DB::table($table_post)
                ->where('post_id', $post_id)
                ->update([
                    'category'   => $request->category   ?? '',
                    'subject'    => $request->subject    ?? '',
                    'content'    => $request->contents   ?? '',
                    'is_display'  => $request->is_display  ?? 1,
                    'writer_name'  => $request->writer_name  ?? '',
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
                    ->where('post_type', 'POSTS')
                    ->whereNotIn('id', $hidden_file)
                    ->pluck('path')->toArray();

                foreach ($paths as $path) {
                    public_delete($path);
                }

                DB::table($table_files)
                    ->where('post_id',   $post_id)
                    ->where('post_type', 'POSTS')
                    ->whereNotIn('id',   $hidden_file)
                    ->delete();
            } else {
                //파일을 다 삭제 했을 경우 첨부가 있을때 삭제
                $paths = DB::table($table_files)
                    ->select('path')
                    ->where('post_id', $post_id)
                    ->where('post_type', 'POSTS')
                    ->pluck('path')->toArray();

                foreach ($paths as $path) {
                    public_delete($path);
                }

                DB::table($table_files)
                    ->where('post_id',   $post_id)
                    ->where('post_type', 'POSTS')
                    ->delete();
            }

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
                        if (in_array($ext, $allowed_ext)) {
                            $originalName = $file->getClientOriginalName();
                            $storedPath = $file->store('board/faq/' . $level1 . '/' . $level2 , 'public');
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


        $postTableName = 'bl_faq_posts';
        $fileTableName = 'bl_faq_files';

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

        return redirect()->route('faq.index')
            ->with('success', '게시글이 성공적으로 삭제되었습니다.');
    }
}
