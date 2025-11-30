<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

// =======================
// トップ / home
// =======================

// / と /home はどちらも管理者ログインへ
Route::get('/', function () {
    return redirect()->route('admin.login');
});
Route::get('/home', function () {
    return redirect()->route('admin.login');
});

// =======================
// 一般ユーザー 認証
// =======================

Route::middleware('guest')->group(function () {
    // 会員登録
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // 一般ログイン
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// 一般ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// =======================
// 一般ユーザー 機能
// =======================

Route::middleware('auth')->group(function () {

    // 勤怠登録画面（出勤前／出勤中／休憩中／退勤後）
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 勤怠登録の各操作（出勤・休憩入・休憩戻・退勤）
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    // （必要なら）一般ユーザー側の申請一覧
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');
});

// =======================
// 管理者 認証 ＋ 機能
// =======================

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        // -------- 管理者ログイン（guest のみ）--------
        Route::middleware('guest')->group(function () {
            Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])
                ->name('login');
            Route::post('/login', [AdminAuthenticatedSessionController::class, 'store']);
        });

        // -------- 管理者ログアウト --------
        Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
            ->middleware(['auth', 'admin'])
            ->name('logout');

        // -------- 管理者機能（auth + admin）--------
        Route::middleware(['auth', 'admin'])->group(function () {

            // 勤怠一覧（管理者）
            Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
                ->name('attendance.index');

            // ★ 勤怠詳細画面（管理者）
        Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->name('attendance.show');
            
            // スタッフ一覧
            Route::get('/staff/list', [StaffController::class, 'index'])
                ->name('staff.list');

            // 申請一覧
            Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])
                ->name('stamp_correction_request.list');

            // 修正申請 承認画面表示
            Route::get(
                '/stamp_correction_request/approve/{attendance_correction_request_id}',
                [AdminStampCorrectionRequestController::class, 'approve']
            )->name('stamp_correction_request.approve');

            // 修正申請 承認/却下 更新処理
            Route::post(
                '/stamp_correction_request/approve/{attendance_correction_request_id}',
                [AdminStampCorrectionRequestController::class, 'update']
            )->name('stamp_correction_request.update');
        });
    });
