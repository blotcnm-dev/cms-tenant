<?php
namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use App\Services\JwtService;

class JwtManagementController extends Controller
{

    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }
    /*
     *  JWT 토큰 생성
     */
    public function generate(Request $request)
    {
        /*
         * logged_out
         * token_expired
         * invalid_token
         * forced_logout
         * token_updated
         */
        try {

            // 로그인 된 세션정보 확인
//            if (!Session::has('blot_mbid')) {
//                throw new \Exception('logged_out');
//            }

            // 사용자 정보 설정
            $jwt_user_id = '16';
            $jwt_user_name = '나는사용자다';

            // 사용자 정보 확인
            if (empty($jwt_user_id) || empty($jwt_user_name)) {
                throw new \Exception('invalid_token');
            }

            $exptime = time() + $this->jwtService->getTtl();
            // JWT 페이로드 구성
            $payload = [
                'id' => $jwt_user_id,
                'name' => $jwt_user_name,
                'exp' => $exptime,
                'expread' => date('Y-m-d', $exptime),
                'iat' => time(),
                'iatread' => date('Y-m-d', time()),
            ];

            // 토큰 생성 및 쿠키 설정
            $return_result = $this->jwtService->generate($payload);
            echo "<pre>";
            print_r($return_result);
            echo "</pre>";
            echo "========================<br>";
            $this->jwtService->setCookie($return_result);

            echo " JWTMANAGEMENT CONTROLLER\n";
            echo "<pre>";
            print_r($_COOKIE);
            echo "</pre>";


            // 성공 응답
            return response()->json([
                'status' => 'success',
                'message' => '인증토큰이 생성되었습니다'
            ], 200);

        } catch (\Exception $e) {
            // 예외 발생 시 쿠키 제거
            $this->jwtService->removeCookie();

            // 예외 메시지에 따른 상태 설정
            $status = $e->getMessage();
            $message = '';
            $code = 401;

            // 상태에 따른 메시지 설정
            switch ($status) {
                case 'logged_out':
                    $message = '인증토큰이 없습니다';
                    break;
                case 'token_expired':
                    $message = '토큰이 만료되었습니다';
                    break;
                case 'invalid_token':
                    $message = '유효하지 않은 토큰입니다';
                    break;
                case 'forced_logout':
                    $message = '강제 로그아웃 되었습니다';
                    break;
                default:
                    $status = 'error';
                    $message = '토큰 생성 중 오류가 발생했습니다';
                    $code = 500;
                    // 로그 기록
                    \Log::error('Token error: ' . $e->getMessage());
            }

            // 오류 응답 반환
            return response()->json([
                'status' => $status,
                'message' => $message
            ], $code);
        }
    }

    /*
     * JWT 토큰 갱신
     */
    public function refresh(Request $request)
    {
        $token = $request->cookie($this->jwtService->getCookieName());
        $payload = $this->jwtService->verify($token);
        if (!$payload) {
            // 세션의 모든 데이터를 삭제
            session()->flush();
            // 현재 세션의 CSRF 토큰 재생성
            session()->regenerateToken();

            // 모든 세션 데이터 삭제 및 세션 ID 재생성
            session()->invalidate();

            // 또는 특정 세션만 삭제하는 경우
            session()->forget(['blot_mbid', 'user_role', 'other_data']);
            throw new \Exception('invalid_token');
        }

        // 토큰 리프레시가 필요한지 확인
        if ($this->jwtService->needsRefresh($token)) {
            // 토큰 갱신
            $newToken = $this->jwtService->refresh($token);

            if ($newToken) {
                // 새 쿠키 설정
                $payload = $this->jwtService->getPayload($newToken);
                $expiry = $payload['exp'] ?? (time() + $this->jwtService->getTtl());

                // 쿠키 생성
                $cookie = cookie(
                    $this->jwtService->getCookieName(),
                    $newToken,
                    // 쿠키 수명은 (만료시간 - 현재시간) / 60 (분 단위)로 계산
                    ($expiry - time()) / 60,
                    '/',
                    $this->jwtService->getCookieDomain(),
                    $this->jwtService->isSecure(),
                    $this->jwtService->isHttpOnly()
                );

                // 성공 응답
                return response()->json([
                    'status' => 'success',
                    'message' => '인증토큰이 갱신되었습니다'
                ], 200);
            }
        }
    }

    /*
     * JWT 토큰 삭제
     */
    public function destory(Request $request)
    {
        try {
            $this->jwtService->removeCookie();
            return response()->json([
                'status' => 'success',
                'message' => 'JWT토큰을 삭제하였습니다.'
            ], 400);
        }catch (\Exception $e){
            // 오류 응답 반환
            return response()->json([
                'status' => $e->getMessage(),
                'message' => $e->getMessage()
            ], 500);

        }
    }
}
