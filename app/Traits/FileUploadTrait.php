<?php
namespace App\Traits;

use Exception;
use Intervention\Image\File;
//use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUploadTrait
{
    /**
     * 이미지 파일 업로드
     *
     * @param array $files
     * @param string $uploadPath
     * @param array $thumbArr
     * @return array
     */
    public function fileUploads(array $files, string $uploadPath, array $thumbArr = [], string $storage = null, array $resizeInfo = []) : array
    {
        try
        {
            $i = 0;
            $filePath = [];
            $storage = $storage ?? env('STORAGE_DRIVER');

            $dirChk = Storage::disk($storage)->exists($uploadPath);

            if(!$dirChk) {
                Storage::disk($storage)->makeDirectory($uploadPath);
            }

            foreach($files as $key => $file) {
                if(!empty($resizeInfo)) { //리사이즈 해야하는 경우
                    $image = Image::make($file)->resize($resizeInfo[0], $resizeInfo[1], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode(explode('/', $file->getMimeType())[1], 60);

                    Storage::disk($storage)->put($uploadPath.'/'.$resizeInfo[2].$file->hashName(), $image->stream());

                    $filePath[$i] = $uploadPath.'/'.$resizeInfo[2].$file->hashName();
                } else {
                    $filePath[$i] = Storage::disk($storage)->put($uploadPath, $file);
                }

                if(count($thumbArr) > 0) {
                    for($j = 0; $j < count($thumbArr); $j++) {
                        $thumbNailDir = $uploadPath.'/'.$thumbArr[$j];

                        $dirChk = Storage::disk($storage)->exists($thumbNailDir);

                        if(!$dirChk) {
                            Storage::disk($storage)->makeDirectory($thumbNailDir);
                        }

                        $image = Image::make($file)->resize($thumbArr[$j], null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })->encode(explode('/', $file->getMimeType())[1], 60);

                        Storage::disk($storage)->put($uploadPath.'/'.$thumbArr[$j].'/'.$file->hashName(), $image->stream());
                    }
                }

                if(!Storage::disk($storage)->exists($filePath[$i])) {
                    throw new Exception("파일 업로드 실패.");
                }

                $i++;
            }

            $response = [
                'success' => true,
                'filePath' => $filePath
            ];
        }
        catch (Exception $e)
        {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * 이미지 파일 삭제
     * @param string $deletePath
     * @return array|bool[]
     */
    public function fileDelete(string $deletePath, string $storage = null) : array|bool
    {
        try
        {
            $storage = $storage ?? env('STORAGE_DRIVER');
            $fileCheck = $dirChk = Storage::disk($storage)->exists($deletePath);

            if($fileCheck){//파일존재할경우에만 삭제
                $fileDelete = Storage::disk($storage)->delete($deletePath);

                if(!$fileDelete) {
                    throw new Exception("파일 삭제 실패.");
                }
            }else{
                throw new Exception("파일이 존재하지 않습니다.");
            }
            $response = [
                'success' => true
            ];
        }
        catch (Exception $e)
        {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }


    /**
     * 이미지 파일 복사
     * @param string $copyPath
     * @param string|null $uploadPath
     * @return array
     */
    public function fileCopy(string $copyPath, string $uploadPath = null, string $storage = null)
    {
        try
        {
            $storage = $storage ?? env('STORAGE_DRIVER');
            $fileCheck = Storage::disk($storage)->exists($copyPath);

            if($fileCheck){//파일존재할경우에만
                $fileInfo = pathinfo($copyPath);
                $dirname = $fileInfo['dirname'];
                $extension = $fileInfo['extension'];

                $fileName = Str::random(40).'.'.$extension;

                //uploadPath 존재 하지 않을경우 동일 폴더에 복사
                if(empty($uploadPath)){
                    $filePath = $dirname.'/'.$fileName;
                }else{
                    $dirChk = Storage::disk($storage)->exists($uploadPath);
                    if(!$dirChk) {
                        Storage::disk($storage)->makeDirectory($uploadPath);
                    }
                    $filePath = $uploadPath.'/'.$fileName;
                }

                $fileCopyResult = Storage::disk($storage)->copy($copyPath, $filePath);

                if(!$fileCopyResult) {
                    throw new Exception("파일 복사 실패.");
                }
            }else{
                throw new Exception("파일이 존재하지 않습니다.");
            }
            $response = [
                'success' => true,
                'filePath' => $filePath
            ];
        }
        catch (Exception $e)
        {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @brief URL 파일 업로드
     * @param array $files
     * @param string $uploadPath
     * @param string|null $storage
     * @param array $resizeInfo
     * @return array
     */
    public function urlFileUploads(array $files, string $uploadPath, string $storage = null, array $resizeInfo = []) : array
    {
        try
        {
            $i = 0;
            $filePath = [];
            $storage = $storage ?? env('STORAGE_DRIVER');

            $dirChk = Storage::disk($storage)->exists($uploadPath);

            if(!$dirChk) {
                Storage::disk($storage)->makeDirectory($uploadPath);
            }

            foreach($files as $file) {
                $fileName = basename($file);
                $getFile = file_get_contents($file);

                if(!empty($resizeInfo)) { //리사이즈 해야하는 경우

                    $image = Image::make($getFile)->resize($resizeInfo[0], $resizeInfo[1], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $reImage = $resizeInfo[0]."x".$resizeInfo[1]."_".$fileName;

                    if(Storage::disk($storage)->exists($uploadPath.'/'.$reImage)) {
                        Storage::disk($storage)->put($uploadPath.'/'.$reImage, $image->stream());
                    }
                    $filePath[$i] = $uploadPath.'/'.$reImage;
                } else {
                    Storage::disk($storage)->put($uploadPath.'/'.$fileName,$file);
                    $filePath[$i] = $uploadPath.'/'.$fileName;
                }


                if(!Storage::disk($storage)->exists($filePath[$i])) {
                    throw new Exception("파일 업로드 실패.");
                }

                $i++;
            }

            $response = [
                'success' => true,
                'filePath' => $filePath
            ];
        }
        catch (Exception $e)
        {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getFile()
            ];
        }

        return $response;
    }
}
