<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

//use App\Models\Statistics\StatisticsJoin;
//use App\Models\Statistics\StatisticsVisit;

class Ckeditor5Controller extends Controller
{

    protected $sessionId;
    protected $configService;

    public function __construct()
    {
    }

    public function ckeditor(){
        return view('editortest');
    }

    public function upload(Request $request)
    {

        if ($request->hasFile('upload')) {
            $file = $request->file('upload');

            // 파일 유효성 검사
            $validator = Validator::make($request->all(), [
                'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => [
                        'message' => $validator->errors()->first('upload')
                    ]
                ], 400);
            }

            // 파일 저장
            $fileName = time() . '_' . $file->getClientOriginalName();

            $time = time();
            $yearMonth = date('Ym', $time);

            $hash = md5($fileName . time()); // 파일명과 시간을 조합
            $level1 = substr($hash, 0, 2);
            $level2 = substr($hash, 2, 2);


            $filePath = $file->storeAs(
                'editor/' . $yearMonth . '/' . $level1 . '/' . $level2,
                $fileName,
                'public'
            );

            // 성공 응답
            return response()->json([
                'url' => asset('storage/' . $filePath)
            ]);
        }

        return response()->json([
            'error' => [
                'message' => '파일이 업로드되지 않았습니다.'
            ]
        ], 400);
    }

}
