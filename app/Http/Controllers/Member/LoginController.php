<?php

namespace App\Http\Controllers\Member;

use App\Exceptions\CodeException;
use App\Services\MemberConfigService;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Models\AdminUserLog;
use App\Models\Admins\AdminUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
class LoginController extends Controller
{

    /**
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request)
    {
        if (Session::has('blot_adid')) {
            return redirect(route('master.dashboard'));
        }

        if(Str::contains($request->header('User-Agent'), 'Trident') || Str::contains($request->header('User-Agent'), 'MSIE'))
        {
            echo "Explorer not supported";
            exit;
        }

        return view('admin.logins.index');
    }


    /**
     * 관리자 로그인
     *
     * @param Request $request
     * @return JsonResponse|array
     * @throws \JsonException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function loginProc(Request $request): JsonResponse|array
    {
        $userIp = getRealIP();
        try
        {
            $securityConfig = getAppGlobalData('securityConfig');
            if (isset($securityConfig->user_reject_ip) && in_array($userIp, $securityConfig->user_reject_ip, true)) {
                throw new CodeException('IP_REJECT', '해당 아이피는 접근 차단 입니다.');
            }

            // 로그인 제한 상태인지 체크
            $rejectDate = session()->get('LOGIN_REJECT_DATE');
            if ($rejectDate > now()) {
                throw new CodeException('LOGIN_REJECT_DATE', '로그인 시도 초과입니다.', [
                    'rejectDate' => $rejectDate
                ]);
            }

            $validate = Validator::make($request->all(), [
                'user_id' => 'required',
                'password' => 'required'
            ], [
                'user_id.required' => '관리자 아이디를 입력하세요.',
                'password.required' => '비밀번호를 입력하세요.'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validate->errors()
                ], 422);
            }

            $user = DB::table('bl_members')
                ->select(
                    'bl_members.*'
                )
                ->where('bl_members.user_id', $request->user_id )
                ->first();

            if (!$user) {
                throw new CodeException('ID_ERROR', '가입된 회원이 아닐 수 있습니다. ::: 로그인 실패 입니다.');
            }
            //차단
            if ($user->state === '2') {
                    throw new CodeException('STATE_REJECT', '로그인이 차단된 상태입니다. ');
            }
            //탈퇴
            if ($user->state === '1') {
                throw new CodeException('STATE_WITEDRAW', '탈퇴된 회원입니다.');
            }

//            // 상태 체크
//            if ($user->state === 'REJECT') {
//                session()->put('REJECT_LOGIN_USER_ID', $user->user_id);
//
//                if ($user->lock_expires > now()) {
//                    session()->put('USER_REJECT_DATE', $user->lock_expires);
//                    throw new CodeException('STATE_REJECT', '로그인 제한 상태입니다.');
//                }
//
////                // 거부 상태에 대한 일자가 초과 했다면 상태를 변경한다.
////                $adminUserModel->setUpdate(['state' => 'ACTIVE', 'lock_expires' => null], ['admin_id' => $user->admin_id]);
////
////                (new AdminUserLog())->postModel((object)[
////                    'admin_id' => $user->admin_id,
////                    'channel' => 'login',
////                    'log' => json_encode(['message' => 'reject => active 자동 처리'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
////                ]);
//            }

//            // 접속 허용 아이피 체크
//            if ($user->access_ip_check === 1) {
//                $access_ip = json_decode($user->access_ips, true, 512, JSON_THROW_ON_ERROR);
//                if (!in_array($userIp, $access_ip, true)) {
//                    throw new CodeException('IP_NOT_ALLOWED', '접속 아이피는 허용된 아이피가 아닙니다.');
//                }
//            }

//            // 실패 횟수 체크
//            $loginFailCount = session()->get('LOGIN_FAIL_COUNT') ?? 0;
            if (Hash::check($request->password, $user->password) === false) {
                //$loginFailCount++;
//
////                // 로그인 업데이트
////                $adminUserModel->setUpdate([
////                    'failures_count' => $user->failures_count+1,
////                    'last_failure_at' => now()
////                ], [
////                    'admin_id' => $user->admin_id
////                ]);
//
//                if ($securityConfig->login_reject === 'use' && $loginFailCount >= (int)$securityConfig->login_fail_count) {
//                    //session()->put('REJECT_LOGIN_USER_ID', $user->user_id);
//                    //session()->put('LOGIN_REJECT_DATE', now()->addHours((int)$securityConfig->login_reject_hour)->format('Y-m-d H:i:s'));
//                }
//
//                // 횟수
//                session()->put('LOGIN_FAIL_COUNT', $loginFailCount);
//
                throw new CodeException('PASSWORD_CHECK_ERROR',  '비밀번호가 올바르지 않습니다.');
            }

            $code = '';
//            // 비밀번호 초과여부 체크
//            if ($user->password_updated_at <= now()->subDays((int)$securityConfig->passwd_day)->format('Y-m-d H:i:s')) {
//                # $code = 'PASSWORD_CHANGE';
//            }
//
//            // 보안 로그인이 설정된 경우
//            if ($securityConfig->security_login === 'use') {
//                if ($user->security_auth_date === '' || $user->security_auth_date <= now()->subDays((int)$securityConfig->security_login_day)->format('Y-m-d')) {
//                    session()->put('SECURITY_LOGIN_ADMIN_ID', $user->admin_id);
//
//                    throw new CodeException('SECURITY_LOGIN', '보안인증이 필요합니다.', [
//                        'phone' => $user->phone,
//                        'email' => $user->email,
//                        'rememberme' => $request->rememberme ?? ''
//                    ]);
//                }
//            }

            $token = $this->successLogin($user, $request);

            return [
                'success' => true,
                'code' => $code,
                'token' => $token
            ];
        }
        catch(CodeException $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|array
     * @throws \JsonException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function loginSecurityProc(Request $request): JsonResponse|array
    {
        try {
            $res = (new AuthController())->auth($request);
            if (!$res['success']) {
                throw new CodeException('AUTH_ERROR', $res['message']);
            }

            if(!session()->exists('SECURITY_LOGIN_ADMIN_ID')) {
                throw new CodeException('ERROR', '정상적인 접근이 아닙니다.');
            }

            $adminUserModel = new AdminUser();
            $user = $adminUserModel->getFirst(['*'], ['admin_id' => session()->get('SECURITY_LOGIN_ADMIN_ID')])['data']['row'];

            $adminUserModel->setUpdate([
                'security_auth_date' => now()
            ], [
                'admin_id' => $user->admin_id
            ]);

            $token = $this->successLogin($user, $request);
            return [
                'success' => true,
                'token' => $token
            ];
        }
        catch(CodeException $e)
        {
            return $e->render();
        }
    }

    /**
     * 로그인 처리
     *
     * @param AdminUser $user
     * @param Request $request
     * @return string
     * @throws \JsonException
     */
    protected function successLogin_origin(AdminUser $user, Request $request): string
    {
        // 토큰 생성
        $token = $user->createToken($user->user_id)->plainTextToken;

        // 이전 세션 모두 삭제
        session()->flush();

        // 세션 생성
        session()->put('ADMIN_ID', $user->user_id);
        session()->put('USER_ID', $user->user_id);
        session()->put('ADMIN_NAME', $user->user_name);
        session()->put('ADMIN_TYPE', $user->user_type);
        session()->put('PROFILE', $user->profile_image);
        session()->put('MENU_AUTH_IDS', Arr::pluck((new \App\Models\AdminUserAuth())->getAuths($user->admin_id)['data']['rows']->where('access', 1), 'admin_menu_id'));
        session()->put('TOKEN_ID', explode('|', $token)[0]);

//        // 로그인 업데이트
//        (new AdminUser())->setUpdate([
//            'last_login_at' => now(),
//            'last_login_ip' => DB::raw("INET_ATON('".getRealIP()."')"),
//            'login_count' => $user->login_count+1,
//            'failures_count' => 0,
//            'first_failure' => null,
//            'lock_expires' => null
//        ], [
//            'admin_id' => $user->admin_id
//        ]);

//        //event log insert
//        (new AdminUserLog())->postModel((object)[
//            'admin_id' => $user->admin_id,
//            'channel' => 'login',
//            'log' => json_encode($request->all(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
//        ]);

        return $token;
    }


    protected function successLogin($user, Request $request): string
    {
        // 토큰 생성
        //$token = $user->createToken($user->user_id)->plainTextToken;


        // 서비스 인스턴스 생성
        $memberConfigService = new MemberConfigService();
        // 특정 코드에 대한 등급 이름 가져오기
        $gradeName = $memberConfigService->getGradeName($user->member_grade_id);

        // 이전 세션 모두 삭제
        session()->flush();
        // 세션 생성
        session()->put('blot_adid', $user->user_id);
        session()->put('blot_mbid', $user->member_id);
        session()->put('blot_adnm', decrypt($user->user_name));
        session()->put('blot_uid', $user->user_id);
        session()->put('blot_ugrd', $user->member_grade_id);;
        session()->put('blot_ugnm', $gradeName);
        session()->put('blot_atyp', $user->user_type);
        session()->put('blot_upf', $user->profile_image);





 //      session()->put('MENU_AUTH_IDS', Arr::pluck((new \App\Models\AdminUserAuth())->getAuths($user->user_id)['data']['rows']->where('access', 1), 'admin_menu_id'));
//        session()->put('TOKEN_ID', explode('|', $token)[0]);

//        // 로그인 업데이트
//        (new AdminUser())->setUpdate([
//            'last_login_at' => now(),
//            'last_login_ip' => DB::raw("INET_ATON('".getRealIP()."')"),
//            'login_count' => $user->login_count+1,
//            'failures_count' => 0,
//            'first_failure' => null,
//            'lock_expires' => null
//        ], [
//            'admin_id' => $user->admin_id
//        ]);

//        //event log insert
//        (new AdminUserLog())->postModel((object)[
//            'admin_id' => $user->admin_id,
//            'channel' => 'login',
//            'log' => json_encode($request->all(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
//        ]);

//        return $token;
        return 'token';
    }
    /**
     * 로그아웃
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function logout(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        // 토큰삭제
        if (session()->get('TOKEN_ID')) {
            (new AdminUser())->getFirst(['*'], ['admin_id' => session()->get('ADMIN_ID')])['data']['row']->tokens()->where('id', session()->get('TOKEN_ID'))->delete();
        }

        // 세션삭제
        session()->flush();

        return redirect('/master/login'.($request->referrer ? '?referrer='.urlencode($request->referrer) : ''));
    }

    /**
     * 로그인 실패 레이어
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function loginFailLayer(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('/admin/logins/layers/fail', [
            'securityConfig' => getAppGlobalData('securityConfig')
        ]);
    }

    /**
     * 로그인 거부 사유 레이어들
     * @param $type
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function loginRejectLayer($type): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('/admin/logins/layers/reject', [
            'type' => $type
        ]);
    }


    /**
     * 비밀번호 변경 알림 레이어
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function passwordChangeAlertLayer(Request $request)
    {
        return view('/admin/logins/layers/passwordChangeAlert');
    }

    /**
     * 비밀번호 변경 레이어
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function passwordChangeLayer(Request $request)
    {
        return view('/admin/logins/layers/passwordChange', [
            'securityConfig' => getAppGlobalData('securityConfig')
        ]);
    }

    /**
     * 인증레이어
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function authLayer(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('/admin/logins/layers/auth', [
            'securityConfig' => getAppGlobalData('securityConfig')
        ]);
    }




    public function dbinfo()
    {
        $allowIP = [
            '211.59.67.87',
            '222.238.66.76',
            '203.216.182.245',
            '118.37.55.78',
            '112.148.3.105',
            '121.190.233.7',
            '127.0.0.1'
        ];
        if (!in_array(request()->ip(), $allowIP)) {
            return response("권한 없음", 403);
        }


        $dbName = "cms";

        $tables = DB::select("SELECT * FROM INFORMATION_SCHEMA.TABLES
                          WHERE TABLE_SCHEMA = ?
                          ORDER BY
                            CASE WHEN TABLE_NAME LIKE 'bl_%' THEN 0 ELSE 1 END,
                            CASE WHEN TABLE_NAME LIKE 'bl_%' THEN CREATE_TIME END DESC,
                            TABLE_NAME", [$dbName]);

        $tablesData = [];

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;

            $columns = DB::select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                               WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                               ORDER BY ORDINAL_POSITION", [$dbName, $tableName]);

            $tablesData[] = [
                'table' => $table,
                'columns' => $columns
            ];
        }

        return view('db-info', compact('tablesData'));
    }



    /**
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application
     */
    public function front_login(Request $request)
    {
        if (Session::has('blot_mbid')) {
            return redirect(route('index'));
        }

        if(Str::contains($request->header('User-Agent'), 'Trident') || Str::contains($request->header('User-Agent'), 'MSIE'))
        {
            echo "Explorer not supported";
            exit;
        }

        return view('web.member.signin');
    }
    /**
     * 프론트 로그인
     *
     * @param Request $request
     * @return JsonResponse|array
     * @throws \JsonException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function front_loginProc(Request $request): JsonResponse|array
    {
        $userIp = getRealIP();
        try
        {
            $securityConfig = getAppGlobalData('securityConfig');
            if (isset($securityConfig->user_reject_ip) && in_array($userIp, $securityConfig->user_reject_ip, true)) {
                throw new CodeException('IP_REJECT', '해당 아이피는 접근 차단 입니다.');
            }

            // 로그인 제한 상태인지 체크
            $rejectDate = session()->get('LOGIN_REJECT_DATE');
            if ($rejectDate > now()) {
                throw new CodeException('LOGIN_REJECT_DATE', '로그인 시도 초과입니다.', [
                    'rejectDate' => $rejectDate
                ]);
            }

            $validate = Validator::make($request->all(), [
                'user_id' => 'required',
                'password' => 'required'
            ], [
                'user_id.required' => '아이디를 입력하세요.',
                'password.required' => '비밀번호를 입력하세요.'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validate->errors()
                ], 422);
            }

            $user = DB::table('bl_members')
                ->select(
                    'bl_members.*'
                )
                ->where('bl_members.user_id', $request->user_id )
                ->first();

            if (!$user) {
                throw new CodeException('ID_ERROR', '회원이 아닐 수 있습니다.::: 로그인 실패 입니다.');
            }
            //차단
            if ($user->state === '2') {
                throw new CodeException('STATE_REJECT', '로그인이 차단된 상태입니다. ');
            }
            //탈퇴
            if ($user->state === '1') {
                throw new CodeException('STATE_WITEDRAW', '탈퇴된 회원입니다.');
            }

            $code = '';
            $token = $this->successLogin($user, $request);

            return [
                'success' => true,
                'code' => $code,
                'token' => $token
            ];
        }
        catch(CodeException $e)
        {
            return $e->render();
        }
    }

    public function front_logout(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
//        // 토큰삭제
//        if (session()->get('TOKEN_ID')) {
//            (new AdminUser())->getFirst(['*'], ['admin_id' => session()->get('ADMIN_ID')])['data']['row']->tokens()->where('id', session()->get('TOKEN_ID'))->delete();
//        }

        // 세션삭제
        session()->flush();

        return redirect('/login'.($request->referrer ? '?referrer='.urlencode($request->referrer) : ''));
    }
}
