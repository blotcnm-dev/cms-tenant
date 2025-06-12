<?php
namespace App\Services;

class FileDownloader
{
    public function download($path, $filename)
    {
        // path가 이미 완전한 파일 경로인지 확인
        $pathExtension = pathinfo($path, PATHINFO_EXTENSION);
        $isFullPath = !empty($pathExtension);

        if ($isFullPath) {
            // path가 이미 전체 파일 경로인 경우
            $filePath = $this->findActualFile($path);
            $downloadName = $filename; // 사용자에게 보여줄 원본 파일명
        } else {
            // path가 디렉토리인 경우
            $fullPath = rtrim($path, '/') . '/' . $filename;
            $filePath = $this->findActualFile($fullPath);
            $downloadName = $filename;
        }

        if (!$filePath) {
            abort(404, '파일을 찾을 수 없습니다: ' . $filename);
        }

        return response()->download($filePath, $downloadName);
    }

    private function findActualFile($path)
    {
        // /storage/ 접두사 제거
        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8); // 'storage/' 제거
        }

        // 가능한 파일 위치들
        $possiblePaths = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
        ];

        foreach ($possiblePaths as $testPath) {
            if (file_exists($testPath) && is_file($testPath)) {
                return $testPath;
            }
        }

        return null;
    }
}
