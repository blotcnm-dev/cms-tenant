<?php

namespace App\Http\Controllers;

use App\Exceptions\CodeException;
use App\Models\Designs\PopupContent;
use App\Models\Designs\Banner;
use App\Models\Promotions\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\File;


class MainController extends Controller
{
     public function index(Request $request)
    {
        return view('web.index');
    }

    public function explain_setup_back (Request $request)
    {
        // .env 파일 존재 확인
        if (file_exists(base_path('.env'))) {
            return redirect()->route('master.login');
        }

        $readmePath = base_path('README.md');

        if (!File::exists($readmePath)) {
            abort(404, 'README.md 파일을 찾을 수 없습니다.');
        }

        $content = File::get($readmePath);
        $escaped = htmlspecialchars($content);

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>설치 가이드</title>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Consolas', 'Monaco', monospace; margin: 20px; background: #f8f9fa; }
                .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                pre { white-space: pre-wrap; line-height: 1.6; font-size: 14px; }
                h1 { color: #333; text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>📖 설치 및 설정 가이드</h1>
                <pre>{$escaped}</pre>
            </div>
        </body>
        </html>";

        return response($html);
    }

}
?>
