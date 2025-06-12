<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호가 초기화되었습니다</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans KR', Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #ffffff;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
        }
        .icon-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .icon {
            width: 80px;
            height: 80px;
        }
        .password-box {
            background-color: #f8f9fa;
            border: 2px dashed #28a745;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
            border-radius: 10px;
        }
        .temp-password {
            font-size: 28px;
            font-weight: bold;
            color: #dc3545;
            letter-spacing: 2px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
        }
        .info-list {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 5px;
            margin: 30px 0;
        }
        .login-button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #007bff;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
        }
        .login-button:hover {
            background-color: #0056b3;
        }
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        .footer a {
            color: #ffffff;
            text-decoration: underline;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .step-number {
            background-color: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- 헤더 -->
    <div class="header">
        <img src="{{ asset('src/assets/icons/logo@x3.png') }}" alt="회사 로고">
    </div>

    <!-- 콘텐츠 -->
    <div class="content">
        <h2 style="text-align: center; color: #333;">{{ $user_name }}님, 안녕하세요!</h2>

        <p style="text-align: center; color: #666; font-size: 16px;">
            요청하신 비밀번호 초기화가 완료되었습니다.<br>
            아래의 비밀번호로 로그인하세요.<br>
            만약 비밀번호 초기화를 요청하지 않으셨다면,<br>
            즉시 고객센터로 연락해주시기 바랍니다.
        </p>

        <div class="password-box">
            <p style="margin: 0; color: #666;">임시 비밀번호</p>
            <div class="temp-password">{{ $password }}</div>
            <p style="margin: 0; color: #999; font-size: 14px;">
                * 복사하여 사용하세요
            </p>
        </div>

        <div class="info-list">
            <h3 style="margin-top: 0; color: #007bff;">초기화 정보</h3>
            <ul style="list-style: none; padding: 0;">
                <li><strong>이메일:</strong> {{ $user_email }}</li>
                <li><strong>초기화 일시:</strong> {{ $reset_time }}</li>
            </ul>
        </div>
    </div>

    <!-- 푸터 -->
    <div class="footer">
        <p>이 메일은 보안을 위해 자동 발송되었습니다.</p>
        <p style="margin-top: 20px; font-size: 12px;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            이 이메일은 {{  $user_email }}로 발송되었습니다.
        </p>
    </div>
</div>
</body>
</html>
