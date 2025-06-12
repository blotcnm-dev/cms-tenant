<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\JwtService;
use Illuminate\Http\Request;

class JwtApiMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        // 여러 소스에서 토큰 획득 (쿠키, 헤더, Bearer 토큰)
        $token = $this->jwtService->getTokenFromRequest($request);

        // 토큰이 없는 경우
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => '인증 토큰이 없습니다'
            ], 401);
        }

        // 토큰 검증
        $payload = $this->jwtService->verify($token);

        // 토큰이 유효하지 않은 경우
        if (!$payload) {
            return response()->json([
                'status' => 'error',
                'message' => '유효하지 않은 토큰입니다'
            ], 401);
        }

        // 검증된 페이로드를 요청에 추가
        $request->attributes->add(['jwt_payload' => $payload]);

        // 사용자 ID를 auth 시스템에 등록 (필요한 경우)
        if (isset($payload['user_id'])) {
            $request->attributes->add(['user_id' => $payload['user_id']]);
        }

        // 토큰 만료 임계값 체크 및 갱신
        $tokenRefreshed = false;
        $newToken = null;

        if ($this->jwtService->needsRefresh($token)) {
            $newToken = $this->jwtService->refresh($token);

            if ($newToken) {
                $tokenRefreshed = true;
                // 토큰 갱신 정보를 요청 객체에 추가
                $request->attributes->add(['token_refreshed' => true]);
                $request->attributes->add(['new_token' => $newToken]);
            }
        }

        // 원래 요청 처리 후 응답 가져오기
        $response = $next($request);

        // 토큰이 갱신된 경우 응답에 토큰 추가
        if ($tokenRefreshed && $newToken) {
            // 쿠키에 토큰 설정 (SPA와 브라우저 기반 클라이언트용)
            $this->jwtService->setCookie($newToken);

            // Authorization 헤더에 토큰 추가 (모바일 앱이나 다른 클라이언트용)
            $response->header('Authorization', 'Bearer ' . $newToken);

            // 토큰 갱신 정보를 JSON 응답에 추가 (가능한 경우)
            if (method_exists($response, 'getData')) {
                $data = $response->getData(true);

                if (is_array($data)) {
                    // 기존 meta 섹션이 있으면 그곳에 추가, 없으면 생성
                    if (!isset($data['meta'])) {
                        $data['meta'] = [];
                    }

                    $data['meta']['token_refreshed'] = true;
                    // 필요한 경우 만료 시간 포함
                    $payloadData = $this->jwtService->getPayload($newToken);
                    if (isset($payloadData['exp'])) {
                        $data['meta']['token_expires_at'] = date('Y-m-d H:i:s', $payloadData['exp']);
                    }

                    $response->setData($data);
                }
            }
        }

        return $response;
    }
}
