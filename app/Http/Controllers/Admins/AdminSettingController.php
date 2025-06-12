<?php

namespace App\Http\Controllers\Admins;



use App\Http\Controllers\Controller;
use App\Services\FrontendDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

//use App\Models\Statistics\StatisticsJoin;
//use App\Models\Statistics\StatisticsVisit;

class AdminSettingController extends Controller
{
    public function __construct(FrontendDataService $frontendDataService)
    {
        $this->frontendDataService = $frontendDataService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        // 사이트 설정 내용
        $site_config = DB::table('bl_config')
            ->where('code_group', 'site')
            ->orderBy('code', 'asc')
            ->get();

        // 푸터, SNS, 로그인 설정 데이터 가져오기
        $footerSettings = $site_config->where('code', 'footer_settings')->first();
        $snsSettings = $site_config->where('code', 'sns_settings')->first();
        $loginSettings = $site_config->where('code', 'login_settings')->first();
        $forbidSettings = $site_config->where('code', 'forbid_settings')->first();
        // JSON 데이터 디코딩
        $footerData = $footerSettings ? json_decode($footerSettings->value, true) : [];
        $snsData = $snsSettings ? json_decode($snsSettings->value, true) : [];
        $loginData = $loginSettings ? json_decode($loginSettings->value, true) : [];
        $forbidData = $forbidSettings ? json_decode($forbidSettings->value, true) : [];

        // 뷰로 데이터 전달
        return view('admin.site.setting', compact(
            'site_config',
            'footerData',
            'snsData',
            'loginData',
            'forbidData'
        ));
    }
    public function settingStore(Request $request)
    {
        //dd($request);
        try {
            // 각 섹션별 처리 함수 호출
            $faviconResult = $this->handleFaviconUpload($request);
            $footerResult = $this->handleFooterSettings($request);
            $snsResult = $this->handleSnsSettings($request);
            $loginResult = $this->handleLoginSettings($request);
            $forbidResult = $this->handleForbidSettings($request);


            // 스크립트 설정 처리 추가
            $scriptResult = $this->handleScriptSettings($request);


            // 일반 사이트 설정 처리
            $siteSettingsResult = $this->handleSiteSettings($request);

            // 모든 처리 결과 성공 여부 확인
            $allSuccess =
                $faviconResult['success'] &&
                $footerResult['success'] &&
                $snsResult['success'] &&
                $loginResult['success'] &&
                $forbidResult['success'] &&
                $siteSettingsResult['success'];

            $statusCode = $allSuccess ? 200 : 422;
            // JSON 파일 업데이트
            $this->updateFrontendData();

            return response()->json([
                'success' => $allSuccess,
                'msg' => $allSuccess ? '설정이 성공적으로 저장되었습니다.' : '일부 설정 저장 중 오류가 발생했습니다.',
                'details' => [
                    'favicon' => $faviconResult,
                    'footer' => $footerResult,
                    'sns' => $snsResult,
                    'login' => $loginResult,
                    'forbid' => $forbidResult,
                    'siteSettings' => $siteSettingsResult,
                    'script' => $scriptResult
                ]
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error('사이트 설정 저장 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * 간단한 스크립트 설정 처리 (GTM, GA만)
     */
    private function handleScriptSettings(Request $request): array
    {
        try {
            // GTM Head 스크립트
            $gtmHead = $request->input('gtm_head');
            if ($gtmHead !== null) {
                DB::table('bl_config')
                    ->updateOrInsert(
                        ['code_group' => 'site', 'code' => 'gtm-head'],
                        ['value' => trim($gtmHead), 'updated_at' => now()]
                    );
            }

            // GTM Body 스크립트 (noscript)
            $gtmBody = $request->input('gtm_body');
            if ($gtmBody !== null) {
                DB::table('bl_config')
                    ->updateOrInsert(
                        ['code_group' => 'site', 'code' => 'gtm-body'],
                        ['value' => trim($gtmBody), 'updated_at' => now()]
                    );
            }

            // Google Analytics Head 스크립트
            $gtaHead = $request->input('gta_head');
            if ($gtaHead !== null) {
                DB::table('bl_config')
                    ->updateOrInsert(
                        ['code_group' => 'site', 'code' => 'gta-head'],
                        ['value' => trim($gtaHead), 'updated_at' => now()]
                    );
            }

            return ['success' => true, 'msg' => '스크립트 설정이 저장되었습니다.'];

        } catch (\Exception $e) {
            Log::error('스크립트 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '스크립트 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }


    public function user_index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {


        // 필드 타입 매핑 배열 정의
        $fieldTypeMap = [
            'input' => '인풋박스',
            'textarea' => '텍스트 에이리어',
            'password' => '비밀번호',
            'radio' => '라디오',
            //'upload' => '업로드',
            'selectbox' => '셀렉트 박스',
            'checkbox' => '체크 박스',
            //'link' => '링크'
        ];

        $requiredCodes = [ 'user_id', 'password', 'user_name', 'phone', 'email'];

        // 회원관리 사이트 설정 내용 (user_etc 제외)
        $user_config = DB::table('bl_config')
            ->where('code_group', 'user')
            ->where('code', '!=', 'user_etc')  // user_etc 코드 제외
            ->orderBy('config_id', 'asc')
            ->get();

        // 각 항목에 표시 텍스트 추가
        foreach ($user_config as $item) {
            $item->value_txt = $fieldTypeMap[$item->value] ?? $item->value;
            // 필수 코드인지 확인하여 disabled 속성 추가
            if (in_array($item->code, $requiredCodes)) {
                $item->disabled = true;
            } else {
                $item->disabled = false;
            }
        }

        // user_etc 설정 데이터는 별도로 가져오기
        $user_etc_Settings = DB::table('bl_config')
            ->where('code_group', 'user')
            ->where('code', 'user_etc')
            ->first();

        // JSON 데이터 디코딩
        $etcData = $user_etc_Settings ? json_decode($user_etc_Settings->value, true) : [];


        // 뷰로 데이터 전달
        return view('admin.user.setting', compact(
            'user_config',
            'etcData',
            'fieldTypeMap'
        ));
    }



    public function user_etc_del(Request $request)
    {
        try {
            // 삭제할 항목의 ID를 받아옵니다
            $etcNo = $request->input('etc_no');

            if (!$etcNo) {
                return response()->json(['success' => false, 'msg' => '삭제할 항목 ID가 없습니다.'], 400);
            }else {

                DB::table('bl_member_etc')
                    ->update([
                        'field'.$etcNo => null
                    ]);

            }
            $configData = DB::table('bl_config')
                ->where('code_group', 'user')
                ->where('code', 'user_etc')
                ->first();

            if (!$configData) {
                return response()->json(['success' => false, 'msg' => '설정 데이터가 존재하지 않습니다.'], 404);
            }

            // JSON 문자열을 배열로 변환
            $customFields = json_decode($configData->value, true);

            if (!is_array($customFields)) {
                $customFields = [];
            }

            // 삭제할 항목 찾기 및 제거
            $filtered = array_filter($customFields, function($item) use ($etcNo) {
                return $item['etc_no'] != $etcNo;
            });

            // 인덱스 재정렬
            $filtered = array_values($filtered);

            // 업데이트된 데이터 저장
            DB::table('bl_config')
                ->where('code_group', 'user')
                ->where('code', 'user_etc')
                ->update([
                    'value' => json_encode($filtered, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now()
                ]);

            // 캐시 초기화 (필요한 경우)
            Cache::forget('user_all_config');

            return response()->json([
                'success' => true,
                'msg' => '항목이 성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('부가 설정 삭제 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }



    public function user_store(Request $request)
    {
        try {

            // 각 섹션별 처리 함수 호출
            $etcResult = $this->handleEtcSettings($request);
            // 일반 사이트 설정 처리
            $userSettingsResult = $this->handleUserSettings($request);

            // 모든 처리 결과 성공 여부 확인
            $allSuccess =
                $etcResult['success'] &&
                $userSettingsResult['success'];

            $statusCode = $allSuccess ? 200 : 422;

            // 캐싱 변경
//            Cache::remember('user_all_config', 60*24, function () {
//                return DB::table('bl_config')
//                    ->where('code_group', 'user')
//                    ->orderBy('config_id', 'asc')
//                    ->get();
//            });
            // 캐시 내용을 지워서 초기화
            Cache::forget('user_all_config');


            return response()->json([
                'success' => $allSuccess,
                'msg' => $allSuccess ? '설정이 성공적으로 저장되었습니다.' : '일부 설정 저장 중 오류가 발생했습니다.',
                'details' => [
                    'etcResult' => $etcResult,
                    'userSetting' => $userSettingsResult
                ]
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error('사이트 설정 저장 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 회원가입 기본 사항 처리
     */
    private function handleUserSettings(Request $request): array
    {

        try {
            $useCheckboxes = $request->input('use_chk', []);
            $requireCheckboxes = $request->input('require_chk', []);

            // 사용 체크박스 처리
            foreach ($useCheckboxes as $code => $value) {
                DB::table('bl_config')
                    ->updateOrInsert(
                        [
                            'code_group' => 'user',
                            'code' => $code
                        ],
                        [
                            'use' => 1,
                            'updated_at' => now()
                        ]
                    );
            }

            // 필수 체크박스 처리
            foreach ($requireCheckboxes as $code => $value) {
                DB::table('bl_config')
                    ->where('code_group', 'user')
                    ->where('code', $code)
                    ->update([
                        'sort' => 1,
                        'updated_at' => now()
                    ]);
            }

            // 체크되지 않은 항목들 처리 (사용안함으로 업데이트)
            $allCodes = DB::table('bl_config')
                ->where('code_group', 'user')
                ->pluck('code')
                ->toArray();

            foreach ($allCodes as $code) {

                $requiredCodes = [ 'user_id', 'password', 'user_name', 'phone', 'email'];
                // 필수 코드는 업데이트에서 제외
                if (in_array($code, $requiredCodes)) {
                    continue;
                }

                if (!isset($useCheckboxes[$code])) {
                    DB::table('bl_config')
                        ->where('code_group', 'user')
                        ->where('code', $code)
                        ->update([
                            'use' => 0,
                            'updated_at' => now()
                        ]);
                }
                 if (!isset($requireCheckboxes[$code])) {
                    DB::table('bl_config')
                        ->where('code_group', 'user')
                        ->where('code', $code)
                        ->update([
                            'sort' => 0,
                            'updated_at' => now()
                        ]);
                }
            }

            return ['success' => true, 'msg' => '사이트 기본 설정이 저장되었습니다.'];
        } catch (\Exception $e) {
            Log::error('사이트 기본 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '사이트 기본 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * 회원가입 관리 추가사항 처리
     */
    private function handleEtcSettings(Request $request): array
    {
        try{
            $custom_fields = $request->input('custom_fields');

            if (!$custom_fields) {
                return ['success' => true, 'msg' => '부가 설정 데이터가 없습니다.'];
            }

            // 이미 JSON 문자열이 아니라면 인코딩
            if (!is_string($custom_fields) || !$this->isJson($custom_fields)) {
                $custom_fields = json_encode($custom_fields, JSON_UNESCAPED_UNICODE);
            }


            // DB에 저장
            DB::table('bl_config')
                ->updateOrInsert(
                    [
                        'code_group' => 'user',
                        'code' => 'user_etc'
                    ],
                    [
                        'value' => $custom_fields,
                        'updated_at' => now()
                    ]
                );

//            // 실행된 쿼리 가져오기
//            $queries = DB::getQueryLog();
//
//            // 쿼리 로그 비활성화
//            DB::disableQueryLog();
//
//            // 쿼리도 로그에 기록
//            foreach ($queries as $query) {
//                Log::info('실행된 쿼리: ' . $this->formatSqlQuery($query));
//            }

            return ['success' => true, 'msg' => '부가 설정이 저장되었습니다.'];
        }
        catch (\Exception $e) {
            Log::error('회원가입 관리 부가 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '회원가입 관리 부가 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * 파비콘 업로드 처리
     */
    private function handleFaviconUpload(Request $request): array
    {
        try {
            if (!$request->hasFile('favicon_file')) {
                return ['success' => true, 'msg' => '파비콘 파일이 없습니다.'];
            }

            $file = $request->file('favicon_file');

            $validator = Validator::make($request->file(), [
                'favicon_file' => 'required|file|mimes:ico,png,jpg,jpeg|max:10240',
            ]);

            $validator->setCustomMessages([
                'favicon_file.required' => '파비콘 이미지를 첨부해주세요.',
                'favicon_file.file' => '유효한 파일 형식이 아닙니다.',
                'favicon_file.mimes' => '파비콘은 ico, png, jpg, jpeg 형식만 가능합니다.',
                'favicon_file.max' => '파일 크기는 최대 10MB까지 가능합니다.',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'msg' => '파일 유효성 검사 실패',
                    'errors' => $validator->errors()->toArray()
                ];
            }

            // 사이트 설정 내용
            $faviconConfig = DB::table('bl_config')
                ->where('code_group', 'site')
                ->where('code', 'favicon')
                ->first();

            $oldFileName = $faviconConfig ? $faviconConfig->value : null;

            // 기존 파일 있으면 삭제
            if ($oldFileName && Storage::disk('public')->exists('site/' . $oldFileName)) {
                Storage::disk('public')->delete('site/' . $oldFileName);
            }

            // 새 파일명 생성 및 저장
            $fileName = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('site', $fileName, 'public');

            // DB 업데이트
            DB::table('bl_config')
                ->updateOrInsert(
                    [
                        'code_group' => 'site',
                        'code' => 'favicon'
                    ],
                    [
                        'value' => $fileName,
                        'updated_at' => now()
                    ]
                );

            return ['success' => true, 'msg' => '파비콘이 성공적으로 저장되었습니다.', 'fileName' => $fileName];
        } catch (\Exception $e) {
            Log::error('파비콘 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '파비콘 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * 푸터 설정 처리
     */
    private function handleFooterSettings(Request $request): array
    {
        try {
            $footerSettings = $request->input('footer_settings');

            if (!$footerSettings) {
                return ['success' => true, 'msg' => '푸터 설정 데이터가 없습니다.'];
            }

            // 이미 JSON 문자열이 아니라면 인코딩
            if (!is_string($footerSettings) || !$this->isJson($footerSettings)) {
                $footerSettings = json_encode($footerSettings, JSON_UNESCAPED_UNICODE);
            }

            // DB에 저장
            DB::table('bl_config')
                ->updateOrInsert(
                    [
                        'code_group' => 'site',
                        'code' => 'footer_settings'
                    ],
                    [
                        'value' => $footerSettings,
                        'updated_at' => now()
                    ]
                );

            return ['success' => true, 'msg' => '푸터 설정이 저장되었습니다.'];
        } catch (\Exception $e) {
            Log::error('푸터 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '푸터 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * SNS 설정 처리
     */
    private function handleSnsSettings(Request $request): array
    {
        try {
            $snsSettings = $request->input('sns_settings');

            if (!$snsSettings) {
                return ['success' => true, 'msg' => 'SNS 설정 데이터가 없습니다.'];
            }

            // 이미 JSON 문자열이 아니라면 인코딩
            if (!is_string($snsSettings) || !$this->isJson($snsSettings)) {
                $snsSettings = json_encode($snsSettings, JSON_UNESCAPED_UNICODE);
            }

            // DB에 저장
            DB::table('bl_config')
                ->updateOrInsert(
                    [
                        'code_group' => 'site',
                        'code' => 'sns_settings'
                    ],
                    [
                        'value' => $snsSettings,
                        'updated_at' => now()
                    ]
                );

            return ['success' => true, 'msg' => 'SNS 설정이 저장되었습니다.'];
        } catch (\Exception $e) {
            Log::error('SNS 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => 'SNS 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }
    /**
     * 금칙어 설정 처리
     */
    private function handleForbidSettings(Request $request): array
    {
        try {
            $forbidSettings = $request->input('forbid_settings');

            if (!$forbidSettings) {
                return ['success' => true, 'msg' => '금칙어 설정 데이터가 없습니다.'];
            }

            // 이미 JSON 문자열이 아니라면 인코딩
            if (!is_string($forbidSettings) || !$this->isJson($forbidSettings)) {
                $forbidSettings = json_encode($forbidSettings, JSON_UNESCAPED_UNICODE);
            }

            // DB에 저장
            DB::table('bl_config')
                ->updateOrInsert(
                    [
                        'code_group' => 'site',
                        'code' => 'forbid_settings'
                    ],
                    [
                        'value' => $forbidSettings,
                        'updated_at' => now()
                    ]
                );

            return ['success' => true, 'msg' => '금칙어 설정이 저장되었습니다.'];
        } catch (\Exception $e) {
            Log::error('금칙어 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '금칙어 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }
    /**
     * 로그인 설정 처리
     */
    private function handleLoginSettings(Request $request): array
    {
        try {
            $loginSettings = $request->input('login_settings');

            if (!$loginSettings) {
                return ['success' => true, 'msg' => '로그인 설정 데이터가 없습니다.'];
            }

            // 이미 JSON 문자열이 아니라면 인코딩
            if (!is_string($loginSettings) || !$this->isJson($loginSettings)) {
                $loginSettings = json_encode($loginSettings, JSON_UNESCAPED_UNICODE);
            }

            // DB에 저장
            DB::table('bl_config')
                ->updateOrInsert(
                    [
                        'code_group' => 'site',
                        'code' => 'login_settings'
                    ],
                    [
                        'value' => $loginSettings,
                        'updated_at' => now()
                    ]
                );

//            // 실행된 쿼리 가져오기
//            $queries = DB::getQueryLog();
//
//            // 쿼리 로그 비활성화
//            DB::disableQueryLog();
//
//            // 쿼리도 로그에 기록
//            foreach ($queries as $query) {
//                Log::info('실행된 쿼리: ' . $this->formatSqlQuery($query));
//            }

            return ['success' => true, 'msg' => '로그인 설정이 저장되었습니다.'];
        } catch (\Exception $e) {
            Log::error('로그인 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '로그인 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * 일반 사이트 설정 처리 (홈페이지명, 도메인, 메타태그 등)
     */
    private function handleSiteSettings(Request $request): array
    {
        try {
            // 처리할 기본 설정 항목들
            $siteConfigItems = [
                'home_name_kr',
                'home_name_en',
                'domain',
                'meta-title',
                'meta-desc',
                'meta-keyword',
                'meta-author'
            ];

            foreach ($siteConfigItems as $item) {
                $value = $request->input($item);

                if ($value !== null) {
                    DB::table('bl_config')
                        ->updateOrInsert(
                            [
                                'code_group' => 'site',
                                'code' => $item
                            ],
                            [
                                'value' => $value,
                                'updated_at' => now()
                            ]
                        );
                }
            }

            return ['success' => true, 'msg' => '사이트 기본 설정이 저장되었습니다.'];
        } catch (\Exception $e) {
            Log::error('사이트 기본 설정 저장 오류: ' . $e->getMessage());
            return ['success' => false, 'msg' => '사이트 기본 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * JSON 문자열인지 확인하는 헬퍼 함수
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 파비콘 삭제 처리
     */
    public function deleteFavicon(Request $request)
    {
        try {
            // 파비콘 설정 정보 조회
            $faviconConfig = DB::table('bl_config')
                ->where('code_group', 'site')
                ->where('code', 'favicon')
                ->first();

            if (!$faviconConfig) {

                return response()->json([
                    'success' => false,
                    'msg' => '파비콘 정보를 찾을 수 없습니다.'
                ]);
            }

            $fileName = $faviconConfig->value;

            // 파일 삭제
            if ($fileName && Storage::disk('public')->exists('site/' . $fileName)) {
                Storage::disk('public')->delete('site/' . $fileName);
            }

            // DB 업데이트 (값을 비우거나 null로 설정)
            DB::table('bl_config')
                ->where('code_group', 'site')
                ->where('code', 'favicon')
                ->update(['value' => null]);

            return response()->json([
                'success' => true,
                'msg' => '파비콘이 성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('파비콘 삭제 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => '파비콘 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
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
