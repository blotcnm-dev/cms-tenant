<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Services\EmailService;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
 * 크론탭에 저장
# crontab -e

/ 5 * * * * cd /var/www/myapp && php artisan emails:send
0 * * * * cd /var/www/myapp && php artisan emails:retry
0 0 * * * cd /var/www/myapp && php artisan emails:cleanup
*/



// 1. 이메일 발송
Artisan::command('emails:process {--limit=50} {--queue=default}', function () {
    $limit = $this->option('limit');
    $queue = $this->option('queue');

    $this->info("Processing {$limit} emails from {$queue} queue...");

    $emailService = app(EmailService::class);
    $processed = $emailService->processQueuedEmails($limit);

    $this->info("Processed: {$processed}");
    //$this->info("Remaining: " . $emailService->getQueuedCount());
})->describe('Process queued emails');

// 2. 상태 확인
Artisan::command('emails:status', function () {
    $stats = DB::table('bl_queued_email')
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->get();

    $this->table(['Status', 'Count'], $stats->map(function ($s) {
        return [$s->status, $s->count];
    }));
})->describe('Show email queue status');

// 3. 특정 이메일 재발송
Artisan::command('emails:resend {id}', function ($id) {
    $email = DB::table('bl_queued_email')->find($id);

    if (!$email) {
        $this->error("Email {$id} not found");
        return;
    }

    if ($this->confirm("Resend email to {$email->recipients}?")) {
        DB::table('bl_queued_email')
            ->where('id', $id)
            ->update(['status' => 'queued', 'attempts' => 0]);

        $this->info("Email queued for resending");
    }
})->describe('Resend specific email');

// 4. 대화형 이메일 발송
Artisan::command('emails:interactive', function () {
    $to = $this->ask('Recipient email?');
    $subject = $this->ask('Subject?');
    $message = $this->ask('Message?');

    if ($this->confirm('Send this email?')) {
        app(EmailService::class)->queueEmail(
            'emails.simple',
            ['content' => $message],
            $to,
            $subject
        );

        $this->info('Email queued!');
    }
})->describe('Send email interactively');
