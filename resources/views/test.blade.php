<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Nginx + PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .info-box {
            background-color: #f9f9f9;
            border-left: 5px solid #009879;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            margin-top: 0;
            color: #009879;
        }
        .server-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        .server-info dt {
            font-weight: bold;
            color: #555;
        }
        .server-info dd {
            margin: 0;
            color: #777;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #999;
        }
        .test-functions {
            margin-top: 30px;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .test-functions h3 {
            color: #666;
            margin-bottom: 15px;
        }
        .test-functions ul {
            list-style-type: none;
            padding: 0;
        }
        .test-functions li {
            background-color: white;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }



        .select-container {
            position: relative;
            display: inline-block;
            width: 200px;
        }

        select {
            width: 200px;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            background-color: white;
            color: #333;
            appearance: none; /* 브라우저 기본 스타일 제거 */
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('https://upload.wikimedia.org/wikipedia/commons/9/9d/Caret_down_font_awesome_whitevariation.svg');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }

        /* IE에서 기본 화살표 제거 */
        select::-ms-expand {
            display: none;
        }

    </style>
</head>
<body>

<div class="select-container">
    <select>
        <option value="apple">사과</option>
        <option value="banana">바나나</option>
        <option value="cherry">체리</option>
        <option value="grape">포도</option>
    </select>
</div>


<?php


echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "=================================<br>";

function generate_jwt($payload, $secret_key) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = base64url_encode($header);
    $payload = base64url_encode(json_encode($payload));

    $signature = hash_hmac('sha256', $header . "." . $payload, $secret_key, true);
    $signature =  base64url_encode($signature);

    return $header . "." . $payload . "." . $signature;
}

function verify_jwt($token, $secret_key) {
    $parts = explode('.', $token);
    if (count($parts) != 3) return false;

    list($header, $payload, $signature) = $parts;

    $valid_signature = hash_hmac('sha256', $header . "." . $payload, $secret_key, true);
    $valid_signature = base64url_encode($valid_signature);

    if ($signature != $valid_signature) return false;

    $payload_data = json_decode(base64url_decode($payload), true);

    // 만료 시간 검증
    if (isset($payload_data['exp']) && time() > $payload_data['exp']) {
        return false; // 토큰 만료됨
    }

    return $payload_data;
}

function refresh_jwt($token, $secret_key, $new_expiry) {
    // 기존 토큰 검증
    $payload = verify_jwt($token, $secret_key);

    // 토큰이 유효하지 않으면 false 반환
    if (!$payload) return false;

    // 새로운 만료 시간 설정
    $payload['exp'] = $new_expiry;

    // 새 토큰 생성
    return generate_jwt($payload, $secret_key);
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

echo "사용 중인 php.ini 파일 경로: " . php_ini_loaded_file() . "<br>";
echo "현재 PHP 시간대: " . date_default_timezone_get() . "<br>";
echo "현재 시간: " . date('Y-m-d H:i:s') . "<br>";
echo "현재 타임스탬프: " . time() . "<br>";
?>
</body>
<script src="/JwtManager.js"></script>
<script>
    // 페이지 로드 시 시작
    const myJwtManager = new JwtManager();
</script>
</html>
