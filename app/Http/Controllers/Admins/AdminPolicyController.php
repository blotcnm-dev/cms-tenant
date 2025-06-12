<?php

namespace App\Http\Controllers\Admins;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController;

class AdminPolicyController extends Controller
{
    protected $sessionId;

    public function __construct(){
        //dewbian 세션관리 끝나면 정리
        $this->sessionId = 1;
        //$this->sessionId = session()->getId();
        // 로그인 상태 확인 /// 이거 다시 확인해보자
//        if (!auth()->check()) {
//            // 로그인되지 않은 사용자 처리
//            // 1. 리디렉션 처리
//            // return redirect()->route('login');
//
//            // 2. 또는 비회원용 임시 ID 할당
//            $this->userId = null;
//        } else {
//            // 로그인된 사용자
//            $this->userId = auth()->id();
//        }

    }


    /**
     * 게시글 목록 표시 (검색 및 필터링 추가)
     */
    public function index(Request $request)
    {
        $query = DB::table('bl_policy_contents')
            ->leftJoin('bl_members', 'bl_policy_contents.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_policy_contents.*',
                'bl_members.user_name',
                'bl_members.user_type'
            );

        // 제목 검색
        if ($request->filled('title')) {
            $query->where('bl_policy_contents.title', 'like', '%' . $request->input('title') . '%');
        }

        // 작성자 검색 - 이름으로 검색
        if ($request->filled('author')) {
            $query->where(function($q) use ($request) {
                $q->where('bl_members.user_name', 'like', '%' . $request->input('author') . '%')
                    ->orWhere('bl_members.user_id', 'like', '%' . $request->input('author') . '%');
            });
        }

        // 등록 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_policy_contents.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_policy_contents.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 수정 기간 검색
        if ($request->filled('start_update_date')) {
            $query->where('bl_policy_contents.updated_at', '>=', $request->input('start_update_date') . ' 00:00:00');
        }

        if ($request->filled('end_update_date')) {
            $query->where('bl_policy_contents.updated_at', '<=', $request->input('end_update_date') . ' 23:59:59');
        }

        // 노출 여부 필터링
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '노출' ? 'Y' : 'N';
            $query->where('bl_policy_contents.is_state', $status);
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
        $query->orderBy('bl_policy_contents.'.$sortField, $sortDirection);

        // 페이징 적용
        $policy_contents = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $policy_contents->appends($request->query());

//        //dewbian 디버그용 스크립트
//        $sql = $query->toSql();
//        $bindings = $query->getBindings();
//
//        debug_info($sql);
//        debug_info($bindings);
//
//        echo "<pre>";
//        print_r($policy_contents);
//        echo "</pre>";
//        exit;

        return view('admin.policy.list', compact('policy_contents'));
    }


    /**
     * 엑셀 다운로드 기능 추가
     */
    public function downloadExcel(Request $request)
    {
        $query = DB::table('bl_policy_contents')
            ->leftJoin('bl_members', 'bl_policy_contents.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_policy_contents.*',
                'bl_members.user_name',
                'bl_members.user_type'
            );

        // 제목 검색
        if ($request->filled('title')) {
            $query->where('bl_policy_contents.title', 'like', '%' . $request->input('title') . '%');
        }

        // 작성자 검색 - 이름으로 검색
        if ($request->filled('author')) {
            $query->where(function($q) use ($request) {
                $q->where('bl_members.user_name', 'like', '%' . $request->input('author') . '%')
                    ->orWhere('bl_members.user_id', 'like', '%' . $request->input('author') . '%');
            });
        }

        // 등록 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_policy_contents.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_policy_contents.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 수정 기간 검색
        if ($request->filled('start_update_date')) {
            $query->where('bl_policy_contents.updated_at', '>=', $request->input('start_update_date') . ' 00:00:00');
        }

        if ($request->filled('end_update_date')) {
            $query->where('bl_policy_contents.updated_at', '<=', $request->input('end_update_date') . ' 23:59:59');
        }

        // 노출 여부 필터링
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '노출' ? 'Y' : 'N';
            $query->where('bl_policy_contents.is_state', $status);
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
        $query->orderBy('bl_policy_contents.'.$sortField, $sortDirection);

        // 데이터 가져오기 (페이징 없이 전체)
        $bl_policy_contents = $query->get();

        // 스프레드시트 생성
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 헤더 설정
        $sheet->setCellValue('A1', '번호');
        $sheet->setCellValue('B1', '제목');
        $sheet->setCellValue('C1', '내용');
        $sheet->setCellValue('D1', '작성자');
        $sheet->setCellValue('E1', '노출 여부');
        $sheet->setCellValue('F1', '등록일');
        $sheet->setCellValue('G1', '수정일');

        // 데이터 입력
        $row = 2;
        foreach ($bl_policy_contents as $item) {
            $sheet->setCellValue('A'.$row, $item->policy_contents_id);
            $sheet->setCellValue('B'.$row, $item->title);
            $sheet->setCellValue('C'.$row, $item->info);
            $sheet->setCellValue('D'.$row, $item->user_name ?? '익명');
            $sheet->setCellValue('E'.$row, $item->is_state == 'Y' ? '노출' : '비노출');
            $sheet->setCellValue('F'.$row, $item->created_at);
            $sheet->setCellValue('G'.$row, $item->updated_at);
            $row++;
        }

        // 열 너비 자동 조정
        foreach(range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 파일 생성
        $writer = new Xlsx($spreadsheet);
        $filename = '게시글목록_' . date('Ymd_His') . '.xlsx';

        // 헤더 설정
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // 파일 저장
        $writer->save('php://output');
        exit;
    }


    /**
     * 게시글 작성 폼
     */
    public function create()
    {
        return view('admin.policy.create');
    }

    /**
     * 게시글 저장
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'info' => 'required',
        ]);

        $validator->setCustomMessages([
            'title.required' => '제목을 입력해주세요.',
            'title.max' => '제목은 최대 255자까지 입력 가능합니다.',
            'info.required' => '내용을 입력해주세요.',
        ]);


        if ($validator->fails()) {
            return handle_validation_response($request, $validator, 'policy.create', []);
        }

        DB::table('bl_policy_contents')->insert([
            'admin_id' => $this->sessionId,
            'pc_type' => 'terms',
            'title' => $request->input('title'),
            'info' => $request->input('info'),
            'version' => 1 // 초기 버전은 1로 설정
        ]);

        return handle_success_response($request,'게시글이 성공적으로 등록되었습니다.','policy.index', []);
    }

    /**
     * 게시글 상세 보기
     */
    public function show(Request $request , $id)
    {

        $policy_content = DB::table('bl_policy_contents')
            ->leftJoin('bl_members', 'bl_policy_contents.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_policy_contents.*',
                'bl_members.user_name'
            )
            ->where('bl_policy_contents.policy_contents_id', $id)->first();


        if (!$policy_content) {
            return handle_success_response($request,'존재하지 않는 게시글입니다.','policy.index', []);
        }


        $policy_content = (array) $policy_content;
        $policy_content['is_state_text'] = ($policy_content['is_state'] === 'Y') ? '노출' : '비노출';
        $policy_content = (object) $policy_content;

        // 수정이력테이블
        $query = DB::table('bl_policy_contents_history')
            ->leftJoin('bl_members', 'bl_policy_contents_history.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_policy_contents_history.*',
                'bl_members.user_name')
            ->where('bl_policy_contents_history.policy_contents_id', $id);
        // 정렬
        $sort = $request->input('sort', 'bl_policy_contents_history.created_at');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        // 페이징 적용
        $policy_content_history = $query->paginate(10);
        $policy_content_history->appends($request->query());



        return view('admin.policy.show', compact('policy_content', 'policy_content_history'));
    }

    /**
     * 업데이트 이력 게시글 상세 보기
     */
    public function show_history($id)
    {
        $policy_history_content = DB::table('bl_policy_contents_history')
            ->leftJoin('bl_members', 'bl_policy_contents_history.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_policy_contents_history.*',
                'bl_members.user_name'
            )
            ->where('bl_policy_contents_history.history_id', $id)->first();

        return view('admin.policy.show_history', compact('policy_history_content'));
    }


    /**
     * 게시글 업데이트
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'info' => 'required',
        ]);

        if ($validator->fails()) {
            return handle_validation_response($request, $validator, 'policy.show', ['id' => $id]);
        }
        $status = $request->input('status') === '노출' ? 'Y' : 'N';

        try {

            $affected = DB::table('bl_policy_contents')
                ->where('policy_contents_id', $id)
                ->update([
                    'title' => $request->input('title'),
                    'info' => $request->input('info'),
                    'is_state' => $status
                ]);

            if ($affected == 0) {
                return handle_success_response($request, '변경된 내용이 없거나 데이터를 찾을 수 없습니다.', 'policy.show', ['id' => $id]);
            } else {
                //변경이력 등록
                DB::table('bl_policy_contents_history')->insert([
                    'policy_contents_id' => $id,
                    'admin_id' => $this->sessionId,
                    'title' => $request->input('title'),
                    'info' => $request->input('info')
                ]);
            }

            return handle_success_response($request, '게시글이 성공적으로 업데이트되었습니다.', 'policy.show', ['id' => $id]);

        } catch (\Exception $e) {
            // 쿼리 실행 중 오류 발생
            return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
        }
    }



    /**
     * 게시글 업데이트
     */
    public function versionup(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'info' => 'required',
        ]);

        if ($validator->fails()) {
            return handle_validation_response($request, $validator, 'policy.show', ['id' => $id]);
        }


        $status = $request->input('status') === '노출' ? 'Y' : 'N';
        $version = $request->input('version');
        if($version){
            $version = (int)$version + 1;
        }

        $newId = DB::table('bl_policy_contents')->insertGetId([
            'admin_id' => $this->sessionId,
            'pc_type' => 'terms',
            'title' => $request->input('title'),
            'info' => $request->input('info'),
            'is_state' => $status,
            'version' => $version
        ]);

        if (!$newId) {
            return handle_success_response($request,'게시글 버전 업데이트에 실패했습니다.','policy.show', ['id' => $id]);
        }

        return handle_success_response($request,'게시글이 성공적으로 버전 업데이트 되었습니다.','policy.show', ['id' => $newId]);
    }




    /**
     * 게시글 삭제
     */
    public function destroy($id)
    {
        $deleted = DB::table('bl_policy_contents')->where('policy_contents_id', $id)->delete();

        if (!$deleted) {
            return redirect()->route('policy.index')
                ->with('error', '게시글 삭제에 실패했습니다.');
        }

        return redirect()->route('policy.index')
            ->with('success', '게시글이 성공적으로 삭제되었습니다.');
    }
}
