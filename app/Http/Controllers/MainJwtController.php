<?php

namespace App\Http\Controllers;

use App\Exceptions\CodeException;
use App\Models\Designs\PopupContent;
use App\Models\Designs\Banner;
use App\Models\Promotions\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class MainJwtController extends Controller
{
    private function generate_jwt($payload, $secret_key) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = $this->base64url_encode($header);
        $payload = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $header . "." . $payload, $secret_key, true);
        $signature = $this->base64url_encode($signature);

        return $header . "." . $payload . "." . $signature;
    }

    private function verify_jwt($token, $secret_key) {
        $parts = explode('.', $token);
        if (count($parts) != 3) return false;

        list($header, $payload, $signature) = $parts;

        $valid_signature = hash_hmac('sha256', $header . "." . $payload, $secret_key, true);
        $valid_signature = $this->base64url_encode($valid_signature);

        if ($signature != $valid_signature) return false;

        $payload_data = json_decode($this->base64url_decode($payload), true);

        // 만료 시간 검증
        if (isset($payload_data['exp']) && time() > $payload_data['exp']) {
            return false; // 토큰 만료됨
        }

        return $payload_data;
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    private function refresh_jwt($token, $secret_key, $new_expiry) {
        // 기존 토큰 검증
        $payload = $this->verify_jwt($token, $secret_key);

        // 토큰이 유효하지 않으면 false 반환
        if (!$payload) return false;

        // 새로운 만료 시간 설정
        $payload['exp'] = $new_expiry;

        // 새 토큰 생성
        return $this->generate_jwt($payload, $secret_key);
    }


    public function checkAndRefreshToken(Request $request)
    {
        $jwt_secret = 'a9f3b2c8d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1';
        $cookie_domain = '.blot-i.co.kr';

        // 쿠키에서 JWT 토큰 가져오기
        $token = $request->cookie('jwt_token');

        if (!$token) {
            return response()->json(['error' => '토큰이 없습니다'], 401);
        }

        // 토큰 검증
        $payload = $this->verify_jwt($token, $jwt_secret);

        // 토큰이 유효하지 않은 경우
        if (!$payload) {
            return response()->json(['error' => '유효하지 않은 토큰입니다'], 401);
        }

        // 토큰 만료 시간 확인 (예: 만료 1시간 전에 갱신)
        $refresh_threshold = 3600; // 1시간 (초 단위)
        $current_time = time();

        if (isset($payload['exp']) && ($payload['exp'] - $current_time) < $refresh_threshold) {
            // 토큰 갱신 필요
            $new_exp = $current_time + (60 * 60 * 24); // 새로운 만료 시간 (24시간)
            $new_payload = [
                'user_id' => $payload['user_id'],
                'username' => $payload['username'],
                'exp' => $new_exp,
                'iat' => $current_time,
                'refresh_count' => isset($payload['refresh_count']) ? $payload['refresh_count'] + 1 : 1
            ];

            // 새 토큰 생성
            $new_token = $this->generate_jwt($new_payload, $jwt_secret);

            // 쿠키 갱신
            setcookie('jwt_token', $new_token, $new_exp, '/', $cookie_domain, true, true);

            return response()->json([
                'message' => '토큰이 갱신되었습니다',
                'token_refreshed' => true,
                'new_exp' => date('Y-m-d H:i:s', $new_exp)
            ]);
        }

        // 토큰이 아직 유효한 경우
        return response()->json([
            'message' => '토큰이 유효합니다',
            'token_refreshed' => false,
            'exp' => date('Y-m-d H:i:s', $payload['exp'])
        ]);
    }


    public function index(Request $request)
    {
        $jwt_secret = 'a9f3b2c8d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1';
        $payload = [
            'user_id' => "blot",
            'username' => "변혜선",
            'exp' => time() + (60 * 60 * 24), // 24시간
            'iat' => time()
        ];

        $jwt = $this->generate_jwt($payload, $jwt_secret);

        // 서브도메인 간 공유를 위한 쿠키 설정
        setcookie('jwt_token', $jwt, time() + (60 * 60 * 24), '/', '.blot-i.co.kr', true, true);

        return view('test');
    }
}
?>
