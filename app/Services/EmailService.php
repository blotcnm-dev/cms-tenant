<?php

namespace App\Services;

use App\Models\QueuedEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class EmailService
{

    /**
     * 이메일을 큐에 추가합니다.
     *
     * @param string $view 렌더링할 뷰 경로
     * @param array $viewData 뷰에 전달할 데이터
     * @param array|string $recipients 수신자 이메일 또는 이메일 배열
     * @param string $fromEmail 발신자 이메일
     * @param string $fromName 발신자 명
     * @param array $attachments 첨부파일 배열
     * @return QueuedEmail
     */

    public function queueEmail(string $view, array $viewData, $recipients, string $fromEmail = null, string $fromName = null, array $attachments = [])
    {
        try {
            // 뷰 존재 여부 확인
            if (!View::exists($view)) {
                throw new Exception("이메일 템플릿을 찾을 수 없습니다: {$view}");
            }

            $recipientsArray = is_array($recipients) ? $recipients : [$recipients];

            if (empty($recipientsArray)) {
                throw new Exception('수신자 정보가 없습니다.');
            }
            // 각 이메일 검증
            foreach ($recipientsArray as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("유효하지 않은 이메일 주소: {$email}");
                }
            }
            // 첨부파일 검증
            $validatedAttachments = [];
            foreach ($attachments as $attachment) {
                $attachmentPath = '';
                $attachmentName = '';

                // 배열로 전달된 경우
                if (is_array($attachment)) {
                    $attachmentPath = $attachment['path'] ?? '';
                    $attachmentName = $attachment['name'] ?? '';
                }
                // 문자열로 전달된 경우
                else if (is_string($attachment)) {
                    $attachmentPath = $attachment;
                    $attachmentName = basename($attachment);
                }

                // 경로에서 공백 제거
                $attachmentPath = str_replace(' ', '', $attachmentPath);

                // 실제 파일 경로 찾기
                $realPath = $this->getRealFilePath($attachmentPath);

                if (!$realPath || !file_exists($realPath)) {
                    Log::warning("첨부파일을 찾을 수 없습니다", [
                        'original_path' => $attachmentPath,
                        'real_path' => $realPath
                    ]);
                    continue; // 파일이 없어도 메일은 전송되도록 continue 사용
                }

                $validatedAttachments[] = [
                    'path' => $realPath,
                    'name' => $attachmentName ?: basename($realPath),
                    'mime' => $this->getMimeType($realPath)
                ];
            }

            // 뷰 렌더링하여 내용 생성
            $content = View::make($view, $viewData)->render();

            // 발신자 정보 처리
            $fromEmail = $fromEmail ?: config('mail.from.address');
            $fromName = $fromName ?: config('mail.from.name');

            $insertedId = DB::table('bl_queued_email')->insertGetId([
                'recipients' => json_encode($recipientsArray),
                'subject' => $viewData['subject'],
                'content' => $content,
                'attachments' => json_encode($validatedAttachments),
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'status' => 'queued',
                'attempts' => 0
            ]);

            return (bool) $insertedId;

        } catch (Exception $e) {

            Log::error('이메일 큐 추가 실패', [
                'view' => $view,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * 대기 중인 이메일을 처리합니다.
     *
     * @param int $limit 한 번에 처리할 최대 이메일 수
     * @return int 처리된 이메일 수
     */
    public function processQueuedEmails(int $limit = 50): int
    {
        $count = 0;

        $emails = DB::table('bl_queued_email')
            ->where('status', 'queued')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();


        foreach ($emails as $email) {
            $this->sendEmail($email);
            $count++;
        }
        return $count;
    }

    /**
     * 큐에 있는 이메일 하나를 전송합니다.
     *
     * @param QueuedEmail $queuedEmail
     * @return bool
     */
    protected function sendEmail($queuedEmail): bool
    {
        try {

            $recipients = is_string($queuedEmail->recipients)
                ? json_decode($queuedEmail->recipients, true)
                : $queuedEmail->recipients;

            $attachments = is_string($queuedEmail->attachments)
                ? json_decode($queuedEmail->attachments, true)
                : ($queuedEmail->attachments ?? []);

            foreach ($recipients as $recipient) {

                Mail::html($queuedEmail->content, function ($message) use ($recipient, $queuedEmail, $attachments) {
                    $message->to($recipient)
                        ->subject($queuedEmail->subject)
                        ->from($queuedEmail->from_email, $queuedEmail->from_name);

                    // 첨부파일 추가
                    foreach ($attachments as $attachment) {
                        if (file_exists($attachment['path'])) {
                            $message->attach($attachment['path'], [
                                'as' => $attachment['name'],
                                'mime' => $attachment['mime']
                            ]);
                        } else {
                            Log::warning('첨부파일을 찾을 수 없습니다', [
                                'email_id' => $queuedEmail->id,
                                'attachment' => $attachment['path']
                            ]);
                        }
                    }

                });
            }

            DB::table('bl_queued_email')
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

            return true;

        } catch (Exception $e) {

            DB::table('bl_queued_email')
                ->where('id', $queuedEmail->id)
                ->increment('attempts', 1, [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);
            return false;
        }
    }


    /**
     * 웹 경로를 실제 파일 시스템 경로로 변환합니다.
     *
     * @param string $webPath
     * @return string|false
     */
    protected function getRealFilePath($webPath)
    {
        // /storage로 시작하는 경우
        if (strpos($webPath, '/storage/') === 0) {
            $relativePath = str_replace('/storage/', '', $webPath);

            // Storage::disk('public')를 사용해서 실제 경로 얻기
            if (Storage::disk('public')->exists($relativePath)) {
                return Storage::disk('public')->path($relativePath);
            }

            // storage/app/public 경로 시도
            $storagePath = storage_path('app/public/' . $relativePath);
            if (file_exists($storagePath)) {
                return $storagePath;
            }

            // public/storage 경로 시도 (심볼릭 링크)
            $publicPath = public_path($webPath);
            if (file_exists($publicPath)) {
                return $publicPath;
            }
        }

        // 절대 경로로 시도
        if (file_exists($webPath)) {
            return $webPath;
        }

        return false;
    }




    /**
     * 파일의 MIME 타입을 안전하게 가져옵니다.
     *
     * @param string $filePath
     * @return string
     */
    protected function getMimeType($filePath)
    {
        try {
            if (file_exists($filePath)) {
                return mime_content_type($filePath);
            }
        } catch (\Exception $e) {
            // 오류 발생 시 기본값 반환
        }

        // 확장자 기반으로 MIME 타입 추측
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip'
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
