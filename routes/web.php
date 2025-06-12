<?php

use Illuminate\Support\Facades\Route;
/*
 * 메인페이지
 */
Route::get('/',                [\App\Http\Controllers\MainController::class, 'index'])->name('index');
/*
* 테스트 메인페이지
*/
Route::get('/member_hash_test',                [\App\Http\Controllers\Member\MemberController::class, 'test']);

/*
 * 게시판 관리
 */
Route::group(['where' => ['board_id' => '[0-9]{10}', 'post_id' => '[0-9]+']], function () {
    Route::get('{board_id}/{post_id}/edit', [\App\Http\Controllers\Boards\BoardController::class, 'edit'])          ->name('boards.edit');
    Route::get('{board_id}/{post_id}',      [\App\Http\Controllers\Boards\BoardController::class, 'show'])          ->name('boards.show');
    Route::get('{board_id}',                [\App\Http\Controllers\Boards\BoardController::class, 'list'])          ->name('boards.list');
    Route::get('{board_id}/create',         [\App\Http\Controllers\Boards\BoardController::class, 'create'])        ->name('boards.create');
    Route::post('{board_id}',               [\App\Http\Controllers\Boards\BoardController::class, 'store'])         ->name('boards.store');
    Route::put('{board_id}',      [\App\Http\Controllers\Boards\BoardController::class, 'update'])        ->name('boards.update');
    Route::delete('{board_id}',             [\App\Http\Controllers\Boards\BoardController::class, 'destroy'])       ->name('boards.destroy');
    Route::post('{board_id}/repliesstore/{post_id}', [\App\Http\Controllers\Boards\BoardController::class, 'repliesstore'])->name('boards.repliesstore');
    Route::get('{board_id}/replieslist/{post_id}', [\App\Http\Controllers\Boards\BoardController::class, 'replieslist_ajax'])->name('boards.replieslist_ajax');
    Route::delete('{board_id}/reply/{commentId}', [\App\Http\Controllers\Boards\BoardController::class, 'replyDestroy'])->name('boards.replyDestroy');
    Route::post('/boards/{boardId}/likes',  [\App\Http\Controllers\Boards\BoardController::class, 'likes_ajax'])    ->name('boards.likes');
});

/*
 * 사용자 로그인 페이지
 */
Route::get('/login',                [\App\Http\Controllers\Member\LoginController::class, 'front_login'])           ->name('login');
Route::post('/loginProc',           [\App\Http\Controllers\Member\LoginController::class, 'front_loginProc'])       ->name('loginProc');
Route::get('/logout',               [\App\Http\Controllers\Member\LoginController::class, 'front_logout'])          ->name('logout');

Route::get('/member_create',        [\App\Http\Controllers\Member\MemberController::class, 'front_create'])         ->name('front_member.create');
Route::post('/member_store',        [\App\Http\Controllers\Member\MemberController::class, 'store'])                ->name('front_member.store');

Route::get('/member_update/{id}',   [\App\Http\Controllers\Member\MemberController::class, 'show'])                 ->name('front_member.show');
Route::put('/member_update/{id}',   [\App\Http\Controllers\Member\MemberController::class, 'update'])               ->name('front_member.update');
/*
 * FAQ관리
 */
Route::prefix('faq')->group(function (): void {
    Route::get('',                  [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'front_list'])           ->name('front_faq.index');
});

/*
* 1:1문의 관리
*/
Route::prefix('inquiry')->group(function (): void {
    Route::get('',                  [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'front_list'])          ->name('front_inquiry.index');
    Route::get('/create',           [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'front_create'])         ->name('front_inquiry.create');
    Route::post('/store',           [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'front_store'])         ->name('front_inquiry.store');
    Route::get('/view/{id}',        [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'front_view'])           ->name('front_inquiry.view');
});


/*
* 서비스이용약관
*/
Route::get('/policy/{version?}',                  [\App\Http\Controllers\MainPolicyController::class, 'front_policy'])          ->name('front_policy.index');

/*
* 개인정보 처리방침
*/
Route::get('/privacy/{version?}',                  [\App\Http\Controllers\MainPolicyController::class, 'front_privacy'])          ->name('front_privacy.index');

/*
* 파일 다운로드
*/
Route::get('/download', [\App\Http\Controllers\FileDownloadController::class, 'download'])->name('file.download');

/*
 * JWT token 테스트
 */
Route::get('/test', [\App\Http\Controllers\MainJwtController::class, 'index' ]);


Route::get('ckeditor', [\App\Http\Controllers\Ckeditor5Controller::class, 'ckeditor']);
Route::post('ckeditor/upload', [\App\Http\Controllers\Ckeditor5Controller::class, 'upload'])->name('ckeditor.upload');

Route::prefix('master')->group(function() : void {
    require __DIR__ . '/webMaster.php';
});
