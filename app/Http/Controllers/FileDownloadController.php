<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileDownloadController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'filename' => 'required|string'
        ]);

        $path = $request->get('path');
        $filename = $request->get('filename');

        // path에서 /storage/ 제거
        $cleanPath = str_replace('/storage/', '', $path);

        // storage/app/public에서 파일 찾기
        $fullPath = storage_path('app/public/' . $cleanPath);

        if (!file_exists($fullPath)) {
            // public/storage에서도 찾아보기
            $fullPath = public_path('storage/' . $cleanPath);
        }

        if (!file_exists($fullPath)) {
            abort(404, '파일을 찾을 수 없습니다: ' . $filename);
        }

        return response()->download($fullPath, $filename);
    }
}
