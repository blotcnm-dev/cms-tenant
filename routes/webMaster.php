<?php
/*
 * 관리자 로그인 페이지
 */
Route::get('/login',                [\App\Http\Controllers\Admins\AdminLoginController::class, 'index'])->name('master.login');
Route::post('/loginProc',           [\App\Http\Controllers\Admins\AdminLoginController::class, 'loginProc'])->name('master.loginProc');
Route::get('/logout',               [\App\Http\Controllers\Admins\AdminLoginController::class, 'logout'])->name('master.logout');
//임시사용
Route::get('/dbinfo',               [\App\Http\Controllers\Admins\AdminLoginController::class, 'dbinfo']);

Route::get('/permissionError',      [\App\Http\Controllers\Admins\AdminUserErrorController::class, 'permission'])->name('master.permissionError');

Route::get('/processemailqueue',   [\App\Services\EmailService::class, 'processQueuedEmails']);


/*
 * 슈퍼 관리자 패키지
 * 입점사 관리 및 다이렉트 로그인
 */
Route::get('/provider',             [\App\Http\Controllers\SuperAdmins\SuperAdminController::class, 'index'])       ->name('provider.list');
Route::get('/provider/create',      [\App\Http\Controllers\SuperAdmins\SuperAdminController::class, 'create'])      ->name('provider.create');
Route::post('/provider/store',      [\App\Http\Controllers\SuperAdmins\SuperAdminController::class, 'store'])       ->name('provider.store');

Route::get('/provider/{id}',        [\App\Http\Controllers\SuperAdmins\SuperAdminController::class, 'show'])        ->name('provider.show');
Route::put('/provider/{id}',        [\App\Http\Controllers\SuperAdmins\SuperAdminController::class, 'update'])      ->name('provider.update');

Route::get('/directLogin/{id}',     [\App\Http\Controllers\SuperAdmins\DirectLoginController::class, 'directLogin'])->name('superadmin.directlogin');

/*
 * 입점사 슈퍼관리자 로그인
 */
Route::get('/handleDirectLogin',    [\App\Http\Controllers\Admins\DirectLoginController::class, 'handleDirectLogin'])->name('provider.directlogin');



Route::get('', [\App\Http\Controllers\Admins\AdminMainController::class, 'index'])                              ->name('master.dashboard');

//Route::middleware(['admin', 'permission', 'admin.menus'])->group(function() {
Route::middleware(['admin', 'permission' ])->group(function() {
    /*
     * 대시보드
     */

    /*
     * 사이트 설정 페이지
     */
    Route::prefix('site')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminSettingController::class, 'index'])          ->name('site.index');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminSettingController::class, 'settingStore'])   ->name('site.store');
        Route::post('/favicon_del',     [\App\Http\Controllers\Admins\AdminSettingController::class, 'deleteFavicon'])  ->name('site.favicon.delete');
    });

    /*
     * 관리자 권한 설정 페이지
     */
    Route::prefix('auth')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminGradeController::class, 'auth_index'])       ->name('auth.index');
        Route::post('/',                [\App\Http\Controllers\Admins\AdminGradeController::class, 'updatePermissions']) ->name('auth.update');
        Route::get('/{gradeId}',        [\App\Http\Controllers\Admins\AdminGradeController::class, 'getPermissions'])   ->name('auth.getPermissions');
    });

    /*
     * 회원 가입 관리 설정 페이지
     */
    Route::prefix('user')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminSettingController::class, 'user_index'])     ->name('user.index');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminSettingController::class, 'user_store'])     ->name('user.store');
        Route::delete('/etc_del',       [\App\Http\Controllers\Admins\AdminSettingController::class, 'user_etc_del'])   ->name('user.etc_del');
    });

    /*
     * 사용자 관리 페이지
     */
    Route::prefix('member')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminMemberController::class, 'index'])           ->name('member.list');
        Route::get('/create',           [\App\Http\Controllers\Admins\AdminMemberController::class, 'create'])          ->name('member.create');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminMemberController::class, 'store'])           ->name('member.store');

        Route::get('/downloadexcel',    [\App\Http\Controllers\Admins\AdminMemberController::class, 'downloadExcel'])   ->name('member.download.excel');

        Route::get('/{id}',             [\App\Http\Controllers\Admins\AdminMemberController::class, 'show'])            ->name('member.show');
        Route::put('/{id}',             [\App\Http\Controllers\Admins\AdminMemberController::class, 'update'])          ->name('member.update');
        Route::delete('/{id}',          [\App\Http\Controllers\Admins\AdminMemberController::class, 'destroy'])         ->name('member.destroy');

        Route::delete('/{id}/profile_del',  [\App\Http\Controllers\Admins\AdminMemberController::class, 'deleteProfileImage']) ->name('member.profile.delete');
        Route::post('/{id}/withdraw',       [\App\Http\Controllers\Admins\AdminMemberController::class, 'withdraw'])           ->name('member.withdraw');
        Route::post('/{id}/reset_password', [\App\Http\Controllers\Admins\AdminMemberController::class, 'resetPassword'])      ->name('member.reset.password');
    });

    /*
     * 약관관리
     */
    Route::prefix('policy')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminPolicyController::class, 'index'])           ->name('policy.index');
        Route::get('/create',           [\App\Http\Controllers\Admins\AdminPolicyController::class, 'create'])          ->name('policy.create');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminPolicyController::class, 'store'])           ->name('policy.store');

        Route::get('/downloadexcel',    [\App\Http\Controllers\Admins\AdminPolicyController::class, 'downloadExcel'])   ->name('policy.download.excel');

        Route::get('/{id}',             [\App\Http\Controllers\Admins\AdminPolicyController::class, 'show'])            ->name('policy.show');
        Route::get('/{id}/history',     [\App\Http\Controllers\Admins\AdminPolicyController::class, 'show_history'])    ->name('policy.show_history');
        Route::put('/{id}',             [\App\Http\Controllers\Admins\AdminPolicyController::class, 'update'])          ->name('policy.update');
        Route::put('/{id}/versionup',   [\App\Http\Controllers\Admins\AdminPolicyController::class, 'versionup'])       ->name('policy.versionup');
        Route::delete('/{id}',          [\App\Http\Controllers\Admins\AdminPolicyController::class, 'destroy'])         ->name('policy.destroy');
    });


    /*
     * 배너관리
     */
    Route::prefix('banner')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminBannerController::class, 'index'])           ->name('banner.index');
        Route::get('/create',           [\App\Http\Controllers\Admins\AdminBannerController::class, 'create'])          ->name('banner.create');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminBannerController::class, 'store'])           ->name('banner.store');
        Route::get('/downloadexcel',    [\App\Http\Controllers\Admins\AdminBannerController::class, 'downloadExcel'])   ->name('banner.download.excel');
        Route::get('/{id}',             [\App\Http\Controllers\Admins\AdminBannerController::class, 'show'])            ->name('banner.show');
        Route::put('/{id}',             [\App\Http\Controllers\Admins\AdminBannerController::class, 'update'])          ->name('banner.update');
        Route::delete('/{id}',          [\App\Http\Controllers\Admins\AdminBannerController::class, 'destroy'])         ->name('banner.destroy');
    });

    /*
     * 팝업관리
     */
    Route::prefix('popup')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminPopupController::class, 'index'])           ->name('popup.index');
        Route::get('/create',           [\App\Http\Controllers\Admins\AdminPopupController::class, 'create'])          ->name('popup.create');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminPopupController::class, 'store'])           ->name('popup.store');
        Route::get('/downloadexcel',    [\App\Http\Controllers\Admins\AdminPopupController::class, 'downloadExcel'])   ->name('popup.download.excel');
        Route::get('/{id}',             [\App\Http\Controllers\Admins\AdminPopupController::class, 'show'])            ->name('popup.show');
        Route::put('/{id}',             [\App\Http\Controllers\Admins\AdminPopupController::class, 'update'])          ->name('popup.update');
        Route::delete('/{id}',          [\App\Http\Controllers\Admins\AdminPopupController::class, 'destroy'])         ->name('popup.destroy');
    });

    /*
     * 사이트 메뉴 관리
     */
    Route::prefix('menus')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminMenuController::class, 'index'])             ->name('menus.index');
        Route::post('',                 [\App\Http\Controllers\Admins\AdminMenuController::class, 'store'])             ->name('menus.store');
        Route::get('/data',             [\App\Http\Controllers\Admins\AdminMenuController::class, 'getData'])           ->name('menus.data');
    });

    /*
     * 등급 명칭 설정
     */
    Route::prefix('grade')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\AdminGradeController::class, 'index'])            ->name('grade.index');
        Route::post('update',           [\App\Http\Controllers\Admins\AdminGradeController::class, 'update'])           ->name('grade.update');
        Route::get('/{grade_id}',             [\App\Http\Controllers\Admins\AdminGradeController::class, 'viewPermissions'])  ->name('grade.getPermissions');
    });

    /*
     * FAQ관리
     */
    Route::prefix('faq')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'index'])           ->name('faq.index');
        Route::get('/create',           [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'create'])          ->name('faq.create');
        Route::post('',                 [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'store'])           ->name('faq.store');
        Route::get('/downloadexcel',    [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'downloadExcel'])   ->name('faq.download.excel');
        Route::get('/{id}',             [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'show'])            ->name('faq.show');
        Route::put('/{id}',             [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'update'])          ->name('faq.update');
        Route::delete('',               [\App\Http\Controllers\Admins\Boards\BoardFaqController::class, 'destroy'])         ->name('faq.destroy');
    });
    /*
 * 1:1문의 관리
 */
    Route::prefix('inquiry')->group(function (): void {
        Route::get('',                  [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'index'])           ->name('inquiry.index');
        Route::get('/downloadexcel',    [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'downloadExcel'])   ->name('inquiry.download.excel');
        Route::get('/view/{id}',             [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'view'])            ->name('inquiry.view');//답변완료
        Route::get('/{id}',             [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'show'])            ->name('inquiry.show');//답변대기,수정
        Route::put('/{id}',             [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'update'])          ->name('inquiry.update');
        Route::delete('',          [\App\Http\Controllers\Admins\Boards\BoardInquiryController::class, 'destroy'])         ->name('inquiry.destroy');
    });


    Route::prefix('configBoards')->group(function() {
        Route::controller(\App\Http\Controllers\Admins\Boards\BoardConfigController::class)->group(function () : void {
            Route::get('', 'list')->name('configBoards.list');
            Route::get('create', 'create')->name('configBoards.create');
            Route::get('edit/{board_config_id}', 'edit')->name('configBoards.edit');
            Route::post('', 'store')->name('configBoards.store');
            Route::put('/{board_config_id}', 'update')->name('configBoards.update');
            Route::delete('/{board_config_id}', 'destroy')->name('configBoards.destroy');
            Route::get('categorylist', 'categorylist')->name('configBoards.categorylist');
            Route::get('categorylist_ajax', 'categorylist_ajax')->name('configBoards.categorylist_ajax');
            Route::post('categorystore', 'categorystore')->name('configBoards.categorystore');
            Route::get('boardcopy/{board_config_id}', 'boardcopy')->name('configBoards.boardcopy');
            Route::post('boardcopyadd', 'boardcopyadd')->name('configBoards.boardcopyadd');
        });
    });


    Route::prefix('board')->group(function() : void {
        Route::controller(\App\Http\Controllers\Admins\Boards\BoardController::class)->group(function () : void {
            Route::get('/downloadexcel', 'downloadExcel')->name('boards.board.download.Excel');// 엑셀다운로드
            Route::get('', 'index')->name('boards.board.index');
            Route::get('{board_id}', 'list')->name('boards.board.list');
            Route::get('write/{board_id}', 'write')->name('boards.board.write');// 게시글 쓰기
            Route::post('store/{board_id}', 'store')->name('boards.board.store');// 게시글 저장
            Route::get('{board_id}/edit/{post_id}', 'edit')->name('boards.board.edit');//게시글 수정
            Route::put('{board_id}/edit', 'update')->name('boards.board.update');// 게시글 수정 저장
            Route::put('{board_id}/state_update', 'state_update')->name('boards.board.state_update');// 게시글 상태 수정
            Route::get('{board_id}/view/{post_id}', 'show')->name('boards.board.show');// 게시글 보기
            Route::delete('{board_id}', 'destroy')->name('boards.board.destroy');// 게시글 삭제
            Route::post('{board_id}/repliesstore/{post_id}', 'repliesstore')->name('boards.board.repliesstore');// 댓글 쓰기
            Route::get('{board_id}/replieslist/{post_id}', 'replieslist_ajax')->name('boards.board.replieslist_ajax');// 댓글 리스트
            Route::delete('{board_id}/reply/{commentId}', 'replyDestroy')->name('boards.board.replyDestroy');                             // 댓글 삭제
        });
    });

    Route::get('/analytics', [\App\Http\Controllers\Admins\AnalyticsController::class, 'index']);

});


Route::get('/analytics/dashboard', [\App\Http\Controllers\Admins\AnalyticsController::class, 'getDashboardData']);
Route::get('/analytics/realtime', [\App\Http\Controllers\Admins\AnalyticsController::class, 'getRealtimeData']);
