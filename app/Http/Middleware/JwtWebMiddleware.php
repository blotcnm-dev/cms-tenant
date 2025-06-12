<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JwtWebMiddleware
{
    protected $jwtService;
    protected $loginRoute;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
        $this->loginRoute = config('auth.jwt.login_route', '/login');
    }

    public function handle(Request $request, Closure $next)
    {
        // 쿠키에서 JWT 토큰 가져오기 (웹에서는 주로 쿠키 사용)
        $token = $request->cookie($this->jwtService->getCookieName());

        // 토큰이 없으면 로그인 페이지로 리디렉션
        if (!$token) {
            // 로그인이 필요하다는 세션 메시지 추가
            session()->flash('message', '로그인이 필요합니다.');
            session()->flash('message_type', 'warning');

            // 현재 URL을 세션에 저장하여 로그인 후 리디렉션에 사용
            session()->put('url.intended', url()->current());

            return redirect($this->loginRoute);
        }

        // 토큰 검증
        $payload = $this->jwtService->verify($token);

        // 토큰이 유효하지 않으면 로그인 페이지로 리디렉션
        if (!$payload) {
            // 세션에서 사용자 정보 제거 (로그아웃 처리)
            Auth::logout();

            // 쿠키 삭제
            $cookie = cookie()->forget($this->jwtService->getCookieName());

            // 로그인이 만료되었다는 메시지 표시
            session()->flash('message', '세션이 만료되었습니다. 다시 로그인해 주세요.');
            session()->flash('message_type', 'info');

            return redirect($this->loginRoute)->withCookie($cookie);
        }

        // 토큰에서 사용자 정보 가져오기
        $userId = $payload['user_id'] ?? null;

        // 사용자가 인증되지 않았으면 자동 로그인 처리
        if ($userId && !Auth::check()) {
            // 사용자 모델 가져오기
            $user = \App\Models\User::find($userId);

            if ($user) {
                // 수동으로 인증 처리
                Auth::login($user);
            } else {
                // 사용자를 찾을 수 없으면 토큰 무효화
                $cookie = cookie()->forget($this->jwtService->getCookieName());
                session()->flash('message', '유효하지 않은 사용자입니다. 다시 로그인해 주세요.');

                return redirect($this->loginRoute)->withCookie($cookie);
            }
        }

        // 페이로드를 요청에 추가
        $request->attributes->add(['jwt_payload' => $payload]);

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

                // 요청 속성에 갱신 정보 추가
                $request->attributes->add(['token_refreshed' => true]);

                // 응답 생성 후 쿠키 추가
                $response = $next($request);
                return $response->withCookie($cookie);
            }
        }

        return $next($request);
    }
}
