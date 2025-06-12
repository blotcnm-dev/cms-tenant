<?php

namespace App\Http\Controllers\Admins;

use App\Exceptions\CodeException;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Direct\Auth;
use App\Models\AdminUserLog;
use App\Services\MemberConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;


class DirectLoginController extends Controller{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

     /**
     * 최고관리자를 통한 입점사 관리자 직접 로그인 처리
     */
    public function handleDirectLogin(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            session()->flush();
            return redirect()->route('master.login')->with('error', '잘못된 접근입니다.');
        }

        // 토큰 검증
        $payload = $this->validateToken($token);
        if (!$payload) {
            session()->flush();
            return redirect()->route('master.login')->with('error', '토큰이 유효 하지 않습니다. ');
        }

//        // 최고관리자 정보 검증 // 이 검증은 나중에 하자
//        if (!$this->verifySuperAdmin($payload)) {
//            return redirect('/admin/login')->with('error', '권한이 없습니다.');
//        }

        // 입점사 관리자 정보 확인 및 로그인 처리
        $admin = $this->getProviderAdmin($payload['pm_id']);
        if (!$admin) {
            //session()->flush();
            return redirect()->route('master.login')->with('error', '관리자 정보를 찾을 수 없습니다.');
        }


//        if (Hash::check($payload['pm_password'], $admin->password) === false) {
//            session()->flush();
//            return redirect()->route('master.login')->with('error', '비밀번호가 올바르지 않습니다.');
//        }

        // 새로운 세션
        $token = $this->successLogin($admin, $request);

       // dd(session()->all());
        return redirect()->route('master.dashboard');
    }

    /**
     * 입점사 관리자 세션 할당
     */
    protected function successLogin($user, Request $request): string
    {
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
     * 입점사 관리자 정보 조회
     */
    private function getProviderAdmin($adminId)
    {


        $user = DB::table('bl_members')
            ->select(
                'bl_members.*'
            )
            ->where('bl_members.user_id', $adminId )
            ->where('bl_members.user_type', '1' )
            ->first();

        return $user;
    }


    /**
     * 토큰 검증
     */
    private function validateToken($token)
    {
        try {
            // JwtService를 사용해서 토큰 검증
            $payload = $this->jwtService->verify($token);

            if (!$payload) {
                return false;
            }

            // IP 확인
            if (isset($payload['ip']) && $payload['ip'] !== request()->ip()) {
                return false;
            }

            // 목적 확인
            if (!isset($payload['purpose']) || $payload['purpose'] !== 'SuperAdmin_direct_login') {
                return false;
            }

            return $payload;

        } catch (\Exception $e) {
            \Log::error('Token validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
