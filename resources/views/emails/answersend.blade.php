<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
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
        .question-box {
            background-color: #f8f9fa;
            border: 2px dashed #6c757d;
            padding: 25px;
            margin: 30px 0;
            border-radius: 10px;
        }
        .question-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .question-content {
            color: #666;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .answer-box {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
        }
        .answer-title {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
        }
        .answer-content {
            color: #333;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .additional-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 5px;
            margin: 30px 0;
        }
        .contact-button {
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
        .contact-button:hover {
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
        .info-list {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .info-list li {
            margin: 8px 0;
            color: #666;
        }
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 30px 0;
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
        <h2 style="text-align: center; color: #333;">문의에 답변드립니다!</h2>

        <p style="text-align: center; color: #666; font-size: 16px;">
            소중한 문의사항에 대한 답변을 보내드립니다.<br>
            추가 문의사항이 있으시면 언제든지 연락주시기 바랍니다.
        </p>

        <!-- 질문 내용 -->
        <div class="question-box">
            <div class="question-title">
                <span style="color: #6c757d; margin-right: 10px;">Q.</span>
                {{ $question_subject }}
            </div>
            <div class="question-content">{!! $question_content !!}</div>
        </div>

        <div class="divider"></div>

        <!-- 답변 내용 -->
        <div class="answer-box">
            <div class="answer-title">
                <span style="margin-right: 10px;">A.</span>
                답변드립니다
            </div>
            <div class="answer-content">{!! $answer_content !!}</div>
        </div>

    </div>

    <!-- 푸터 -->
    <div class="footer">
        <p>이 메일은 1대1 문의 답변을 위해 자동 발송되었습니다.</p>
        <p style="margin-top: 20px; font-size: 12px;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</div>
</body>
</html>
