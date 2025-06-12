<?php

namespace App\Http\Controllers\Admins;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;

class AdminPopupController extends Controller
{
    protected $sessionId;

    public function __construct(){
        $this->middleware('web');
        $this->middleware(function ($request, $next) {
            $this->sessionId = session()->get('blot_mbid');
            return $next($request);
        });
    }


    /**
     * 게시글 목록 표시 (검색 및 필터링 추가)
     */
    public function index(Request $request)
    {
        $query = DB::table('bl_promotions')
            ->leftJoin('bl_members', 'bl_promotions.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_promotions.*',
                'bl_members.user_name',
                'bl_members.user_type'
            );

        $query->where('bl_promotions.promotions_type', '=','popup');

        // 제목 검색
        if ($request->filled('title')) {
            $query->where('bl_promotions.title', 'like', '%' . $request->input('title') . '%');
        }

        // 작성자 검색 - 이름으로 검색
        if ($request->filled('author')) {
            $query->where(function($q) use ($request) {
                $q->where('bl_members.user_name_hash', hash('sha256', $request->input('author')))
                    ->orWhere('bl_members.user_id', 'like', '%' . $request->input('author') . '%');
            });
        }

        // 등록 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_promotions.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_promotions.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 노출 기간 검색
        if ($request->filled('start_update_date')) {
            $query->where(function ($q) use ($request) {
                $q->where('bl_promotions.sdate', '>=', $request->input('start_update_date') . ' 00:00:00')
                    ->orWhere('bl_promotions.is_view', 'always');
            });
        }

        if ($request->filled('end_update_date')) {
            $query->where(function ($q) use ($request) {
                $q->where('bl_promotions.edate', '>=', $request->input('start_update_date') . ' 00:00:00')
                    ->orWhere('bl_promotions.is_view', 'always');
            });
        }

        // 노출 여부 필터링
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '노출' ? 'Y' : 'N';
            $query->where('bl_promotions.is_state', $status);
        }
        // 디바이스 필터링
        if ($request->filled('device')) {
            if($request->input('device') === '데스크톱') {
                $device = 'P';
            } elseif($request->input('device') === '모바일') {
                $device = 'M';
            } else {
                $device = 'A';
            }
            $query->where('bl_promotions.device', $device);
        }

        // 정렬 설정 추출
        $sortOrder = $request->input('sort_order', 'edate__desc');
        $sortParts = explode('__', $sortOrder);

        // 분리된 값이 적절한 형식인지 확인
        $sortField = "edate";
        $sortDirection = "desc";

        if (count($sortParts) >= 2) {
            $sortField = $sortParts[0];
            $sortDirection =  $sortParts[1];
        }
        $query->orderBy('bl_promotions.'.$sortField, $sortDirection);

        // 페이징 적용
        $popup_contents = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $popup_contents->appends($request->query());

//        //dewbian 디버그용 스크립트
//        $sql = $query->toSql();
//        $bindings = $query->getBindings();
//
//        debug_info($sql);
//        debug_info($bindings);
//
//        echo "<pre>";
//        print_r($popup_contents);
//        echo "</pre>";
//        exit;

        return view('admin.popup.list', compact('popup_contents'));
    }

    /**
     * 엑셀 다운로드 기능 추가
     */
    public function downloadExcel(Request $request)
    {

        $query = DB::table('bl_promotions')
            ->leftJoin('bl_members', 'bl_promotions.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_promotions.*',
                'bl_members.user_name'
            );

        $query->where('bl_promotions.promotions_type', '=','popup');

        // 제목 검색
        if ($request->filled('title')) {
            $query->where('bl_promotions.title', 'like', '%' . $request->input('title') . '%');
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
            $query->where('bl_promotions.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_promotions.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 노출 기간 검색
        if ($request->filled('start_update_date')) {
            $query->where(function ($q) use ($request) {
                $q->where('bl_promotions.sdate', '>=', $request->input('start_update_date') . ' 00:00:00')
                    ->orWhere('bl_promotions.is_view', 'always');
            });
        }

        if ($request->filled('end_update_date')) {
            $query->where(function ($q) use ($request) {
                $q->where('bl_promotions.edate', '>=', $request->input('start_update_date') . ' 00:00:00')
                    ->orWhere('bl_promotions.is_view', 'always');
            });
        }

        // 노출 여부 필터링
        if ($request->filled('status') && $request->input('status') !== '전체') {
            $status = $request->input('status') === '사용함' ? 'Y' : 'N';
            $query->where('bl_promotions.is_state', $status);
        }

        // 디바이스 필터링
        if ($request->filled('device')) {
            if($request->input('device') === '데스크톱') {
                $device = 'P';
            } elseif($request->input('device') === '모바일') {
                $device = 'M';
            } else {
                $device = 'A';
            }
            $query->where('bl_promotions.device', $device);
        }

        // 정렬 설정 추출
        $sortOrder = $request->input('sort_order', 'edate__desc');
        $sortParts = explode('__', $sortOrder);

        // 분리된 값이 적절한 형식인지 확인
        $sortField = "edate";
        $sortDirection = "desc";

        if (count($sortParts) >= 2) {
            $sortField = $sortParts[0];
            $sortDirection =  $sortParts[1];
        }
        $query->orderBy('bl_promotions.'.$sortField, $sortDirection);

        //쿼리디버그
//        $sql = $query->toSql();
//        $bindings = $query->getBindings();
//        debug_error($sql);

        // 데이터 가져오기 (페이징 없이 전체)
        $bl_promotions = $query->get();

        // 스프레드시트 생성
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 헤더 설정
        $sheet->setCellValue('A1', '번호');
        $sheet->setCellValue('B1', '제목');
        $sheet->setCellValue('C1', '팝업이미지');
        $sheet->setCellValue('D1', '이동URL');
        $sheet->setCellValue('E1', '텍스트');
        $sheet->setCellValue('F1', '노출방식');
        $sheet->setCellValue('G1', '노출기간');
        $sheet->setCellValue('H1', '노출여부');
        $sheet->setCellValue('I1', '노출환경');
        $sheet->setCellValue('J1', '하루보지않기');
        $sheet->setCellValue('K1', '등록자');
        $sheet->setCellValue('L1', '등록일자');


        // 데이터 입력
        $row = 2;
        foreach ($bl_promotions as $item) {
            if($item->device === 'P') {
                $device = '데스크톱';
            } elseif($item->device === 'M') {
                $device = '모바일';
            } else {
                $device = '전체';
            }
            $sheet->setCellValue('A'.$row, $item->promotions_id);
            $sheet->setCellValue('B'.$row, $item->title);
            $sheet->setCellValue('C'.$row, $item->pc_img);
            $sheet->setCellValue('D'.$row, $item->path.' target='.$item->target);
            $sheet->setCellValue('E'.$row, $item->info);
            $sheet->setCellValue('F'.$row, $item->is_view == 'always' ? '상시노출' : '기간노출');
            $sheet->setCellValue('G'.$row, $item->sdate.' ~ '.$item->edate);
            $sheet->setCellValue('H'.$row, $item->is_state == 'Y' ? '사용함' : '사용안함');
            $sheet->setCellValue('I'.$row, $device);
            $sheet->setCellValue('J'.$row, $item->is_today == 'Y' ? '사용함' : '사용안함');
            $sheet->setCellValue('K'.$row, $item->user_name);
            $sheet->setCellValue('L'.$row, $item->created_at);
            $row++;
        }

        // 열 너비 자동 조정
        foreach(range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 파일 생성
        $writer = new Xlsx($spreadsheet);
        $filename = '팝업목록_' . date('Ymd_His') . '.xlsx';

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
        return view('admin.popup.create');
    }

    /**
     * 게시글 저장
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'popup_img' => 'required|mimes:jpg,jpeg,png,pdf|max:10240',
            'is_view' => 'required'
        ], [
            'title.required' => '제목은 필수 입력입니다.',
            'title.max' => '제목은 최대 255자까지 가능합니다.',
            'popup_img.required' => '이미지는 필수 입력입니다.',
            'popup_img.mimes' => '이미지 파일만 가능합니다.',
            'popup_img.max' => '이미지 용량은 최대 10M까지 업로드 가능합니다.',
            'is_view.required' => '노출 방식을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return handle_validation_response($request, $validator, 'popup.create');
        }

        $img_path = $request->file('popup_img')->store('promotions', 'public');

        $target = $request->input('target') === '' ? '_self' : $request->input('target');
        $is_state = $request->input('is_state') === '' ? 'N' : $request->input('is_state');
        $is_today = $request->input('is_today') === '' ? 'N' : $request->input('is_today');

        DB::table('bl_promotions')->insert([
            'promotions_type' => $request->input('promotions_type'),
            'admin_id' => $this->sessionId,
            'title' => $request->input('title'),
            'pc_img' => '/storage/'.$img_path,
            'path' => $request->input('path'),
            'target' => $target,
            'info' => $request->input('info'),
            'device' => $request->input('device'),
            'is_view' => $request->input('is_view'),
            'is_state' => $is_state,
            'is_today' => $is_today,
            'sdate' => $request->input('sdate'),
            'edate' => $request->input('edate'),
            'position' => $request->input('position')
        ]);

        $devices = ['pc', 'mobile', 'tablet']; // 사용하는 디바이스 타입들
        foreach ($devices as $device) {
            Cache::forget("global_banners_{$device}");
        }
        return handle_success_response($request,'팝업가 성공적으로 등록되었습니다.','popup.index', []);
    }

    /**
     * 게시글 상세 보기
     */
    public function show(Request $request , $id)
    {

        $popup_content = DB::table('bl_promotions')
            ->leftJoin('bl_members', 'bl_promotions.admin_id', '=', 'bl_members.member_id')
            ->select(
                'bl_promotions.*',
                'bl_members.user_name'
            )
            ->where('bl_promotions.promotions_id', $id)->first();


        if (!$popup_content) {
            return handle_success_response($request,'존재하지 않는 게시글입니다.','popup.index', []);
        }


        $popup_content = (array) $popup_content;
        $popup_content['is_view_txt'] = ($popup_content['is_view'] === 'always') ? '상시 노출' : '기간 노출';
        $popup_content['is_state_txt'] = ($popup_content['is_state'] === 'Y') ? '사용함' : '사용안함';
        $popup_content = (object) $popup_content;

        $popup_config = DB::table('bl_config')
            ->select(DB::raw('code_name, config_id'))
            ->where('code_group', 'popup')
            ->get();

        return view('admin.popup.show', compact('popup_content', 'popup_config'));
    }

    /**
     * 게시글 업데이트
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'is_view' => 'required'
        ], [
            'title.required' => '제목은 필수 입력입니다.',
            'title.max' => '제목은 최대 255자까지 가능합니다.',
            'is_view.required' => '노출 방식을 선택해주세요.'
        ]);

        if ($validator->fails()) {
            return handle_validation_response($request, $validator, 'popup.show', ['id' => $id]);
        }

        $popup_content = DB::table('bl_promotions')
            ->select(DB::raw('pc_img'))
            ->where('bl_promotions.promotions_id', $id)
            ->first();

        if ($request->hasFile('popup_img') && $request->file('popup_img')->isValid()) {

            $img_path = $request->file('popup_img')->store('promotions', 'public');
            $img_path = '/storage/'.$img_path;
            if ($popup_content && $popup_content->pc_img) {
                // 파일 존재 → 삭제 가능
                public_delete($popup_content->pc_img);
            }
        } else {
            $img_path = $popup_content->pc_img;
        }

        $target = $request->input('target') === '' ? '_self' : $request->input('target');
        $is_state = $request->input('is_state') === '' ? 'N' : $request->input('is_state');
        $is_today = $request->input('is_today') === '' ? 'N' : $request->input('is_today');

        try {


            $affected = DB::table('bl_promotions')
                ->where('promotions_id', $id)
                ->update([
                    'promotions_type' => $request->input('promotions_type'),
                    'admin_id' => $this->sessionId,
                    'title' => $request->input('title'),
                    'pc_img' => $img_path,
                    'path' => $request->input('path'),
                    'target' => $target,
                    'info' => $request->input('info'),
                    'device' => $request->input('device'),
                    'is_view' => $request->input('is_view'),
                    'is_state' => $is_state,
                    'is_today' => $is_today,
                    'sdate' => $request->input('sdate'),
                    'edate' => $request->input('edate'),
                    'position' => $request->input('position')
                ]);

            $devices = ['pc', 'mobile', 'tablet']; // 사용하는 디바이스 타입들
            foreach ($devices as $device) {
                Cache::forget("global_banners_{$device}");
            }
            return handle_success_response($request, '게시글이 성공적으로 업데이트되었습니다.', 'popup.show', ['id' => $id]);

        } catch (\Exception $e) {
            // 쿼리 실행 중 오류 발생
            return response()->json(['error' => '데이터 업데이트 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * 게시글 삭제
     */
    public function destroy($id)
    {

        $popup_content = DB::table('bl_promotions')
            ->select(DB::raw('pc_img'))
            ->where('bl_promotions.promotions_id', $id)
            ->first();

        if ($popup_content && $popup_content->pc_img) {
            // 파일 존재 → 삭제 가능
            public_delete($popup_content->pc_img);
        }

        $deleted = DB::table('bl_promotions')->where('promotions_id', $id)->delete();

        if (!$deleted) {
            return redirect()->route('popup.index')
                ->with('error', '게시글 삭제에 실패했습니다.');
        }

        $devices = ['pc', 'mobile', 'tablet']; // 사용하는 디바이스 타입들
        foreach ($devices as $device) {
            Cache::forget("global_banners_{$device}");
        }
        return redirect()->route('popup.index')
            ->with('success', '게시글이 성공적으로 삭제되었습니다.');
    }
}
