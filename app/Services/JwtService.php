<?php

namespace App\Services;

class JwtService
{
    private $secret_key;
    private $cookie_domain;
    private $ttl;
    private $refresh_threshold;
    private $cookie_name;
    private $secure;
    private $http_only;

    public function __construct()
    {
        $this->secret_key = config('auth.jwt.secret');
        $this->cookie_domain = config('auth.jwt.cookie_domain');
        $this->ttl = config('auth.jwt.ttl');
        $this->refresh_threshold = config('auth.jwt.refresh_threshold');
        $this->cookie_name = config('auth.jwt.cookie_name');
        $this->secure = config('auth.jwt.secure');
        $this->http_only = config('auth.jwt.http_only');
    }

    public function generate($payload, $customTtl = null)
    {
        // 기본 payload 설정이 없으면 현재 시간 추가
        if (!isset($payload['iat'])) {
            $payload['iat'] = time();
        }

        // 만료 시간이 없으면 기본 TTL 사용
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + ($customTtl ?? $this->ttl);
        }

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = $this->base64url_encode($header);
        $payload = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $signature = $this->base64url_encode($signature);

        return $header . "." . $payload . "." . $signature;
    }

    public function verify($token)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3) return false;

        list($header, $payload, $signature) = $parts;

        $valid_signature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $valid_signature = $this->base64url_encode($valid_signature);

        if ($signature != $valid_signature) return false;

        $payload_data = json_decode($this->base64url_decode($payload), true);

        // 만료 시간 검증
        if (isset($payload_data['exp']) && time() > $payload_data['exp']) {
            return false; // 토큰 만료됨
        }

        return $payload_data;
    }

    public function refresh($token, $customTtl = null)
    {
        // 기존 토큰 검증
        $payload = $this->verify($token);

        // 토큰이 유효하지 않으면 false 반환
        if (!$payload) return false;

        // 새로운 만료 시간 설정
        $payload['exp'] = time() + ($customTtl ?? $this->ttl);
        $payload['iat'] = time();
        $payload['refresh_count'] = isset($payload['refresh_count']) ? $payload['refresh_count'] + 1 : 1;

        // 새 토큰 생성
        return $this->generate($payload);
    }

    public function needsRefresh($token)
    {
        $payload = $this->getPayload($token);
        if (!$payload || !isset($payload['exp'])) {
            return false;
        }

        return ($payload['exp'] - time()) < $this->refresh_threshold;
    }

    public function getPayload($token)
    {

        // 토큰이 이미 배열인 경우 그대로 반환
        if (is_array($token)) {
            return $token;
        }

        // 문자열 형태의 JWT 토큰인 경우 디코딩
        if (is_string($token)) {
            $parts = explode('.', $token);
            if (count($parts) != 3) return false;

            return json_decode($this->base64url_decode($parts[1]), true);
        }

        // 토큰이 없거나 유효하지 않은 경우
        return [];
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function setCookie($token, $expiry = null)
    {
        echo "1111111111111111111<br>";
        if ($expiry === null) {
            echo "2222222222222222222222<br>";
            $payload = $this->getPayload($token);
            $expiry = $payload['exp'] ?? (time() + $this->ttl);
        }

        // 토큰이 배열이면 JSON으로 변환
        if (is_array($token)) {
            echo "3333333333333333333<br>";
            $token = json_encode($token);
        }

        setcookie(
            $this->cookie_name,
            $token,
            $expiry,
            '/',
            $this->cookie_domain,
            $this->secure,
            $this->http_only
        );
    }

    public function getTokenFromRequest($request)
    {
        return $request->cookie($this->cookie_name) ??
            $request->bearerToken() ??
            $request->header('Authorization');
    }


    public function getCookieName()
    {
        return $this->cookie_name;
    }
// 쿠키 도메인 가져오기
    public function getCookieDomain()
    {
        return $this->cookie_domain;
    }

// 토큰 TTL 가져오기
    public function getTtl()
    {
        return $this->ttl;
    }

// Secure 설정 가져오기
    public function isSecure()
    {
        return $this->secure;
    }

// HttpOnly 설정 가져오기
    public function isHttpOnly()
    {
        return $this->http_only;
    }

// 리프레시 임계값 가져오기
    public function getRefreshThreshold()
    {
        return $this->refresh_threshold;
    }

// 쿠키 제거하기
    public function removeCookie()
    {
        setcookie(
            $this->cookie_name,
            '',
            time() - 3600, // 과거 시간으로 설정하여 즉시 만료
            '/',
            $this->cookie_domain,
            $this->secure,
            $this->http_only
        );
    }

}
