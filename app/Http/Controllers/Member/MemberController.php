<?php

namespace App\Http\Controllers\Member;



use App\Http\Controllers\Controller;
use App\Services\EmailService;
use App\Services\MemberConfigService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//use App\Models\Statistics\StatisticsJoin;
//use App\Models\Statistics\StatisticsVisit;


// 이 줄 추가


class MemberController extends Controller
{

    protected $configService;

    public function __construct(MemberConfigService $configService)
    {
        $this->configService = $configService;
    }



    /**
     * 검색을 위해 회원데이터 hash코드 생성
     * 이름, 이메일,
     */
//    public function test(){
//
//        $members = DB::table('bl_members')->get();
//
//        foreach ($members as $member) {
//            $originalName = decrypt($member->user_name); // 암호화된 경우
//            $originalemail = decrypt($member->email); // 암호화된 경우
//            $originalphone = decrypt($member->phone); // 암호화된 경우
//            // 또는 평문이라면: $originalName = $member->name;
//
//
//            DB::table('bl_members')
//                ->where('member_id', $member->member_id)
//                ->update([
//                    'user_name_hash' => hash('sha256', $originalName),
//                    'email_hash' => hash('sha256', $originalemail),
//                    'phone_hash' => hash('sha256', $originalphone)
//                ]);
//        }
//    }


    /**
     * 회원 목록 표시 (검색 및 필터링 추가)
     */
    public function index(Request $request)
    {

        $query = DB::table('bl_members')
            ->select('bl_members.*');

        // 아이디 검색
        if ($request->filled('user_id')) {
            $query->where('bl_members.user_id', 'like', '%' . $request->input('user_id') . '%');
        }

        // 이름 검색
        if ($request->filled('user_name')) {
            $query->where('bl_members.user_name', 'like', '%' . $request->input('user_name') . '%');
        }

        // 전화번호 검색
        if ($request->filled('phone')) {
            $query->where('bl_members.phone', 'like', '%' . $request->input('phone') . '%');
        }

        // 가입일 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_members.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_members.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 회원 방식 필터링
        if ($request->filled('join_type') && $request->input('join_type') !== '전체') {
            $joinType = $request->input('join_type') === '일반' ? 0 : 1;
            $query->where('bl_members.join_type', $joinType);
        }

        // 회원 상태 필터링
        if ($request->filled('state') && $request->input('state') !== '전체') {
            $query->where('bl_members.state', $request->input('state'));
        }

        // 회원 등급 필터링
        if ($request->filled('member_grade_id') && $request->input('member_grade_id') !== '전체') {
            $query->where('bl_members.member_grade_id', $request->input('member_grade_id'));
        }

        // 휴면회원 필터링
        if ($request->filled('sleep') && $request->input('sleep') !== '전체') {
            $sleepStatus = $request->input('sleep') === '휴면' ? 1 : 0;
            $query->where('bl_members.sleep', $sleepStatus);
        }

        // 정렬 설정 추출
        $sortOrder = $request->input('sort_order', 'created_at__desc');
        $sortParts = explode('__', $sortOrder);

        // 분리된 값이 적절한 형식인지 확인
        $sortField = "created_at";
        $sortDirection = "desc";

        if (count($sortParts) >= 2) {
            $sortField = $sortParts[0];
            $sortDirection = $sortParts[1];
        }
        $query->orderBy('bl_members.'.$sortField, $sortDirection);

        // 페이징 적용
        $members = $query->paginate(10);
        // 페이지네이션 링크에 현재 쿼리스트링 유지
        $members->appends($request->query());
        // 암호화된 데이터 복호화 처리
        foreach ($members as $member) {
            // 암호화된 필드들 복호화
            if (isset($member->user_name) && !empty($member->user_name)) {
                try {
                    $member->user_name = decrypt($member->user_name);
                } catch (\Exception $e) {
                    $member->user_name = '-';
                }
            }

            if (isset($member->email) && !empty($member->email)) {
                try {
                    $member->email = decrypt($member->email);
                } catch (\Exception $e) {
                    $member->email = '-';
                }
            }

            if (isset($member->phone) && !empty($member->phone)) {
                try {
                    $member->phone = decrypt($member->phone);
                } catch (\Exception $e) {
                    $member->phone = '-';
                }
            }

            if (isset($member->birthday_date) && !empty($member->birthday_date)) {
                try {
                    $member->birthday_date = decrypt($member->birthday_date);
                } catch (Exception $e) {
                    $member->birthday_date = '-';
                }
            }

            $member->member_grade_name = '비회원';
            if (isset($member->member_grade_id) && !empty($member->member_grade_id)) {
                try {
                    $member->member_grade_name = $this->configService->getGradeName($member->member_grade_id);
                } catch (Exception $e) {
                    $member->member_grade_name = '비회원';
                }
            }
        }

        $grades = $this->configService->getAllGrade();

        return view('admin.member.list', compact('members', 'grades'));
    }


    /**
     * 회원 추가 폼
     */
    public function create()
    {
        // 서비스에서 설정 데이터 가져오기
        $basicConfig = $this->configService->getBasicConfig();
        $profileConfig = $this->configService->getProfileConfig();
        $basic_fields = $this->configService->generateBasicFieldsUseFront();
        $etc_fields = $this->configService->generateEtcFieldsUseFront();

        return view('admin.member.create', compact(
            'basicConfig',
            'profileConfig',
            'basic_fields',
            'etc_fields'
        ));
    }

    /**
     * 회원 저장
     */
    public function store(Request $request)
    {
        try {
            // 서비스에서 필수 필드 정보 가져오기
            $requiredMap = $this->configService->getRequiredFields();
            $rules = [];
            $messages = [];

            // 필수 필드에 대한 검증 규칙
            foreach ($requiredMap as $fieldCode => $fieldName) {
                // 일반 필드의 경우
                if ($fieldCode != 'profile_image') {
                    $rules[$fieldCode] = 'required';
                    $messages[$fieldCode.'.required'] = $fieldName.'은(는) 필수 입력입니다.';
                } else {
                    // 파일 필드인 경우 특별한 규칙 적용
                    $rules[$fieldCode] = 'required|file|mimes:jpeg,png,jpg,gif|max:2048';
                    $messages[$fieldCode.'.required'] = $fieldName.'은(는) 필수 입력입니다.';
                    $messages[$fieldCode.'.file'] = $fieldName.'은(는) 파일이어야 합니다.';
                    $messages[$fieldCode.'.mimes'] = $fieldName.'은(는) jpeg, png, jpg, gif 형식이어야 합니다.';
                    $messages[$fieldCode.'.max'] = $fieldName.'은(는) 2MB 이하여야 합니다.';
                }
            }

            // 이메일 및 기타 유효성 검사 규칙 추가
            if (isset($rules['email'])) {
                $rules['email'] .= '|email|max:100';
                $messages['email.email'] = '이메일 형식이 올바르지 않습니다.';
                $messages['email.max'] = '이메일은 최대 100자까지 입력 가능합니다.';
                // 이메일 중복체크 추가
                $rules['email'] .= '|unique_hashed_email';
                $messages['email.unique_hashed_email'] = '이미 사용중인 이메일입니다.';
            }

            // 사용자 아이디 검사규칙 추가
            if (isset($rules['user_id'])) {
                $rules['user_id'] .= '|unique_hashed_user_id';
                $messages['user_id.unique_hashed_user_id'] = '이미 사용중인 아이디입니다.';
            }

            // 사용자 핸드폰번호 검사규칙 추가
            if (isset($rules['phone'])) {
                $rules['phone'] .= '|numeric|unique_hashed_phone';
                $messages['phone.numeric'] = '핸드폰 번호는 숫자만 입력해주세요.';
                $messages['phone.unique_hashed_phone'] = '이미 사용중인 핸드폰 번호입니다.';
            }


            // 생년월일 유효성 검사 규칙 추가 (숫자 형식)
            if (isset($rules['birthday_date'])) {
                $rules['birthday_date'] .= '|numeric|digits:8|date_format:Ymd';
                $messages['birthday_date.numeric'] = '생년월일은 숫자만 입력 가능합니다.';
                $messages['birthday_date.digits'] = '생년월일은 8자리 숫자(YYYYMMDD)여야 합니다.';
                $messages['birthday_date.date_format'] = '유효하지 않은 날짜 형식입니다.';
            }

            // 비밀번호 필드 규칙 (nullable 처리)
            if (isset($rules['password'])) {
                $rules['password'] = 'nullable|min:8';
                $messages['password.min'] = '비밀번호는 최소 8자 이상이어야 합니다.';
            }
            // 유효성 검사 실행
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $dataToSave = [];
            $etc_dataToSave = [];

            // basic필드만 확인하며 조건에 맞는 데이터만 선택
            foreach ($request->all() as $key => $value) {
                // etc_로 시작하는 필드 확인
                if (strpos($key, 'field') === 0) {
                    $etc_key = 'field'.substr($key, 5);
                    $etc_dataToSave[$etc_key] = $value;
                    continue;
                }
                $dataToSave[$key] = $value;
            }

            // CSRF 토큰 제거
            if (isset($dataToSave['_token'])) {
                unset($dataToSave['_token']);
            }

            //비밀번호 암호화
            $dataToSave['password'] = $request->password ? Hash::make(trim($request->password)) : '';
            //검색을 위해 해시코드 생성
            $dataToSave['user_name_hash'] = hash('sha256', $dataToSave['user_name']);
            $dataToSave['phone_hash'] = hash('sha256', $dataToSave['phone']);
            $dataToSave['email_hash'] = hash('sha256', $dataToSave['email']);

            //이름,전화번호,이메일 암호화
            $dataToSave['user_name'] = encrypt($dataToSave['user_name']);
            $dataToSave['phone'] = encrypt($dataToSave['phone']);
            $dataToSave['email'] = encrypt($dataToSave['email']);
            $dataToSave['birthday_date'] = encrypt($dataToSave['birthday_date']);


            // 프로필 이미지 처리
            $profileImagePath = null;
            if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
                try {
                    $file = $request->file('profile_image');
                    $fileName = 'profile_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $profileImagePath = $file->storeAs('member', $fileName, 'public');
                } catch (Exception $e) {
                    Log::error('프로필 이미지 저장 오류: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => '프로필 이미지 저장 중 오류가 발생했습니다.',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            if ($profileImagePath) {
                $dataToSave['profile_image'] = "/storage/".$profileImagePath;
            }

            $dataToSave['member_grade_id'] = '1';
            // 트랜잭션 시작
            DB::beginTransaction();

            try {
                // 회원 정보 저장
                $member_id = DB::table('bl_members')->insertGetId($dataToSave);

                if (!$member_id) {
                    throw new Exception('회원 정보 저장에 실패했습니다.');
                }

                // 추가 정보 저장
                $etc_dataToSave['member_id'] = $member_id;
                // 배열 데이터를 JSON으로 변환
                foreach ($etc_dataToSave as $key => $value) {
                    if (is_array($value)) {
                        $etc_dataToSave[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                }
                $memberetc_id = DB::table('bl_member_etc')->insertGetId($etc_dataToSave);

                if (!$memberetc_id) {
                    throw new Exception('회원 추가 정보 저장에 실패했습니다.');
                }

                // 모든 작업이 성공하면 트랜잭션 커밋
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => '회원이 성공적으로 등록되었습니다.',
                    'data' => [
                        'member_id' => $member_id,
                        'memberetc_id' => $memberetc_id
                    ],
                    'redirect' => '/'
                ], 200);

            } catch (Exception $e) {
                // 오류 발생 시 트랜잭션 롤백
                DB::rollBack();

                // 업로드된 이미지가 있다면 삭제
                if ($profileImagePath) {
                    Storage::disk('public')->delete($profileImagePath);
                }

                Log::error('회원 등록 오류: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => '회원 등록 중 오류가 발생했습니다.',
                    'error' => $e->getMessage()
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('회원 등록 처리 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '처리 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * * @param bool $use 사용여부
     * * @param string $id 필드 ID
     * * @param string $name 필드 이름
     * * @param string $label 필드 레이블
     * * @param bool $required 필수 여부
     * * @param string $placeholder 플레이스홀더 텍스트
     * * @param string $fieldType 필드 타입 (input, textarea, radio 등)
     * * @param string $options 추가 옵션 (라디오/체크박스/셀렉트박스 옵션 콤마 구분)
     * * @return string HTML 마크업
 */


    /**
     * 회원 상세 보기
     */
    public function show(Request $request , $id)
    {



        $member = DB::table('bl_members')->where('member_id', $id)->first();
        if (!$member) {
            return redirect()->route('member.list')
                ->with('error', '회원을 찾을 수 없습니다.');
        }

        // 암호화된 필드들 복호화
        if (isset($member->user_name) && !empty($member->user_name)) {
            try {
                $member->user_name = decrypt($member->user_name);
            } catch (Exception $e) {
                $member->user_name = '-';
            }
        }

        if (isset($member->email) && !empty($member->email)) {
            try {
                $member->email = decrypt($member->email);
            } catch (Exception $e) {
                $member->email = '-';
            }
        }

        if (isset($member->phone) && !empty($member->phone)) {
            try {
                $member->phone = decrypt($member->phone);
            } catch (Exception $e) {
                $member->phone = '-';
            }
        }

        if (isset($member->birthday_date) && !empty($member->birthday_date)) {
            try {
                $member->birthday_date = decrypt($member->birthday_date);
            } catch (Exception $e) {
                $member->birthday_date = '';
            }
        }

        // 객체를 배열로 변환
        $memberArray = (array)$member;
        // 서비스에서 설정 데이터 가져오기
        $basicConfig = $this->configService->getBasicConfig();
        $profileConfig = $this->configService->getProfileConfig();
        // 기본입력사항 필드만들기
        $basic_fields = $this->configService->generateBasicFieldsUseFront($memberArray, [], false, true);
        // 추가입력사항 필드만들기
        $etc_member = DB::table('bl_member_etc')->where('member_id', $id)->first();
        $etc_memberArray = (array)$etc_member;
        $etc_fields = $this->configService->generateEtcFieldsUseFront($etc_memberArray);

        // 서비스에서 회원 등급 설정 데이터 가져오기
        $grades = $this->configService->getAllGrade();

        $member->gradeName = $this->configService->getGradeName($member->member_grade_id);
        return view('web.member.show',
                        compact('member',
                                'grades',
                                'basicConfig',
                                'profileConfig',
                                'basic_fields',
                                'etc_fields')
        );
    }

    /**
     * 회원 정보 업데이트
     */
    public function update(Request $request, $id)
    {
        try {
            // 서비스에서 필수 필드 정보 가져오기
            $requiredMap = $this->configService->getRequiredFields();

            // 수정시에는 패스워드는 받지 않음.
            if (isset($requiredMap['password'])) {
                unset($requiredMap['password']);
            }

            $rules = [];
            $messages = [];

            // 필수 필드에 대한 검증 규칙
            foreach ($requiredMap as $fieldCode => $fieldName) {
                // 일반 필드의 경우
                if ($fieldCode != 'profile_image') {
                    $rules[$fieldCode] = 'required';
                    $messages[$fieldCode.'.required'] = $fieldName.'은(는) 필수 입력입니다.';
                } else {
                    // 파일 필드인 경우 특별한 규칙 적용
                    // 업데이트에서는 이미지가 필수가 아닐 수 있으므로 required 제외
                    $rules[$fieldCode] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
                    $messages[$fieldCode.'.file'] = $fieldName.'은(는) 파일이어야 합니다.';
                    $messages[$fieldCode.'.mimes'] = $fieldName.'은(는) jpeg, png, jpg, gif 형식이어야 합니다.';
                    $messages[$fieldCode.'.max'] = $fieldName.'은(는) 2MB 이하여야 합니다.';
                }
            }

            // 이메일 및 기타 유효성 검사 규칙 추가
            if (isset($rules['email'])) {
                $rules['email'] .= '|email|max:100';
                $messages['email.email'] = '이메일 형식이 올바르지 않습니다.';
                $messages['email.max'] = '이메일은 최대 100자까지 입력 가능합니다.';
                // 이메일 중복체크 추가
                $rules['email'] .= '|unique_hashed_email:'.$id;
                $messages['email.unique_hashed_email'] = '이미 사용중인 이메일입니다.';
            }

            // 사용자 아이디 검사규칙 추가
            if (isset($rules['user_id'])) {
                $rules['user_id'] .= '|unique_hashed_user_id:'.$id;
                $messages['user_id.unique_hashed_user_id'] = '이미 사용중인 아이디입니다.';
            }

            // 사용자 핸드폰번호 검사규칙 추가
            if (isset($rules['phone'])) {
                $rules['phone'] .= '|numeric|unique_hashed_phone:'.$id;
                $messages['phone.numeric'] = '핸드폰 번호는 숫자만 입력해주세요.';
                $messages['phone.unique_hashed_phone'] = '이미 사용중인 핸드폰 번호입니다.';
            }

            // 생년월일 유효성 검사 규칙 추가 (숫자 형식)
            if (isset($rules['birthday_date'])) {
                $rules['birthday_date'] .= '|numeric|digits:8|date_format:Ymd';
                $messages['birthday_date.numeric'] = '생년월일은 숫자만 입력 가능합니다.';
                $messages['birthday_date.digits'] = '생년월일은 8자리 숫자(YYYYMMDD)여야 합니다.';
                $messages['birthday_date.date_format'] = '유효하지 않은 날짜 형식입니다.';
            }

            // 비밀번호 필드 규칙 (nullable 처리)
            if (isset($rules['password'])) {
                $rules['password'] = 'nullable|min:8|confirmed';
                $messages['password.min'] = '비밀번호는 최소 8자 이상이어야 합니다.';
                $messages['password.confirmed'] = '비밀번호 확인이 일치하지 않습니다.';
            }

            // 유효성 검사 실행
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // 기존 회원 정보 조회
            $member = DB::table('bl_members')->where('member_id', $id)->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => '회원을 찾을 수 없습니다.'
                ], 404);
            }

            $dataToSave = [];
            $etc_dataToSave = [];

            // basic필드만 확인하며 조건에 맞는 데이터만 선택
            foreach ($request->all() as $key => $value) {
                // etc_로 시작하는 필드 확인
                if (strpos($key, 'field') === 0) {
                    $etc_key = 'field'.substr($key, 5);
                    $etc_dataToSave[$etc_key] = $value;
                    continue;
                }
                $dataToSave[$key] = $value;
            }

            // CSRF 토큰 제거
            if (isset($dataToSave['_token'])) {
                unset($dataToSave['_token']);
            }
            // method 제거
            if (isset($dataToSave['_method'])) {
                unset($dataToSave['_method']);
            }
            //비밀번호 제거
            if (isset($dataToSave['password'])) {
                unset($dataToSave['password']);
            }
            // 프로필이미지 수정 플래그데이터 제거
            if (isset($dataToSave['profile_image_change'])) {
                unset($dataToSave['profile_image_change']);
            }
            // 이름, 전화번호, 이메일, 생년월일 암호화
            if (isset($dataToSave['user_name'])) {
                $dataToSave['user_name_hash'] = hash('sha256', $dataToSave['user_name']);
                $dataToSave['user_name'] = encrypt($dataToSave['user_name']);
            }
            if (isset($dataToSave['phone'])) {
                $dataToSave['phone_hash'] = hash('sha256', $dataToSave['phone']);
                $dataToSave['phone'] = encrypt($dataToSave['phone']);
            }
            if (isset($dataToSave['email'])) {
                $dataToSave['email_hash'] = hash('sha256', $dataToSave['email']);
                $dataToSave['email'] = encrypt($dataToSave['email']);
            }
            if (isset($dataToSave['birthday_date'])) {
                $dataToSave['birthday_date'] = encrypt($dataToSave['birthday_date']);
            }

            if (isset($dataToSave['member_grade_id'])) {
                if($dataToSave['member_grade_id'] > 5){
                    $dataToSave['user_type'] = '1'; //관리자로
                }
                if($dataToSave['member_grade_id'] < 6){
                    $dataToSave['user_type'] = '0'; //사용자로
                }
            }

            // 프로필 이미지 처리
            $profileImagePath = $member->profile_image;
            if ($request->profile_image_change == '1' && $request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
                try {
                    // 기존 이미지가 있으면 삭제
                    if ($profileImagePath) {
                        Storage::disk('public')->delete($profileImagePath);
                        //Storage::disk('public')->delete(str_replace('/storage/', '', $profileImagePath));
                    }

                    $file = $request->file('profile_image');
                    $fileName = 'profile_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $uploadPath = $file->storeAs('member', $fileName, 'public');
                    $dataToSave['profile_image'] = "/storage/".$uploadPath;
                } catch (Exception $e) {
                    Log::error('프로필 이미지 저장 오류: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => '프로필 이미지 저장 중 오류가 발생했습니다.',
                        'error' => $e->getMessage(),
                        'redirect' => route('front_member.show', $member->member_id)
                    ], 500);
                }
            }

//            // 동의 필드 처리
//            if (isset($dataToSave['mail_agree'])) {
//                $dataToSave['mail_agree'] = $request->has('mail_agree') ? 1 : 0;
//                $dataToSave['mail_agree_date'] = $request->has('mail_agree') ? now() : $member->mail_agree_date;
//            }
//            if (isset($dataToSave['sms_agree'])) {
//                $dataToSave['sms_agree'] = $request->has('sms_agree') ? 1 : 0;
//                $dataToSave['sms_agree_date'] = $request->has('sms_agree') ? now() : $member->sms_agree_date;
//            }
//            if (isset($dataToSave['select_privacy'])) {
//                $dataToSave['select_privacy'] = $request->has('select_privacy') ? 1 : 0;
//            }


            // 기본 업데이트 필드 추가
            $dataToSave['updated_at'] = now();

            // 쿼리 로그 활성화
            DB::enableQueryLog();

            try {
                DB::beginTransaction();
                // 회원 정보 업데이트 전 데이터 로깅
                Log::info('업데이트할 데이터:', $dataToSave);

                // 회원 정보 업데이트
                $result = DB::table('bl_members')
                    ->where('member_id', $id)
                    ->update($dataToSave);

                // 실행된 쿼리 확인 (업데이트 쿼리만)
                $queries = DB::getQueryLog();
                Log::info('회원 업데이트 쿼리:', [end($queries)]);

                // 결과가 0인 경우와 false인 경우 구분하기
                if ($result === false) {
                    throw new Exception('회원 정보 업데이트에 실패했습니다.');
                } else {
                    Log::info('업데이트된 레코드 수: ' . $result);
                }

                // 추가 정보가 있으면 업데이트
                if (!empty($etc_dataToSave)) {
                    // 추가 정보 데이터 로깅
                    Log::info('추가 정보 업데이트 데이터:', $etc_dataToSave);
                    // 추가 정보가 이미 있는지 확인
                    $memberEtc = DB::table('bl_member_etc')->where('member_id', $id)->first();
                    // 배열 데이터를 JSON으로 변환
                    foreach ($etc_dataToSave as $key => $value) {
                        if (is_array($value)) {
                            $etc_dataToSave[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
                        }
                    }

                    // 추가 정보가 이미 있는지 확인
                    $memberEtc = DB::table('bl_member_etc')->where('member_id', $id)->first();

                    if ($memberEtc) {
                        // 기존 정보 업데이트
                        $etcResult = DB::table('bl_member_etc')
                            ->where('member_id', $id)
                            ->update($etc_dataToSave);

                        // 추가 정보 쿼리 확인
                        $queries = DB::getQueryLog();
                        Log::info('추가 정보 업데이트 쿼리:', [end($queries)]);
                    } else {
                        // 새로 추가
                        $etc_dataToSave['member_id'] = $id;
                        $etcResult = DB::table('bl_member_etc')->insert($etc_dataToSave);

                        // 추가 정보 쿼리 확인
                        $queries = DB::getQueryLog();
                        Log::info('추가 정보 삽입 쿼리:', [end($queries)]);
                    }

                    if ($etcResult === false) {
                        throw new Exception('회원 추가 정보 업데이트에 실패했습니다.');
                    } else {
                        Log::info('추가 정보 업데이트 결과: ' . $etcResult);
                    }
                }

                // 모든 작업이 성공하면 트랜잭션 커밋
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => '회원 정보가 성공적으로 업데이트되었습니다.',
                    'data' => [
                        'member_id' => $id
                    ],
                    'redirect' => route('front_member.show', $member->member_id)
                ], 200);

            } catch (Exception $e) {
                // 오류 발생 시 트랜잭션 롤백
                DB::rollBack();

                Log::error('회원 정보 업데이트 오류: ' . $e->getMessage());
                Log::error('오류 스택 트레이스: ' . $e->getTraceAsString());

                return response()->json([
                    'success' => false,
                    'message' => '회원 정보 업데이트 중 오류가 발생했습니다.',
                    'error' => $e->getMessage()
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('회원 정보 업데이트 처리 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '처리 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function generateField(bool $use, string $id, string $name, string $label, bool $required, string $placeholder, string $fieldType, string $options = '')
    {
        $html = '';
        if($use){
            $html = '<div class="input_item half">';
            $html .= '<label class="input_title" for="' . $id . '">' . $label . ($required ? ' <span class="text-danger">*</span>' : '') . '</label>';
            $html .= '<div class="inner_box">';

            // 기본 속성 설정
            $attrs = '';
            $value = '';
            $attributes = [];
            $readonly = false;
            foreach ($attributes as $key => $val) {
                $attrs .= ' ' . $key . '="' . $val . '"';
            }

            // readonly 속성 추가
            $readonlyAttr = $readonly ? ' readonly' : '';

            // 필드 타입에 따라 다른 입력 요소 생성
            switch ($fieldType) {
                case 'input':
                    $html .= '<input type="text" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '" ' . $readonlyAttr . $attrs . '>';
                    break;

                case 'password':
                    $html .= '<input type="password" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $readonlyAttr . $attrs . '>';
                    break;

                case 'textarea':
                    $html .= '<div class="textarea_count">';
                    $html .= '<textarea class="common_textarea" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $readonlyAttr . $attrs . '>' . $value . '</textarea>';
                    $html .= '<p><span>0</span> / 200</p>';
                    $html .= '</div>';
                    break;

                case 'radio':
                    $html .= '<div class="flex gap_input">';
                    if (!empty($options)) {
                        $optionList = explode(',', $options);
                        foreach ($optionList as $index => $option) {
                            $option = trim($option);
                            $isChecked = $value == $option ? ' checked' : '';

                            $html .= '<label class="radio_input">';
                            $html .= '<input type="radio" value = "'.$option.'" name="' . $name . '"  ' . $isChecked . $readonlyAttr . $attrs . '>';
                            $html .= '<span>' . $option . '</span>';
                            $html .= '</label>';
                        }
                    }
                    $html .= '</div>';
                    break;

                case 'checkbox':
                    $html .= '<label class="chk_input">';
                    $html .= '<input type="checkbox"   name="' . $name . '"' . ($value ? ' checked' : '') . $readonlyAttr . $attrs . '>';
                    $html .= '<span>' . $placeholder . '</span>';
                    $html .= '</label>';
                    break;

                case 'selectbox':
                    $html .= '<div class="custom_select_1 js_custom_select">';
                    $html .= '<input type="text" class="common_input select_value" placeholder="' . $placeholder . '" data-value="' . $value . '" value="' . $value . '" readonly>';
                    $html .= '<ul role="list">';

                    if (!empty($options)) {
                        $optionList = explode(',', $options);
                        foreach ($optionList as $option) {
                            $option = trim($option);
                            $dataValue = strtolower(str_replace(' ', '_', $option));
                            $html .= '<li role="listitem" data-value="' . $dataValue . '">' . $option . '</li>';
                        }
                    }

                    $html .= '</ul>';
                    $html .= '</div>';
                    break;

                default:
                    $html .= '<input type="text" class="common_input" id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '"' . $readonlyAttr . $attrs . '>';
                    break;
            }

            $html .= '</div>';
            $html .= '<div id="'.$id.'-error" class="error_msg"></div>';

            $html .= '</div>';
        }
        return $html;
    }
    /**
     * 회원 탈퇴 처리
     */
    public function withdraw($id)
    {
        // 회원 정보 조회
        $member = DB::table('bl_members')->where('member_id', $id)->first();

        if (!$member) {
            return response()->json(['message' => '회원을 찾을 수 없습니다.'], 404);
        }

        // 회원 상태를 탈퇴로 변경
        DB::table('bl_members')
            ->where('member_id', $id)
            ->update([
                'state' => 1, // 탈퇴 상태
                'withdrawal_at' => now(),
            ]);

        return response()->json(['message' => '회원이 성공적으로 탈퇴 처리되었습니다.']);
    }


    /**
     * 회원 비밀번호 초기화 및 이메일 전송
     */
    public function resetPassword(Request $request, $id)
    {
        try {
            // 회원 정보 조회
            $member = DB::table('bl_members')->where('member_id', $id)->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => '회원을 찾을 수 없습니다.'
                ], 404);
            }

            // 암호화된 이메일 복호화
            try {
                $email = decrypt($member->email);
            } catch (Exception $e) {
                Log::error('이메일 복호화 실패: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => '회원 이메일을 확인할 수 없습니다.'
                ], 400);
            }

            // 랜덤 비밀번호 생성 (8자리 숫자와 문자 조합)
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
            $randomPassword = substr(str_shuffle($chars), 0, 10);

            try {
                $hashedPassword = Hash::make($randomPassword);

                DB::beginTransaction();

                DB::table('bl_members')
                    ->where('member_id', $id)
                    ->update([
                        'password' => $hashedPassword,
                        'password_old' => $member->password,
                        'password_changed_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::commit();

                // 데이터베이스 트랜잭션 완료 후 이메일 발송
                try {
                    $this->sendPasswordResetEmail($member, $randomPassword);
                } catch (Exception $emailError) {
                    // 이메일 발송 실패 로깅 (DB 업데이트는 롤백하지 않음)
                    Log::error('비밀번호 초기화 이메일 발송 오류: ' . $emailError->getMessage());

                    return response()->json([
                        'success' => false,
                        'message' => '비밀번호가 초기화되었으나, 이메일 발송에 실패했습니다.'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => '비밀번호가 초기화되었으며, 회원 이메일로 전송되었습니다.'
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('비밀번호 초기화 오류: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => '비밀번호 초기화 중 오류가 발생했습니다.'
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('비밀번호 초기화 처리 오류: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 비밀번호 초기화 이메일 발송
     */
    private function sendPasswordResetEmail($member, $password)
    {
        $decrypt_email = safe_decrypt($member->email);
        try {

            // 메일 발송 전 이메일 주소 유효성 검사
            if (!filter_var($decrypt_email, FILTER_VALIDATE_EMAIL)) {
                Log::error('이메일 주소 형식 오류: ' . $decrypt_email);
                throw new Exception('이메일 주소 형식이 올바르지 않습니다.');
            }


            $emailService = app(EmailService::class);

            // 메일 데이터 준비
            $mail_data = [
                'subject'    => '비밀번호가 초기화되었습니다',
                'password'      => $password,
                'user_name'     => safe_decrypt($member->user_name),
                'user_email'    => $decrypt_email,
                'reset_time'    => now()->format('Y-m-d H:i:s')
            ];


            $queuedEmail = $emailService->queueEmail(
                'emails.passwordreset',     // 뷰 경로
                $mail_data,                 // 뷰에 전달할 데이터
                $decrypt_email,             // 수신자
                '',                         // 발신자메일주소
                ''                          //발신자명
            );

            Log::info('비밀번호 초기화 이메일 발송 완료: ' . $queuedEmail);

            return true;

        } catch (Exception $e) {
            dd($e->getMessage());
            Log::error('이메일 발송 오류: ' . $e->getMessage());
            throw $e; // 상위 메서드에서 처리할 수 있도록 예외를 다시 던짐
        }
    }

    /**
     * 회원 프로필 이미지 삭제
     */
    public function deleteProfileImage($id)
    {
        // 회원 정보 조회
        $member = DB::table('bl_members')->where('member_id', $id)->first();

        if (!$member) {
            return response()->json(['message' => '회원을 찾을 수 없습니다.'], 404);
        }

        // 프로필 이미지 삭제
        if ($member->profile_image) {
            Storage::disk('public')->delete($member->profile_image);

            DB::table('bl_members')
                ->where('member_id', $id)
                ->update([
                    'profile_image' => null,
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['message' => '프로필 이미지가 성공적으로 삭제되었습니다.']);
    }




    /**
     * 엑셀 다운로드 기능 추가
     */
    public function downloadExcel(Request $request)
    {
        $query = DB::table('bl_members')
        ->select('bl_members.*');

        // 아이디 검색
        if ($request->filled('user_id')) {
            $query->where('bl_members.user_id', 'like', '%' . $request->input('user_id') . '%');
        }

        // 이름 검색
        if ($request->filled('user_name')) {
            $query->where('bl_members.user_name', 'like', '%' . $request->input('user_name') . '%');
        }

        // 전화번호 검색
        if ($request->filled('phone')) {
            $query->where('bl_members.phone', 'like', '%' . $request->input('phone') . '%');
        }

        // 가입일 기간 검색
        if ($request->filled('start_date')) {
            $query->where('bl_members.created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('bl_members.created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        // 회원 방식 필터링
        if ($request->filled('join_type') && $request->input('join_type') !== '전체') {
            $joinType = $request->input('join_type') === '일반' ? 0 : 1;
            $query->where('bl_members.join_type', $joinType);
        }

        // 회원 상태 필터링
        if ($request->filled('state') && $request->input('state') !== '전체') {
            $query->where('bl_members.state', $request->input('state'));
        }

        // 회원 등급 필터링
        if ($request->filled('member_grade_id') && $request->input('member_grade_id') !== '전체') {
            $query->where('bl_members.member_grade_id', $request->input('member_grade_id'));
        }

        // 휴면회원 필터링
        if ($request->filled('sleep') && $request->input('sleep') !== '전체') {
            $sleepStatus = $request->input('sleep') === '휴면' ? 1 : 0;
            $query->where('bl_members.sleep', $sleepStatus);
        }

        // 정렬 설정 추출
        $sortOrder = $request->input('sort_order', 'created_at__desc');
        $sortParts = explode('__', $sortOrder);

        // 분리된 값이 적절한 형식인지 확인
        $sortField = "created_at";
        $sortDirection = "desc";

        if (count($sortParts) >= 2) {
            $sortField = $sortParts[0];
            $sortDirection = $sortParts[1];
        }
        $query->orderBy('bl_members.'.$sortField, $sortDirection);

        // 데이터 가져오기 (페이징 없이 전체)
        $bl_member_contents = $query->get();

        // 스프레드시트 생성
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 헤더 설정
        $sheet->setCellValue('A1', '번호');
        $sheet->setCellValue('B1', '구분');
        $sheet->setCellValue('C1', '이름');
        $sheet->setCellValue('D1', '닉네임');
        $sheet->setCellValue('E1', '멤버등급');
        $sheet->setCellValue('F1', '등록일');
        $sheet->setCellValue('G1', '수정일');

        // 데이터 입력
        $row = 2;
        foreach ($bl_member_contents as $item) {
            $sheet->setCellValue('A'.$row, $item->member_id);
            $sheet->setCellValue('B'.$row, $item->user_type == '1' ? '관리자' : '일반');
            $sheet->setCellValue('C'.$row, $item->user_id);
            $sheet->setCellValue('D'.$row, safe_decrypt($item->user_name));
            $sheet->setCellValue('E'.$row, $item->member_grade_id);
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
        $filename = '사용자목록_' . date('Ymd_His') . '.xlsx';

        // 헤더 설정
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // 파일 저장
        $writer->save('php://output');
        exit;
    }

    /**
     * 회원 추가 폼
     */
    public function front_create()
    {
        // 서비스에서 설정 데이터 가져오기
        $basicConfig = $this->configService->getBasicConfig();
        $profileConfig = $this->configService->getProfileConfig();
        $basic_fields = $this->configService->generateBasicFieldsUseFront();
        $etc_fields = $this->configService->generateEtcFieldsUseFront();
        return view('web.member.signup', compact(
            'basicConfig',
            'profileConfig',
            'basic_fields',
            'etc_fields'
        ));
    }

}
